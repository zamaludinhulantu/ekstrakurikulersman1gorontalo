@extends('layouts.app')

@section('page_title', 'Daftar Kegiatan')
@section('page_subtitle', 'Pilih ekstrakurikuler atau olimpiade sesuai minatmu, lalu lihat detail dan lakukan pendaftaran.')

@push('styles')
    <style>
        .catalog-search-panel,
        .catalog-helper-panel {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
            box-shadow: 0 16px 28px rgba(16, 35, 63, 0.06);
        }

        .catalog-search-panel {
            padding: 1.2rem;
        }

        .catalog-search-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.7rem;
        }

        .catalog-count-badge,
        .catalog-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.5rem 0.82rem;
            font-size: 0.8rem;
            font-weight: 800;
            line-height: 1;
        }

        .catalog-count-badge {
            border: 1px solid #cddaf0;
            background: #f8fbff;
            color: #45617f;
        }

        .catalog-filter-chip-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .catalog-filter-chip {
            border: 1px solid #d1dff0;
            background: #fff;
            color: #44607a;
            cursor: pointer;
            transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        }

        .catalog-filter-chip:focus-visible,
        .catalog-card-actions .btn:focus-visible,
        #frontendSearch:focus-visible {
            outline: 0;
            box-shadow: 0 0 0 0.22rem rgba(47, 111, 255, 0.16);
        }

        .catalog-filter-chip.active {
            background: #eaf2ff;
            border-color: #9ebfff;
            color: #1849cb;
            box-shadow: 0 10px 18px rgba(31, 94, 255, 0.1);
        }

        .catalog-grid {
            align-items: stretch;
        }

        .catalog-card {
            position: relative;
            height: 100%;
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(248, 251, 255, 0.95));
            box-shadow: 0 14px 26px rgba(16, 35, 63, 0.06);
            overflow: hidden;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .catalog-card-media {
            position: relative;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            border-radius: 24px 24px 0 0;
            background: linear-gradient(135deg, #dfeeff 0%, #f4f8ff 100%);
        }

        .catalog-card-media::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(9, 30, 66, 0.04) 0%, rgba(9, 30, 66, 0.2) 100%);
            pointer-events: none;
        }

        .catalog-card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.28s ease;
        }

        .catalog-card-media-badge {
            position: absolute;
            top: 0.9rem;
            right: 0.9rem;
            z-index: 2;
        }

        .catalog-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #2f6fff 0%, #74b6ff 100%);
            opacity: 0.88;
        }

        .catalog-card:hover {
            transform: translateY(-4px);
            border-color: #aec8f6;
            box-shadow: 0 20px 34px rgba(16, 35, 63, 0.1);
        }

        .catalog-card:hover .catalog-card-image {
            transform: scale(1.04);
        }

        .catalog-card .card-body {
            display: flex;
            flex-direction: column;
            padding: 1.15rem;
        }

        .catalog-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.9rem;
            margin-bottom: 0.9rem;
        }

        .catalog-card-heading {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            min-width: 0;
        }

        .catalog-card-icon {
            width: 2.9rem;
            height: 2.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: linear-gradient(135deg, #e8f0ff 0%, #f7fbff 100%);
            color: #1b52d1;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .catalog-card-title {
            margin: 0;
            font-size: 1.06rem;
            font-weight: 800;
            line-height: 1.3;
            color: #163252;
        }

        .catalog-card-category {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.3rem;
            padding: 0.38rem 0.68rem;
            border-radius: 999px;
            background: #eef5ff;
            color: #3c5f8a;
            font-size: 0.74rem;
            font-weight: 700;
        }

        .catalog-card-description {
            margin: 0 0 0.95rem;
            color: #5a6d84;
            font-size: 0.9rem;
            line-height: 1.65;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 3rem;
        }

        .catalog-card-meta {
            display: grid;
            gap: 0.65rem;
            margin-bottom: 1rem;
        }

        .catalog-card-meta-item {
            display: grid;
            grid-template-columns: 1.2rem minmax(0, 1fr);
            gap: 0.62rem;
            align-items: start;
            color: #49617b;
            font-size: 0.84rem;
        }

        .catalog-card-meta-item i {
            color: #2e67dd;
            margin-top: 0.18rem;
        }

        .catalog-card-meta-item strong {
            display: inline;
            margin-right: 0.22rem;
            color: #18334f;
            font-size: 0.8rem;
        }

        .catalog-card-status {
            margin-bottom: 1rem;
            padding: 0.78rem 0.88rem;
            border-radius: 18px;
            border: 1px solid #e2ebf5;
            background: #fbfdff;
        }

        .catalog-card-status strong {
            display: block;
            margin-bottom: 0.32rem;
            color: #29445f;
            font-size: 0.78rem;
        }

        .catalog-card-actions {
            display: flex;
            gap: 0.7rem;
            margin-top: auto;
        }

        .catalog-card-actions .btn,
        .catalog-card-actions .disabled {
            flex: 1 1 0;
        }

        .catalog-empty-card {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 12px 24px rgba(16, 35, 63, 0.05);
        }

        @media (max-width: 767.98px) {
            .catalog-search-panel {
                padding: 1rem;
            }

            .catalog-card-media {
                min-height: 158px;
            }

            .catalog-card .card-body {
                padding: 1rem;
            }

            .catalog-card-actions {
                flex-direction: column;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .catalog-card,
            .catalog-filter-chip {
                transition: none;
            }

            .catalog-card:hover {
                transform: none;
            }

            .catalog-card:hover .catalog-card-image {
                transform: none;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $currentCategory = $category ?? 'semua';
        $currentStatus = $status ?? 'all';
        $showCategoryFilter = $extracurriculars->isNotEmpty();
        $resolveIcon = function ($name, $category = null) {
            $haystack = \Illuminate\Support\Str::lower(trim(($category ? $category.' ' : '').$name));

            return match (true) {
                str_contains($haystack, 'futsal'), str_contains($haystack, 'sepak bola'), str_contains($haystack, 'voli'), str_contains($haystack, 'basket') => 'bi-trophy',
                str_contains($haystack, 'musik'), str_contains($haystack, 'band'), str_contains($haystack, 'paduan suara') => 'bi-music-note-beamed',
                str_contains($haystack, 'pmr'), str_contains($haystack, 'kesehatan') => 'bi-heart-pulse',
                str_contains($haystack, 'pramuka') => 'bi-compass',
                str_contains($haystack, 'sains'), str_contains($haystack, 'robotik'), str_contains($haystack, 'teknologi') => 'bi-cpu',
                str_contains($haystack, 'bahasa'), str_contains($haystack, 'jurnalistik'), str_contains($haystack, 'literasi') => 'bi-journal-richtext',
                str_contains($haystack, 'tilawatil'), str_contains($haystack, 'rohis') => 'bi-stars',
                default => 'bi-grid-1x2',
            };
        };
    @endphp

    <div class="catalog-search-panel mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="section-kicker"><i class="bi bi-search-heart"></i>Katalog Kegiatan</div>
                <h2 class="h5 mb-1">Cari Kegiatan</h2>
                <p class="toolbar-hint mb-0">Temukan ekstrakurikuler atau olimpiade yang sesuai untuk mengembangkan minat dan bakatmu.</p>
            </div>
            <div class="catalog-search-meta">
                <span class="catalog-count-badge"><i class="bi bi-collection"></i>{{ $extracurriculars->total() }} kegiatan</span>
            </div>
        </div>

        <form method="get" action="{{ route('student.extracurriculars.index') }}" class="toolbar-grid" id="catalogSearchForm">
            <input type="hidden" name="category" id="selectedCategory" value="{{ $currentCategory }}">
            <input type="hidden" name="status" id="selectedStatus" value="{{ $currentStatus }}">
            <div class="toolbar-col-8">
                <label class="form-label" for="frontendSearch">Cari kegiatan</label>
                <input type="text" id="frontendSearch" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama atau deskripsi kegiatan">
            </div>
            <div class="toolbar-col-2">
                <button class="btn btn-primary w-100" type="submit" id="triggerSearch"><i class="bi bi-search"></i>Cari</button>
            </div>
            <div class="toolbar-col-2">
                <button class="btn btn-outline-secondary w-100" type="button" id="resetSearch"><i class="bi bi-arrow-repeat"></i>Reset</button>
            </div>
            <div class="toolbar-col-12">
                <label class="form-label mb-2">Filter status</label>
                <div class="catalog-filter-chip-group" id="statusFilters">
                    <button class="catalog-filter-chip {{ $currentStatus === 'all' ? 'active' : '' }}" type="button" data-status-filter="all">Semua</button>
                    <button class="catalog-filter-chip {{ $currentStatus === 'active' ? 'active' : '' }}" type="button" data-status-filter="active">Aktif</button>
                    <button class="catalog-filter-chip {{ $currentStatus === 'registered' ? 'active' : '' }}" type="button" data-status-filter="registered">Sudah terdaftar</button>
                    <button class="catalog-filter-chip {{ $currentStatus === 'unregistered' ? 'active' : '' }}" type="button" data-status-filter="unregistered">Belum terdaftar</button>
                </div>
            </div>
            @if($showCategoryFilter)
                <div class="toolbar-col-12">
                    <label class="form-label mb-2">Filter kategori</label>
                    <div class="catalog-filter-chip-group" id="categoryFilters">
                        @foreach($filterCategories as $filterCategory)
                            <button class="catalog-filter-chip {{ $filterCategory['key'] === $currentCategory ? 'active' : '' }}" type="button" data-category-filter="{{ $filterCategory['key'] }}">
                                {{ $filterCategory['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </form>
    </div>

    <div class="row g-3 catalog-grid" id="extracurricularList">
        @forelse($extracurriculars as $item)
            @php
                $status = $registrationStatuses[$item->id] ?? null;
                $categoryKey = $item->category_key;
                $category = $item->category_label;
                $schedule = $item->schedule_overview ?: 'Jadwal belum tersedia';
                $coachNames = collect([$item->coach?->user?->name])
                    ->merge($item->coaches->pluck('user.name'))
                    ->filter()
                    ->unique()
                    ->values();
                $coachText = $coachNames->isNotEmpty() ? $coachNames->join(', ') : 'Pembina belum ditentukan';
                $quota = $item->quota ?? $item->member_quota ?? $item->capacity ?? null;
                $memberCount = $item->participants_count ?? 0;
                $memberText = $quota ? "{$memberCount} / {$quota} siswa" : ($memberCount > 0 ? "{$memberCount} anggota aktif" : 'Kuota belum tersedia');
                $location = $item->location ?? $item->meeting_location ?? null;
                $locationText = $location ?: 'Lokasi belum tersedia';
                $statusLabel = match (strtolower((string) $status)) {
                    'pending', 'menunggu' => 'Menunggu Konfirmasi',
                    'approved', 'diterima' => 'Sudah Mendaftar',
                    'rejected', 'ditolak' => 'Pendaftaran Ditolak',
                    default => $status ? 'Sudah Mendaftar' : null,
                };
                $statusButtonClass = match (strtolower((string) $status)) {
                    'pending', 'menunggu' => 'btn-outline-warning',
                    'approved', 'diterima' => 'btn-outline-success',
                    'rejected', 'ditolak' => 'btn-outline-danger',
                    default => 'btn-outline-secondary',
                };
                $description = \Illuminate\Support\Str::of($item->description ?: 'Belum ada deskripsi singkat untuk ekstrakurikuler ini.')
                    ->trim()
                    ->toString();
            @endphp
            <div
                class="col-12 col-md-6 col-xl-4 extracurricular-card"
                data-search="{{ \Illuminate\Support\Str::lower($item->name.' '.$description) }}"
                data-category="{{ $categoryKey }}"
                data-status="{{ $statusLabel ? 'registered' : 'unregistered' }}"
                data-active="{{ $item->is_active ? '1' : '0' }}"
            >
                <div class="card catalog-card">
                    <div class="catalog-card-media">
                        <img
                            src="{{ $item->preview_image }}"
                            alt="{{ $item->name }}"
                            class="catalog-card-image"
                            width="640"
                            height="360"
                            loading="lazy"
                            decoding="async"
                        >
                        <span class="badge catalog-card-media-badge {{ $item->is_active ? 'badge-status-success' : 'badge-status-secondary' }}">
                            {{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="catalog-card-header">
                            <div class="catalog-card-heading">
                                <span class="catalog-card-icon" aria-hidden="true"><i class="bi {{ $resolveIcon($item->name, $category) }}"></i></span>
                                <div>
                                    <h3 class="catalog-card-title">{{ $item->name }}</h3>
                                    @if($category)
                                        <span class="catalog-card-category"><i class="bi bi-bookmark-star"></i>{{ $category }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <p class="catalog-card-description">{{ $description }}</p>

                        <div class="catalog-card-meta">
                            <div class="catalog-card-meta-item">
                                <i class="bi bi-calendar3"></i>
                                <div><strong>Jadwal:</strong>{{ $schedule }}</div>
                            </div>
                            <div class="catalog-card-meta-item">
                                <i class="bi bi-person-workspace"></i>
                                <div><strong>Pembina:</strong>{{ $coachText }}</div>
                            </div>
                            <div class="catalog-card-meta-item">
                                <i class="bi bi-people"></i>
                                <div><strong>Kuota:</strong>{{ $memberText }}</div>
                            </div>
                            <div class="catalog-card-meta-item">
                                <i class="bi bi-geo-alt"></i>
                                <div><strong>Lokasi:</strong>{{ $locationText }}</div>
                            </div>
                        </div>

                        @if($statusLabel)
                            <div class="catalog-card-status">
                                <strong>Status Pendaftaran</strong>
                                <span class="badge {{ strtolower((string) $status) === 'approved' ? 'badge-status-success' : (strtolower((string) $status) === 'rejected' ? 'badge-status-danger' : 'badge-status-warning') }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        @endif

                        <div class="catalog-card-actions">
                            <a href="{{ route('student.extracurriculars.show', $item) }}" class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i>Lihat Detail
                            </a>
                            @if($statusLabel)
                                <a href="{{ route('student.registrations.index') }}" class="btn {{ $statusButtonClass }}">
                                    {{ strtolower((string) $status) === 'approved' ? 'Lihat Status' : $statusLabel }}
                                </a>
                            @else
                                <a href="{{ route('student.extracurriculars.register', $item) }}" class="btn btn-primary">
                                    <i class="bi bi-send-check"></i>Daftar Kegiatan
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="catalog-empty-card">
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-inbox"></i></div>
                        <p class="mb-0">Belum ada kegiatan yang tersedia saat ini.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $extracurriculars->links() }}</div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('catalogSearchForm');
            const resetButton = document.getElementById('resetSearch');
            const categoryButtons = Array.from(document.querySelectorAll('[data-category-filter]'));
            const statusButtons = Array.from(document.querySelectorAll('[data-status-filter]'));
            const selectedCategory = document.getElementById('selectedCategory');
            const selectedStatus = document.getElementById('selectedStatus');

            resetButton?.addEventListener('click', function () {
                window.location.href = '{{ route('student.extracurriculars.index') }}';
            });

            categoryButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    selectedCategory.value = button.dataset.categoryFilter || 'semua';
                    form?.submit();
                });
            });

            statusButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    selectedStatus.value = button.dataset.statusFilter || 'all';
                    form?.submit();
                });
            });
        })();
    </script>
@endpush
