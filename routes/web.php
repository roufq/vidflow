<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PlatformConnectionController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\WebhookController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Halaman Privacy Policy
Route::get('/privacy', function () {
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Privacy Policy - VidFlow</title>
        <link rel="icon" type="image/png" href="/vidflow-icon.png">
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 2rem; }
            h1 { color: #111; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
            h2 { color: #444; margin-top: 2rem; }
            p { margin-bottom: 1rem; }
        </style>
    </head>
    <body>
        <div style="display: flex; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 2rem;">
            <img src="/vidflow-icon.png" alt="VidFlow Icon" style="width: 48px; height: 48px; margin-right: 15px; border-radius: 8px;">
            <h1 style="border: none; padding: 0; margin: 0;">VidFlow Privacy Policy</h1>
        </div>
        <p><strong>Last Updated:</strong> June 2026</p>
        
        <h2>1. Information We Collect</h2>
        <p>We collect basic profile information (Name, Email) and OAuth access tokens required to publish videos to your connected social media channels. When you connect your YouTube, Facebook, Instagram, or TikTok accounts, we access your basic profile information and the ability to publish videos on your behalf via their respective APIs.</p>
        
        <h2>2. Use of Third-Party Data (Google, Meta, TikTok)</h2>
        <p>VidFlow uses API services from Google (YouTube), Meta (Facebook & Instagram), and TikTok to facilitate video uploading and scheduling. We strictly use your data (access tokens) only to provide the upload functionality you request. We do not use your data for any other purposes, nor do we run automated scripts outside of your explicit upload commands.</p>
        
        <h2>3. Data Storage and Sharing</h2>
        <p>Your OAuth tokens are securely encrypted in our database. We <strong>do not sell, rent, or share</strong> your personal information or connected account data with any third parties, AI models, or advertising networks.</p>
        
        <h2>4. Data Deletion and Revoking Access</h2>
        <p>You can revoke VidFlow's access to your social media accounts and delete your data at any time by logging into your VidFlow dashboard and clicking the "Disconnect" button. Upon disconnection, all related access tokens and account references are permanently deleted from our servers.</p>
        <p>Additionally, you can revoke access directly through the security settings of your respective Google, Facebook, Instagram, or TikTok accounts. For Google accounts, you can manage access via the <a href="https://myaccount.google.com/permissions" target="_blank">Google Security Settings</a>.</p>
        
        <h2>5. Third-Party Policies</h2>
        <p>By using VidFlow to connect to third-party platforms, you also agree to their respective privacy policies:</p>
        <ul>
            <li><a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a></li>
            <li><a href="https://www.facebook.com/privacy/policy/" target="_blank">Meta Privacy Policy</a></li>
            <li><a href="https://www.tiktok.com/legal/page/row/privacy-policy/en" target="_blank">TikTok Privacy Policy</a></li>
        </ul>
        
        <p style="margin-top: 3rem; font-size: 0.9em; color: #666;">
            Contact us: roufmawanto194@gmail.com
        </p>
    </body>
    </html>
HTML;
});

// Halaman Terms of Service
Route::get('/terms', function () {
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Terms of Service - VidFlow</title>
        <link rel="icon" type="image/png" href="/vidflow-icon.png">
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 2rem; }
            h1 { color: #111; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
            h2 { color: #444; margin-top: 2rem; }
            p { margin-bottom: 1rem; }
        </style>
    </head>
    <body>
        <div style="display: flex; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 2rem;">
            <img src="/vidflow-icon.png" alt="VidFlow Icon" style="width: 48px; height: 48px; margin-right: 15px; border-radius: 8px;">
            <h1 style="border: none; padding: 0; margin: 0;">VidFlow Terms of Service</h1>
        </div>
        <p><strong>Last Updated:</strong> June 2026</p>
        <p>Welcome to VidFlow. By using our service, you agree to these terms. VidFlow is a video management and scheduling dashboard designed to help users upload content to various social media platforms.</p>
        <h2>1. User Responsibilities</h2>
        <p>You must ensure that any video content you upload via VidFlow complies with the copyright laws and the community guidelines of the respective platforms (YouTube, Facebook, Instagram, TikTok).</p>
        <h2>2. API Services</h2>
        <p>Our application uses API services from YouTube, Meta, and TikTok. By authenticating your accounts, you grant us permission to publish videos on your behalf. We do not use your account for any other automated actions.</p>
        <h2>3. Third-Party Terms of Service</h2>
        <p>By using VidFlow to connect to third-party platforms, you also agree to be bound by their respective Terms of Service:</p>
        <ul>
            <li><a href="https://www.youtube.com/t/terms" target="_blank">YouTube Terms of Service</a></li>
            <li><a href="https://www.facebook.com/legal/terms" target="_blank">Meta/Facebook Terms of Service</a></li>
            <li><a href="https://www.tiktok.com/legal/page/row/terms-of-service/en" target="_blank">TikTok Terms of Service</a></li>
        </ul>
        
        <p style="margin-top: 3rem; font-size: 0.9em; color: #666;">
            Contact us: roufmawanto194@gmail.com
        </p>
    </body>
    </html>
HTML;
});

Route::get('/video-stream/{job}', function (\App\Models\UploadJob $job) {
    if (! request()->hasValidSignature()) {
        abort(401, 'Invalid or expired signature.');
    }

    $localDisk = \Illuminate\Support\Facades\Storage::disk('local');
    $cloudDiskName = env('CLOUD_FILESYSTEM_DISK', 'google');
    
    // Tentukan disk mana yang menyimpan file video saat ini
    if ($localDisk->exists($job->file_path)) {
        $diskInstance = $localDisk;
        $fileSize = $localDisk->size($job->file_path);
    } else {
        // Fallback ke Cloud Storage jika file lokal sudah dipindahkan & dihapus
        $diskInstance = \Illuminate\Support\Facades\Storage::disk($cloudDiskName);
        if (!$diskInstance->exists($job->file_path)) {
            abort(404, 'File not found.');
        }
        $fileSize = $job->file_size_bytes;
    }

    $headers = [
        'Content-Type' => 'video/mp4',
        'Accept-Ranges' => 'bytes',
        'Content-Disposition' => 'inline; filename="' . basename($job->file_path) . '"',
    ];

    $range = request()->header('Range');
    if ($range) {
        // Format range: bytes=start-end
        if (preg_match('/bytes=\s*(\d+)-(\d*)/i', $range, $matches)) {
            $start = intval($matches[1]);
            $end = $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;
            
            if ($start >= $fileSize || $end >= $fileSize || $start > $end) {
                return response('Invalid Range', 416, [
                    'Content-Range' => "bytes */{$fileSize}",
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            $length = $end - $start + 1;
            
            $stream = $diskInstance->readStream($job->file_path);
            if (!$stream) {
                abort(500, 'Could not open video stream.');
            }

            return response()->stream(function () use ($stream, $start, $length) {
                // Pindah posisi baca byte (seeking)
                $meta = stream_get_meta_data($stream);
                $seekable = $meta['seekable'] ?? false;
                
                if ($seekable) {
                    fseek($stream, $start);
                } else {
                    // Manual skip byte jika stream dari cloud storage tidak mendukung seeking
                    $discarded = 0;
                    while ($discarded < $start && !feof($stream)) {
                        $toRead = min(8192, $start - $discarded);
                        $data = fread($stream, $toRead);
                        if ($data === false || $data === '') {
                            break;
                        }
                        $discarded += strlen($data);
                    }
                }

                $chunkSize = 8192; // Buffer 8KB
                $bytesRemaining = $length;
                
                while ($bytesRemaining > 0 && !feof($stream)) {
                    $readSize = min($chunkSize, $bytesRemaining);
                    $data = fread($stream, $readSize);
                    if ($data === false || $data === '') {
                        break;
                    }
                    echo $data;
                    flush();
                    $bytesRemaining -= strlen($data);
                }
                
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, 206, array_merge($headers, [
                'Content-Length' => $length,
                'Content-Range' => "bytes {$start}-{$end}/{$fileSize}"
            ]));
        }
    }

    // Default: Response penuh 200 OK
    $stream = $diskInstance->readStream($job->file_path);
    if (!$stream) {
        abort(500, 'Could not open video stream.');
    }

    return response()->stream(function () use ($stream) {
        fpassthru($stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
    }, 200, array_merge($headers, [
        'Content-Length' => $fileSize
    ]));
})->name('video.stream');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $connections = \App\Models\PlatformConnection::where('user_id', auth()->id())->get()->groupBy('platform');
        return Inertia::render('Dashboard', ['connections' => $connections]);
    })->name('dashboard');

    Route::get('/analytics', function () {
        // Calculate uploads for the current month only for the quota
        $currentMonthUploads = \App\Models\UploadJob::where('user_id', auth()->id())
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        $monthlyLimit = auth()->user()->upload_limit; // Now pulls from User model attribute
        
        $activeConnections = \App\Models\PlatformConnection::where('user_id', auth()->id())->count();
        $connectionsList = \App\Models\PlatformConnection::where('user_id', auth()->id())->get();
        
        $uploads = \App\Models\PlatformUpload::with(['uploadJob:id,title', 'connection'])
            ->whereHas('uploadJob', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->whereNotNull('platform_video_id')
            ->orderByDesc('uploaded_at')
            ->get();
        
        $totalViews = $uploads->sum('views');
        $totalLikes = $uploads->sum('likes');

        return Inertia::render('Analytics', [
            'currentMonthUploads' => $currentMonthUploads,
            'monthlyLimit' => $monthlyLimit,
            'activeConnections' => $activeConnections,
            'totalViews' => $totalViews,
            'totalLikes' => $totalLikes,
            'recentUploads' => $uploads,
            'connectionsList' => $connectionsList
        ]);
    })->name('analytics');

    Route::post('/analytics/sync', function () {
        $uploads = \App\Models\PlatformUpload::whereHas('uploadJob', function($q) {
            $q->where('user_id', auth()->id());
        })->whereNotNull('platform_video_id')->get();
        
        // This is the manual sync logic. 
        // In a full production environment, this iterates through $uploads and queries:
        // - YouTube API: videos.list(statistics)
        // - Meta Graph API: /{video_id}?fields=views,likes
        // - TikTok API: /video/query/
        foreach ($uploads as $upload) {
            $connection = \App\Models\PlatformConnection::find($upload->connection_id);
            if (!$connection) continue;

            try {
                $accessToken = \Illuminate\Support\Facades\Crypt::decryptString($connection->access_token);
                
                if ($upload->platform === 'youtube') {
                    $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
                        ->get('https://www.googleapis.com/youtube/v3/videos', [
                            'part' => 'statistics',
                            'id' => $upload->platform_video_id
                        ]);
                    
                    if ($response->successful() && isset($response->json()['items'][0]['statistics'])) {
                        $stats = $response->json()['items'][0]['statistics'];
                        $upload->views = $stats['viewCount'] ?? $upload->views;
                        $upload->likes = $stats['likeCount'] ?? $upload->likes;
                    }
                } elseif ($upload->platform === 'facebook') {
                    $pagesResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)
                        ->get('https://graph.facebook.com/v19.0/me/accounts');
                    
                    $pages = $pagesResponse->json('data');
                    if (!empty($pages)) {
                        $pageToken = $pages[0]['access_token'];
                        
                        $response = \Illuminate\Support\Facades\Http::withToken($pageToken)
                            ->get("https://graph.facebook.com/v19.0/{$upload->platform_video_id}", [
                                'fields' => 'likes.summary(true)'
                            ]);
                            
                        $insightsResponse = \Illuminate\Support\Facades\Http::withToken($pageToken)
                            ->get("https://graph.facebook.com/v19.0/{$upload->platform_video_id}/video_insights/total_video_views");
                        
                        if ($response->successful()) {
                            $stats = $response->json();
                            $upload->likes = isset($stats['likes']['summary']['total_count']) ? $stats['likes']['summary']['total_count'] : 0;
                            
                            if ($insightsResponse->successful()) {
                                $viewStats = $insightsResponse->json('data');
                                $upload->views = isset($viewStats[0]['values'][0]['value']) ? $viewStats[0]['values'][0]['value'] : 0;
                            } else {
                                $upload->views = 0;
                            }
                            
                            $upload->error_message = null;
                        } else {
                            $upload->error_message = 'FB API Error: ' . $response->json('error.message', 'Unknown error');
                        }
                    } else {
                        $upload->error_message = 'FB Error: No pages found for this user.';
                    }
                } elseif ($upload->platform === 'instagram') {
                    $pagesResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)
                        ->get('https://graph.facebook.com/v19.0/me/accounts');
                    
                    $pages = $pagesResponse->json('data');
                    if (!empty($pages)) {
                        $pageToken = $pages[0]['access_token'];
                        
                        $response = \Illuminate\Support\Facades\Http::withToken($pageToken)
                            ->get("https://graph.facebook.com/v19.0/{$upload->platform_video_id}", [
                                'fields' => 'like_count,comments_count'
                            ]);
                        
                        if ($response->successful()) {
                            $stats = $response->json();
                            $upload->likes = isset($stats['like_count']) ? $stats['like_count'] : 0;
                            $upload->views = isset($stats['view_count']) ? $stats['view_count'] : 0;
                            $upload->error_message = null;
                        } else {
                            $upload->error_message = 'IG API Error: ' . $response->json('error.message', 'Unknown error');
                        }
                    } else {
                        $upload->error_message = 'IG Error: No pages found for this user.';
                    }
                } elseif ($upload->platform === 'tiktok') {
                    // Check if token expired or about to expire
                    if ($connection->token_expires_at && $connection->token_expires_at->subMinutes(5)->isPast()) {
                        $credential = \App\Models\UserPlatformCredential::where('user_id', $connection->user_id)
                            ->where('platform', 'tiktok')
                            ->first();

                        if ($credential) {
                            $clientKey = $credential->app_id;
                            $clientSecret = $credential->app_secret;
                        } else {
                            $clientKey = config('services.tiktok.client_id');
                            $clientSecret = config('services.tiktok.client_secret');
                        }

                        if (!empty($clientKey) && !empty($clientSecret) && $connection->refresh_token) {
                            try {
                                $refreshToken = \Illuminate\Support\Facades\Crypt::decryptString($connection->refresh_token);

                                $refreshResponse = \Illuminate\Support\Facades\Http::asForm()
                                    ->post('https://open.tiktokapis.com/v2/oauth/token/', [
                                        'client_key' => $clientKey,
                                        'client_secret' => $clientSecret,
                                        'grant_type' => 'refresh_token',
                                        'refresh_token' => $refreshToken,
                                    ]);

                                if ($refreshResponse->successful()) {
                                    $responseData = $refreshResponse->json();
                                    if (!empty($responseData['access_token'])) {
                                        $connection->update([
                                            'access_token' => \Illuminate\Support\Facades\Crypt::encryptString($responseData['access_token']),
                                            'refresh_token' => !empty($responseData['refresh_token']) ? \Illuminate\Support\Facades\Crypt::encryptString($responseData['refresh_token']) : $connection->refresh_token,
                                            'token_expires_at' => now()->addSeconds($responseData['expires_in'] ?? 86400),
                                        ]);
                                        $accessToken = $responseData['access_token'];
                                    }
                                }
                            } catch (\Exception $refreshEx) {
                                \Illuminate\Support\Facades\Log::error('TikTok token refresh during sync failed: ' . $refreshEx->getMessage());
                            }
                        }
                    }

                    $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
                        ->post('https://open.tiktokapis.com/v2/video/query/?fields=view_count,like_count', [
                            'filters' => ['video_ids' => [$upload->platform_video_id]]
                        ]);

                    if ($response->successful() && isset($response->json()['data']['videos'][0])) {
                        $stats = $response->json()['data']['videos'][0];
                        $upload->views = $stats['view_count'] ?? $upload->views;
                        $upload->likes = $stats['like_count'] ?? $upload->likes;
                    }
                }

                $upload->last_synced_at = now();
                $upload->save();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Analytics sync failed for platform: ' . $upload->platform . ' Error: ' . $e->getMessage());
                // Silently continue for other uploads
            }
        }

        return redirect()->back()->with('success', 'Sinkronisasi analitik selesai ditarik dari platform (YouTube/Meta/TikTok)!');
    })->name('analytics.sync')->middleware('throttle:5,1');

    // ROUTE SEMENTARA UNTUK RESET DATA DUMMY
    Route::get('/analytics/reset-dummy-data', function () {
        \App\Models\PlatformUpload::whereHas('uploadJob', function($q) {
            $q->where('user_id', auth()->id());
        })->update([
            'views' => 0,
            'likes' => 0
        ]);
        return redirect()->route('analytics')->with('success', 'Data berhasil di-reset ke 0.');
    });


    Route::get('/history', [HistoryController::class, 'index'])->name('history');
    Route::post('/history/retry/{id}', [HistoryController::class, 'retry'])->name('history.retry');
    Route::post('/notifications/read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read');
    Route::get('/connections', [PlatformConnectionController::class, 'index'])->name('connections');

    // OAuth Platform Connections
    Route::post('/auth/{platform}/credentials', [PlatformConnectionController::class, 'saveCredentials'])->name('platform.credentials');
    Route::get('/auth/{platform}', [PlatformConnectionController::class, 'redirect'])->name('platform.redirect');
    // Override khusus TikTok untuk menghindari kata "tiktok" pada URI callback
    Route::get('/auth/tt/callback', function () {
        return app(\App\Http\Controllers\PlatformConnectionController::class)->callback('tiktok');
    })->name('tt.callback.override');

    Route::get('/auth/{platform}/callback', [PlatformConnectionController::class, 'callback'])->name('platform.callback');
    Route::delete('/auth/connection/{id}', [PlatformConnectionController::class, 'disconnect'])->name('platform.disconnect');

    // Upload Video
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store')->middleware('throttle:10,1');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Access
    Route::middleware(['role:super-admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');

        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    });
});

require __DIR__ . '/auth.php';

// Webhooks from Social Media
Route::match(['get', 'post'], '/webhooks/{platform}', [WebhookController::class, 'handle'])->name('webhooks.handle');

// =========================================================================
// ALTERNATIF HOSTING TANPA TERMINAL (INFINITYFREE / CPANEL)
// =========================================================================
// Rute rahasia ini bisa Anda panggil menggunakan layanan gratis seperti
// cron-job.org atau fitur CronJob di cPanel setiap 1-5 menit.
// Contoh url: https://domain-infinityfree-anda.com/system/run-worker?key=vidflow_secret_123
// =========================================================================
Route::get('/system/run-worker', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== env('SYSTEM_SECRET_KEY', 'vidflow_secret_123')) {
        abort(403, 'Unauthorized Access');
    }

    // Matikan batasan waktu eksekusi PHP agar tidak timeout saat upload
    set_time_limit(0);
    ignore_user_abort(true); // Sangat krusial agar Cloudflare tidak membunuh proses di tengah jalan!

    // Jalankan antrian dan akan otomatis berhenti jika antrian sudah kosong
    \Illuminate\Support\Facades\Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--max-time' => 55, // Berhenti otomatis sebelum 1 menit agar tidak bertabrakan dengan cron berikutnya
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Worker successfully executed.',
        'output' => \Illuminate\Support\Facades\Artisan::output()
    ]);
});

