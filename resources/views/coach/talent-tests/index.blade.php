@extends('layouts.app')

@section('page_title', 'Tes Bakat')
@section('page_subtitle', 'Kelola jadwal tes, peserta, dan hasil penilaian awal dengan alur yang lebih ringkas.')

@push('styles')
    <style>
        .talent-index-grid {
            display: grid;
            gap: 1rem;
        }

        .talent-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .talent-summary-card {
            border: 1px solid #dbe5f0;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 16px 32px rgba(17, 38, 68, 0.06);
            padding: 1rem 1.05rem 0.95rem;
            min-height: 142px;
            display: flex;
            flex-direction: column;
        }

        .talent-summary-card__label {
            display: block;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6f839c;
            margin-bottom: 0.45rem;
        }

        .talent-summary-card__value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #193252;
            line-height: 1;
        }

        .talent-summary-card__hint {
            margin-top: auto;
            padding-top: 0.55rem;
            font-size: 0.82rem;
            color: #69809a;
        }

        .talent-filter-form {
            align-items: end;
        }

        .talent-filter-actions {
            display: flex;
            gap: 0.65rem;
            align-items: center;
            justify-content: flex-end;
            min-width: 0;
        }

        .talent-filter-actions .btn {
            flex: 1 1 0;
            min-width: 0;
        }

        .talent-filter-card .form-label {
            white-space: nowrap;
        }

        .talent-filter-card,
        .talent-list-card {
            border: 1px solid #dbe5f0;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 16px 32px rgba(17, 38, 68, 0.05);
        }

        .talent-filter-card .card-body,
        .talent-list-card .card-body {
            padding: 1rem 1.15rem;
        }

        .talent-list-card .card-header {
            padding: 1rem 1.15rem 0.9rem;
            background: transparent;
            border-bottom: 1px solid #e7eef6;
        }

        .talent-list-card .card-header h2 {
            margin: 0 0 0.2rem;
            font-size: 1rem;
            font-weight: 800;
        }

        .talent-list-card .card-header p {
            margin: 0;
            color: #6a8099;
            font-size: 0.82rem;
            max-width: 780px;
        }

        .talent-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.42rem 0.72rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
            border: 1px solid transparent;
        }

        .talent-status-badge[data-status="draft"] {
            color: #8f5a00;
            background: #fff5d8;
            border-color: #efd08d;
        }

        .talent-status-badge[data-status="scheduled"] {
            color: #1d5bd2;
            background: #edf4ff;
            border-color: #bfd3fb;
        }

        .talent-status-badge[data-status="ongoing"] {
            color: #0d6b59;
            background: #e6fbf4;
            border-color: #a9e4d0;
        }

        .talent-status-badge[data-status="completed"] {
            color: #4d627b;
            background: #f3f7fb;
            border-color: #d8e2ec;
        }

        .talent-status-badge[data-status="cancelled"] {
            color: #9b3340;
            background: #ffe6ea;
            border-color: #f0b2bc;
        }

        .talent-list-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
        }

        .talent-participant-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.38rem;
            padding: 0.42rem 0.72rem;
            border-radius: 999px;
            border: 1px solid #dbe5f0;
            background: #f6f9fc;
            color: #27415f;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .talent-table-title {
            font-weight: 700;
            color: #1d314f;
        }

        .talent-table-subtitle {
            display: block;
            margin-top: 0.2rem;
            font-size: 0.8rem;
            color: #6b8098;
        }

        .talent-table-date {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            color: #27415f;
            font-size: 0.88rem;
        }

        .talent-mobile-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .talent-mobile-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.6rem;
        }

        .talent-mobile-summary div {
            min-width: 0;
        }

        .talent-mobile-summary span {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #72869f;
            margin-bottom: 0.18rem;
        }

        .talent-mobile-summary p {
            margin: 0;
            color: #243d5d;
            font-size: 0.88rem;
        }

        @media (max-width: 991.98px) {
            .talent-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .talent-filter-actions {
                justify-content: stretch;
            }
        }

        @media (max-width: 767.98px) {
            .talent-summary-grid {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }

            .talent-summary-card {
                min-height: 128px;
            }

            .talent-filter-card .card-body,
            .talent-list-card .card-body,
            .talent-list-card .card-header {
                padding-inline: 0.95rem;
            }

            .talent-filter-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.6rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="talent-index-grid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="form-actions">
                <a href="{{ route('coach.talent-tests.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Buat Tes Bakat</a>
                <a href="{{ route('coach.talent-test-aspects.index') }}" class="btn btn-outline-primary"><i class="bi bi-list-check"></i>Aspek Penilaian</a>
            </div>
        </div>

        <div class="talent-summary-grid">
            <div class="talent-summary-card">
                <span class="talent-summary-card__label">Tes Mendatang</span>
                <div class="talent-summary-card__value">{{ $summary['upcoming'] }}</div>
                <div class="talent-summary-card__hint">Tes yang sudah dijadwalkan.</div>
            </div>
            <div class="talent-summary-card">
                <span class="talent-summary-card__label">Sedang Dinilai</span>
                <div class="talent-summary-card__value">{{ $summary['grading'] }}</div>
                <div class="talent-summary-card__hint">Masih ada hasil draft yang dikerjakan.</div>
            </div>
            <div class="talent-summary-card">
                <span class="talent-summary-card__label">Hasil Belum Dipublikasikan</span>
                <div class="talent-summary-card__value">{{ $summary['unpublished'] }}</div>
                <div class="talent-summary-card__hint">Jumlah hasil peserta yang masih draft.</div>
            </div>
            <div class="talent-summary-card">
                <span class="talent-summary-card__label">Tes Selesai</span>
                <div class="talent-summary-card__value">{{ $summary['completed'] }}</div>
                <div class="talent-summary-card__hint">Tes yang sudah selesai diproses.</div>
            </div>
        </div>

        <div class="talent-filter-card card">
            <div class="card-body">
                <form class="toolbar-grid talent-filter-form" method="get" action="{{ route('coach.talent-tests.index') }}">
                    <div class="toolbar-col-3">
                        <label class="form-label" for="talent_search">Cari nama tes</label>
                        <input id="talent_search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama tes bakat">
                    </div>
                    <div class="toolbar-col-3">
                        <label class="form-label" for="talent_extracurricular_filter">Ekstrakurikuler</label>
                        <select id="talent_extracurricular_filter" name="extracurricular_id" class="form-select">
                            <option value="">Semua ekstrakurikuler</option>
                            @foreach($extracurriculars as $item)
                                <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="toolbar-col-2">
                        <label class="form-label" for="talent_status_filter">Status</label>
                        <select id="talent_status_filter" name="status" class="form-select">
                            <option value="">Semua status</option>
                            <option value="draft" @selected($status === 'draft')>Draft</option>
                            <option value="scheduled" @selected($status === 'scheduled')>Dijadwalkan</option>
                            <option value="ongoing" @selected($status === 'ongoing')>Sedang Berlangsung</option>
                            <option value="completed" @selected($status === 'completed')>Selesai</option>
                            <option value="cancelled" @selected($status === 'cancelled')>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="toolbar-col-2">
                        <label class="form-label" for="talent_period_filter">Periode</label>
                        <select id="talent_period_filter" name="period" class="form-select">
                            <option value="">Semua periode</option>
                            <option value="today" @selected($period === 'today')>Hari ini</option>
                            <option value="week" @selected($period === 'week')>Minggu ini</option>
                            <option value="month" @selected($period === 'month')>Bulan ini</option>
                        </select>
                    </div>
                    <div class="toolbar-col-2">
                        <div class="talent-filter-actions">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan</button>
                            <a href="{{ route('coach.talent-tests.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="talent-list-card card">
            <div class="card-header d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h2>Daftar Tes Bakat</h2>
                    <p>Gunakan tombol Kelola untuk mengisi hasil, lalu tindakan lain dipindahkan ke menu tiga titik agar tabel tetap ringkas.</p>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="desktop-table table-responsive d-none d-md-block">
                    <table class="table table-striped table-compact mb-0">
                        <thead>
                        <tr>
                            <th>Nama Tes</th>
                            <th>Ekstrakurikuler</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Peserta</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tests as $test)
                            <tr>
                                <td>
                                    <span class="talent-table-title">{{ $test->title }}</span>
                                    <span class="talent-table-subtitle">{{ $test->location ?: 'Lokasi belum ditentukan' }}</span>
                                </td>
                                <td>{{ $test->extracurricular->name ?? '-' }}</td>
                                <td>
                                    <div class="talent-table-date">
                                        <span>{{ optional($test->activity_date)->translatedFormat('d M Y') ?? '-' }}</span>
                                        <small>{{ \Illuminate\Support\Str::substr((string) $test->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $test->end_time, 0, 5) }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="talent-list-meta">
                                        <span class="talent-status-badge" data-status="{{ $test->coach_status_key }}">{{ $test->coach_status_label }}</span>
                                        @if($test->has_unpublished_results)
                                            <span class="badge badge-status-warning">Belum dipublikasikan</span>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="talent-participant-pill"><i class="bi bi-people"></i>{{ $test->participant_count_label }} peserta</span></td>
                                <td class="text-end table-action-col">
                                    <div class="table-inline-actions table-inline-actions--compact justify-content-end">
                                        <a href="{{ route('coach.talent-tests.manage', $test) }}" class="btn btn-sm btn-primary action-button-compact">
                                            <i class="bi bi-clipboard-data"></i>
                                            <span class="d-none d-lg-inline">{{ $test->coach_status_key === 'completed' ? 'Kelola Hasil' : 'Kelola' }}</span>
                                        </a>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu tindakan">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item talent-detail-trigger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#talentTestDetailModal"
                                                        data-title="{{ $test->title }}"
                                                        data-extracurricular="{{ $test->extracurricular->name ?? '-' }}"
                                                        data-date="{{ optional($test->activity_date)->translatedFormat('d F Y') ?? '-' }}"
                                                        data-time="{{ \Illuminate\Support\Str::substr((string) $test->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $test->end_time, 0, 5) }}"
                                                        data-location="{{ $test->location ?: 'Lokasi belum ditentukan' }}"
                                                        data-status="{{ $test->coach_status_label }}"
                                                        data-participants="{{ $test->participant_count_label }}"
                                                        data-equipment="{{ $test->equipment ?: 'Belum ada peralatan khusus' }}"
                                                        data-instructions="{{ $test->instructions ?: 'Belum ada instruksi tambahan' }}"
                                                    >
                                                        <i class="bi bi-eye me-2"></i>Lihat Detail
                                                    </button>
                                                </li>
                                                @if(in_array($test->coach_status_key, ['draft', 'scheduled'], true))
                                                    <li><a href="{{ route('coach.talent-tests.edit', $test) }}" class="dropdown-item"><i class="bi bi-pencil-square me-2"></i>Edit Jadwal</a></li>
                                                @endif
                                                @if($test->coach_status_key === 'completed')
                                                    <li><a href="{{ route('coach.talent-tests.manage', $test) }}" class="dropdown-item"><i class="bi bi-bar-chart-line me-2"></i>Lihat Rekap</a></li>
                                                @endif
                                                <li>
                                                    <form method="post" action="{{ route('coach.talent-tests.duplicate', $test) }}">
                                                        @csrf
                                                        <button class="dropdown-item" type="submit"><i class="bi bi-files me-2"></i>Duplikasi Tes</button>
                                                    </form>
                                                </li>
                                                @if(in_array($test->coach_status_key, ['draft', 'scheduled'], true))
                                                    <li>
                                                        <form method="post" action="{{ route('coach.talent-tests.cancel', $test) }}" onsubmit="return confirm('Batalkan tes bakat ini?')">
                                                            @csrf
                                                            @method('patch')
                                                            <button class="dropdown-item text-danger" type="submit"><i class="bi bi-x-circle me-2"></i>Batalkan Tes</button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="icon"><i class="bi bi-clipboard2-pulse"></i></div>
                                        <p class="mb-0">Belum ada jadwal tes bakat.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mobile-stack-table d-md-none">
                    @forelse($tests as $test)
                        <div class="mobile-data-card">
                            <div class="mobile-data-card-header">
                                <div>
                                    <h3 class="mobile-data-card-title">{{ $test->title }}</h3>
                                    <div class="small text-muted">{{ $test->extracurricular->name ?? '-' }}</div>
                                </div>
                                <span class="talent-status-badge" data-status="{{ $test->coach_status_key }}">{{ $test->coach_status_label }}</span>
                            </div>

                            <div class="talent-mobile-summary mb-3">
                                <div>
                                    <span>Tanggal</span>
                                    <p>{{ optional($test->activity_date)->translatedFormat('d M Y') ?? '-' }}</p>
                                </div>
                                <div>
                                    <span>Waktu</span>
                                    <p>{{ \Illuminate\Support\Str::substr((string) $test->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $test->end_time, 0, 5) }}</p>
                                </div>
                                <div>
                                    <span>Peserta</span>
                                    <p>{{ $test->participant_count_label }} peserta</p>
                                </div>
                                <div>
                                    <span>Lokasi</span>
                                    <p>{{ $test->location ?: 'Belum ditentukan' }}</p>
                                </div>
                            </div>

                            <div class="talent-mobile-actions">
                                <a href="{{ route('coach.talent-tests.manage', $test) }}" class="btn btn-sm btn-primary action-button-compact">
                                    <i class="bi bi-clipboard-data"></i>{{ $test->coach_status_key === 'completed' ? 'Kelola Hasil' : 'Kelola' }}
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                        <li>
                                            <button
                                                type="button"
                                                class="dropdown-item talent-detail-trigger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#talentTestDetailModal"
                                                data-title="{{ $test->title }}"
                                                data-extracurricular="{{ $test->extracurricular->name ?? '-' }}"
                                                data-date="{{ optional($test->activity_date)->translatedFormat('d F Y') ?? '-' }}"
                                                data-time="{{ \Illuminate\Support\Str::substr((string) $test->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $test->end_time, 0, 5) }}"
                                                data-location="{{ $test->location ?: 'Lokasi belum ditentukan' }}"
                                                data-status="{{ $test->coach_status_label }}"
                                                data-participants="{{ $test->participant_count_label }}"
                                                data-equipment="{{ $test->equipment ?: 'Belum ada peralatan khusus' }}"
                                                data-instructions="{{ $test->instructions ?: 'Belum ada instruksi tambahan' }}"
                                            >Lihat Detail</button>
                                        </li>
                                        @if(in_array($test->coach_status_key, ['draft', 'scheduled'], true))
                                            <li><a href="{{ route('coach.talent-tests.edit', $test) }}" class="dropdown-item">Edit Jadwal</a></li>
                                        @endif
                                        @if($test->coach_status_key === 'completed')
                                            <li><a href="{{ route('coach.talent-tests.manage', $test) }}" class="dropdown-item">Lihat Rekap</a></li>
                                        @endif
                                        <li>
                                            <form method="post" action="{{ route('coach.talent-tests.duplicate', $test) }}">
                                                @csrf
                                                <button class="dropdown-item" type="submit">Duplikasi Tes</button>
                                            </form>
                                        </li>
                                        @if(in_array($test->coach_status_key, ['draft', 'scheduled'], true))
                                            <li>
                                                <form method="post" action="{{ route('coach.talent-tests.cancel', $test) }}" onsubmit="return confirm('Batalkan tes bakat ini?')">
                                                    @csrf
                                                    @method('patch')
                                                    <button class="dropdown-item text-danger" type="submit">Batalkan Tes</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="icon"><i class="bi bi-clipboard2-pulse"></i></div>
                            <p class="mb-0">Belum ada jadwal tes bakat.</p>
                        </div>
                    @endforelse
                </div>

                <div class="pt-3">{{ $tests->links() }}</div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="talentTestDetailModal" tabindex="-1" aria-labelledby="talentTestDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content verification-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="h5 mb-1" id="talentTestDetailModalLabel">Detail Tes Bakat</h2>
                        <p class="text-muted mb-0" id="talentTestDetailModalMeta">Ringkasan jadwal tes bakat</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="verification-modal__summary mb-3">
                        <div class="data-point"><div class="data-point-label">Ekstrakurikuler</div><p class="data-point-value mb-0" id="talentTestDetailExtracurricular">-</p></div>
                        <div class="data-point"><div class="data-point-label">Tanggal</div><p class="data-point-value mb-0" id="talentTestDetailDate">-</p></div>
                        <div class="data-point"><div class="data-point-label">Waktu</div><p class="data-point-value mb-0" id="talentTestDetailTime">-</p></div>
                        <div class="data-point"><div class="data-point-label">Lokasi</div><p class="data-point-value mb-0" id="talentTestDetailLocation">-</p></div>
                        <div class="data-point"><div class="data-point-label">Status</div><p class="data-point-value mb-0" id="talentTestDetailStatus">-</p></div>
                        <div class="data-point"><div class="data-point-label">Peserta</div><p class="data-point-value mb-0" id="talentTestDetailParticipants">-</p></div>
                    </div>
                    <div class="info-item mb-3">
                        <div class="title mb-2" id="talentTestDetailTitle">-</div>
                        <p class="mb-0 text-muted" id="talentTestDetailEquipment">-</p>
                    </div>
                    <div class="info-item">
                        <div class="title mb-2">Instruksi Singkat</div>
                        <p class="mb-0" id="talentTestDetailInstructions">-</p>
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
            const detailModal = document.getElementById('talentTestDetailModal');

            detailModal?.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) return;

                const mapping = {
                    title: 'talentTestDetailTitle',
                    extracurricular: 'talentTestDetailExtracurricular',
                    date: 'talentTestDetailDate',
                    time: 'talentTestDetailTime',
                    location: 'talentTestDetailLocation',
                    status: 'talentTestDetailStatus',
                    participants: 'talentTestDetailParticipants',
                    equipment: 'talentTestDetailEquipment',
                    instructions: 'talentTestDetailInstructions',
                };

                Object.entries(mapping).forEach(([key, id]) => {
                    const node = document.getElementById(id);
                    if (node) {
                        node.textContent = trigger.dataset[key] || '-';
                    }
                });
            });
        });
    </script>
@endpush
