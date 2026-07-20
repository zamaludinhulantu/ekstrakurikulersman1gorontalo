<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    public const SAVE_STATE_DRAFT = 'draft';
    public const SAVE_STATE_FINALIZED = 'finalized';

    protected $fillable = [
        'schedule_id',
        'extracurricular_id',
        'student_id',
        'recorded_by',
        'status',
        'is_late',
        'save_state',
        'notes',
        'recorded_at',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'is_late' => 'boolean',
            'recorded_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'present' && $this->is_late) {
            return 'late';
        }

        return $this->status;
    }

    public function getDisplayStatusLabelAttribute(): string
    {
        return match ($this->display_status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => $this->display_status,
        };
    }

    public function getSaveStateLabelAttribute(): string
    {
        return match ($this->save_state) {
            self::SAVE_STATE_DRAFT => 'Draft',
            self::SAVE_STATE_FINALIZED => 'Sudah Disimpan',
            default => 'Belum Diisi',
        };
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
