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
    return '
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
        <p>We collect basic profile information (Name, Email) and OAuth access tokens required to publish videos to your connected social media channels.</p>
        <h2>2. Data Usage</h2>
        <p>Your OAuth tokens are securely encrypted in our database and are solely used for the purpose of uploading your videos as requested by you within the dashboard.</p>
        <h2>3. Data Deletion</h2>
        <p>You can revoke access and delete your data at any time by logging into your VidFlow dashboard and clicking the "Disconnect" button next to your connected platform. Upon disconnection, your access tokens are permanently deleted from our servers.</p>
        <h2>4. Contact Us</h2>
        <p>If you have any questions or concerns regarding your privacy or data, please contact the system administrator.</p>
    </body>
    </html>
    ';
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $connections = \App\Models\PlatformConnection::where('user_id', auth()->id())->get()->groupBy('platform');
        return Inertia::render('Dashboard', ['connections' => $connections]);
    })->name('dashboard');

    Route::get('/analytics', function () {
        $totalUploads = \App\Models\UploadJob::where('user_id', auth()->id())->count();
        $activeConnections = \App\Models\PlatformConnection::where('user_id', auth()->id())->count();

        return Inertia::render('Analytics', [
            'totalUploads' => $totalUploads,
            'activeConnections' => $activeConnections
        ]);
    })->name('analytics');

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

Route::get('/system/errors', function () {
    return response()->json(
        \App\Models\PlatformUpload::where('status', 'failed')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get(['id', 'platform', 'error_message', 'created_at'])
    );
});

