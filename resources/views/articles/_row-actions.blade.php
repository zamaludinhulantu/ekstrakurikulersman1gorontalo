<div class="dropdown">
    <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu konten {{ $article->title }}">
        <i class="bi bi-three-dots-vertical"></i>@if($mobile ?? false)<span class="ms-1">Aksi</span>@endif
    </button>
    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
        @if($article->is_publicly_visible)
            <li><a class="dropdown-item" href="{{ route('public.articles.show', $article->slug) }}" target="_blank"><i class="bi bi-globe2 me-2"></i>Lihat Publik</a></li>
        @endif
        <li><a class="dropdown-item" href="{{ route($routePrefix.'.preview', $article) }}" target="_blank"><i class="bi bi-display me-2"></i>Pratinjau</a></li>
        <li><a class="dropdown-item" href="{{ route($routePrefix.'.edit', $article) }}"><i class="bi bi-pencil-square me-2"></i>Detail dan Edit</a></li>
        <li><form method="post" action="{{ route($routePrefix.'.duplicate', $article) }}">@csrf<button class="dropdown-item" type="submit"><i class="bi bi-copy me-2"></i>Duplikasi</button></form></li>

        @if(! in_array($article->publication_status, [\App\Models\Article::STATUS_PUBLISHED, \App\Models\Article::STATUS_ARCHIVED], true))
            <li><form method="post" action="{{ route($routePrefix.'.publish', $article) }}">@csrf @method('patch')<button class="dropdown-item" type="submit"><i class="bi bi-send-check me-2"></i>Publikasikan</button></form></li>
        @endif
        @if($article->publication_status === \App\Models\Article::STATUS_SCHEDULED)
            <li><form method="post" action="{{ route($routePrefix.'.unpublish', $article) }}">@csrf @method('patch')<button class="dropdown-item" type="submit"><i class="bi bi-calendar-x me-2"></i>Batalkan Jadwal</button></form></li>
        @elseif($article->publication_status === \App\Models\Article::STATUS_PUBLISHED)
            <li><form method="post" action="{{ route($routePrefix.'.unpublish', $article) }}" class="article-confirmable-form" data-confirm-title="Tarik publikasi?" data-confirm-message="Konten akan berhenti tampil pada halaman publik." data-confirm-submit-label="Tarik Publikasi" data-confirm-variant="warning">@csrf @method('patch')<button class="dropdown-item" type="submit"><i class="bi bi-arrow-counterclockwise me-2"></i>Tarik Publikasi</button></form></li>
        @elseif($article->publication_status === \App\Models\Article::STATUS_ARCHIVED)
            <li><form method="post" action="{{ route($routePrefix.'.unpublish', $article) }}">@csrf @method('patch')<button class="dropdown-item" type="submit"><i class="bi bi-arrow-counterclockwise me-2"></i>Pulihkan ke Draft</button></form></li>
        @endif

        @if($article->publication_status !== \App\Models\Article::STATUS_ARCHIVED)
            <li><form method="post" action="{{ route($routePrefix.'.archive', $article) }}" class="article-confirmable-form" data-confirm-title="Arsipkan konten?" data-confirm-message="Konten tidak akan tampil pada halaman publik." data-confirm-submit-label="Arsipkan" data-confirm-variant="warning">@csrf @method('patch')<button class="dropdown-item" type="submit"><i class="bi bi-archive me-2"></i>Arsipkan</button></form></li>
        @endif

        @if(in_array($article->publication_status, [\App\Models\Article::STATUS_DRAFT, \App\Models\Article::STATUS_ARCHIVED], true))
            <li><hr class="dropdown-divider"></li>
            <li><form method="post" action="{{ route($routePrefix.'.destroy', $article) }}" class="article-confirmable-form" data-confirm-title="Hapus konten?" data-confirm-message="Konten dan media yang tidak digunakan akan dihapus permanen." data-confirm-submit-label="Hapus Permanen" data-confirm-variant="danger">@csrf @method('delete')<button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Hapus</button></form></li>
        @endif
    </ul>
</div>
