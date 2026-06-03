<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\PlatformConnection;
use Illuminate\Support\Facades\Crypt;

class PlatformConnectionController extends Controller
{
    public function index()
    {
        // Use groupBy so each platform can have an array of multiple accounts
        $connections = PlatformConnection::where('user_id', auth()->id())->get()->groupBy('platform');
        return inertia('Connections', ['connections' => $connections]);
    }

    public function redirect($platform)
    {
        $allowed = ['youtube', 'facebook', 'instagram', 'tiktok'];
        if (!in_array($platform, $allowed)) abort(404);
        
        // Instagram API modern (Graph API) menggunakan jalur Facebook Login
        $driverName = ($platform === 'instagram') ? 'facebook' : $platform;
        $driver = Socialite::driver($driverName);
        
        if ($platform === 'youtube') {
            $driver->scopes(['https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube.readonly']);
            $driver->with(['access_type' => 'offline', 'prompt' => 'consent']);
        } elseif ($platform === 'tiktok') {
            $driver->scopes(['video.upload', 'video.publish', 'user.info.basic']);
        } elseif ($platform === 'facebook' || $platform === 'instagram') {
            $driver->scopes([
                'pages_show_list', 
                'pages_read_engagement',
                'pages_manage_posts',       
                'instagram_basic',          
                'instagram_content_publish',
                'business_management' // WAJIB DITAMBAHKAN KARENA META BUSINESS SUITE
            ])->with(['auth_type' => 'rerequest']); // PAKSA FACEBOOK MINTA IZIN BARU
            // Timpa redirect URL agar kembali ke platform yang benar (IG atau FB)
            $driver->redirectUrl(route('platform.callback', ['platform' => $platform]));
        }
        
        return $driver->redirect();
    }

    public function callback($platform)
    {
        try {
            $driverName = ($platform === 'instagram') ? 'facebook' : $platform;
            
            $driver = Socialite::driver($driverName);
            if ($platform === 'facebook' || $platform === 'instagram') {
                $driver->redirectUrl(route('platform.callback', ['platform' => $platform]));
            }
            
            $socialUser = $driver->user();
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Koneksi gagal: ' . $e->getMessage());
        }

        // Update based on platform_user_id to allow multiple accounts per platform!
        PlatformConnection::updateOrCreate(
            [
                'user_id' => auth()->id(), 
                'platform' => $platform,
                'platform_user_id' => $socialUser->getId()
            ],
            [
                'platform_username' => $socialUser->getNickname() ?? $socialUser->getName(),
                'access_token' => Crypt::encryptString($socialUser->token),
                'refresh_token' => $socialUser->refreshToken ? Crypt::encryptString($socialUser->refreshToken) : null,
                'token_expires_at' => now()->addSeconds($socialUser->expiresIn ?? 3600),
            ]
        );

        return redirect()->route('connections')->with('success', 'Berhasil menghubungkan akun ' . ucfirst($platform));
    }

    public function disconnect($id)
    {
        PlatformConnection::where('user_id', auth()->id())->where('id', $id)->delete();
        return back()->with('success', 'Koneksi akun diputus.');
    }
}
