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

// Halaman privasi sederhana untuk lolos verifikasi bot Facebook (Wajib HTTP 200 OK)
Route::get('/privacy', function () {
    return '<h1>Privacy Policy & Data Deletion</h1><p>To delete your data or disconnect your account, please login and use the Disconnect button in the dashboard, or contact the administrator.</p>';
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
    Route::post('/notifications/read', function() {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read');
    Route::get('/connections', [PlatformConnectionController::class, 'index'])->name('connections');

    // OAuth Platform Connections
    Route::post('/auth/{platform}/credentials', [PlatformConnectionController::class, 'saveCredentials'])->name('platform.credentials');
    Route::get('/auth/{platform}', [PlatformConnectionController::class, 'redirect'])->name('platform.redirect');
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

require __DIR__.'/auth.php';

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
    if (!$conn) return "Belum ada koneksi Facebook.";
    
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

