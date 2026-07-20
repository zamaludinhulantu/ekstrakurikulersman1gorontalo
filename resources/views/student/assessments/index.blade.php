@extends('layouts.app')

@section('page_title', 'Penilaian Siswa')
@section('page_subtitle', 'Lihat ringkasan nilai, perkembangan, dan catatan pembina yang sudah dipublikasikan.')

@push('styles')
    <style>
        .student-assessment-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .student-assessment-card {
            min-height: 118px;
            padding: 0.95rem 1rem;
            border-radius: 22px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.06);
        }

        .student-assessment-card .label {
            margin: 0 0 0.35rem;
            color: #677b93;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .student-assessment-card .value {
            margin: 0;
            color: #18334f;
            font-size: 1.3rem;
            line-height: 1.1;
            font-weight: 800;
        }

        .student-assessment-card .meta {
            margin-top: 0.35rem;
            color: #60748c;
            font-size: 0.8rem;
            line-height: 1.45;
        }

        .student-assessment-panel {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.05);
            overflow: hidden;
        }

        .student-assessment-panel-header {
            padding: 1rem 1.2rem 0.85rem;
            border-bottom: 1px solid #e8eef5;
            background: rgba(255, 255, 255, 0.72);
        }

        .student-assessment-panel-header h2 {
            margin: 0 0 0.35rem;
            font-size: 1.02rem;
            line-height: 1.3;
            font-weight: 800;
            color: #18334f;
        }

        .student-assessment-panel-header p {
            margin: 0;
            color: #667b93;
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .student-assessment-panel-body {
            padding: 1rem 1.2rem 1.2rem;
        }

        .student-assessment-quick-filters {
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .student-assessment-quick-filters .btn {
            min-height: 36px;
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
        }

        .student-assessment-detail-modal {
            border: 1px solid var(--ui-border);
            border-radius: 28px;
            overflow: hidden;
        }

        .student-assessment-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .student-assessment-detail-item {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: #fbfdff;
            border: 1px solid #dfe9f4;
        }

        .student-assessment-detail-item span {
            display: block;
            margin-bottom: 0.25rem;
            color: #6a7f98;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 800;
        }

        @media (max-width: 1199.98px) {
            .student-assessment-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .student-assessment-summary-grid,
            .student-assessment-detail-grid {
                grid-template-columns: 1fr;
            }

            .student-assessment-panel-header,
            .student-assessment-panel-body {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $summaryCount = $assessmentSummary['count'] ?? 0;
        $summaryAverage = $assessmentSummary['average'];
        $summaryHighest = $assessmentSummary['highest'];
        $latestAssessment = $assessmentSummary['latest'];
    @endphp

    <div class="student-assessment-summary-grid mb-3">
        <div class="student-assessment-card">
            <p class="label">Jumlah Penilaian</p>
            <p class="value">{{ $summaryCount }}</p>
            <div class="meta">{{ $summaryCount > 0 ? 'Penilaian yang sudah dipublikasikan.' : 'Belum ada penilaian yang dipublikasikan.' }}</div>
        </div>
        <div class="student-assessment-card">
            <p class="label">Nilai Rata-rata</p>
            <p class="value">{{ $summaryAverage !== null ? number_format((float) $summaryAverage, 1, ',', '.') : '—' }}</p>
            <div class="meta">{{ $summaryAverage !== null ? 'Rata-rata dari semua nilai yang tersedia.' : 'Belum ada nilai untuk dihitung.' }}</div>
        </div>
        <div class="student-assessment-card">
            <p class="label">Nilai Tertinggi</p>
            <p class="value">{{ $summaryHighest !== null ? number_format((float) $summaryHighest, 0, ',', '.') : '—' }}</p>
            <div class="meta">{{ $summaryHighest !== null ? 'Nilai terbaik pada periode yang ditampilkan.' : 'Belum ada nilai tertinggi.' }}</div>
        </div>
        <div class="student-assessment-card">
            <p class="label">Penilaian Terbaru</p>
            <p class="value">{{ $latestAssessment?->assessment_date?->translatedFormat('d M') ?: '—' }}</p>
            <div class="meta">{{ $latestAssessment?->title ?: 'Belum ada penilaian yang dipublikasikan.' }}</div>
        </div>
    </div>

    @if($assessmentTrend)
        <div class="student-assessment-panel mb-3">
            <div class="student-assessment-panel-header">
                <h2>Perkembangan Penilaian</h2>
                <p>Perbandingan nilai sebelumnya dan nilai terbaru untuk jenis penilaian yang sedang difilter.</p>
            </div>
            <div class="student-assessment-panel-body">
                <div class="student-assessment-summary-grid">
                    <div class="student-assessment-card">
                        <p class="label">Nilai Sebelumnya</p>
                        <p class="value">{{ number_format((float) $assessmentTrend['previous'], 0, ',', '.') }}</p>
                    </div>
                    <div class="student-assessment-card">
                        <p class="label">Nilai Terbaru</p>
                        <p class="value">{{ number_format((float) $assessmentTrend['latest'], 0, ',', '.') }}</p>
                    </div>
                    <div class="student-assessment-card">
                        <p class="label">Selisih Nilai</p>
                        <p class="value">{{ ($assessmentTrend['difference'] > 0 ? '+' : '').number_format((float) $assessmentTrend['difference'], 0, ',', '.') }}</p>
                    </div>
                    <div class="student-assessment-card">
                        <p class="label">Status</p>
                        <p class="value">
                            {{ $assessmentTrend['status'] === 'up' ? 'Meningkat' : ($assessmentTrend['status'] === 'down' ? 'Menurun' : 'Tetap') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="student-assessment-panel mb-3">
        <div class="student-assessment-panel-header">
            <h2>Filter Penilaian</h2>
            <p>Saring penilaian berdasarkan ekstrakurikuler, jenis, dan periode.</p>
        </div>
        <div class="student-assessment-panel-body">
            @if($summaryCount > 0)
                <div class="student-assessment-quick-filters">
                    <a href="{{ route('student.assessments.index', array_merge(request()->except(['period', 'page']), ['period' => 'latest'])) }}" class="btn {{ $period === 'latest' ? 'btn-primary' : 'btn-outline-secondary' }}">Terbaru</a>
                    <a href="{{ route('student.assessments.index', array_merge(request()->except(['period', 'page']), ['period' => 'month'])) }}" class="btn {{ $period === 'month' ? 'btn-primary' : 'btn-outline-secondary' }}">Bulan ini</a>
                    <a href="{{ route('student.assessments.index', array_merge(request()->except(['period', 'page']), ['period' => 'semester'])) }}" class="btn {{ $period === 'semester' ? 'btn-primary' : 'btn-outline-secondary' }}">Semester ini</a>
                    <a href="{{ route('student.assessments.index', array_merge(request()->except(['period', 'page']), ['period' => 'all'])) }}" class="btn {{ $period === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
                </div>
            @endif

            <form class="toolbar-grid" method="get" action="{{ route('student.assessments.index') }}">
                <input type="hidden" name="period" value="{{ $period }}">
                <div class="toolbar-col-4">
                    <label class="form-label">Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select" @disabled($extracurriculars->isEmpty())>
                        <option value="">Semua ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-4">
                    <label class="form-label">Jenis penilaian</label>
                    <select name="title" class="form-select" @disabled($titleOptions->isEmpty())>
                        <option value="">Semua jenis penilaian</option>
                        @foreach($titleOptions as $itemTitle)
                            <option value="{{ $itemTitle }}" @selected($title === $itemTitle)>{{ $itemTitle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select" @disabled($monthOptions->isEmpty())>
                        <option value="">Semua bulan</option>
                        @foreach($monthOptions as $itemMonth)
                            <option value="{{ $itemMonth }}" @selected($month === $itemMonth)>{{ \Carbon\Carbon::create()->month((int) $itemMonth)->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select" @disabled($yearOptions->isEmpty())>
                        <option value="">Semua tahun</option>
                        @foreach($yearOptions as $itemYear)
                            <option value="{{ $itemYear }}" @selected($year === $itemYear)>{{ $itemYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button>
                </div>
                <div class="toolbar-col-2">
                    <a href="{{ route('student.assessments.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Penilaian</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0 table-compact">
                    <thead>
                    <tr>
                        <th>Ekstrakurikuler</th>
                        <th>Jenis Penilaian</th>
                        <th>Nilai</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($assessments as $row)
                        <tr>
                            <td>{{ $row->extracurricular->name ?? '-' }}</td>
                            <td>{{ $row->title }}</td>
                            <td>{{ $row->student_score_value !== null ? number_format((float) $row->student_score_value, 0, ',', '.') : '—' }}</td>
                            <td><span class="badge {{ $row->student_category_class }}">{{ $row->student_category }}</span></td>
                            <td>{{ optional($row->assessment_date)->translatedFormat('d F Y') ?: '-' }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary action-button-compact assessment-detail-trigger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assessmentDetailModal"
                                    data-type="{{ $row->title }}"
                                    data-title="{{ $row->title }}"
                                    data-student="{{ $row->student_category }}"
                                    data-extracurricular="{{ $row->extracurricular->name ?? 'Ekstrakurikuler belum dipilih' }}"
                                    data-score="{{ $row->student_score_value !== null ? number_format((float) $row->student_score_value, 0, ',', '.') : '—' }}"
                                    data-date="{{ optional($row->assessment_date)->translatedFormat('d F Y') ?: '-' }}"
                                    data-coach="{{ $row->coach?->user?->name ?: 'Pembina tidak ditampilkan' }}"
                                    data-description="{{ $row->description ?: 'Belum ada catatan pembina.' }}"
                                    data-recommendation="{{ $row->student_recommendation }}"
                                    data-category="{{ $row->student_category }}"
                                >
                                    <i class="bi bi-eye"></i>Lihat Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-award"></i></div>
                                    <p class="mb-0">Belum ada penilaian yang dipublikasikan. Penilaian dari pembina akan muncul setelah selesai diperiksa.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-stack-table p-3">
                @forelse($assessments as $row)
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <div>
                                <h3 class="mobile-data-card-title">{{ $row->title }}</h3>
                                <div class="small text-muted">{{ $row->extracurricular->name ?? '-' }}</div>
                            </div>
                            <span class="badge {{ $row->student_category_class }}">{{ $row->student_category }}</span>
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">Nilai</span><p class="mobile-data-item-value">{{ $row->student_score_value !== null ? number_format((float) $row->student_score_value, 0, ',', '.') : '—' }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($row->assessment_date)->translatedFormat('d F Y') ?: '-' }}</p></div>
                        </div>
                        <div class="form-actions mt-3">
                            <button
                                type="button"
                                class="btn btn-outline-primary w-100 assessment-detail-trigger"
                                data-bs-toggle="modal"
                                data-bs-target="#assessmentDetailModal"
                                data-type="{{ $row->title }}"
                                data-title="{{ $row->title }}"
                                data-student="{{ $row->student_category }}"
                                data-extracurricular="{{ $row->extracurricular->name ?? 'Ekstrakurikuler belum dipilih' }}"
                                data-score="{{ $row->student_score_value !== null ? number_format((float) $row->student_score_value, 0, ',', '.') : '—' }}"
                                data-date="{{ optional($row->assessment_date)->translatedFormat('d F Y') ?: '-' }}"
                                data-coach="{{ $row->coach?->user?->name ?: 'Pembina tidak ditampilkan' }}"
                                data-description="{{ $row->description ?: 'Belum ada catatan pembina.' }}"
                                data-recommendation="{{ $row->student_recommendation }}"
                                data-category="{{ $row->student_category }}"
                            >
                                <i class="bi bi-eye"></i>Lihat Detail
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-award"></i></div>
                        <p class="mb-0">Belum ada penilaian yang dipublikasikan. Penilaian dari pembina akan muncul setelah selesai diperiksa.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $assessments->links() }}</div>
    </div>

    <div class="modal fade" id="assessmentDetailModal" tabindex="-1" aria-labelledby="assessmentDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content student-assessment-detail-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="h5 mb-1" id="assessmentDetailModalLabel">Detail Penilaian</h2>
                        <p class="text-muted mb-0" id="assessmentDetailMeta">Ringkasan penilaian siswa</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="student-assessment-detail-grid mb-3">
                        <div class="student-assessment-detail-item"><span>Ekstrakurikuler</span><strong id="assessmentDetailExtracurricular">-</strong></div>
                        <div class="student-assessment-detail-item"><span>Jenis penilaian</span><strong id="assessmentDetailType">-</strong></div>
                        <div class="student-assessment-detail-item"><span>Nilai</span><strong id="assessmentDetailScore">-</strong></div>
                        <div class="student-assessment-detail-item"><span>Kategori</span><strong id="assessmentDetailCategory">-</strong></div>
                        <div class="student-assessment-detail-item"><span>Tanggal penilaian</span><strong id="assessmentDetailDate">-</strong></div>
                        <div class="student-assessment-detail-item"><span>Nama pembina</span><strong id="assessmentDetailCoach">-</strong></div>
                    </div>
                    <div class="student-assessment-detail-item mb-3">
                        <span>Catatan pembina</span>
                        <p class="mb-0" id="assessmentDetailDescription">Belum ada catatan pembina.</p>
                    </div>
                    <div class="student-assessment-detail-item">
                        <span>Rekomendasi atau tindak lanjut</span>
                        <p class="mb-0" id="assessmentDetailRecommendation">Belum ada rekomendasi tambahan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const modal = document.getElementById('assessmentDetailModal');
            if (!modal) {
                return;
            }

            const fields = {
                meta: document.getElementById('assessmentDetailMeta'),
                extracurricular: document.getElementById('assessmentDetailExtracurricular'),
                type: document.getElementById('assessmentDetailType'),
                score: document.getElementById('assessmentDetailScore'),
                category: document.getElementById('assessmentDetailCategory'),
                date: document.getElementById('assessmentDetailDate'),
                coach: document.getElementById('assessmentDetailCoach'),
                description: document.getElementById('assessmentDetailDescription'),
                recommendation: document.getElementById('assessmentDetailRecommendation'),
            };

            modal.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!(trigger instanceof HTMLElement)) {
                    return;
                }

                fields.meta.textContent = trigger.dataset.title || 'Ringkasan penilaian siswa';
                fields.extracurricular.textContent = trigger.dataset.extracurricular || '-';
                fields.type.textContent = trigger.dataset.type || '-';
                fields.score.textContent = trigger.dataset.score || '-';
                fields.category.textContent = trigger.dataset.category || '-';
                fields.date.textContent = trigger.dataset.date || '-';
                fields.coach.textContent = trigger.dataset.coach || '-';
                fields.description.textContent = trigger.dataset.description || 'Belum ada catatan pembina.';
                fields.recommendation.textContent = trigger.dataset.recommendation || 'Belum ada rekomendasi tambahan.';
            });
        })();
    </script>
@endpush
