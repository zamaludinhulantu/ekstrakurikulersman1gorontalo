<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Extracurricular extends Model
{
    use HasFactory;

    private const CACHE_KEY_CATEGORY_DEFINITIONS = 'extracurricular.category-definitions.v1';
    private const CACHE_KEY_CATEGORY_IDS = 'extracurricular.category-ids.v1';
    private const CACHE_KEY_ACTIVE_CATEGORY_COUNTS = 'extracurricular.active-category-counts.v1';

    public const TYPE_EXTRACURRICULAR = 'extracurricular';
    public const TYPE_OLYMPIAD = 'olympiad';
    public const CATEGORY_GENERAL = 'general';
    public const CATEGORY_OSN = 'osn';
    public const CATEGORY_FLS3N = 'fls3n';
    public const CATEGORY_DEBATE = 'debate';
    public const CATEGORY_O2SN = 'o2sn';
    public const CATEGORY_MUSEUM = 'museum';

    protected $fillable = [
        'coach_id',
        'type',
        'name',
        'description',
        'requirements',
        'schedule_overview',
        'branch_options',
        'image_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'branch_options' => 'array',
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

    public function talentTestAspects(): HasMany
    {
        return $this->hasMany(TalentTestAspect::class)->orderBy('display_order')->orderBy('name');
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

        if ($this->relationLoaded('coach') && $this->coach?->relationLoaded('user')) {
            return $this->coach->user->name ?? 'Belum tersedia';
        }

        return $this->coach()->with('user')->first()?->user?->name ?? 'Belum tersedia';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_OLYMPIAD => 'Olimpiade',
            default => 'Ekstrakurikuler',
        };
    }

    public function getActivityLabelAttribute(): string
    {
        return $this->type === self::TYPE_OLYMPIAD ? 'kegiatan olimpiade' : 'ekstrakurikuler';
    }

    public function getHasBranchesAttribute(): bool
    {
        return collect($this->branch_options)->filter()->isNotEmpty();
    }

    public function getCatalogGroupLabelAttribute(): string
    {
        return $this->category_label;
    }

    public function getCatalogItemNameAttribute(): string
    {
        if (str_contains($this->name, ' - ')) {
            return (string) str($this->name)->after(' - ');
        }

        return $this->name;
    }

    public function getCategoryKeyAttribute(): string
    {
        return self::inferCategoryKey($this->name, $this->type);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categoryDefinitions()[$this->category_key]['label'] ?? 'Ekstrakurikuler';
    }

    public function getCategorySlugAttribute(): string
    {
        return self::categoryDefinitions()[$this->category_key]['slug'] ?? 'ekskul-umum';
    }

    public static function defaultCategoryDefinitions(): array
    {
        return [
            self::CATEGORY_GENERAL => [
                'key' => self::CATEGORY_GENERAL,
                'slug' => 'ekskul-umum',
                'label' => 'Ekskul Umum',
                'icon' => 'bi-grid-1x2',
                'tone' => 'is-extracurricular',
                'description' => 'Kegiatan organisasi, keagamaan, literasi, media, dan pengembangan karakter siswa.',
                'catalog_title' => 'Jelajahi Ekskul Umum',
                'catalog_subtitle' => 'Temukan kegiatan sekolah yang sesuai dengan minat, organisasi, karakter, dan kreativitasmu.',
                'image' => self::makeCategoryPreviewImage('Ekskul Umum', 'bi-grid-1x2', ['#1d4ed8', '#60a5fa', '#dbeafe']),
            ],
            self::CATEGORY_OSN => [
                'key' => self::CATEGORY_OSN,
                'slug' => 'osn',
                'label' => 'OSN',
                'icon' => 'bi-cpu',
                'tone' => 'is-osn',
                'description' => 'Pembinaan bidang akademik dan sains untuk meningkatkan kemampuan serta persiapan kompetisi.',
                'catalog_title' => 'Olimpiade Sains Nasional',
                'catalog_subtitle' => 'Jelajahi bidang pembinaan akademik untuk mengembangkan kemampuan dan mempersiapkan kompetisi.',
                'image' => self::makeCategoryPreviewImage('OSN', 'bi-cpu', ['#0f766e', '#2dd4bf', '#ccfbf1']),
            ],
            self::CATEGORY_FLS3N => [
                'key' => self::CATEGORY_FLS3N,
                'slug' => 'fls3n',
                'label' => 'FLS3N',
                'icon' => 'bi-palette2',
                'tone' => 'is-extracurricular',
                'description' => 'Pembinaan seni dan kreativitas siswa untuk lomba, apresiasi karya, dan pertunjukan sekolah.',
                'catalog_title' => 'Festival dan Lomba Seni Siswa Nasional',
                'catalog_subtitle' => 'Pilih bidang seni yang sesuai dengan kreativitas, ekspresi, dan kemampuan berkaryamu.',
                'image' => self::makeCategoryPreviewImage('FLS3N', 'bi-palette2', ['#7c3aed', '#c084fc', '#f3e8ff']),
            ],
            self::CATEGORY_DEBATE => [
                'key' => self::CATEGORY_DEBATE,
                'slug' => 'debat',
                'label' => 'Debat',
                'icon' => 'bi-chat-square-text',
                'tone' => 'is-extracurricular',
                'description' => 'Pembinaan argumentasi, public speaking, dan analisis isu untuk kompetisi debat siswa.',
                'catalog_title' => 'Jelajahi Kegiatan Debat',
                'catalog_subtitle' => 'Temukan bidang debat yang melatih logika, komunikasi, dan kepercayaan dirimu.',
                'image' => self::makeCategoryPreviewImage('Debat', 'bi-chat-square-text', ['#be123c', '#fb7185', '#ffe4e6']),
            ],
            self::CATEGORY_O2SN => [
                'key' => self::CATEGORY_O2SN,
                'slug' => 'o2sn',
                'label' => 'O2SN',
                'icon' => 'bi-trophy',
                'tone' => 'is-o2sn',
                'description' => 'Pembinaan olahraga dan seni untuk mengembangkan kemampuan serta persiapan perlombaan.',
                'catalog_title' => 'Olimpiade Olahraga Siswa Nasional',
                'catalog_subtitle' => 'Pilih cabang olahraga atau seni yang sesuai dengan kemampuan dan minatmu.',
                'image' => self::makeCategoryPreviewImage('O2SN', 'bi-trophy', ['#b45309', '#f59e0b', '#fef3c7']),
            ],
            self::CATEGORY_MUSEUM => [
                'key' => self::CATEGORY_MUSEUM,
                'slug' => 'kegiatan-museum',
                'label' => 'Keg. Museum',
                'icon' => 'bi-bank',
                'tone' => 'is-extracurricular',
                'description' => 'Kegiatan bertema sejarah, budaya, dan museum untuk memperluas wawasan siswa melalui karya dan eksplorasi.',
                'catalog_title' => 'Jelajahi Kegiatan Museum',
                'catalog_subtitle' => 'Temukan kegiatan sejarah, budaya, dan karya kreatif yang terhubung dengan pembinaan museum.',
                'image' => self::makeCategoryPreviewImage('Museum', 'bi-bank', ['#374151', '#6b7280', '#e5e7eb']),
            ],
        ];
    }

    public static function categoryDefinitions(): array
    {
        return Cache::rememberForever(self::CACHE_KEY_CATEGORY_DEFINITIONS, function (): array {
            if (class_exists(ExtracurricularCategory::class)) {
                return ExtracurricularCategory::catalogDefinitions()
                    ->keyBy('key')
                    ->map(fn (array $definition) => $definition)
                    ->all();
            }

            return self::defaultCategoryDefinitions();
        });
    }

    public static function idsForCategory(string $category): array
    {
        return self::categoryIdsByKey()[$category] ?? [];
    }

    public static function activeCategoryCounts(): array
    {
        return Cache::rememberForever(self::CACHE_KEY_ACTIVE_CATEGORY_COUNTS, function (): array {
            $items = self::query()
                ->where('is_active', true)
                ->where(function ($query): void {
                    $query->where('type', '!=', self::TYPE_OLYMPIAD)
                        ->orWhereNull('branch_options');
                })
                ->get(['id', 'name', 'type']);

            $counts = ['total' => $items->count()];

            foreach (array_keys(self::categoryDefinitions()) as $key) {
                $counts[$key] = $items->filter(fn (self $item) => $item->category_key === $key)->count();
            }

            return $counts;
        });
    }

    public static function forgetCatalogCaches(): void
    {
        Cache::forget(self::CACHE_KEY_CATEGORY_DEFINITIONS);
        Cache::forget(self::CACHE_KEY_CATEGORY_IDS);
        Cache::forget(self::CACHE_KEY_ACTIVE_CATEGORY_COUNTS);
    }

    private static function categoryIdsByKey(): array
    {
        return Cache::rememberForever(self::CACHE_KEY_CATEGORY_IDS, function (): array {
            $grouped = self::query()
                ->get(['id', 'name', 'type'])
                ->groupBy(fn (self $item) => $item->category_key)
                ->map(fn ($items) => $items->pluck('id')->all())
                ->all();

            foreach (array_keys(self::categoryDefinitions()) as $key) {
                $grouped[$key] = $grouped[$key] ?? [];
            }

            return $grouped;
        });
    }

    public static function inferCategoryKey(?string $name, ?string $type = null): string
    {
        $normalized = str((string) $name)->lower()->squish()->toString();
        $normalized = str_replace(['&', '/', '.'], ['dan', ' ', ''], $normalized);

        if (str_starts_with($normalized, 'o2sn - ') || str_starts_with($normalized, '02sn - ')) {
            return self::CATEGORY_O2SN;
        }

        if ($type === self::TYPE_OLYMPIAD || str_starts_with($normalized, 'osn - ')) {
            return self::CATEGORY_OSN;
        }

        if (str_starts_with($normalized, 'fls3n - ')) {
            return self::CATEGORY_FLS3N;
        }

        if (str_starts_with($normalized, 'debat - ')) {
            return self::CATEGORY_DEBATE;
        }

        if (str_starts_with($normalized, 'keg musium - ') || str_starts_with($normalized, 'kegiatan museum - ')) {
            return self::CATEGORY_MUSEUM;
        }

        $fls3nItems = [
            'vokalia pa/pi', 'cipta lagu', 'baca puisi', 'cipta puisi', 'design poster pa/pi',
            'komik digital', 'monolog', 'kriya pa/pi', 'gitar solo', 'tari kreasi',
            'film pendek', 'musik tradisional', 'photography', 'fotography', 'jurnalistik',
            'menulis cerpen', 'vokal solo', 'vokal grup', 'seni tari', 'seni musik',
        ];

        if (in_array($normalized, $fls3nItems, true)) {
            return self::CATEGORY_FLS3N;
        }

        $debateItems = ['bahasa inggris', 'bahasa indonesia', 'hukum', 'ekonomi', 'debat bahasa inggris', 'debat bahasa indonesia'];
        if (in_array($normalized, $debateItems, true)) {
            return self::CATEGORY_DEBATE;
        }

        $museumItems = [
            'tutur sejarah', 'melukis', 'cipta lagu tentang musium', 'cipta lagu tentang museum',
            'tarian tidi', 'paiya lo hungulo poli', 'keg musium', 'kegiatan museum',
        ];
        if (in_array($normalized, $museumItems, true)) {
            return self::CATEGORY_MUSEUM;
        }

        $osnItems = ['matematika', 'fisika', 'biologi', 'kimia', 'ekonomi', 'geografi', 'kebumian', 'astronomi', 'informatika', 'infomatika', 'ipa terpadu'];
        if (in_array($normalized, $osnItems, true)) {
            return self::CATEGORY_OSN;
        }

        $o2snItems = [
            'silat', 'karate dan taekwondo', 'karate taekwondo', 'renang', 'badminton',
            'atletik', 'panjat tebing', 'tenis meja', 'takraw', 'volly ball', 'voli ball',
            'voli', 'basket ball', 'basketball', 'futsal sepak bola', 'futsal', 'sepak bola',
        ];
        if (in_array($normalized, $o2snItems, true)) {
            return self::CATEGORY_O2SN;
        }

        return self::CATEGORY_GENERAL;
    }

    public static function makePreviewImage(string $name): string
    {
        $palette = collect([
            ['#c70f43', '#ff6f87', '#ffd5e0'],
            ['#008d8a', '#34d2c5', '#dcfffb'],
            ['#d67b00', '#ffb11a', '#fff0c7'],
            ['#2d63d8', '#63a3ff', '#dce9ff'],
            ['#6a35cc', '#9f72ff', '#efe4ff'],
        ]);

        $colors = $palette[abs(crc32($name)) % $palette->count()];
        $label = e(Str::upper($name));

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 520">
    <defs>
        <linearGradient id="bg" x1="0%" y1="8%" x2="100%" y2="92%">
            <stop offset="0%" stop-color="{$colors[0]}"/>
            <stop offset="55%" stop-color="{$colors[1]}"/>
            <stop offset="100%" stop-color="{$colors[2]}"/>
        </linearGradient>
    </defs>
    <rect width="800" height="520" rx="36" fill="url(#bg)"/>
    <rect x="6" y="6" width="788" height="508" rx="30" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="4"/>
    <circle cx="118" cy="76" r="46" fill="rgba(255,255,255,0.96)"/>
    <circle cx="696" cy="72" r="78" fill="rgba(255,255,255,0.14)"/>
    <circle cx="620" cy="286" r="118" fill="rgba(15,23,42,0.14)"/>
    <circle cx="92" cy="368" r="74" fill="rgba(255,255,255,0.16)"/>
    <text x="84" y="100" font-family="Segoe UI, Arial, sans-serif" font-size="92" font-weight="900" fill="{$colors[0]}">O</text>
    <rect x="0" y="410" width="800" height="110" fill="rgba(255,255,255,0.16)"/>
    <text x="70" y="475" font-family="Segoe UI, Arial, sans-serif" font-size="46" font-weight="800" fill="#ffffff">{$label}</text>
</svg>
SVG;

        return 'data:image/svg+xml;utf8,'.rawurlencode($svg);
    }

    public static function makeCategoryPreviewImage(string $label, string $icon, array $colors): string
    {
        $title = e(Str::upper($label));
        $iconGlyph = match ($icon) {
            'bi-cpu' => '⌘',
            'bi-palette2' => '✦',
            'bi-chat-square-text' => '✎',
            'bi-trophy' => '◔',
            'bi-bank' => '▦',
            default => '◫',
        };

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 540">
    <defs>
        <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="{$colors[0]}"/>
            <stop offset="55%" stop-color="{$colors[1]}"/>
            <stop offset="100%" stop-color="{$colors[2]}"/>
        </linearGradient>
    </defs>
    <rect width="960" height="540" rx="36" fill="url(#bg)"/>
    <rect x="10" y="10" width="940" height="520" rx="28" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="4"/>
    <circle cx="112" cy="84" r="42" fill="rgba(255,255,255,0.96)"/>
    <circle cx="816" cy="92" r="92" fill="rgba(255,255,255,0.16)"/>
    <circle cx="746" cy="370" r="138" fill="rgba(15,23,42,0.14)"/>
    <circle cx="138" cy="404" r="92" fill="rgba(255,255,255,0.14)"/>
    <text x="86" y="103" font-family="Segoe UI Symbol, Segoe UI, Arial, sans-serif" font-size="64" font-weight="900" fill="{$colors[0]}">{$iconGlyph}</text>
    <rect x="0" y="404" width="960" height="136" fill="rgba(255,255,255,0.18)"/>
    <text x="76" y="485" font-family="Segoe UI, Arial, sans-serif" font-size="52" font-weight="900" fill="#ffffff">{$title}</text>
</svg>
SVG;

        return 'data:image/svg+xml;utf8,'.rawurlencode($svg);
    }
}
