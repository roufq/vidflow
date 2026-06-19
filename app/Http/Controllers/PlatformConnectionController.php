<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\PlatformConnection;
use App\Models\UserPlatformCredential;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;

class PlatformConnectionController extends Controller
{
    public function index()
    {
        // Use groupBy so each platform can have an array of multiple accounts
        $connections = PlatformConnection::where('user_id', auth()->id())->get()->groupBy('platform');
        $credentials = UserPlatformCredential::where('user_id', auth()->id())->get()->keyBy('platform');
        
        $hasGlobalCredentials = [
            'youtube' => false,
            'facebook' => false,
            'instagram' => false,
            'tiktok' => !empty(config('services.tiktok.client_id')) && !empty(config('services.tiktok.client_secret')),
        ];

        return inertia('Connections', [
            'connections' => $connections,
            'credentials' => $credentials,
            'hasGlobalCredentials' => $hasGlobalCredentials,
        ]);
    }

    public function saveCredentials(Request $request, $platform)
    {
        $request->validate([
            'app_id' => 'required|string',
            'app_secret' => 'required|string',
        ]);

        UserPlatformCredential::updateOrCreate(
            ['user_id' => auth()->id(), 'platform' => $platform],
            ['app_id' => $request->app_id, 'app_secret' => $request->app_secret]
        );

        return back()->with('success', 'Credentials untuk ' . ucfirst($platform) . ' berhasil disimpan.');
    }

    private function setDynamicConfig($platform)
    {
        // Instagram API uses Facebook's credentials behind the scenes
        $credentialPlatform = ($platform === 'instagram') ? 'facebook' : $platform;
        $driverName = $credentialPlatform;

        // Pengecualian spesial untuk TikTok: Gunakan Master Credentials dari .env
        // dan gunakan URI samaran agar terhindar dari pemblokiran kata "tiktok"
        if ($platform === 'tiktok') {
            $credential = UserPlatformCredential::where('user_id', auth()->id())
                ->where('platform', 'tiktok')
                ->first();

            if ($credential) {
                Config::set("services.tiktok.client_id", $credential->app_id);
                Config::set("services.tiktok.client_secret", $credential->app_secret);
            } elseif (empty(config('services.tiktok.client_id')) || empty(config('services.tiktok.client_secret'))) {
                abort(403, 'Demi alasan privasi dan perizinan API, Anda DIHARUSKAN mengatur App ID / Client ID dan Secret untuk TikTok Anda sendiri di menu pengaturan akun sebelum menghubungkan.');
            }
            Config::set("services.tiktok.redirect", "https://uploadvideo.my.id/auth/tt/callback");
            return $driverName;
        }

        // Check if user has saved credentials
        $credential = UserPlatformCredential::where('user_id', auth()->id())
            ->where('platform', $credentialPlatform)
            ->first();

        if ($credential) {
            Config::set("services.{$driverName}.client_id", $credential->app_id);
            Config::set("services.{$driverName}.client_secret", $credential->app_secret);
            Config::set("services.{$driverName}.redirect", route('platform.callback', ['platform' => $platform]));
        } else {
            abort(403, 'Demi alasan privasi dan perizinan API, Anda DIHARUSKAN mengatur App ID / Client ID dan Secret untuk ' . ucfirst($platform) . ' Anda sendiri di menu pengaturan akun sebelum menghubungkan.');
        }
        return $driverName;
    }

    public function redirect($platform)
    {
        $allowed = ['youtube', 'facebook', 'instagram', 'tiktok'];
        if (!in_array($platform, $allowed)) abort(404);
        
        $driverName = $this->setDynamicConfig($platform);
        $driver = Socialite::driver($driverName);
        
        if ($platform === 'youtube') {
            $driver->scopes(['https://www.googleapis.com/auth/youtube.upload', 'https://www.googleapis.com/auth/youtube.readonly']);
            $driver->with(['access_type' => 'offline', 'prompt' => 'consent']);
        } elseif ($platform === 'tiktok') {
            $driver->scopes(['video.upload', 'video.publish', 'user.info.basic', 'video.list']);
        } elseif ($platform === 'facebook' || $platform === 'instagram') {
            $driver->scopes([
                'pages_show_list', 
                'pages_read_engagement',
                'pages_manage_posts',       
                'instagram_basic',          
                'instagram_content_publish',
                'business_management'
            ])->with(['auth_type' => 'rerequest']);
            $driver->redirectUrl(route('platform.callback', ['platform' => $platform]));
        }
        
        return $driver->redirect();
    }

    public function callback($platform)
    {
        try {
            $driverName = $this->setDynamicConfig($platform);
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
                    'business_management'
                ])->with(['auth_type' => 'rerequest']);
            }
            
            $socialUser = $driver->user();

            PlatformConnection::updateOrCreate(
                [
                    'user_id' => auth()->id(), 
                    'platform' => $platform,
                    'platform_user_id' => $socialUser->getId() ?? 'unknown_'.uniqid()
                ],
                [
                    'platform_username' => $socialUser->getNickname() ?? $socialUser->getName() ?? 'Akun ' . ucfirst($platform),
                    'access_token' => Crypt::encryptString($socialUser->token),
                    'refresh_token' => $socialUser->refreshToken ? Crypt::encryptString($socialUser->refreshToken) : null,
                    'token_expires_at' => now()->addSeconds($socialUser->expiresIn ?? 3600),
                ]
            );

            return redirect()->route('connections')->with('success', 'Berhasil menghubungkan akun ' . ucfirst($platform));
            
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Socialite Error: ' . $e->getMessage(), ['exception' => $e]);
            $shortError = substr($e->getMessage(), 0, 150) . '...';
            return redirect()->route('connections')->with('error', 'Koneksi gagal: ' . $shortError);
        }
    }

    public function disconnect($id)
    {
        PlatformConnection::where('user_id', auth()->id())->where('id', $id)->delete();
        return back()->with('success', 'Koneksi akun diputus.');
    }
}
