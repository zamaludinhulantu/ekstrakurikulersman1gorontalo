@extends('layouts.app')

@section('page_title', 'Jadwal Kegiatan Pembina')
@section('page_subtitle', 'Kelola jadwal kegiatan ekstrakurikuler')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('coach.schedules.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Tambah Jadwal</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Filter Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select">
                        <option value="">Semua Ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string)$extracurricularId === (string)$item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button></div>
                <div class="col-md-2"><a href="{{ route('coach.schedules.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="desktop-table table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Ekstrakurikuler</th>
                    <th>Judul</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Lokasi</th>
                    <th>Aksi</th>
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
                        <td class="d-flex flex-wrap gap-1">
                            <a href="{{ route('coach.schedules.edit', $row) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                            <form method="post" action="{{ route('coach.schedules.destroy', $row) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-calendar-x"></i></div>
                                <p class="mb-0">Belum ada jadwal.</p>
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
                        <div>
                            <h3 class="mobile-data-card-title">{{ $row->title }}</h3>
                            <div class="small text-muted">{{ $row->extracurricular->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="mobile-data-list mb-3">
                        <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($row->activity_date)->format('d-m-Y') }}</p></div>
                        <div><span class="mobile-data-item-label">Jam</span><p class="mobile-data-item-value">{{ \Illuminate\Support\Str::substr($row->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($row->end_time, 0, 5) }}</p></div>
                        <div><span class="mobile-data-item-label">Lokasi</span><p class="mobile-data-item-value">{{ $row->location }}</p></div>
                    </div>
                    <div class="mobile-data-card-actions">
                        <a href="{{ route('coach.schedules.edit', $row) }}" class="btn btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                        <form method="post" action="{{ route('coach.schedules.destroy', $row) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-calendar-x"></i></div>
                    <p class="mb-0">Belum ada jadwal.</p>
                </div>
            @endforelse
        </div>
        <div class="card-body">{{ $schedules->links() }}</div>
    </div>
@endsection
