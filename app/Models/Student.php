<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Student extends Model
{
    use HasFactory;

    public const MIN_REGISTRATION_AGE = 8;
    public const MAX_ACTIVE_REGISTRATIONS = 3;
    private const CUSTOM_CLASS_OPTIONS_SETTING = 'student_class_options';

    protected $fillable = [
        'user_id',
        'nis',
        'class_name',
        'gender',
        'date_of_birth',
        'address',
        'parent_name',
        'parent_phone',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function extracurriculars(): BelongsToMany
    {
        return $this->belongsToMany(Extracurricular::class, 'registrations')
            ->withPivot(['status', 'registration_date', 'notes', 'verified_by', 'verified_at'])
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function talentTestParticipants(): HasMany
    {
        return $this->hasMany(TalentTestParticipant::class);
    }

    public function talentTestResults(): HasMany
    {
        return $this->hasMany(TalentTestResult::class);
    }

    public static function registrationClassOptions(): array
    {
        return collect(static::defaultRegistrationClassOptions())
            ->merge(static::customRegistrationClassOptions())
            ->mapWithKeys(function (string $label): array {
                $normalized = static::normalizeClassName($label);

                return $normalized ? [$normalized => $normalized] : [];
            })
            ->sortKeysUsing('strnatcasecmp')
            ->all();
    }

    public static function defaultRegistrationClassOptions(): array
    {
        return collect(range(1, 12))
            ->map(fn (int $number) => 'X - '.$number)
            ->all();
    }

    public static function customRegistrationClassOptions(): array
    {
        $stored = SystemSetting::getValue(self::CUSTOM_CLASS_OPTIONS_SETTING, []);

        if (is_string($stored)) {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($stored)) {
            return [];
        }

        return collect($stored)
            ->map(fn ($label) => static::normalizeClassName(is_scalar($label) ? (string) $label : null))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public static function addCustomRegistrationClassOption(?string $value): ?string
    {
        $normalized = static::normalizeClassName($value);

        if (! $normalized) {
            return null;
        }

        $options = collect(static::defaultRegistrationClassOptions())
            ->merge(static::customRegistrationClassOptions())
            ->map(fn ($label) => static::normalizeClassName($label))
            ->filter()
            ->unique()
            ->values();

        if (! $options->contains($normalized)) {
            $options->push($normalized);
        }

        SystemSetting::setValue(
            self::CUSTOM_CLASS_OPTIONS_SETTING,
            json_encode($options->sort()->values()->all(), JSON_UNESCAPED_UNICODE)
        );

        return $normalized;
    }

    public static function normalizeClassName(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = Str::of($value)
            ->trim()
            ->upper()
            ->replaceMatches('/[.,:\/_-]+/u', ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->toString();

        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^10\s*(\d{1,2})(?:\s+[A-Z]{1,10})?$/', $normalized, $matches) === 1) {
            return 'X - '.$matches[1];
        }

        if (preg_match('/^(XII|XI|X)\s*(IPA|IPS|BAHASA|TP)\s*(\d{1,2})$/', $normalized, $matches) === 1) {
            return 'X - '.$matches[3];
        }

        if (preg_match('/^(XII|XI|X)\s*(\d{1,2})(?:\s+[A-Z]{1,10})?$/', $normalized, $matches) === 1) {
            return 'X - '.$matches[2];
        }

        if (preg_match('/^(XII|XI|X)(\d{1,2})$/', $normalized, $matches) === 1) {
            return 'X - '.$matches[2];
        }

        if ($normalized === '10') {
            return 'X';
        }

        return $normalized;
    }

    public static function normalizedClassExpression(string $column = 'class_name'): string
    {
        return "UPPER(TRIM("
            ."REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE("
            ."COALESCE({$column}, ''), '.', ' '), ',', ' '), '-', ' '), '/', ' '), '_', ' '), ':', ' '), '  ', ' '), '  ', ' ')"
            ."))";
    }

    public static function normalizedClassComparable(?string $value): ?string
    {
        $normalized = static::normalizeClassName($value);

        if ($normalized === null) {
            return null;
        }

        return Str::of($normalized)
            ->upper()
            ->replace('-', ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->toString();
    }

    public static function latestAllowedRegistrationBirthDate(): string
    {
        return Carbon::today()->subYears(static::MIN_REGISTRATION_AGE)->format('Y-m-d');
    }

    public function activeRegistrationCount(): int
    {
        $registrations = $this->relationLoaded('registrations')
            ? $this->registrations
            : $this->registrations()->get(['id', 'status']);

        return $registrations
            ->filter(fn (Registration $registration) => ! in_array($registration->status, [
                Registration::STATUS_REJECTED,
                Registration::STATUS_CANCELLED,
            ], true))
            ->count();
    }

    public function hasReachedRegistrationLimit(): bool
    {
        return $this->activeRegistrationCount() >= static::MAX_ACTIVE_REGISTRATIONS;
    }

    public function hasLegacyRegistrationOverflow(): bool
    {
        return $this->activeRegistrationCount() > static::MAX_ACTIVE_REGISTRATIONS;
    }

    public function hasCompleteProfile(): bool
    {
        return filled($this->nis)
            && filled($this->class_name)
            && filled($this->gender)
            && filled($this->user?->name)
            && filled($this->user?->email);
    }

    public function registrationLimitReachedMessage(): string
    {
        return 'Anda sudah terdaftar pada '.static::MAX_ACTIVE_REGISTRATIONS.' ekstrakurikuler. Jika ingin mendaftar ekstrakurikuler lain, batalkan salah satu pendaftaran terlebih dahulu.';
    }

    public function registrationLegacyOverflowMessage(): string
    {
        return 'Data pendaftaran siswa ini melebihi batas maksimal '.static::MAX_ACTIVE_REGISTRATIONS.' ekstrakurikuler. Data lama tetap disimpan, tetapi pendaftaran baru tidak dapat ditambahkan.';
    }
}
