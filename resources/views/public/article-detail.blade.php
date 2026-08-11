@extends('layouts.public')

@section('title', $article->title.' | Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo')

@php
    $backToArticlesUrl = $backToArticlesUrl ?? route('public.articles.index');
@endphp

@push('styles')
    <style>
        .article-detail-shell {
            display: grid;
            gap: 1.25rem;
        }

        .article-detail-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.55rem;
            color: #6f8299;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .article-detail-breadcrumb a {
            color: inherit;
            text-decoration: none;
        }

        .article-detail-breadcrumb a:hover {
            color: #1849cb;
        }

        .article-detail-breadcrumb span + span::before,
        .article-detail-breadcrumb a + span::before,
        .article-detail-breadcrumb a + a::before {
            content: "/";
            margin-right: 0.55rem;
            color: #9bb0c8;
        }

        .article-detail-card {
            border-radius: 32px;
            padding: 1.35rem;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
            box-shadow: 0 18px 34px rgba(16, 35, 63, 0.08);
        }

        .article-detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin-bottom: 1rem;
        }

        .article-detail-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-height: 32px;
            padding: 0.38rem 0.7rem;
            border-radius: 999px;
            background: #eef4ff;
            border: 1px solid #d8e5f7;
            color: #35567f;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .article-detail-title {
            margin: 0 0 0.85rem;
            color: #12305b;
            font-size: clamp(1.9rem, 3.8vw, 3rem);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .article-detail-lead {
            margin: 0 0 1rem;
            color: #5e738c;
            font-size: 1.02rem;
            line-height: 1.75;
        }

        .article-detail-header-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem;
            color: #72859a;
            font-size: 0.84rem;
            margin-bottom: 1.1rem;
        }

        .article-detail-cover-wrap {
            width: 100%;
            margin-bottom: 1.25rem;
            border-radius: 26px;
            overflow: hidden;
            background: linear-gradient(135deg, #edf4ff 0%, #dbeaff 100%);
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .article-detail-cover {
            display: block;
            max-width: 100%;
            max-height: 640px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .article-detail-content {
            color: #22344a;
            font-size: 1rem;
            line-height: 1.75;
        }

        .article-detail-content h2,
        .article-detail-content h3,
        .article-detail-content h4 {
            margin-top: 1.4rem;
            margin-bottom: 0.5rem;
            color: #12305b;
            line-height: 1.25;
            font-weight: 900;
        }

        .article-detail-content p,
        .article-detail-content ul,
        .article-detail-content ol,
        .article-detail-content blockquote {
            margin-bottom: 0.65rem;
        }

        .article-detail-content p:empty {
            display: none;
        }

        .article-detail-content ul,
        .article-detail-content ol {
            padding-left: 1.2rem;
        }

        .article-detail-content blockquote {
            padding: 0.95rem 1rem;
            border-left: 4px solid #4f8dff;
            border-radius: 0 18px 18px 0;
            background: #f4f8ff;
            color: #38557a;
        }

        .article-detail-content img {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 20px;
            margin: 1rem 0;
        }

        .article-detail-side-card {
            border-radius: 24px;
            padding: 1rem;
            border: 1px solid #dbe5f0;
            background: #fff;
        }

        .article-detail-side-card h3 {
            margin: 0 0 0.35rem;
            color: #12305b;
            font-size: 1rem;
            font-weight: 800;
        }

        .article-detail-side-card p {
            margin: 0;
            color: #667a92;
            line-height: 1.7;
            font-size: 0.9rem;
        }

        @media (max-width: 767.98px) {
            .article-detail-card {
                padding: 1rem;
                border-radius: 24px;
            }

            .article-detail-cover-wrap {
                border-radius: 18px;
            }

            .article-detail-cover {
                max-height: 480px;
            }

            .article-detail-content {
                font-size: 0.96rem;
                line-height: 1.85;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        <div class="article-detail-shell">
            <nav class="article-detail-breadcrumb" aria-label="Breadcrumb" data-reveal style="--reveal-delay: 0ms;">
                <a href="{{ route('landing') }}">Beranda</a>
                <a href="{{ $backToArticlesUrl }}">Berita</a>
                <span aria-current="page">{{ \Illuminate\Support\Str::limit($article->title, 56) }}</span>
            </nav>

            <section class="article-detail-card" data-reveal style="--reveal-delay: 50ms;">
                @if(!empty($previewMode))
                    <div class="alert alert-warning border-0 rounded-4 mb-4">
                        <strong>Mode pratinjau.</strong> Artikel ini sedang dilihat dari panel pengelola dan belum tentu tampil ke publik.
                    </div>
                @endif

                <div class="article-detail-meta">
                    <span><i class="bi bi-bookmark-star"></i>{{ $article->content_category_label }}</span>
                    <span><i class="bi bi-calendar-event"></i>{{ optional($article->publish_at ?? $article->created_at)->translatedFormat('d F Y H:i') ?? '-' }}</span>
                    <span><i class="bi bi-person"></i>{{ $article->publisher?->name ?? 'Admin' }}</span>
                    @if($article->extracurricular)
                        <span><i class="bi bi-diagram-3"></i>{{ $article->extracurricular?->catalog_item_name ?? $article->extracurricular?->name }}</span>
                    @endif
                </div>

                <h1 class="article-detail-title">{{ $article->title }}</h1>



                <div class="article-detail-header-meta">
                    <span>Dipublikasikan secara resmi untuk portal informasi kegiatan siswa.</span>
                </div>

                @if($article->cover_image_url)
                    <div class="article-detail-cover-wrap">
                        <img
                            src="{{ $article->cover_image_url }}"
                            alt="{{ $article->image_alt_text_label }}"
                            class="article-detail-cover"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                    </div>
                @endif

                <div class="article-detail-content">
                    {!! $article->formatted_content !!}
                </div>
            </section>

            @if($article->extracurricular)
                <section class="article-detail-side-card" data-reveal style="--reveal-delay: 90ms;">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <span class="section-kicker"><i class="bi bi-diagram-3"></i>Kegiatan Terkait</span>
                            <h3>{{ $article->extracurricular->name }}</h3>
                            <p>Artikel ini terhubung dengan kegiatan tersebut. Anda bisa melihat detail kegiatan dan informasi pendaftarannya.</p>
                        </div>
                        <a href="{{ route('public.extracurriculars.show', $article->extracurricular) }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-right-circle"></i>Lihat Detail Kegiatan
                        </a>
                    </div>
                </section>
            @endif

            <div class="d-flex flex-wrap gap-2" data-reveal style="--reveal-delay: 120ms;">
                <a href="{{ $backToArticlesUrl }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>Kembali ke daftar berita
                </a>
                <a href="{{ route('public.activities.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-grid-3x3-gap"></i>Jelajahi Kategori
                </a>
            </div>

            @if($relatedArticles->isNotEmpty())
                <section data-reveal style="--reveal-delay: 150ms;">
                    <div class="section-header-inline">
                        <div>
                            <span class="section-kicker"><i class="bi bi-arrow-repeat"></i>Artikel Terkait</span>
                            <h2 class="section-title">Baca juga publikasi lainnya</h2>
                        </div>
                    </div>
                    <div class="row g-3">
                        @foreach($relatedArticles as $item)
                            <div class="col-12 col-md-6 col-xl-4">
                                @include('public._article-card', ['article' => $item, 'revealDelay' => 40 + (($loop->index % 3) * 60)])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection
