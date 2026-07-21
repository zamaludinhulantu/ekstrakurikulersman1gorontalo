@extends('layouts.app')

@section('page_title', 'Data Siswa')
@section('page_subtitle', 'Kelola data siswa peserta ekstrakurikuler')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Tambah Siswa</a>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.students.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i>Unduh PDF
            </a>
            <a href="{{ route('admin.students.export', array_merge(request()->query(), ['format' => 'xls'])) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i>Unduh Excel
            </a>
        </div>
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
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="all">Semua kategori</option>
                        @foreach($categories as $item)
                            <option value="{{ $item['key'] }}" @selected(($category ?? 'all') === $item['key'])>{{ $item['label'] }}</option>
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
        <div class="desktop-table table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIS</th>
                    <th>Kelas</th>
                    <th>Email</th>
                    <th>Ekskul Diikuti</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($students as $student)
                    @php
                        $studentActivities = $student->registrations
                            ->when($extracurricularId, fn ($items) => $items->where('extracurricular_id', $extracurricularId))
                            ->map(fn ($registration) => $registration->extracurricular)
                            ->filter()
                            ->unique('id')
                            ->values();
                    @endphp
                    <tr>
                        <td>{{ $students->firstItem() + $loop->index }}</td>
                        <td>{{ $student->user->name }}</td>
                        <td>{{ $student->nis }}</td>
                        <td>{{ $student->class_name }}</td>
                        <td>{{ $student->user->email }}</td>
                        <td>
                            <div class="student-activity-list">
                                @forelse($studentActivities as $activity)
                                    <a href="{{ route('admin.extracurriculars.show', $activity) }}" class="student-activity-link">{{ $activity->name }}</a>
                                @empty
                                    <span class="text-muted small">Belum mengikuti kegiatan</span>
                                @endforelse
                            </div>
                        </td>
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
                        <td colspan="8">
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
        <div class="mobile-stack-table p-3">
            @forelse($students as $student)
                @php
                    $studentActivities = $student->registrations
                        ->when($extracurricularId, fn ($items) => $items->where('extracurricular_id', $extracurricularId))
                        ->map(fn ($registration) => $registration->extracurricular)
                        ->filter()
                        ->unique('id')
                        ->values();
                @endphp
                <div class="mobile-data-card">
                    <div class="mobile-data-card-header">
                        <h3 class="mobile-data-card-title">{{ $student->user->name }}</h3>
                        <span class="badge" data-status="{{ $student->user->is_active ? 'active' : 'inactive' }}">{{ $student->user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                    <div class="mobile-data-list">
                        <div><span class="mobile-data-item-label">NIS</span><p class="mobile-data-item-value">{{ $student->nis }}</p></div>
                        <div><span class="mobile-data-item-label">Kelas</span><p class="mobile-data-item-value">{{ $student->class_name }}</p></div>
                        <div><span class="mobile-data-item-label">Email</span><p class="mobile-data-item-value">{{ $student->user->email }}</p></div>
                        <div>
                            <span class="mobile-data-item-label">Ekskul Diikuti</span>
                            <div class="student-activity-list">
                                @forelse($studentActivities as $activity)
                                    <a href="{{ route('admin.extracurriculars.show', $activity) }}" class="student-activity-link">{{ $activity->name }}</a>
                                @empty
                                    <p class="mobile-data-item-value mb-0">Belum mengikuti kegiatan</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="mobile-data-card-actions">
                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i>Detail</a>
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                        <form method="post" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Hapus siswa ini?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-outline-danger w-100" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-person-badge"></i></div>
                    <p class="mb-0">Data tidak ditemukan.</p>
                </div>
            @endforelse
        </div>
        <div class="card-body">{{ $students->links() }}</div>
    </div>
@endsection
