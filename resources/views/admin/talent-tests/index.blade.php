@extends('layouts.app')

@section('page_title', 'Monitoring Tes Bakat')
@section('page_subtitle', 'Pantau jadwal, peserta, dan status hasil tes seluruh ekstrakurikuler')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="toolbar-grid">
                <div class="toolbar-col-4">
                    <label class="form-label">Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select">
                        <option value="">Semua ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-4">
                    <label class="form-label">Pembina</label>
                    <select name="coach_id" class="form-select">
                        <option value="">Semua pembina</option>
                        @foreach($coaches as $item)
                            <option value="{{ $item->id }}" @selected((string) $coachId === (string) $item->id)>{{ $item->user->name ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="scheduled" @selected($status === 'scheduled')>Terjadwal</option>
                        <option value="completed" @selected($status === 'completed')>Selesai</option>
                        <option value="cancelled" @selected($status === 'cancelled')>Dibatalkan</option>
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Ekstrakurikuler</th>
                    <th>Nama Tes</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Peserta</th>
                </tr>
                </thead>
                <tbody>
                @forelse($tests as $test)
                    <tr>
                        <td>{{ $test->extracurricular->name ?? '-' }}</td>
                        <td>{{ $test->title }}</td>
                        <td>{{ optional($test->activity_date)->format('d-m-Y') }}</td>
                        <td><span class="badge" data-status="{{ $test->status }}">{{ $test->status }}</span></td>
                        <td>{{ $test->talent_test_participants_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <p class="mb-0">Belum ada data tes bakat.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $tests->links() }}</div>
    </div>
@endsection
