@extends('layouts.app')

@section('page_title', 'Pendaftar Ekstrakurikuler')
@section('page_subtitle', 'Periksa pendaftaran siswa dan berikan keputusan dengan tampilan yang lebih mudah dipantau')

@section('content')
    @php
        $hasAdvancedFilters = ($category ?? 'all') !== 'all' || filled($extracurricularId ?? null) || filled($gender ?? null);
        $activeFilters = [
            ['label' => 'Cari', 'value' => $search ?: null],
            ['label' => 'Kelas', 'value' => $className ?: null],
            ['label' => 'Status', 'value' => $status ? ($statusMap[$status] ?? null) : null],
            ['label' => 'Ekskul', 'value' => data_get($extracurriculars->firstWhere('id', $extracurricularId), 'name')],
            ['label' => 'Kategori', 'value' => ($category ?? 'all') !== 'all' ? data_get(collect($categories)->firstWhere('key', $category), 'label', $category) : null],
            ['label' => 'JK', 'value' => ($gender ?? '') === 'L' ? 'Laki-laki' : (($gender ?? '') === 'P' ? 'Perempuan' : null)],
        ];
    @endphp

    <x-filter.card
        class="mb-3"
        title="Filter Data Pendaftar"
        description="Gunakan filter utama untuk verifikasi cepat, lalu buka filter lanjutan untuk penyaringan lebih spesifik."
    >
        <x-slot:actions>
            <x-filter.export-dropdown
                :items="[
                    ['label' => 'Unduh Excel', 'href' => route('admin.registrations.export', array_merge(request()->query(), ['format' => 'xls'])), 'icon' => 'bi-file-earmark-excel'],
                    ['label' => 'Unduh PDF', 'href' => route('admin.registrations.export', array_merge(request()->query(), ['format' => 'pdf'])), 'icon' => 'bi-file-earmark-pdf'],
                ]"
            />
        </x-slot:actions>
        <x-slot:active>
            <x-filter.active-filters :items="$activeFilters" :reset-url="route('admin.registrations.index')" />
        </x-slot:active>
        <form class="toolbar-grid" method="get">
            <x-filter.field label="Cari siswa" for="registration_search" col="toolbar-col-4">
                <input type="text" id="registration_search" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama atau NIS">
            </x-filter.field>
            <x-filter.field label="Kelas" for="registration_class_name" col="toolbar-col-2">
                <select id="registration_class_name" name="class_name" class="form-select">
                    <option value="">Semua kelas</option>
                    @foreach($classOptions as $option)
                        <option value="{{ $option }}" @selected($className === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Status" for="registration_status" col="toolbar-col-3">
                <select id="registration_status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="pending" @selected($status === 'pending')>Menunggu</option>
                    <option value="waiting_test" @selected($status === 'waiting_test')>Menunggu Tes</option>
                    <option value="scheduled_test" @selected($status === 'scheduled_test')>Tes Dijadwalkan</option>
                    <option value="approved" @selected($status === 'approved')>Diterima</option>
                    <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
                </select>
            </x-filter.field>
            <x-filter.actions col="toolbar-col-3">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan Filter</button>
                <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
            </x-filter.actions>
            <div class="toolbar-col-12">
                <div class="filter-advanced-toggle">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#adminRegistrationAdvancedFilters" aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}" aria-controls="adminRegistrationAdvancedFilters">
                        <i class="bi bi-sliders"></i>Filter Lanjutan
                    </button>
                </div>
            </div>
            <div id="adminRegistrationAdvancedFilters" class="toolbar-col-12 filter-advanced collapse {{ $hasAdvancedFilters ? 'show' : '' }}">
                <div class="toolbar-grid">
                    <x-filter.field label="Kategori" for="registration_category" col="toolbar-col-4">
                        <select id="registration_category" name="category" class="form-select">
                            <option value="all">Semua kategori</option>
                            @foreach($categories as $item)
                                <option value="{{ $item['key'] }}" @selected(($category ?? 'all') === $item['key'])>{{ $item['label'] }}</option>
                            @endforeach
                        </select>
                    </x-filter.field>
                    <x-filter.field label="Ekstrakurikuler" for="registration_extracurricular_id" col="toolbar-col-4">
                        <select id="registration_extracurricular_id" name="extracurricular_id" class="form-select">
                            <option value="">Semua ekskul</option>
                            @foreach($extracurriculars as $item)
                                <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </x-filter.field>
                    <x-filter.field label="Jenis kelamin" for="registration_gender" col="toolbar-col-4">
                        <select id="registration_gender" name="gender" class="form-select">
                            <option value="">Semua</option>
                            <option value="L" @selected(($gender ?? '') === 'L')>Laki-laki</option>
                            <option value="P" @selected(($gender ?? '') === 'P')>Perempuan</option>
                        </select>
                    </x-filter.field>
                </div>
            </div>
        </form>
    </x-filter.card>

    <div class="card">
        <div class="card-header">Daftar Pendaftar</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped table-compact mb-0">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Siswa</th>
                        <th>Kegiatan yang Diikuti</th>
                        <th>Kelas</th>
                        <th>Tanggal Daftar</th>
                        <th>Status</th>
                        <th>Ringkasan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse(($groupedRegistrations ?? collect()) as $group)
                        @php
                            $student = $group['student'];
                            $studentRegistrations = $group['registrations'];
                            $latestRegistration = $group['latest_registration'];
                            $rowNumber = $registrations->firstItem() + $loop->index;
                        @endphp
                        <tr>
                            <td>{{ $rowNumber }}</td>
                            <td>
                                <div class="table-person">
                                    <strong>{{ $student->user->name ?? '-' }}</strong>
                                    <small>NIS: {{ $student->nis ?? '-' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="student-activity-list">
                                    @foreach($studentRegistrations as $registration)
                                        <a href="{{ route('admin.registrations.show', $registration) }}" class="student-activity-link">
                                            {{ $registration->extracurricular->name ?? '-' }}
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $student->class_name ?? '-' }}</td>
                            <td>{{ optional($latestRegistration?->registration_date)->format('d-m-Y') ?: '-' }}</td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @foreach($studentRegistrations as $registration)
                                        @php
                                            $displayStatus = $registration->displayStatus();
                                            $displayStatusLabel = $statusMap[$displayStatus] ?? ucfirst($displayStatus);
                                        @endphp
                                        <span class="badge" data-status="{{ $displayStatusLabel }}">{{ $displayStatusLabel }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="table-summary-text">
                                    {{ $studentRegistrations->count() }} kegiatan dipilih.
                                    @if($latestRegistration?->extracurricular)
                                        Terakhir: {{ $latestRegistration->extracurricular->name }}.
                                    @endif
                                </div>
                                @if($student->hasLegacyRegistrationOverflow())
                                    <div class="small text-warning fw-semibold mt-1">
                                        Data lama siswa ini melebihi batas 3 ekstrakurikuler. Pendaftaran baru harus ditahan.
                                    </div>
                                @endif
                            </td>
                            <td class="text-end table-action-col">
                                <div class="d-flex flex-column gap-2 align-items-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $latestRegistration) }}">
                                        <i class="bi bi-person-badge"></i>
                                        <span class="d-none d-md-inline">Profil</span>
                                    </button>
                                    @foreach($studentRegistrations as $registration)
                                        @php
                                            $displayStatus = $registration->displayStatus();
                                        @endphp
                                        <div class="d-flex flex-wrap justify-content-end gap-1">
                                            <a href="{{ route('admin.registrations.show', $registration) }}" class="btn btn-sm btn-outline-primary action-button-compact">
                                                <i class="bi bi-eye"></i>
                                                <span class="d-none d-md-inline">{{ $registration->extracurricular->catalog_item_name ?? 'Detail' }}</span>
                                            </a>
                                            @if(in_array($displayStatus, ['pending', 'approved', 'rejected'], true))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-secondary registration-verify-trigger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#registrationVerificationModal"
                                                    data-action="{{ route('admin.registrations.update-status', $registration) }}"
                                                    data-student="{{ $registration->student->user->name ?? '-' }}"
                                                    data-nis="{{ $registration->student->nis ?? '-' }}"
                                                    data-class-name="{{ $registration->student->class_name ?? '-' }}"
                                                    data-extracurricular-id="{{ $registration->extracurricular_id }}"
                                                    data-extracurricular="{{ $registration->extracurricular->name ?? '-' }}"
                                                    data-primary-talent="{{ $registration->primary_talent ?: '-' }}"
                                                    data-prior-experience="{{ $registration->prior_experience ?: '-' }}"
                                                    data-current-skills="{{ $registration->current_skills ?: '-' }}"
                                                    data-achievement-history="{{ $registration->achievement_history ?: '-' }}"
                                                    data-notes="{{ $registration->notes ?? '' }}"
                                                    data-current-schedule-id="{{ optional($registration->talentTestParticipants->sortByDesc('id')->first())->schedule_id ?? '' }}"
                                                    data-default-decision="{{ $registration->willing_to_take_test ? 'schedule_test' : 'approve' }}"
                                                    data-modal-title="{{ $displayStatus === 'pending' ? 'Verifikasi Pendaftar' : ($displayStatus === 'rejected' ? 'Tinjau Kembali Pendaftaran' : 'Ubah Keputusan Pendaftaran') }}"
                                                >
                                                    <i class="bi bi-check2-square"></i>
                                                    <span class="d-none d-md-inline">Verifikasi</span>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                                    <p class="mb-0">Data belum tersedia.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mobile-stack-table p-3">
                @forelse(($groupedRegistrations ?? collect()) as $group)
                    @php
                        $student = $group['student'];
                        $studentRegistrations = $group['registrations'];
                        $latestRegistration = $group['latest_registration'];
                    @endphp
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <div>
                                <h3 class="mobile-data-card-title">{{ $student->user->name ?? '-' }}</h3>
                                <div class="small text-muted">NIS: {{ $student->nis ?? '-' }} | {{ $student->class_name ?? '-' }}</div>
                            </div>
                            <span class="badge badge-status-secondary">{{ $studentRegistrations->count() }} kegiatan</span>
                        </div>
                        <div class="mobile-data-list mb-3">
                            <div>
                                <span class="mobile-data-item-label">Kegiatan yang Diikuti</span>
                                <div class="student-activity-list">
                                    @foreach($studentRegistrations as $registration)
                                        <a href="{{ route('admin.registrations.show', $registration) }}" class="student-activity-link">
                                            {{ $registration->extracurricular->name ?? '-' }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            <div><span class="mobile-data-item-label">Tanggal daftar terakhir</span><p class="mobile-data-item-value">{{ optional($latestRegistration?->registration_date)->format('d-m-Y') ?: '-' }}</p></div>
                            <div>
                                <span class="mobile-data-item-label">Status Pendaftaran</span>
                                <div class="d-flex flex-column gap-1">
                                    @foreach($studentRegistrations as $registration)
                                        @php
                                            $displayStatus = $registration->displayStatus();
                                            $displayStatusLabel = $statusMap[$displayStatus] ?? ucfirst($displayStatus);
                                        @endphp
                                        <span class="badge align-self-start" data-status="{{ $displayStatusLabel }}">{{ ($registration->extracurricular->name ?? '-') . ': ' . $displayStatusLabel }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @if($student->hasLegacyRegistrationOverflow())
                                <div>
                                    <span class="mobile-data-item-label">Peringatan</span>
                                    <p class="mobile-data-item-value text-warning fw-semibold">Data lama siswa ini melebihi batas 3 ekstrakurikuler. Pendaftaran baru harus ditahan.</p>
                                </div>
                            @endif
                        </div>
                        <div class="mobile-data-card-actions">
                            <button type="button" class="btn btn-outline-secondary profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $latestRegistration) }}">
                                <i class="bi bi-person-badge"></i>Profil
                            </button>
                            @foreach($studentRegistrations as $registration)
                                @php
                                    $displayStatus = $registration->displayStatus();
                                @endphp
                                <a href="{{ route('admin.registrations.show', $registration) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>{{ $registration->extracurricular->catalog_item_name ?? 'Detail' }}
                                </a>
                                @if(in_array($displayStatus, ['pending', 'approved', 'rejected'], true))
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary registration-verify-trigger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#registrationVerificationModal"
                                        data-action="{{ route('admin.registrations.update-status', $registration) }}"
                                        data-student="{{ $registration->student->user->name ?? '-' }}"
                                        data-nis="{{ $registration->student->nis ?? '-' }}"
                                        data-class-name="{{ $registration->student->class_name ?? '-' }}"
                                        data-extracurricular-id="{{ $registration->extracurricular_id }}"
                                        data-extracurricular="{{ $registration->extracurricular->name ?? '-' }}"
                                        data-primary-talent="{{ $registration->primary_talent ?: '-' }}"
                                        data-prior-experience="{{ $registration->prior_experience ?: '-' }}"
                                        data-current-skills="{{ $registration->current_skills ?: '-' }}"
                                        data-achievement-history="{{ $registration->achievement_history ?: '-' }}"
                                        data-notes="{{ $registration->notes ?? '' }}"
                                        data-current-schedule-id="{{ optional($registration->talentTestParticipants->sortByDesc('id')->first())->schedule_id ?? '' }}"
                                        data-default-decision="{{ $registration->willing_to_take_test ? 'schedule_test' : 'approve' }}"
                                        data-modal-title="{{ $displayStatus === 'pending' ? 'Verifikasi Pendaftar' : ($displayStatus === 'rejected' ? 'Tinjau Kembali Pendaftaran' : 'Ubah Keputusan Pendaftaran') }}"
                                    >
                                        <i class="bi bi-check2-square"></i>Verifikasi {{ $registration->extracurricular->catalog_item_name ?? 'Pendaftaran' }}
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                        <p class="mb-0">Data belum tersedia.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $registrations->links() }}</div>
    </div>

    <div class="modal fade" id="registrationVerificationModal" tabindex="-1" aria-labelledby="registrationVerificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content verification-modal">
                <form method="post" action="#" id="registrationVerificationForm" data-schedule-options='@json($talentTestScheduleOptions ?? [])'>
                    @csrf
                    @method('patch')
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h2 class="modal-title h4 mb-1" id="registrationVerificationModalLabel">Verifikasi Pendaftar</h2>
                            <p class="text-muted mb-0">Tinjau profil singkat siswa sebelum menyimpan keputusan verifikasi.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <div class="modal-body">
                        <div class="verification-modal__summary">
                            <div class="data-point">
                                <div class="data-point-label">Siswa</div>
                                <p class="data-point-value mb-0" id="registrationVerificationStudent">-</p>
                                <div class="helper-text mb-0" id="registrationVerificationMeta">-</div>
                            </div>
                            <div class="data-point">
                                <div class="data-point-label">Ekstrakurikuler</div>
                                <p class="data-point-value mb-0" id="registrationVerificationExtracurricular">-</p>
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="info-item h-100">
                                    <div class="title">Minat dan kemampuan awal</div>
                                    <div class="small mt-1"><strong>Bakat utama:</strong> <span id="registrationVerificationPrimaryTalent">-</span></div>
                                    <div class="small mt-1"><strong>Kemampuan awal:</strong> <span id="registrationVerificationCurrentSkills">-</span></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item h-100">
                                    <div class="title">Pengalaman dan prestasi</div>
                                    <div class="small mt-2"><strong>Pengalaman:</strong> <span id="registrationVerificationExperience">-</span></div>
                                    <div class="small mt-1"><strong>Prestasi:</strong> <span id="registrationVerificationAchievements">-</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section-card mt-3">
                            <h3 class="form-section-title">Keputusan Verifikasi</h3>
                            <p class="form-section-copy">Pilih keputusan yang paling sesuai. Saat memilih jadwalkan tes, isi jadwal tes langsung dari form ini dan status pendaftaran tidak langsung menjadi diterima.</p>
                            <div class="verification-decision-group">
                                <label class="verification-decision-option">
                                    <input type="radio" name="decision" value="approve" checked>
                                    <span>
                                        <strong>Terima</strong>
                                        <small>Siswa langsung diterima ke ekstrakurikuler.</small>
                                    </span>
                                </label>
                                <label class="verification-decision-option">
                                    <input type="radio" name="decision" value="schedule_test">
                                    <span>
                                        <strong>Jadwalkan Tes</strong>
                                        <small>Simpan sebagai proses tes dan buat jadwal tes bakat untuk siswa ini.</small>
                                    </span>
                                </label>
                                <label class="verification-decision-option">
                                    <input type="radio" name="decision" value="reject">
                                    <span>
                                        <strong>Tolak</strong>
                                        <small>Pendaftaran ditolak dengan catatan verifikasi.</small>
                                    </span>
                                </label>
                            </div>

                            <div class="mt-3">
                                <label class="form-label" for="registrationVerificationNotes">Catatan verifikasi</label>
                                <textarea name="notes" id="registrationVerificationNotes" class="form-control" rows="4" placeholder="Tulis alasan keputusan, arahan tes, atau catatan tindak lanjut"></textarea>
                            </div>

                            <div class="mt-3 d-none" id="registrationVerificationScheduleFields">
                                <div class="alert alert-danger d-none" id="registrationVerificationScheduleError" role="alert"></div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label" for="registrationVerificationExistingSchedule">Gunakan jadwal yang sudah ada</label>
                                        <select name="existing_schedule_id" id="registrationVerificationExistingSchedule" class="form-select">
                                            <option value="">Buat jadwal baru</option>
                                        </select>
                                        <div class="helper-text mt-1" id="registrationVerificationExistingScheduleHelp">Pilih jadwal tes yang sudah dibuat untuk ekskul ini jika ingin menambahkan siswa ke jadwal yang sama.</div>
                                    </div>
                                </div>
                                <div class="row g-3 mt-0" id="registrationVerificationScheduleManualFields">
                                    <div class="col-md-12">
                                        <label class="form-label" for="registrationVerificationScheduleTitle">Judul tes</label>
                                        <input type="text" name="schedule_title" id="registrationVerificationScheduleTitle" class="form-control" placeholder="Contoh: Tes Bakat Gelombang 1">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="registrationVerificationScheduleDate">Tanggal tes</label>
                                        <input type="date" name="schedule_date" id="registrationVerificationScheduleDate" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="registrationVerificationScheduleStartTime">Jam mulai</label>
                                        <input type="time" name="schedule_start_time" id="registrationVerificationScheduleStartTime" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="registrationVerificationScheduleEndTime">Jam selesai</label>
                                        <input type="time" name="schedule_end_time" id="registrationVerificationScheduleEndTime" class="form-control">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label" for="registrationVerificationScheduleLocation">Lokasi</label>
                                        <input type="text" name="schedule_location" id="registrationVerificationScheduleLocation" class="form-control" placeholder="Aula, lapangan, ruang musik, dll.">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label" for="registrationVerificationScheduleDescription">Keterangan jadwal</label>
                                        <textarea name="schedule_description" id="registrationVerificationScheduleDescription" class="form-control" rows="3" placeholder="Instruksi tes, perlengkapan, atau keterangan tambahan"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan keputusan..."><i class="bi bi-save"></i>Simpan Keputusan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="registrationNoteModal" tabindex="-1" aria-labelledby="registrationNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content verification-modal">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="modal-title h5 mb-1" id="registrationNoteModalLabel">Catatan Verifikasi</h2>
                        <p class="text-muted mb-0" id="registrationNoteModalMeta">-</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="info-item">
                        <div class="title">Catatan</div>
                        <div class="small mt-2" id="registrationNoteModalBody">Belum ada catatan verifikasi.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
