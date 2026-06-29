@extends('layouts.app')

@section('page_title', 'Daftar Ekstrakurikuler')
@section('page_subtitle', 'Pilih ekstrakurikuler sesuai minatmu, lihat detail kegiatannya, lalu lakukan pendaftaran.')

@push('styles')
    <style>
        .page-intro,
        .simple-guide {
            border-radius: 22px;
            border: 1px solid #dbe5f0;
            background: #f8fbff;
            padding: 1rem 1.1rem;
        }

        .page-intro {
            background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
            box-shadow: 0 14px 24px rgba(16, 35, 63, 0.06);
        }

        .simple-guide ol {
            margin: 0;
            padding-left: 1.1rem;
            color: #52667f;
        }

        .simple-guide li + li {
            margin-top: 0.35rem;
        }

        .simple-card {
            border-radius: 18px;
            border: 1px solid #dbe5f0;
            background: #fff;
            box-shadow: 0 10px 18px rgba(16, 35, 63, 0.05);
        }

        .simple-meta {
            display: grid;
            gap: 0.4rem;
            margin: 0.65rem 0;
        }

        .simple-meta-item {
            padding: 0.5rem 0.65rem;
            border-radius: 10px;
            background: #f8fbff;
            border: 1px solid #e3edf7;
        }

        .simple-meta-item strong {
            display: block;
            margin-bottom: 0.1rem;
            font-size: 0.7rem;
            color: #617891;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .simple-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
            margin-top: auto;
        }

        .simple-card .card-body {
            padding: 0.9rem;
        }

        .simple-card h3.h5 {
            font-size: 1.05rem;
        }

        .category-chip-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .category-chip {
            border: 1px solid #d1dff0;
            background: #fff;
            color: #405a75;
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
        }

        .category-chip.active {
            background: #e8f0ff;
            color: #1849cb;
            border-color: #9ebfff;
        }

        .simple-card-category {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.2rem;
            padding: 0.36rem 0.65rem;
            border-radius: 999px;
            background: #eef5ff;
            color: #355987;
            font-size: 0.76rem;
            font-weight: 700;
        }

        @media (max-width: 767.98px) {
            .simple-actions .btn,
            .simple-actions .disabled {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $categoryFieldCandidates = ['category', 'category_name', 'type'];
        $rawCategories = collect($extracurriculars->items())
            ->map(function ($item) use ($categoryFieldCandidates) {
                foreach ($categoryFieldCandidates as $field) {
                    if (!empty($item->{$field})) {
                        return $item->{$field};
                    }
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values();
        $showCategoryFilter = $rawCategories->isNotEmpty();
        $filterCategories = collect(['Semua'])->merge($rawCategories);
    @endphp

    <div class="page-intro mb-3">
        <h2 class="h4 mb-1">Daftar Ekstrakurikuler</h2>
        <p class="mb-0 text-muted">Pilih ekstrakurikuler sesuai minatmu, lihat detail kegiatannya, lalu lakukan pendaftaran.</p>
    </div>

    <div class="card mb-3">
        <div class="card-body toolbar-card">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Cari Ekstrakurikuler</h2>
                    <p class="toolbar-hint mb-0">Gunakan pencarian untuk menemukan ekskul yang ingin kamu daftar.</p>
                </div>
                <span class="badge badge-status-secondary">{{ $extracurriculars->total() }} ekskul</span>
            </div>

            <div class="toolbar-grid">
                <div class="toolbar-col-8">
                    <label class="form-label" for="frontendSearch">Cari ekstrakurikuler</label>
                    <input type="text" id="frontendSearch" value="{{ $search }}" class="form-control" placeholder="Cari ekstrakurikuler, misalnya PMR, OSIS, Futsal...">
                </div>
                <div class="toolbar-col-2">
                    <button class="btn btn-primary w-100" type="button" id="triggerSearch"><i class="bi bi-search"></i>Cari</button>
                </div>
                <div class="toolbar-col-2">
                    <button class="btn btn-outline-secondary w-100" type="button" id="resetSearch"><i class="bi bi-arrow-repeat"></i>Reset</button>
                </div>
                @if($showCategoryFilter)
                    <div class="toolbar-col-12">
                        <label class="form-label mb-2">Filter kategori</label>
                        <div class="category-chip-group" id="categoryFilters">
                            @foreach($filterCategories as $category)
                                <button
                                    class="category-chip {{ $category === 'Semua' ? 'active' : '' }}"
                                    type="button"
                                    data-category-filter="{{ \Illuminate\Support\Str::lower($category) }}"
                                >
                                    {{ $category }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3" id="extracurricularList">
        @forelse($extracurriculars as $item)
            @php
                $status = $registrationStatuses[$item->id] ?? null;
                $category = $item->category ?? $item->category_name ?? $item->type;
                $schedule = $item->schedule_overview ?: 'Jadwal belum ditentukan';
                $quota = $item->quota ?? $item->member_quota ?? $item->capacity ?? null;
                $memberCount = $item->participants_count ?? 0;
                $quotaText = $quota ? $quota : ($memberCount > 0 ? $memberCount . ' anggota' : 'Kuota belum tersedia');
                $statusLabel = match (strtolower((string) $status)) {
                    'pending', 'menunggu' => 'Menunggu Konfirmasi',
                    'approved', 'diterima' => 'Diterima',
                    'rejected', 'ditolak' => 'Ditolak',
                    default => $status ? 'Sudah Mendaftar' : null,
                };
                $statusButtonClass = match (strtolower((string) $status)) {
                    'pending', 'menunggu' => 'btn-outline-warning',
                    'approved', 'diterima' => 'btn-outline-success',
                    'rejected', 'ditolak' => 'btn-outline-danger',
                    default => 'btn-outline-secondary',
                };
            @endphp
            <div
                class="col-12 col-md-6 col-xl-4 extracurricular-card"
                data-search="{{ \Illuminate\Support\Str::lower($item->name) }}"
                data-category="{{ \Illuminate\Support\Str::lower($category ?? '') }}"
            >
                <div class="card simple-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <h3 class="h5 mb-1">{{ $item->name }}</h3>
                                @if($category)
                                    <span class="simple-card-category"><i class="bi bi-bookmark-star"></i>{{ $category }}</span>
                                @endif
                            </div>
                            <span class="badge" data-status="{{ $item->is_active ? 'active' : 'inactive' }}">{{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                        </div>

                        <div class="simple-meta">
                            <div class="simple-meta-item">
                                <strong>Jadwal Latihan</strong>
                                <div>{{ $schedule }}</div>
                            </div>
                            <div class="simple-meta-item">
                                <strong>Kuota / Anggota</strong>
                                <div>{{ $quotaText }}</div>
                            </div>
                        </div>

                        @if($statusLabel)
                            <div class="info-banner mb-3">
                                <i class="bi bi-info-circle"></i>
                                <div>
                                    <strong class="d-block mb-1">Status pendaftaran</strong>
                                    <span class="badge" data-status="{{ $status }}">{{ $statusLabel }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="simple-actions">
                            <a href="{{ route('student.extracurriculars.show', $item) }}" class="btn btn-outline-primary flex-fill"><i class="bi bi-eye"></i>Lihat Detail</a>
                            @if($statusLabel)
                                <span class="btn {{ $statusButtonClass }} flex-fill disabled" aria-disabled="true">{{ $statusLabel }}</span>
                            @else
                                <a href="{{ route('student.extracurriculars.show', $item) }}#form-pendaftaran" class="btn btn-primary flex-fill"><i class="bi bi-send-check"></i>Daftar Ekskul</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-inbox"></i></div>
                        <p class="mb-0">Belum ada ekstrakurikuler yang tersedia saat ini.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="card mt-3 d-none" id="searchEmptyState">
        <div class="empty-state">
            <div class="icon"><i class="bi bi-search"></i></div>
            <p class="mb-0">Ekstrakurikuler tidak ditemukan. Coba gunakan kata kunci lain.</p>
        </div>
    </div>

    <div class="mt-3">{{ $extracurriculars->links() }}</div>
@endsection

@push('scripts')
    <script>
        (function () {
            const input = document.getElementById('frontendSearch');
            const resetButton = document.getElementById('resetSearch');
            const triggerButton = document.getElementById('triggerSearch');
            const cards = Array.from(document.querySelectorAll('.extracurricular-card'));
            const emptyState = document.getElementById('searchEmptyState');
            const categoryButtons = Array.from(document.querySelectorAll('[data-category-filter]'));
            let activeCategory = 'semua';

            const applyFilter = function () {
                const keyword = String(input?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(function (card) {
                    const name = card.dataset.search || '';
                    const category = card.dataset.category || '';
                    const matchesSearch = keyword === '' || name.includes(keyword);
                    const matchesCategory = activeCategory === 'semua' || category === activeCategory;
                    const visible = matchesSearch && matchesCategory;

                    card.classList.toggle('d-none', !visible);
                    if (visible) {
                        visibleCount += 1;
                    }
                });

                emptyState?.classList.toggle('d-none', visibleCount !== 0);
            };

            input?.addEventListener('input', applyFilter);
            triggerButton?.addEventListener('click', applyFilter);
            resetButton?.addEventListener('click', function () {
                if (input) {
                    input.value = '';
                }
                activeCategory = 'semua';
                categoryButtons.forEach(function (button) {
                    button.classList.toggle('active', button.dataset.categoryFilter === 'semua');
                });
                applyFilter();
            });

            categoryButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    activeCategory = button.dataset.categoryFilter || 'semua';
                    categoryButtons.forEach(function (item) {
                        item.classList.toggle('active', item === button);
                    });
                    applyFilter();
                });
            });

            applyFilter();
        })();
    </script>
@endpush
