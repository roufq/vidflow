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

class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 3;

    protected $platformUpload;
    protected $baseUrl;

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
            $disk = env('FILESYSTEM_DISK', 'public');
            
            // 1. Download file temporarily from Google Drive to local server for API streaming
            $tempPath = storage_path('app/temp_' . uniqid() . '_' . basename($job->file_path));
            
            // Menggunakan stream agar RAM tidak jebol saat mendownload video 4GB
            $readStream = Storage::disk($disk)->readStream($job->file_path);
            
            if ($readStream) {
                $writeStream = fopen($tempPath, 'w');
                stream_copy_to_stream($readStream, $writeStream);
                fclose($writeStream);
                fclose($readStream);
            } else {
                // Fallback jika Google Drive gagal memberikan Stream (sering terjadi jika API token limit/expired)
                $content = Storage::disk($disk)->get($job->file_path);
                if ($content === null || $content === false) {
                    throw new \Exception("Gagal mendownload video dari Google Drive. File mungkin telah terhapus atau koneksi Google Drive bermasalah. Path: " . $job->file_path);
                }
                file_put_contents($tempPath, $content);
            }
            $this->platformUpload->update(['progress_percent' => 10]);

            // =====================================================================
            // ACTUAL API LOGIC FOR YOUTUBE
            // =====================================================================
            if ($this->platformUpload->platform === 'youtube') {
                $credential = \App\Models\UserPlatformCredential::where('user_id', $job->user_id)
                    ->where('platform', 'youtube')
                    ->first();

                if (!$credential) {
                    throw new \Exception("YouTube Client ID dan Secret belum dikonfigurasi di menu pengaturan (Connections).");
                }

                $client = new \Google\Client();
                $client->setClientId($credential->app_id);
                $client->setClientSecret($credential->app_secret);
                
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
                $media->setFileSize(filesize($tempPath));

                $status = false;
                $handle = fopen($tempPath, "rb");
                while (!$status && !feof($handle)) {
                    $chunk = fread($handle, $chunkSizeBytes);
                    $status = $media->nextChunk($chunk);
                    
                    // Update progress ke database untuk dilihat di Dashboard
                    $progress = $media->getProgress();
                    $percent = min(99, 10 + intval(($progress / filesize($tempPath)) * 90));
                    $this->platformUpload->update(['progress_percent' => $percent]);
                }
                fclose($handle);
                $client->setDefer(false);

                $finalVideoId = $status['id'];
                $finalUrl = 'https://youtube.com/watch?v=' . $finalVideoId;

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

                $response = \Illuminate\Support\Facades\Http::timeout(3600)->withToken($pageToken)
                    ->attach('source', fopen($tempPath, 'r'), basename($tempPath))
                    ->post('https://graph.facebook.com/v19.0/' . $pageId . '/videos', [
                        'description' => $job->description ?? $job->title,
                        'title' => $job->title,
                    ]);

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

                // 3. Instagram API mewajibkan URL video publik, bukan upload file biner.
                $publicTempDir = public_path('temp_ig');
                if (!is_dir($publicTempDir)) {
                    mkdir($publicTempDir, 0755, true);
                }
                $publicFilename = basename($tempPath);
                $publicFilePath = $publicTempDir . '/' . $publicFilename;
                if (!file_exists($publicFilePath)) {
                    copy($tempPath, $publicFilePath);
                }
                
                $videoUrl = $this->baseUrl . '/temp_ig/' . $publicFilename;

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
                        @unlink($publicFilePath);
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
                        @unlink($publicFilePath);
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

                @unlink($publicFilePath); // Hapus file publik setelah selesai

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
                $this->platformUpload->update(['progress_percent' => 40]);
                
                // 1. Inisialisasi Upload ke TikTok API v2
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
                            'video_size' => filesize($tempPath),
                            'chunk_size' => filesize($tempPath),
                            'total_chunk_count' => 1
                        ]
                    ]);

                if ($initResponse->failed()) {
                    throw new \Exception('TikTok Init API Error: ' . $initResponse->body());
                }

                $this->platformUpload->update(['progress_percent' => 60]);
                
                $uploadUrl = $initResponse->json('data.upload_url');
                $publishId = $initResponse->json('data.publish_id');

                // 2. Upload Binary File Video ke URL yang diberikan TikTok
                $videoSize = filesize($tempPath);
                $uploadResponse = \Illuminate\Support\Facades\Http::timeout(3600)
                    ->withHeaders([
                        'Content-Type' => 'video/mp4',
                        'Content-Range' => 'bytes 0-' . ($videoSize - 1) . '/' . $videoSize,
                    ])
                    ->withOptions([
                        'body' => fopen($tempPath, 'r')
                    ])
                    ->put($uploadUrl);

                if ($uploadResponse->failed()) {
                    throw new \Exception('TikTok Video Upload Error (Status ' . $uploadResponse->status() . '): ' . $uploadResponse->body());
                }

                $this->platformUpload->update(['progress_percent' => 90]);
                
                $finalVideoId = $publishId;
                $finalUrl = 'https://tiktok.com/@me/video/' . $finalVideoId;
            }

            // Cleanup local temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
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

        } catch (\Throwable $e) {
            if (isset($tempPath) && file_exists($tempPath)) @unlink($tempPath);
            
            $this->platformUpload->update([
                'status' => 'failed',
                'error_message' => 'SysError: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(),
                'retry_count' => $this->platformUpload->retry_count + 1
            ]);
            Log::error("Failed to upload to {$this->platformUpload->platform}: " . $e->getMessage());
            
            throw $e;
        }
    }
}
