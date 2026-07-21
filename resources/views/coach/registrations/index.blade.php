@extends('layouts.app')

@section('page_title', 'Pendaftar Ekstrakurikuler')
@section('page_subtitle', 'Periksa pendaftaran siswa pada ekstrakurikuler binaan dan berikan keputusan dengan alur yang lebih rapi.')

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
                    <p class="toolbar-hint mb-0">Cari berdasarkan nama, NIS, kelas, atau kegiatan, lalu saring per kategori, kegiatan yang diikuti, jenis kelamin, dan status agar verifikasi lebih cepat.</p>
                </div>
            </div>
            <form class="toolbar-grid">
                <div class="toolbar-col-4">
                    <label class="form-label" for="search">Cari siswa</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Masukkan nama siswa, NIS, kelas, atau kegiatan">
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="extracurricular_id">Kegiatan yang Diikuti</label>
                    <select id="extracurricular_id" name="extracurricular_id" class="form-select">
                        <option value="">Semua kegiatan binaan</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string) $extracurricularId === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="category">Kategori</label>
                    <select id="category" name="category" class="form-select">
                        <option value="all">Semua kategori</option>
                        @foreach($categories as $item)
                            <option value="{{ $item['key'] }}" @selected($category === $item['key'])>{{ $item['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="class_name">Kelas</label>
                    <select id="class_name" name="class_name" class="form-select">
                        <option value="">Semua kelas</option>
                        @foreach($classOptions as $item)
                            <option value="{{ $item }}" @selected($className === $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="gender">Jenis kelamin</label>
                    <select id="gender" name="gender" class="form-select">
                        <option value="">Semua</option>
                        <option value="L" @selected($gender === 'L')>Laki-laki</option>
                        <option value="P" @selected($gender === 'P')>Perempuan</option>
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
                <div class="toolbar-col-2">
                    <a href="{{ route('coach.registrations.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a>
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
                        <th>No</th>
                        <th>Siswa</th>
                        <th>Kegiatan yang Diikuti</th>
                        <th>Kelas</th>
                        <th>Tanggal Daftar Terakhir</th>
                        <th>Status</th>
                        <th>Ringkasan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($registrations as $student)
                        @php
                            $studentRegistrations = $student->registrations
                                ->sortByDesc(fn ($item) => optional($item->registration_date)->timestamp ?? 0)
                                ->values();
                            $latestRegistration = $studentRegistrations->first();
                        @endphp
                        <tr>
                            <td>{{ $registrations->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="table-person">
                                    <strong>{{ $student->user->name ?? '-' }}</strong>
                                    <small>NIS: {{ $student->nis ?? '-' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="student-activity-list">
                                    @foreach($studentRegistrations as $registration)
                                        <a href="{{ route('coach.registrations.show', $registration) }}" class="student-activity-link">
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
                                            $hasPublishedResult = $registration->talentTestResults->contains(fn ($item) => $item->status === 'published');
                                            $latestTalentParticipant = $registration->talentTestParticipants
                                                ->sortByDesc(fn ($item) => optional($item->schedule)->activity_date?->timestamp ?? 0)
                                                ->first();
                                            $hasScheduledTest = (bool) $latestTalentParticipant;
                                            $displayStatus = $registration->status;
                                            if ($registration->status === 'approved' && $registration->willing_to_take_test && ! $hasPublishedResult) {
                                                $displayStatus = $hasScheduledTest ? 'scheduled_test' : 'waiting_test';
                                            }
                                            $displayStatusLabel = $statusMap[$displayStatus] ?? ucfirst($displayStatus);
                                        @endphp
                                        <span class="badge" data-status="{{ $displayStatusLabel }}">{{ $displayStatusLabel }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="table-summary-text">
                                    {{ $studentRegistrations->count() }} kegiatan dipilih.
                                    @if($latestRegistration?->primary_talent)
                                        Fokus terakhir: {{ $latestRegistration->primary_talent }}.
                                    @elseif($latestRegistration?->current_skills)
                                        Kemampuan awal: {{ $latestRegistration->current_skills }}.
                                    @endif
                                </div>
                            </td>
                            <td class="text-end table-action-col">
                                <div class="d-flex flex-column gap-2 align-items-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $latestRegistration) }}">
                                        <i class="bi bi-person-badge"></i>
                                        <span class="d-none d-md-inline">Profil</span>
                                    </button>
                                    @foreach($studentRegistrations as $registration)
                                        @php
                                            $hasPublishedResult = $registration->talentTestResults->contains(fn ($item) => $item->status === 'published');
                                            $latestTalentParticipant = $registration->talentTestParticipants
                                                ->sortByDesc(fn ($item) => optional($item->schedule)->activity_date?->timestamp ?? 0)
                                                ->first();
                                            $hasScheduledTest = (bool) $latestTalentParticipant;
                                            $displayStatus = $registration->status;
                                            if ($registration->status === 'approved' && $registration->willing_to_take_test && ! $hasPublishedResult) {
                                                $displayStatus = $hasScheduledTest ? 'scheduled_test' : 'waiting_test';
                                            }
                                        @endphp
                                        <div class="d-flex flex-wrap justify-content-end gap-1">
                                            <a href="{{ route('coach.registrations.show', $registration) }}" class="btn btn-sm btn-outline-primary action-button-compact">
                                                <i class="bi bi-eye"></i>
                                                <span class="d-none d-md-inline">{{ $registration->extracurricular->catalog_item_name ?? 'Detail' }}</span>
                                            </a>
                                            @if($displayStatus === 'pending' || in_array($displayStatus, ['approved', 'rejected'], true))
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-secondary registration-verify-trigger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#registrationVerificationModal"
                                                    data-action="{{ route('coach.registrations.update-status', $registration) }}"
                                                    data-student="{{ $registration->student->user->name ?? '-' }}"
                                                    data-nis="{{ $registration->student->nis ?? '-' }}"
                                                    data-class-name="{{ $registration->student->class_name ?? '-' }}"
                                                    data-extracurricular="{{ $registration->extracurricular->name ?? '-' }}"
                                                    data-primary-talent="{{ $registration->primary_talent ?: '-' }}"
                                                    data-prior-experience="{{ $registration->prior_experience ?: '-' }}"
                                                    data-current-skills="{{ $registration->current_skills ?: '-' }}"
                                                    data-achievement-history="{{ $registration->achievement_history ?: '-' }}"
                                                    data-notes="{{ $registration->notes ?? '' }}"
                                                    data-default-decision="{{ $displayStatus === 'pending' ? ($registration->willing_to_take_test ? 'schedule_test' : 'approve') : ($displayStatus === 'rejected' ? 'approve' : ($registration->willing_to_take_test ? 'schedule_test' : 'approve')) }}"
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
                                    <p class="mb-0">Belum ada pendaftar untuk ekskul binaan Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mobile-stack-table p-3">
                @forelse($registrations as $student)
                    @php
                        $studentRegistrations = $student->registrations
                            ->sortByDesc(fn ($item) => optional($item->registration_date)->timestamp ?? 0)
                            ->values();
                        $latestRegistration = $studentRegistrations->first();
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
                                        <a href="{{ route('coach.registrations.show', $registration) }}" class="student-activity-link">
                                            {{ $registration->extracurricular->name ?? '-' }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            <div><span class="mobile-data-item-label">Tanggal daftar terakhir</span><p class="mobile-data-item-value">{{ optional($latestRegistration?->registration_date)->format('d-m-Y') ?: '-' }}</p></div>
                        </div>
                        <div class="mobile-data-card-actions">
                            <button type="button" class="btn btn-outline-secondary profile-preview-trigger" data-profile-url="{{ route('registrations.profile-preview', $latestRegistration) }}">
                                <i class="bi bi-person-badge"></i>Profil
                            </button>
                            @foreach($studentRegistrations as $registration)
                                <a href="{{ route('coach.registrations.show', $registration) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>{{ $registration->extracurricular->catalog_item_name ?? 'Detail' }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                        <p class="mb-0">Belum ada pendaftar untuk ekskul binaan Anda.</p>
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
                            <p class="form-section-copy">Pilih keputusan yang paling sesuai. Opsi jadwalkan tes tetap mempertahankan alur verifikasi, lalu lanjutkan penjadwalan tes dari modul tes bakat pembina.</p>
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
