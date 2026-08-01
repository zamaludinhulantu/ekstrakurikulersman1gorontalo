<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Announcement extends Model
{
    use HasFactory;

    private static bool $enhancedSchemaConfirmed = false;

    public const PRIORITY_NORMAL = 'normal';

    public const PRIORITY_IMPORTANT = 'important';

    public const PRIORITY_URGENT = 'urgent';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'title',
        'content',
        'priority',
        'extracurricular_id',
        'published_by',
        'publication_status',
        'publish_at',
        'ends_at',
        'attachment_path',
        'attachment_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'publish_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function getDisplayStatusAttribute(): string
    {
        if (! self::supportsEnhancedSchema()) {
            return $this->is_active ? 'Dipublikasikan' : 'Dinonaktifkan';
        }

        if ($this->publication_status === self::STATUS_INACTIVE || ! $this->is_active) {
            return 'Dinonaktifkan';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'Berakhir';
        }

        if (
            $this->publication_status === self::STATUS_SCHEDULED
            && $this->publish_at
            && $this->publish_at->isPast()
        ) {
            return 'Dipublikasikan';
        }

        return match ($this->publication_status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_PUBLISHED => 'Dipublikasikan',
            default => 'Draft',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        if (! self::supportsEnhancedSchema()) {
            return 'Biasa';
        }

        return match ($this->priority) {
            self::PRIORITY_IMPORTANT => 'Penting',
            self::PRIORITY_URGENT => 'Mendesak',
            default => 'Biasa',
        };
    }

    public function scopeVisibleToStudents($query)
    {
        if (! self::supportsEnhancedSchema()) {
            return $query->where('is_active', true);
        }

        return $query
            ->where('is_active', true)
            ->where(function (Builder $statusQuery): void {
                $statusQuery->where('publication_status', self::STATUS_PUBLISHED)
                    ->orWhere(function (Builder $scheduledQuery): void {
                        $scheduledQuery->where('publication_status', self::STATUS_SCHEDULED)
                            ->where('publish_at', '<=', now());
                    });
            })
            ->where(function ($subQuery): void {
                $subQuery->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($subQuery): void {
                $subQuery->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->where(function (Builder $audienceQuery): void {
                $audienceQuery->whereNotNull('extracurricular_id')
                    ->orWhereHas('publisher', fn (Builder $publisherQuery) => $publisherQuery
                        ->whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN]));
            });
    }

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public static function supportsEnhancedSchema(): bool
    {
        if (static::$enhancedSchemaConfirmed) {
            return true;
        }

        return static::$enhancedSchemaConfirmed = Schema::hasColumns('announcements', [
            'priority',
            'publication_status',
            'publish_at',
            'ends_at',
            'attachment_path',
            'attachment_name',
        ]);
    }
}