Route::get('/system/reset-queue', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== env('SYSTEM_SECRET_KEY', 'vidflow_secret_123')) {
        abort(403, 'Unauthorized Access');
    }

    // Bersihkan semua antrean yang nyangkut di database
    \Illuminate\Support\Facades\DB::table('jobs')->truncate();

    // Ubah status yang nyangkut jadi failed agar tidak membingungkan
    \App\Models\PlatformUpload::whereIn('status', ['uploading', 'pending'])
        ->update([
            'status' => 'failed',
            'error_message' => 'Dibatalkan paksa oleh sistem reset.'
        ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Semua antrean yang nyangkut berhasil dibersihkan! Anda bisa mencoba upload ulang sekarang.'
    ]);
});

Route::get('/system/debug', function () {
    $latest = \App\Models\PlatformUpload::with('uploadJob')->latest('created_at')->take(5)->get();

    $results = $latest->map(function ($item) {
        return [
            'id' => $item->id,
            'created_at' => $item->created_at->format('Y-m-d H:i:s'),
            'platform' => $item->platform,
            'status' => $item->status,
            'error_message' => $item->error_message,
            'file_path_saved' => $item->uploadJob ? $item->uploadJob->file_path : 'NO JOB',
        ];
    });

    return response()->json([
        'waktu_sekarang' => now()->format('Y-m-d H:i:s'),
        '5_data_terakhir' => $results
    ]);
});

