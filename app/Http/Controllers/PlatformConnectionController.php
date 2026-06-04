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
        
        return inertia('Connections', [
            'connections' => $connections,
            'credentials' => $credentials
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

        // Check if user has saved credentials
        $credential = UserPlatformCredential::where('user_id', auth()->id())
            ->where('platform', $credentialPlatform)
            ->first();

        if ($credential) {
            Config::set("services.{$driverName}.client_id", $credential->app_id);
            Config::set("services.{$driverName}.client_secret", $credential->app_secret);
            Config::set("services.{$driverName}.redirect", route('platform.callback', ['platform' => $platform]));
        } else {
            // Force them to input it for ALL platforms
            abort(403, 'Anda harus mengatur App ID / Client ID dan Secret untuk ' . ucfirst($platform) . ' terlebih dahulu sebelum menghubungkan akun.');
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Socialite Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('connections')->with('error', 'Koneksi gagal: ' . $e->getMessage());
        }

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
