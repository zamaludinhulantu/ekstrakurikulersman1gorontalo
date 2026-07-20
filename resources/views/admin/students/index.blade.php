@extends('layouts.app')

@section('page_title', 'Data Siswa')
@section('page_subtitle', 'Kelola data siswa peserta ekstrakurikuler')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Tambah Siswa</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama, email, NIS, atau kelas">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">Kelas</label>
                    <select name="class_name" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($classOptions as $option)
                            <option value="{{ $option }}" @selected($className === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Kegiatan yang Diikuti</label>
                    <select name="extracurricular_id" class="form-select">
                        <option value="">Semua Kegiatan</option>
                        @foreach($extracurricularOptions as $activity)
                            <option value="{{ $activity->id }}" @selected(($extracurricularId ?? null) === $activity->id)>{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-select">
                        <option value="">Semua</option>
                        <option value="L" @selected($gender === 'L')>Laki-laki</option>
                        <option value="P" @selected($gender === 'P')>Perempuan</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i>Cari</button></div>
                <div class="col-lg-1 col-md-2"><a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIS</th>
                    <th>Kelas</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $student->user->name }}</td>
                        <td>{{ $student->nis }}</td>
                        <td>{{ $student->class_name }}</td>
                        <td>{{ $student->user->email }}</td>
                        <td><span class="badge" data-status="{{ $student->user->is_active ? 'active' : 'inactive' }}">{{ $student->user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                        <td class="d-flex flex-wrap gap-1">
                            <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i>Detail</a>
                            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                            <form method="post" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Hapus siswa ini?')">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-person-badge"></i></div>
                                <p class="mb-0">Data tidak ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $students->links() }}</div>
    </div>
@endsection