Route::get('/system/check-token', function () {
    $conn = \App\Models\PlatformConnection::where('platform', 'facebook')->latest('updated_at')->first();
    if (!$conn)
        return "Belum ada koneksi Facebook.";

    $token = \Illuminate\Support\Facades\Crypt::decryptString($conn->access_token);

    // 1. Cek token permissions
    $perms = \Illuminate\Support\Facades\Http::get('https://graph.facebook.com/v19.0/me/permissions', [
        'access_token' => $token
    ])->json();

    // 2. Cek akun secara langsung
    $pages = \Illuminate\Support\Facades\Http::get('https://graph.facebook.com/v19.0/me/accounts', [
        'access_token' => $token
    ])->json();

    return response()->json([
        'username_tersimpan' => $conn->platform_username,
        'token_permissions' => $perms,
        'pages_api_result' => $pages
    ]);
});

Route::get('/system/clear-cache', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== env('SYSTEM_SECRET_KEY', 'vidflow_secret_123')) {
        abort(403, 'Unauthorized Access');
    }

    \Illuminate\Support\Facades\Artisan::call('optimize:clear');

    return response()->json([
        'status' => 'success',
        'message' => 'Cache cleared successfully. New .env variables loaded!',
        'output' => \Illuminate\Support\Facades\Artisan::output()
    ]);
});

