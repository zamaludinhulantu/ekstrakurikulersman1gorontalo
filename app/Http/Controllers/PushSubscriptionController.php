<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PushSubscriptionController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:2048'],
        ]);

        $subscriptions = PushSubscription::query()
            ->where('endpoint', $validated['endpoint'])
            ->orderByDesc('id')
            ->get();

        $subscription = $subscriptions->first();

        if (! $subscription) {
            return response()->json(['status' => 'not_linked']);
        }

        $ownedByCurrentUser = $subscriptions->firstWhere('user_id', $request->user()->id);
        if ($ownedByCurrentUser) {
            return response()->json([
                'status' => 'linked',
                'subscription_id' => $ownedByCurrentUser->id,
                'device_name' => $ownedByCurrentUser->device_name,
            ]);
        }

        return response()->json([
            'status' => 'linked_to_other_account',
            'requires_confirmation' => true,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:2048'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', Rule::in(['aesgcm', 'aes128gcm'])],
            'deviceName' => ['nullable', 'string', 'max:120'],
            'takeover' => ['nullable', 'boolean'],
        ]);

        $currentUser = $request->user();
        $takeover = (bool) ($validated['takeover'] ?? false);

        $subscription = DB::transaction(function () use ($validated, $request, $currentUser, $takeover) {
            $existing = PushSubscription::query()
                ->where('endpoint', $validated['endpoint'])
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get();

            $currentOwned = $existing->firstWhere('user_id', $currentUser->id);
            $foreignOwned = $existing->first(fn (PushSubscription $subscription) => $subscription->user_id !== $currentUser->id);

            if (! $currentOwned && $foreignOwned && ! $takeover) {
                throw new HttpResponseException(response()->json([
                    'status' => 'confirmation_required',
                    'message' => 'Perangkat ini masih terhubung ke akun lain. Konfirmasi diperlukan untuk memindahkan notifikasi ke akun yang sedang login.',
                    'requires_confirmation' => true,
                ], 409));
            }

            $subscription = $currentOwned ?? $foreignOwned ?? new PushSubscription();
            $subscription->fill([
                'user_id' => $currentUser->id,
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aes128gcm',
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'device_name' => $validated['deviceName'] ?? null,
                'last_used_at' => now(),
            ]);
            $subscription->save();

            PushSubscription::query()
                ->where('endpoint', $validated['endpoint'])
                ->whereKeyNot($subscription->id)
                ->delete();

            return $subscription;
        });

        return response()->json([
            'status' => 'subscribed',
            'subscription_id' => $subscription->id,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:2048'],
        ]);

        $request->user()->pushSubscriptions()
            ->where('endpoint', $validated['endpoint'])
            ->delete();

        return response()->json(['status' => 'unsubscribed']);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $deleted = $request->user()->pushSubscriptions()->delete();

        return response()->json([
            'status' => 'unsubscribed_all',
            'deleted' => $deleted,
        ]);
    }
}
