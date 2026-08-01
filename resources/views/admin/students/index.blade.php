@extends('layouts.app')

@section('page_title', 'Data Siswa')
@section('page_subtitle', 'Daftar seluruh siswa yang terdaftar di sistem')

@section('content')
    @php
        $hasAdvancedFilters = ($category ?? 'all') !== 'all'
            || filled($gender ?? null)
            || filled($extracurricularId ?? null)
            || filled($profileStatus ?? null)
            || (int) $perPage !== 20;
        $activeFilters = [
            ['label' => 'Cari', 'value' => $search ?: null],
            ['label' => 'Kelas', 'value' => $className ?: null],
            ['label' => 'Ekskul', 'value' => data_get($extracurricularOptions->firstWhere('id', $extracurricularId), 'name')],
            ['label' => 'Kategori', 'value' => ($category ?? 'all') !== 'all' ? data_get(collect($categories)->firstWhere('key', $category), 'label', $category) : null],
            ['label' => 'JK', 'value' => $gender === 'L' ? 'Laki-laki' : ($gender === 'P' ? 'Perempuan' : null)],
            ['label' => 'Status', 'value' => $status === 'active' ? 'Aktif' : ($status === 'inactive' ? 'Tidak aktif' : null)],
            ['label' => 'Profil', 'value' => $profileStatus === 'complete' ? 'Lengkap' : ($profileStatus === 'incomplete' ? 'Belum lengkap' : null)],
        ];
    @endphp

    <div class="student-summary-grid mb-3">
        <x-dashboard.stat-card label="Total Siswa" :value="$studentSummary['total']" hint="Seluruh akun siswa" icon="bi-people" :href="route('admin.students.index')" />
        <x-dashboard.stat-card label="Siswa Aktif" :value="$studentSummary['active']" hint="Akun dapat menggunakan sistem" icon="bi-person-check" tone="success" :href="route('admin.students.index', ['status' => 'active'])" />
        <x-dashboard.stat-card label="Siswa Tidak Aktif" :value="$studentSummary['inactive']" hint="Akun dinonaktifkan" icon="bi-person-slash" tone="danger" :href="route('admin.students.index', ['status' => 'inactive'])" />
        <x-dashboard.stat-card label="Profil Belum Lengkap" :value="$studentSummary['incomplete']" hint="Data siswa belum lengkap" icon="bi-person-exclamation" tone="warning" :href="route('admin.students.index', ['profile_status' => 'incomplete'])" />
    </div>

    <x-filter.card
        class="mb-3"
        title="Filter Siswa"
        description="Menampilkan seluruh siswa, termasuk yang belum mengikuti kegiatan."
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
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <x-filter.field label="Pencarian" for="student_search" col="toolbar-col-3">
                <input id="student_search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama, NIS, email, atau kegiatan">
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
            <x-filter.actions col="toolbar-col-5">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#studentAdvancedFilters" aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}" aria-controls="studentAdvancedFilters">
                    <i class="bi bi-sliders"></i>Filter Lanjutan
                </button>
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan</button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
            </x-filter.actions>

            <div id="studentAdvancedFilters" class="toolbar-col-12 filter-advanced collapse {{ $hasAdvancedFilters ? 'show' : '' }}">
                <div class="toolbar-grid">
                    <x-filter.field label="Ekstrakurikuler" for="student_extracurricular_id" col="toolbar-col-3">
                        <select id="student_extracurricular_id" name="extracurricular_id" class="form-select">
                            <option value="">Semua ekskul</option>
                            @foreach($extracurricularOptions as $activity)
                                <option value="{{ $activity->id }}" @selected((string) ($extracurricularId ?? '') === (string) $activity->id)>{{ $activity->name }}</option>
                            @endforeach
                        </select>
                    </x-filter.field>
                    <x-filter.field label="Kategori" for="student_category" col="toolbar-col-3">
                        <select id="student_category" name="category" class="form-select">
                            <option value="all">Semua kategori</option>
                            @foreach($categories as $item)
                                <option value="{{ $item['key'] }}" @selected(($category ?? 'all') === $item['key'])>{{ $item['label'] }}</option>
                            @endforeach
                        </select>
                    </x-filter.field>
                    <x-filter.field label="Jenis kelamin" for="student_gender" col="toolbar-col-2">
                        <select id="student_gender" name="gender" class="form-select">
                            <option value="">Semua</option>
                            <option value="L" @selected($gender === 'L')>Laki-laki</option>
                            <option value="P" @selected($gender === 'P')>Perempuan</option>
                        </select>
                    </x-filter.field>
                    <x-filter.field label="Kelengkapan profil" for="student_profile_status" col="toolbar-col-2">
                        <select id="student_profile_status" name="profile_status" class="form-select">
                            <option value="">Semua profil</option>
                            <option value="complete" @selected($profileStatus === 'complete')>Lengkap</option>
                            <option value="incomplete" @selected($profileStatus === 'incomplete')>Belum lengkap</option>
                        </select>
                    </x-filter.field>
                    <x-filter.field label="Per halaman" for="student_filter_per_page" col="toolbar-col-2">
                        <select id="student_filter_per_page" name="per_page" class="form-select">
                            @foreach([10, 20, 50, 100] as $option)
                                <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </x-filter.field>
                </div>
            </div>
        </form>
    </x-filter.card>

    <div class="card student-directory-card student-member-directory-card directory-table-card">
        <div class="card-header dashboard-panel-header">
            <div class="directory-table-card__heading">
                <span class="directory-table-card__icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                <div>
                    <strong>Daftar Seluruh Siswa</strong>
                    <small>Identitas, kegiatan, status akun, dan tindakan siswa</small>
                </div>
            </div>
            <span class="directory-table-card__count">{{ number_format($students->total()) }} siswa</span>
        </div>
        <div class="desktop-table table-responsive">
            <table class="table student-directory-table directory-data-table mb-0">
                <colgroup>
                    <col class="directory-col-student">
                    <col class="directory-col-nis">
                    <col class="directory-col-class">
                    <col class="directory-col-contact">
                    <col class="directory-col-activity">
                    <col class="directory-col-status">
                    <col class="directory-col-actions">
                </colgroup>
                <thead>
                <tr>
                    <th class="student-directory-table__name"><x-student.sort-link column="name" label="Siswa" :current-sort="$sort" :direction="$direction" /></th>
                    <th><x-student.sort-link column="nis" label="NIS" :current-sort="$sort" :direction="$direction" /></th>
                    <th><x-student.sort-link column="class_name" label="Kelas" :current-sort="$sort" :direction="$direction" /></th>
                    <th class="student-directory-table__contact">Kontak</th>
                    <th class="student-directory-table__activities">Kegiatan</th>
                    <th class="directory-status-heading"><x-student.sort-link column="status" label="Status" :current-sort="$sort" :direction="$direction" /></th>
                    <th class="text-center table-action-col table-action-col--compact">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($students as $student)
                    @php
                        $studentActivities = $student->registrations
                            ->map(fn ($registration) => $registration->extracurricular)
                            ->filter()
                            ->unique('id')
                            ->values();
                    @endphp
                    <tr>
                        <td>
                            <x-student.identity
                                :student="$student"
                                :href="route('admin.students.show', $student)"
                                :subtitle="$student->hasCompleteProfile() ? null : 'Profil belum lengkap'"
                            />
                        </td>
                        <td>
                            @if($student->nis)
                                <span class="student-nis">{{ $student->nis }}</span>
                            @else
                                <span class="student-missing-value">NIS belum diisi</span>
                            @endif
                        </td>
                        <td>{{ $student->class_name ?: 'Belum diisi' }}</td>
                        <td>
                            <div class="student-contact">
                                <span title="{{ $student->user?->email }}">{{ $student->user?->email ?: 'Email belum diisi' }}</span>
                                @if($student->user?->phone)<small title="{{ $student->user->phone }}">{{ $student->user->phone }}</small>@endif
                            </div>
                        </td>
                        <td><x-student.activity-badges :activities="$studentActivities" route-name="admin.extracurriculars.show" /></td>
                        <td class="directory-status-cell"><span class="badge" data-status="{{ $student->user?->is_active ? 'active' : 'inactive' }}">{{ $student->user?->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                        <td class="directory-actions-cell table-action-col table-action-col--compact"><x-student.admin-actions :student="$student" class="justify-content-center" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-person-badge"></i></div>
                                <p class="mb-2">Tidak ada siswa yang sesuai dengan pencarian atau filter.</p>
                                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-primary btn-sm">Reset Filter</a>
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
                        ->map(fn ($registration) => $registration->extracurricular)
                        ->filter()
                        ->unique('id')
                        ->values();
                @endphp
                <article class="mobile-data-card student-mobile-card">
                    <div class="mobile-data-card-header">
                        <x-student.identity :student="$student" :subtitle="$student->class_name ?: 'Kelas belum diisi'" />
                        <span class="badge" data-status="{{ $student->user?->is_active ? 'active' : 'inactive' }}">{{ $student->user?->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                    <div class="mobile-data-list">
                        <div><span class="mobile-data-item-label">NIS</span><p class="mobile-data-item-value {{ $student->nis ? '' : 'student-missing-value' }}">{{ $student->nis ?: 'NIS belum diisi' }}</p></div>
                        <div><span class="mobile-data-item-label">Kontak</span><div class="student-contact"><span>{{ $student->user?->email ?: 'Email belum diisi' }}</span>@if($student->user?->phone)<small>{{ $student->user->phone }}</small>@endif</div></div>
                        <div><span class="mobile-data-item-label">Kegiatan</span><x-student.activity-badges :activities="$studentActivities" :limit="1" /></div>
                    </div>
                    <x-student.admin-actions :student="$student" class="mt-3" />
                </article>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-person-badge"></i></div>
                    <p class="mb-2">Tidak ada siswa yang sesuai dengan pencarian atau filter.</p>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-primary btn-sm">Reset Filter</a>
                </div>
            @endforelse
        </div>

        <div class="card-body student-directory-card__footer">
            <x-student.pagination :paginator="$students" noun="siswa" :per-page="$perPage" />
        </div>
    </div>
@endsection
