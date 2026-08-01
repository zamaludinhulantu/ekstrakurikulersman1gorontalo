<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $cachedConfigPath = dirname(__DIR__).'/bootstrap/cache/config.php';
        if (is_file($cachedConfigPath)) {
            $cachedConfig = require $cachedConfigPath;
            $defaultConnection = data_get($cachedConfig, 'database.default');
            $sqliteDatabase = data_get($cachedConfig, 'database.connections.sqlite.database');

            if ($defaultConnection !== 'sqlite' || $sqliteDatabase !== ':memory:') {
                throw new \RuntimeException(
                    'Pengujian dihentikan: cache konfigurasi tidak menunjuk ke database SQLite in-memory. Jalankan php artisan config:clear.'
                );
            }
        }

        parent::setUp();

        $compiledViewsPath = base_path('storage/framework/testing/views');
        if (! is_dir($compiledViewsPath)) {
            mkdir($compiledViewsPath, 0777, true);
        }

        $this->withoutMiddleware([
            VerifyCsrfToken::class,
            ValidateCsrfToken::class,
        ]);
    }
}
