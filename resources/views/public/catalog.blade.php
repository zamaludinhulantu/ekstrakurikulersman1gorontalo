@extends('layouts.public')

@section('title', ($fixedCategory['label'] ?? 'Semua Kegiatan') . ' | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@push('styles')
    <style>
        .catalog-page-hero,
        .catalog-toolbar,
        .catalog-empty-card,
        .catalog-mobile-sheet {
            border-radius: 28px;
            border: 1px solid #dbe5f0;
            background: #fff;
            box-shadow: 0 14px 26px rgba(16, 35, 63, 0.06);
        }

        .catalog-page-hero {
            padding: 1.15rem 1.25rem;
            margin: 1rem 0 1rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(242, 247, 255, 0.96));
        }

        .catalog-page-hero.is-osn {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(238, 249, 255, 0.96));
        }

        .catalog-page-hero.is-o2sn {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(255, 248, 240, 0.96));
        }

        .catalog-toolbar {
            padding: 0.9rem;
            margin-bottom: 1rem;
        }

        .catalog-toolbar.is-sticky {
            position: sticky;
            top: 5.3rem;
            z-index: 20;
        }

        .catalog-toolbar-grid {
            display: grid;
            grid-template-columns: minmax(0, 2.2fr) repeat(2, minmax(150px, 0.9fr)) auto;
            gap: 0.8rem;
            align-items: end;
        }

        .catalog-toolbar-mobile-row {
            display: none;
        }

        .catalog-select,
        .catalog-search-input {
            min-height: 46px;
        }

        .catalog-chip-row {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            margin-top: 0.9rem;
        }

        .catalog-active-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.42rem;
            padding: 0.45rem 0.72rem;
            border-radius: 999px;
            border: 1px solid #d1dff0;
            background: #f8fbff;
            color: #355987;
            font-size: 0.78rem;
            font-weight: 800;
            text-decoration: none;
        }

        .catalog-chip-clear {
            display: inline-flex;
            align-items: center;
            gap: 0.42rem;
            color: #6a7e97;
            font-size: 0.8rem;
            font-weight: 800;
            text-decoration: none;
        }

        .catalog-grid {
            align-items: stretch;
        }

        .catalog-result-count {
            color: #607389;
            font-size: 0.9rem;
        }

        .catalog-mobile-sheet .offcanvas-header,
        .catalog-mobile-sheet .offcanvas-body {
            padding: 1rem;
        }

        .catalog-mobile-sheet .offcanvas-body {
            padding-top: 0;
        }

        .catalog-tone-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.42rem 0.72rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .catalog-tone-badge.is-extracurricular {
            background: #eaf2ff;
            color: #1849cb;
        }

        .catalog-tone-badge.is-osn {
            background: #eaf8ff;
            color: #0d78a7;
        }

        .catalog-tone-badge.is-o2sn {
            background: #fff4dd;
            color: #a76405;
        }

        @include('public._activity-card-styles')

        @media (max-width: 991.98px) {
            .catalog-toolbar.is-sticky {
                top: 5rem;
            }
        }

        @media (max-width: 767.98px) {
            .catalog-page-hero,
            .catalog-toolbar {
                padding: 1rem;
            }

            .catalog-toolbar.is-sticky {
                position: static;
            }

            .catalog-toolbar-grid {
                display: none;
            }

            .catalog-toolbar-mobile-row {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto auto;
                gap: 0.7rem;
                align-items: end;
            }

            .public-activity-card-actions {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $statusOptions = [
            'all' => 'Semua',
            'open' => 'Pendaftaran Dibuka',
            'closed' => 'Pendaftaran Ditutup',
        ];
        $sortOptions = [
            'relevant' => 'Paling Relevan',
            'name' => 'Nama A-Z',
            'latest' => 'Terbaru',
            'open' => 'Pendaftaran Dibuka',
        ];

        $activeFilterChips = collect();
        if ($search !== '') {
            $activeFilterChips->push([
                'label' => "Pencarian: {$search}",
                'query' => request()->except('page', 'search'),
            ]);
        }
        if ($status !== 'all') {
            $activeFilterChips->push([
                'label' => 'Status: '.$statusOptions[$status],
                'query' => array_merge(request()->except('page'), ['status' => 'all']),
            ]);
        }
        if ($sort !== 'relevant') {
            $activeFilterChips->push([
                'label' => 'Urutan: '.$sortOptions[$sort],
                'query' => array_merge(request()->except('page'), ['sort' => 'relevant']),
            ]);
        }

        $heroTone = $fixedCategory['tone'] ?? 'is-extracurricular';
        $baseRoute = $fixedCategory
            ? route('public.activities.category', $fixedCategory['slug'])
            : route('public.activities.all');
        $resultTitle = $search !== ''
            ? "Hasil untuk '{$search}'"
            : ($fixedCategory['catalogTitle'] ?? 'Semua Kegiatan');
        $resultSubtitle = $fixedCategory['catalogSubtitle'] ?? 'Halaman semua kegiatan tetap tersedia sebagai opsi tambahan untuk menjelajahi seluruh data.';
        $hasActiveFilters = $activeFilterChips->isNotEmpty();
    @endphp

    <div class="container py-3 py-md-4">
        <section class="catalog-page-hero {{ $heroTone }}" data-reveal>
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <span class="catalog-tone-badge {{ $heroTone }}"><i class="bi {{ $fixedCategory['icon'] ?? 'bi-grid-3x3-gap' }}"></i>{{ $fixedCategory['label'] ?? 'Semua Kegiatan' }}</span>
                    <h1 class="section-title mt-3 mb-2">{{ $fixedCategory['catalogTitle'] ?? 'Semua Kegiatan' }}</h1>
                    <p class="section-subtitle mb-0">{{ $resultSubtitle }}</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if($fixedCategory)
                        <a href="{{ route('public.activities.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke Semua Kategori</a>
                    @else
                        <a href="{{ route('public.activities.index') }}" class="btn btn-outline-secondary"><i class="bi bi-grid"></i>Semua Kategori</a>
                    @endif
                    <a href="{{ route('public.information') }}" class="btn btn-outline-primary"><i class="bi bi-signpost-2"></i>Lihat Alur Pendaftaran</a>
                </div>
            </div>
        </section>

        <section class="catalog-toolbar is-sticky" data-reveal>
            <form method="get" action="{{ $baseRoute }}" class="catalog-toolbar-grid">
                <div>
                    <label class="form-label" for="catalogSearch">Cari kegiatan</label>
                    <input type="text" id="catalogSearch" name="search" value="{{ $search }}" class="form-control catalog-search-input" placeholder="Cari nama kegiatan">
                </div>
                <div>
                    <label class="form-label" for="catalogStatus">Status</label>
                    <select id="catalogStatus" name="status" class="form-select catalog-select">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="catalogSort">Urutkan</label>
                    <select id="catalogSort" name="sort" class="form-select catalog-select">
                        @foreach($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i>Cari</button>
                </div>
            </form>

            <form method="get" action="{{ $baseRoute }}" class="catalog-toolbar-mobile-row">
                <div>
                    <label class="form-label" for="catalogSearchMobile">Cari kegiatan</label>
                    <input type="text" id="catalogSearchMobile" name="search" value="{{ $search }}" class="form-control catalog-search-input" placeholder="Cari nama kegiatan">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                </div>
                <div>
                    <label class="form-label d-block">&nbsp;</label>
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#catalogFilterSheet"><i class="bi bi-funnel"></i>Filter</button>
                </div>
                <div>
                    <label class="form-label d-block">&nbsp;</label>
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#catalogFilterSheet"><i class="bi bi-arrow-down-up"></i>Urutkan</button>
                </div>
            </form>

            @if($hasActiveFilters)
                <div class="catalog-chip-row">
                    @foreach($activeFilterChips as $chip)
                        <a href="{{ $baseRoute . '?' . http_build_query($chip['query']) }}" class="catalog-active-chip">
                            <i class="bi bi-x-circle"></i>{{ $chip['label'] }}
                        </a>
                    @endforeach
                    <a href="{{ $baseRoute }}" class="catalog-chip-clear"><i class="bi bi-arrow-repeat"></i>Hapus Semua Filter</a>
                </div>
            @endif
        </section>

        <section data-reveal>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                <div>
                    <span class="section-kicker"><i class="bi bi-stars"></i>Daftar Kegiatan</span>
                    <h2 class="section-title">{{ $resultTitle }}</h2>
                    <p class="section-subtitle mb-0 catalog-result-count">Total {{ $extracurriculars->total() }} kegiatan tersedia.</p>
                </div>
            </div>

            @if($extracurriculars->isNotEmpty())
                <div class="row g-3 catalog-grid">
                    @foreach($extracurriculars as $activity)
                        <div class="col-12 col-md-6 col-xl-4">
                            @include('public._activity-card', ['activity' => $activity])
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">{{ $extracurriculars->links() }}</div>
            @else
                <div class="catalog-empty-card">
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-search"></i></div>
                        <p class="mb-2">Kegiatan tidak ditemukan. Coba gunakan kata pencarian lain atau hapus beberapa filter.</p>
                        <div class="empty-state-actions">
                            <a href="{{ $baseRoute }}" class="btn btn-outline-primary"><i class="bi bi-arrow-repeat"></i>Reset Filter</a>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>

    <div class="offcanvas offcanvas-bottom catalog-mobile-sheet" tabindex="-1" id="catalogFilterSheet" aria-labelledby="catalogFilterSheetLabel">
        <div class="offcanvas-header">
            <div>
                <h2 class="h5 mb-1" id="catalogFilterSheetLabel">Filter dan Urutan</h2>
                <div class="small text-muted">Atur hasil katalog tanpa memenuhi layar.</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body">
            <form method="get" action="{{ $baseRoute }}" class="d-grid gap-3">
                <div>
                    <label class="form-label" for="catalogSearchSheet">Cari kegiatan</label>
                    <input type="text" id="catalogSearchSheet" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama kegiatan">
                </div>
                <div>
                    <label class="form-label" for="catalogStatusSheet">Status</label>
                    <select id="catalogStatusSheet" name="status" class="form-select">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="catalogSortSheet">Urutkan</label>
                    <select id="catalogSortSheet" name="sort" class="form-select">
                        @foreach($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i>Terapkan</button>
                    <a href="{{ $baseRoute }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Hapus Semua Filter</a>
                </div>
            </form>
        </div>
    </div>
@endsection
