<?php

namespace App\Support;

use App\Jobs\SendPushNotificationJob;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\GenericAppNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NotificationCenter
{
    public function notifyUser(User $user, array $payload, bool $withPush = true): void
    {
        $preference = $this->preferenceFor($user);
        $category = (string) ($payload['category'] ?? 'general');

        if (($preference->mergedInAppPreferences()[$category] ?? true) === false) {
            return;
        }

        $notification = new GenericAppNotification($payload);
        $notification->id = (string) Str::uuid();
        $user->notify($notification);

        if (
            $withPush
            && ($preference->mergedPushPreferences()[$category] ?? false)
            && (
                $user->relationLoaded('pushSubscriptions')
                    ? $user->pushSubscriptions->isNotEmpty()
                    : $user->pushSubscriptions()->exists()
            )
        ) {
            SendPushNotificationJob::dispatchSync($user->id, $notification->id);
        }
    }

    public function notifyUsers(iterable $users, array $payload, bool $withPush = true): void
    {
        $collection = $users instanceof Collection ? $users : collect($users);

        $collection
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->each(fn (User $user) => $this->notifyUser($user, $payload, $withPush));
    }

    private function preferenceFor(User $user): NotificationPreference
    {
        return $user->notificationPreference
            ?? $user->notificationPreference()->create([
                'in_app_preferences' => NotificationPreference::defaultInAppPreferences(),
                'push_preferences' => NotificationPreference::defaultPushPreferences(),
                'email_preferences' => NotificationPreference::defaultEmailPreferences(),
            ]);
    }
}
