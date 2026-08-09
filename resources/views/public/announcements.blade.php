@extends('layouts.public')

@section('title', 'Pengumuman | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@push('styles')
    <style>
        .announcement-card {
            margin-top: 1.25rem;
        }

        .announcement-page-header {
            display: grid;
            gap: 1rem;
            margin: 1rem 0 1.5rem;
        }

        .announcement-page-header h1 {
            margin: 0;
            color: #12345c;
        }

        .announcement-search-tools {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .65rem;
        }

        .announcement-quick-search {
            display: flex;
            flex: 1 1 28rem;
            gap: .55rem;
        }

        .announcement-quick-search .form-control {
            min-width: 0;
        }

        .announcement-filter-panel {
            flex: 0 0 auto;
        }

        .announcement-filter-panel summary {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            min-height: 44px;
            padding: .6rem 1rem;
            border: 1px solid #cfe0fb;
            border-radius: 999px;
            color: #1552d6;
            background: #f5f8ff;
            font-weight: 700;
            cursor: pointer;
            list-style: none;
        }

        .announcement-filter-panel summary::-webkit-details-marker {
            display: none;
        }

        .announcement-filter-panel[open] {
            flex-basis: 100%;
            width: 100%;
        }

        .public-announcement-filter {
            padding: 1rem;
            border: 1px solid #dce7f4;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(17, 48, 82, 0.05);
        }

        .public-announcement-filter form {
            display: grid;
            grid-template-columns: repeat(2, minmax(180px, 1fr)) auto;
            gap: 0.75rem;
            align-items: end;
        }

        @media (max-width: 767.98px) {
            .announcement-quick-search {
                flex-basis: 100%;
            }

            .public-announcement-filter form {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        <section data-reveal>
            @php($hasFilters = filled($filters['extracurricular_id']) || filled($filters['priority']))
            <header class="announcement-page-header">
                <h1><i class="bi bi-megaphone me-2"></i>Pengumuman</h1>

                <div class="announcement-search-tools">
                    <form method="get" action="{{ route('public.announcements') }}" class="announcement-quick-search">
                        <input type="hidden" name="extracurricular_id" value="{{ $filters['extracurricular_id'] }}">
                        <input type="hidden" name="priority" value="{{ $filters['priority'] }}">
                        <input id="public_announcement_search" name="search" value="{{ $filters['search'] }}" class="form-control" maxlength="120" placeholder="Cari pengumuman">
                        <button class="btn btn-primary px-3" type="submit" aria-label="Cari pengumuman"><i class="bi bi-search"></i></button>
                    </form>

                    <details class="announcement-filter-panel" @if($hasFilters) open @endif>
                        <summary><i class="bi bi-funnel"></i>Filter</summary>
                        <div class="public-announcement-filter mt-2">
                            <form method="get" action="{{ route('public.announcements') }}">
                                <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                <div>
                                    <label class="form-label" for="public_announcement_activity">Kegiatan</label>
                                    <select id="public_announcement_activity" name="extracurricular_id" class="form-select">
                                        <option value="">Semua kegiatan</option>
                                        @foreach($extracurriculars as $item)
                                            <option value="{{ $item->id }}" @selected($filters['extracurricular_id'] === $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label" for="public_announcement_priority">Prioritas</label>
                                    <select id="public_announcement_priority" name="priority" class="form-select">
                                        <option value="">Semua prioritas</option>
                                        <option value="normal" @selected($filters['priority'] === 'normal')>Biasa</option>
                                        <option value="important" @selected($filters['priority'] === 'important')>Penting</option>
                                        <option value="urgent" @selected($filters['priority'] === 'urgent')>Mendesak</option>
                                    </select>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('public.announcements') }}" class="btn btn-outline-secondary">Reset</a>
                                    <button class="btn btn-primary" type="submit">Cari</button>
                                </div>
                            </form>
                        </div>
                    </details>
                </div>
            </header>

            <div class="row g-3">
                @forelse($announcements as $announcement)
                    <div class="col-12 col-md-6 col-xl-4">
                        @include('public._announcement-card', ['announcement' => $announcement, 'expandable' => true])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-megaphone"></i></div>
                                <p class="mb-0">Belum ada pengumuman yang ditampilkan saat ini.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($announcements->hasPages())
                <div class="mt-4">{{ $announcements->links() }}</div>
            @endif
        </section>
    </div>
@endsection
