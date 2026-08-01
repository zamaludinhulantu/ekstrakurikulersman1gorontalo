@extends('layouts.app')

@section('page_title', 'Kelola Hasil Tes Bakat')
@section('page_subtitle', 'Kelola kehadiran, penilaian inti, dan publikasi hasil peserta dengan form yang lebih ringkas.')

@push('styles')
    <style>
        .talent-manage-shell {
            max-width: 1460px;
            margin: 0 auto;
            padding-inline: clamp(0.15rem, 1.4vw, 0.9rem);
        }

        .talent-manage-grid {
            display: grid;
            gap: 1.2rem;
        }

        .talent-overview-card {
            border: 1px solid #dfe7f1;
            border-radius: 22px;
            box-shadow: 0 10px 24px rgba(17, 38, 68, 0.04);
        }

        .talent-manage-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(168px, 1fr));
            gap: 0.8rem;
        }

        .talent-manage-stat {
            border: 1px solid #e2eaf3;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 8px 20px rgba(17, 38, 68, 0.035);
            padding: 0.82rem 0.92rem;
        }

        .talent-manage-stat span {
            display: block;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #71849b;
            margin-bottom: 0.3rem;
        }

        .talent-manage-stat strong {
            display: block;
            font-size: 1.35rem;
            line-height: 1;
            color: #1e3454;
        }

        .talent-manage-stat small {
            display: block;
            margin-top: 0.28rem;
            color: #69809a;
            font-size: 0.76rem;
        }

        .talent-manage-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: start;
        }

        .talent-workspace {
            border: 1px solid #dfe7f1;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 10px 24px rgba(17, 38, 68, 0.04);
            overflow: hidden;
        }

        .talent-workspace .talent-manage-layout {
            gap: 1rem;
            padding: 1rem;
        }

        .talent-pane {
            min-width: 0;
        }

        .talent-pane--aside {
            border: 1px solid #ebf0f5;
            border-radius: 18px;
            background: #fbfdff;
        }

        .talent-pane--detail {
            background: rgba(255, 255, 255, 0.98);
        }

        .talent-detail-card {
            border: 1px solid #dfe7f1;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(17, 38, 68, 0.035);
        }

        .talent-panel-card {
            border: 1px solid #dfe7f1;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 10px 24px rgba(17, 38, 68, 0.04);
        }

        .talent-panel-card .card-body,
        .talent-panel-card .card-header {
            padding: 0.95rem 1.05rem;
        }

        .talent-panel-card .card-header {
            background: transparent;
            border-bottom: 1px solid #ebf0f5;
        }

        .talent-pane .card-header {
            background: transparent;
            border-bottom: 1px solid #ebf0f5;
            padding: 0.95rem 1.05rem;
        }

        .talent-pane .card-body {
            padding: 0.95rem 1.05rem;
        }

        .talent-panel-card .card-header h2,
        .talent-panel-card .card-header h3 {
            margin: 0 0 0.2rem;
            font-size: 0.98rem;
            font-weight: 800;
        }

        .talent-panel-card .card-header p {
            margin: 0;
            color: #6d829b;
            font-size: 0.82rem;
        }

        .talent-participant-tools {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(220px, 260px);
            gap: 0.7rem;
            margin-bottom: 0.85rem;
        }

        .talent-participant-list {
            display: grid;
            gap: 0.55rem;
        }

        .talent-participant-item {
            width: 100%;
            border: 1px solid #e3eaf2;
            border-radius: 14px;
            background: #fff;
            padding: 0.72rem 0.8rem;
            text-align: left;
            transition: 0.2s ease;
        }

        .talent-participant-item.is-active {
            border-color: #9ec0ff;
            background: #f7fbff;
            box-shadow: 0 8px 18px rgba(40, 97, 204, 0.08);
        }

        .talent-participant-item.is-hidden {
            display: none;
        }

        .talent-participant-item__top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
        }

        .talent-participant-item__identity {
            min-width: 0;
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
            gap: 0.38rem;
            justify-content: flex-end;
        }

        .talent-chip-row .badge {
            font-size: 0.72rem;
        }

        .talent-participant-item__summary {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) auto;
            gap: 0.85rem;
            align-items: center;
        }

        .talent-participant-item__stats {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem 0.9rem;
            color: #5d7490;
            font-size: 0.78rem;
        }

        .talent-participant-item__stats strong {
            color: #1f3555;
            font-weight: 700;
        }

        .talent-participant-item__hint {
            color: #7b8da3;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            white-space: nowrap;
        }

        .talent-panel-stack {
            display: grid;
            gap: 1rem;
            padding: 0;
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

        .talent-help-text {
            margin: -0.08rem 0 0.8rem;
            color: #6d8198;
            font-size: 0.79rem;
        }

        .talent-field-grid {
            display: grid;
            gap: 0.9rem;
        }

        .talent-form-section + .talent-form-section {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #ecf1f6;
        }

        .talent-form-section h4 {
            margin: 0 0 0.2rem;
            font-size: 0.88rem;
            font-weight: 800;
            color: #1f3555;
        }

        .talent-form-section p {
            margin: 0 0 0.8rem;
            color: #70839b;
            font-size: 0.78rem;
        }

        .page-summary-banner {
            border: 1px solid #ebf0f5;
            border-radius: 18px;
            background: #fbfdff;
            padding: 0.85rem 0.9rem;
        }

        .page-summary-banner .data-point {
            border: 1px solid #edf2f7;
            border-radius: 14px;
            background: #fff;
            padding: 0.7rem 0.8rem;
        }

        .page-summary-banner .data-point-label {
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #7488a0;
            font-weight: 800;
            margin-bottom: 0.2rem;
        }

        .page-summary-banner .data-point-value {
            color: #203655;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .talent-aspect-list {
            display: grid;
            gap: 0.85rem;
        }

        .talent-aspect-card {
            border: 1px solid #e2eaf3;
            border-radius: 16px;
            background: #fbfdff;
            padding: 0.85rem;
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
            border-radius: 16px;
            background: #fbfdff;
            padding: 0.78rem 0.88rem;
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
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 14px 28px rgba(18, 40, 70, 0.08);
            padding: 0.78rem 0.9rem;
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
            gap: 0.55rem;
        }

        .talent-sticky-bar__actions .btn {
            min-height: 40px;
            padding-inline: 0.95rem;
        }

        .talent-block-reason {
            color: #9b5f00;
            font-size: 0.79rem;
        }

        .talent-optional-panel {
            margin-top: 1rem;
            border: 1px dashed #d8e2ef;
            border-radius: 16px;
            background: #fcfdff;
            padding: 0.82rem 0.92rem;
        }

        .talent-optional-panel summary {
            cursor: pointer;
            color: #24456f;
            font-weight: 800;
            list-style: none;
        }

        .talent-optional-panel summary::-webkit-details-marker {
            display: none;
        }

        .talent-optional-panel summary::before {
            content: '+';
            display: inline-block;
            width: 1rem;
            margin-right: 0.35rem;
            color: #4e73a3;
            font-weight: 900;
        }

        .talent-optional-panel[open] summary::before {
            content: '-';
        }

        .talent-section-label {
            margin-bottom: 0.3rem;
            color: #70839b;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .talent-bulk-toolbar {
            display: grid;
            grid-template-columns: minmax(220px, 0.9fr) repeat(3, minmax(160px, 1fr)) auto auto;
            gap: 0.75rem;
            align-items: end;
            padding: 0.9rem 1rem 0;
        }

        .talent-bulk-count {
            color: #647b95;
            font-size: 0.83rem;
            white-space: nowrap;
        }

        .talent-bulk-fields {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
            padding: 0 1rem 1rem;
        }

        .talent-bulk-state {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .talent-bulk-actions {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .talent-collapsible summary {
            list-style: none;
            cursor: pointer;
        }

        .talent-collapsible summary::-webkit-details-marker {
            display: none;
        }

        .talent-recap-table .table {
            min-width: 760px;
        }

        .talent-recap-table .table {
            margin-bottom: 0;
        }

        .talent-recap-table thead th {
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #6f839b;
            font-weight: 800;
            padding: 0.75rem 0.85rem;
            background: #fbfdff;
            border-bottom-color: #eaf0f6;
        }

        .talent-recap-table tbody td {
            padding: 0.72rem 0.85rem;
            vertical-align: middle;
            border-bottom-color: #edf2f7;
            font-size: 0.88rem;
        }

        .talent-recap-table .badge {
            font-size: 0.72rem;
        }

        .talent-panel-header-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.55rem;
        }

        .talent-overview-title {
            font-size: 1.02rem;
            font-weight: 800;
            color: #1f3555;
            margin-bottom: 0.15rem;
        }

        .talent-overview-subtitle,
        .talent-overview-meta-label {
            color: #70839b;
            font-size: 0.76rem;
        }

        .talent-overview-meta-value {
            color: #1f3555;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .talent-form-cluster {
            display: grid;
            gap: 0.9rem;
        }

        @media (max-width: 1199.98px) {
            .talent-bulk-toolbar {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .talent-bulk-state {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 991.98px) {
            .talent-workspace .talent-manage-layout {
                padding: 0.9rem;
            }

            .talent-bulk-toolbar {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .talent-bulk-actions {
                justify-content: flex-start;
            }

            .talent-participant-tools {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .talent-manage-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
            }

            .talent-overview-card {
                border-radius: 20px;
            }

            .talent-manage-shell {
                padding-inline: 0;
            }

            .talent-panel-card .card-body,
            .talent-panel-card .card-header {
                padding-inline: 0.95rem;
            }

            .talent-pane .card-body,
            .talent-pane .card-header {
                padding-inline: 0.95rem;
            }

            .talent-workspace .talent-manage-layout {
                padding: 0.85rem;
            }

            .talent-manage-stat strong {
                font-size: 1.25rem;
            }

            .talent-bulk-toolbar {
                grid-template-columns: 1fr;
                padding: 0.95rem 0.95rem 0;
                gap: 0.75rem;
            }

            .talent-bulk-state,
            .talent-bulk-actions {
                justify-content: flex-start;
            }

            .talent-recap-table .table th,
            .talent-recap-table .table td {
                white-space: nowrap;
                font-size: 0.84rem;
                vertical-align: middle;
            }

            .talent-participant-item {
                border-radius: 18px;
                padding: 0.8rem 0.85rem;
            }

            .talent-panel-header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .talent-panel-header-actions .btn {
                width: 100%;
            }

            .talent-form-cluster {
                gap: 0.78rem;
            }

            .talent-participant-list {
                grid-template-columns: 1fr;
            }

            .talent-chip-row {
                justify-content: flex-start;
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

        @media (max-width: 575.98px) {
            .talent-overview-card .row > div {
                width: 100%;
            }

            .talent-manage-summary {
                grid-template-columns: 1fr;
            }

            .talent-help-text {
                font-size: 0.78rem;
            }

            .talent-panel-card {
                border-radius: 20px;
            }

            .talent-workspace {
                border-radius: 20px;
            }

            .talent-panel-card .card-body,
            .talent-panel-card .card-header {
                padding: 0.85rem;
            }

            .talent-pane .card-body,
            .talent-pane .card-header,
            .talent-panel-stack {
                padding: 0.85rem;
            }

            .talent-sticky-bar {
                bottom: 0.65rem;
            }

            .talent-sticky-bar__inner {
                border-radius: 18px;
                padding: 0.85rem;
            }

            .talent-optional-panel,
            .talent-summary-box,
            .talent-aspect-card {
                border-radius: 16px;
                padding: 0.8rem;
            }

            .talent-bulk-count {
                white-space: normal;
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
        $decisionClassMap = [
            'Diterima ke Ekskul' => 'badge-status-success',
            'Tidak Diterima' => 'badge-status-danger',
            'Belum diputuskan' => 'badge-status-secondary',
        ];
    @endphp

    <div class="talent-manage-shell">
        <div class="talent-manage-grid" data-talent-manage>
        <div class="card talent-overview-card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="talent-overview-title">{{ $talentTest->title }}</div>
                        <div class="talent-overview-subtitle">{{ $talentTest->extracurricular->name ?? '-' }}</div>
                    </div>
                    <div class="col-lg-3">
                        <div class="talent-overview-meta-label">Jadwal</div>
                        <div class="talent-overview-meta-value">{{ optional($talentTest->activity_date)->translatedFormat('d M Y') }} | {{ \Illuminate\Support\Str::substr((string) $talentTest->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $talentTest->end_time, 0, 5) }}</div>
                    </div>
                    <div class="col-lg-3">
                        <div class="talent-overview-meta-label">Lokasi</div>
                        <div class="talent-overview-meta-value">{{ $talentTest->location ?: 'Belum ditentukan' }}</div>
                    </div>
                    <div class="col-lg-2">
                        <div class="talent-overview-meta-label">Status</div>
                        <div class="talent-overview-meta-value">{{ $talentTest->status === 'completed' ? 'Selesai' : ($talentTest->status === 'cancelled' ? 'Dibatalkan' : 'Dijadwalkan') }}</div>
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
            <div class="talent-manage-stat"><span>Nilai Rata-rata</span><strong>{{ $summary['average'] ?? '-' }}</strong><small>Berdasarkan hasil yang sudah tersimpan.</small></div>
        </div>

        <form method="post" action="{{ route('coach.talent-tests.results.save', $talentTest) }}" id="talentManageForm">
            @csrf
            <input type="hidden" name="target_participant_id" id="targetParticipantId" value="{{ $activeParticipantId }}">

            @if($participants->isNotEmpty())
                <div class="talent-panel-card card">
                    <div class="card-header">
                        <h2>Rekap Hasil Peserta</h2>
                        <p>Gunakan penilaian massal untuk update cepat, lalu cek rekap peserta di tabel bawah.</p>
                    </div>
                    <div class="talent-bulk-toolbar">
                        <div class="talent-bulk-state">
                            <strong id="bulkSelectionCount" class="talent-bulk-count">0 peserta dipilih untuk aksi massal.</strong>
                        </div>
                        <div>
                            <label class="form-label" for="bulkOverallScore">Nilai Umum</label>
                            <input id="bulkOverallScore" type="number" step="0.01" min="0" max="100" name="bulk_overall_score" class="form-control" value="{{ old('bulk_overall_score') }}" placeholder="Contoh: 80">
                        </div>
                        <div>
                            <label class="form-label" for="bulkAbilityCategory">Kategori</label>
                            <input id="bulkAbilityCategory" type="text" name="bulk_ability_category" class="form-control" value="{{ old('bulk_ability_category') }}" placeholder="Contoh: Menengah">
                        </div>
                        <div>
                            <label class="form-label" for="bulkDecisionStatus">Keputusan</label>
                            <select id="bulkDecisionStatus" name="bulk_decision_status" class="form-select">
                                <option value="">Pilih keputusan</option>
                                <option value="accepted" @selected(old('bulk_decision_status') === 'accepted')>Diterima ke ekskul</option>
                                <option value="rejected" @selected(old('bulk_decision_status') === 'rejected')>Tidak diterima</option>
                            </select>
                        </div>
                        <div class="talent-bulk-actions">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="bulkSelectVisibleButton">Pilih Semua</button>
                            <button type="submit" class="btn btn-outline-primary btn-sm" name="apply_bulk_decision" value="1" id="applyBulkDecisionButton" disabled>
                                <i class="bi bi-check2-square"></i>Terapkan
                            </button>
                        </div>
                    </div>
                    <div class="talent-bulk-fields">
                        <div>
                            <label class="form-label" for="bulkDecisionNotes">Catatan keputusan massal</label>
                            <input id="bulkDecisionNotes" type="text" name="bulk_decision_notes" class="form-control" value="{{ old('bulk_decision_notes') }}" placeholder="Opsional, diterapkan ke peserta yang dipilih">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive talent-recap-table">
                            <table class="table table-striped mb-0">
                                <thead>
                                <tr>
                                    <th style="width: 48px;">
                                        <input type="checkbox" class="form-check-input" id="bulkSelectAllRows">
                                    </th>
                                    <th>Peserta</th>
                                    <th>Kehadiran</th>
                                    <th>Aspek</th>
                                    <th>Nilai</th>
                                    <th>Kategori</th>
                                    <th>Keputusan</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($participants as $participant)
                                    @php $result = $resultsByStudent[$participant->student_id] ?? null; @endphp
                                    <tr>
                                        <td>
                                            <input
                                                type="checkbox"
                                                class="form-check-input"
                                                name="selected_participant_ids[]"
                                                value="{{ $participant->id }}"
                                                data-bulk-select-row
                                                @checked(in_array((string) $participant->id, array_map('strval', old('selected_participant_ids', [])), true))
                                            >
                                        </td>
                                        <td>
                                            <strong>{{ $participant->student->user->name ?? '-' }}</strong>
                                            <div class="small text-muted">{{ $participant->student->class_name ?: 'Kelas belum diatur' }}</div>
                                        </td>
                                        <td><span class="badge {{ $attendanceClassMap[$participant->attendance_label] ?? 'badge-status-secondary' }}">{{ $participant->attendance_label }}</span></td>
                                        <td>{{ $participant->filled_aspect_count }}/{{ $participant->total_aspect_count }}</td>
                                        <td>{{ $participant->overall_score_label ?? 'Belum ada' }}</td>
                                        <td>{{ $result?->ability_category ?: 'Belum ditentukan' }}</td>
                                        <td><span class="badge {{ $decisionClassMap[$participant->decision_label] ?? 'badge-status-secondary' }}">{{ $participant->decision_label }}</span></td>
                                        <td><span class="badge {{ $participant->result_status_class }}">{{ $participant->result_status_label }}</span></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="talent-workspace">
            <div class="talent-manage-layout">
                <aside class="talent-pane talent-pane--aside">
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
                                            <div class="talent-participant-item__identity">
                                                <div class="talent-participant-item__title">{{ $participant->student->user->name ?? '-' }}</div>
                                                <span class="talent-participant-item__subtitle">{{ $participant->student->class_name ?: 'Kelas belum diatur' }}</span>
                                            </div>
                                            <span class="badge {{ $attendanceClassMap[$participant->attendance_label] ?? 'badge-status-secondary' }}">{{ $participant->attendance_label }}</span>
                                        </div>
                                        <div class="talent-participant-item__summary">
                                            <div class="talent-participant-item__stats">
                                                <span data-picker-stat="status">Status: <strong>{{ $participant->result_status_label }}</strong></span>
                                                <span data-picker-stat="aspects">Aspek: <strong>{{ $participant->filled_aspect_count }}/{{ $participant->total_aspect_count }}</strong></span>
                                                <span data-picker-stat="score">Nilai: <strong>{{ $participant->overall_score_label ?? '-' }}</strong></span>
                                            </div>
                                            <div class="talent-chip-row">
                                                <span class="badge {{ $participant->result_status_class }}">{{ $participant->result_status_label }}</span>
                                                <span class="talent-participant-item__hint">Klik untuk menilai</span>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </aside>

                <div class="talent-pane talent-pane--detail">
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
                                $overallScoreInput = old("participants.$index.overall_score", $result?->overall_score);
                                $scoreValue = $overallScoreInput !== null && $overallScoreInput !== '' ? number_format((float) $overallScoreInput, 2, ',', '.') : null;
                                $scoreLabel = $participant->is_publish_ready && $scoreValue !== null ? 'Nilai Akhir' : ($scoreValue !== null ? 'Nilai Sementara' : 'Belum ada nilai');
                            @endphp
                            <section class="talent-detail-card talent-panel-section @if($panelActive) is-active @endif" data-participant-panel="{{ $participant->id }}">
                                <div class="card-header d-flex justify-content-between align-items-start gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary talent-mobile-back" data-mobile-back><i class="bi bi-arrow-left"></i></button>
                                            <h3 class="mb-0">{{ $participant->student->user->name ?? '-' }}</h3>
                                        </div>
                                        <p>{{ $participant->student->class_name ?: 'Kelas belum diatur' }} | {{ $participant->attendance_label }}</p>
                                    </div>
                                    <div class="talent-panel-header-actions">
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
                                                    <div class="data-point-value" data-panel-aspect-summary>{{ $participant->filled_aspect_count }}/{{ $participant->total_aspect_count }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="talent-form-section">
                                        <h4>Kehadiran</h4>
                                        <p>Catat kehadiran peserta sebelum melanjutkan penilaian.</p>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Status Kehadiran</label>
                                                <select name="participants[{{ $index }}][attendance_status]" class="form-select" data-attendance-status>
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

                                    <div class="talent-form-section">
                                        <h4>Penilaian</h4>
                                        <p>Isi nilai inti peserta yang hadir. Kategori dan keputusan akhir tetap wajib sebelum publikasi.</p>
                                        <div class="talent-form-cluster mb-3">
                                            <div class="row g-3">
                                            <div class="col-lg-4 col-md-6">
                                                <label class="form-label">Nilai Umum</label>
                                                <input type="number" step="0.01" min="0" max="100" name="participants[{{ $index }}][overall_score]" class="form-control" value="{{ $overallScoreInput }}" placeholder="Contoh: 87.50">
                                                <div class="form-text">Nilai umum non-aspek. Jika diisi, nilai ini dipakai sebagai nilai akhir peserta.</div>
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <label class="form-label">Kategori Kemampuan</label>
                                                <input type="text" name="participants[{{ $index }}][ability_category]" class="form-control" value="{{ old("participants.$index.ability_category", $result->ability_category ?? '') }}" placeholder="Contoh: Dasar, Menengah" data-ability-category>
                                                <div class="form-text">Wajib diisi untuk peserta yang hadir sebelum publikasi hasil.</div>
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <label class="form-label">Keputusan Akhir</label>
                                                <select name="participants[{{ $index }}][decision_status]" class="form-select" data-decision-status>
                                                    <option value="">Belum diputuskan</option>
                                                    <option value="accepted" @selected(old("participants.$index.decision_status", $result->decision_status ?? '') === 'accepted')>Diterima ke ekskul</option>
                                                    <option value="rejected" @selected(old("participants.$index.decision_status", $result->decision_status ?? '') === 'rejected')>Tidak diterima</option>
                                                </select>
                                                <div class="form-text">Wajib dipilih sebelum publikasi, kecuali peserta ditandai tes ulang.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="talent-form-section">
                                        <h4>Catatan</h4>
                                        <p>Tambahkan catatan pembina atau keputusan untuk memudahkan tindak lanjut.</p>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Catatan Pembina</label>
                                                <textarea name="participants[{{ $index }}][coach_notes]" class="form-control" rows="2" placeholder="Catatan inti hasil peserta">{{ old("participants.$index.coach_notes", $result->coach_notes ?? '') }}</textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Catatan Keputusan</label>
                                                <textarea name="participants[{{ $index }}][decision_notes]" class="form-control" rows="2" placeholder="Opsional, jelaskan alasan diterima atau tidak diterima">{{ old("participants.$index.decision_notes", $result->decision_notes ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="talent-form-section">
                                        <h4>Aspek Penilaian</h4>
                                        <p>Isi aspek teknis bila sudah tersedia untuk ekstrakurikuler ini.</p>
                                        @if($aspects->isEmpty())
                                            <div class="alert alert-warning mb-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                                <span>Aspek penilaian untuk {{ $talentTest->extracurricular->name }} belum dibuat. Tambahkan dulu agar kolom input nilai muncul.</span>
                                                <a href="{{ route('coach.talent-test-aspects.index', ['extracurricular_id' => $talentTest->extracurricular_id]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-sliders"></i> Kelola Aspek Tes
                                                </a>
                                            </div>
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
                                                                <input type="number" step="0.01" min="0" max="{{ $aspect->max_score }}" name="participants[{{ $index }}][scores][{{ $aspect->id }}]" class="form-control" value="{{ $currentScore }}" data-score-input>
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

                                        <details class="talent-optional-panel">
                                            <summary>Opsi lanjutan</summary>
                                            <p class="talent-help-text mt-2 mb-3">Bagian ini opsional. Isi hanya jika memang diperlukan untuk tindak lanjut pembinaan.</p>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Kelompok Latihan</label>
                                                    <input type="text" name="participants[{{ $index }}][training_group]" class="form-control" value="{{ old("participants.$index.training_group", $result->training_group ?? '') }}" placeholder="Contoh: Fundamental A">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Posisi atau Peran</label>
                                                    <input type="text" name="participants[{{ $index }}][recommended_role]" class="form-control" value="{{ old("participants.$index.recommended_role", $result->recommended_role ?? '') }}" placeholder="Opsional">
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
                                                <div class="col-md-6">
                                                    <label class="form-label">Rekomendasi Pembina</label>
                                                    <textarea name="participants[{{ $index }}][recommendation]" class="form-control" rows="3" placeholder="Saran latihan atau tindak lanjut untuk siswa">{{ old("participants.$index.recommendation", $result->recommendation ?? '') }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Catatan Internal</label>
                                                    <textarea name="participants[{{ $index }}][internal_notes]" class="form-control" rows="3" placeholder="Catatan internal pembina">{{ old("participants.$index.internal_notes", $result->internal_notes ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </details>
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
            </div>
            </div>

        </form>
        </div>
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
            const bulkCheckboxes = Array.from(root.querySelectorAll('[data-bulk-select-row]'));
            const bulkSelectAllRows = document.getElementById('bulkSelectAllRows');
            const bulkSelectVisibleButton = document.getElementById('bulkSelectVisibleButton');
            const bulkSelectionCount = document.getElementById('bulkSelectionCount');
            const applyBulkDecisionButton = document.getElementById('applyBulkDecisionButton');
            const attendanceBadgeClassMap = {
                present: 'badge-status-success',
                absent: 'badge-status-danger',
                permission: 'badge-status-warning',
                sick: 'badge-status-warning',
                pending: 'badge-status-secondary',
            };
            const attendanceLabelMap = {
                present: 'Hadir',
                absent: 'Tidak Hadir',
                permission: 'Izin',
                sick: 'Sakit',
                pending: 'Belum Diisi',
            };

            const getParticipantPanel = (participantId) => participantPanels.find((panel) => panel.dataset.participantPanel === participantId);

            const resolveParticipantState = (participantId) => {
                const panel = getParticipantPanel(participantId);
                if (!panel) return null;

                const attendanceStatus = panel.querySelector('[data-attendance-status]')?.value || 'pending';
                const abilityCategory = (panel.querySelector('[data-ability-category]')?.value || '').trim();
                const decisionStatus = panel.querySelector('[data-decision-status]')?.value || '';
                const needsRetest = !!panel.querySelector('input[name$="[needs_retest]"]')?.checked;
                const filledAspectCount = Array.from(panel.querySelectorAll('[data-score-input]'))
                    .filter((input) => input.value !== '' && input.value !== null)
                    .length;
                const totalAspectCount = Array.from(panel.querySelectorAll('[data-score-input]')).length;
                const isAbsent = ['absent', 'sick', 'permission'].includes(attendanceStatus);

                let reason = '';
                let ready = false;

                if (isAbsent) {
                    ready = true;
                } else if (attendanceStatus !== 'present') {
                    reason = 'Tentukan status kehadiran peserta terlebih dahulu.';
                } else if (totalAspectCount > 0 && filledAspectCount === 0) {
                    reason = 'Isi minimal satu aspek penilaian.';
                } else if (!abilityCategory) {
                    reason = 'Kategori kemampuan belum diisi.';
                } else if (!needsRetest && !decisionStatus) {
                    reason = 'Pilih keputusan akhir peserta atau tandai tes ulang.';
                } else {
                    ready = true;
                }

                return {
                    attendanceStatus,
                    attendanceLabel: attendanceLabelMap[attendanceStatus] || 'Belum Diisi',
                    decisionStatus,
                    needsRetest,
                    filledAspectCount,
                    totalAspectCount,
                    ready,
                    reason,
                };
            };

            const syncParticipantState = (participantId) => {
                const button = participantButtons.find((item) => item.dataset.participantId === participantId);
                const panel = getParticipantPanel(participantId);
                const state = resolveParticipantState(participantId);
                if (!button || !panel || !state) return;

                button.dataset.filled = String(state.filledAspectCount);
                button.dataset.total = String(state.totalAspectCount);
                button.dataset.ready = state.ready ? '1' : '0';
                button.dataset.reason = state.reason;

                const badge = button.querySelector('.talent-participant-item__top .badge');
                if (badge) {
                    badge.textContent = state.attendanceLabel;
                    badge.className = `badge ${attendanceBadgeClassMap[state.attendanceStatus] || 'badge-status-secondary'}`;
                }

                const statusStat = button.querySelector('[data-picker-stat="status"]');
                const aspectsStat = button.querySelector('[data-picker-stat="aspects"]');
                const scoreStat = button.querySelector('[data-picker-stat="score"]');
                if (statusStat) {
                    statusStat.innerHTML = `Status: <strong>${state.ready ? 'Siap dipublikasikan' : 'Perlu dilengkapi'}</strong>`;
                }
                if (aspectsStat) {
                    aspectsStat.innerHTML = `Aspek: <strong>${state.filledAspectCount}/${state.totalAspectCount}</strong>`;
                }
                if (scoreStat) {
                    const scoreInput = panel.querySelector('input[name$="[overall_score]"]');
                    const scoreValue = scoreInput?.value ? Number.parseFloat(scoreInput.value).toFixed(2).replace('.', ',') : '-';
                    scoreStat.innerHTML = `Nilai: <strong>${scoreValue}</strong>`;
                }

                const panelScoreSummary = panel.querySelector('[data-panel-aspect-summary]');
                if (panelScoreSummary) {
                    panelScoreSummary.textContent = `${state.filledAspectCount}/${state.totalAspectCount}`;
                }

                const warning = panel.querySelector('.alert.alert-warning');
                if (warning) {
                    if (state.reason) {
                        warning.textContent = state.reason;
                        warning.classList.remove('d-none');
                    } else {
                        warning.classList.add('d-none');
                    }
                }

                if (targetParticipantId.value === participantId) {
                    activeProgress.textContent = `${state.filledAspectCount}/${state.totalAspectCount} aspek terisi`;
                    activeReason.textContent = state.reason;
                    if (publishButton) {
                        publishButton.disabled = !state.ready;
                    }
                }
            };

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
                syncParticipantState(participantId);
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

            const syncBulkSelectionState = () => {
                const selectedCount = bulkCheckboxes.filter((checkbox) => checkbox.checked).length;

                if (bulkSelectionCount) {
                    bulkSelectionCount.textContent = selectedCount > 0
                        ? `${selectedCount} peserta dipilih untuk aksi massal.`
                        : '0 peserta dipilih untuk aksi massal.';
                }

                if (applyBulkDecisionButton) {
                    applyBulkDecisionButton.disabled = selectedCount === 0;
                }

                if (bulkSelectAllRows) {
                    bulkSelectAllRows.checked = bulkCheckboxes.length > 0 && bulkCheckboxes.every((checkbox) => checkbox.checked);
                }
            };

            bulkCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', syncBulkSelectionState);
            });

            bulkSelectAllRows?.addEventListener('change', () => {
                bulkCheckboxes.forEach((checkbox) => {
                    checkbox.checked = bulkSelectAllRows.checked;
                });
                syncBulkSelectionState();
            });

            bulkSelectVisibleButton?.addEventListener('click', () => {
                bulkCheckboxes.forEach((checkbox) => {
                    checkbox.checked = true;
                });
                syncBulkSelectionState();
            });

            root.querySelectorAll('[data-mobile-back]').forEach((button) => {
                button.addEventListener('click', () => {
                    document.getElementById('talentParticipantList')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            participantPanels.forEach((panel) => {
                const participantId = panel.dataset.participantPanel;
                panel.querySelectorAll('[data-attendance-status], [data-score-input], [data-ability-category], [data-decision-status], input[name$="[needs_retest]"]').forEach((input) => {
                    input.addEventListener('input', () => syncParticipantState(participantId));
                    input.addEventListener('change', () => syncParticipantState(participantId));
                });
            });

            applyParticipantFilter();
            participantButtons.forEach((button) => syncParticipantState(button.dataset.participantId));
            syncBulkSelectionState();
        });
    </script>
@endpush
