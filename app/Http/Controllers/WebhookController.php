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

            // Handle Meta POST Webhook (e.g., video processed, permissions revoked)
            if ($request->isMethod('post')) {
                // Here we would parse $request->all() to find specific events
                // e.g., if a user revoked access, we disconnect their PlatformConnection
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
}
