<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PlatformConnection;
use App\Models\UserPlatformCredential;
use App\Models\UploadJob;
use App\Models\PlatformUpload;
use App\Jobs\ProcessVideoUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class TikTokIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_tiktok_redirect_uses_user_custom_credentials_if_available(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Save custom credential
        UserPlatformCredential::create([
            'user_id' => $user->id,
            'platform' => 'tiktok',
            'app_id' => 'custom_client_key',
            'app_secret' => 'custom_client_secret',
        ]);

        $socialiteMock = \Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $socialiteMock->shouldReceive('scopes')->with(['video.upload', 'video.publish', 'user.info.basic', 'video.list'])->andReturnSelf();
        $socialiteMock->shouldReceive('redirect')->andReturn(redirect('https://tiktok.mock/auth'));

        Socialite::shouldReceive('driver')->with('tiktok')->andReturn($socialiteMock);

        $response = $this->get(route('platform.redirect', ['platform' => 'tiktok']));

        $response->assertRedirect('https://tiktok.mock/auth');

        $this->assertEquals('custom_client_key', config('services.tiktok.client_id'));
        $this->assertEquals('custom_client_secret', config('services.tiktok.client_secret'));
    }

    public function test_tiktok_redirect_falls_back_to_global_credentials_if_no_user_credential(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Set global configuration
        config([
            'services.tiktok.client_id' => 'global_client_key',
            'services.tiktok.client_secret' => 'global_client_secret',
        ]);

        $socialiteMock = \Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $socialiteMock->shouldReceive('scopes')->andReturnSelf();
        $socialiteMock->shouldReceive('redirect')->andReturn(redirect('https://tiktok.mock/auth'));

        Socialite::shouldReceive('driver')->with('tiktok')->andReturn($socialiteMock);

        $response = $this->get(route('platform.redirect', ['platform' => 'tiktok']));

        $response->assertRedirect('https://tiktok.mock/auth');

        $this->assertEquals('global_client_key', config('services.tiktok.client_id'));
        $this->assertEquals('global_client_secret', config('services.tiktok.client_secret'));
    }

    public function test_tiktok_redirect_aborts_if_no_credentials_at_all(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Clear global configurations
        config([
            'services.tiktok.client_id' => null,
            'services.tiktok.client_secret' => null,
        ]);

        $response = $this->get(route('platform.redirect', ['platform' => 'tiktok']));

        $response->assertStatus(403);
        $response->assertSee('Anda DIHARUSKAN mengatur App ID / Client ID dan Secret untuk TikTok');
    }

    public function test_tiktok_token_refresh_during_job_processing(): void
    {
        $user = User::factory()->create();
        
        $connection = PlatformConnection::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'platform' => 'tiktok',
            'access_token' => Crypt::encryptString('old_access_token'),
            'refresh_token' => Crypt::encryptString('valid_refresh_token'),
            'token_expires_at' => now()->subMinutes(1), // Already expired
            'platform_user_id' => 'tiktok_user_123',
            'platform_username' => 'TikTok User',
        ]);

        $job = UploadJob::create([
            'user_id' => $user->id,
            'title' => 'My TikTok Video',
            'description' => 'Cool video description',
            'file_path' => 'transit_videos/test_video.mp4',
            'file_size_bytes' => 1000,
            'status' => 'pending',
        ]);

        $platformUpload = PlatformUpload::create([
            'job_id' => $job->id,
            'platform' => 'tiktok',
            'connection_id' => $connection->id,
            'status' => 'pending',
        ]);

        Storage::disk('local')->put('transit_videos/test_video.mp4', 'dummy_video_data');

        config([
            'services.tiktok.client_id' => 'global_client_key',
            'services.tiktok.client_secret' => 'global_client_secret',
        ]);

        Http::fake([
            'https://open.tiktokapis.com/v2/oauth/token/' => Http::response([
                'access_token' => 'new_access_token',
                'refresh_token' => 'new_refresh_token',
                'expires_in' => 86400,
            ], 200),
            'https://open.tiktokapis.com/v2/post/publish/video/init/' => Http::response([
                'data' => [
                    'upload_url' => 'https://open-upload.tiktokapis.com/video/?upload_id=123',
                    'publish_id' => 'publish_session_456',
                ]
            ], 200),
            'https://open-upload.tiktokapis.com/video/*' => Http::response('', 200),
            'https://open.tiktokapis.com/v2/post/publish/status/fetch/' => Http::response([
                'data' => [
                    'status' => 'PUBLISH_COMPLETE',
                    'publicaly_available_post_id' => ['real_video_id_999'],
                ]
            ], 200),
        ]);

        $processJob = new ProcessVideoUpload($platformUpload);
        $processJob->handle();

        $connection->refresh();
        $this->assertEquals('new_access_token', Crypt::decryptString($connection->access_token));
        $this->assertEquals('new_refresh_token', Crypt::decryptString($connection->refresh_token));
        $this->assertTrue($connection->token_expires_at->isFuture());

        $platformUpload->refresh();
        $this->assertEquals('done', $platformUpload->status);
        $this->assertEquals('real_video_id_999', $platformUpload->platform_video_id);
        $this->assertEquals('https://tiktok.com/@me/video/real_video_id_999', $platformUpload->platform_url);
    }

    public function test_tiktok_upload_polls_and_releases_if_still_processing(): void
    {
        $user = User::factory()->create();
        
        $connection = PlatformConnection::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'platform' => 'tiktok',
            'access_token' => Crypt::encryptString('access_token'),
            'token_expires_at' => now()->addHours(1),
            'platform_user_id' => 'tiktok_user_123',
            'platform_username' => 'TikTok User',
        ]);

        $job = UploadJob::create([
            'user_id' => $user->id,
            'title' => 'My TikTok Video',
            'description' => 'Cool video description',
            'file_path' => 'transit_videos/test_video.mp4',
            'file_size_bytes' => 1000,
            'status' => 'pending',
        ]);

        $platformUpload = PlatformUpload::create([
            'job_id' => $job->id,
            'platform' => 'tiktok',
            'connection_id' => $connection->id,
            'status' => 'pending',
        ]);

        Storage::disk('local')->put('transit_videos/test_video.mp4', 'dummy_video_data');

        config([
            'services.tiktok.client_id' => 'global_client_key',
            'services.tiktok.client_secret' => 'global_client_secret',
        ]);

        Http::fake([
            'https://open.tiktokapis.com/v2/post/publish/video/init/' => Http::response([
                'data' => [
                    'upload_url' => 'https://open-upload.tiktokapis.com/video/?upload_id=123',
                    'publish_id' => 'publish_session_456',
                ]
            ], 200),
            'https://open-upload.tiktokapis.com/video/*' => Http::response('', 200),
            'https://open.tiktokapis.com/v2/post/publish/status/fetch/' => Http::response([
                'data' => [
                    'status' => 'PROCESSING_UPLOAD', // Not finished
                ]
            ], 200),
        ]);

        $processJob = \Mockery::mock(ProcessVideoUpload::class, [$platformUpload])->makePartial();
        $processJob->shouldReceive('release')->once()->with(30);

        $processJob->handle();

        $platformUpload->refresh();
        $this->assertEquals('publish_session_456', $platformUpload->platform_video_id);
        // It should NOT be marked as done yet
        $this->assertEquals('uploading', $platformUpload->status);
    }
}
