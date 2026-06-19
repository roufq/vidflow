<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class DebugIgSync extends Command
{
    protected $signature = 'debug:ig-sync';
    protected $description = 'Debug IG API Sync';

    public function handle()
    {
        $upload = DB::table('platform_uploads')->where('platform', 'instagram')->latest('id')->first();
        if (!$upload) {
            $this->error("No IG upload found.");
            return;
        }

        $connection = DB::table('platform_connections')->where('id', $upload->connection_id)->first();
        $accessToken = Crypt::decryptString($connection->access_token);

        $pagesResponse = Http::withToken($accessToken)->get('https://graph.facebook.com/v19.0/me/accounts');
        $pages = $pagesResponse->json('data');
        if (empty($pages)) {
            $this->error("No pages found");
            return;
        }

        $pageToken = $pages[0]['access_token'];

        $this->info("Fetching metrics for video: " . $upload->platform_video_id);

        $response1 = Http::withToken($pageToken)->get("https://graph.facebook.com/v19.0/{$upload->platform_video_id}?fields=like_count,comments_count,views,plays,video_views,ig_reels_video_view_total_time");
        
        file_put_contents('ig_debug.json', json_encode([
            'media_object_direct' => $response1->json(),
        ], JSON_PRETTY_PRINT));
        
        $this->info("Done! Check ig_debug.json");
    }
}
