<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    use HasFactory;

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
        return collect(range(1, 12))
            ->mapWithKeys(fn (int $number) => ['X - '.$number => 'X - '.$number])
            ->all();
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
}
