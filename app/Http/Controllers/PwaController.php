<?php

namespace App\Http\Controllers;

use App\Models\Portal\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PwaController extends Controller
{
    /**
     * Subscribe to PWA Push Notifications.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $userId = Auth::id();
        $endpoint = $request->input('endpoint');
        $publicKey = $request->input('keys.p256dh');
        $authToken = $request->input('keys.auth');

        // Check if subscription already exists for this endpoint
        $subscription = PushSubscription::where('endpoint', $endpoint)->first();

        if ($subscription) {
            // Update the user ID and keys in case they changed
            $subscription->update([
                'user_id' => $userId,
                'public_key' => $publicKey,
                'auth_token' => $authToken,
            ]);
        } else {
            PushSubscription::create([
                'user_id' => $userId,
                'endpoint' => $endpoint,
                'public_key' => $publicKey,
                'auth_token' => $authToken,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'PWA Push Subscription registered successfully.',
        ]);
    }

    /**
     * Send a test push notification to the authenticated user's registered PWA devices.
     */
    public function sendTest(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active PWA push subscriptions found for this account. Make sure you install the PWA and allow notifications.',
            ], 404);
        }

        // Setup VAPID auth credentials
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:admin@fmikom.suntree.my.id',
                'publicKey' => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
            ],
        ];

        try {
            $webPush = new WebPush($auth);
            
            $payload = json_encode([
                'title' => 'Portal FMIKOM',
                'body' => 'Halo ' . $user->name . '! Ini adalah uji coba notifikasi latar belakang PWA Anda. 🎉',
                'icon' => '/asset/android-chrome-192x192.png',
                'badge' => '/asset/android-chrome-192x192.png',
                'vibrate' => [100, 50, 100],
                'url' => '/dashboard',
            ]);

            foreach ($subscriptions as $sub) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $sub->endpoint,
                        'publicKey' => $sub->public_key,
                        'authToken' => $sub->auth_token,
                    ]),
                    $payload
                );
            }

            // Flush queue and send all queued push messages
            $results = [];
            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getEndpoint();
                if ($report->isSuccess()) {
                    $results[] = "[Push Sent] Success for endpoint: " . substr($endpoint, 0, 30) . "...";
                } else {
                    $results[] = "[Push Failed] Reason: " . $report->getReason() . " for endpoint: " . substr($endpoint, 0, 30);
                    // Optionally clean up expired subscriptions
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint', $endpoint)->delete();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Test push notification queued.',
                'details' => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send push notification: ' . $e->getMessage(),
            ], 500);
        }
    }
}
