<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Extracurricular extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'name',
        'description',
        'requirements',
        'schedule_overview',
        'achievements_overview',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(Coach::class, 'extracurricular_coach', 'extracurricular_id', 'coach_id')
            ->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(ExtracurricularAchievement::class)->orderByDesc('achievement_date')->orderByDesc('id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function getCoachNamesAttribute(): string
    {
        $coaches = $this->relationLoaded('coaches')
            ? $this->coaches
            : $this->coaches()->with('user')->get();

        $names = $coaches
            ->map(fn (Coach $coach) => $coach->user->name ?? null)
            ->filter()
            ->values();

        if ($names->isNotEmpty()) {
            return $names->implode(', ');
        }

        return $this->coach->user->name ?? 'Belum tersedia';
    }
}
