@extends('layouts.public')

@section('title', 'Detail ' . $extracurricular->category_label . ' | ' . $extracurricular->name)

@push('styles')
    <style>
        .detail-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.92rem;
            color: #5b6f88;
        }

        .detail-breadcrumb a {
            color: #355987;
            text-decoration: none;
        }

        .detail-shell,
        .detail-section,
        .detail-cta-card {
            border-radius: 28px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(247, 250, 255, 0.96));
            box-shadow: 0 18px 30px rgba(16, 35, 63, 0.06);
        }

        .detail-shell {
            padding: 1.2rem;
        }

        .detail-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(280px, 0.8fr);
            gap: 1.15rem;
            align-items: center;
        }

        .detail-hero-copy {
            min-width: 0;
        }

        .detail-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            margin: 0.85rem 0 1rem;
        }

        .detail-summary {
            display: grid;
            gap: 0.8rem;
        }

        .detail-summary-block {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.55rem;
            border-radius: 20px;
            border: 1px solid #e1ebf5;
            background: #fbfdff;
            padding: 0.95rem 1rem;
        }

        .detail-summary-label,
        .detail-meta-label {
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #637b98;
        }

        .detail-coach-list,
        .detail-keyfacts,
        .detail-schedule-grid,
        .detail-achievement-list,
        .detail-announcement-grid {
            display: grid;
            gap: 0.85rem;
        }

        .detail-coach-list {
            margin: 0;
        }

        .detail-coach-item {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }

        .detail-coach-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
            color: #23446f;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
        }

        .detail-cover-wrap,
        .detail-cover-fallback {
            border-radius: 22px;
            overflow: hidden;
            background: linear-gradient(135deg, #dfeeff 0%, #eef5ff 55%, #d9e9ff 100%);
            border: 1px solid #dbe5f0;
            min-height: 280px;
        }

        .detail-cover-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            aspect-ratio: 4 / 3;
        }

        .detail-cover-fallback {
            position: relative;
            display: flex;
            align-items: flex-end;
            padding: 1.2rem;
        }

        .detail-cover-fallback::before,
        .detail-cover-fallback::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            background: rgba(79, 124, 196, 0.1);
        }

        .detail-cover-fallback::before {
            width: 160px;
            height: 160px;
            top: -40px;
            right: -32px;
        }

        .detail-cover-fallback::after {
            width: 120px;
            height: 120px;
            left: -18px;
            bottom: -36px;
        }

        .detail-cover-fallback-body {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 0.95rem;
        }

        .detail-cover-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.82);
            color: #355987;
            font-size: 1.7rem;
            flex-shrink: 0;
        }

        .detail-section {
            padding: 1.15rem;
            height: 100%;
        }

        .detail-keyfacts {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .detail-keyfact,
        .detail-schedule-card,
        .detail-achievement-card,
        .detail-announcement-card,
        .detail-empty,
        .detail-process-card {
            border-radius: 20px;
            border: 1px solid #e1ebf5;
            background: #fbfdff;
            padding: 1rem;
        }

        .detail-keyfact {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .detail-keyfact-icon {
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #edf4ff;
            color: #355987;
            flex-shrink: 0;
        }

        .detail-requirements {
            display: grid;
            gap: 0.7rem;
            margin: 0;
            padding-left: 1.1rem;
        }

        .detail-process-card {
            margin-top: 1rem;
            background: linear-gradient(135deg, #f8fbff 0%, #eef5ff 100%);
        }

        .detail-schedule-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .detail-schedule-meta,
        .detail-achievement-meta,
        .detail-announcement-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem 0.9rem;
            color: #607389;
            font-size: 0.92rem;
        }

        .detail-schedule-meta span,
        .detail-achievement-meta span,
        .detail-announcement-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .detail-cta-card {
            padding: 1.15rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(245, 249, 255, 0.97));
            color: #173556;
        }

        .detail-cta-card p {
            color: #58708f;
        }

        .detail-cta-card .section-kicker {
            margin-bottom: 0.5rem;
        }

        .detail-cta-summary {
            display: grid;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .detail-cta-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.85rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e1ebf5;
        }

        .detail-cta-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .detail-cta-label {
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #6b819c;
        }

        .detail-cta-value {
            text-align: right;
            color: #173556;
            font-weight: 600;
        }

        .detail-cta-note {
            border-radius: 18px;
            border: 1px solid #dbe7f4;
            background: #f7faff;
            padding: 0.9rem 1rem;
            color: #4f6785;
            margin-bottom: 1rem;
        }

        .detail-cta-actions {
            display: grid;
            gap: 0.75rem;
        }

        .detail-cta-actions .btn {
            width: 100%;
        }

        .detail-cta-card.is-inline {
            margin-top: 1rem;
        }

        .detail-cta-card.is-inline .detail-cta-actions {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .detail-empty {
            color: #61748c;
            min-height: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            .detail-shell,
            .detail-section,
            .detail-cta-card {
                scroll-behavior: auto;
            }
        }

        @media (max-width: 991.98px) {
            .detail-hero-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .detail-shell,
            .detail-section,
            .detail-cta-card {
                padding: 1rem;
            }

            .detail-cover-wrap,
            .detail-cover-fallback {
                min-height: 220px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container py-3 py-md-4">
        @php
            $cta = $detailPage['cta'];
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
                'opsi' => ['icon' => 'bi-lightbulb', 'label' => 'Kegiatan akademik'],
                'osis / mpk' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                'pelsis' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                'smag' => ['icon' => 'bi-people', 'label' => 'Kegiatan organisasi'],
                'fortina' => ['icon' => 'bi-megaphone', 'label' => 'Kegiatan komunikasi'],
            ];
            $visual = $visualMap[$normalizedName] ?? ['icon' => 'bi-stars', 'label' => 'Kegiatan siswa'];
        @endphp

        <nav class="detail-breadcrumb" aria-label="Breadcrumb">
            @foreach($detailPage['breadcrumbs'] as $crumb)
                @if($crumb['url'])
                    <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    <span>/</span>
                @else
                    <span aria-current="page">{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </nav>

        <div class="split-actions mb-3">
            <a href="{{ $backToActivitiesUrl ?? route('public.activities.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke Kategori</a>
            <a href="{{ route('public.information') }}" class="btn btn-outline-primary"><i class="bi bi-signpost-2"></i>Lihat Alur Pendaftaran</a>
        </div>

        <section class="detail-shell mb-3">
            <div class="detail-hero-grid">
                <div class="detail-hero-copy">
                    <span class="section-kicker"><i class="bi bi-grid-1x2"></i>{{ $extracurricular->category_label }}</span>
                    <h1 class="section-title mb-2">{{ $extracurricular->name }}</h1>
                    <p class="section-subtitle mb-0">{{ $detailPage['short_description'] }}</p>

                    <div class="detail-badges">
                        <span class="badge {{ $detailPage['activity_badge']['class'] }}">{{ $detailPage['activity_badge']['label'] }}</span>
                        <span class="badge {{ $detailPage['registration_badge']['class'] }}">{{ $detailPage['registration_badge']['label'] }}</span>
                        @if($detailPage['student_badge'])
                            <span class="badge {{ $detailPage['student_badge']['class'] }}">{{ $detailPage['student_badge']['label'] }}</span>
                        @endif
                    </div>

                    <div class="detail-summary">
                        <div class="detail-summary-block">
                            <span class="detail-summary-label">Pembina Kegiatan</span>
                            @if($detailPage['visible_coaches']->isNotEmpty())
                                <div class="detail-coach-list">
                                    @foreach($detailPage['visible_coaches'] as $coach)
                                        <div class="detail-coach-item">
                                            <span class="detail-coach-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($coach['name'], 0, 1)) }}</span>
                                            <span>{{ $coach['name'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                @if($detailPage['remaining_coach_count'] > 0)
                                    <details>
                                        <summary>+{{ $detailPage['remaining_coach_count'] }} pembina lainnya</summary>
                                        <div class="detail-coach-list mt-2">
                                            @foreach($detailPage['coach_items']->slice($detailPage['visible_coaches']->count()) as $coach)
                                                <div class="detail-coach-item">
                                                    <span class="detail-coach-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($coach['name'], 0, 1)) }}</span>
                                                    <span>{{ $coach['name'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                            @else
                                <p class="mb-0">Pembina belum ditentukan.</p>
                            @endif
                        </div>
                        <div class="detail-summary-block">
                            <span class="detail-summary-label">Lokasi</span>
                            <p class="mb-0">{{ $detailPage['primary_location'] }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="detail-cover-wrap">
                        @if(!empty($extracurricular->image_path))
                            <img src="{{ $extracurricular->preview_image }}" alt="{{ $extracurricular->name }}" width="800" height="600" decoding="async" fetchpriority="high">
                        @else
                            <div class="detail-cover-fallback">
                                <div class="detail-cover-fallback-body">
                                    <span class="detail-cover-icon" aria-hidden="true"><i class="bi {{ $visual['icon'] }}"></i></span>
                                    <div>
                                        <span class="detail-summary-label d-block mb-1">{{ $visual['label'] }}</span>
                                        <strong>{{ $extracurricular->name }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-3">
            <div class="col-12">
                <div class="row g-3">
                    <div class="col-12">
                        <section class="detail-section">
                            <span class="section-kicker"><i class="bi bi-info-circle"></i>Tentang Kegiatan</span>
                            <h2 class="h5 mb-2">Ringkasan kegiatan</h2>
                            <p class="mb-0">{{ $extracurricular->description ?: 'Informasi kegiatan ini akan diperbarui oleh sekolah.' }}</p>
                        </section>
                    </div>

                    <div class="col-12">
                        <section class="detail-section">
                            <span class="section-kicker"><i class="bi bi-card-checklist"></i>Informasi Pendaftaran</span>
                            <h2 class="h5 mb-3">Informasi utama</h2>
                            <div class="detail-keyfacts">
                                @foreach($detailPage['information_rows'] as $row)
                                    <div class="detail-keyfact">
                                        <span class="detail-keyfact-icon" aria-hidden="true"><i class="bi {{ $row['icon'] }}"></i></span>
                                        <div>
                                            <div class="detail-meta-label">{{ $row['label'] }}</div>
                                            <div>{{ $row['value'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="detail-process-card">
                                <div class="detail-meta-label mb-1">Petunjuk Proses</div>
                                <p class="mb-0">Lengkapi pendaftaran melalui akun siswa. Verifikasi status, seleksi, dan penerimaan tetap mengikuti alur yang dikelola pembina atau admin.</p>
                            </div>
                            <div class="detail-cta-card is-inline">
                                <span class="section-kicker"><i class="bi bi-send-check"></i>Langkah Berikutnya</span>
                                <h2 class="h5 mb-2">Status pendaftaran kegiatan</h2>
                                <p class="mb-3">{{ $cta['description'] }}</p>

                                <div class="detail-cta-summary">
                                    <div class="detail-cta-row">
                                        <div>
                                            <div class="detail-cta-label">Pendaftaran</div>
                                            <div class="small text-muted">Status saat ini</div>
                                        </div>
                                        <div class="detail-cta-value">{{ $detailPage['registration_badge']['label'] }}</div>
                                    </div>
                                    <div class="detail-cta-row">
                                        <div>
                                            <div class="detail-cta-label">Kuota</div>
                                            <div class="small text-muted">Ketersediaan peserta</div>
                                        </div>
                                        <div class="detail-cta-value">{{ $detailPage['quota_text'] }}</div>
                                    </div>
                                    @if($detailPage['student_badge'])
                                        <div class="detail-cta-row">
                                            <div>
                                                <div class="detail-cta-label">Status Saya</div>
                                                <div class="small text-muted">Untuk akun yang sedang masuk</div>
                                            </div>
                                            <div class="detail-cta-value">{{ $detailPage['student_badge']['label'] }}</div>
                                        </div>
                                    @endif
                                </div>

                                @if($cta['status_note'])
                                    <div class="detail-cta-note">{{ $cta['status_note'] }}</div>
                                @endif

                                <div class="detail-cta-actions">
                                    @if($cta['primary'])
                                        @if(!empty($cta['primary']['disabled']))
                                            <button type="button" class="btn btn-{{ $cta['primary']['variant'] }}" disabled><i class="bi {{ $cta['primary']['icon'] }}"></i>{{ $cta['primary']['label'] }}</button>
                                        @else
                                            <a href="{{ $cta['primary']['href'] }}" class="btn btn-{{ $cta['primary']['variant'] }}"><i class="bi {{ $cta['primary']['icon'] }}"></i>{{ $cta['primary']['label'] }}</a>
                                        @endif
                                    @endif

                                    @if($cta['secondary'])
                                        <a href="{{ $cta['secondary']['href'] }}" class="btn btn-{{ $cta['secondary']['variant'] }}">
                                            <i class="bi {{ $cta['secondary']['icon'] ?? 'bi-signpost-2' }}"></i>{{ $cta['secondary']['label'] }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
