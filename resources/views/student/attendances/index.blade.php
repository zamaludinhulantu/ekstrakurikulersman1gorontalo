@extends('layouts.app')

@section('page_title', 'Riwayat Presensi Pribadi')
@section('page_subtitle', 'Pantau kehadiran kegiatan ekstrakurikuler secara lebih lengkap.')

@push('styles')
    <style>
        .attendance-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .attendance-summary-card {
            padding: 0.95rem 1rem;
            border-radius: 22px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.06);
            min-height: 124px;
        }

        .attendance-summary-card .label {
            margin: 0 0 0.35rem;
            color: #677b93;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .attendance-summary-card .value {
            margin: 0;
            color: #18334f;
            font-size: 1.35rem;
            line-height: 1.1;
            font-weight: 800;
        }

        .attendance-summary-card .meta {
            margin-top: 0.35rem;
            color: #60748c;
            font-size: 0.8rem;
        }

        .attendance-rate-card {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.05);
            overflow: hidden;
        }

        .attendance-rate-header {
            padding: 0.95rem 1.15rem 0.85rem;
            border-bottom: 1px solid #e8eef5;
            background: rgba(255, 255, 255, 0.74);
        }

        .attendance-rate-body {
            padding: 1rem 1.15rem 1.1rem;
        }

        .attendance-rate-track {
            height: 10px;
            border-radius: 999px;
            background: #edf3fb;
            overflow: hidden;
        }

        .attendance-rate-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #1f5eff 0%, #69a8ff 100%);
        }

        .attendance-filter-panel {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 14px 28px rgba(16, 35, 63, 0.05);
            overflow: hidden;
        }

        .attendance-filter-header {
            padding: 1rem 1.2rem 0.85rem;
            border-bottom: 1px solid #e8eef5;
            background: rgba(255, 255, 255, 0.72);
        }

        .attendance-filter-header h2 {
            margin: 0 0 0.35rem;
            font-size: 1.02rem;
            line-height: 1.3;
            font-weight: 800;
            color: #18334f;
        }

        .attendance-filter-header p {
            margin: 0;
            color: #667b93;
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .attendance-filter-body {
            padding: 1rem 1.2rem 1.2rem;
        }

        .attendance-quick-filters {
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .attendance-quick-filters .btn {
            min-height: 36px;
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
        }

        .attendance-export-menu {
            min-width: 14rem;
        }

        .attendance-note {
            max-width: 20rem;
            white-space: normal;
        }

        @media (max-width: 1199.98px) {
            .attendance-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .attendance-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .attendance-rate-header,
            .attendance-rate-body,
            .attendance-filter-header,
            .attendance-filter-body {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $statusBadgeMap = [
            'present' => 'badge-status-success',
            'late' => 'badge-status-warning',
            'permission' => 'badge-status-warning',
            'sick' => 'badge-status-warning',
            'absent' => 'badge-status-danger',
            'pending' => 'badge-status-secondary',
        ];
        $statusLabels = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'permission' => 'Izin',
            'sick' => 'Sakit',
            'absent' => 'Tidak hadir',
            'pending' => 'Belum dicatat',
        ];
    @endphp

    <div class="attendance-summary-grid mb-3">
        <div class="attendance-summary-card">
            <p class="label">Total Pertemuan</p>
            <p class="value">{{ $attendanceSummary['total'] }}</p>
            <div class="meta">Jadwal valid pada periode ini.</div>
        </div>
        <div class="attendance-summary-card">
            <p class="label">Hadir</p>
            <p class="value">{{ $attendanceSummary['present'] }}</p>
            <div class="meta">Kehadiran tercatat.</div>
        </div>
        <div class="attendance-summary-card">
            <p class="label">Izin</p>
            <p class="value">{{ $attendanceSummary['permission'] }}</p>
            <div class="meta">Tidak hadir dengan izin.</div>
        </div>
        <div class="attendance-summary-card">
            <p class="label">Sakit</p>
            <p class="value">{{ $attendanceSummary['sick'] }}</p>
            <div class="meta">Tidak hadir karena sakit.</div>
        </div>
        <div class="attendance-summary-card">
            <p class="label">Tidak Hadir</p>
            <p class="value">{{ $attendanceSummary['absent'] }}</p>
            <div class="meta">Termasuk alpa.</div>
        </div>
        <div class="attendance-summary-card">
            <p class="label">Persentase Kehadiran</p>
            <p class="value">{{ rtrim(rtrim(number_format($attendanceRate, 1, '.', ''), '0'), '.') }}%</p>
            <div class="meta">Berdasarkan data presensi valid.</div>
        </div>
    </div>

    <div class="attendance-rate-card mb-3">
        <div class="attendance-rate-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="section-kicker"><i class="bi bi-graph-up-arrow"></i>Ringkasan Kehadiran</div>
                    <h2 class="h6 mb-1">Progress Kehadiran</h2>
                </div>
            </div>
        </div>
        <div class="attendance-rate-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-2">
                <p class="toolbar-hint mb-0">Jadwal yang dibatalkan tidak dihitung ke statistik kehadiran.</p>
                <strong class="fs-5">{{ rtrim(rtrim(number_format($attendanceRate, 1, '.', ''), '0'), '.') }}%</strong>
            </div>
            <div class="attendance-rate-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) round($attendanceRate) }}">
                <div class="attendance-rate-bar" style="width: {{ min(100, max(0, $attendanceRate)) }}%"></div>
            </div>
        </div>
    </div>

    <div class="attendance-filter-panel mb-3">
        <div class="attendance-filter-header">
            <h2>Filter Presensi</h2>
            <p>Saring riwayat berdasarkan periode, ekstrakurikuler, dan status kehadiran.</p>
        </div>
        <div class="attendance-filter-body">
            <div class="attendance-quick-filters">
                <a href="{{ route('student.attendances.index', array_merge(request()->except(['period', 'page']), ['period' => 'month'])) }}" class="btn {{ $period === 'month' ? 'btn-primary' : 'btn-outline-secondary' }}">Bulan ini</a>
                <a href="{{ route('student.attendances.index', array_merge(request()->except(['period', 'page']), ['period' => 'semester'])) }}" class="btn {{ $period === 'semester' ? 'btn-primary' : 'btn-outline-secondary' }}">Semester ini</a>
                <a href="{{ route('student.attendances.index', array_merge(request()->except(['period', 'page']), ['period' => 'all'])) }}" class="btn {{ $period === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">Semua riwayat</a>
            </div>

            <form class="toolbar-grid" method="get" action="{{ route('student.attendances.index') }}">
                <input type="hidden" name="period" value="{{ $period }}">
                <div class="toolbar-col-4">
                    <label class="form-label">Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select">
                        <option value="">Semua ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select">
                        <option value="">Semua bulan</option>
                        @foreach($monthOptions as $itemMonth)
                            <option value="{{ $itemMonth }}" @selected($month === $itemMonth)>{{ \Carbon\Carbon::create()->month((int) $itemMonth)->translatedFormat('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        <option value="">Semua tahun</option>
                        @foreach($yearOptions as $itemYear)
                            <option value="{{ $itemYear }}" @selected($year === $itemYear)>{{ $itemYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        @foreach($statusLabels as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-1">
                    <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button>
                </div>
                <div class="toolbar-col-1">
                    <a href="{{ route('student.attendances.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a>
                </div>
                <div class="toolbar-col-12 d-flex justify-content-end">
                    <div class="dropdown">
                        <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i>Ekspor
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end attendance-export-menu">
                            <li><button type="button" class="dropdown-item" onclick="window.print()"><i class="bi bi-file-earmark-pdf me-2"></i>Unduh PDF</button></li>
                            <li><a class="dropdown-item" href="{{ route('student.attendances.export', array_merge(request()->query(), ['format' => 'csv'])) }}"><i class="bi bi-filetype-csv me-2"></i>Unduh CSV</a></li>
                            <li><a class="dropdown-item" href="{{ route('student.attendances.export', array_merge(request()->query(), ['format' => 'xls'])) }}"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Unduh Excel</a></li>
                            <li><button type="button" class="dropdown-item" onclick="window.print()"><i class="bi bi-printer me-2"></i>Cetak riwayat</button></li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Riwayat Presensi</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0 table-compact">
                    <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Ekstrakurikuler</th>
                        <th>Nama Kegiatan</th>
                        <th>Waktu</th>
                        <th>Status Kehadiran</th>
                        <th>Catatan</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($attendances as $row)
                        @php
                            $statusValue = $row->student_attendance_status;
                            $statusLabel = $statusLabels[$statusValue] ?? $row->student_attendance_label;
                        @endphp
                        <tr>
                            <td>{{ optional($row->activity_date)->translatedFormat('d F Y') ?: '-' }}</td>
                            <td>{{ $row->extracurricular->name ?? '-' }}</td>
                            <td>{{ $row->title ?: '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::substr((string) $row->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $row->end_time, 0, 5) }}</td>
                            <td><span class="badge {{ $statusBadgeMap[$statusValue] ?? 'badge-status-secondary' }}">{{ $statusLabel }}</span></td>
                            <td class="attendance-note">{{ $row->student_attendance_notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-check2-square"></i></div>
                                    <p class="mb-0">Belum ada riwayat presensi pada periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-stack-table p-3">
                @forelse($attendances as $row)
                    @php
                        $statusValue = $row->student_attendance_status;
                        $statusLabel = $statusLabels[$statusValue] ?? $row->student_attendance_label;
                    @endphp
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <h3 class="mobile-data-card-title">{{ $row->title ?: 'Kegiatan ekstrakurikuler' }}</h3>
                            <span class="badge {{ $statusBadgeMap[$statusValue] ?? 'badge-status-secondary' }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($row->activity_date)->translatedFormat('d F Y') ?: '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Ekstrakurikuler</span><p class="mobile-data-item-value">{{ $row->extracurricular->name ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Waktu</span><p class="mobile-data-item-value">{{ \Illuminate\Support\Str::substr((string) $row->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr((string) $row->end_time, 0, 5) }}</p></div>
                            <div><span class="mobile-data-item-label">Catatan</span><p class="mobile-data-item-value">{{ $row->student_attendance_notes ?: '—' }}</p></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-check2-square"></i></div>
                        <p class="mb-0">Belum ada riwayat presensi pada periode ini.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $attendances->links() }}</div>
    </div>
@endsection
