<?php

namespace App\Support;

use Base64Url\Base64Url;
use GuzzleHttp\Psr7\Request;
use Minishlink\WebPush\Notification;
use Minishlink\WebPush\WebPush;

class PatchedWebPush extends WebPush
{
    protected function prepare(array $notifications): array
    {
        $requests = [];

        foreach ($notifications as $notification) {
            \assert($notification instanceof Notification);
            $subscription = $notification->getSubscription();
            $endpoint = $subscription->getEndpoint();
            $userPublicKey = $subscription->getPublicKey();
            $userAuthToken = $subscription->getAuthToken();
            $contentEncoding = $subscription->getContentEncoding();
            $payload = $notification->getPayload();
            $options = $notification->getOptions($this->getDefaultOptions());
            $auth = $notification->getAuth($this->auth);

            if (! empty($payload) && ! empty($userPublicKey) && ! empty($userAuthToken)) {
                if (! $contentEncoding) {
                    throw new \ErrorException('Subscription should have a content encoding');
                }

                $encrypted = PatchedPushEncryption::encrypt($payload, $userPublicKey, $userAuthToken, $contentEncoding);
                $cipherText = $encrypted['cipherText'];
                $salt = $encrypted['salt'];
                $localPublicKey = $encrypted['localPublicKey'];

                $headers = [
                    'Content-Type' => 'application/octet-stream',
                    'Content-Encoding' => $contentEncoding,
                ];

                if ($contentEncoding === 'aesgcm') {
                    $headers['Encryption'] = 'salt='.Base64Url::encode($salt);
                    $headers['Crypto-Key'] = 'dh='.Base64Url::encode($localPublicKey);
                }

                $encryptionContentCodingHeader = PatchedPushEncryption::getContentCodingHeader($salt, $localPublicKey, $contentEncoding);
                $content = $encryptionContentCodingHeader.$cipherText;

                $headers['Content-Length'] = (string) \Minishlink\WebPush\Utils::safeStrlen($content);
            } else {
                $headers = [
                    'Content-Length' => '0',
                ];

                $content = '';
            }

            $headers['TTL'] = $options['TTL'];

            if (isset($options['urgency'])) {
                $headers['Urgency'] = $options['urgency'];
            }

            if (isset($options['topic'])) {
                $headers['Topic'] = $options['topic'];
            }

            if (array_key_exists('VAPID', $auth) && $contentEncoding) {
                $audience = parse_url($endpoint, PHP_URL_SCHEME).'://'.parse_url($endpoint, PHP_URL_HOST);
                if (! parse_url($audience)) {
                    throw new \ErrorException('Audience "'.$audience.'"" could not be generated.');
                }

                $vapidHeaders = $this->resolveVapidHeaders($audience, $contentEncoding, $auth['VAPID']);
                $headers['Authorization'] = $vapidHeaders['Authorization'];

                if ($contentEncoding === 'aesgcm') {
                    if (array_key_exists('Crypto-Key', $headers)) {
                        $headers['Crypto-Key'] .= ';'.$vapidHeaders['Crypto-Key'];
                    } else {
                        $headers['Crypto-Key'] = $vapidHeaders['Crypto-Key'];
                    }
                }
            }

            $requests[] = new Request('POST', $endpoint, $headers, $content);
        }

        return $requests;
    }

    private function resolveVapidHeaders(string $audience, string $contentEncoding, array $vapid): ?array
    {
        $method = new \ReflectionMethod(WebPush::class, 'getVAPIDHeaders');
        $parameter = $method->getParameters()[1] ?? null;
        $parameterType = $parameter?->getType();

        if (! $parameterType instanceof \ReflectionNamedType || $parameterType->isBuiltin()) {
            /** @var array<string, string>|null $headers */
            $headers = $method->invoke($this, $audience, $contentEncoding, $vapid);

            return $headers;
        }

        $typeName = $parameterType->getName();
        $resolvedEncoding = $this->resolveContentEncodingArgument($typeName, $contentEncoding);

        /** @var array<string, string>|null $headers */
        $headers = $method->invoke($this, $audience, $resolvedEncoding, $vapid);

        return $headers;
    }

    private function resolveContentEncodingArgument(string $typeName, string $contentEncoding): mixed
    {
        if (enum_exists($typeName) && method_exists($typeName, 'from')) {
            return $typeName::from($contentEncoding);
        }

        if (class_exists($typeName) && method_exists($typeName, 'from')) {
            return $typeName::from($contentEncoding);
        }

        return $contentEncoding;
    }
}
