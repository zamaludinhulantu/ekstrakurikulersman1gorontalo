@extends('layouts.app')

@section('page_title', 'Data Siswa')
@section('page_subtitle', 'Kelola data siswa peserta ekstrakurikuler')

@section('content')
    @php
        $hasAdvancedFilters = ($category ?? 'all') !== 'all' || filled($gender ?? null) || filled($extracurricularId ?? null);
        $activeFilters = [
            ['label' => 'Cari', 'value' => $search ?: null],
            ['label' => 'Kelas', 'value' => $className ?: null],
            ['label' => 'Ekskul', 'value' => data_get($extracurricularOptions->firstWhere('id', $extracurricularId), 'name')],
            ['label' => 'Kategori', 'value' => ($category ?? 'all') !== 'all' ? data_get(collect($categories)->firstWhere('key', $category), 'label', $category) : null],
            ['label' => 'JK', 'value' => $gender === 'L' ? 'Laki-laki' : ($gender === 'P' ? 'Perempuan' : null)],
            ['label' => 'Status', 'value' => $status === 'active' ? 'Aktif' : ($status === 'inactive' ? 'Tidak aktif' : null)],
        ];
    @endphp

    <x-filter.card
        class="mb-3"
        title="Filter Data Siswa"
        description="Cari siswa dengan filter utama yang ringkas, lalu buka filter lanjutan bila perlu."
    >
        <x-slot:actions>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Tambah Siswa</a>
            <x-filter.export-dropdown
                :items="[
                    ['label' => 'Unduh Excel', 'href' => route('admin.students.export', array_merge(request()->query(), ['format' => 'xls'])), 'icon' => 'bi-file-earmark-excel'],
                    ['label' => 'Unduh PDF', 'href' => route('admin.students.export', array_merge(request()->query(), ['format' => 'pdf'])), 'icon' => 'bi-file-earmark-pdf'],
                ]"
            />
        </x-slot:actions>

        <x-slot:active>
            <x-filter.active-filters :items="$activeFilters" :reset-url="route('admin.students.index')" />
        </x-slot:active>

        <form class="toolbar-grid" method="get">
            <x-filter.field label="Pencarian" for="student_search" col="toolbar-col-4">
                <input id="student_search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama atau NIS">
            </x-filter.field>
            <x-filter.field label="Kelas" for="student_class_name" col="toolbar-col-2">
                <select id="student_class_name" name="class_name" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach($classOptions as $option)
                        <option value="{{ $option }}" @selected($className === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Status" for="student_status" col="toolbar-col-2">
                <select id="student_status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Tidak aktif</option>
                </select>
            </x-filter.field>
            <x-filter.actions col="toolbar-col-4">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan Filter</button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
            </x-filter.actions>
            <div class="toolbar-col-12">
                <div class="filter-advanced-toggle">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#studentAdvancedFilters" aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}" aria-controls="studentAdvancedFilters">
                        <i class="bi bi-sliders"></i>Filter Lanjutan
                    </button>
                </div>
            </div>
            <div id="studentAdvancedFilters" class="toolbar-col-12 filter-advanced collapse {{ $hasAdvancedFilters ? 'show' : '' }}">
                <div class="toolbar-grid">
                    <x-filter.field label="Ekstrakurikuler" for="student_extracurricular_id" col="toolbar-col-4">
                        <select id="student_extracurricular_id" name="extracurricular_id" class="form-select">
                            <option value="">Semua ekskul</option>
                            @foreach($extracurricularOptions as $activity)
                                <option value="{{ $activity->id }}" @selected(($extracurricularId ?? null) === $activity->id)>{{ $activity->name }}</option>
                            @endforeach
                        </select>
                    </x-filter.field>
                    <x-filter.field label="Kategori" for="student_category" col="toolbar-col-4">
                        <select id="student_category" name="category" class="form-select">
                            <option value="all">Semua kategori</option>
                            @foreach($categories as $item)
                                <option value="{{ $item['key'] }}" @selected(($category ?? 'all') === $item['key'])>{{ $item['label'] }}</option>
                            @endforeach
                        </select>
                    </x-filter.field>
                    <x-filter.field label="Jenis kelamin" for="student_gender" col="toolbar-col-4">
                        <select id="student_gender" name="gender" class="form-select">
                            <option value="">Semua</option>
                            <option value="L" @selected($gender === 'L')>Laki-laki</option>
                            <option value="P" @selected($gender === 'P')>Perempuan</option>
                        </select>
                    </x-filter.field>
                </div>
            </div>
        </form>
    </x-filter.card>

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
