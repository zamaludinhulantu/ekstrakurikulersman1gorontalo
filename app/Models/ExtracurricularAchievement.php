<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtracurricularAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'extracurricular_id',
        'title',
        'description',
        'achievement_date',
    ];

    protected function casts(): array
    {
        return [
            'achievement_date' => 'date',
        ];
    }

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }
}
