@extends('layouts.app')

@section('page_title', 'Detail Ekstrakurikuler')
@section('page_subtitle', 'Pelajari informasi lengkap sebelum mengirim pendaftaran')

@section('content')
    <div class="split-actions mb-3">
        <a href="{{ route('student.extracurriculars.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke Daftar Ekskul</a>
        <a href="#form-pendaftaran" class="btn btn-primary"><i class="bi bi-send-check"></i>Daftar Ekstrakurikuler Ini</a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <span>{{ $extracurricular->name }}</span>
                    <span class="badge" data-status="{{ $extracurricular->is_active ? 'active' : 'inactive' }}">{{ $extracurricular->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="data-point h-100">
                                <div class="data-point-label">Pembina</div>
                                <p class="data-point-value mb-0"><strong>{{ $extracurricular->coach_names }}</strong></p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="data-point h-100">
                                <div class="data-point-label">Prestasi kegiatan ekstrakurikuler</div>
                                <div class="info-list mt-3">
                                    @forelse($extracurricular->assessments as $item)
                                        <div class="info-item">
                                            <div class="title">{{ $item->title }}</div>
                                            @if($item->description)
                                                <div class="small text-muted mt-1">{{ $item->description }}</div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="empty-state py-3">
                                            <div class="icon"><i class="bi bi-award"></i></div>
                                            <p class="mb-0">Belum ada data prestasi kegiatan.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="data-point h-100">
                                <div class="data-point-label">Jadwal kegiatan</div>
                                <div class="info-list mt-3">
                                    @forelse($extracurricular->schedules as $schedule)
                                        <div class="info-item">
                                            <div class="title">{{ $schedule->title }}</div>
                                            <div class="small text-muted mt-1">{{ optional($schedule->activity_date)->format('d-m-Y') }} | {{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }}</div>
                                            <div class="small text-muted">{{ $schedule->location ?: 'Lokasi belum ditentukan' }}</div>
                                        </div>
                                    @empty
                                        <div class="empty-state py-3">
                                            <div class="icon"><i class="bi bi-calendar3"></i></div>
                                            <p class="mb-0">Belum ada jadwal latihan yang ditampilkan.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4" id="form-pendaftaran">
            <div class="card h-100">
                <div class="card-header">Form Pendaftaran</div>
                <div class="card-body">
                    @if($registration)
                        <div class="info-banner mb-3">
                            <i class="bi bi-clipboard-check"></i>
                            <div>
                                <strong class="d-block mb-1">Pendaftaran sudah dikirim</strong>
                                Status saat ini: <span class="badge" data-status="{{ $registration->status }}">{{ $registration->status }}</span>
                            </div>
                        </div>
                        <div class="form-section-card">
                            <h3 class="form-section-title">Catatan admin atau pembina</h3>
                            <p class="mb-0">{{ $registration->notes ?: 'Belum ada catatan tambahan.' }}</p>
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-section-card">
                                    <h3 class="form-section-title">1. Data siswa</h3>
                                    <p class="form-section-copy">Data akunmu akan digunakan secara otomatis saat pendaftaran dikirim.</p>
                                    <div class="helper-text">Periksa kembali profil jika ada data yang belum sesuai.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-section-card">
                                    <h3 class="form-section-title">2. Pilihan ekstrakurikuler</h3>
                                    <p class="form-section-copy mb-0">{{ $extracurricular->name }}</p>
                                </div>
                            </div>
                        </div>

                        <form method="post" action="{{ route('student.registrations.store', $extracurricular) }}" class="mt-3">
                            @csrf
                            <div class="form-section-card mb-3">
                                <h3 class="form-section-title">3. Alasan atau minat mengikuti ekskul</h3>
                                <p class="form-section-copy">Tuliskan singkat alasan kamu tertarik mengikuti kegiatan ini.</p>
                                <label class="form-label" for="notes">Catatan pendaftaran</label>
                                <textarea id="notes" name="notes" class="form-control" rows="5" placeholder="Contoh: Saya tertarik mengembangkan kemampuan dan siap mengikuti latihan rutin.">{{ old('notes') }}</textarea>
                                <div class="helper-text">Boleh dikosongkan jika tidak ada catatan tambahan.</div>
                            </div>

                            <div class="form-section-card mb-3">
                                <h3 class="form-section-title">4. Konfirmasi pendaftaran</h3>
                                <p class="form-section-copy mb-0">Setelah dikirim, pendaftaran akan menunggu konfirmasi dari pembina atau admin.</p>
                            </div>

                            <div class="form-actions">
                                <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-send-check"></i>Kirim Pendaftaran</button>
                                <a href="{{ route('student.extracurriculars.index') }}" class="btn btn-outline-secondary flex-fill"><i class="bi bi-arrow-left"></i>Kembali ke Daftar Ekskul</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
