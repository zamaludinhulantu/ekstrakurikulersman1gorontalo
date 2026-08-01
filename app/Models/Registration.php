<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const DISPLAY_STATUS_WAITING_TEST = 'waiting_test';

    public const DISPLAY_STATUS_SCHEDULED_TEST = 'scheduled_test';

    public const DISPLAY_STATUS_CANCELLATION_REQUESTED = 'cancellation_requested';

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
        'cancellation_requested_at',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'date',
            'verified_at' => 'datetime',
            'cancellation_requested_at' => 'datetime',
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

    public function scheduleParticipants(): HasMany
    {
        return $this->hasMany(ScheduleParticipant::class);
    }

    public function talentTestResults()
    {
        return $this->hasMany(TalentTestResult::class);
    }

    public function getSelectedBranchLabelAttribute(): string
    {
        return $this->selected_branch ?: '-';
    }

    public function hasPublishedTalentTestResult(): bool
    {
        $results = $this->relationLoaded('talentTestResults')
            ? $this->talentTestResults
            : $this->talentTestResults()->get(['id', 'status']);

        return $results->contains(fn (TalentTestResult $result) => $result->status === 'published');
    }

    public function latestPublishedTalentTestResult(): ?TalentTestResult
    {
        $results = $this->relationLoaded('talentTestResults')
            ? $this->talentTestResults
            : $this->talentTestResults()->get();

        return $results
            ->where('status', 'published')
            ->sortByDesc(fn (TalentTestResult $result) => optional($result->published_at)->getTimestamp() ?? 0)
            ->first();
    }

    public function hasScheduledTalentTest(): bool
    {
        $participants = $this->relationLoaded('talentTestParticipants')
            ? $this->talentTestParticipants
            : $this->talentTestParticipants()->with('schedule:id,activity_date')->get();

        return $participants->isNotEmpty();
    }

    public function displayStatus(): string
    {
        if ($this->isCancellationRequested()) {
            return self::DISPLAY_STATUS_CANCELLATION_REQUESTED;
        }

        if (
            $this->status !== self::STATUS_REJECTED
            && $this->willing_to_take_test
            && ! $this->hasPublishedTalentTestResult()
        ) {
            return $this->hasScheduledTalentTest()
                ? self::DISPLAY_STATUS_SCHEDULED_TEST
                : self::DISPLAY_STATUS_WAITING_TEST;
        }

        return $this->status;
    }

    public function canStudentEdit(): bool
    {
        if ($this->isCancellationRequested()) {
            return false;
        }

        if ($this->status === self::STATUS_REJECTED) {
            return ! $this->hasPublishedTalentTestResult();
        }

        return $this->status === self::STATUS_PENDING
            && ! $this->willing_to_take_test
            && ! $this->hasScheduledTalentTest();
    }

    public function canStudentCancel(): bool
    {
        return ! $this->isCancellationRequested()
            && ! in_array($this->status, [self::STATUS_REJECTED, self::STATUS_CANCELLED], true);
    }

    public function isCancellationRequested(): bool
    {
        return $this->cancellation_requested_at !== null;
    }

    public function requiresCancellationApproval(): bool
    {
        return $this->status === self::STATUS_APPROVED || $this->hasScheduledTalentTest();
    }

    public function canCoachRemoveParticipant(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
