<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Extracurricular extends Model
{
    use HasFactory;

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
                'image' => asset('images/extracurriculars/student-discussion.jpg'),
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
                'image' => asset('images/extracurriculars/student-camera.jpg'),
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
                'image' => asset('images/extracurriculars/student-camera.jpg'),
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
                'image' => asset('images/extracurriculars/student-discussion.jpg'),
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
                'image' => asset('images/extracurriculars/futsal.jpg'),
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
                'image' => asset('images/extracurriculars/student-discussion.jpg'),
            ],
        ];
    }

    public static function categoryDefinitions(): array
    {
        if (class_exists(ExtracurricularCategory::class)) {
            return ExtracurricularCategory::catalogDefinitions()
                ->keyBy('key')
                ->map(fn (array $definition) => $definition)
                ->all();
        }

        return self::defaultCategoryDefinitions();
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
}
