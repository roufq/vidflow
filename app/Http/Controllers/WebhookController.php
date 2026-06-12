<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhooks from social media platforms.
     */
    public function handle(Request $request, $platform)
    {
        Log::info("Webhook received for {$platform}", $request->all());

        if ($platform === 'facebook' || $platform === 'instagram') {
            // Meta webhook verification (hub.challenge)
            if ($request->isMethod('get') && $request->query('hub_mode') === 'subscribe') {
                $challenge = $request->query('hub_challenge');
                $verifyToken = env('WEBHOOK_VERIFY_TOKEN', 'vidflow_secret_token');

                if ($request->query('hub_verify_token') === $verifyToken) {
                    return response($challenge, 200);
                }

                return response('Invalid Verify Token', 403);
            }

            // Handle Meta POST Webhook
            if ($request->isMethod('post')) {
                $payload = $request->all();

                // Get video ID to find the correct app secret for signature validation
                $videoId = null;
                if (isset($payload['entry'])) {
                    foreach ($payload['entry'] as $entry) {
                        if (isset($entry['changes'])) {
                            foreach ($entry['changes'] as $change) {
                                if ($change['field'] === 'videos' && isset($change['value']['video_id'])) {
                                    $videoId = $change['value']['video_id'];
                                    break 2;
                                }
                            }
                        }
                    }
                }

                if ($videoId) {
                    $upload = \App\Models\PlatformUpload::where('platform_video_id', $videoId)->first();
                    if ($upload) {
                        $credential = \App\Models\UserPlatformCredential::where('user_id', $upload->uploadJob->user_id)
                            ->where('platform', 'facebook')
                            ->first();

                        $appSecret = $credential ? $credential->app_secret : config('services.facebook.client_secret');

                        if ($appSecret) {
                            if (!$this->verifyMetaSignature($request, $appSecret)) {
                                Log::warning("Meta Webhook: Invalid signature for video {$videoId}");
                                return response('Invalid Signature', 403);
                            }
                        }
                    }
                }
                
                // Parse video status changes
                if (isset($payload['entry'])) {
                    foreach ($payload['entry'] as $entry) {
                        if (isset($entry['changes'])) {
                            foreach ($entry['changes'] as $change) {
                                if ($change['field'] === 'videos' && isset($change['value']['video_id'])) {
                                    $videoId = $change['value']['video_id'];
                                    $status = $change['value']['status']['video_status'] ?? null; // Usually 'published', 'error', etc.
                                    
                                    if ($status) {
                                        $upload = \App\Models\PlatformUpload::where('platform_video_id', $videoId)->first();
                                        if ($upload) {
                                            $newStatus = ($status === 'published') ? 'done' : (($status === 'error') ? 'failed' : 'uploading');
                                            
                                            if ($newStatus !== $upload->status) {
                                                $upload->update([
                                                    'status' => $newStatus,
                                                    'error_message' => ($newStatus === 'failed') ? 'Meta Webhook: Video processing failed' : null
                                                ]);

                                                // Trigger In-App Notification + Email
                                                if ($newStatus === 'done' || $newStatus === 'failed') {
                                                    $user = \App\Models\User::find($upload->uploadJob->user_id);
                                                    if ($user) {
                                                        $user->notify(new \App\Notifications\VideoStatusNotification($upload));
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                return response('EVENT_RECEIVED', 200);
            }
        }

        // Add other platforms like TikTok here
        if ($platform === 'tiktok') {
            // TikTok Webhook logic
            return response('EVENT_RECEIVED', 200);
        }

        return response('Platform not supported', 404);
    }

    /**
     * Verify the Meta Webhook signature.
     */
    private function verifyMetaSignature(Request $request, string $appSecret): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        if (!$signature) {
            return false;
        }

        $parts = explode('=', $signature);
        if (count($parts) !== 2 || $parts[0] !== 'sha256') {
            return false;
        }

        $expectedHeader = $parts[1];
        $payload = $request->getContent();
        $actualHeader = hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expectedHeader, $actualHeader);
    }
}
