<?php

namespace App\Support;

use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class PushNotificationSender
{
    public function send(PushSubscription $subscription, array $payload): void
    {
        $this->configureOpenSsl();

        $auth = [
            'VAPID' => [
                'subject' => (string) config('services.webpush.subject'),
                'publicKey' => (string) config('services.webpush.public_key'),
                'privateKey' => (string) config('services.webpush.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);
        $webPush->setAutomaticPadding(true);

        $webPush->queueNotification(
            Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->public_key,
                'authToken' => $subscription->auth_token,
                'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
            ]),
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $subscription->forceFill(['last_used_at' => now()])->save();

                return;
            }

            $reason = $report->getReason();
            $statusCode = $this->extractStatusCode($reason);

            if (in_array($statusCode, [404, 410], true)) {
                $subscription->delete();

                return;
            }

            throw new \RuntimeException($reason ?: 'Pengiriman push notification gagal.');
        }
    }

    public function configured(): bool
    {
        return filled(config('services.webpush.subject'))
            && filled(config('services.webpush.public_key'))
            && filled(config('services.webpush.private_key'));
    }

    private function configureOpenSsl(): void
    {
        $opensslConfigPath = (string) config('services.webpush.openssl_conf');

        if ($opensslConfigPath === '') {
            return;
        }

        putenv('OPENSSL_CONF='.$opensslConfigPath);
    }

    private function extractStatusCode(?string $reason): ?int
    {
        if (! $reason) {
            return null;
        }

        if (preg_match('/\b(404|410)\b/', $reason, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }
}
