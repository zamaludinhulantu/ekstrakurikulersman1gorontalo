@extends('layouts.app')

@section('page_title', 'Kelola Prestasi/Penilaian')
@section('page_subtitle', 'Catat prestasi dan penilaian peserta ekstrakurikuler')

@section('content')
    <div class="card mb-3">
        <div class="card-header">Tambah Data Prestasi/Penilaian</div>
        <div class="card-body">
            <form method="post" action="{{ route('coach.assessments.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select" required>
                        <option value="">Pilih Ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Siswa</label>
                    <select name="student_id" class="form-select" required>
                        <option value="">Pilih Siswa</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->nis }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jenis</label>
                    <select name="assessment_type" class="form-select" required>
                        <option value="achievement">Prestasi</option>
                        <option value="assessment">Penilaian</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Juara Lomba" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nilai</label>
                    <input type="number" step="0.01" min="0" max="100" name="score" class="form-control" placeholder="Opsional">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="assessment_date" class="form-control" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-save"></i>Simpan</button>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Uraian singkat"></textarea>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Filter Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select">
                        <option value="">Semua Ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string)$extracurricularId === (string)$item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button></div>
                <div class="col-md-2"><a href="{{ route('coach.assessments.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Siswa</th>
                    <th>Ekstrakurikuler</th>
                    <th>Jenis</th>
                    <th>Judul</th>
                    <th>Nilai</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($assessments as $row)
                    <tr>
                        <td>{{ $row->student->user->name ?? '-' }}</td>
                        <td>{{ $row->extracurricular->name ?? '-' }}</td>
                        <td class="text-capitalize">{{ $row->assessment_type }}</td>
                        <td>{{ $row->title }}</td>
                        <td>{{ $row->score ?? '-' }}</td>
                        <td>{{ optional($row->assessment_date)->format('d-m-Y') }}</td>
                        <td class="d-flex flex-wrap gap-1">
                            <a href="{{ route('coach.assessments.edit', $row) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                            <form method="post" action="{{ route('coach.assessments.destroy', $row) }}" onsubmit="return confirm('Hapus data ini?')">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-award"></i></div>
                                <p class="mb-0">Belum ada data prestasi atau penilaian.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $assessments->links() }}</div>
    </div>
@endsection
