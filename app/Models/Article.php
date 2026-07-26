<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_INACTIVE = 'inactive';

    public const CATEGORY_ACTIVITY_NEWS = 'activity_news';
    public const CATEGORY_INFORMATION = 'information_article';
    public const CATEGORY_ACHIEVEMENT = 'achievement';
    public const CATEGORY_SCHOOL_COVERAGE = 'school_coverage';
    public const CATEGORY_PUBLIC_NOTICE = 'public_notice';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'content_category',
        'extracurricular_id',
        'published_by',
        'image_path',
        'image_name',
        'image_alt_text',
        'meta_description',
        'publication_status',
        'publish_at',
        'expires_at',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $article): void {
            if (blank($article->slug) && filled($article->title)) {
                $article->slug = static::uniqueSlugFromTitle($article->title, $article->id);
            }
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

    public function scopeVisibleToPublic($query)
    {
        return $query
            ->where('is_active', true)
            ->where('publication_status', self::STATUS_PUBLISHED)
            ->where(function ($subQuery): void {
                $subQuery->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($subQuery): void {
                $subQuery->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function getDisplayStatusAttribute(): string
    {
        if (! $this->is_active || $this->publication_status === self::STATUS_INACTIVE) {
            return 'Dinonaktifkan';
        }

        return static::publicationStatuses()[$this->publication_status] ?? 'Draft';
    }

    public function getIsPubliclyVisibleAttribute(): bool
    {
        if (! $this->is_active || $this->publication_status !== self::STATUS_PUBLISHED) {
            return false;
        }

        if ($this->publish_at !== null && $this->publish_at->gt(now())) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (blank($this->image_path)) {
            return null;
        }

        if (Str::startsWith($this->image_path, ['http://', 'https://'])) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function getImageAltTextLabelAttribute(): string
    {
        return $this->image_alt_text ?: $this->title;
    }

    public function getContentCategoryLabelAttribute(): string
    {
        return static::contentCategories()[$this->content_category] ?? 'Berita Kegiatan';
    }

    public static function uniqueSlugFromTitle(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $counter = 2;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public static function publicationStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Dijadwalkan',
            self::STATUS_PUBLISHED => 'Dipublikasikan',
            self::STATUS_ARCHIVED => 'Diarsipkan',
            self::STATUS_INACTIVE => 'Dinonaktifkan',
        ];
    }

    public static function contentCategories(): array
    {
        return [
            self::CATEGORY_ACTIVITY_NEWS => 'Berita Kegiatan',
            self::CATEGORY_INFORMATION => 'Artikel Informasi',
            self::CATEGORY_ACHIEVEMENT => 'Prestasi',
            self::CATEGORY_SCHOOL_COVERAGE => 'Liputan Sekolah',
            self::CATEGORY_PUBLIC_NOTICE => 'Pengumuman Publik',
        ];
    }

    public static function sanitizeHtml(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/<(script|style)(.*?)>(.*?)<\/\\1>/is', '', $value) ?? $value;
        $value = preg_replace('/on[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $value) ?? $value;
        $value = preg_replace('/javascript:/i', '', $value) ?? $value;
        $value = preg_replace('/<a\b(?![^>]*\btarget=)[^>]*>/i', '<a>', $value) ?? $value;

        return strip_tags($value, '<p><br><strong><b><em><i><ul><ol><li><blockquote><h2><h3><h4><a>');
    }
}
