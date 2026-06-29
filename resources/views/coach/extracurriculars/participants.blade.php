@extends('layouts.app')

@section('page_title', 'Daftar Peserta')
@section('page_subtitle', 'Ekstrakurikuler: ' . $extracurricular->name)

@section('content')
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">{{ $extracurricular->name }}</h5>
                <p class="mb-0 text-muted small">Daftar peserta dengan status pendaftaran diterima.</p>
            </div>
            <a href="{{ route('coach.extracurriculars.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i>Kembali</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>NIS</th>
                    <th>Kelas</th>
                    <th>Tanggal Gabung</th>
                </tr>
                </thead>
                <tbody>
                @forelse($participants as $row)
                    <tr>
                        <td>{{ $row->student->user->name ?? '-' }}</td>
                        <td>{{ $row->student->nis ?? '-' }}</td>
                        <td>{{ $row->student->class_name ?? '-' }}</td>
                        <td>{{ optional($row->registration_date)->format('d-m-Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-people"></i></div>
                                <p class="mb-0">Belum ada peserta aktif.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $participants->links() }}</div>
    </div>
@endsection
