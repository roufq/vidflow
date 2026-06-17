<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PlatformUpload;
use App\Models\PlatformConnection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Notifications\VideoStatusNotification;
use Illuminate\Support\Facades\Mail;

class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 50; // Ditingkatkan agar tidak prematur gagal saat menunggu antrean WithoutOverlapping

    protected $platformUpload;
    protected $baseUrl;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        $jobId = $this->platformUpload->job_id;

        return [
            (new \Illuminate\Queue\Middleware\WithoutOverlapping($jobId))
                ->releaseAfter(60) // Coba lagi setelah 60 detik jika ter-lock oleh platform lain
                ->expireAfter(3600) // Masa kadaluarsa lock maksimal 1 jam
        ];
    }

    public function __construct(PlatformUpload $platformUpload, $baseUrl = 'http://localhost:8000')
    {
        $this->platformUpload = $platformUpload;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function handle(): void
    {
        $this->platformUpload->update(['status' => 'uploading', 'progress_percent' => 5]);
        $job = $this->platformUpload->uploadJob;
        $connection = PlatformConnection::find($this->platformUpload->connection_id);

        if (!$connection) {
            $this->platformUpload->update(['status' => 'failed', 'error_message' => 'Platform connection not found.']);
            return;
        }

        try {
            $token = Crypt::decryptString($connection->access_token);
            $disk = 'local'; // Membaca file transit dari penyimpanan lokal VPS SSD secara bawaan
            
            // Verifikasi bahwa file video ada di storage lokal VPS
            if (!Storage::disk($disk)->exists($job->file_path)) {
                // Fallback ke Cloud Storage (misal Google Drive) jika file lokal sudah di-backup & dihapus dari VPS
                $cloudDiskName = env('CLOUD_FILESYSTEM_DISK', 'google');
                if (Storage::disk($cloudDiskName)->exists($job->file_path)) {
                    $disk = $cloudDiskName;
                } else {
                    throw new \Exception("File video tidak ditemukan secara lokal maupun di Cloud Storage. Path: " . $job->file_path);
                }
            }
            
            $this->platformUpload->update(['progress_percent' => 10]);

            // =====================================================================
            // ACTUAL API LOGIC FOR YOUTUBE
            // =====================================================================
            if ($this->platformUpload->platform === 'youtube') {
                $client = new \Google\Client();

                $credential = \App\Models\UserPlatformCredential::where('user_id', $job->user_id)
                    ->where('platform', 'youtube')
                    ->first();

                if ($credential) {
                    $client->setClientId($credential->app_id);
                    $client->setClientSecret($credential->app_secret);
                } else {
                    $globalClientId = config('services.youtube.client_id');
                    $globalClientSecret = config('services.youtube.client_secret');

                    if ($globalClientId && $globalClientSecret) {
                        $client->setClientId($globalClientId);
                        $client->setClientSecret($globalClientSecret);
                    } else {
                        throw new \Exception("YouTube Client ID dan Secret belum dikonfigurasi.");
                    }
                }
                
                // Construct the token array for Google Client
                $tokenArray = [
                    'access_token' => $token,
                    'refresh_token' => $connection->refresh_token ? Crypt::decryptString($connection->refresh_token) : null,
                    'created' => $connection->updated_at->timestamp,
                    'expires_in' => $connection->token_expires_at ? max(0, $connection->token_expires_at->timestamp - $connection->updated_at->timestamp) : 3600
                ];
                $client->setAccessToken($tokenArray);

                // Auto Refresh Token if Expired
                if ($client->isAccessTokenExpired()) {
                    if ($connection->refresh_token) {
                        $newTokens = $client->fetchAccessTokenWithRefreshToken();
                        if (isset($newTokens['error'])) {
                            throw new \Exception('Failed to refresh YouTube token: ' . json_encode($newTokens));
                        }
                        $connection->update([
                            'access_token' => Crypt::encryptString($newTokens['access_token']),
                            'token_expires_at' => now()->addSeconds($newTokens['expires_in'] ?? 3600)
                        ]);
                    } else {
                        throw new \Exception('Token expired and no refresh token available. Silakan reconnect ulang akun Anda.');
                    }
                }

                $youtube = new \Google\Service\YouTube($client);
                $video = new \Google\Service\YouTube\Video();
                
                $snippet = new \Google\Service\YouTube\VideoSnippet();
                $snippet->setTitle($job->title);
                $snippet->setDescription($job->description);
                $snippet->setTags($job->tags);
                $snippet->setCategoryId("22"); // People & Blogs

                $statusObj = new \Google\Service\YouTube\VideoStatus();
                $statusObj->privacyStatus = "public"; // Bisa diubah jadi 'private' untuk uji coba

                $video->setSnippet($snippet);
                $video->setStatus($statusObj);

                // Resumable Chunked Upload (Aman untuk file besar)
                $chunkSizeBytes = 2 * 1024 * 1024; // 2MB Chunks
                $client->setDefer(true);
                $insertRequest = $youtube->videos->insert("status,snippet", $video);

                $media = new \Google\Http\MediaFileUpload(
                    $client,
                    $insertRequest,
                    'video/*',
                    null,
                    true,
                    $chunkSizeBytes
                );
                $media->setFileSize($job->file_size_bytes);

                $status = false;
                $readStream = Storage::disk($disk)->readStream($job->file_path);
                if (!$readStream) {
                    throw new \Exception("Gagal membuka stream file video untuk YouTube.");
                }

                while (!$status && !feof($readStream)) {
                    $chunk = fread($readStream, $chunkSizeBytes);
                    $status = $media->nextChunk($chunk);
                    
                    // Update progress ke database untuk dilihat di Dashboard
                    $progress = $media->getProgress();
                    $percent = min(99, 10 + intval(($progress / $job->file_size_bytes) * 90));
                    $this->platformUpload->update(['progress_percent' => $percent]);
                }
                fclose($readStream);
                $client->setDefer(false);

                $finalVideoId = $status['id'];
                $finalUrl = 'https://youtube.com/watch?v=' . $finalVideoId;
                
                // Custom Thumbnail Logic for YouTube
                if ($job->thumbnail_path) {
                    try {
                        $thumbTempPath = storage_path('app/thumb_' . uniqid() . '_' . basename($job->thumbnail_path));
                        
                        $readThumbStream = Storage::disk($disk)->readStream($job->thumbnail_path);
                        if ($readThumbStream) {
                            $writeThumbStream = fopen($thumbTempPath, 'w');
                            stream_copy_to_stream($readThumbStream, $writeThumbStream);
                            fclose($writeThumbStream);
                            fclose($readThumbStream);
                            
                            $chunkSizeBytesThumb = 1 * 1024 * 1024;
                            $client->setDefer(true);
                            $setRequest = $youtube->thumbnails->set($finalVideoId);
                            $mediaThumb = new \Google\Http\MediaFileUpload(
                                $client,
                                $setRequest,
                                'image/*',
                                null,
                                true,
                                $chunkSizeBytesThumb
                            );
                            $mediaThumb->setFileSize(filesize($thumbTempPath));
                            $statusThumb = false;
                            $handleThumb = fopen($thumbTempPath, "rb");
                            while (!$statusThumb && !feof($handleThumb)) {
                                $chunkThumb = fread($handleThumb, $chunkSizeBytesThumb);
                                $statusThumb = $mediaThumb->nextChunk($chunkThumb);
                            }
                            fclose($handleThumb);
                            $client->setDefer(false);
                            @unlink($thumbTempPath);
                        } else {
                            $thumbContent = Storage::disk($disk)->get($job->thumbnail_path);
                            if ($thumbContent) {
                                file_put_contents($thumbTempPath, $thumbContent);
                                $chunkSizeBytesThumb = 1 * 1024 * 1024;
                                $client->setDefer(true);
                                $setRequest = $youtube->thumbnails->set($finalVideoId);
                                $mediaThumb = new \Google\Http\MediaFileUpload(
                                    $client,
                                    $setRequest,
                                    'image/*',
                                    null,
                                    true,
                                    $chunkSizeBytesThumb
                                );
                                $mediaThumb->setFileSize(filesize($thumbTempPath));
                                $statusThumb = false;
                                $handleThumb = fopen($thumbTempPath, "rb");
                                while (!$statusThumb && !feof($handleThumb)) {
                                    $chunkThumb = fread($handleThumb, $chunkSizeBytesThumb);
                                    $statusThumb = $mediaThumb->nextChunk($chunkThumb);
                                }
                                fclose($handleThumb);
                                $client->setDefer(false);
                                @unlink($thumbTempPath);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Gagal mengupload thumbnail ke YouTube: " . $e->getMessage());
                    }
                }

            } elseif ($this->platformUpload->platform === 'facebook') {
                // =====================================================================
                // ACTUAL API LOGIC FOR FACEBOOK
                // =====================================================================
                $this->platformUpload->update(['progress_percent' => 30]);
                
                $pagesResponse = \Illuminate\Support\Facades\Http::withToken($token)
                    ->get('https://graph.facebook.com/v19.0/me/accounts');
                
                $pages = $pagesResponse->json('data');
                if (empty($pages)) {
                    throw new \Exception('Facebook Error: Anda tidak memiliki Halaman (Facebook Page). Anda WAJIB membuat minimal 1 Halaman Facebook.');
                }

                $this->platformUpload->update(['progress_percent' => 50]);

                $page = $pages[0]; 
                $pageToken = $page['access_token'];
                $pageId = $page['id'];

                $readStream = Storage::disk($disk)->readStream($job->file_path);
                if (!$readStream) {
                    throw new \Exception("Gagal membuka stream file video untuk Facebook.");
                }

                $response = \Illuminate\Support\Facades\Http::timeout(3600)->withToken($pageToken)
                    ->attach('source', $readStream, basename($job->file_path))
                    ->post('https://graph.facebook.com/v19.0/' . $pageId . '/videos', [
                        'description' => $job->description ?? $job->title,
                        'title' => $job->title,
                    ]);

                if (is_resource($readStream)) {
                    fclose($readStream);
                }

                if ($response->failed()) {
                    throw new \Exception('Facebook API Error: ' . $response->body());
                }

                $this->platformUpload->update(['progress_percent' => 90]);
                $finalVideoId = $response->json('id');
                $finalUrl = 'https://facebook.com/' . $pageId . '/videos/' . $finalVideoId;

            } elseif ($this->platformUpload->platform === 'instagram') {
                // =====================================================================
                // ACTUAL API LOGIC FOR INSTAGRAM
                // =====================================================================
                $this->platformUpload->update(['progress_percent' => 20]);
                
                // 1. Dapatkan Halaman Facebook yang terhubung ke Instagram
                $pagesResponse = \Illuminate\Support\Facades\Http::withToken($token)
                    ->get('https://graph.facebook.com/v19.0/me/accounts');
                $pages = $pagesResponse->json('data');
                if (empty($pages)) {
                    throw new \Exception('Instagram Error: Tidak ada Halaman Facebook yang ditemukan. Akun Instagram Bisnis wajib ditautkan ke Halaman Facebook.');
                }
                
                $page = $pages[0];
                $pageToken = $page['access_token'];
                $pageId = $page['id'];

                // 2. Dapatkan ID Akun Instagram Bisnis yang tertaut ke Halaman tersebut
                $igResponse = \Illuminate\Support\Facades\Http::withToken($pageToken)
                    ->get('https://graph.facebook.com/v19.0/' . $pageId . '?fields=instagram_business_account');
                
                $igAccountId = $igResponse->json('instagram_business_account.id');
                if (!$igAccountId) {
                    throw new \Exception('Instagram Error: Halaman Facebook ini tidak memiliki akun Instagram Bisnis yang tertaut. Harap tautkan akun Instagram Profesional ke Halaman Facebook Anda.');
                }

                $this->platformUpload->update(['progress_percent' => 40]);

                // 3. Instagram API mewajibkan URL video publik. Kita buat Signed URL sementara 
                // agar Meta bisa melakukan stream langsung dari Google Drive lewat server kita tanpa local caching.
                $videoUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'video.stream',
                    now()->addHours(4), // Valid selama 4 jam
                    ['job' => $job->id]
                );

                // 4. Buat Container Media (Hanya jika belum dibuat sebelumnya)
                $creationId = $this->platformUpload->platform_video_id;

                if (!$creationId) {
                    $containerResponse = \Illuminate\Support\Facades\Http::withToken($pageToken)
                        ->post('https://graph.facebook.com/v19.0/' . $igAccountId . '/media', [
                            'media_type' => 'REELS',
                            'video_url' => $videoUrl,
                            'caption' => $job->description ?? $job->title,
                        ]);

                    if ($containerResponse->failed()) {
                        throw new \Exception('Instagram API Container Error: ' . $containerResponse->body());
                    }

                    $creationId = $containerResponse->json('id');
                    $this->platformUpload->update([
                        'platform_video_id' => $creationId, 
                        'progress_percent' => 60
                    ]);
                }

                // 5. Cek status render video di server Instagram
                $statusCheck = \Illuminate\Support\Facades\Http::withToken($pageToken)
                    ->get('https://graph.facebook.com/v19.0/' . $creationId . '?fields=status_code');
                
                $statusCode = $statusCheck->json('status_code');

                if ($statusCode !== 'FINISHED') {
                    if ($statusCode === 'ERROR') {
                        throw new \Exception('Instagram Error: Meta gagal memproses video ini. Status: ERROR.');
                    }
                    
                    // Video belum selesai dirender (IN_PROGRESS). 
                    // Lempar kembali Job ini ke antrean untuk dicek lagi 30 detik kemudian!
                    $this->release(30);
                    return; // Hentikan eksekusi saat ini
                }

                // 6. Jika FINISHED, Publish Container!
                $publishResponse = \Illuminate\Support\Facades\Http::withToken($pageToken)
                    ->post('https://graph.facebook.com/v19.0/' . $igAccountId . '/media_publish', [
                        'creation_id' => $creationId,
                    ]);

                if ($publishResponse->failed()) {
                    throw new \Exception('Instagram API Publish Error: ' . $publishResponse->body());
                }

                $finalVideoId = $publishResponse->json('id');
                $finalUrl = 'https://instagram.com/p/' . $finalVideoId;
                
                $this->platformUpload->update([
                    'progress_percent' => 100,
                    'platform_video_id' => $finalVideoId, // Timpa creation_id dengan ID final
                    'platform_url' => $finalUrl
                ]);

            } elseif ($this->platformUpload->platform === 'tiktok') {
                // =====================================================================
                // ACTUAL API LOGIC FOR TIKTOK
                // =====================================================================
                
                // 1. Cek & Refresh Access Token jika kadaluarsa
                if ($connection->token_expires_at && $connection->token_expires_at->subMinutes(5)->isPast()) {
                    $credential = \App\Models\UserPlatformCredential::where('user_id', $job->user_id)
                        ->where('platform', 'tiktok')
                        ->first();

                    if ($credential) {
                        $clientKey = $credential->app_id;
                        $clientSecret = $credential->app_secret;
                    } else {
                        $clientKey = config('services.tiktok.client_id');
                        $clientSecret = config('services.tiktok.client_secret');
                    }

                    if (empty($clientKey) || empty($clientSecret)) {
                        throw new \Exception("Kredensial API TikTok (Client Key / Secret) tidak terkonfigurasi.");
                    }

                    if (!$connection->refresh_token) {
                        throw new \Exception("Token TikTok kadaluarsa dan tidak ada Refresh Token. Silakan hubungkan ulang akun Anda.");
                    }
                    $refreshToken = Crypt::decryptString($connection->refresh_token);

                    $refreshResponse = \Illuminate\Support\Facades\Http::asForm()
                        ->post('https://open.tiktokapis.com/v2/oauth/token/', [
                            'client_key' => $clientKey,
                            'client_secret' => $clientSecret,
                            'grant_type' => 'refresh_token',
                            'refresh_token' => $refreshToken,
                        ]);

                    if ($refreshResponse->failed()) {
                        throw new \Exception("Gagal me-refresh token TikTok: " . $refreshResponse->body());
                    }

                    $responseData = $refreshResponse->json();
                    
                    if (empty($responseData['access_token'])) {
                        throw new \Exception("Respon refresh token TikTok tidak valid: " . json_encode($responseData));
                    }

                    $connection->update([
                        'access_token' => Crypt::encryptString($responseData['access_token']),
                        'refresh_token' => !empty($responseData['refresh_token']) ? Crypt::encryptString($responseData['refresh_token']) : $connection->refresh_token,
                        'token_expires_at' => now()->addSeconds($responseData['expires_in'] ?? 86400),
                    ]);

                    $token = $responseData['access_token'];
                }

                $publishId = $this->platformUpload->platform_video_id;

                if (!$publishId) {
                    $this->platformUpload->update(['progress_percent' => 40]);
                    
                    // 2. Inisialisasi Upload ke TikTok API v2
                    $initResponse = \Illuminate\Support\Facades\Http::withToken($token)
                        ->post('https://open.tiktokapis.com/v2/post/publish/video/init/', [
                            'post_info' => [
                                'title' => $job->title,
                                'privacy_level' => 'SELF_ONLY', // Wajib SELF_ONLY selama aplikasi masih berstatus Sandbox/Draft
                                'disable_duet' => false,
                                'disable_comment' => false,
                                'disable_stitch' => false
                            ],
                            'source_info' => [
                                'source' => 'FILE_UPLOAD',
                                'video_size' => $job->file_size_bytes,
                                'chunk_size' => $job->file_size_bytes,
                                'total_chunk_count' => 1
                            ]
                        ]);

                    if ($initResponse->failed()) {
                        throw new \Exception('TikTok Init API Error: ' . $initResponse->body());
                    }

                    $this->platformUpload->update(['progress_percent' => 60]);
                    
                    $uploadUrl = $initResponse->json('data.upload_url');
                    $publishId = $initResponse->json('data.publish_id');

                    if (!$uploadUrl || !$publishId) {
                        throw new \Exception('Gagal mendapatkan upload_url atau publish_id dari TikTok.');
                    }

                    // 3. Upload Binary File Video ke URL yang diberikan TikTok
                    $readStream = Storage::disk($disk)->readStream($job->file_path);
                    if (!$readStream) {
                        throw new \Exception("Gagal membuka stream file video untuk TikTok.");
                    }
                    
                    $uploadResponse = \Illuminate\Support\Facades\Http::timeout(3600)
                        ->withHeaders([
                            'Content-Range' => 'bytes 0-' . ($job->file_size_bytes - 1) . '/' . $job->file_size_bytes,
                        ])
                        ->withBody($readStream, 'video/mp4')
                        ->put($uploadUrl);

                    if (is_resource($readStream)) {
                        fclose($readStream);
                    }

                    if ($uploadResponse->failed()) {
                        throw new \Exception('TikTok Video Upload Error (Status ' . $uploadResponse->status() . '): ' . $uploadResponse->body());
                    }

                    $this->platformUpload->update([
                        'platform_video_id' => $publishId,
                        'progress_percent' => 80
                    ]);
                }

                // 4. Polling Status Publish TikTok
                $statusResponse = \Illuminate\Support\Facades\Http::withToken($token)
                    ->post('https://open.tiktokapis.com/v2/post/publish/status/fetch/', [
                        'publish_id' => $publishId
                    ]);

                if ($statusResponse->failed()) {
                    throw new \Exception('TikTok Fetch Status API Error: ' . $statusResponse->body());
                }

                $statusData = $statusResponse->json('data');
                $publishStatus = $statusData['status'] ?? null;

                if ($publishStatus !== 'PUBLISH_COMPLETE' && $publishStatus !== 'SEND_TO_USER_INBOX') {
                    if ($publishStatus === 'FAILED') {
                        throw new \Exception('TikTok Upload Failed: ' . ($statusData['fail_reason'] ?? 'Unknown reason'));
                    }

                    // Masih diproses, antrekan kembali
                    $this->release(30);
                    return;
                }

                $finalVideoId = null;
                if (!empty($statusData['publicaly_available_post_id'])) {
                    $finalVideoId = $statusData['publicaly_available_post_id'][0];
                } else {
                    $finalVideoId = $publishId;
                }
                
                $finalUrl = 'https://tiktok.com/@me/video/' . $finalVideoId;
            }

            // Mark job as absolutely DONE
            $this->platformUpload->update([
                'status' => 'done',
                'progress_percent' => 100,
                'platform_video_id' => $finalVideoId,
                'platform_url' => $finalUrl,
                'uploaded_at' => now(),
            ]);

            Log::info("Successfully uploaded video to {$this->platformUpload->platform} for user {$job->user_id}");
            
            // Send Success Email
            try {
                $user = \App\Models\User::find($job->user_id);
                if ($user) {
                    $user->notify(new VideoStatusNotification($this->platformUpload));
                }
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim email notifikasi sukses: " . $e->getMessage());
            }

        } catch (\Throwable $e) {
            $this->platformUpload->update([
                'status' => 'failed',
                'error_message' => 'SysError: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(),
                'retry_count' => $this->platformUpload->retry_count + 1
            ]);
            Log::error("Failed to upload to {$this->platformUpload->platform}: " . $e->getMessage());
            
            // Send Failure Email if out of retries (or just send it anyway for transparency)
            if ($this->platformUpload->retry_count >= $this->tries) {
                try {
                    $user = \App\Models\User::find($job->user_id);
                    if ($user) {
                        $user->notify(new VideoStatusNotification($this->platformUpload));
                    }
                } catch (\Exception $mailEx) {
                    Log::warning("Gagal mengirim email/notifikasi: " . $mailEx->getMessage());
                }
            }
            
            throw $e;
        } finally {
            $this->cleanUpCache();
        }
    }

    /**
     * Bersihkan cache file lokal dan lakukan backup ke Google Drive jika semua platform selesai.
     */
    protected function cleanUpCache(): void
    {
        $job = $this->platformUpload->uploadJob;
        if (!$job) {
            return;
        }

        $localDisk = \Illuminate\Support\Facades\Storage::disk('local');
        $cloudDiskName = env('CLOUD_FILESYSTEM_DISK', 'google');

        // 1. Bersihkan file sampah yatim piatu yang berumur lebih dari 24 jam
        try {
            $files = $localDisk->files('transit_videos');
            foreach ($files as $file) {
                if ($localDisk->lastModified($file) < now()->subHours(24)->getTimestamp()) {
                    $localDisk->delete($file);
                    Log::info("Cleaned up orphaned transit video: {$file}");
                }
            }

            $thumbnails = $localDisk->files('transit_thumbnails');
            foreach ($thumbnails as $thumb) {
                if ($localDisk->lastModified($thumb) < now()->subHours(24)->getTimestamp()) {
                    $localDisk->delete($thumb);
                    Log::info("Cleaned up orphaned transit thumbnail: {$thumb}");
                }
            }
        } catch (\Exception $e) {
            Log::warning("Gagal membersihkan cache lama: " . $e->getMessage());
        }

        // 2. Periksa apakah semua platform untuk job ini sudah selesai diproses (done atau failed)
        $remaining = \App\Models\PlatformUpload::where('job_id', $job->id)
            ->whereIn('status', ['pending', 'uploading'])
            ->count();

        if ($remaining === 0) {
            try {
                // Copy file video ke Google Drive jika masih ada di local VPS
                if ($localDisk->exists($job->file_path)) {
                    Log::info("Uploading video from local VPS to Cloud Storage ({$cloudDiskName}): {$job->id}");

                    $localStream = $localDisk->readStream($job->file_path);
                    if ($localStream) {
                        $cloudPath = 'transit_videos/' . basename($job->file_path);
                        $uploaded = \Illuminate\Support\Facades\Storage::disk($cloudDiskName)->writeStream($cloudPath, $localStream);
                        if (is_resource($localStream)) {
                            fclose($localStream);
                        }

                        if ($uploaded) {
                            Log::info("Successfully uploaded video to Cloud Storage: {$cloudPath}");
                            
                            // Update path file ke cloud
                            $job->update(['file_path' => $cloudPath]);
                            
                            // Hapus file video lokal dari VPS
                            $localDisk->delete($job->file_path);
                        } else {
                            Log::error("Gagal menyalin video ke Cloud Storage.");
                        }
                    }
                }

                // Copy thumbnail ke Google Drive jika ada dan masih di local VPS
                if ($job->thumbnail_path && $localDisk->exists($job->thumbnail_path)) {
                    $localThumbStream = $localDisk->readStream($job->thumbnail_path);
                    if ($localThumbStream) {
                        $cloudThumbPath = 'transit_thumbnails/' . basename($job->thumbnail_path);
                        $uploadedThumb = \Illuminate\Support\Facades\Storage::disk($cloudDiskName)->writeStream($cloudThumbPath, $localThumbStream);
                        if (is_resource($localThumbStream)) {
                            fclose($localThumbStream);
                        }

                        if ($uploadedThumb) {
                            // Update path thumbnail ke cloud
                            $job->update(['thumbnail_path' => $cloudThumbPath]);
                            
                            // Hapus file thumbnail lokal dari VPS
                            $localDisk->delete($job->thumbnail_path);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Gagal melakukan backup video/thumbnail ke Cloud Storage: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Fitur ini penting agar ketika Job "mati" di tengah jalan (misal kehabisan memori atau limit retry habis),
        // status di database tetap berubah menjadi failed dan tidak nyangkut di "uploading" selamanya.
        $this->platformUpload->update([
            'status' => 'failed',
            'error_message' => 'Job Failed/Timeout/Max Tries Reached: ' . $exception->getMessage()
        ]);

        Log::error("Job ProcessVideoUpload mati secara fatal untuk {$this->platformUpload->platform}: " . $exception->getMessage());

        try {
            $job = $this->platformUpload->uploadJob;
            $user = \App\Models\User::find($job->user_id);
            if ($user) {
                $user->notify(new VideoStatusNotification($this->platformUpload));
            }
        } catch (\Exception $mailEx) {
            Log::warning("Gagal mengirim email notifikasi gagal saat job mati: " . $mailEx->getMessage());
        }
    }
}
