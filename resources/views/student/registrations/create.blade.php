@extends('layouts.app')

@section('page_title', 'Form Pendaftaran Kegiatan')
@section('page_subtitle', 'Isi pendaftaran secara ringkas setelah membaca detail kegiatan.')

@push('styles')
    <style>
        .registration-summary-card,
        .registration-form-card {
            border-radius: 28px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(248, 251, 255, 0.95));
            box-shadow: 0 18px 32px rgba(16, 35, 63, 0.07);
        }

        .registration-summary-card {
            padding: 1.15rem;
        }

        .registration-summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .registration-summary-item {
            border: 1px solid #e1ebf5;
            border-radius: 18px;
            background: #fbfdff;
            padding: 0.9rem 1rem;
        }

        .registration-summary-item .label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b8098;
        }

        .registration-summary-item .value {
            color: #18334f;
            font-weight: 700;
            line-height: 1.45;
        }

        .registration-form-card .card-body {
            padding: 1.2rem;
        }

        .registration-profile-alert {
            border-radius: 18px;
            border: 1px solid #f4d7a7;
            background: linear-gradient(180deg, #fffaf0 0%, #fff7e8 100%);
            padding: 1rem;
        }

        .registration-textarea {
            min-height: 110px;
            resize: vertical;
        }

        .registration-accordion summary {
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            cursor: pointer;
        }

        .registration-accordion summary::-webkit-details-marker {
            display: none;
        }

        .registration-accordion summary strong {
            display: block;
            color: #18334f;
        }

        .registration-accordion summary small {
            display: block;
            color: #6a7f98;
            margin-top: 0.15rem;
        }

        .registration-accordion summary i {
            transition: transform 0.18s ease;
        }

        .registration-accordion[open] summary i {
            transform: rotate(180deg);
        }

        .registration-accordion-body {
            padding-top: 1rem;
        }

        @media (max-width: 767.98px) {
            .registration-summary-grid {
                grid-template-columns: 1fr;
            }

            .registration-summary-card,
            .registration-form-card .card-body {
                padding: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $memberCount = $extracurricular->participants_count ?? 0;
        $scheduleText = $extracurricular->schedule_overview ?: ($extracurricular->schedules->first()?->title ?: 'Jadwal latihan belum tersedia.');
        $coachText = $extracurricular->coach_names === 'Belum tersedia' ? 'Pembina belum ditentukan.' : $extracurricular->coach_names;
        $profileIncomplete = blank($student->nis) || blank($student->class_name);
        $branchOptions = collect($extracurricular->branch_options ?? [])->filter()->values();
        $statusLabel = match (strtolower((string) $registration?->status)) {
            'pending' => 'Menunggu Konfirmasi',
            'approved' => 'Sudah Mendaftar',
            'rejected' => 'Pendaftaran Ditolak',
            default => $extracurricular->is_active ? 'Pendaftaran Tersedia' : 'Pendaftaran Ditutup',
        };
    @endphp

    <div class="split-actions mb-3">
        <a href="{{ route('student.extracurriculars.show', $extracurricular) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke Detail</a>
        @if($registration)
            <a href="{{ route('student.registrations.index') }}" class="btn btn-outline-primary"><i class="bi bi-clipboard-check"></i>Lihat Status Pendaftaran</a>
        @endif
    </div>

    <div class="registration-summary-card mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="section-kicker"><i class="bi bi-send-check"></i>Ringkasan Ekstrakurikuler</div>
                <h2 class="h5 mb-1">{{ $extracurricular->name }}</h2>
                <p class="toolbar-hint mb-0">Pastikan ringkasan kegiatan ini sudah sesuai sebelum mengirim formulir.</p>
            </div>
            <span class="badge {{ strtolower((string) $registration?->status) === 'approved' ? 'badge-status-success' : (strtolower((string) $registration?->status) === 'rejected' ? 'badge-status-danger' : ($extracurricular->is_active ? 'badge-status-warning' : 'badge-status-secondary')) }}">
                {{ $statusLabel }}
            </span>
        </div>

        <div class="registration-summary-grid">
            <div class="registration-summary-item">
                <span class="label">Jadwal</span>
                <div class="value">{{ $scheduleText }}</div>
            </div>
            <div class="registration-summary-item">
                <span class="label">Pembina</span>
                <div class="value">{{ $coachText }}</div>
            </div>
            @if($branchOptions->isNotEmpty())
                <div class="registration-summary-item">
                    <span class="label">Pilihan cabang</span>
                    <div class="value">{{ $branchOptions->implode(', ') }}</div>
                </div>
            @endif
            <div class="registration-summary-item">
                <span class="label">Anggota aktif</span>
                <div class="value">{{ $memberCount > 0 ? $memberCount.' siswa' : 'Belum ada data anggota aktif.' }}</div>
            </div>
            <div class="registration-summary-item">
                <span class="label">Siswa pendaftar</span>
                <div class="value">{{ $student->user->name }}{{ $student->nis ? ' | NIS '.$student->nis : '' }}{{ $student->class_name ? ' | '.$student->class_name : '' }}</div>
            </div>
        </div>
    </div>

    <div class="card registration-form-card">
        <div class="card-header">{{ $registration ? 'Status Pendaftaran' : 'Form Pendaftaran' }}</div>
        <div class="card-body">
            @if($registration && $registration->status !== \App\Models\Registration::STATUS_REJECTED)
                <div class="info-banner mb-3">
                    <i class="bi bi-clipboard-check"></i>
                    <div>
                        <strong class="d-block mb-1">Pendaftaran sudah dikirim</strong>
                        Status saat ini: <span class="badge" data-status="{{ $registration->status }}">{{ $registration->status }}</span>
                    </div>
                </div>
                <div class="form-section-card mb-3">
                    <h3 class="form-section-title">Data yang sudah dikirim</h3>
                    @include('partials.registration-talent-summary', ['registration' => $registration])
                </div>
                <div class="form-actions">
                    <a href="{{ route('student.extracurriculars.show', $extracurricular) }}" class="btn btn-outline-secondary flex-fill"><i class="bi bi-arrow-left"></i>Kembali ke Detail</a>
                    <a href="{{ route('student.registrations.index') }}" class="btn btn-outline-primary flex-fill"><i class="bi bi-clipboard-check"></i>Lihat Status Pendaftaran</a>
                </div>
            @else
                <form method="post" action="{{ route('student.registrations.store', $extracurricular) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-section-card mb-3">
                        <h3 class="form-section-title">1. Data siswa</h3>
                        <p class="form-section-copy">Data akun dan profil siswa berikut akan otomatis dipakai pada proses pendaftaran.</p>
                        @if($profileIncomplete)
                            <div class="registration-profile-alert mb-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                    <div>
                                        <strong class="d-block mb-1">Profil siswa belum lengkap</strong>
                                        <span class="helper-text">NIS atau kelas masih kosong. Kamu tidak perlu mengisi ulang di sini, tetapi sebaiknya lengkapi profil terlebih dahulu.</span>
                                    </div>
                                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-person-circle"></i>Lengkapi Profil</a>
                                </div>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama siswa</label>
                                <input type="text" class="form-control" value="{{ $student->user->name }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">NIS</label>
                                <input type="text" class="form-control" value="{{ $student->nis ?: 'Belum diisi' }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Kelas</label>
                                <input type="text" class="form-control" value="{{ $student->class_name ?: 'Belum diisi' }}" readonly>
                            </div>
                        </div>
                    </div>

                    @include('partials.registration-talent-fields', ['registration' => $registration, 'extracurricular' => $extracurricular])

                    <div class="form-actions">
                        <a href="{{ route('student.extracurriculars.show', $extracurricular) }}" class="btn btn-outline-secondary flex-fill"><i class="bi bi-arrow-left"></i>Kembali ke Detail</a>
                        <button class="btn btn-primary flex-fill" type="submit" data-loading-text="Mengirim..." @disabled(!$extracurricular->is_active)><i class="bi bi-send-check"></i>Kirim Pendaftaran</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.querySelector('.registration-form-card form');
            const motivation = document.getElementById('motivation_reason');
            const count = document.getElementById('motivationCount');
            const goalStatement = document.querySelector('input[name="goal_statement"]');
            const currentSkills = document.getElementById('current_skills');
            const priorExperience = document.querySelector('input[name="prior_experience"]');

            const syncTextFields = function () {
                if (goalStatement && motivation) {
                    goalStatement.value = motivation.value;
                }

                if (priorExperience && currentSkills) {
                    priorExperience.value = currentSkills.value;
                }
            };

            const updateCount = function () {
                if (count && motivation) {
                    count.textContent = motivation.value.length;
                }
            };

            motivation?.addEventListener('input', function () {
                updateCount();
                syncTextFields();
            });

            currentSkills?.addEventListener('input', syncTextFields);
            updateCount();
            syncTextFields();

            form?.addEventListener('submit', function (event) {
                syncTextFields();
                const submitButton = form.querySelector('button[type="submit"]');
                if (!submitButton || submitButton.disabled) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.style.width = `${submitButton.offsetWidth}px`;
                submitButton.classList.add('is-loading');
                submitButton.dataset.originalHtml = submitButton.innerHTML;
                submitButton.innerHTML = '<span class="btn-loading-content"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span class="btn-loading-label">' + (submitButton.dataset.loadingText || 'Memproses...') + '</span></span>';

                if (!form.checkValidity()) {
                    event.preventDefault();
                    submitButton.disabled = false;
                    submitButton.classList.remove('is-loading');
                    submitButton.style.width = '';
                    submitButton.innerHTML = submitButton.dataset.originalHtml;
                }
            });
        })();
    </script>
@endpush
