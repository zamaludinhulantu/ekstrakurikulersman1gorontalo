<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TalentTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'registration_id',
        'student_id',
        'coach_id',
        'status',
        'overall_score',
        'ability_category',
        'training_group',
        'recommended_role',
        'recommendation',
        'coach_notes',
        'internal_notes',
        'needs_retest',
        'retest_schedule_id',
        'evaluated_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_score' => 'decimal:2',
            'needs_retest' => 'boolean',
            'evaluated_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    public function retestSchedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'retest_schedule_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TalentTestResultItem::class, 'talent_test_result_id')->with('aspect');
    }
}
