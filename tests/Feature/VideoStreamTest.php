<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UploadJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VideoStreamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('google');
        config(['filesystems.disks.google.driver' => 'local']); // Fake google driver as local for testing
    }

    public function test_stream_route_requires_valid_signature(): void
    {
        $user = User::factory()->create();
        
        $job = UploadJob::create([
            'user_id' => $user->id,
            'title' => 'Test Video',
            'description' => 'Description',
            'file_path' => 'transit_videos/test_video.mp4',
            'file_size_bytes' => 36,
            'status' => 'pending',
        ]);

        Storage::disk('local')->put('transit_videos/test_video.mp4', '0123456789abcdefghijklmnopqrstuvwxyz');

        // Access without signature
        $response = $this->get(route('video.stream', ['job' => $job->id]));
        $response->assertStatus(401);
    }

    public function test_stream_route_reads_from_local_disk_when_available(): void
    {
        $user = User::factory()->create();
        
        $job = UploadJob::create([
            'user_id' => $user->id,
            'title' => 'Test Video',
            'description' => 'Description',
            'file_path' => 'transit_videos/test_video.mp4',
            'file_size_bytes' => 36,
            'status' => 'pending',
        ]);

        $content = '0123456789abcdefghijklmnopqrstuvwxyz';
        Storage::disk('local')->put('transit_videos/test_video.mp4', $content);

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'video.stream',
            now()->addHours(1),
            ['job' => $job->id]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'video/mp4');
        $response->assertHeader('Content-Length', '36');
        $response->assertHeader('Accept-Ranges', 'bytes');
        
        // Assert output stream matches the full content
        ob_start();
        $response->sendContent();
        $output = ob_get_clean();
        
        $this->assertEquals($content, $output);
    }

    public function test_stream_route_falls_back_to_cloud_disk_when_deleted_from_local(): void
    {
        $user = User::factory()->create();
        
        $job = UploadJob::create([
            'user_id' => $user->id,
            'title' => 'Test Video',
            'description' => 'Description',
            'file_path' => 'transit_videos/test_video.mp4',
            'file_size_bytes' => 36,
            'status' => 'pending',
        ]);

        $content = '0123456789abcdefghijklmnopqrstuvwxyz';
        
        // Put file in the faked 'google' (cloud) disk, but NOT in 'local'
        Storage::disk('google')->put('transit_videos/test_video.mp4', $content);

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'video.stream',
            now()->addHours(1),
            ['job' => $job->id]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'video/mp4');
        $response->assertHeader('Content-Length', '36');
        
        ob_start();
        $response->sendContent();
        $output = ob_get_clean();
        
        $this->assertEquals($content, $output);
    }

    public function test_stream_route_returns_206_partial_content_with_range_header(): void
    {
        $user = User::factory()->create();
        
        $job = UploadJob::create([
            'user_id' => $user->id,
            'title' => 'Test Video',
            'description' => 'Description',
            'file_path' => 'transit_videos/test_video.mp4',
            'file_size_bytes' => 36,
            'status' => 'pending',
        ]);

        $content = '0123456789abcdefghijklmnopqrstuvwxyz'; // 36 bytes
        Storage::disk('local')->put('transit_videos/test_video.mp4', $content);

        // Generate signed URL
        $signedUrl = URL::temporarySignedRoute(
            'video.stream',
            now()->addHours(1),
            ['job' => $job->id]
        );

        // Request range 10 to 20 (11 bytes)
        $response = $this->get($signedUrl, [
            'Range' => 'bytes=10-20'
        ]);

        $response->assertStatus(206);
        $response->assertHeader('Content-Type', 'video/mp4');
        $response->assertHeader('Content-Length', '11');
        $response->assertHeader('Content-Range', 'bytes 10-20/36');

        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertEquals('abcdefghijk', $output);
    }

    public function test_stream_route_returns_416_on_invalid_range(): void
    {
        $user = User::factory()->create();
        
        $job = UploadJob::create([
            'user_id' => $user->id,
            'title' => 'Test Video',
            'description' => 'Description',
            'file_path' => 'transit_videos/test_video.mp4',
            'file_size_bytes' => 36,
            'status' => 'pending',
        ]);

        Storage::disk('local')->put('transit_videos/test_video.mp4', '0123456789abcdefghijklmnopqrstuvwxyz');

        $signedUrl = URL::temporarySignedRoute(
            'video.stream',
            now()->addHours(1),
            ['job' => $job->id]
        );

        // Request invalid range (out of bounds)
        $response = $this->get($signedUrl, [
            'Range' => 'bytes=40-50'
        ]);

        $response->assertStatus(416);
        $response->assertHeader('Content-Range', 'bytes */36');
    }
}
