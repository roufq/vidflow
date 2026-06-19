<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $upload = Illuminate\Support\Facades\DB::table('platform_uploads')->where('platform', 'instagram')->latest('id')->first();
    $connection = Illuminate\Support\Facades\DB::table('platform_connections')->where('id', $upload->connection_id)->first();
    $accessToken = Illuminate\Support\Facades\Crypt::decryptString($connection->access_token);

    $pagesResponse = \Illuminate\Support\Facades\Http::withToken($accessToken)->get('https://graph.facebook.com/v19.0/me/accounts');
    $pages = $pagesResponse->json('data');
    $pageToken = $pages[0]['access_token'];

    $response = \Illuminate\Support\Facades\Http::withToken($pageToken)->get("https://graph.facebook.com/v19.0/{$upload->platform_video_id}?fields=like_count,comments_count,views,plays");

    $insights = \Illuminate\Support\Facades\Http::withToken($pageToken)->get("https://graph.facebook.com/v19.0/{$upload->platform_video_id}/insights?metric=plays,total_views,ig_reels_video_view_total_time,ig_reels_aggregated_all_plays_count,video_views");

    echo "Media Response:\n" . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n\n";
    echo "Insights Response:\n" . json_encode($insights->json(), JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
