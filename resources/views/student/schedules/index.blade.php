@extends('layouts.app')

@section('page_title', 'Jadwal Kegiatan Saya')
@section('page_subtitle', 'Lihat jadwal ekstrakurikuler yang diikuti')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Tanggal</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('student.schedules.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Jadwal Kegiatan</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Ekstrakurikuler</th>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Lokasi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($schedules as $row)
                        <tr>
                            <td>{{ $row->extracurricular->name ?? '-' }}</td>
                            <td>{{ $row->title }}</td>
                            <td>{{ optional($row->activity_date)->format('d-m-Y') }}</td>
                            <td>{{ \Illuminate\Support\Str::substr($row->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($row->end_time, 0, 5) }}</td>
                            <td>{{ $row->location }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-calendar-x"></i></div>
                                    <p class="mb-0">Belum ada jadwal kegiatan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-stack-table p-3">
                @forelse($schedules as $row)
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <h3 class="mobile-data-card-title">{{ $row->title }}</h3>
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">Ekstrakurikuler</span><p class="mobile-data-item-value">{{ $row->extracurricular->name ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($row->activity_date)->format('d-m-Y') }}</p></div>
                            <div><span class="mobile-data-item-label">Jam</span><p class="mobile-data-item-value">{{ \Illuminate\Support\Str::substr($row->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($row->end_time, 0, 5) }}</p></div>
                            <div><span class="mobile-data-item-label">Lokasi</span><p class="mobile-data-item-value">{{ $row->location }}</p></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-calendar-x"></i></div>
                        <p class="mb-0">Belum ada jadwal kegiatan.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $schedules->links() }}</div>
    </div>
@endsection
