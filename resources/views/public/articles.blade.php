@extends('layouts.public')

@section('title', 'Berita dan Artikel | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@php
    $hasFilters = $search !== '' || $contentCategory !== '' || ! empty($extracurricularId) || $publishedFrom !== '' || $publishedUntil !== '';
@endphp

@push('styles')
    <style>
        .article-page-hero {
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            padding: 1.5rem;
            margin: 1.25rem 0 1.25rem;
            color: #143252;
            background:
                radial-gradient(circle at top right, rgba(111, 174, 255, 0.18) 0%, rgba(111, 174, 255, 0) 28%),
                linear-gradient(135deg, #ffffff 0%, #f4f8fd 62%, #eaf3ff 100%);
            border: 1px solid #d9e6f5;
            box-shadow: 0 18px 34px rgba(16, 35, 63, 0.08);
        }

        .article-page-hero .hero-title {
            color: #143252;
        }

        .article-page-hero .hero-text {
            color: #58708c;
        }

        .article-page-hero__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .article-featured-card {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
            gap: 1.25rem;
            align-items: stretch;
            overflow: hidden;
            border-radius: 30px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, #ffffff, #f7fbff);
            box-shadow: 0 18px 34px rgba(16, 35, 63, 0.08);
            margin-bottom: 1.25rem;
        }

        .article-featured-card__copy {
            padding: 1.35rem;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .article-featured-card__media {
            min-height: 100%;
            background: linear-gradient(135deg, #edf4ff 0%, #dbeaff 100%);
        }

        .article-featured-card__media img,
        .article-featured-card__placeholder {
            width: 100%;
            height: 100%;
            display: block;
        }

        .article-featured-card__media img {
            object-fit: cover;
        }

        .article-featured-card__placeholder {
            display: grid;
            place-items: center;
            min-height: 100%;
            padding: 1rem;
            text-align: center;
            background: linear-gradient(135deg, #1f5eff 0%, #66c8ff 100%);
            color: #fff;
            font-weight: 800;
        }

        .article-featured-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .article-featured-card__meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-height: 32px;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            background: #eef4ff;
            border: 1px solid #d7e4f8;
            color: #375478;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .article-featured-card__title {
            margin: 0;
            color: #12305b;
            font-size: clamp(1.6rem, 3vw, 2.25rem);
            line-height: 1.12;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .article-featured-card__excerpt {
            margin: 0;
            color: #59708b;
            font-size: 0.98rem;
            line-height: 1.85;
        }

        .article-featured-card__footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .article-list-toolbar {
            border: 1px solid #dbe5f0;
            border-radius: 22px;
            padding: 0.85rem;
            margin-bottom: 1rem;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            box-shadow: 0 14px 26px rgba(16, 35, 63, 0.05);
        }

        .article-filter-panel {
            flex: 0 0 auto;
            margin: 0;
        }

        .article-filter-panel[open] {
            flex-basis: 100%;
            width: 100%;
        }

        .article-page-header {
            position: relative;
            display: grid;
            gap: 1rem;
            margin: 1rem 0 0.8rem;
        }

        .article-page-heading {
            margin: 0;
            color: #143252;
            font-size: clamp(1.45rem, 2.4vw, 2rem);
            font-weight: 900;
            letter-spacing: -0.035em;
        }

        .article-quick-search {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            flex: 1 1 20rem;
            width: auto;
        }

        .article-search-tools {
            display: flex;
            width: 100%;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            gap: 0.55rem;
        }

        .article-quick-search .form-control {
            min-height: 42px;
        }

        .article-filter-panel summary {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.62rem 0.9rem;
            border: 1px solid #cbdcf4;
            border-radius: 999px;
            background: #fff;
            color: #1849cb;
            cursor: pointer;
            font-size: 0.84rem;
            font-weight: 800;
            list-style: none;
        }

        .article-filter-panel summary::-webkit-details-marker {
            display: none;
        }

        .article-filter-panel[open] summary {
            margin-bottom: 0.7rem;
            background: #edf4ff;
        }

        .article-result-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .article-result-meta__summary strong {
            color: #12305b;
        }

        .article-active-filter-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .article-active-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-height: 32px;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            background: #edf4ff;
            border: 1px solid #cfe0ff;
            color: #1849cb;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .article-page-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (max-width: 991.98px) {
            .article-featured-card,
            .article-page-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .article-featured-card {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .article-page-hero {
                padding: 1.15rem;
                border-radius: 26px;
            }

            .article-page-hero__actions,
            .article-featured-card__footer {
                flex-direction: column;
                align-items: stretch;
            }

            .article-list-toolbar {
                padding: 0.95rem;
                border-radius: 22px;
            }

            .article-page-grid {
                grid-template-columns: 1fr;
            }

            .article-page-header {
                align-items: stretch;
                flex-direction: column;
            }

            .article-quick-search {
                width: 100%;
            }

            .article-search-tools {
                width: 100%;
            }

            .article-filter-panel[open] {
                position: static;
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        <header class="article-page-header" data-reveal>
            <h1 class="article-page-heading"><i class="bi bi-newspaper me-2"></i>Berita & Artikel</h1>
            <div class="article-search-tools">
            <form method="get" class="article-quick-search">
                <label class="visually-hidden" for="article_quick_search">Cari judul artikel</label>
                <input id="article_quick_search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Cari artikel">
                <input type="hidden" name="content_category" value="{{ $contentCategory }}">
                <input type="hidden" name="extracurricular_id" value="{{ $extracurricularId }}">
                <input type="hidden" name="published_from" value="{{ $publishedFrom }}">
                <input type="hidden" name="published_until" value="{{ $publishedUntil }}">
                <button class="btn btn-primary" type="submit" aria-label="Cari artikel"><i class="bi bi-search"></i></button>
            </form>
            <details class="article-filter-panel" @if($hasFilters) open @endif>
                <summary><i class="bi bi-funnel"></i>Filter</summary>
                <form method="get" class="article-list-toolbar">
                    <div class="row g-3">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="visually-hidden" for="article_content_category">Kategori</label>
                        <select id="article_content_category" name="content_category" class="form-select">
                            <option value="">Semua kategori</option>
                            @foreach($contentCategories as $key => $label)
                                <option value="{{ $key }}" @selected($contentCategory === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="visually-hidden" for="article_extracurricular_filter">Kegiatan</label>
                        <select id="article_extracurricular_filter" name="extracurricular_id" class="form-select">
                            <option value="">Semua kegiatan</option>
                            @foreach($extracurriculars as $item)
                                <option value="{{ $item->id }}" @selected((int) $extracurricularId === (int) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="visually-hidden" for="article_published_from">Dari</label>
                        <input id="article_published_from" type="date" name="published_from" value="{{ $publishedFrom }}" class="form-control">
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="visually-hidden" for="article_published_until">Sampai</label>
                        <input id="article_published_until" type="date" name="published_until" value="{{ $publishedUntil }}" class="form-control">
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i>Cari</button>
                        <a href="{{ route('public.articles.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset Filter</a>
                    </div>
                    </div>
                </form>
            </details>
            </div>
        </header>

        <section data-reveal style="--reveal-delay: 90ms;">
            <div class="article-result-meta">
                <div class="article-result-meta__summary">
                    <span class="section-kicker"><i class="bi bi-list-ul"></i>{{ $search !== '' ? "Hasil untuk '{$search}'" : 'Semua Artikel' }}</span>
                    <p class="mb-0 text-muted"><strong>{{ $articles->total() }}</strong> artikel ditemukan.</p>
                </div>
                @if($hasFilters)
                    <div class="article-active-filter-chips">
                        @if($contentCategory !== '')
                            <span class="article-active-filter-chip"><i class="bi bi-bookmark-star"></i>{{ $contentCategories[$contentCategory] ?? $contentCategory }}</span>
                        @endif
                        @if(! empty($extracurricularId))
                            <span class="article-active-filter-chip"><i class="bi bi-diagram-3"></i>{{ optional($extracurriculars->firstWhere('id', $extracurricularId))->name ?? 'Kegiatan' }}</span>
                        @endif
                        @if($publishedFrom !== '' || $publishedUntil !== '')
                            <span class="article-active-filter-chip"><i class="bi bi-calendar-range"></i>Periode aktif</span>
                        @endif
                    </div>
                @endif
            </div>

            @if($articles->isEmpty())
                <div class="card">
                    <div class="empty-state py-5">
                        <div class="icon"><i class="bi bi-newspaper"></i></div>
                        <p class="mb-0">Belum ada berita atau artikel yang dipublikasikan.</p>
                    </div>
                </div>
            @else
                <div class="article-page-grid">
                    @foreach($articles as $article)
                        @include('public._article-card', ['article' => $article, 'revealDelay' => 40 + (($loop->index % 3) * 60)])
                    @endforeach
                </div>

                @if($articles->hasPages())
                    <div class="mt-4">{{ $articles->links() }}</div>
                @endif
            @endif
        </section>
    </div>
@endsection
