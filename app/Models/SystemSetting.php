<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
    private const CACHE_KEY = 'system-settings.raw.v1';

    private static ?Collection $requestValues = null;

    private static bool $tableConfirmed = false;

    public $timestamps = true;

    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::allValues()->get($key);

        if (! $setting) {
            return $default;
        }

        if ($setting->is_encrypted && filled($setting->value)) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $setting->value ?? $default;
    }

    public static function setValue(string $key, mixed $value, bool $encrypted = false): void
    {
        if (! static::hasSettingsTable()) {
            return;
        }

        static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $encrypted && filled($value)
                    ? Crypt::encryptString((string) $value)
                    : ($value === null ? null : (string) $value),
                'is_encrypted' => $encrypted,
            ]
        );

        static::forgetCache();
    }

    public static function valuesFor(array $keys): Collection
    {
        return static::allValues()
            ->only($keys)
            ->mapWithKeys(fn (SystemSetting $setting) => [
                $setting->key => $setting->is_encrypted && filled($setting->value)
                    ? rescue(fn () => Crypt::decryptString($setting->value), report: false)
                    : $setting->value,
            ]);
    }

    private static function allValues(): Collection
    {
        if (static::$requestValues instanceof Collection) {
            return static::$requestValues;
        }

        if (! static::hasSettingsTable()) {
            return collect();
        }

        $rows = Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(5),
            fn (): array => static::query()
                ->get(['key', 'value', 'is_encrypted'])
                ->map(fn (SystemSetting $setting): array => $setting->attributesToArray())
                ->all()
        );

        return static::$requestValues = static::hydrate($rows)->keyBy('key');
    }

    private static function hasSettingsTable(): bool
    {
        if (static::$tableConfirmed) {
            return true;
        }

        return static::$tableConfirmed = Schema::hasTable('system_settings');
    }

    private static function forgetCache(): void
    {
        static::$requestValues = null;
        Cache::forget(self::CACHE_KEY);
    }
}
