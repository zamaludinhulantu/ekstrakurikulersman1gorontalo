<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class SystemSetting extends Model
{
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
        if (! Schema::hasTable('system_settings')) {
            return $default;
        }

        $setting = static::query()->where('key', $key)->first();

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
        if (! Schema::hasTable('system_settings')) {
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
    }

    public static function valuesFor(array $keys): Collection
    {
        if (! Schema::hasTable('system_settings')) {
            return collect();
        }

        return static::query()
            ->whereIn('key', $keys)
            ->get()
            ->mapWithKeys(fn (SystemSetting $setting) => [
                $setting->key => $setting->is_encrypted && filled($setting->value)
                    ? rescue(fn () => Crypt::decryptString($setting->value), report: false)
                    : $setting->value,
            ]);
    }
}
