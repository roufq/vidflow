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
    return redirect()->route('login');
});

// Halaman privasi dan TOS untuk lolos verifikasi bot/manual Meta dan TikTok
Route::get('/privacy', function () {
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Terms of Service & Privacy Policy - VidFlow</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 2rem; }
            h1 { color: #111; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
            h2 { color: #444; margin-top: 2rem; }
            p { margin-bottom: 1rem; }
        </style>
    </head>
    <body>
        <h1>VidFlow Terms of Service</h1>
        <p><strong>Last Updated:</strong> June 2026</p>
        <p>Welcome to VidFlow. By using our service, you agree to these terms. VidFlow is a video management and scheduling dashboard designed to help users upload content to various social media platforms.</p>
        <h2>1. User Responsibilities</h2>
        <p>You must ensure that any video content you upload via VidFlow complies with the copyright laws and the community guidelines of the respective platforms (YouTube, Facebook, Instagram, TikTok).</p>
        <h2>2. API Services</h2>
        <p>Our application uses API services from YouTube, Meta, and TikTok. By authenticating your accounts, you grant us permission to publish videos on your behalf. We do not use your account for any other automated actions.</p>

        <h1 style="margin-top: 3rem;">VidFlow Privacy Policy</h1>
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
        <p>By using VidFlow, you also agree to be bound by the Terms of Service and Privacy Policies of the platforms you connect:</p>
        <ul>
            <li><a href="https://www.youtube.com/t/terms" target="_blank">YouTube Terms of Service</a> and <a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a></li>
            <li><a href="https://www.facebook.com/legal/terms" target="_blank">Meta/Facebook Terms of Service</a> and <a href="https://www.facebook.com/privacy/policy/" target="_blank">Privacy Policy</a></li>
            <li><a href="https://www.tiktok.com/legal/page/row/terms-of-service/en" target="_blank">TikTok Terms of Service</a> and <a href="https://www.tiktok.com/legal/page/row/privacy-policy/en" target="_blank">Privacy Policy</a></li>
        </ul>
        
        <h2>6. Contact Us</h2>
        <p>If you have any questions or concerns regarding your privacy, data usage, or wish to request manual data deletion, please contact our system administrator.</p>
    </body>
    </html>
HTML;
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $connections = \App\Models\PlatformConnection::where('user_id', auth()->id())->get()->groupBy('platform');
        return Inertia::render('Dashboard', ['connections' => $connections]);
    })->name('dashboard');

    Route::get('/analytics', function () {
        $totalUploads = \App\Models\UploadJob::where('user_id', auth()->id())->count();
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
            'totalUploads' => $totalUploads,
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
                if ($upload->platform === 'youtube') {
                    $response = \Illuminate\Support\Facades\Http::withToken($connection->access_token)
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
                    $pagesResponse = \Illuminate\Support\Facades\Http::withToken($connection->access_token)
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
                    $pagesResponse = \Illuminate\Support\Facades\Http::withToken($connection->access_token)
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
                    $response = \Illuminate\Support\Facades\Http::withToken($connection->access_token)
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
    })->name('analytics.sync');

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
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');

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
    if ($request->query('key') !== 'vidflow_secret_123') {
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
    if ($request->query('key') !== 'vidflow_secret_123') {
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
    if ($request->query('key') !== 'vidflow_secret_123') {
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
        
        $pagesResponse = \Illuminate\Support\Facades\Http::withToken($connection->access_token)
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

