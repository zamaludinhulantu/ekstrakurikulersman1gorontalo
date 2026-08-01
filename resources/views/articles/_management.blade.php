@php
    $isWriteTab = $tab === 'write';
    $hasFilters = collect($filters)->except(['tab', 'sort', 'direction', 'per_page'])->filter(fn ($value) => filled($value))->isNotEmpty();
    $hasAdvancedFilters = filled($filters['extracurricular_id'])
        || filled($filters['author_id'])
        || filled($filters['published_from'])
        || filled($filters['published_until'])
        || filled($filters['image'])
        || $filters['per_page'] !== 10;
    $categoryLabels = $contentCategories;
    $statusLabels = $publicationStatuses;
    $imageLabels = ['with' => 'Dengan thumbnail', 'without' => 'Tanpa thumbnail'];
    $sortUrl = fn (string $column) => route($routePrefix.'.index', [
        ...request()->except(['page', 'sort', 'direction']),
        'tab' => 'list',
        'sort' => $column,
        'direction' => $filters['sort'] === $column && $filters['direction'] === 'asc' ? 'desc' : 'asc',
    ]);
@endphp

@include('articles._management-assets')

<div class="article-workspace article-management-page">
    <div class="article-management-toolbar">
        <div>
            <h2>{{ $isWriteTab ? 'Buat Konten' : 'Daftar Konten' }}</h2>
            <p>{{ $isWriteTab ? 'Tulis dan atur publikasi tanpa memuat editor pada halaman daftar.' : 'Berita, artikel, prestasi, liputan, dan informasi publik.' }}</p>
        </div>
        <div class="article-management-toolbar__actions">
            <a href="{{ route('public.articles.index') }}" class="btn btn-outline-secondary" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i>Lihat Publik
            </a>
            @if($isWriteTab)
                <a href="{{ $listUrl }}" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i>Kembali ke Daftar</a>
            @else
                <a href="{{ $writeUrl }}#article-create-panel" class="btn btn-primary"><i class="bi bi-pencil-square"></i>Buat Konten</a>
            @endif
        </div>
    </div>

    @if(! $isWriteTab)
        <section class="article-management-stats" aria-label="Statistik konten">
            <a href="{{ route($routePrefix.'.index') }}" class="article-management-stat"><span>Total Konten</span><strong>{{ $statistics['total'] }}</strong></a>
            <a href="{{ route($routePrefix.'.index', ['status' => 'draft']) }}" class="article-management-stat is-draft"><span>Draft</span><strong>{{ $statistics['draft'] }}</strong></a>
            <a href="{{ route($routePrefix.'.index', ['status' => 'scheduled']) }}" class="article-management-stat is-scheduled"><span>Terjadwal</span><strong>{{ $statistics['scheduled'] }}</strong></a>
            <a href="{{ route($routePrefix.'.index', ['status' => 'published']) }}" class="article-management-stat is-published"><span>Dipublikasikan</span><strong>{{ $statistics['published'] }}</strong></a>
            <a href="{{ route($routePrefix.'.index', ['status' => 'archived']) }}" class="article-management-stat is-archived"><span>Diarsipkan</span><strong>{{ $statistics['archived'] }}</strong></a>
            <div class="article-management-stat is-inactive"><span>Berakhir/Nonaktif</span><strong>{{ $statistics['expired'] + $statistics['inactive'] }}</strong></div>
        </section>

        <section class="article-surface article-management-list">
            <form method="get" action="{{ route($routePrefix.'.index') }}" class="article-management-filter">
                <input type="hidden" name="tab" value="list">
                <div class="article-management-filter__main">
                    <div class="article-management-search">
                        <label class="form-label" for="article_search">Cari Konten</label>
                        <input id="article_search" name="search" value="{{ $filters['search'] }}" class="form-control" maxlength="120" placeholder="Cari judul, ringkasan, atau isi">
                    </div>
                    <div>
                        <label class="form-label" for="article_category">Kategori</label>
                        <select id="article_category" name="content_category" class="form-select">
                            <option value="">Semua kategori</option>
                            @foreach($categoryLabels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['content_category'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="article_status">Status</label>
                        <select id="article_status" name="status" class="form-select">
                            <option value="">Semua status</option>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i>Cari</button>
                    <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-secondary">Reset</a>
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#articleAdvancedFilters" aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}">
                        <i class="bi bi-sliders"></i>Filter
                    </button>
                </div>

                <div class="collapse @if($hasAdvancedFilters) show @endif" id="articleAdvancedFilters">
                    <div class="article-management-filter__advanced">
                        <div>
                            <label class="form-label" for="article_activity">Kegiatan</label>
                            <select id="article_activity" name="extracurricular_id" class="form-select">
                                <option value="">Semua kegiatan</option>
                                @foreach($extracurriculars as $item)
                                    <option value="{{ $item->id }}" @selected($filters['extracurricular_id'] === $item->id)>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($allowsGeneralContent)
                            <div>
                                <label class="form-label" for="article_author">Penulis</label>
                                <select id="article_author" name="author_id" class="form-select">
                                    <option value="">Semua penulis</option>
                                    @foreach($authors as $author)
                                        <option value="{{ $author->id }}" @selected($filters['author_id'] === $author->id)>{{ $author->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div><label class="form-label" for="article_from">Tayang Dari</label><input id="article_from" type="date" name="published_from" value="{{ $filters['published_from'] }}" class="form-control"></div>
                        <div><label class="form-label" for="article_until">Sampai</label><input id="article_until" type="date" name="published_until" value="{{ $filters['published_until'] }}" class="form-control"></div>
                        <div>
                            <label class="form-label" for="article_image">Thumbnail</label>
                            <select id="article_image" name="image" class="form-select">
                                <option value="">Semua media</option>
                                @foreach($imageLabels as $value => $label)<option value="{{ $value }}" @selected($filters['image'] === $value)>{{ $label }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="article_per_page">Per Halaman</label>
                            <select id="article_per_page" name="per_page" class="form-select">
                                @foreach([10, 20, 50, 100] as $size)<option value="{{ $size }}" @selected($filters['per_page'] === $size)>{{ $size }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </form>

            @if($hasFilters)
                <div class="article-management-chips">
                    @foreach([
                        'search' => $filters['search'],
                        'content_category' => $categoryLabels[$filters['content_category']] ?? '',
                        'status' => $statusLabels[$filters['status']] ?? '',
                        'extracurricular_id' => data_get($extracurriculars->firstWhere('id', $filters['extracurricular_id']), 'name'),
                        'author_id' => data_get($authors->firstWhere('id', $filters['author_id']), 'name'),
                        'published_from' => $filters['published_from'],
                        'published_until' => $filters['published_until'],
                        'image' => $imageLabels[$filters['image']] ?? '',
                    ] as $key => $value)
                        @if(filled($value))
                            <a href="{{ route($routePrefix.'.index', request()->except([$key, 'page'])) }}" class="article-management-chip">
                                {{ $value }} <span aria-hidden="true">&times;</span><span class="visually-hidden">Hapus filter</span>
                            </a>
                        @endif
                    @endforeach
                    <a href="{{ route($routePrefix.'.index') }}" class="article-management-clear">Hapus semua filter</a>
                </div>
            @endif

            <div class="desktop-table table-responsive d-none d-md-block article-management-table-wrap">
                <table class="table table-hover table-compact align-middle mb-0 article-management-table">
                    <thead><tr>
                        <th><a href="{{ $sortUrl('title') }}">Konten <i class="bi bi-arrow-down-up"></i></a></th>
                        <th>Kategori</th>
                        <th><a href="{{ $sortUrl('author') }}">Penulis <i class="bi bi-arrow-down-up"></i></a></th>
                        <th><a href="{{ $sortUrl('publication_status') }}">Status <i class="bi bi-arrow-down-up"></i></a></th>
                        <th class="text-center table-action-col table-action-col--compact">Aksi</th>
                    </tr></thead>
                    <tbody>
                    @forelse($articles as $article)
                        <tr>
                            <td>
                                <div class="article-management-content">
                                    <div class="article-management-thumb-wrap">
                                        @if($article->cover_image_url)
                                            <img src="{{ $article->cover_image_url }}" alt="{{ $article->image_alt_text_label }}" class="article-management-thumb" width="72" height="48" loading="lazy" decoding="async" data-article-list-image>
                                            <span class="article-management-thumb article-management-thumb--placeholder" hidden data-article-list-fallback><i class="bi bi-image"></i></span>
                                        @else
                                            <span class="article-management-thumb article-management-thumb--placeholder"><i class="bi bi-image"></i></span>
                                        @endif
                                    </div>
                                    <div class="article-management-content__copy">
                                        <strong title="{{ $article->title }}">{{ \Illuminate\Support\Str::limit($article->title, 30) }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td><span class="article-management-category">{{ $article->content_category_label }}</span><small>{{ $article->extracurricular?->name ?? 'Umum' }}</small></td>
                            <td><strong class="article-management-author">{{ $article->publisher?->name ?? '-' }}</strong><small>{{ $article->publisher?->roleLabel() ?? '-' }}</small></td>
                            <td>@include('partials.articles.status-badge')</td>
                            <td class="text-center table-action-col table-action-col--compact">
                                <div class="table-inline-actions table-inline-actions--compact justify-content-center">
                                    @include('articles._row-actions', ['article' => $article, 'routePrefix' => $routePrefix])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state article-management-empty"><div class="icon"><i class="bi bi-newspaper"></i></div><p class="mb-2">{{ $hasFilters ? 'Tidak ada konten yang sesuai dengan filter.' : 'Belum ada berita atau artikel.' }}</p>@if($hasFilters)<a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-primary btn-sm">Reset Filter</a>@else<a href="{{ $writeUrl }}" class="btn btn-primary btn-sm">Buat Konten</a>@endif</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mobile-stack-table article-management-mobile">
                @forelse($articles as $article)
                    <article class="mobile-data-card">
                        <div class="article-management-mobile__head">
                            @if($article->cover_image_url)
                                <img src="{{ $article->cover_image_url }}" alt="{{ $article->image_alt_text_label }}" class="article-management-mobile__image" width="320" height="180" loading="lazy" decoding="async">
                            @else
                                <span class="article-management-mobile__image article-management-thumb--placeholder"><i class="bi bi-image"></i></span>
                            @endif
                            <div><h3>{{ $article->title }}</h3><p>{{ \Illuminate\Support\Str::limit($article->excerpt ?: strip_tags($article->content ?? ''), 100) }}</p></div>
                        </div>
                        <div class="article-management-mobile__meta">
                            <span>{{ $article->content_category_label }} · {{ $article->extracurricular?->name ?? 'Umum' }}</span>
                            @include('partials.articles.status-badge')
                            <span>{{ $article->publish_at?->translatedFormat('d M Y') ?? 'Belum tayang' }} · {{ $article->publisher?->name ?? '-' }}</span>
                        </div>
                        <div class="mobile-data-card-actions">
                            @include('articles._row-actions', ['article' => $article, 'routePrefix' => $routePrefix, 'mobile' => true])
                        </div>
                    </article>
                @empty
                    <div class="empty-state"><div class="icon"><i class="bi bi-newspaper"></i></div><p class="mb-0">{{ $hasFilters ? 'Tidak ada hasil yang sesuai.' : 'Belum ada konten.' }}</p></div>
                @endforelse
            </div>

            <div class="article-management-pagination">
                <span>Menampilkan {{ $articles->firstItem() ?? 0 }}-{{ $articles->lastItem() ?? 0 }} dari {{ $articles->total() }} konten</span>
                @if($articles->hasPages()){{ $articles->links() }}@endif
            </div>
        </section>
    @else
        <section class="article-surface article-surface--padded" id="article-create-panel">
            <div class="article-editor-shell__head">
                <div><h2>Buat Konten</h2><p>Isi informasi, media, publikasi, dan SEO pada form berikut.</p></div>
            </div>
            <div class="card-body pt-3">
                <form method="post" action="{{ route($routePrefix.'.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include($allowsGeneralContent ? 'admin.articles._form' : 'coach.articles._form', [
                        'article' => null,
                        'extracurriculars' => $extracurriculars,
                        'contentCategories' => $contentCategories,
                        'publicationStatuses' => \App\Models\Article::publicationStatuses(),
                    ])
                </form>
            </div>
        </section>
    @endif
</div>
