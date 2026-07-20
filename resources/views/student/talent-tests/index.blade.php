@extends('layouts.app')

@section('page_title', 'Tes Bakat Saya')
@section('page_subtitle', 'Lihat jadwal, proses penilaian, dan hasil tes bakat yang sudah dipublikasikan.')

@push('styles')
    <style>
        .talent-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .talent-stat-card {
            min-height: 112px;
            padding: 0.95rem 1rem;
            border-radius: 22px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.06);
        }

        .talent-stat-card .label {
            margin: 0 0 0.35rem;
            color: #677b93;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .talent-stat-card .value {
            margin: 0;
            color: #18334f;
            font-size: 1.35rem;
            line-height: 1.1;
            font-weight: 800;
        }

        .talent-stat-card .meta {
            margin-top: 0.35rem;
            color: #60748c;
            font-size: 0.8rem;
            line-height: 1.45;
        }

        .talent-tab-panel {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.05);
            overflow: hidden;
        }

        .talent-tab-nav {
            display: flex;
            gap: 0.75rem;
            overflow-x: auto;
            padding: 1rem 1.2rem;
            border-bottom: 1px solid #e8eef5;
            background: rgba(255, 255, 255, 0.72);
        }

        .talent-tab-link {
            flex: 0 0 auto;
            min-height: 42px;
            padding: 0.72rem 1rem;
            border: 1px solid #dbe5f0;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.9);
            color: #4c617b;
            text-decoration: none;
            font-size: 0.86rem;
            font-weight: 800;
        }

        .talent-tab-link.is-active {
            color: #1849cb;
            border-color: #9ebfff;
            background: linear-gradient(180deg, #fbfdff 0%, #eef5ff 100%);
            box-shadow: 0 12px 22px rgba(31, 94, 255, 0.08);
        }

        .talent-list-body {
            padding: 1rem 1.2rem 1.2rem;
        }

        .talent-card-grid {
            display: grid;
            gap: 0.9rem;
        }

        .talent-card {
            border: 1px solid #dbe5f0;
            border-radius: 22px;
            background: #fbfdff;
            padding: 1rem 1.05rem;
        }

        .talent-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.8rem;
            margin-bottom: 0.8rem;
        }

        .talent-card-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #18334f;
        }

        .talent-card-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
            margin-bottom: 0.9rem;
        }

        .talent-card-meta-item span {
            display: block;
            margin-bottom: 0.2rem;
            color: #6a7f98;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 800;
        }

        .talent-card-meta-item strong,
        .talent-card-meta-item p {
            margin: 0;
        }

        .talent-card-meta-item p {
            color: #5f738b;
            line-height: 1.55;
        }

        .talent-result-highlight {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.5rem 0.8rem;
            border-radius: 999px;
            background: #eef5ff;
            color: #1849cb;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .talent-detail-modal {
            border: 1px solid var(--ui-border);
            border-radius: 28px;
            overflow: hidden;
        }

        .talent-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .talent-detail-item {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: #fbfdff;
            border: 1px solid #dfe9f4;
        }

        .talent-detail-item span {
            display: block;
            margin-bottom: 0.25rem;
            color: #6a7f98;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 800;
        }

        @media (max-width: 1199.98px) {
            .talent-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .talent-stats-grid,
            .talent-card-meta,
            .talent-detail-grid {
                grid-template-columns: 1fr;
            }

            .talent-tab-nav,
            .talent-list-body {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $upcomingQuery = array_merge(request()->except(['tab', 'upcoming_page', 'pending_page', 'history_page']), ['tab' => 'upcoming']);
        $pendingQuery = array_merge(request()->except(['tab', 'upcoming_page', 'pending_page', 'history_page']), ['tab' => 'pending']);
        $historyQuery = array_merge(request()->except(['tab', 'upcoming_page', 'pending_page', 'history_page']), ['tab' => 'history']);
        $statusClassMap = [
            'Tes Dijadwalkan' => 'badge-status-success',
            'Hari Ini' => 'badge-status-warning',
            'Besok' => 'badge-status-warning',
            'Mendatang' => 'badge-status-success',
            'Hadir' => 'badge-status-success',
            'Terlambat' => 'badge-status-warning',
            'Tidak Hadir' => 'badge-status-danger',
            'Izin' => 'badge-status-warning',
            'Sakit' => 'badge-status-warning',
            'Sedang Dinilai' => 'badge-status-warning',
            'Hasil Tersedia' => 'badge-status-success',
            'Tes Ulang' => 'badge-status-warning',
            'Dibatalkan' => 'badge-status-danger',
            'Menunggu Jadwal' => 'badge-status-secondary',
        ];
        $rows = $tab === 'pending' ? $pendingTests : ($tab === 'history' ? $historyTests : $upcomingTests);
    @endphp

    <div class="talent-stats-grid mb-3">
        <div class="talent-stat-card">
            <p class="label">Tes Mendatang</p>
            <p class="value">{{ $summary['upcoming'] }}</p>
            <div class="meta">Jadwal tes yang sudah ditetapkan.</div>
        </div>
        <div class="talent-stat-card">
            <p class="label">Menunggu Hasil</p>
            <p class="value">{{ $summary['pending'] }}</p>
            <div class="meta">Tes yang sudah berlangsung dan sedang dinilai.</div>
        </div>
        <div class="talent-stat-card">
            <p class="label">Hasil Tersedia</p>
            <p class="value">{{ $summary['available'] }}</p>
            <div class="meta">Hasil tes yang sudah dipublikasikan.</div>
        </div>
        <div class="talent-stat-card">
            <p class="label">Tes Selesai</p>
            <p class="value">{{ $summary['completed'] }}</p>
            <div class="meta">Tidak termasuk tes yang dibatalkan.</div>
        </div>
    </div>

    @if($upcomingTests->total() + $pendingTests->total() + $historyTests->total() === 0)
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-clipboard2-pulse"></i></div>
                    <p class="mb-3">Belum ada tes bakat yang dijadwalkan. Jadwal tes akan muncul setelah pendaftaranmu diverifikasi oleh pembina.</p>
                    <div class="empty-state-actions">
                        <a href="{{ route('student.registrations.index') }}" class="btn btn-primary"><i class="bi bi-clipboard-check"></i>Lihat Status Pendaftaran</a>
                        <a href="{{ route('student.extracurriculars.index') }}" class="btn btn-outline-secondary"><i class="bi bi-grid-1x2"></i>Lihat Ekstrakurikuler</a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="talent-tab-panel">
            <div class="talent-tab-nav">
                <a href="{{ route('student.talent-tests.index', $upcomingQuery) }}" class="talent-tab-link {{ $tab === 'upcoming' ? 'is-active' : '' }}">Tes Mendatang</a>
                <a href="{{ route('student.talent-tests.index', $pendingQuery) }}" class="talent-tab-link {{ $tab === 'pending' ? 'is-active' : '' }}">Menunggu Hasil</a>
                <a href="{{ route('student.talent-tests.index', $historyQuery) }}" class="talent-tab-link {{ $tab === 'history' ? 'is-active' : '' }}">Riwayat</a>
            </div>
            <div class="talent-list-body">
                <div class="talent-card-grid">
                    @forelse($rows as $test)
                        @php
                            $schedule = $test->schedule;
                            $result = $test->student_result;
                        @endphp
                        <div class="talent-card">
                            <div class="talent-card-header">
                                <div>
                                    <h3 class="talent-card-title">{{ $schedule->title ?? 'Tes bakat' }}</h3>
                                    <div class="small text-muted">{{ $schedule->extracurricular->name ?? '-' }}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <span class="badge {{ $statusClassMap[$test->student_schedule_badge] ?? 'badge-status-secondary' }}">{{ $test->student_schedule_badge }}</span>
                                    <span class="badge {{ $statusClassMap[$test->student_status_label] ?? 'badge-status-secondary' }}">{{ $test->student_status_label }}</span>
                                </div>
                            </div>

                            <div class="talent-card-meta">
                                <div class="talent-card-meta-item">
                                    <span>Tanggal</span>
                                    <strong>{{ optional($schedule->activity_date)->translatedFormat('d F Y') ?: '-' }}</strong>
                                </div>
                                <div class="talent-card-meta-item">
                                    <span>Jam</span>
                                    <strong>{{ \Illuminate\Support\Str::substr((string) $schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $schedule->end_time, 0, 5) }}</strong>
                                </div>
                                <div class="talent-card-meta-item">
                                    <span>Lokasi</span>
                                    <p>{{ $schedule->location ?: 'Lokasi belum tersedia.' }}</p>
                                </div>
                                @if($tab === 'history')
                                    <div class="talent-card-meta-item">
                                        <span>Kelompok kemampuan</span>
                                        <p>{{ $result?->training_group ?: 'Belum ada kelompok' }}</p>
                                    </div>
                                @else
                                    <div class="talent-card-meta-item">
                                        <span>Status kehadiran</span>
                                        <p>{{ $test->student_attendance_label }}</p>
                                    </div>
                                @endif
                                @if($tab === 'upcoming')
                                    <div class="talent-card-meta-item">
                                        <span>Peralatan</span>
                                        <p>{{ $schedule->equipment ?: 'Tidak ada peralatan khusus.' }}</p>
                                    </div>
                                    <div class="talent-card-meta-item">
                                        <span>Instruksi</span>
                                        <p>{{ $schedule->instructions ?: 'Ikuti arahan pembina saat tes berlangsung.' }}</p>
                                    </div>
                                @elseif($tab === 'pending')
                                    <div class="talent-card-meta-item">
                                        <span>Status penilaian</span>
                                        <p>{{ $test->student_result_status_label }}</p>
                                    </div>
                                    <div class="talent-card-meta-item">
                                        <span>Informasi</span>
                                        <p>Sedang dinilai oleh pembina. Hasil akan muncul setelah dipublikasikan.</p>
                                    </div>
                                @else
                                    <div class="talent-card-meta-item">
                                        <span>Nilai akhir</span>
                                        <strong>{{ $result?->overall_score !== null ? number_format((float) $result->overall_score, 0, ',', '.') : '—' }}</strong>
                                    </div>
                                    <div class="talent-card-meta-item">
                                        <span>Kategori</span>
                                        <p>{{ $result?->ability_category ?: 'Belum ada kategori' }}</p>
                                    </div>
                                @endif
                            </div>

                            @if($tab === 'history')
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <span class="talent-result-highlight">{{ $result?->recommended_role ?: 'Belum ada peran rekomendasi' }}</span>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#talentResultModal"
                                        data-title="{{ $schedule->title ?? 'Tes bakat' }}"
                                        data-extracurricular="{{ $schedule->extracurricular->name ?? '-' }}"
                                        data-score="{{ $result?->overall_score !== null ? number_format((float) $result->overall_score, 0, ',', '.') : '—' }}"
                                        data-category="{{ $result?->ability_category ?: 'Belum ada kategori' }}"
                                        data-group="{{ $result?->training_group ?: 'Belum ada kelompok' }}"
                                        data-role="{{ $result?->recommended_role ?: 'Belum ada peran rekomendasi' }}"
                                        data-notes="{{ $result?->coach_notes ?: 'Belum ada catatan pembina.' }}"
                                        data-recommendation="{{ $result?->recommendation ?: 'Belum ada rekomendasi tambahan.' }}"
                                        data-date="{{ $result?->published_at?->translatedFormat('d F Y') ?: (optional($schedule->activity_date)->translatedFormat('d F Y') ?: '-') }}"
                                    >
                                        <i class="bi bi-eye"></i>Lihat Hasil
                                    </button>
                                </div>
                            @else
                                <div class="form-actions">
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#talentScheduleModal"
                                        data-title="{{ $schedule->title ?? 'Tes bakat' }}"
                                        data-extracurricular="{{ $schedule->extracurricular->name ?? '-' }}"
                                        data-date="{{ optional($schedule->activity_date)->translatedFormat('d F Y') ?: '-' }}"
                                        data-time="{{ \Illuminate\Support\Str::substr((string) $schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $schedule->end_time, 0, 5) }}"
                                        data-location="{{ $schedule->location ?: 'Lokasi belum tersedia.' }}"
                                        data-equipment="{{ $schedule->equipment ?: 'Tidak ada peralatan khusus.' }}"
                                        data-instructions="{{ $schedule->instructions ?: 'Ikuti arahan pembina saat tes berlangsung.' }}"
                                        data-status="{{ $test->student_status_label }}"
                                    >
                                        <i class="bi bi-eye"></i>Lihat Detail
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="icon"><i class="bi bi-clipboard2-pulse"></i></div>
                            <p class="mb-0">
                                {{ $tab === 'upcoming'
                                    ? 'Belum ada tes bakat mendatang.'
                                    : ($tab === 'pending'
                                        ? 'Belum ada tes yang sedang menunggu hasil.'
                                        : 'Belum ada riwayat hasil tes yang dipublikasikan.') }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="card-body">{{ $rows->links() }}</div>
        </div>
    @endif

    <div class="modal fade" id="talentScheduleModal" tabindex="-1" aria-labelledby="talentScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content talent-detail-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="h5 mb-1" id="talentScheduleModalLabel">Detail Tes Bakat</h2>
                        <p class="text-muted mb-0" id="talentScheduleMeta">Informasi jadwal tes bakat</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="talent-detail-grid mb-3">
                        <div class="talent-detail-item"><span>Ekstrakurikuler</span><strong id="talentScheduleExtracurricular">-</strong></div>
                        <div class="talent-detail-item"><span>Status</span><strong id="talentScheduleStatus">-</strong></div>
                        <div class="talent-detail-item"><span>Tanggal</span><strong id="talentScheduleDate">-</strong></div>
                        <div class="talent-detail-item"><span>Jam</span><strong id="talentScheduleTime">-</strong></div>
                        <div class="talent-detail-item"><span>Lokasi</span><strong id="talentScheduleLocation">-</strong></div>
                        <div class="talent-detail-item"><span>Peralatan</span><strong id="talentScheduleEquipment">-</strong></div>
                    </div>
                    <div class="talent-detail-item">
                        <span>Instruksi</span>
                        <p class="mb-0" id="talentScheduleInstructions">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="talentResultModal" tabindex="-1" aria-labelledby="talentResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content talent-detail-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="h5 mb-1" id="talentResultModalLabel">Hasil Tes Bakat</h2>
                        <p class="text-muted mb-0" id="talentResultMeta">Ringkasan hasil tes bakat yang dipublikasikan</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="talent-detail-grid mb-3">
                        <div class="talent-detail-item"><span>Ekstrakurikuler</span><strong id="talentResultExtracurricular">-</strong></div>
                        <div class="talent-detail-item"><span>Nilai akhir</span><strong id="talentResultScore">-</strong></div>
                        <div class="talent-detail-item"><span>Kategori</span><strong id="talentResultCategory">-</strong></div>
                        <div class="talent-detail-item"><span>Kelompok kemampuan</span><strong id="talentResultGroup">-</strong></div>
                        <div class="talent-detail-item"><span>Peran rekomendasi</span><strong id="talentResultRole">-</strong></div>
                        <div class="talent-detail-item"><span>Tanggal penilaian</span><strong id="talentResultDate">-</strong></div>
                    </div>
                    <div class="talent-detail-item mb-3">
                        <span>Catatan pembina</span>
                        <p class="mb-0" id="talentResultNotes">-</p>
                    </div>
                    <div class="talent-detail-item">
                        <span>Rekomendasi</span>
                        <p class="mb-0" id="talentResultRecommendation">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const scheduleModal = document.getElementById('talentScheduleModal');
            if (scheduleModal) {
                const scheduleFields = {
                    meta: document.getElementById('talentScheduleMeta'),
                    extracurricular: document.getElementById('talentScheduleExtracurricular'),
                    status: document.getElementById('talentScheduleStatus'),
                    date: document.getElementById('talentScheduleDate'),
                    time: document.getElementById('talentScheduleTime'),
                    location: document.getElementById('talentScheduleLocation'),
                    equipment: document.getElementById('talentScheduleEquipment'),
                    instructions: document.getElementById('talentScheduleInstructions'),
                };

                scheduleModal.addEventListener('show.bs.modal', (event) => {
                    const trigger = event.relatedTarget;
                    if (!(trigger instanceof HTMLElement)) {
                        return;
                    }

                    scheduleFields.meta.textContent = trigger.dataset.title || 'Informasi jadwal tes bakat';
                    scheduleFields.extracurricular.textContent = trigger.dataset.extracurricular || '-';
                    scheduleFields.status.textContent = trigger.dataset.status || '-';
                    scheduleFields.date.textContent = trigger.dataset.date || '-';
                    scheduleFields.time.textContent = trigger.dataset.time || '-';
                    scheduleFields.location.textContent = trigger.dataset.location || '-';
                    scheduleFields.equipment.textContent = trigger.dataset.equipment || '-';
                    scheduleFields.instructions.textContent = trigger.dataset.instructions || '-';
                });
            }

            const resultModal = document.getElementById('talentResultModal');
            if (resultModal) {
                const resultFields = {
                    meta: document.getElementById('talentResultMeta'),
                    extracurricular: document.getElementById('talentResultExtracurricular'),
                    score: document.getElementById('talentResultScore'),
                    category: document.getElementById('talentResultCategory'),
                    group: document.getElementById('talentResultGroup'),
                    role: document.getElementById('talentResultRole'),
                    date: document.getElementById('talentResultDate'),
                    notes: document.getElementById('talentResultNotes'),
                    recommendation: document.getElementById('talentResultRecommendation'),
                };

                resultModal.addEventListener('show.bs.modal', (event) => {
                    const trigger = event.relatedTarget;
                    if (!(trigger instanceof HTMLElement)) {
                        return;
                    }

                    resultFields.meta.textContent = trigger.dataset.title || 'Ringkasan hasil tes bakat';
                    resultFields.extracurricular.textContent = trigger.dataset.extracurricular || '-';
                    resultFields.score.textContent = trigger.dataset.score || '-';
                    resultFields.category.textContent = trigger.dataset.category || '-';
                    resultFields.group.textContent = trigger.dataset.group || '-';
                    resultFields.role.textContent = trigger.dataset.role || '-';
                    resultFields.date.textContent = trigger.dataset.date || '-';
                    resultFields.notes.textContent = trigger.dataset.notes || '-';
                    resultFields.recommendation.textContent = trigger.dataset.recommendation || '-';
                });
            }
        })();
    </script>
@endpush
