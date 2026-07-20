<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'student_id',
        'extracurricular_id',
        'selected_branch',
        'registration_date',
        'status',
        'notes',
        'motivation_reason',
        'goal_statement',
        'prior_experience',
        'current_skills',
        'primary_talent',
        'preferred_position',
        'achievement_history',
        'achievement_proof_path',
        'willing_to_take_test',
        'student_notes',
        'allow_public_profile',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'verified_at' => 'datetime',
            'willing_to_take_test' => 'boolean',
            'allow_public_profile' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function talentTestParticipants()
    {
        return $this->hasMany(TalentTestParticipant::class);
    }

    public function talentTestResults()
    {
        return $this->hasMany(TalentTestResult::class);
    }

    public function getSelectedBranchLabelAttribute(): string
    {
        return $this->selected_branch ?: '-';
    }
}
