@php
    $isAdminRegistrationList = $registrationRole === 'admin';
    $indexRoute = route($registrationRole . '.registrations.index');
    $showRouteName = $registrationRole . '.registrations.show';
    $updateRouteName = $registrationRole . '.registrations.update-status';
    $reviewCancellationRouteName = $registrationRole . '.registrations.review-cancellation';
    $filterPrefix = $registrationRole . '_registration';
    $hasAdvancedFilters = ($category ?? 'all') !== 'all'
        || filled($extracurricularId ?? null)
        || filled($gender ?? null)
        || filled($dateFrom ?? null)
        || filled($dateTo ?? null);
    $hasFilters = filled($search ?? null)
        || filled($className ?? null)
        || filled($status ?? null)
        || $hasAdvancedFilters;
    $verifiableDisplayStatuses = ['pending', 'waiting_test', 'scheduled_test', 'approved', 'rejected'];
    $activeFilters = [
        ['label' => 'Cari', 'value' => $search ?: null],
        ['label' => 'Kelas', 'value' => $className ?: null],
        ['label' => 'Status', 'value' => $status ? ($statusMap[$status] ?? null) : null],
        ['label' => 'Ekskul', 'value' => data_get($extracurriculars->firstWhere('id', $extracurricularId), 'name')],
        ['label' => 'Kategori', 'value' => ($category ?? 'all') !== 'all' ? data_get(collect($categories)->firstWhere('key', $category), 'label', $category) : null],
        ['label' => 'JK', 'value' => ($gender ?? '') === 'L' ? 'Laki-laki' : (($gender ?? '') === 'P' ? 'Perempuan' : null)],
        ['label' => 'Mulai', 'value' => $dateFrom ?: null],
        ['label' => 'Selesai', 'value' => $dateTo ?: null],
    ];
    $statCards = [
        ['label' => 'Total Pendaftaran', 'value' => $statistics['total'], 'icon' => 'bi-clipboard-data', 'tone' => 'primary'],
        ['label' => 'Menunggu', 'value' => $statistics['pending'], 'icon' => 'bi-hourglass-split', 'tone' => 'info'],
        ['label' => 'Proses Tes', 'value' => $statistics['test'], 'icon' => 'bi-clipboard2-pulse', 'tone' => 'warning'],
        ['label' => 'Diterima', 'value' => $statistics['approved'], 'icon' => 'bi-person-check', 'tone' => 'success'],
        ['label' => 'Ditolak', 'value' => $statistics['closed'], 'icon' => 'bi-person-x', 'tone' => 'danger'],
    ];
@endphp

<div class="registration-stat-grid mb-3" aria-label="Ringkasan pendaftaran">
    @foreach($statCards as $stat)
        <div class="registration-stat-card is-{{ $stat['tone'] }}">
            <span class="registration-stat-card__icon"><i class="bi {{ $stat['icon'] }}"></i></span>
            <div>
                <span>{{ $stat['label'] }}</span>
                <strong>{{ $stat['value'] }}</strong>
            </div>
        </div>
    @endforeach
</div>

<x-filter.card
    class="mb-3"
    title="Filter Data Pendaftar"
    :description="$registrationFilterDescription"
