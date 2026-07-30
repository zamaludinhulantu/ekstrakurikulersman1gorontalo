<?php

namespace App\Support;

use Base64Url\Base64Url;
use Brick\Math\BigInteger;
use Jose\Component\Core\JWK;
use Minishlink\WebPush\Utils;

class PatchedPushEncryption
{
    public static function encrypt(string $payload, string $userPublicKey, string $userAuthToken, string $contentEncoding): array
    {
        return self::deterministicEncrypt(
            $payload,
            $userPublicKey,
            $userAuthToken,
            $contentEncoding,
            self::createLocalKeyObject(),
            random_bytes(16)
        );
    }

    public static function deterministicEncrypt(
        string $payload,
        string $userPublicKey,
        string $userAuthToken,
        string $contentEncoding,
        array $localKeyObject,
        string $salt
    ): array {
        $userPublicKey = Base64Url::decode($userPublicKey);
        $userAuthToken = Base64Url::decode($userAuthToken);

        ['private_scalar' => $privateScalar, 'public_x' => $localPublicX, 'public_y' => $localPublicY] = $localKeyObject;
        $localJwk = new JWK([
            'kty' => 'EC',
            'crv' => 'P-256',
            'd' => Base64Url::encode($privateScalar),
            'x' => Base64Url::encode($localPublicX),
            'y' => Base64Url::encode($localPublicY),
        ]);
        $localPublicKey = hex2bin(Utils::serializePublicKeyFromJWK($localJwk));

        if (! $localPublicKey) {
            throw new \RuntimeException('Failed to convert local public key from hexadecimal to binary.');
        }

        [$userPublicKeyObjectX, $userPublicKeyObjectY] = Utils::unserializePublicKey($userPublicKey);
        $userJwk = new JWK([
            'kty' => 'EC',
            'crv' => 'P-256',
            'x' => Base64Url::encode($userPublicKeyObjectX),
            'y' => Base64Url::encode($userPublicKeyObjectY),
        ]);

        $sharedSecret = self::calculateAgreementKey($privateScalar, $userJwk);
        $sharedSecret = str_pad($sharedSecret, 32, chr(0), STR_PAD_LEFT);

        $ikm = self::getIKM($userAuthToken, $userPublicKey, $localPublicKey, $sharedSecret, $contentEncoding);
        $context = self::createContext($userPublicKey, $localPublicKey, $contentEncoding);

        $contentEncryptionKeyInfo = self::createInfo($contentEncoding, $context, $contentEncoding);
        $contentEncryptionKey = self::hkdf($salt, $ikm, $contentEncryptionKeyInfo, 16);

        $nonceInfo = self::createInfo('nonce', $context, $contentEncoding);
        $nonce = self::hkdf($salt, $ikm, $nonceInfo, 12);

        $tag = '';
        $encryptedText = openssl_encrypt($payload, 'aes-128-gcm', $contentEncryptionKey, OPENSSL_RAW_DATA, $nonce, $tag);

        return [
            'localPublicKey' => $localPublicKey,
            'salt' => $salt,
            'cipherText' => $encryptedText.$tag,
        ];
    }

    public static function getContentCodingHeader(string $salt, string $localPublicKey, string $contentEncoding): string
    {
        if ($contentEncoding === 'aes128gcm') {
            return $salt
                . pack('N*', 4096)
                . pack('C*', Utils::safeStrlen($localPublicKey))
                . $localPublicKey;
        }

        return '';
    }

    private static function createLocalKeyObject(): array
    {
        $privateScalarInt = self::generatePrivateScalar();
        $publicPoint = self::scalarMultiply(self::generatorPoint(), $privateScalarInt);

        return [
            'private_scalar' => self::padCoordinate($privateScalarInt),
            'public_x' => self::padCoordinate($publicPoint['x']),
            'public_y' => self::padCoordinate($publicPoint['y']),
        ];
    }

    private static function hkdf(string $salt, string $ikm, string $info, int $length): string
    {
        $prk = hash_hmac('sha256', $ikm, $salt, true);

        return mb_substr(hash_hmac('sha256', $info.chr(1), $prk, true), 0, $length, '8bit');
    }

    private static function createContext(string $clientPublicKey, string $serverPublicKey, string $contentEncoding): ?string
    {
        if ($contentEncoding === 'aes128gcm') {
            return null;
        }

        if (Utils::safeStrlen($clientPublicKey) !== 65) {
            throw new \ErrorException('Invalid client public key length');
        }

        if (Utils::safeStrlen($serverPublicKey) !== 65) {
            throw new \ErrorException('Invalid server public key length');
        }

        $len = chr(0).'A';

        return chr(0).$len.$clientPublicKey.$len.$serverPublicKey;
    }

    private static function createInfo(string $type, ?string $context, string $contentEncoding): string
    {
        if ($contentEncoding === 'aesgcm') {
            if (! $context) {
                throw new \ErrorException('Context must exist');
            }

            if (Utils::safeStrlen($context) !== 135) {
                throw new \ErrorException('Context argument has invalid size');
            }

            return 'Content-Encoding: '.$type.chr(0).'P-256'.$context;
        }

        if ($contentEncoding === 'aes128gcm') {
            return 'Content-Encoding: '.$type.chr(0);
        }

        throw new \ErrorException('This content encoding is not supported.');
    }

