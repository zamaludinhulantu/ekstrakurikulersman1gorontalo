@extends('layouts.public')

@section('title', 'Detail Ekstrakurikuler | ' . $extracurricular->name)

@push('styles')
    <style>
        .detail-hero {
            border-radius: 32px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(237, 245, 255, 0.96) 100%);
            border: 1px solid #d8e4f2;
            padding: 1.3rem;
            margin: 1.25rem 0 1.5rem;
            box-shadow: 0 24px 40px rgba(16, 35, 63, 0.08);
        }

        .detail-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 22px;
        }

        .detail-cover-frame {
            background: linear-gradient(135deg, #eef5ff 0%, #f8fbff 100%);
            border: 1px solid #dbe5f0;
            border-radius: 28px;
            padding: 0.85rem;
            box-shadow: 0 18px 30px rgba(16, 35, 63, 0.08);
        }

        .detail-cover-box {
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border-radius: 22px;
            background: #f4f8ff;
        }

        .detail-default-visual {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            background: linear-gradient(135deg, #dfeeff 0%, #eef5ff 50%, #d9e9ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .detail-default-visual::before,
        .detail-default-visual::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(79, 124, 196, 0.1);
        }

        .detail-default-visual::before {
            width: 150px;
            height: 150px;
            top: -46px;
            right: -28px;
        }

        .detail-default-visual::after {
            width: 120px;
            height: 120px;
            bottom: -36px;
            left: -22px;
        }

        .detail-default-visual-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
            width: 100%;
            padding: 0 1.25rem;
        }

        .detail-default-visual-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.76);
            color: #355987;
            font-size: 1.7rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
            flex-shrink: 0;
        }

        .detail-default-visual-label {
            display: block;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 800;
            color: #5d789a;
            margin-bottom: 0.25rem;
        }

        .detail-default-visual-title {
            display: block;
            color: #23446f;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .detail-summary-list {
            display: grid;
            gap: 0.9rem;
        }

        .detail-cta-card {
            border-radius: 28px;
            background: linear-gradient(135deg, #0f2f57 0%, #1f5eff 100%);
            color: #fff;
            padding: 1.2rem;
            box-shadow: 0 24px 38px rgba(31, 94, 255, 0.18);
        }

        .detail-cta-card p {
            color: rgba(239, 246, 255, 0.88);
        }

        @media (max-width: 991.98px) {
            .detail-cover-frame {
                max-width: 560px;
                margin: 0 auto;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        @php
            $user = auth()->user();
            $isStudent = $user?->hasRole(\App\Models\User::ROLE_STUDENT) ?? false;
            $firstSchedule = $extracurricular->schedules->first();
            $normalizedName = \Illuminate\Support\Str::lower(trim($extracurricular->name));
            $visualMap = [
                'pramuka' => ['icon' => 'bi-tree', 'label' => 'Kegiatan lapangan'],
                'paskibra' => ['icon' => 'bi-flag', 'label' => 'Latihan disiplin'],
                'pbb/paskib' => ['icon' => 'bi-flag', 'label' => 'Latihan disiplin'],
                'pmr' => ['icon' => 'bi-heart-pulse', 'label' => 'Kegiatan sosial'],
                'basket' => ['icon' => 'bi-dribbble', 'label' => 'Latihan olahraga'],
                'basketball' => ['icon' => 'bi-dribbble', 'label' => 'Latihan olahraga'],
                'futsal' => ['icon' => 'bi-trophy', 'label' => 'Latihan olahraga'],
                'rohis' => ['icon' => 'bi-moon-stars', 'label' => 'Pembinaan rohani'],
                "tilawatil qur'an" => ['icon' => 'bi-book', 'label' => 'Pembinaan keagamaan'],
                "tartil dan hifzil qur'an" => ['icon' => 'bi-book', 'label' => 'Pembinaan keagamaan'],
                'konten kreator' => ['icon' => 'bi-camera-video', 'label' => 'Kegiatan media'],
                'menulis artikel' => ['icon' => 'bi-pencil-square', 'label' => 'Kegiatan literasi'],
                'opsis' => ['icon' => 'bi-lightbulb', 'label' => 'Kegiatan akademik'],
                'osis / mpk' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                'pelsis' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                'smag' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                'fortina' => ['icon' => 'bi-megaphone', 'label' => 'Kegiatan komunikasi'],
            ];
            $visual = $visualMap[$normalizedName] ?? ['icon' => 'bi-stars', 'label' => 'Kegiatan siswa'];
        @endphp

        <div class="split-actions mb-3">
            <a href="{{ route('landing') }}#daftar-ekskul" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke Daftar Ekskul</a>
            <a href="{{ route('public.information') }}" class="btn btn-outline-primary"><i class="bi bi-signpost-2"></i>Lihat Alur Pendaftaran</a>
        </div>

        <section class="detail-hero">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <span class="section-kicker"><i class="bi bi-grid-1x2"></i>Detail Ekstrakurikuler</span>
                    <h1 class="section-title">{{ $extracurricular->name }}</h1>
                    <p class="section-subtitle mb-3">Baca informasi berikut terlebih dahulu agar kamu yakin sebelum mengirim pendaftaran.</p>

                    <div class="detail-summary-list">
                        <div class="dashboard-highlight">
                            <span class="dashboard-highlight-icon"><i class="bi bi-person-workspace"></i></span>
                            <div class="dashboard-highlight-copy">
                                <h3>Pembina</h3>
                                <p>{{ $extracurricular->coach_names }}</p>
                            </div>
                        </div>
                        <div class="dashboard-highlight">
                            <span class="dashboard-highlight-icon"><i class="bi bi-calendar3"></i></span>
                            <div class="dashboard-highlight-copy">
                                <h3>Deskripsi dan jadwal</h3>
                                <p class="mb-2">{{ $extracurricular->description }}</p>
                                <p>
                                    @if($extracurricular->schedule_overview)
                                        {{ $extracurricular->schedule_overview }}
                                    @elseif($firstSchedule)
                                        {{ $firstSchedule->title }} - {{ optional($firstSchedule->activity_date)->format('d-m-Y') }} di {{ $firstSchedule->location }}
                                    @else
                                        Jadwal latihan belum tersedia.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="detail-cover-frame">
                        <div class="detail-cover-box">
                            @if(!empty($extracurricular->image_path))
                                <img src="{{ $extracurricular->preview_image }}" alt="{{ $extracurricular->name }}" class="detail-cover" decoding="async" fetchpriority="high">
                            @else
                                <div class="detail-default-visual" aria-hidden="true">
                                    <div class="detail-default-visual-inner">
                                        <span class="detail-default-visual-icon"><i class="bi {{ $visual['icon'] }}"></i></span>
                                        <div>
                                            <span class="detail-default-visual-label">{{ $visual['label'] }}</span>
                                            <span class="detail-default-visual-title">{{ $extracurricular->name }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-3 mb-3">
            <div class="col-lg-12">
                <div class="detail-panel">
                    <span class="section-kicker"><i class="bi bi-award"></i>Prestasi</span>
                    <h3>Prestasi atau kegiatan unggulan</h3>
                    <p class="mb-0">{{ $extracurricular->achievements_overview ?: 'Informasi prestasi dan dokumentasi belum tersedia.' }}</p>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header">Jadwal Latihan</div>
                    <div class="card-body">
                        @if($extracurricular->schedules->isNotEmpty())
                            <div class="desktop-table table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                    <tr>
                                        <th>Kegiatan</th>
                                        <th>Tanggal</th>
                                        <th>Jam</th>
                                        <th>Lokasi</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($extracurricular->schedules as $schedule)
                                        <tr>
                                            <td>{{ $schedule->title }}</td>
                                            <td>{{ optional($schedule->activity_date)->format('d-m-Y') }}</td>
                                            <td>{{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }}</td>
                                            <td>{{ $schedule->location ?: 'Belum ditentukan' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mobile-stack-table">
                                @foreach($extracurricular->schedules as $schedule)
                                    <div class="mobile-data-card">
                                        <div class="mobile-data-card-header">
                                            <h3 class="mobile-data-card-title">{{ $schedule->title }}</h3>
                                        </div>
                                        <div class="mobile-data-list">
                                            <div><span class="mobile-data-item-label">Tanggal</span><p class="mobile-data-item-value">{{ optional($schedule->activity_date)->format('d-m-Y') }}</p></div>
                                            <div><span class="mobile-data-item-label">Jam</span><p class="mobile-data-item-value">{{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} - {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }}</p></div>
                                            <div><span class="mobile-data-item-label">Lokasi</span><p class="mobile-data-item-value">{{ $schedule->location ?: 'Belum ditentukan' }}</p></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state py-3">
                                <div class="icon"><i class="bi bi-calendar3"></i></div>
                                <p class="mb-0">Jadwal latihan belum tersedia.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="detail-cta-card h-100">
                    <div class="small text-white-50 mb-2">Langkah berikutnya</div>
                    <h3 class="h5">Siap mendaftar ke ekskul ini?</h3>
                    <p class="mb-3">Pastikan data diri kamu sudah benar sebelum mendaftar. Setelah dikirim, pendaftaran akan menunggu konfirmasi dari pembina atau admin.</p>
                    <div class="d-grid gap-2">
                        @if(!$user)
                            <a href="{{ route('public.extracurriculars.register', $extracurricular) }}" class="btn btn-light text-primary"><i class="bi bi-send-check"></i>Daftar Ekstrakurikuler Ini</a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light"><i class="bi bi-box-arrow-in-right"></i>Masuk sebagai Siswa</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-light"><i class="bi bi-person-plus"></i>Buat Akun Siswa</a>
                        @elseif($isStudent)
                            <a href="{{ route('student.extracurriculars.show', $extracurricular) }}" class="btn btn-light text-primary"><i class="bi bi-send-check"></i>Daftar Ekstrakurikuler Ini</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn btn-light text-primary"><i class="bi bi-arrow-right-circle"></i>Kembali ke Dashboard</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
