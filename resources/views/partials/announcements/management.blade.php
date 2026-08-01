@php
    $hasFilters = collect($filters)->except(['sort', 'direction', 'per_page', 'form'])->filter(fn ($value) => filled($value))->isNotEmpty();
    $createOpen = $filters['form'] === 'create' || $errors->any();
    $statusLabels = [
        'draft' => 'Draft',
        'scheduled' => 'Terjadwal',
        'published' => 'Dipublikasikan',
        'inactive' => 'Dinonaktifkan',
        'expired' => 'Berakhir',
    ];
    $priorityLabels = ['normal' => 'Biasa', 'important' => 'Penting', 'urgent' => 'Mendesak'];
    $sortUrl = fn (string $column) => route($routePrefix.'.index', [
        ...request()->except(['page', 'sort', 'direction']),
        'sort' => $column,
        'direction' => $filters['sort'] === $column && $filters['direction'] === 'asc' ? 'desc' : 'asc',
    ]);
@endphp

<div class="announcement-management" data-announcement-page>
    <section class="announcement-summary" aria-label="Ringkasan pengumuman">
        <a href="{{ route($routePrefix.'.index') }}" class="announcement-stat">
            <span>Total</span><strong>{{ $statistics['total'] }}</strong>
        </a>
        <a href="{{ route($routePrefix.'.index', ['status' => 'published']) }}" class="announcement-stat is-published">
            <span>Aktif</span><strong>{{ $statistics['published'] }}</strong>
        </a>
        <a href="{{ route($routePrefix.'.index', ['status' => 'draft']) }}" class="announcement-stat is-draft">
            <span>Draft</span><strong>{{ $statistics['draft'] }}</strong>
        </a>
        <a href="{{ route($routePrefix.'.index', ['status' => 'scheduled']) }}" class="announcement-stat is-scheduled">
            <span>Terjadwal</span><strong>{{ $statistics['scheduled'] }}</strong>
        </a>
        <a href="{{ route($routePrefix.'.index', ['status' => 'inactive']) }}" class="announcement-stat is-inactive">
            <span>Berakhir/Nonaktif</span><strong>{{ $statistics['inactive'] }}</strong>
        </a>
    </section>

    <section class="announcement-surface">
        <div class="announcement-surface-header">
            <div>
                <h2>Daftar Pengumuman</h2>
                <p>{{ $roleLabel }} mengelola {{ $canTargetAllStudents ? 'informasi umum dan kegiatan' : 'informasi kegiatan binaan' }} dari halaman ini.</p>
            </div>
            <div class="announcement-header-actions">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#announcementFilterPanel" aria-expanded="{{ $hasFilters ? 'true' : 'false' }}">
                    <i class="bi bi-funnel"></i>Filter
                </button>
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#announcementCreatePanel" aria-expanded="{{ $createOpen ? 'true' : 'false' }}">
                    <i class="bi bi-plus-circle"></i>Buat Pengumuman
                </button>
            </div>
        </div>

        <div class="collapse @if($createOpen) show @endif" id="announcementCreatePanel">
            <div class="announcement-create-panel">
                <div class="announcement-panel-title">
                    <h3>Buat Pengumuman</h3>
                    <p>Isi target dan status publikasi sebelum menyimpan.</p>
                </div>
                @include('partials.announcements.form')
            </div>
        </div>

        <div class="collapse @if($hasFilters) show @endif" id="announcementFilterPanel">
            <form method="get" action="{{ route($routePrefix.'.index') }}" class="announcement-filter">
                <div class="announcement-filter-grid">
                    <div class="announcement-filter-search">
                        <label class="form-label" for="announcement_search">Cari Pengumuman</label>
                        <input id="announcement_search" name="search" value="{{ $filters['search'] }}" class="form-control" maxlength="120" placeholder="Cari judul atau isi">
                    </div>
                    <div>
                        <label class="form-label" for="announcement_target_filter">Target</label>
                        <select id="announcement_target_filter" name="target" class="form-select">
                            <option value="">Semua target</option>
                            @if($canTargetAllStudents)<option value="all" @selected($filters['target'] === 'all')>Semua siswa</option>@endif
                            <option value="activity" @selected($filters['target'] === 'activity')>Kegiatan tertentu</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="announcement_activity_filter">Kegiatan</label>
                        <select id="announcement_activity_filter" name="extracurricular_id" class="form-select">
                            <option value="">Semua kegiatan</option>
                            @foreach($extracurriculars as $item)
                                <option value="{{ $item->id }}" @selected($filters['extracurricular_id'] === $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="announcement_status_filter">Status</label>
                        <select id="announcement_status_filter" name="status" class="form-select">
                            <option value="">Semua status</option>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="announcement_priority_filter">Prioritas</label>
                        <select id="announcement_priority_filter" name="priority" class="form-select">
                            <option value="">Semua prioritas</option>
                            @foreach($priorityLabels as $value => $label)
                                <option value="{{ $value }}" @selected($filters['priority'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="announcement_date_from">Dari Tanggal</label>
                        <input id="announcement_date_from" type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label" for="announcement_date_to">Sampai Tanggal</label>
                        <input id="announcement_date_to" type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                    </div>
                    <div>
                        <label class="form-label" for="announcement_per_page">Per Halaman</label>
                        <select id="announcement_per_page" name="per_page" class="form-select">
                            @foreach([10, 20, 50] as $size)<option value="{{ $size }}" @selected($filters['per_page'] === $size)>{{ $size }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="announcement-filter-actions">
                    <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-secondary">Reset</a>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i>Terapkan</button>
                </div>
            </form>
        </div>

        @if($hasFilters)
            <div class="announcement-filter-chips" aria-label="Filter aktif">
                @foreach([
                    'search' => $filters['search'],
                    'target' => $filters['target'] === 'all' ? 'Semua siswa' : ($filters['target'] === 'activity' ? 'Kegiatan tertentu' : ''),
                    'extracurricular_id' => data_get($extracurriculars->firstWhere('id', $filters['extracurricular_id']), 'name'),
                    'status' => $statusLabels[$filters['status']] ?? '',
                    'priority' => $priorityLabels[$filters['priority']] ?? '',
                    'date_from' => $filters['date_from'],
                    'date_to' => $filters['date_to'],
                ] as $key => $value)
                    @if(filled($value))
                        <a class="announcement-filter-chip" href="{{ route($routePrefix.'.index', request()->except([$key, 'page'])) }}">
                            {{ $value }} <span aria-hidden="true">&times;</span><span class="visually-hidden">Hapus filter</span>
                        </a>
                    @endif
                @endforeach
                <a href="{{ route($routePrefix.'.index') }}" class="announcement-clear-filters">Hapus semua filter</a>
            </div>
        @endif

        <div class="desktop-table table-responsive announcement-table-wrap">
            <table class="table table-hover table-compact mb-0">
                <thead>
                <tr>
                    <th><a href="{{ $sortUrl('title') }}">Judul <i class="bi bi-arrow-down-up"></i></a></th>
                    <th>Target</th>
                    <th>Periode</th>
                    <th>Pembuat</th>
                    <th><a href="{{ $sortUrl('publication_status') }}">Status <i class="bi bi-arrow-down-up"></i></a></th>
                    <th><a href="{{ $sortUrl('updated_at') }}">Diubah <i class="bi bi-arrow-down-up"></i></a></th>
                    <th class="text-center table-action-col table-action-col--compact">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($announcements as $announcement)
                    @php
                        $targetLabel = $announcement->extracurricular?->name
                            ?? ($announcement->publisher?->hasRole(\App\Models\User::ROLE_COACH) ? 'Target lama tidak valid' : 'Semua siswa');
                    @endphp
                    <tr>
                        <td class="announcement-title-cell">
                            <strong>{{ $announcement->title }}</strong>
                            <span>{{ \Illuminate\Support\Str::limit($announcement->content, 110) }}</span>
                        </td>
                        <td><span class="announcement-target">{{ $targetLabel }}</span></td>
                        <td>
                            <span class="announcement-period">{{ $announcement->publish_at?->translatedFormat('d M Y, H:i') ?? 'Belum dijadwalkan' }}</span>
                            @if($announcement->ends_at)<small>s.d. {{ $announcement->ends_at->translatedFormat('d M Y') }}</small>@endif
                        </td>
                        <td>
                            <span class="announcement-publisher">{{ $announcement->publisher?->name ?? '-' }}</span>
                            <small>{{ $announcement->publisher?->roleLabel() ?? '-' }}</small>
                        </td>
                        <td>@include('partials.announcements.status-badge')</td>
                        <td><span class="announcement-updated">{{ $announcement->updated_at?->diffForHumans() }}</span></td>
                        <td class="text-center table-action-col table-action-col--compact">
                            <div class="table-inline-actions table-inline-actions--compact justify-content-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu pengumuman {{ $announcement->title }}">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                        <li>
                                            <button
                                                type="button"
                                                class="dropdown-item announcement-detail-trigger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#announcementDetailModal"
                                                data-title="{{ $announcement->title }}"
                                                data-content="{{ $announcement->content }}"
                                                data-target="{{ $targetLabel }}"
                                                data-status="{{ $announcement->display_status }}"
                                                data-priority="{{ $announcement->priority_label }}"
                                                data-publisher="{{ $announcement->publisher?->name ?? '-' }}"
                                                data-publish-at="{{ $announcement->publish_at?->translatedFormat('d F Y H:i') ?? 'Belum dijadwalkan' }}"
                                                data-ends-at="{{ $announcement->ends_at?->translatedFormat('d F Y H:i') ?? 'Tidak dibatasi' }}"
                                            ><i class="bi bi-eye me-2"></i>Detail</button>
                                        </li>
                                        <li><a class="dropdown-item" href="{{ route($routePrefix.'.edit', $announcement) }}"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                        @if($announcement->display_status !== 'Dipublikasikan')
                                            <li><form method="post" action="{{ route($routePrefix.'.publish', $announcement) }}">@csrf @method('patch')<button class="dropdown-item" type="submit"><i class="bi bi-send-check me-2"></i>Publikasikan</button></form></li>
                                        @endif
                                        @if($announcement->is_active)
                                            <li><form method="post" action="{{ route($routePrefix.'.deactivate', $announcement) }}">@csrf @method('patch')<button class="dropdown-item" type="submit"><i class="bi bi-pause-circle me-2"></i>Nonaktifkan</button></form></li>
                                        @endif
                                        @if($announcement->publication_status === \App\Models\Announcement::STATUS_DRAFT)
                                            <li><hr class="dropdown-divider"></li>
                                            <li><form method="post" action="{{ route($routePrefix.'.destroy', $announcement) }}" onsubmit="return confirm('Hapus draft pengumuman ini? Tindakan ini tidak dapat dibatalkan.')">@csrf @method('delete')<button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Hapus Draft</button></form></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <div class="icon"><i class="bi bi-megaphone"></i></div>
                            <p class="mb-2">{{ $hasFilters ? 'Tidak ada pengumuman yang sesuai dengan filter.' : 'Belum ada pengumuman yang dibuat.' }}</p>
                            @if($hasFilters)<a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-primary btn-sm">Reset Filter</a>@endif
                        </div>
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mobile-stack-table announcement-mobile-list">
            @forelse($announcements as $announcement)
                @php
                    $targetLabel = $announcement->extracurricular?->name
                        ?? ($announcement->publisher?->hasRole(\App\Models\User::ROLE_COACH) ? 'Target lama tidak valid' : 'Semua siswa');
                @endphp
                <article class="mobile-data-card">
                    <div class="mobile-data-card-header">
                        <div><h3 class="mobile-data-card-title">{{ $announcement->title }}</h3><p>{{ \Illuminate\Support\Str::limit($announcement->content, 100) }}</p></div>
                        @include('partials.announcements.status-badge')
                    </div>
                    <div class="announcement-mobile-meta">
                        <span><i class="bi bi-bullseye"></i>{{ $targetLabel }}</span>
                        <span><i class="bi bi-calendar3"></i>{{ $announcement->publish_at?->translatedFormat('d M Y') ?? 'Belum tayang' }}</span>
                        <span><i class="bi bi-person"></i>{{ $announcement->publisher?->name ?? '-' }}</span>
                    </div>
                    <div class="mobile-data-card-actions">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu pengumuman {{ $announcement->title }}">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                <li><button type="button" class="dropdown-item announcement-detail-trigger" data-bs-toggle="modal" data-bs-target="#announcementDetailModal" data-title="{{ $announcement->title }}" data-content="{{ $announcement->content }}" data-target="{{ $targetLabel }}" data-status="{{ $announcement->display_status }}" data-priority="{{ $announcement->priority_label }}" data-publisher="{{ $announcement->publisher?->name ?? '-' }}" data-publish-at="{{ $announcement->publish_at?->translatedFormat('d F Y H:i') ?? 'Belum dijadwalkan' }}" data-ends-at="{{ $announcement->ends_at?->translatedFormat('d F Y H:i') ?? 'Tidak dibatasi' }}"><i class="bi bi-eye me-2"></i>Detail</button></li>
                                <li><a class="dropdown-item" href="{{ route($routePrefix.'.edit', $announcement) }}"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                @if($announcement->display_status !== 'Dipublikasikan')
                                    <li><form method="post" action="{{ route($routePrefix.'.publish', $announcement) }}">@csrf @method('patch')<button class="dropdown-item" type="submit"><i class="bi bi-send-check me-2"></i>Publikasikan</button></form></li>
                                @endif
                                @if($announcement->is_active)
                                    <li><form method="post" action="{{ route($routePrefix.'.deactivate', $announcement) }}">@csrf @method('patch')<button class="dropdown-item" type="submit"><i class="bi bi-pause-circle me-2"></i>Nonaktifkan</button></form></li>
                                @endif
                                @if($announcement->publication_status === \App\Models\Announcement::STATUS_DRAFT)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form method="post" action="{{ route($routePrefix.'.destroy', $announcement) }}" onsubmit="return confirm('Hapus draft pengumuman ini? Tindakan ini tidak dapat dibatalkan.')">@csrf @method('delete')<button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Hapus Draft</button></form></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-state"><div class="icon"><i class="bi bi-megaphone"></i></div><p class="mb-0">{{ $hasFilters ? 'Tidak ada hasil yang sesuai.' : 'Belum ada pengumuman.' }}</p></div>
            @endforelse
        </div>

        @if($announcements->hasPages() || $announcements->total())
            <div class="announcement-pagination">
                <span>Menampilkan {{ $announcements->firstItem() ?? 0 }}-{{ $announcements->lastItem() ?? 0 }} dari {{ $announcements->total() }} pengumuman</span>
                {{ $announcements->links() }}
            </div>
        @endif
    </section>

    <div class="modal fade" id="announcementDetailModal" tabindex="-1" aria-labelledby="announcementDetailTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div><span class="announcement-modal-kicker">Detail Pengumuman</span><h2 class="modal-title h5" id="announcementDetailTitle">-</h2></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="announcement-detail-grid">
                        <div><span>Target</span><strong data-detail-target>-</strong></div>
                        <div><span>Status</span><strong data-detail-status>-</strong></div>
                        <div><span>Prioritas</span><strong data-detail-priority>-</strong></div>
                        <div><span>Pembuat</span><strong data-detail-publisher>-</strong></div>
                        <div><span>Mulai</span><strong data-detail-publish-at>-</strong></div>
                        <div><span>Berakhir</span><strong data-detail-ends-at>-</strong></div>
                    </div>
                    <p class="announcement-detail-content" data-detail-content>-</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="announcementPreviewModal" tabindex="-1" aria-labelledby="announcementPreviewTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><div><span class="announcement-modal-kicker">Pratinjau Siswa</span><h2 class="modal-title h5" id="announcementPreviewTitle">Judul pengumuman</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
                <div class="modal-body"><div class="announcement-preview-meta" data-preview-meta>-</div><p class="announcement-detail-content" data-preview-content>Isi pengumuman akan tampil di sini.</p></div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>
</div>
