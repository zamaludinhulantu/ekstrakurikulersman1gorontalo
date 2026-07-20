<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentTestResultItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'talent_test_result_id',
        'talent_test_aspect_id',
        'score',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(TalentTestResult::class, 'talent_test_result_id');
    }

    public function aspect(): BelongsTo
    {
        return $this->belongsTo(TalentTestAspect::class, 'talent_test_aspect_id');
    }
}
