@extends('layouts.app')

@section('page_title', 'Daftar Peserta')
@section('page_subtitle', 'Peserta aktif pada ' . $extracurricular->name)

@section('content')
    @php
        $activeFilters = [
            ['label' => 'Cari', 'value' => $search ?: null],
            ['label' => 'Kelas', 'value' => $className ?: null],
            ['label' => 'Status', 'value' => $status === 'active' ? 'Aktif' : ($status === 'inactive' ? 'Tidak aktif' : null)],
        ];
    @endphp

    <div class="student-summary-grid mb-3">
        <x-dashboard.stat-card label="Total Peserta" :value="$participantSummary['total']" hint="Siswa unik berstatus diterima" icon="bi-people" :href="route('coach.extracurriculars.participants', $extracurricular)" />
        <x-dashboard.stat-card label="Akun Aktif" :value="$participantSummary['active']" hint="Peserta dapat menggunakan sistem" icon="bi-person-check" tone="success" :href="route('coach.extracurriculars.participants', [$extracurricular, 'status' => 'active'])" />
        <x-dashboard.stat-card label="Akun Tidak Aktif" :value="$participantSummary['inactive']" hint="Akun peserta dinonaktifkan Admin" icon="bi-person-slash" tone="danger" :href="route('coach.extracurriculars.participants', [$extracurricular, 'status' => 'inactive'])" />
        <x-dashboard.stat-card label="Profil Belum Lengkap" :value="$participantSummary['incomplete']" hint="NIS, kelas, atau gender belum lengkap" icon="bi-person-exclamation" tone="warning" />
    </div>

    <x-filter.card
        class="mb-3"
        title="{{ $extracurricular->name }}"
        description="Cari peserta yang sudah diterima. Pembina tidak dapat mengubah data inti atau menghapus akun siswa."
    >
        <x-slot:actions>
            <a href="{{ route('coach.extracurriculars.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
        </x-slot:actions>
        <x-slot:active>
            <x-filter.active-filters :items="$activeFilters" :reset-url="route('coach.extracurriculars.participants', $extracurricular)" />
        </x-slot:active>

        <form class="toolbar-grid" method="get">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <x-filter.field label="Pencarian" for="participant_search" col="toolbar-col-4">
                <input id="participant_search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama, NIS, atau email">
            </x-filter.field>
            <x-filter.field label="Kelas" for="participant_class" col="toolbar-col-2">
                <select id="participant_class" name="class_name" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach($classOptions as $option)
                        <option value="{{ $option }}" @selected($className === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Status akun" for="participant_status" col="toolbar-col-2">
                <select id="participant_status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="active" @selected($status === 'active')>Aktif</option>
                    <option value="inactive" @selected($status === 'inactive')>Tidak aktif</option>
                </select>
            </x-filter.field>
            <x-filter.field label="Per halaman" for="participant_per_page" col="toolbar-col-2">
                <select id="participant_per_page" name="per_page" class="form-select">
                    @foreach([10, 20, 50, 100] as $option)
                        <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.actions col="toolbar-col-2">
                <button class="btn btn-primary" type="submit" aria-label="Terapkan filter"><i class="bi bi-funnel"></i>Terapkan</button>
                <a href="{{ route('coach.extracurriculars.participants', $extracurricular) }}" class="btn btn-outline-secondary" aria-label="Reset filter"><i class="bi bi-arrow-repeat"></i></a>
            </x-filter.actions>
        </form>
    </x-filter.card>

    <div class="card student-directory-card">
        <div class="card-header dashboard-panel-header">
            <div><strong>Peserta Aktif</strong><small>{{ $participants->total() }} peserta sesuai pencarian dan filter</small></div>
        </div>
        <div class="desktop-table table-responsive">
            <table class="table student-directory-table mb-0">
                <thead>
                    <tr>
                        <th class="student-directory-table__name"><x-student.sort-link column="name" label="Siswa" :current-sort="$sort" :direction="$direction" /></th>
                        <th><x-student.sort-link column="nis" label="NIS" :current-sort="$sort" :direction="$direction" /></th>
                        <th><x-student.sort-link column="class_name" label="Kelas" :current-sort="$sort" :direction="$direction" /></th>
                        <th>Kelompok</th>
                        <th><x-student.sort-link column="registration_date" label="Tanggal Gabung" :current-sort="$sort" :direction="$direction" /></th>
                        <th>Status</th>
                        <th class="text-center table-action-col table-action-col--compact">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $row)
                        @php $latestResult = $row->latestPublishedTalentTestResult(); @endphp
                        <tr>
                            <td>
                                <button type="button" class="student-profile-button profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $row) }}">
                                    <x-student.identity :student="$row->student" :subtitle="$row->student->hasCompleteProfile() ? null : 'Profil belum lengkap'" />
                                </button>
                            </td>
                            <td>
                                @if($row->student->nis)
                                    <span class="student-nis">{{ $row->student->nis }}</span>
                                @else
                                    <span class="student-missing-value">NIS belum diisi</span>
                                @endif
                            </td>
                            <td>{{ $row->student->class_name ?: 'Kelas belum diisi' }}</td>
                            <td>{{ $latestResult?->training_group ?: 'Belum ditentukan' }}</td>
                            <td>{{ optional($row->registration_date)->format('d-m-Y') }}</td>
                            <td><span class="badge" data-status="{{ $row->student->user?->is_active ? 'active' : 'inactive' }}">{{ $row->student->user?->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                            <td class="text-center table-action-col table-action-col--compact">
                                <div class="student-row-actions justify-content-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light student-row-actions__menu" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" aria-label="Buka aksi peserta {{ $row->student->user?->name }}">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <button type="button" class="dropdown-item profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $row) }}"><i class="bi bi-eye"></i>Detail</button>
                                            <div class="dropdown-divider"></div>
                                            <form method="post" action="{{ route('coach.extracurriculars.participants.destroy', [$extracurricular, $row]) }}" onsubmit="return confirm('Keluarkan siswa dari {{ $extracurricular->name }}? Status keikutsertaan akan dibatalkan, tetapi akun dan riwayat siswa tetap tersimpan.')">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-person-x"></i>Keluarkan dari kegiatan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-people"></i></div>
                                    <p class="mb-2">Tidak ada peserta yang sesuai dengan pencarian atau filter.</p>
                                    <a href="{{ route('coach.extracurriculars.participants', $extracurricular) }}" class="btn btn-outline-primary btn-sm">Reset Filter</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mobile-stack-table p-3">
            @forelse($participants as $row)
                @php $latestResult = $row->latestPublishedTalentTestResult(); @endphp
                <article class="mobile-data-card student-mobile-card">
                    <div class="mobile-data-card-header">
                        <x-student.identity :student="$row->student" :subtitle="$row->student->class_name ?: 'Kelas belum diisi'" />
                        <span class="badge" data-status="{{ $row->student->user?->is_active ? 'active' : 'inactive' }}">{{ $row->student->user?->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                    <div class="mobile-data-list">
                        <div><span class="mobile-data-item-label">NIS</span><p class="mobile-data-item-value {{ $row->student->nis ? '' : 'student-missing-value' }}">{{ $row->student->nis ?: 'NIS belum diisi' }}</p></div>
                        <div><span class="mobile-data-item-label">Kelompok</span><p class="mobile-data-item-value">{{ $latestResult?->training_group ?: 'Belum ditentukan' }}</p></div>
                        <div><span class="mobile-data-item-label">Tanggal gabung</span><p class="mobile-data-item-value">{{ optional($row->registration_date)->format('d-m-Y') }}</p></div>
                    </div>
                    <div class="student-row-actions mt-3">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light student-row-actions__menu" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka aksi peserta"><i class="bi bi-three-dots-vertical"></i></button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <button type="button" class="dropdown-item profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $row) }}"><i class="bi bi-eye"></i>Detail</button>
                                <div class="dropdown-divider"></div>
                                <form method="post" action="{{ route('coach.extracurriculars.participants.destroy', [$extracurricular, $row]) }}" onsubmit="return confirm('Keluarkan siswa dari {{ $extracurricular->name }}? Akun dan riwayat siswa tetap tersimpan.')">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-person-x"></i>Keluarkan dari kegiatan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-people"></i></div>
                    <p class="mb-2">Tidak ada peserta yang sesuai dengan pencarian atau filter.</p>
                    <a href="{{ route('coach.extracurriculars.participants', $extracurricular) }}" class="btn btn-outline-primary btn-sm">Reset Filter</a>
                </div>
            @endforelse
        </div>
        <div class="card-body student-directory-card__footer">
            <x-student.pagination :paginator="$participants" noun="peserta" :per-page="$perPage" />
        </div>
    </div>
@endsection
