@extends('layouts.app')

@section('page_title', 'Kelola Hasil Tes Bakat')
@section('page_subtitle', 'Kelola kehadiran, nilai, rekomendasi, dan publikasi hasil peserta dengan alur yang lebih fokus.')

@push('styles')
    <style>
        .talent-manage-grid {
            display: grid;
            gap: 1rem;
        }

        .talent-manage-summary {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .talent-manage-stat {
            border: 1px solid #dbe5f0;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 16px 32px rgba(17, 38, 68, 0.05);
            padding: 0.95rem 1rem;
        }

        .talent-manage-stat span {
            display: block;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #71849b;
            margin-bottom: 0.42rem;
        }

        .talent-manage-stat strong {
            display: block;
            font-size: 1.55rem;
            line-height: 1;
            color: #1e3454;
        }

        .talent-manage-stat small {
            display: block;
            margin-top: 0.4rem;
            color: #69809a;
            font-size: 0.8rem;
        }

        .talent-manage-layout {
            display: grid;
            grid-template-columns: minmax(300px, 360px) minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
        }

        .talent-panel-card {
            border: 1px solid #dbe5f0;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 16px 32px rgba(17, 38, 68, 0.05);
        }

        .talent-panel-card .card-body,
        .talent-panel-card .card-header {
            padding: 1rem 1.1rem;
        }

        .talent-panel-card .card-header {
            background: transparent;
            border-bottom: 1px solid #e7eef6;
        }

        .talent-panel-card .card-header h2,
        .talent-panel-card .card-header h3 {
            margin: 0 0 0.2rem;
            font-size: 1rem;
            font-weight: 800;
        }

        .talent-panel-card .card-header p {
            margin: 0;
            color: #6d829b;
            font-size: 0.82rem;
        }

        .talent-participant-tools {
            display: grid;
            gap: 0.75rem;
            margin-bottom: 0.95rem;
        }

        .talent-participant-list {
            display: grid;
            gap: 0.7rem;
            max-height: calc(100vh - 310px);
            overflow-y: auto;
            padding-right: 0.2rem;
        }

        .talent-participant-item {
            width: 100%;
            border: 1px solid #dde6f1;
            border-radius: 20px;
            background: #fff;
            padding: 0.9rem 0.95rem;
            text-align: left;
            transition: 0.2s ease;
        }

        .talent-participant-item.is-active {
            border-color: #8fb6ff;
            background: linear-gradient(180deg, rgba(236, 244, 255, 0.98), rgba(250, 252, 255, 0.98));
            box-shadow: 0 14px 28px rgba(40, 97, 204, 0.12);
        }

        .talent-participant-item.is-hidden {
            display: none;
        }

        .talent-participant-item__top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.55rem;
        }

        .talent-participant-item__title {
            font-weight: 800;
            color: #1f3555;
        }

        .talent-participant-item__subtitle {
            display: block;
            color: #68809a;
            font-size: 0.82rem;
            margin-top: 0.15rem;
        }

        .talent-participant-item__meta {
            display: grid;
            gap: 0.38rem;
            color: #516a86;
            font-size: 0.82rem;
        }

        .talent-participant-item__meta strong {
            color: #1f3555;
        }

        .talent-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-bottom: 0.55rem;
        }

        .talent-chip-row .badge {
            font-size: 0.75rem;
        }

        .talent-panel-stack {
            display: grid;
            gap: 1rem;
        }

        .talent-panel-section {
            display: none;
        }

        .talent-panel-section.is-active {
            display: block;
        }

        .talent-mobile-back {
            display: none;
        }

        .talent-tab-bar {
            display: flex;
            gap: 0.65rem;
            overflow-x: auto;
            padding-bottom: 0.1rem;
            margin-bottom: 1rem;
        }

        .talent-tab-button {
            border: 1px solid #d7e3f1;
            background: #fff;
            color: #36506f;
            border-radius: 999px;
            padding: 0.62rem 1rem;
            font-size: 0.84rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .talent-tab-button.is-active {
            background: #edf4ff;
            border-color: #9fc0ff;
            color: #1e5bd1;
        }

        .talent-help-text {
            margin: -0.2rem 0 0.9rem;
            color: #6d8198;
            font-size: 0.82rem;
        }

        .talent-field-grid {
            display: grid;
            gap: 0.9rem;
        }

        .talent-aspect-list {
            display: grid;
            gap: 0.85rem;
        }

        .talent-aspect-card {
            border: 1px solid #e2eaf3;
            border-radius: 18px;
            background: #fbfdff;
            padding: 0.9rem;
        }

        .talent-aspect-card__head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .talent-aspect-card__head strong {
            color: #1d3352;
        }

        .talent-aspect-card__head small {
            display: block;
            margin-top: 0.15rem;
            color: #6f839b;
        }

        .talent-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .talent-summary-box {
            border: 1px solid #e1e9f3;
            border-radius: 18px;
            background: #fbfdff;
            padding: 0.85rem 0.95rem;
        }

        .talent-summary-box span {
            display: block;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #7488a0;
            margin-bottom: 0.18rem;
        }

        .talent-summary-box p {
            margin: 0;
            color: #203655;
            font-weight: 700;
        }

        .talent-sticky-bar {
            position: sticky;
            bottom: 1rem;
            z-index: 12;
        }

        .talent-sticky-bar__inner {
            border: 1px solid #d7e3f2;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 20px 40px rgba(18, 40, 70, 0.12);
            padding: 0.9rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .talent-sticky-bar__meta {
            display: grid;
            gap: 0.2rem;
        }

        .talent-sticky-bar__meta strong {
            color: #1b3252;
        }

        .talent-sticky-bar__meta span,
        .talent-sticky-bar__meta small {
            color: #6c8097;
        }

        .talent-sticky-bar__actions {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }

        .talent-block-reason {
            color: #9b5f00;
            font-size: 0.79rem;
        }

        @media (max-width: 1199.98px) {
            .talent-manage-summary {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .talent-manage-layout {
                grid-template-columns: 1fr;
            }

            .talent-participant-list {
                max-height: none;
            }
        }

        @media (max-width: 767.98px) {
            .talent-manage-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
            }

            .talent-panel-card .card-body,
            .talent-panel-card .card-header {
                padding-inline: 0.95rem;
            }

            .talent-mobile-back {
                display: inline-flex;
            }

            .talent-sticky-bar__inner,
            .talent-sticky-bar__actions {
                display: grid;
                gap: 0.75rem;
            }

            .talent-sticky-bar__actions .btn {
                width: 100%;
            }

            .talent-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $attendanceClassMap = [
            'Hadir' => 'badge-status-success',
            'Tidak Hadir' => 'badge-status-danger',
            'Izin' => 'badge-status-warning',
            'Sakit' => 'badge-status-warning',
            'Belum Diisi' => 'badge-status-secondary',
        ];
    @endphp

    <div class="talent-manage-grid" data-talent-manage>
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <strong>{{ $talentTest->title }}</strong>
                        <div class="small text-muted">{{ $talentTest->extracurricular->name ?? '-' }}</div>
                    </div>
                    <div class="col-lg-3">
                        <div class="small text-muted">Jadwal</div>
                        <div>{{ optional($talentTest->activity_date)->translatedFormat('d M Y') }} | {{ \Illuminate\Support\Str::substr((string) $talentTest->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $talentTest->end_time, 0, 5) }}</div>
                    </div>
                    <div class="col-lg-3">
                        <div class="small text-muted">Lokasi</div>
                        <div>{{ $talentTest->location ?: 'Belum ditentukan' }}</div>
                    </div>
                    <div class="col-lg-2">
                        <div class="small text-muted">Status</div>
                        <div>{{ $talentTest->status === 'completed' ? 'Selesai' : ($talentTest->status === 'cancelled' ? 'Dibatalkan' : 'Dijadwalkan') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="talent-manage-summary">
            <div class="talent-manage-stat"><span>Total Peserta</span><strong>{{ $summary['total'] }}</strong><small>Peserta yang terdaftar pada tes ini.</small></div>
            <div class="talent-manage-stat"><span>Hadir</span><strong>{{ $summary['present'] }}</strong><small>Sudah tercatat hadir.</small></div>
            <div class="talent-manage-stat"><span>Belum Dinilai</span><strong>{{ $summary['pending'] }}</strong><small>Belum memiliki hasil.</small></div>
            <div class="talent-manage-stat"><span>Draft</span><strong>{{ $summary['draft'] }}</strong><small>Masih perlu dilengkapi.</small></div>
            <div class="talent-manage-stat"><span>Dipublikasikan</span><strong>{{ $summary['published'] }}</strong><small>Sudah terlihat untuk siswa.</small></div>
            <div class="talent-manage-stat"><span>Nilai Rata-rata</span><strong>{{ $summary['average'] ?? '—' }}</strong><small>Berdasarkan hasil yang sudah tersimpan.</small></div>
        </div>

        <form method="post" action="{{ route('coach.talent-tests.results.save', $talentTest) }}" id="talentManageForm">
            @csrf
            <input type="hidden" name="target_participant_id" id="targetParticipantId" value="{{ $activeParticipantId }}">

            <div class="talent-manage-layout">
                <aside class="talent-panel-card card">
                    <div class="card-header">
                        <h2>Daftar Peserta</h2>
                        <p>Cari dan pilih peserta untuk mulai mengisi hasil tes bakat.</p>
                    </div>
                    <div class="card-body">
                        @if($participants->isEmpty())
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-people"></i></div>
                                <p class="mb-0">Belum ada peserta pada tes ini.</p>
                            </div>
                        @else
                            <div class="talent-participant-tools">
                                <div>
                                    <label class="form-label" for="talentParticipantSearch">Cari peserta</label>
                                    <input id="talentParticipantSearch" type="text" class="form-control" placeholder="Cari nama atau kelas" data-participant-search>
                                </div>
                                <div>
                                    <label class="form-label" for="talentParticipantFilter">Filter peserta</label>
                                    <select id="talentParticipantFilter" class="form-select" data-participant-filter>
                                        <option value="all">Semua</option>
                                        <option value="pending">Belum Dinilai</option>
                                        <option value="draft">Draft</option>
                                        <option value="published">Dipublikasikan</option>
                                        <option value="absent">Tidak Hadir</option>
                                    </select>
                                </div>
                            </div>

                            <div class="talent-participant-list" id="talentParticipantList">
                                @foreach($participants as $index => $participant)
                                    @php
                                        $result = $resultsByStudent[$participant->student_id] ?? null;
                                    @endphp
                                    <button
                                        type="button"
                                        class="talent-participant-item @if($participant->id === $activeParticipantId || ($activeParticipantId === null && $index === 0)) is-active @endif"
                                        data-participant-button
                                        data-participant-id="{{ $participant->id }}"
                                        data-search-text="{{ strtolower(trim(($participant->student->user->name ?? '').' '.($participant->student->class_name ?? ''))) }}"
                                        data-filter-status="{{ $participant->result_status_filter }}"
                                        data-name="{{ $participant->student->user->name ?? '-' }}"
                                        data-filled="{{ $participant->filled_aspect_count }}"
                                        data-total="{{ $participant->total_aspect_count }}"
                                        data-ready="{{ $participant->is_publish_ready ? '1' : '0' }}"
                                        data-published="{{ $result?->status === 'published' || $result?->published_at ? '1' : '0' }}"
                                        data-reason="{{ $participant->publish_block_reason }}"
                                    >
                                        <div class="talent-participant-item__top">
                                            <div>
                                                <div class="talent-participant-item__title">{{ $participant->student->user->name ?? '-' }}</div>
                                                <span class="talent-participant-item__subtitle">{{ $participant->student->class_name ?: 'Kelas belum diatur' }}</span>
                                            </div>
                                            <span class="badge {{ $attendanceClassMap[$participant->attendance_label] ?? 'badge-status-secondary' }}">{{ $participant->attendance_label }}</span>
                                        </div>
                                        <div class="talent-chip-row">
                                            <span class="badge {{ $participant->result_status_class }}">{{ $participant->result_status_label }}</span>
                                            @if($participant->overall_score_label)
                                                <span class="badge badge-status-secondary">Nilai {{ $participant->overall_score_label }}</span>
                                            @endif
                                        </div>
                                        <div class="talent-participant-item__meta">
                                            <span>Aspek terisi: <strong>{{ $participant->filled_aspect_count }}/{{ $participant->total_aspect_count }}</strong></span>
                                            <span>{{ $participant->missing_aspect_count > 0 ? $participant->missing_aspect_count.' aspek belum diisi' : 'Semua aspek sudah diisi' }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </aside>

                <div class="talent-panel-stack">
                    @if($participants->isEmpty())
                        <div class="talent-panel-card card">
                            <div class="card-body">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-clipboard2-pulse"></i></div>
                                    <p class="mb-0">Belum ada peserta pada tes ini.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        @foreach($participants as $index => $participant)
                            @php
                                $result = $resultsByStudent[$participant->student_id] ?? null;
                                $itemMap = $result ? $result->items->keyBy('talent_test_aspect_id') : collect();
                                $attendanceStatus = old("participants.$index.attendance_status", $participant->attendance_status ?? 'pending');
                                $panelActive = $participant->id === $activeParticipantId || ($activeParticipantId === null && $index === 0);
                                $scoreValue = $result?->overall_score !== null ? number_format((float) $result->overall_score, 2, ',', '.') : null;
                                $scoreLabel = $participant->is_publish_ready && $scoreValue !== null ? 'Nilai Akhir' : ($scoreValue !== null ? 'Nilai Sementara' : 'Belum ada nilai');
                            @endphp
                            <section class="talent-panel-card card talent-panel-section @if($panelActive) is-active @endif" data-participant-panel="{{ $participant->id }}">
                                <div class="card-header d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary talent-mobile-back" data-mobile-back><i class="bi bi-arrow-left"></i></button>
                                            <h3 class="mb-0">{{ $participant->student->user->name ?? '-' }}</h3>
                                        </div>
                                        <p>{{ $participant->student->class_name ?: 'Kelas belum diatur' }} • {{ $participant->attendance_label }}</p>
                                    </div>
                                    <div class="form-actions">
                                        <span class="badge {{ $participant->result_status_class }}">{{ $participant->result_status_label }}</span>
                                        <button type="button" class="btn btn-outline-primary btn-sm profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $participant->registration) }}">
                                            <i class="bi bi-person-badge"></i>Lihat Profil
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="participants[{{ $index }}][participant_id]" value="{{ $participant->id }}">

                                    <div class="page-summary-banner mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="data-point">
                                                    <div class="data-point-label">Status Penilaian</div>
                                                    <div class="data-point-value">{{ $participant->result_status_label }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-point">
                                                    <div class="data-point-label">{{ $scoreLabel }}</div>
                                                    <div class="data-point-value">{{ $scoreValue ?? 'Belum ada' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="data-point">
                                                    <div class="data-point-label">Kelengkapan Aspek</div>
                                                    <div class="data-point-value">{{ $participant->filled_aspect_count }}/{{ $participant->total_aspect_count }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="talent-tab-bar" data-panel-tabs>
                                        <button type="button" class="talent-tab-button is-active" data-panel-tab-trigger="attendance-{{ $participant->id }}">Kehadiran</button>
                                        <button type="button" class="talent-tab-button" data-panel-tab-trigger="scoring-{{ $participant->id }}">Penilaian</button>
                                        <button type="button" class="talent-tab-button" data-panel-tab-trigger="recommendation-{{ $participant->id }}">Rekomendasi</button>
                                        <button type="button" class="talent-tab-button" data-panel-tab-trigger="summary-{{ $participant->id }}">Ringkasan</button>
                                    </div>

                                    <div data-panel-tab="attendance-{{ $participant->id }}">
                                        <p class="talent-help-text">Catat kehadiran peserta terlebih dahulu sebelum menyimpan hasil tes bakat.</p>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Status Kehadiran</label>
                                                <select name="participants[{{ $index }}][attendance_status]" class="form-select">
                                                    @foreach(['pending' => 'Belum Diisi', 'present' => 'Hadir', 'absent' => 'Tidak Hadir', 'sick' => 'Sakit', 'permission' => 'Izin'] as $value => $label)
                                                        <option value="{{ $value }}" @selected($attendanceStatus === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Catatan Kehadiran</label>
                                                <input type="text" name="participants[{{ $index }}][attendance_notes]" class="form-control" value="{{ old("participants.$index.attendance_notes", $participant->attendance_notes ?? '') }}" placeholder="Opsional, misalnya alasan izin atau catatan kedatangan">
                                            </div>
                                        </div>
                                    </div>

                                    <div data-panel-tab="scoring-{{ $participant->id }}" class="d-none">
                                        <p class="talent-help-text">Isi seluruh aspek penilaian untuk peserta yang hadir. Nilai akhir hanya dianggap lengkap setelah semua aspek terisi.</p>
                                        @if($aspects->isEmpty())
                                            <div class="alert alert-warning mb-0">Aspek penilaian untuk {{ $talentTest->extracurricular->name }} belum dibuat. Tambahkan dulu di menu aspek penilaian.</div>
                                        @else
                                            <div class="talent-aspect-list">
                                                @foreach($aspects as $aspect)
                                                    @php $item = $itemMap[$aspect->id] ?? null; @endphp
                                                    <div class="talent-aspect-card">
                                                        <div class="talent-aspect-card__head">
                                                            <div>
                                                                <strong>{{ $aspect->name }}</strong>
                                                                <small>Skor maksimal {{ rtrim(rtrim(number_format((float) $aspect->max_score, 2, '.', ''), '0'), '.') }}</small>
                                                            </div>
                                                            @php
                                                                $currentScore = old("participants.$index.scores.$aspect->id", $item->score ?? '');
                                                            @endphp
                                                            <span class="badge badge-status-secondary">
                                                                {{ $currentScore !== '' && $currentScore !== null ? 'Nilai '.number_format((float) $currentScore, 2, ',', '.') : 'Belum diisi' }}
                                                            </span>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Input Nilai</label>
                                                                <input type="number" step="0.01" min="0" max="{{ $aspect->max_score }}" name="participants[{{ $index }}][scores][{{ $aspect->id }}]" class="form-control" value="{{ $currentScore }}">
                                                            </div>
                                                            <div class="col-md-8">
                                                                <label class="form-label">Catatan per Aspek</label>
                                                                <input type="text" name="participants[{{ $index }}][score_notes][{{ $aspect->id }}]" class="form-control" value="{{ old("participants.$index.score_notes.$aspect->id", $item->notes ?? '') }}" placeholder="Opsional, misalnya teknik dasar sudah baik">
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div data-panel-tab="recommendation-{{ $participant->id }}" class="d-none">
                                        <p class="talent-help-text">Isi kategori kemampuan, kelompok latihan, dan rekomendasi pembina untuk peserta aktif.</p>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Kategori Kemampuan</label>
                                                <input type="text" name="participants[{{ $index }}][ability_category]" class="form-control" value="{{ old("participants.$index.ability_category", $result->ability_category ?? '') }}" placeholder="Contoh: Dasar, Menengah">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Kelompok Latihan</label>
                                                <input type="text" name="participants[{{ $index }}][training_group]" class="form-control" value="{{ old("participants.$index.training_group", $result->training_group ?? '') }}" placeholder="Contoh: Fundamental A">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Posisi atau Peran</label>
                                                <input type="text" name="participants[{{ $index }}][recommended_role]" class="form-control" value="{{ old("participants.$index.recommended_role", $result->recommended_role ?? '') }}" placeholder="Opsional">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Rekomendasi Pembina</label>
                                                <textarea name="participants[{{ $index }}][recommendation]" class="form-control" rows="3" placeholder="Saran latihan atau tindak lanjut untuk siswa">{{ old("participants.$index.recommendation", $result->recommendation ?? '') }}</textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Catatan Umum</label>
                                                <textarea name="participants[{{ $index }}][coach_notes]" class="form-control" rows="3" placeholder="Catatan umum untuk hasil peserta">{{ old("participants.$index.coach_notes", $result->coach_notes ?? '') }}</textarea>
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label">Catatan Internal</label>
                                                <textarea name="participants[{{ $index }}][internal_notes]" class="form-control" rows="2" placeholder="Catatan internal pembina">{{ old("participants.$index.internal_notes", $result->internal_notes ?? '') }}</textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Tes Ulang</label>
                                                <select name="participants[{{ $index }}][retest_schedule_id]" class="form-select">
                                                    <option value="">Tidak dijadwalkan</option>
                                                    @foreach($retestSchedules as $schedule)
                                                        <option value="{{ $schedule->id }}" @selected((string) old("participants.$index.retest_schedule_id", $result->retest_schedule_id ?? '') === (string) $schedule->id)>{{ optional($schedule->activity_date)->translatedFormat('d M Y') }} | {{ $schedule->title }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" value="1" id="needs_retest_{{ $participant->id }}" name="participants[{{ $index }}][needs_retest]" @checked(old("participants.$index.needs_retest", $result->needs_retest ?? false))>
                                                    <label class="form-check-label" for="needs_retest_{{ $participant->id }}">Perlu tes ulang</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div data-panel-tab="summary-{{ $participant->id }}" class="d-none">
                                        <p class="talent-help-text">Ringkasan ini membantu memastikan hasil peserta sudah lengkap sebelum dipublikasikan.</p>
                                        <div class="talent-summary-grid">
                                            <div class="talent-summary-box">
                                                <span>Status Publikasi</span>
                                                <p>{{ $participant->result_status_label }}</p>
                                            </div>
                                            <div class="talent-summary-box">
                                                <span>{{ $scoreLabel }}</span>
                                                <p>{{ $scoreValue ?? 'Belum ada nilai' }}</p>
                                            </div>
                                            <div class="talent-summary-box">
                                                <span>Aspek Terisi</span>
                                                <p>{{ $participant->filled_aspect_count }} dari {{ $participant->total_aspect_count }}</p>
                                            </div>
                                            <div class="talent-summary-box">
                                                <span>Aspek Belum Diisi</span>
                                                <p>{{ $participant->missing_aspect_count }}</p>
                                            </div>
                                            <div class="talent-summary-box">
                                                <span>Kategori</span>
                                                <p>{{ $result?->ability_category ?? 'Belum ditentukan' }}</p>
                                            </div>
                                            <div class="talent-summary-box">
                                                <span>Kelompok Latihan</span>
                                                <p>{{ $result?->training_group ?? 'Belum ditentukan' }}</p>
                                            </div>
                                            <div class="talent-summary-box">
                                                <span>Peran Rekomendasi</span>
                                                <p>{{ $result?->recommended_role ?? 'Belum ditentukan' }}</p>
                                            </div>
                                            <div class="talent-summary-box">
                                                <span>Ringkasan Catatan</span>
                                                <p>{{ $result?->coach_notes ?: ($result?->recommendation ?: 'Belum ada catatan') }}</p>
                                            </div>
                                        </div>
                                        @if($participant->publish_block_reason)
                                            <div class="alert alert-warning mt-3 mb-0">{{ $participant->publish_block_reason }}</div>
                                        @endif
                                    </div>
                                </div>
                            </section>
                        @endforeach

                        <div class="talent-sticky-bar">
                            <div class="talent-sticky-bar__inner">
                                <div class="talent-sticky-bar__meta">
                                    <strong id="activeParticipantName">{{ optional($participants->firstWhere('id', $activeParticipantId))->student->user->name ?? '-' }}</strong>
                                    <span id="activeParticipantProgress">
                                        @if($activeParticipantId && ($activeParticipant = $participants->firstWhere('id', $activeParticipantId)))
                                            {{ $activeParticipant->filled_aspect_count }}/{{ $activeParticipant->total_aspect_count }} aspek terisi
                                        @else
                                            Pilih peserta untuk mulai mengisi hasil tes.
                                        @endif
                                    </span>
                                    <small class="talent-block-reason" id="activeParticipantReason">
                                        @if($activeParticipantId && ($activeParticipant = $participants->firstWhere('id', $activeParticipantId)))
                                            {{ $activeParticipant->publish_block_reason }}
                                        @endif
                                    </small>
                                </div>
                                <div class="talent-sticky-bar__actions">
                                    <button class="btn btn-outline-primary" type="submit" data-loading-text="Menyimpan draft..."><i class="bi bi-save"></i>Simpan Draft</button>
                                    <button class="btn btn-primary" type="submit" name="publish" value="1" id="publishParticipantButton" data-loading-text="Mempublikasikan hasil..." @if($activeParticipantId && ($activeParticipant = $participants->firstWhere('id', $activeParticipantId)) && ! $activeParticipant->is_publish_ready) disabled @endif>
                                        <i class="bi bi-send-check"></i>
                                        <span id="publishParticipantLabel">
                                            @if($activeParticipantId && ($activeParticipant = $participants->firstWhere('id', $activeParticipantId)) && (($resultsByStudent[$activeParticipant->student_id] ?? null)?->status === 'published' || ($resultsByStudent[$activeParticipant->student_id] ?? null)?->published_at))
                                                Perbarui Hasil
                                            @else
                                                Publikasikan Hasil Peserta
                                            @endif
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-talent-manage]');
            if (!root) return;

            const participantButtons = Array.from(root.querySelectorAll('[data-participant-button]'));
            const participantPanels = Array.from(root.querySelectorAll('[data-participant-panel]'));
            const searchInput = root.querySelector('[data-participant-search]');
            const filterSelect = root.querySelector('[data-participant-filter]');
            const targetParticipantId = document.getElementById('targetParticipantId');
            const activeName = document.getElementById('activeParticipantName');
            const activeProgress = document.getElementById('activeParticipantProgress');
            const activeReason = document.getElementById('activeParticipantReason');
            const publishButton = document.getElementById('publishParticipantButton');
            const publishLabel = document.getElementById('publishParticipantLabel');

            const activateParticipant = (participantId) => {
                participantButtons.forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.participantId === participantId);
                });

                participantPanels.forEach((panel) => {
                    panel.classList.toggle('is-active', panel.dataset.participantPanel === participantId);
                });

                const activeButton = participantButtons.find((button) => button.dataset.participantId === participantId);
                if (!activeButton) return;

                targetParticipantId.value = participantId;
                activeName.textContent = activeButton.dataset.name || '-';
                activeProgress.textContent = `${activeButton.dataset.filled || 0}/${activeButton.dataset.total || 0} aspek terisi`;
                activeReason.textContent = activeButton.dataset.reason || '';
                if (publishButton) {
                    const isReady = activeButton.dataset.ready === '1';
                    publishButton.disabled = !isReady;
                }
                if (publishLabel) {
                    publishLabel.textContent = activeButton.dataset.published === '1'
                        ? 'Perbarui Hasil'
                        : 'Publikasikan Hasil Peserta';
                }

                if (window.innerWidth < 992) {
                    const activePanel = participantPanels.find((panel) => panel.dataset.participantPanel === participantId);
                    activePanel?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            };

            participantButtons.forEach((button) => {
                button.addEventListener('click', () => activateParticipant(button.dataset.participantId));
            });

            const applyParticipantFilter = () => {
                const keyword = (searchInput?.value || '').trim().toLowerCase();
                const status = filterSelect?.value || 'all';
                let firstVisible = null;

                participantButtons.forEach((button) => {
                    const matchKeyword = !keyword || (button.dataset.searchText || '').includes(keyword);
                    const matchStatus = status === 'all' || button.dataset.filterStatus === status;
                    const visible = matchKeyword && matchStatus;
                    button.classList.toggle('is-hidden', !visible);
                    if (visible && !firstVisible) {
                        firstVisible = button;
                    }
                });

                const activeButton = participantButtons.find((button) => button.classList.contains('is-active') && !button.classList.contains('is-hidden'));
                if (!activeButton && firstVisible) {
                    activateParticipant(firstVisible.dataset.participantId);
                }
            };

            searchInput?.addEventListener('input', applyParticipantFilter);
            filterSelect?.addEventListener('change', applyParticipantFilter);

            root.querySelectorAll('[data-panel-tabs]').forEach((tabBar) => {
                const triggers = Array.from(tabBar.querySelectorAll('[data-panel-tab-trigger]'));
                triggers.forEach((trigger) => {
                    trigger.addEventListener('click', () => {
                        const panel = trigger.closest('.card-body');
                        const target = trigger.dataset.panelTabTrigger;
                        if (!panel || !target) return;

                        triggers.forEach((item) => item.classList.toggle('is-active', item === trigger));
                        panel.querySelectorAll('[data-panel-tab]').forEach((section) => {
                            section.classList.toggle('d-none', section.dataset.panelTab !== target);
                        });
                    });
                });
            });

            root.querySelectorAll('[data-mobile-back]').forEach((button) => {
                button.addEventListener('click', () => {
                    document.getElementById('talentParticipantList')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            applyParticipantFilter();
        });
    </script>
@endpush
