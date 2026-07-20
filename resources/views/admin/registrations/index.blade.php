@extends('layouts.app')

@section('page_title', 'Pendaftar Ekstrakurikuler')
@section('page_subtitle', 'Periksa pendaftaran siswa dan berikan keputusan dengan tampilan yang lebih mudah dipantau')

@section('content')
    @php
        $statusMap = [
            'pending' => 'Menunggu',
            'waiting_test' => 'Menunggu Tes',
            'scheduled_test' => 'Tes Dijadwalkan',
            'approved' => 'Diterima',
            'rejected' => 'Ditolak',
        ];
    @endphp

    <div class="card mb-3">
        <div class="card-body toolbar-card">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Filter Data Pendaftar</h2>
                    <p class="toolbar-hint mb-0">Cari berdasarkan nama atau NIS, lalu saring per status dan ekstrakurikuler agar verifikasi lebih cepat.</p>
                </div>
                <div class="quick-actions">
                    <a href="{{ route('admin.registrations.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf"></i>Unduh PDF
                    </a>
                    <a href="{{ route('admin.registrations.export', array_merge(request()->query(), ['format' => 'xls'])) }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel"></i>Unduh Excel
                    </a>
                </div>
            </div>
            <form class="toolbar-grid">
                <div class="toolbar-col-4">
                    <label class="form-label" for="search">Cari siswa</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Masukkan nama siswa atau NIS">
                </div>
                <div class="toolbar-col-4">
                    <label class="form-label" for="extracurricular_id">Ekstrakurikuler</label>
                    <select id="extracurricular_id" name="extracurricular_id" class="form-select">
                        <option value="">Semua ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="pending" @selected($status === 'pending')>Menunggu</option>
                        <option value="waiting_test" @selected($status === 'waiting_test')>Menunggu Tes</option>
                        <option value="approved" @selected($status === 'approved')>Diterima</option>
                        <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i>Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Pendaftar</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped table-compact mb-0">
                    <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Kegiatan</th>
                        <th>Cabang</th>
                        <th>Tanggal Daftar</th>
                        <th>Status</th>
                        <th>Ringkasan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($registrations as $registration)
                        @php
                            $hasPublishedResult = $registration->talentTestResults->contains(fn ($item) => $item->status === 'published');
                            $latestTalentParticipant = $registration->talentTestParticipants
                                ->sortByDesc(fn ($item) => optional($item->schedule)->activity_date?->timestamp ?? 0)
                                ->first();
                            $hasScheduledTest = (bool) $latestTalentParticipant;
                            $displayStatus = $registration->status;
                            if ($registration->status === 'approved' && $registration->willing_to_take_test && !$hasPublishedResult) {
                                $displayStatus = $hasScheduledTest ? 'scheduled_test' : 'waiting_test';
                            }
                            $displayStatusLabel = $statusMap[$displayStatus] ?? ucfirst($displayStatus);
                        @endphp
                        <tr>
                            <td>
                                <div class="table-person">
                                    <strong>{{ $registration->student->user->name ?? '-' }}</strong>
                                    <small>NIS: {{ $registration->student->nis ?? '-' }}</small>
                                </div>
                            </td>
                            <td>{{ $registration->extracurricular->name ?? '-' }}</td>
                            <td>{{ $registration->selected_branch_label }}</td>
                            <td>{{ optional($registration->registration_date)->format('d-m-Y') }}</td>
                            <td><span class="badge" data-status="{{ $displayStatusLabel }}">{{ $displayStatusLabel }}</span></td>
                            <td>
                                <div class="table-summary-text">{{ $registration->primary_talent ?: ($registration->current_skills ?: 'Belum diisi') }}</div>
                            </td>
                            <td class="text-end table-action-col">
                                <div class="table-inline-actions table-inline-actions--compact justify-content-end">
                                    <a
                                        href="{{ route('admin.registrations.show', $registration) }}"
                                        class="btn btn-sm btn-outline-primary action-button-compact"
                                        data-bs-toggle="tooltip"
                                        data-bs-title="Lihat detail pendaftaran"
                                    >
                                        <i class="bi bi-eye"></i>
                                        <span class="d-none d-md-inline">Detail</span>
                                    </a>

                                    <div class="dropdown">
                                        <button
                                            class="btn btn-sm btn-outline-secondary action-button-icon"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            data-bs-title="Tindakan lainnya"
                                            aria-expanded="false"
                                        >
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                            @if($displayStatus === 'pending')
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item registration-verify-trigger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#registrationVerificationModal"
                                                        data-action="{{ route('admin.registrations.update-status', $registration) }}"
                                                        data-student="{{ $registration->student->user->name ?? '-' }}"
                                                        data-nis="{{ $registration->student->nis ?? '-' }}"
                                                        data-class-name="{{ $registration->student->class_name ?? '-' }}"
                                                        data-extracurricular="{{ $registration->extracurricular->name ?? '-' }}"
                                                        data-primary-talent="{{ $registration->primary_talent ?: '-' }}"
                                                        data-prior-experience="{{ $registration->prior_experience ?: '-' }}"
                                                        data-current-skills="{{ $registration->current_skills ?: '-' }}"
                                                        data-achievement-history="{{ $registration->achievement_history ?: '-' }}"
                                                        data-notes="{{ $registration->notes ?? '' }}"
                                                        data-default-decision="{{ $registration->willing_to_take_test ? 'schedule_test' : 'approve' }}"
                                                        data-modal-title="Verifikasi Pendaftar"
                                                    >
                                                        <i class="bi bi-check2-square me-2"></i>Verifikasi
                                                    </button>
                                                </li>
                                            @endif

                                            @if(in_array($displayStatus, ['approved', 'rejected'], true))
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item registration-verify-trigger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#registrationVerificationModal"
                                                        data-action="{{ route('admin.registrations.update-status', $registration) }}"
                                                        data-student="{{ $registration->student->user->name ?? '-' }}"
                                                        data-nis="{{ $registration->student->nis ?? '-' }}"
                                                        data-class-name="{{ $registration->student->class_name ?? '-' }}"
                                                        data-extracurricular="{{ $registration->extracurricular->name ?? '-' }}"
                                                        data-primary-talent="{{ $registration->primary_talent ?: '-' }}"
                                                        data-prior-experience="{{ $registration->prior_experience ?: '-' }}"
                                                        data-current-skills="{{ $registration->current_skills ?: '-' }}"
                                                        data-achievement-history="{{ $registration->achievement_history ?: '-' }}"
                                                        data-notes="{{ $registration->notes ?? '' }}"
                                                        data-default-decision="{{ $displayStatus === 'rejected' ? 'approve' : ($registration->willing_to_take_test ? 'schedule_test' : 'approve') }}"
                                                        data-modal-title="{{ $displayStatus === 'rejected' ? 'Tinjau Kembali Pendaftaran' : 'Ubah Keputusan Pendaftaran' }}"
                                                    >
                                                        <i class="bi bi-arrow-repeat me-2"></i>Ubah Keputusan
                                                    </button>
                                                </li>
                                            @endif

                                            <li>
                                                <button type="button" class="dropdown-item profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $registration) }}">
                                                    <i class="bi bi-person-badge me-2"></i>Lihat Profil
                                                </button>
                                            </li>

                                            @if(in_array($displayStatus, ['waiting_test', 'scheduled_test', 'approved'], true) && $registration->willing_to_take_test)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.talent-tests.index', ['extracurricular_id' => $registration->extracurricular_id]) }}">
                                                        <i class="bi bi-clipboard2-pulse me-2"></i>{{ $displayStatus === 'scheduled_test' ? 'Kelola Tes' : 'Jadwalkan Tes' }}
                                                    </a>
                                                </li>
                                            @endif

                                            <li>
                                                <button
                                                    type="button"
                                                    class="dropdown-item registration-note-trigger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#registrationNoteModal"
                                                    data-student="{{ $registration->student->user->name ?? '-' }}"
                                                    data-extracurricular="{{ $registration->extracurricular->name ?? '-' }}"
                                                    data-note="{{ $registration->notes ?: 'Belum ada catatan verifikasi.' }}"
                                                >
                                                    <i class="bi bi-journal-text me-2"></i>Lihat Catatan
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
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
                @forelse($registrations as $registration)
                    @php
                        $hasPublishedResult = $registration->talentTestResults->contains(fn ($item) => $item->status === 'published');
                        $hasScheduledTest = $registration->talentTestParticipants->isNotEmpty();
                        $displayStatus = $registration->status;
                        if ($registration->status === 'approved' && $registration->willing_to_take_test && !$hasPublishedResult) {
                            $displayStatus = $hasScheduledTest ? 'scheduled_test' : 'waiting_test';
                        }
                        $displayStatusLabel = $statusMap[$displayStatus] ?? ucfirst($displayStatus);
                    @endphp
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <div>
                                <h3 class="mobile-data-card-title">{{ $registration->student->user->name ?? '-' }}</h3>
                                <div class="small text-muted">NIS: {{ $registration->student->nis ?? '-' }} | {{ $registration->student->class_name ?? '-' }}</div>
                            </div>
                            <span class="badge" data-status="{{ $displayStatusLabel }}">{{ $displayStatusLabel }}</span>
                        </div>
                        <div class="mobile-data-list mb-3">
                            <div><span class="mobile-data-item-label">Kegiatan</span><p class="mobile-data-item-value">{{ $registration->extracurricular->name ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Cabang</span><p class="mobile-data-item-value">{{ $registration->selected_branch_label }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal daftar</span><p class="mobile-data-item-value">{{ optional($registration->registration_date)->format('d-m-Y') }}</p></div>
                            <div><span class="mobile-data-item-label">Bakat utama</span><p class="mobile-data-item-value">{{ $registration->primary_talent ?: ($registration->current_skills ?: 'Belum diisi') }}</p></div>
                        </div>
                        <div class="table-inline-actions table-inline-actions--compact">
                            <a href="{{ route('admin.registrations.show', $registration) }}" class="btn btn-sm btn-outline-primary action-button-icon" data-bs-toggle="tooltip" data-bs-title="Lihat detail pendaftaran"><i class="bi bi-eye"></i></a>
                            <div class="dropdown flex-grow-0">
                                <button class="btn btn-sm btn-outline-secondary action-button-icon" type="button" data-bs-toggle="dropdown" data-bs-title="Tindakan lainnya" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-compact">
                                    @if($displayStatus === 'pending')
                                        <li><button type="button" class="dropdown-item registration-verify-trigger" data-bs-toggle="modal" data-bs-target="#registrationVerificationModal" data-action="{{ route('admin.registrations.update-status', $registration) }}" data-student="{{ $registration->student->user->name ?? '-' }}" data-nis="{{ $registration->student->nis ?? '-' }}" data-class-name="{{ $registration->student->class_name ?? '-' }}" data-extracurricular="{{ $registration->extracurricular->name ?? '-' }}" data-primary-talent="{{ $registration->primary_talent ?: '-' }}" data-prior-experience="{{ $registration->prior_experience ?: '-' }}" data-current-skills="{{ $registration->current_skills ?: '-' }}" data-achievement-history="{{ $registration->achievement_history ?: '-' }}" data-notes="{{ $registration->notes ?? '' }}" data-default-decision="{{ $registration->willing_to_take_test ? 'schedule_test' : 'approve' }}" data-modal-title="Verifikasi Pendaftar"><i class="bi bi-check2-square me-2"></i>Verifikasi</button></li>
                                    @endif
                                    @if(in_array($displayStatus, ['approved', 'rejected'], true))
                                        <li><button type="button" class="dropdown-item registration-verify-trigger" data-bs-toggle="modal" data-bs-target="#registrationVerificationModal" data-action="{{ route('admin.registrations.update-status', $registration) }}" data-student="{{ $registration->student->user->name ?? '-' }}" data-nis="{{ $registration->student->nis ?? '-' }}" data-class-name="{{ $registration->student->class_name ?? '-' }}" data-extracurricular="{{ $registration->extracurricular->name ?? '-' }}" data-primary-talent="{{ $registration->primary_talent ?: '-' }}" data-prior-experience="{{ $registration->prior_experience ?: '-' }}" data-current-skills="{{ $registration->current_skills ?: '-' }}" data-achievement-history="{{ $registration->achievement_history ?: '-' }}" data-notes="{{ $registration->notes ?? '' }}" data-default-decision="{{ $displayStatus === 'rejected' ? 'approve' : ($registration->willing_to_take_test ? 'schedule_test' : 'approve') }}" data-modal-title="{{ $displayStatus === 'rejected' ? 'Tinjau Kembali Pendaftaran' : 'Ubah Keputusan Pendaftaran' }}"><i class="bi bi-arrow-repeat me-2"></i>Ubah Keputusan</button></li>
                                    @endif
                                    <li><button type="button" class="dropdown-item profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $registration) }}"><i class="bi bi-person-badge me-2"></i>Lihat Profil</button></li>
                                    @if(in_array($displayStatus, ['waiting_test', 'scheduled_test', 'approved'], true) && $registration->willing_to_take_test)
                                        <li><a class="dropdown-item" href="{{ route('admin.talent-tests.index', ['extracurricular_id' => $registration->extracurricular_id]) }}"><i class="bi bi-clipboard2-pulse me-2"></i>{{ $displayStatus === 'scheduled_test' ? 'Kelola Tes' : 'Jadwalkan Tes' }}</a></li>
                                    @endif
                                    <li><button type="button" class="dropdown-item registration-note-trigger" data-bs-toggle="modal" data-bs-target="#registrationNoteModal" data-student="{{ $registration->student->user->name ?? '-' }}" data-extracurricular="{{ $registration->extracurricular->name ?? '-' }}" data-note="{{ $registration->notes ?: 'Belum ada catatan verifikasi.' }}"><i class="bi bi-journal-text me-2"></i>Lihat Catatan</button></li>
                                </ul>
                            </div>
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
                <form method="post" action="#" id="registrationVerificationForm">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="status" id="registrationVerificationStatus" value="approved">

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
                            <p class="form-section-copy">Pilih keputusan yang paling sesuai. Opsi jadwalkan tes tetap mempertahankan alur verifikasi yang sudah ada, lalu lanjutkan penjadwalan tes dari modul tes bakat.</p>
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
                                        <small>Simpan sebagai diterima dan lanjutkan penjadwalan tes bakat.</small>
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