Route::get('/system/check-meta-api', function () {
    $uploads = \App\Models\PlatformUpload::with('connection')
        ->whereIn('platform', ['facebook', 'instagram'])
        ->whereNotNull('platform_video_id')
        ->orderByDesc('id')
        ->take(2)
        ->get();
        
    $results = [];
    foreach ($uploads as $upload) {
        $connection = $upload->connection;
        if (!$connection) continue;
        
        $accessToken = \Illuminate\Support\Facades\Crypt::decryptString($connection->access_token);
        
        $pagesResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)
            ->get('https://graph.facebook.com/v19.0/me/accounts');
        $pages = $pagesResponse->json();
        
        if (empty($pages['data'])) {
            $results[$upload->platform] = ['error' => 'No pages found', 'raw' => $pages];
            continue;
        }
        
        $pageToken = $pages['data'][0]['access_token'];
        
        if ($upload->platform === 'facebook') {
            $response = \Illuminate\Support\Facades\Http::withToken($pageToken)
                ->get("https://graph.facebook.com/v19.0/{$upload->platform_video_id}", ['fields' => 'likes.summary(true)']);
            $insights = \Illuminate\Support\Facades\Http::withToken($pageToken)
                ->get("https://graph.facebook.com/v19.0/{$upload->platform_video_id}/video_insights/total_video_views");
            $results['facebook'] = [
                'video_id' => $upload->platform_video_id,
                'likes_response' => $response->json(),
                'insights_response' => $insights->json()
            ];
        } else {
            $response = \Illuminate\Support\Facades\Http::withToken($pageToken)
                ->get("https://graph.facebook.com/v19.0/{$upload->platform_video_id}", ['fields' => 'like_count,comments_count']);
            $results['instagram'] = [
                'video_id' => $upload->platform_video_id,
                'ig_response' => $response->json()
            ];
        }
    }
    
    return response()->json($results);
});

Route::get('/system/errors', function () {
    return response()->json(
        \App\Models\PlatformUpload::where('status', 'failed')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get(['id', 'platform', 'error_message', 'created_at'])
    );
});

