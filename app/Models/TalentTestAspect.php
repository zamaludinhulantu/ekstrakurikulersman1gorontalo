<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TalentTestAspect extends Model
{
    use HasFactory;

    protected $fillable = [
        'extracurricular_id',
        'name',
        'description',
        'max_score',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function resultItems(): HasMany
    {
        return $this->hasMany(TalentTestResultItem::class);
    }
}
