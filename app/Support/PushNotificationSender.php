<?php

namespace App\Support;

use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;

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

        $webPush = new PatchedWebPush($auth);
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

    public function diagnosticSummary(): array
    {
        return [
            'configured' => $this->configured(),
            'openssl_extension_loaded' => extension_loaded('openssl'),
            'openssl_conf' => (string) config('services.webpush.openssl_conf'),
            'openssl_conf_runtime' => getenv('OPENSSL_CONF') ?: '',
            'app_url' => (string) config('app.url'),
            'queue_connection' => (string) config('queue.default'),
        ];
    }

    private function configureOpenSsl(): void
    {
        $opensslConfigPath = (string) config('services.webpush.openssl_conf');

        if ($opensslConfigPath === '') {
            return;
        }

        putenv('OPENSSL_CONF='.$this->prepareOpenSslConfig($opensslConfigPath));
    }

    private function prepareOpenSslConfig(string $opensslConfigPath): string
    {
        if (! is_file($opensslConfigPath)) {
            return $opensslConfigPath;
        }

        $contents = @file_get_contents($opensslConfigPath);
        if (! is_string($contents) || $contents === '') {
            return $opensslConfigPath;
        }

        if (
            ! str_contains($contents, '[default_sect]')
            || preg_match('/^\[default_sect\](?:(?!^\[).)*^\s*activate\s*=\s*1\s*$/ms', $contents) === 1
        ) {
            return $opensslConfigPath;
        }

        $patched = preg_replace(
            '/^\[default_sect\]\R#\s*activate\s*=\s*1\s*$/m',
            "[default_sect]\nactivate = 1",
            $contents,
            1,
            $replacements
        );

        if (! is_string($patched)) {
            return $opensslConfigPath;
        }

        if (($replacements ?? 0) === 0) {
            $patched = preg_replace('/^\[default_sect\]\R/m', "[default_sect]\nactivate = 1\n", $contents, 1);
            if (! is_string($patched)) {
                return $opensslConfigPath;
            }
        }

        $runtimeDir = storage_path('app/runtime');
        if (! is_dir($runtimeDir) && ! @mkdir($runtimeDir, 0777, true) && ! is_dir($runtimeDir)) {
            return $opensslConfigPath;
        }

        $runtimePath = $runtimeDir.DIRECTORY_SEPARATOR.'openssl-webpush.cnf';
        if (@file_put_contents($runtimePath, $patched) === false) {
            return $opensslConfigPath;
        }

        return $runtimePath;
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
