@extends('layouts.app')

@section('page_title', 'Berita dan Artikel Admin')
@section('page_subtitle', 'Kelola konten publik sekolah dengan tampilan daftar, filter, dan form editor yang lebih ringkas.')

@include('articles._assets')

@php
    $isWriteTab = $tab === 'write';
    $hasActiveFilters = $search !== '' || $contentCategory !== '' || $status !== '' || $extracurricularId !== '' || $publishedFrom !== '' || $publishedUntil !== '';
    $listUrl = $listUrl ?? route('admin.articles.index', request()->except(['page', 'tab']) + ['tab' => 'list']);
    $writeUrl = $writeUrl ?? route('admin.articles.index', request()->except(['page', 'tab']) + ['tab' => 'write']);
@endphp

@section('content')
    <div class="article-workspace">
        <div class="article-surface article-surface--padded article-surface--soft">
            <div class="article-tab-header">
                <div class="article-tab-nav">
                    <a href="{{ $listUrl }}" class="{{ ! $isWriteTab ? 'active' : '' }}">Daftar Berita / Artikel</a>
                    <a href="{{ $writeUrl }}#article-create-panel" class="{{ $isWriteTab ? 'active' : '' }}">Tulis Artikel</a>
                </div>
                @if(! $isWriteTab)
                    <a href="{{ $writeUrl }}#article-create-panel" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i>Tulis Artikel
                    </a>
                @endif
            </div>
        </div>

        @if(! $isWriteTab)
            <div class="article-summary-grid">
                <article class="article-summary-card">
                    <span class="label">Total Artikel</span>
                    <span class="value">{{ $statistics['total'] }}</span>
                    <span class="hint">Seluruh konten yang tersimpan.</span>
                </article>
                <article class="article-summary-card">
                    <span class="label">Draft</span>
                    <span class="value">{{ $statistics['draft'] }}</span>
                    <span class="hint">Belum tampil ke publik.</span>
                </article>
                <article class="article-summary-card">
                    <span class="label">Dijadwalkan</span>
                    <span class="value">{{ $statistics['scheduled'] }}</span>
                    <span class="hint">Menunggu waktu tayang.</span>
                </article>
                <article class="article-summary-card">
                    <span class="label">Dipublikasikan</span>
                    <span class="value">{{ $statistics['published'] }}</span>
                    <span class="hint">Sedang tampil di halaman publik.</span>
                </article>
            </div>

            <section class="article-surface">
                <div class="article-list-card__head">
                    <div>
                        <h2>Daftar Berita dan Artikel</h2>
                        <p>Gunakan pencarian utama untuk judul, lalu buka filter lanjutan untuk kategori, kegiatan, status, dan periode tayang.</p>
                    </div>
                </div>

                <div class="card-body pt-3">
                    <form method="get" class="article-filter-card">
                        <input type="hidden" name="tab" value="list">

                        <button
                            class="btn btn-outline-primary article-filter-mobile-toggle mb-3"
                            type="button"
                            data-article-filter-toggle="#adminArticleFilterPanel"
                            aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}"
                        >
                            <i class="bi bi-funnel"></i>Filter Daftar Artikel
                        </button>

                        <div class="article-filter-collapse {{ $hasActiveFilters ? 'show' : '' }}" id="adminArticleFilterPanel">
                            <div class="article-filter-grid">
                                <div class="article-filter-field article-filter-search">
                                    <label for="search">Cari judul</label>
                                    <input id="search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari judul artikel">
                                </div>

                                <div class="article-filter-field">
                                    <label for="content_category">Kategori</label>
                                    <select id="content_category" name="content_category" class="form-select">
                                        <option value="">Semua kategori</option>
                                        @foreach($contentCategories as $key => $label)
                                            <option value="{{ $key }}" @selected($contentCategory === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="article-filter-field">
                                    <label for="extracurricular_filter">Kegiatan</label>
                                    <select id="extracurricular_filter" name="extracurricular_id" class="form-select">
                                        <option value="">Semua kegiatan</option>
                                        @foreach($extracurriculars as $item)
                                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="article-filter-field">
                                    <label for="status_filter">Status</label>
                                    <select id="status_filter" name="status" class="form-select">
                                        <option value="">Semua status</option>
                                        @foreach($publicationStatuses as $key => $label)
                                            <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="article-filter-field">
                                    <label for="published_from">Tayang dari</label>
                                    <input id="published_from" type="date" name="published_from" value="{{ $publishedFrom }}" class="form-control">
                                </div>

                                <div class="article-filter-field">
                                    <label for="published_until">Sampai</label>
                                    <input id="published_until" type="date" name="published_until" value="{{ $publishedUntil }}" class="form-control">
                                </div>

                                <div class="article-filter-actions">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search"></i>Cari
                                    </button>
                                    <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-repeat"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if($articles->isEmpty())
                        <div class="empty-state article-empty-state">
                            <div class="icon"><i class="bi bi-newspaper"></i></div>
                            <p class="mb-2">Belum ada berita atau artikel. Mulai tulis konten untuk halaman publik.</p>
                            <a href="{{ $writeUrl }}#article-create-panel" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i>Tulis Artikel
                            </a>
                        </div>
                    @else
                        <div class="desktop-table table-responsive d-none d-md-block article-table-wrap">
                            <table class="table table-striped table-compact align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Thumbnail</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Kegiatan</th>
                                    <th>Pembuat</th>
                                    <th>Status</th>
                                    <th>Tanggal Tayang</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($articles as $article)
                                    <tr>
                                        <td>
                                            @if($article->cover_image_url)
                                                <img src="{{ $article->cover_image_url }}" alt="{{ $article->image_alt_text_label }}" class="article-table-thumb" loading="lazy">
                                            @else
                                                <span class="article-table-thumb article-placeholder-thumb"><i class="bi bi-image"></i></span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="article-title-cell">
                                                <div class="article-title-cell__title">{{ $article->title }}</div>
                                                <div class="article-title-cell__excerpt">{{ \Illuminate\Support\Str::limit($article->excerpt ?: strip_tags($article->content ?? ''), 110) }}</div>
                                                <div class="article-inline-meta">
                                                    @if($article->is_featured)
                                                        <span><i class="bi bi-stars"></i>Unggulan</span>
                                                    @endif
                                                    <span><i class="bi bi-link-45deg"></i>{{ $article->slug }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $article->content_category_label }}</td>
                                        <td>{{ $article->extracurricular?->name ?? 'Umum' }}</td>
                                        <td>{{ $article->publisher?->name ?? '-' }}</td>
                                        <td><span class="badge" data-status="{{ $article->display_status }}">{{ $article->display_status }}</span></td>
                                        <td>
                                            {{ optional($article->publish_at)->translatedFormat('d M Y H:i') ?? '-' }}
                                            @if($article->expires_at)
                                                <div class="small text-muted">Berakhir {{ $article->expires_at->translatedFormat('d M Y H:i') }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex align-items-center gap-2 flex-wrap justify-content-end">
                                                <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i>Detail</a>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-label="Buka aksi artikel">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        @if($article->is_publicly_visible)
                                                            <li><a class="dropdown-item" href="{{ route('public.articles.show', $article->slug) }}" target="_blank"><i class="bi bi-globe2"></i>Lihat Halaman Publik</a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="{{ route('admin.articles.preview', $article) }}" target="_blank"><i class="bi bi-display"></i>Pratinjau</a></li>
                                                        <li><a class="dropdown-item" href="{{ route('admin.articles.edit', $article) }}"><i class="bi bi-pencil-square"></i>Edit</a></li>
                                                        <li>
                                                            <form method="post" action="{{ route('admin.articles.duplicate', $article) }}">
                                                                @csrf
                                                                <button class="dropdown-item" type="submit"><i class="bi bi-copy"></i>Duplikasi</button>
                                                            </form>
                                                        </li>
                                                        @if($article->publication_status !== \App\Models\Article::STATUS_PUBLISHED)
                                                            <li>
                                                                <form method="post" action="{{ route('admin.articles.publish', $article) }}">
                                                                    @csrf
                                                                    @method('patch')
                                                                    <button class="dropdown-item" type="submit"><i class="bi bi-send-check"></i>Publikasikan</button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        @if($article->publication_status === \App\Models\Article::STATUS_PUBLISHED)
                                                            <li>
                                                                <form method="post" action="{{ route('admin.articles.unpublish', $article) }}" class="article-confirmable-form" data-confirm-title="Tarik publikasi?" data-confirm-message="Artikel akan berhenti tampil pada halaman publik. Lanjutkan?" data-confirm-submit-label="Tarik Publikasi" data-confirm-variant="warning">
                                                                    @csrf
                                                                    @method('patch')
                                                                    <button class="dropdown-item" type="submit"><i class="bi bi-arrow-counterclockwise"></i>Tarik Publikasi</button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        @if($article->publication_status !== \App\Models\Article::STATUS_ARCHIVED)
                                                            <li>
                                                                <form method="post" action="{{ route('admin.articles.archive', $article) }}" class="article-confirmable-form" data-confirm-title="Arsipkan artikel?" data-confirm-message="Artikel akan dipindahkan ke arsip dan tidak tampil publik. Lanjutkan?" data-confirm-submit-label="Arsipkan" data-confirm-variant="warning">
                                                                    @csrf
                                                                    @method('patch')
                                                                    <button class="dropdown-item" type="submit"><i class="bi bi-archive"></i>Arsipkan</button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form method="post" action="{{ route('admin.articles.destroy', $article) }}" class="article-confirmable-form" data-confirm-title="Hapus artikel?" data-confirm-message="Artikel ini akan dihapus permanen dan tidak bisa dikembalikan. Lanjutkan?" data-confirm-submit-label="Hapus Permanen" data-confirm-variant="danger">
                                                                @csrf
                                                                @method('delete')
                                                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mobile-stack-table d-md-none">
                            @foreach($articles as $article)
                                <div class="mobile-data-card">
                                    <div class="d-flex gap-3 align-items-start">
                                        @if($article->cover_image_url)
                                            <img src="{{ $article->cover_image_url }}" alt="{{ $article->image_alt_text_label }}" class="article-card-thumb" loading="lazy">
                                        @else
                                            <span class="article-card-thumb article-placeholder-thumb"><i class="bi bi-image"></i></span>
                                        @endif
                                        <div class="flex-fill min-width-0">
                                            <div class="d-flex justify-content-between gap-2 align-items-start">
                                                <h3 class="mobile-data-card-title mb-1">{{ $article->title }}</h3>
                                                <span class="badge" data-status="{{ $article->display_status }}">{{ $article->display_status }}</span>
                                            </div>
                                            <div class="article-title-cell__excerpt mb-2">{{ \Illuminate\Support\Str::limit($article->excerpt ?: strip_tags($article->content ?? ''), 96) }}</div>
                                            <div class="article-inline-meta">
                                                <span><i class="bi bi-bookmark-star"></i>{{ $article->content_category_label }}</span>
                                                <span><i class="bi bi-diagram-3"></i>{{ $article->extracurricular?->name ?? 'Umum' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mobile-data-list mt-3">
                                        <div><span class="mobile-data-item-label">Pembuat</span><span class="mobile-data-item-value">{{ $article->publisher?->name ?? '-' }}</span></div>
                                        <div><span class="mobile-data-item-label">Tayang</span><span class="mobile-data-item-value">{{ optional($article->publish_at)->translatedFormat('d M Y H:i') ?? '-' }}</span></div>
                                    </div>
                                    <div class="mobile-data-card-actions">
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i>Detail</a>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-label="Buka aksi artikel">
                                                <i class="bi bi-three-dots"></i>Aksi
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if($article->is_publicly_visible)
                                                    <li><a class="dropdown-item" href="{{ route('public.articles.show', $article->slug) }}" target="_blank"><i class="bi bi-globe2"></i>Lihat Halaman Publik</a></li>
                                                @endif
                                                <li><a class="dropdown-item" href="{{ route('admin.articles.preview', $article) }}" target="_blank"><i class="bi bi-display"></i>Pratinjau</a></li>
                                                <li><a class="dropdown-item" href="{{ route('admin.articles.edit', $article) }}"><i class="bi bi-pencil-square"></i>Edit</a></li>
                                                <li>
                                                    <form method="post" action="{{ route('admin.articles.duplicate', $article) }}">
                                                        @csrf
                                                        <button class="dropdown-item" type="submit"><i class="bi bi-copy"></i>Duplikasi</button>
                                                    </form>
                                                </li>
                                                @if($article->publication_status !== \App\Models\Article::STATUS_PUBLISHED)
                                                    <li>
                                                        <form method="post" action="{{ route('admin.articles.publish', $article) }}">
                                                            @csrf
                                                            @method('patch')
                                                            <button class="dropdown-item" type="submit"><i class="bi bi-send-check"></i>Publikasikan</button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if($article->publication_status === \App\Models\Article::STATUS_PUBLISHED)
                                                    <li>
                                                        <form method="post" action="{{ route('admin.articles.unpublish', $article) }}" class="article-confirmable-form" data-confirm-title="Tarik publikasi?" data-confirm-message="Artikel akan berhenti tampil pada halaman publik. Lanjutkan?" data-confirm-submit-label="Tarik Publikasi" data-confirm-variant="warning">
                                                            @csrf
                                                            @method('patch')
                                                            <button class="dropdown-item" type="submit"><i class="bi bi-arrow-counterclockwise"></i>Tarik Publikasi</button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if($article->publication_status !== \App\Models\Article::STATUS_ARCHIVED)
                                                    <li>
                                                        <form method="post" action="{{ route('admin.articles.archive', $article) }}" class="article-confirmable-form" data-confirm-title="Arsipkan artikel?" data-confirm-message="Artikel akan dipindahkan ke arsip dan tidak tampil publik. Lanjutkan?" data-confirm-submit-label="Arsipkan" data-confirm-variant="warning">
                                                            @csrf
                                                            @method('patch')
                                                            <button class="dropdown-item" type="submit"><i class="bi bi-archive"></i>Arsipkan</button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="post" action="{{ route('admin.articles.destroy', $article) }}" class="article-confirmable-form" data-confirm-title="Hapus artikel?" data-confirm-message="Artikel ini akan dihapus permanen dan tidak bisa dikembalikan. Lanjutkan?" data-confirm-submit-label="Hapus Permanen" data-confirm-variant="danger">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">{{ $articles->links() }}</div>
                    @endif
                </div>
            </section>
        @else
            <section class="article-surface article-surface--padded" id="article-create-panel">
                <div class="article-editor-shell__head">
                    <div>
                        <h2>Tulis Artikel</h2>
                        <p>Isi artikel baru dengan susunan data, media, SEO sederhana, dan aksi publikasi yang lebih tertata.</p>
                    </div>
                    <a href="{{ $listUrl }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>Kembali ke daftar
                    </a>
                </div>

                <div class="card-body pt-3">
                    <form method="post" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('admin.articles._form', [
                            'article' => null,
                            'extracurriculars' => $extracurriculars,
                            'contentCategories' => $contentCategories,
                            'publicationStatuses' => $publicationStatuses,
                        ])
                    </form>
                </div>
            </section>
        @endif
    </div>
@endsection
