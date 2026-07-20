@extends('layouts.app')

@section('page_title', 'Pengumuman Pembina')
@section('page_subtitle', 'Kelola pengumuman untuk anggota ekstrakurikuler binaan dengan alur yang lebih ringkas.')

@php
    $activeTab = $activeTab ?? 'list';
@endphp

@push('styles')
    <style>
        .coach-announcement-page {
            display: grid;
            gap: 1rem;
        }

        .coach-announcement-panel,
        .coach-announcement-list {
            border: 1px solid #dbe5f0;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.05);
        }

        .coach-announcement-panel {
            padding: 1rem 1.15rem;
        }

        .coach-announcement-list {
            overflow: hidden;
        }

        .coach-announcement-list__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.9rem;
            padding: 1rem 1.15rem 0.9rem;
            border-bottom: 1px solid #e8eef5;
        }

        .coach-announcement-list__header h2 {
            margin: 0 0 0.25rem;
            font-size: 1rem;
            font-weight: 800;
        }

        .coach-announcement-list__header p {
            margin: 0;
            color: #667b93;
            font-size: 0.82rem;
        }

        .coach-announcement-list__body {
            padding: 1rem 1.15rem 1.15rem;
        }

        .announcement-priority-badge,
        .announcement-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.42rem 0.72rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
            border: 1px solid transparent;
        }

        .announcement-priority-badge[data-priority="normal"] {
            color: #44617d;
            background: #f3f7fb;
            border-color: #dce6f0;
        }

        .announcement-priority-badge[data-priority="important"] {
            color: #8f5a00;
            background: #fff5d8;
            border-color: #efd08d;
        }

        .announcement-priority-badge[data-priority="urgent"] {
            color: #a63a3a;
            background: #ffe7e7;
            border-color: #f3b1b1;
        }

        .announcement-status-badge[data-status="Draft"] {
            color: #8f5a00;
            background: #fff5d8;
            border-color: #efd08d;
        }

        .announcement-status-badge[data-status="Terjadwal"] {
            color: #2453ad;
            background: #edf4ff;
            border-color: #bed2fb;
        }

        .announcement-status-badge[data-status="Dipublikasikan"] {
            color: #177245;
            background: #eaf8f0;
            border-color: #bde4cf;
        }

        .announcement-status-badge[data-status="Berakhir"],
        .announcement-status-badge[data-status="Dinonaktifkan"] {
            color: #6f7f93;
            background: #f4f7fb;
            border-color: #dbe5f0;
        }

        @media (max-width: 767.98px) {
            .coach-announcement-panel,
            .coach-announcement-list__header,
            .coach-announcement-list__body {
                padding-inline: 0.95rem;
            }

            .coach-announcement-list__header {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <div class="coach-announcement-page">
        <div class="card">
            <div class="card-body toolbar-card">
                <div class="section-header-inline mb-3">
                    <div>
                        <h2>Kelola Pengumuman</h2>
                        <p>Pilih daftar atau form pembuatan agar halaman tetap ringkas.</p>
                    </div>
                    <a href="{{ route('coach.announcements.index', ['tab' => 'create']) }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i>Buat Pengumuman
                    </a>
                </div>

                <div class="tab-scroll-nav" role="tablist" aria-label="Kelola pengumuman pembina">
                    <button class="tab-scroll-nav__item @if($activeTab === 'list') is-active @endif" data-bs-toggle="tab" data-bs-target="#coach-announcement-list-tab" type="button" role="tab" aria-selected="{{ $activeTab === 'list' ? 'true' : 'false' }}">Daftar Pengumuman</button>
                    <button class="tab-scroll-nav__item @if($activeTab === 'create') is-active @endif" data-bs-toggle="tab" data-bs-target="#coach-announcement-create-tab" type="button" role="tab" aria-selected="{{ $activeTab === 'create' ? 'true' : 'false' }}">Buat Pengumuman</button>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade @if($activeTab === 'list') show active @endif" id="coach-announcement-list-tab" role="tabpanel" tabindex="0">
                <div class="coach-announcement-list">
                    <div class="coach-announcement-list__header">
                        <div>
                            <h2>Daftar Pengumuman</h2>
                            <p>Lihat pengumuman yang sudah dibuat, lalu edit, publikasikan, nonaktifkan, atau hapus dari menu tindakan.</p>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#coachAnnouncementFilterPanel" aria-expanded="{{ $hasFilters ? 'true' : 'false' }}">
                            <i class="bi bi-funnel"></i>Filter Data
                        </button>
                    </div>

                    <div class="coach-announcement-list__body">
                        <div class="collapse @if($hasFilters) show @endif" id="coachAnnouncementFilterPanel">
                            <form class="toolbar-grid mb-3" method="get" action="{{ route('coach.announcements.index') }}">
                                <input type="hidden" name="tab" value="list">
                                <div class="toolbar-col-4">
                                    <label class="form-label" for="announcement_search">Cari judul</label>
                                    <input id="announcement_search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari judul pengumuman">
                                </div>
                                <div class="toolbar-col-3">
                                    <label class="form-label" for="announcement_extracurricular_id">Ekstrakurikuler</label>
                                    <select id="announcement_extracurricular_id" name="extracurricular_id" class="form-select">
                                        <option value="">Semua ekstrakurikuler</option>
                                        <option value="all_managed" @selected($extracurricularId === 'all_managed')>Semua ekstrakurikuler binaan</option>
                                        @foreach($extracurriculars as $item)
                                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="toolbar-col-2">
                                    <label class="form-label" for="announcement_status">Status</label>
                                    <select id="announcement_status" name="status" class="form-select">
                                        <option value="">Semua status</option>
                                        <option value="draft" @selected($status === 'draft')>Draft</option>
                                        <option value="scheduled" @selected($status === 'scheduled')>Terjadwal</option>
                                        <option value="published" @selected($status === 'published')>Dipublikasikan</option>
                                        <option value="expired" @selected($status === 'expired')>Berakhir</option>
                                        <option value="inactive" @selected($status === 'inactive')>Dinonaktifkan</option>
                                    </select>
                                </div>
                                <div class="toolbar-col-2">
                                    <label class="form-label" for="announcement_priority">Prioritas</label>
                                    <select id="announcement_priority" name="priority" class="form-select">
                                        <option value="">Semua prioritas</option>
                                        <option value="normal" @selected($priority === 'normal')>Biasa</option>
                                        <option value="important" @selected($priority === 'important')>Penting</option>
                                        <option value="urgent" @selected($priority === 'urgent')>Mendesak</option>
                                    </select>
                                </div>
                                <div class="toolbar-col-2">
                                    <label class="form-label" for="announcement_period">Periode</label>
                                    <select id="announcement_period" name="period" class="form-select">
                                        <option value="">Semua periode</option>
                                        <option value="today" @selected($period === 'today')>Hari ini</option>
                                        <option value="week" @selected($period === 'week')>7 hari terakhir</option>
                                    </select>
                                </div>
                                <div class="toolbar-col-2">
                                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i>Terapkan</button>
                                </div>
                                <div class="toolbar-col-2">
                                    <a href="{{ route('coach.announcements.index', ['tab' => 'list']) }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a>
                                </div>
                            </form>
                        </div>

                        <div class="desktop-table table-responsive">
                            <table class="table table-striped table-compact mb-0">
                                <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Ekstrakurikuler</th>
                                    <th>Prioritas</th>
                                    <th>Status</th>
                                    <th>Tanggal Tayang</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($announcements as $announcement)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $announcement->title }}</div>
                                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($announcement->content, 96) }}</div>
                                        </td>
                                        <td>{{ $announcement->extracurricular->name ?? 'Semua ekstrakurikuler binaan' }}</td>
                                        <td><span class="announcement-priority-badge" data-priority="{{ $announcement->priority }}">{{ $announcement->priority_label }}</span></td>
                                        <td><span class="announcement-status-badge" data-status="{{ $announcement->display_status }}">{{ $announcement->display_status }}</span></td>
                                        <td>{{ $announcement->publish_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                        <td class="text-end table-action-col">
                                            <div class="table-inline-actions table-inline-actions--compact justify-content-end">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary action-button-compact coach-announcement-detail-trigger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#coachAnnouncementDetailModal"
                                                    data-title="{{ $announcement->title }}"
                                                    data-extracurricular="{{ $announcement->extracurricular->name ?? 'Semua ekstrakurikuler binaan' }}"
                                                    data-priority="{{ $announcement->priority_label }}"
                                                    data-status="{{ $announcement->display_status }}"
                                                    data-publish-at="{{ $announcement->publish_at?->translatedFormat('d F Y H:i') ?? '-' }}"
                                                    data-ends-at="{{ $announcement->ends_at?->translatedFormat('d F Y H:i') ?? 'Tidak dibatasi' }}"
                                                    data-content="{{ $announcement->content }}"
                                                    data-attachment="{{ $announcement->attachment_name ?? 'Tidak ada lampiran' }}"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                    <span class="d-none d-md-inline">Detail</span>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                                        <li><a href="{{ route('coach.announcements.edit', $announcement) }}" class="dropdown-item"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                                        @if($announcement->publication_status !== \App\Models\Announcement::STATUS_PUBLISHED)
                                                            <li>
                                                                <form method="post" action="{{ route('coach.announcements.publish', $announcement) }}">
                                                                    @csrf
                                                                    @method('patch')
                                                                    <button class="dropdown-item" type="submit"><i class="bi bi-send-check me-2"></i>Publikasikan</button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        @if($announcement->publication_status !== \App\Models\Announcement::STATUS_INACTIVE || $announcement->is_active)
                                                            <li>
                                                                <form method="post" action="{{ route('coach.announcements.deactivate', $announcement) }}">
                                                                    @csrf
                                                                    @method('patch')
                                                                    <button class="dropdown-item" type="submit"><i class="bi bi-pause-circle me-2"></i>Nonaktifkan</button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <form method="post" action="{{ route('coach.announcements.destroy', $announcement) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                                                                @csrf
                                                                @method('delete')
                                                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-trash me-2"></i>Hapus</button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <div class="icon"><i class="bi bi-megaphone"></i></div>
                                                <p class="mb-2">Belum ada pengumuman. Buat pengumuman untuk menyampaikan informasi kepada anggota ekstrakurikuler.</p>
                                                <a href="{{ route('coach.announcements.index', ['tab' => 'create']) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i>Buat Pengumuman</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mobile-stack-table d-md-none">
                            @forelse($announcements as $announcement)
                                <div class="mobile-data-card">
                                    <div class="mobile-data-card-header">
                                        <div>
                                            <h3 class="mobile-data-card-title">{{ $announcement->title }}</h3>
                                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($announcement->content, 92) }}</div>
                                        </div>
                                        <span class="announcement-priority-badge" data-priority="{{ $announcement->priority }}">{{ $announcement->priority_label }}</span>
                                    </div>
                                    <div class="mobile-data-list mb-3">
                                        <div><span class="mobile-data-item-label">Ekstrakurikuler</span><p class="mobile-data-item-value">{{ $announcement->extracurricular->name ?? 'Semua ekstrakurikuler binaan' }}</p></div>
                                        <div><span class="mobile-data-item-label">Status</span><p class="mobile-data-item-value"><span class="announcement-status-badge" data-status="{{ $announcement->display_status }}">{{ $announcement->display_status }}</span></p></div>
                                        <div><span class="mobile-data-item-label">Tanggal tayang</span><p class="mobile-data-item-value">{{ $announcement->publish_at?->translatedFormat('d M Y H:i') ?? '-' }}</p></div>
                                    </div>
                                    <div class="table-inline-actions table-inline-actions--compact">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary action-button-compact coach-announcement-detail-trigger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#coachAnnouncementDetailModal"
                                            data-title="{{ $announcement->title }}"
                                            data-extracurricular="{{ $announcement->extracurricular->name ?? 'Semua ekstrakurikuler binaan' }}"
                                            data-priority="{{ $announcement->priority_label }}"
                                            data-status="{{ $announcement->display_status }}"
                                            data-publish-at="{{ $announcement->publish_at?->translatedFormat('d F Y H:i') ?? '-' }}"
                                            data-ends-at="{{ $announcement->ends_at?->translatedFormat('d F Y H:i') ?? 'Tidak dibatasi' }}"
                                            data-content="{{ $announcement->content }}"
                                            data-attachment="{{ $announcement->attachment_name ?? 'Tidak ada lampiran' }}"
                                        >
                                            <i class="bi bi-eye"></i>Detail
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                                <li><a href="{{ route('coach.announcements.edit', $announcement) }}" class="dropdown-item">Edit</a></li>
                                                @if($announcement->publication_status !== \App\Models\Announcement::STATUS_PUBLISHED)
                                                    <li>
                                                        <form method="post" action="{{ route('coach.announcements.publish', $announcement) }}">
                                                            @csrf
                                                            @method('patch')
                                                            <button class="dropdown-item" type="submit">Publikasikan</button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if($announcement->publication_status !== \App\Models\Announcement::STATUS_INACTIVE || $announcement->is_active)
                                                    <li>
                                                        <form method="post" action="{{ route('coach.announcements.deactivate', $announcement) }}">
                                                            @csrf
                                                            @method('patch')
                                                            <button class="dropdown-item" type="submit">Nonaktifkan</button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li>
                                                    <form method="post" action="{{ route('coach.announcements.destroy', $announcement) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="dropdown-item text-danger" type="submit">Hapus</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-megaphone"></i></div>
                                    <p class="mb-2">Belum ada pengumuman. Buat pengumuman untuk menyampaikan informasi kepada anggota ekstrakurikuler.</p>
                                    <a href="{{ route('coach.announcements.index', ['tab' => 'create']) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i>Buat Pengumuman</a>
                                </div>
                            @endforelse
                        </div>

                        <div class="card-body px-0 pt-3 pb-0">{{ $announcements->links() }}</div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade @if($activeTab === 'create') show active @endif" id="coach-announcement-create-tab" role="tabpanel" tabindex="0">
                <div class="coach-announcement-panel">
                    <div class="section-header-inline mb-3">
                        <div>
                            <h2>Buat Pengumuman</h2>
                            <p>Atur target, prioritas, waktu tayang, dan status publikasi dengan lebih jelas.</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('coach.announcements.store') }}" class="row g-3" enctype="multipart/form-data" id="coachAnnouncementForm">
                        @csrf
                        <input type="hidden" name="active_tab" value="create">
                        <div class="col-md-4">
                            <label class="form-label" for="announcement_target_scope">Tujuan pengumuman</label>
                            <select id="announcement_target_scope" name="target_scope" class="form-select" required>
                                <option value="single" @selected(old('target_scope', 'single') === 'single')>Pilih ekstrakurikuler tujuan</option>
                                <option value="all_managed" @selected(old('target_scope') === 'all_managed')>Semua ekstrakurikuler binaan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="announcement_extracurricular_select">Ekstrakurikuler tujuan</label>
                            <select id="announcement_extracurricular_select" name="extracurricular_id" class="form-select">
                                <option value="">Pilih ekstrakurikuler tujuan</option>
                                @foreach($extracurriculars as $item)
                                    <option value="{{ $item->id }}" @selected((string) old('extracurricular_id') === (string) $item->id)>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="announcement_priority_select">Prioritas</label>
                            <select id="announcement_priority_select" name="priority" class="form-select" required>
                                <option value="normal" @selected(old('priority', 'normal') === 'normal')>Biasa</option>
                                <option value="important" @selected(old('priority') === 'important')>Penting</option>
                                <option value="urgent" @selected(old('priority') === 'urgent')>Mendesak</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="announcement_title">Judul pengumuman</label>
                            <input id="announcement_title" type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Contoh: Jadwal latihan dipindah" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="announcement_attachment">Lampiran opsional</label>
                            <input id="announcement_attachment" type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="helper-text">Format aman: PDF, JPG, JPEG, PNG. Maksimal 2 MB.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="announcement_content">Isi pengumuman</label>
                            <textarea id="announcement_content" name="content" class="form-control" rows="5" placeholder="Tulis isi pengumuman untuk siswa" required>{{ old('content') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="announcement_publication_action">Status publikasi</label>
                            <select id="announcement_publication_action" name="publication_action" class="form-select" required>
                                <option value="draft" @selected(old('publication_action', 'draft') === 'draft')>Simpan sebagai Draft</option>
                                <option value="published" @selected(old('publication_action') === 'published')>Publikasikan Sekarang</option>
                                <option value="scheduled" @selected(old('publication_action') === 'scheduled')>Jadwalkan Publikasi</option>
                            </select>
                        </div>
                        <div class="col-md-4 @if(old('publication_action') !== 'scheduled') d-none @endif" data-publish-schedule-group>
                            <label class="form-label" for="announcement_publish_date">Tanggal tayang</label>
                            <input id="announcement_publish_date" type="date" name="publish_date" value="{{ old('publish_date') }}" class="form-control">
                        </div>
                        <div class="col-md-4 @if(old('publication_action') !== 'scheduled') d-none @endif" data-publish-schedule-group>
                            <label class="form-label" for="announcement_publish_time">Jam tayang</label>
                            <input id="announcement_publish_time" type="time" name="publish_time" value="{{ old('publish_time') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="announcement_ends_at_date">Tanggal berakhir opsional</label>
                            <input id="announcement_ends_at_date" type="date" name="ends_at_date" value="{{ old('ends_at_date') }}" class="form-control">
                        </div>
                        <div class="col-12 @if(old('target_scope') !== 'all_managed') d-none @endif" data-confirm-all-managed>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="confirm_all_managed" value="1" id="confirmAllManaged" @checked(old('confirm_all_managed'))>
                                <label class="form-check-label" for="confirmAllManaged">Saya mengonfirmasi pengumuman ini akan dikirim ke semua ekstrakurikuler binaan.</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-actions justify-content-end">
                                <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan pengumuman..."><i class="bi bi-save"></i>Simpan Pengumuman</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="coachAnnouncementDetailModal" tabindex="-1" aria-labelledby="coachAnnouncementDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content verification-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="h5 mb-1" id="coachAnnouncementDetailModalLabel">Detail Pengumuman</h2>
                        <p class="text-muted mb-0" id="coachAnnouncementDetailMeta">Ringkasan pengumuman pembina</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="verification-modal__summary mb-3">
                        <div class="data-point"><div class="data-point-label">Ekstrakurikuler</div><p class="data-point-value mb-0" id="coachAnnouncementDetailExtracurricular">-</p></div>
                        <div class="data-point"><div class="data-point-label">Prioritas</div><p class="data-point-value mb-0" id="coachAnnouncementDetailPriority">-</p></div>
                        <div class="data-point"><div class="data-point-label">Status</div><p class="data-point-value mb-0" id="coachAnnouncementDetailStatus">-</p></div>
                        <div class="data-point"><div class="data-point-label">Tanggal Tayang</div><p class="data-point-value mb-0" id="coachAnnouncementDetailPublishAt">-</p></div>
                        <div class="data-point"><div class="data-point-label">Berakhir</div><p class="data-point-value mb-0" id="coachAnnouncementDetailEndsAt">-</p></div>
                        <div class="data-point"><div class="data-point-label">Lampiran</div><p class="data-point-value mb-0" id="coachAnnouncementDetailAttachment">-</p></div>
                    </div>
                    <div class="info-item">
                        <div class="title mb-2" id="coachAnnouncementDetailTitle">-</div>
                        <p class="mb-0" id="coachAnnouncementDetailContent">-</p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const targetScope = document.getElementById('announcement_target_scope');
            const extracurricularSelect = document.getElementById('announcement_extracurricular_select');
            const confirmAllManaged = document.querySelector('[data-confirm-all-managed]');
            const publicationAction = document.getElementById('announcement_publication_action');
            const scheduleGroups = document.querySelectorAll('[data-publish-schedule-group]');

            const syncTargetScope = () => {
                if (!targetScope || !extracurricularSelect || !confirmAllManaged) return;
                const isAllManaged = targetScope.value === 'all_managed';
                extracurricularSelect.disabled = isAllManaged;
                if (isAllManaged) {
                    extracurricularSelect.value = '';
                }
                confirmAllManaged.classList.toggle('d-none', !isAllManaged);
            };

            const syncPublicationAction = () => {
                const isScheduled = publicationAction && publicationAction.value === 'scheduled';
                scheduleGroups.forEach((group) => group.classList.toggle('d-none', !isScheduled));
            };

            targetScope?.addEventListener('change', syncTargetScope);
            publicationAction?.addEventListener('change', syncPublicationAction);
            syncTargetScope();
            syncPublicationAction();

            const detailModal = document.getElementById('coachAnnouncementDetailModal');
            detailModal?.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mappings = {
                    title: 'coachAnnouncementDetailTitle',
                    extracurricular: 'coachAnnouncementDetailExtracurricular',
                    priority: 'coachAnnouncementDetailPriority',
                    status: 'coachAnnouncementDetailStatus',
                    publishAt: 'coachAnnouncementDetailPublishAt',
                    endsAt: 'coachAnnouncementDetailEndsAt',
                    attachment: 'coachAnnouncementDetailAttachment',
                    content: 'coachAnnouncementDetailContent',
                };

                Object.entries(mappings).forEach(([key, elementId]) => {
                    const node = document.getElementById(elementId);
                    if (node) node.textContent = trigger.dataset[key] || '-';
                });
            });
        });
    </script>
@endpush