>
    @if($isAdminRegistrationList)
        <x-slot:actions>
            <x-filter.export-dropdown
                :items="[
                    ['label' => 'Unduh Excel', 'href' => route('admin.registrations.export', array_merge(request()->query(), ['format' => 'xls'])), 'icon' => 'bi-file-earmark-excel'],
                    ['label' => 'Unduh PDF', 'href' => route('admin.registrations.export', array_merge(request()->query(), ['format' => 'pdf'])), 'icon' => 'bi-file-earmark-pdf'],
                ]"
            />
        </x-slot:actions>
    @endif
    <x-slot:active>
        <x-filter.active-filters :items="$activeFilters" :reset-url="$indexRoute" />
    </x-slot:active>
    <form class="toolbar-grid" method="get">
        <x-filter.field label="Cari siswa atau NIS" :for="$filterPrefix . '_search'" col="toolbar-col-4">
            <input type="search" id="{{ $filterPrefix }}_search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama siswa atau NIS">
        </x-filter.field>
        <x-filter.field label="Kelas" :for="$filterPrefix . '_class_name'" col="toolbar-col-2">
            <select id="{{ $filterPrefix }}_class_name" name="class_name" class="form-select">
                <option value="">Semua kelas</option>
                @foreach($classOptions as $option)
                    <option value="{{ $option }}" @selected($className === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </x-filter.field>
        <x-filter.field label="Status" :for="$filterPrefix . '_status'" col="toolbar-col-3">
            <select id="{{ $filterPrefix }}_status" name="status" class="form-select">
                <option value="">Semua status</option>
                @foreach($statusMap as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
        </x-filter.field>
        <x-filter.actions col="toolbar-col-3">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i>Tampilkan</button>
            <a href="{{ $indexRoute }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
        </x-filter.actions>

        <div class="toolbar-col-12">
            <button
                class="btn btn-outline-secondary btn-sm"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#{{ $filterPrefix }}_advanced"
                aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
                aria-controls="{{ $filterPrefix }}_advanced"
            >
                <i class="bi bi-sliders"></i>Filter Lanjutan
            </button>
        </div>

        <div id="{{ $filterPrefix }}_advanced" class="toolbar-col-12 filter-advanced collapse {{ $hasAdvancedFilters ? 'show' : '' }}">
            <div class="toolbar-grid">
                <x-filter.field label="Ekstrakurikuler" :for="$filterPrefix . '_extracurricular'" col="toolbar-col-4">
                    <select id="{{ $filterPrefix }}_extracurricular" name="extracurricular_id" class="form-select">
                        <option value="">{{ $isAdminRegistrationList ? 'Semua ekskul' : 'Semua ekskul binaan' }}</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </x-filter.field>
                <x-filter.field label="Kategori" :for="$filterPrefix . '_category'" col="toolbar-col-4">
                    <select id="{{ $filterPrefix }}_category" name="category" class="form-select">
                        <option value="all">Semua kategori</option>
                        @foreach($categories as $item)
                            <option value="{{ $item['key'] }}" @selected(($category ?? 'all') === $item['key'])>{{ $item['label'] }}</option>
                        @endforeach
                    </select>
                </x-filter.field>
                <x-filter.field label="Jenis kelamin" :for="$filterPrefix . '_gender'" col="toolbar-col-4">
                    <select id="{{ $filterPrefix }}_gender" name="gender" class="form-select">
                        <option value="">Semua</option>
                        <option value="L" @selected(($gender ?? '') === 'L')>Laki-laki</option>
                        <option value="P" @selected(($gender ?? '') === 'P')>Perempuan</option>
                    </select>
                </x-filter.field>
                <x-filter.field label="Tanggal mulai" :for="$filterPrefix . '_date_from'" col="toolbar-col-3">
                    <input id="{{ $filterPrefix }}_date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="form-control">
                </x-filter.field>
                <x-filter.field label="Tanggal selesai" :for="$filterPrefix . '_date_to'" col="toolbar-col-3">
                    <input id="{{ $filterPrefix }}_date_to" name="date_to" type="date" value="{{ $dateTo }}" class="form-control">
                </x-filter.field>
                <x-filter.field label="Data per halaman" :for="$filterPrefix . '_per_page'" col="toolbar-col-3">
                    <select id="{{ $filterPrefix }}_per_page" name="per_page" class="form-select">
                        @foreach([10, 20, 50] as $size)
                            <option value="{{ $size }}" @selected((int) $perPage === $size)>{{ $size }} data</option>
                        @endforeach
                    </select>
                </x-filter.field>
            </div>
        </div>
    </form>
</x-filter.card>

<div class="card registration-list-card">
    <div class="card-header registration-list-card__header">
        <div>
            <strong>Daftar Pendaftar</strong>
            <div class="small text-muted fw-normal mt-1">Satu baris mewakili satu pendaftaran kegiatan.</div>
        </div>
        @if($registrations->total() > 0)
            <span class="registration-list-card__range">
                Menampilkan {{ $registrations->firstItem() }}-{{ $registrations->lastItem() }} dari {{ $registrations->total() }} pendaftaran
            </span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="desktop-table table-responsive registration-table-wrap">
            <table class="table table-striped table-compact registration-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Siswa</th>
                        <th scope="col">Kegiatan</th>
                        <th scope="col">Kelas</th>
                        <th scope="col">Tanggal Daftar</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center table-action-col table-action-col--compact">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $registration)
                        @php
                            $displayStatus = $registration->displayStatus();
                            $canVerify = in_array($displayStatus, $verifiableDisplayStatuses, true);
                            $studentName = $registration->student->user->name ?? '-';
                            $studentNis = $registration->student->nis ?? null;
                            $studentClass = $registration->student->class_name ?? null;
                            $activityName = $registration->extracurricular->name ?? '-';
                            $hasLegacyOverflow = $registration->student->hasLegacyRegistrationOverflow();
                        @endphp
                        <tr>
                            <td>{{ $registrations->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="table-person">
                                    <strong>{{ $studentName }}</strong>
                                    @if($studentNis)
                                        <small>NIS: {{ $studentNis }}</small>
                                    @else
                                        <small class="registration-missing-data"><i class="bi bi-exclamation-circle"></i>NIS belum diisi</small>
                                    @endif
                                    @if($hasLegacyOverflow)
                                        <small class="registration-overflow-warning">
                                            <i class="bi bi-exclamation-triangle"></i>Pendaftaran baru harus ditahan.
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="{{ route($showRouteName, $registration) }}" class="registration-activity-link">{{ $activityName }}</a>
                                @if($registration->selected_branch)
                                    <div class="small text-muted mt-1">Cabang: {{ $registration->selected_branch }}</div>
                                @endif
                            </td>
                            <td>
                                @if($studentClass)
                                    {{ $studentClass }}
                                @else
                                    <span class="registration-missing-data"><i class="bi bi-exclamation-circle"></i>Kelas belum diisi</span>
                                @endif
                            </td>
                            <td>{{ optional($registration->registration_date)->format('d-m-Y') ?: '-' }}</td>
                            <td><x-registration.status-badge :registration="$registration" /></td>
                            <td class="text-center table-action-col table-action-col--compact">
                                <div class="registration-row-actions">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-sm action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka aksi untuk {{ $studentName }}">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                            <a href="{{ route($showRouteName, $registration) }}" class="dropdown-item">
                                                <i class="bi bi-eye me-2"></i>Periksa pendaftaran
                                            </a>
                                            <button type="button" class="dropdown-item profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $registration) }}">
                                                <i class="bi bi-person-badge me-2"></i>Lihat profil
                                            </button>
                                            @if($canVerify)
                                                <button
                                                    type="button"
                                                    class="dropdown-item registration-verify-trigger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#registrationVerificationModal"
                                                    data-action="{{ route($updateRouteName, $registration) }}"
                                                    data-student="{{ $studentName }}"
                                                    data-nis="{{ $studentNis ?: 'NIS belum diisi' }}"
                                                    data-class-name="{{ $studentClass ?: 'Kelas belum diisi' }}"
                                                    data-extracurricular-id="{{ $registration->extracurricular_id }}"
                                                    data-extracurricular="{{ $activityName }}"
                                                    data-primary-talent="{{ $registration->primary_talent ?: '-' }}"
                                                    data-prior-experience="{{ $registration->prior_experience ?: '-' }}"
                                                    data-current-skills="{{ $registration->current_skills ?: '-' }}"
                                                    data-achievement-history="{{ $registration->achievement_history ?: '-' }}"
                                                    data-notes="{{ $registration->notes ?? '' }}"
                                                    data-current-schedule-id="{{ optional($registration->talentTestParticipants->sortByDesc('id')->first())->schedule_id ?? '' }}"
                                                    data-default-decision="{{ $registration->willing_to_take_test ? 'schedule_test' : 'approve' }}"
                                                    data-modal-title="{{ $displayStatus === 'pending' ? 'Verifikasi Pendaftar' : ($displayStatus === 'rejected' ? 'Tinjau Kembali Pendaftaran' : 'Ubah Keputusan Pendaftaran') }}"
                                                >
                                                    <i class="bi bi-check2-square me-2"></i>Ubah keputusan
                                                </button>
                                            @endif
                                            @if($registration->isCancellationRequested())
                                                <form method="post" action="{{ route($reviewCancellationRouteName, $registration) }}" onsubmit="return confirm('Setujui pembatalan keikutsertaan siswa ini?');">
                                                    @csrf
                                                    @method('patch')
                                                    <input type="hidden" name="decision" value="approve">
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bi bi-check-circle me-2"></i>Setujui pembatalan
                                                    </button>
                                                </form>
                                                <form method="post" action="{{ route($reviewCancellationRouteName, $registration) }}" onsubmit="return confirm('Tolak permintaan pembatalan siswa ini?');">
                                                    @csrf
                                                    @method('patch')
                                                    <input type="hidden" name="decision" value="reject">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-x-circle me-2"></i>Tolak permintaan batal
                                                    </button>
                                                </form>
                                            @endif
                                            @if(filled($registration->notes))
                                                <button
                                                    type="button"
                                                    class="dropdown-item"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#registrationNoteModal"
                                                    data-student="{{ $studentName }}"
                                                    data-extracurricular="{{ $activityName }}"
                                                    data-note="{{ $registration->notes }}"
                                                >
                                                    <i class="bi bi-journal-text me-2"></i>Lihat catatan
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                                    <p class="mb-2">{{ $registrationEmptyMessage }}</p>
                                    @if($hasFilters)
                                        <a href="{{ $indexRoute }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-repeat"></i>Reset filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mobile-stack-table p-3">
            @forelse($registrations as $registration)
                @php
                    $displayStatus = $registration->displayStatus();
                    $canVerify = in_array($displayStatus, $verifiableDisplayStatuses, true);
                    $studentName = $registration->student->user->name ?? '-';
                    $studentNis = $registration->student->nis ?? null;
                    $studentClass = $registration->student->class_name ?? null;
                    $activityName = $registration->extracurricular->name ?? '-';
                    $hasLegacyOverflow = $registration->student->hasLegacyRegistrationOverflow();
                @endphp
                <article class="mobile-data-card registration-mobile-card">
                    <div class="mobile-data-card-header">
                        <div>
                            <h3 class="mobile-data-card-title">{{ $studentName }}</h3>
                            @if($studentNis)
                                <div class="small text-muted">NIS: {{ $studentNis }}</div>
                            @else
                                <div class="registration-missing-data"><i class="bi bi-exclamation-circle"></i>NIS belum diisi</div>
                            @endif
                            @if($hasLegacyOverflow)
                                <div class="registration-overflow-warning">
                                    <i class="bi bi-exclamation-triangle"></i>Pendaftaran baru harus ditahan.
                                </div>
                            @endif
                        </div>
                        <x-registration.status-badge :registration="$registration" />
                    </div>
                    <div class="registration-mobile-card__activity">
                        <span>Kegiatan</span>
                        <strong>{{ $activityName }}</strong>
                        @if($registration->selected_branch)
                            <small>Cabang: {{ $registration->selected_branch }}</small>
                        @endif
                    </div>
                    <div class="mobile-data-list">
                        <div>
                            <span class="mobile-data-item-label">Kelas</span>
                            <p class="mobile-data-item-value">{{ $studentClass ?: 'Kelas belum diisi' }}</p>
                        </div>
                        <div>
                            <span class="mobile-data-item-label">Tanggal daftar</span>
                            <p class="mobile-data-item-value">{{ optional($registration->registration_date)->format('d-m-Y') ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="registration-mobile-actions">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Buka aksi untuk {{ $studentName }}">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                <a href="{{ route($showRouteName, $registration) }}" class="dropdown-item">
                                    <i class="bi bi-eye me-2"></i>Periksa pendaftaran
                                </a>
                                <button type="button" class="dropdown-item profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $registration) }}">
                                    <i class="bi bi-person-badge me-2"></i>Lihat profil
                                </button>
                                @if($canVerify)
                                    <button
                                        type="button"
                                        class="dropdown-item registration-verify-trigger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#registrationVerificationModal"
                                        data-action="{{ route($updateRouteName, $registration) }}"
                                        data-student="{{ $studentName }}"
                                        data-nis="{{ $studentNis ?: 'NIS belum diisi' }}"
                                        data-class-name="{{ $studentClass ?: 'Kelas belum diisi' }}"
                                        data-extracurricular-id="{{ $registration->extracurricular_id }}"
                                        data-extracurricular="{{ $activityName }}"
                                        data-primary-talent="{{ $registration->primary_talent ?: '-' }}"
                                        data-prior-experience="{{ $registration->prior_experience ?: '-' }}"
                                        data-current-skills="{{ $registration->current_skills ?: '-' }}"
                                        data-achievement-history="{{ $registration->achievement_history ?: '-' }}"
                                        data-notes="{{ $registration->notes ?? '' }}"
                                        data-current-schedule-id="{{ optional($registration->talentTestParticipants->sortByDesc('id')->first())->schedule_id ?? '' }}"
                                        data-default-decision="{{ $registration->willing_to_take_test ? 'schedule_test' : 'approve' }}"
                                        data-modal-title="{{ $displayStatus === 'pending' ? 'Verifikasi Pendaftar' : ($displayStatus === 'rejected' ? 'Tinjau Kembali Pendaftaran' : 'Ubah Keputusan Pendaftaran') }}"
                                    >
                                        <i class="bi bi-check2-square me-2"></i>Ubah keputusan
                                    </button>
                                @endif
                                @if($registration->isCancellationRequested())
                                    <form method="post" action="{{ route($reviewCancellationRouteName, $registration) }}" onsubmit="return confirm('Setujui pembatalan keikutsertaan siswa ini?');">
                                        @csrf
                                        @method('patch')
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" class="dropdown-item text-success"><i class="bi bi-check-circle me-2"></i>Setujui pembatalan</button>
                                    </form>
                                    <form method="post" action="{{ route($reviewCancellationRouteName, $registration) }}" onsubmit="return confirm('Tolak permintaan pembatalan siswa ini?');">
                                        @csrf
                                        @method('patch')
                                        <input type="hidden" name="decision" value="reject">
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i>Tolak permintaan batal</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                    <p class="mb-2">{{ $registrationEmptyMessage }}</p>
                    @if($hasFilters)
                        <a href="{{ $indexRoute }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-repeat"></i>Reset filter</a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
    @if($registrations->hasPages())
        <div class="card-body registration-pagination">
            <div class="small text-muted">
                Menampilkan {{ $registrations->firstItem() }}-{{ $registrations->lastItem() }} dari {{ $registrations->total() }} pendaftaran
            </div>
            {{ $registrations->onEachSide(0)->links('vendor.pagination.bootstrap-5-compact') }}
        </div>
    @endif
</div>
