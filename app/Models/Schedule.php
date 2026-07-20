<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'extracurricular_id',
        'coach_id',
        'schedule_type',
        'title',
        'activity_date',
        'start_time',
        'end_time',
        'location',
        'description',
        'status',
        'equipment',
        'instructions',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'cancelled_at' => 'datetime',
        ];
    }

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function talentTestParticipants(): HasMany
    {
        return $this->hasMany(TalentTestParticipant::class);
    }

    public function talentTestResults(): HasMany
    {
        return $this->hasMany(TalentTestResult::class);
    }

    public function isTalentTest(): bool
    {
        return $this->schedule_type === 'talent_test';
    }
}
