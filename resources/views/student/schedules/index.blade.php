@extends('layouts.app')

@section('page_title', 'Jadwal Kegiatan Saya')
@section('page_subtitle', 'Lihat jadwal mendatang, riwayat kegiatan, dan tes bakat terdekat.')

@push('styles')
    <style>
        .schedule-summary-strip {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .schedule-summary-card {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            min-height: 132px;
            padding: 1rem 1.05rem;
            border-radius: 22px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.06);
        }

        .schedule-summary-card.is-link {
            text-decoration: none;
            color: inherit;
            transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .schedule-summary-card.is-link:hover {
            transform: translateY(-2px);
            border-color: #a8c3f1;
            box-shadow: 0 18px 30px rgba(16, 35, 63, 0.1);
        }

        .schedule-summary-card .eyebrow {
            margin: 0;
            color: #657a93;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .schedule-summary-card .value {
            margin: 0;
            font-size: 1.45rem;
            line-height: 1.1;
            font-weight: 800;
            color: #18334f;
        }

        .schedule-summary-card .meta {
            margin-top: auto;
            color: #60748c;
            font-size: 0.8rem;
            line-height: 1.45;
        }

        .schedule-filter-card {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.05);
            overflow: hidden;
        }

        .schedule-history-filter-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1.4fr) minmax(0, 0.95fr) minmax(0, 0.95fr) auto;
            gap: 1rem;
            align-items: end;
        }

        .schedule-filter-field {
            min-width: 0;
        }

        .schedule-tab-nav {
            display: flex;
            gap: 0.75rem;
            overflow-x: auto;
            padding-bottom: 0.15rem;
        }

        .schedule-tab-link {
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

        .schedule-tab-link.is-active {
            color: #1849cb;
            border-color: #9ebfff;
            background: linear-gradient(180deg, #fbfdff 0%, #eef5ff 100%);
            box-shadow: 0 12px 22px rgba(31, 94, 255, 0.08);
        }

        .schedule-section-heading {
            padding: 1rem 1.2rem 0.85rem;
            border-bottom: 1px solid #e8eef5;
            background: rgba(255, 255, 255, 0.72);
        }

        .schedule-section-heading h2 {
            margin: 0 0 0.35rem;
            font-size: 1.02rem;
            line-height: 1.3;
            font-weight: 800;
            color: #18334f;
        }

        .schedule-section-heading p {
            margin: 0;
            color: #667b93;
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .schedule-filter-body {
            padding: 1rem 1.2rem 1.2rem;
        }

        .schedule-history-row {
            opacity: 0.82;
        }

        .schedule-history-row:hover {
            opacity: 1;
        }

        .schedule-mobile-card.is-history {
            opacity: 0.88;
        }

        .schedule-inline-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .schedule-filter-actions {
            display: flex;
            justify-content: flex-start;
            gap: 0.75rem;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .schedule-filter-actions .btn {
            min-width: 124px;
        }

        @media (max-width: 1199.98px) {
            .schedule-summary-strip {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .schedule-history-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .schedule-history-filter-grid .schedule-filter-actions-wrap {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 767.98px) {
            .schedule-summary-strip {
                display: flex;
                overflow-x: auto;
                gap: 0.75rem;
                padding-bottom: 0.15rem;
            }

            .schedule-summary-card {
                flex: 0 0 78%;
                min-height: 118px;
            }

            .schedule-section-heading,
            .schedule-filter-body {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .schedule-history-filter-grid {
                grid-template-columns: 1fr;
                gap: 0.85rem;
            }

            .schedule-history-filter-grid .schedule-filter-actions-wrap {
                grid-column: auto;
            }

            .schedule-filter-actions {
                justify-content: stretch;
                flex-wrap: wrap;
            }

            .schedule-filter-actions .btn {
                flex: 1 1 0;
                min-width: 0;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $upcomingQuery = array_merge(request()->except(['tab', 'upcoming_page', 'history_page']), ['tab' => 'upcoming']);
        $historyQuery = array_merge(request()->except(['tab', 'upcoming_page', 'history_page']), ['tab' => 'history']);
    @endphp

    <div class="schedule-summary-strip mb-3">
        <div class="schedule-summary-card">
            <p class="eyebrow">Jadwal Berikutnya</p>
            <p class="value">{{ $nextSchedule ? optional($nextSchedule->activity_date)->format('d M') : '—' }}</p>
            <div class="meta">
                @if($nextSchedule)
                    <strong>{{ $nextSchedule->title }}</strong><br>
                    {{ $nextSchedule->extracurricular->name ?? '-' }}
                @else
                    Belum ada jadwal mendatang.
                @endif
            </div>
        </div>
        <div class="schedule-summary-card">
            <p class="eyebrow">Kegiatan Hari Ini</p>
            <p class="value">{{ $todayCount }}</p>
            <div class="meta">Jadwal yang berlangsung hari ini.</div>
        </div>
        <div class="schedule-summary-card">
            <p class="eyebrow">Kegiatan Minggu Ini</p>
            <p class="value">{{ $weekCount }}</p>
            <div class="meta">Termasuk kegiatan rutin dan tes bakat.</div>
        </div>
        <a href="{{ route('student.schedules.index', $historyQuery) }}#riwayat-kegiatan" class="schedule-summary-card is-link">
            <p class="eyebrow">Jadwal Selesai</p>
            <p class="value">{{ $completedCount }}</p>
            <div class="meta">Lihat riwayat kegiatan.</div>
        </a>
        <div class="schedule-summary-card">
            <p class="eyebrow">Tes Bakat Terdekat</p>
            <p class="value">{{ $nearestTalentTest ? optional($nearestTalentTest->activity_date)->format('d M') : '—' }}</p>
            <div class="meta">
                @if($nearestTalentTest)
                    <strong>{{ $nearestTalentTest->title }}</strong><br>
                    {{ $nearestTalentTest->location }}
                @else
                    Belum ada tes bakat terjadwal.
                @endif
            </div>
        </div>
    </div>

    <div class="schedule-filter-card mb-3">
        <div class="schedule-section-heading">
            <h2>Filter Tanggal</h2>
            <p>Pilih tanggal tertentu untuk menampilkan jadwal yang sesuai.</p>
        </div>
        <div class="schedule-filter-body">
            <form class="toolbar-grid" method="get" action="{{ route('student.schedules.index') }}">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if($tab === 'history')
                    <input type="hidden" name="history_extracurricular" value="{{ $historyExtracurricular }}">
                    <input type="hidden" name="history_type" value="{{ $historyType }}">
                    <input type="hidden" name="history_month" value="{{ $historyMonth }}">
                    <input type="hidden" name="history_year" value="{{ $historyYear }}">
                @endif
                <div class="toolbar-col-4">
                    <label class="form-label">Tanggal kegiatan</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control">
                </div>
                <div class="toolbar-col-2">
                    <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button>
                </div>
                <div class="toolbar-col-2">
                    <a href="{{ route('student.schedules.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="schedule-tab-nav">
                <a href="{{ route('student.schedules.index', $upcomingQuery) }}" class="schedule-tab-link {{ $tab === 'upcoming' ? 'is-active' : '' }}">Jadwal Mendatang</a>
                <a href="{{ route('student.schedules.index', $historyQuery) }}#riwayat-kegiatan" class="schedule-tab-link {{ $tab === 'history' ? 'is-active' : '' }}">Riwayat</a>
            </div>
        </div>
    </div>

    @if($tab === 'history')
        <div class="schedule-filter-card mb-3" id="riwayat-kegiatan">
            <div class="schedule-section-heading">
                <h2>Filter Riwayat</h2>
                <p>Saring kegiatan yang sudah selesai atau dibatalkan.</p>
            </div>
            <div class="schedule-filter-body">
                <form class="schedule-history-filter-grid" method="get" action="{{ route('student.schedules.index') }}">
                    <input type="hidden" name="tab" value="history">
                    @if($date)
                        <input type="hidden" name="date" value="{{ $date }}">
                    @endif
                    <div class="schedule-filter-field">
                        <label class="form-label">Ekstrakurikuler</label>
                        <select name="history_extracurricular" class="form-select">
                            <option value="">Semua ekstrakurikuler</option>
                            @foreach($extracurriculars as $extracurricular)
                                <option value="{{ $extracurricular->id }}" @selected($historyExtracurricular == $extracurricular->id)>{{ $extracurricular->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="schedule-filter-field">
                        <label class="form-label">Jenis kegiatan</label>
                        <select name="history_type" class="form-select">
                            <option value="">Semua jenis</option>
                            <option value="activity" @selected($historyType === 'activity')>Kegiatan Ekskul</option>
                            <option value="talent_test" @selected($historyType === 'talent_test')>Tes Bakat</option>
                        </select>
                    </div>
                    <div class="schedule-filter-field">
                        <label class="form-label">Bulan</label>
                        <select name="history_month" class="form-select">
                            <option value="">Semua bulan</option>
                            @foreach($historyMonths as $month)
                                <option value="{{ $month }}" @selected($historyMonth === $month)>{{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="schedule-filter-field">
                        <label class="form-label">Tahun</label>
                        <select name="history_year" class="form-select">
                            <option value="">Semua tahun</option>
                            @foreach($historyYears as $year)
                                <option value="{{ $year }}" @selected($historyYear === $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="schedule-filter-actions-wrap">
                        <div class="schedule-filter-actions">
                            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan</button>
                            <a href="{{ route('student.schedules.index', ['tab' => 'history']) }}#riwayat-kegiatan" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">{{ $tab === 'history' ? 'Riwayat Kegiatan' : 'Jadwal Mendatang' }}</div>
        <div class="card-body p-0">
            @php
                $rows = $tab === 'history' ? $historySchedules : $upcomingSchedules;
            @endphp
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Ekstrakurikuler</th>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Lokasi</th>
                        @if($tab === 'history')
                            <th>Jenis</th>
                        @endif
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr class="{{ $tab === 'history' ? 'schedule-history-row' : '' }}">
                            <td>{{ $row->extracurricular->name ?? '-' }}</td>
                            <td>{{ $row->title }}</td>
                            <td>{{ optional($row->activity_date)->format('d-m-Y') }}</td>
                            <td>{{ \Illuminate\Support\Str::substr($row->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($row->end_time, 0, 5) }}</td>
                            <td>{{ $row->location }}</td>
                            @if($tab === 'history')
                                <td>{{ $row->student_type_label }}</td>
                            @endif
                            <td><span class="badge {{ $row->student_display_status === 'completed' ? 'badge-status-secondary' : ($row->student_display_status === 'cancelled' ? 'badge-status-danger' : ($row->student_display_status === 'today' ? 'badge-status-warning' : 'badge-status-success')) }}">{{ $row->student_display_label }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tab === 'history' ? 7 : 6 }}">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-calendar-x"></i></div>
                                    <p class="mb-0">{{ $tab === 'history' ? 'Belum ada riwayat kegiatan.' : 'Belum ada jadwal mendatang.' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-stack-table p-3">
                @forelse($rows as $row)
                    <div class="mobile-data-card schedule-mobile-card {{ $tab === 'history' ? 'is-history' : '' }}">
                        <div class="mobile-data-card-header">
                            <h3 class="mobile-data-card-title">{{ $row->title }}</h3>
                            <span class="badge {{ $row->student_display_status === 'completed' ? 'badge-status-secondary' : ($row->student_display_status === 'cancelled' ? 'badge-status-danger' : ($row->student_display_status === 'today' ? 'badge-status-warning' : 'badge-status-success')) }}">{{ $row->student_display_label }}</span>
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">Ekstrakurikuler</span><p class="mobile-data-item-value">{{ $row->extracurricular->name ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($row->activity_date)->format('d-m-Y') }}</p></div>
                            <div><span class="mobile-data-item-label">Jam</span><p class="mobile-data-item-value">{{ \Illuminate\Support\Str::substr($row->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($row->end_time, 0, 5) }}</p></div>
                            <div><span class="mobile-data-item-label">Lokasi</span><p class="mobile-data-item-value">{{ $row->location }}</p></div>
                            @if($tab === 'history')
                                <div><span class="mobile-data-item-label">Jenis kegiatan</span><p class="mobile-data-item-value">{{ $row->student_type_label }}</p></div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-calendar-x"></i></div>
                        <p class="mb-0">{{ $tab === 'history' ? 'Belum ada riwayat kegiatan.' : 'Belum ada jadwal mendatang.' }}</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $rows->links() }}</div>
    </div>
@endsection