    private static function getIKM(
        string $userAuthToken,
        string $userPublicKey,
        string $localPublicKey,
        string $sharedSecret,
        string $contentEncoding
    ): string {
        if (empty($userAuthToken)) {
            return $sharedSecret;
        }

        if ($contentEncoding === 'aesgcm') {
            $info = 'Content-Encoding: auth'.chr(0);
        } elseif ($contentEncoding === 'aes128gcm') {
            $info = 'WebPush: info'.chr(0).$userPublicKey.$localPublicKey;
        } else {
            throw new \ValueError('This content encoding is not supported.');
        }

        return self::hkdf($userAuthToken, $sharedSecret, $info, 32);
    }

    private static function calculateAgreementKey(string $privateScalar, JWK $publicKey): string
    {
        $x = $publicKey->get('x');
        $y = $publicKey->get('y');
        if (! is_string($x) || ! is_string($y)) {
            throw new \RuntimeException('Unable to compute the agreement key.');
        }

        $publicPoint = [
            'x' => self::bytesToBigInteger(Base64Url::decode($x)),
            'y' => self::bytesToBigInteger(Base64Url::decode($y)),
            'infinity' => false,
        ];

        $sharedPoint = self::scalarMultiply($publicPoint, self::bytesToBigInteger($privateScalar));
        if ($sharedPoint['infinity']) {
            throw new \RuntimeException('Unable to compute the agreement key.');
        }

        return self::padCoordinate($sharedPoint['x']);
    }

    private static function padCoordinate(BigInteger $coordinate): string
    {
        return str_pad($coordinate->toBytes(false), 32, chr(0), STR_PAD_LEFT);
    }

    private static function generatePrivateScalar(): BigInteger
    {
        $orderMinusOne = self::curveOrder()->minus(BigInteger::one());

        do {
            $candidate = self::bytesToBigInteger(random_bytes(32));
            $scalar = $candidate->mod($orderMinusOne)->plus(BigInteger::one());
        } while ($scalar->isEqualTo(BigInteger::zero()));

        return $scalar;
    }

    private static function scalarMultiply(array $point, BigInteger $scalar): array
    {
        $result = self::pointAtInfinity();
        $addend = $point;
        $k = $scalar;

        while ($k->compareTo(BigInteger::zero()) > 0) {
            if ($k->mod(BigInteger::of(2))->isEqualTo(BigInteger::one())) {
                $result = self::pointAdd($result, $addend);
            }

            $addend = self::pointDouble($addend);
            $k = $k->quotient(BigInteger::of(2));
        }

        return $result;
    }

    private static function pointAdd(array $p, array $q): array
    {
        if ($p['infinity']) {
            return $q;
        }

        if ($q['infinity']) {
            return $p;
        }

        $prime = self::curvePrime();

        if ($p['x']->isEqualTo($q['x'])) {
            if ($p['y']->plus($q['y'])->mod($prime)->isEqualTo(BigInteger::zero())) {
                return self::pointAtInfinity();
            }

            return self::pointDouble($p);
        }

        $lambda = self::modDiv($q['y']->minus($p['y']), $q['x']->minus($p['x']), $prime);
        $x = $lambda->power(2)->minus($p['x'])->minus($q['x'])->mod($prime);
        $y = $lambda->multipliedBy($p['x']->minus($x))->minus($p['y'])->mod($prime);

        return ['x' => $x, 'y' => $y, 'infinity' => false];
    }

    private static function pointDouble(array $p): array
    {
        if ($p['infinity']) {
            return $p;
        }

        $prime = self::curvePrime();
        if ($p['y']->isEqualTo(BigInteger::zero())) {
            return self::pointAtInfinity();
        }

        $three = BigInteger::of(3);
        $two = BigInteger::of(2);
        $lambda = self::modDiv(
            $three->multipliedBy($p['x']->power(2))->plus(self::curveA()),
            $two->multipliedBy($p['y']),
            $prime
        );
        $x = $lambda->power(2)->minus($two->multipliedBy($p['x']))->mod($prime);
        $y = $lambda->multipliedBy($p['x']->minus($x))->minus($p['y'])->mod($prime);

        return ['x' => $x, 'y' => $y, 'infinity' => false];
    }

    private static function modDiv(BigInteger $numerator, BigInteger $denominator, BigInteger $modulus): BigInteger
    {
        return $numerator->multipliedBy($denominator->modInverse($modulus))->mod($modulus);
    }

    private static function pointAtInfinity(): array
    {
        return [
            'x' => BigInteger::zero(),
            'y' => BigInteger::zero(),
            'infinity' => true,
        ];
    }

    private static function generatorPoint(): array
    {
        return [
            'x' => BigInteger::fromBase('6b17d1f2e12c4247f8bce6e563a440f277037d812deb33a0f4a13945d898c296', 16),
            'y' => BigInteger::fromBase('4fe342e2fe1a7f9b8ee7eb4a7c0f9e162bce33576b315ececbb6406837bf51f5', 16),
            'infinity' => false,
        ];
    }

    private static function curvePrime(): BigInteger
    {
        return BigInteger::fromBase('ffffffff00000001000000000000000000000000ffffffffffffffffffffffff', 16);
    }

    private static function curveA(): BigInteger
    {
        return BigInteger::fromBase('ffffffff00000001000000000000000000000000fffffffffffffffffffffffc', 16);
    }

    private static function curveOrder(): BigInteger
    {
        return BigInteger::fromBase('ffffffff00000000ffffffffffffffffbce6faada7179e84f3b9cac2fc632551', 16);
    }

    private static function bytesToBigInteger(string $bytes): BigInteger
    {
        $hex = bin2hex($bytes);

        return $hex === '' ? BigInteger::zero() : BigInteger::fromBase($hex, 16);
    }
}
