@php
    $firstSchedule = $activity->schedules->first();
    $catalogName = $activity->catalog_item_name;
    $groupLabel = $activity->catalog_group_label;
    $normalizedName = \Illuminate\Support\Str::lower(trim($catalogName));
    $visualMap = [
        'pramuka' => ['icon' => 'bi-tree', 'label' => 'Kegiatan lapangan'],
        'paskibra' => ['icon' => 'bi-flag', 'label' => 'Latihan disiplin'],
        'pbb/paskib' => ['icon' => 'bi-flag', 'label' => 'Latihan disiplin'],
        'pmr' => ['icon' => 'bi-heart-pulse', 'label' => 'Kegiatan sosial'],
        'basket' => ['icon' => 'bi-dribbble', 'label' => 'Latihan olahraga'],
        'basketball' => ['icon' => 'bi-dribbble', 'label' => 'Latihan olahraga'],
        'futsal / sepak bola' => ['icon' => 'bi-trophy', 'label' => 'Cabang olahraga'],
        'futsal' => ['icon' => 'bi-trophy', 'label' => 'Latihan olahraga'],
        'rohis' => ['icon' => 'bi-moon-stars', 'label' => 'Pembinaan rohani'],
        'matematika' => ['icon' => 'bi-calculator', 'label' => 'Bidang akademik'],
        'informatika' => ['icon' => 'bi-cpu', 'label' => 'Bidang teknologi'],
        'geografi' => ['icon' => 'bi-globe-asia-australia', 'label' => 'Bidang sains'],
        'ekonomi' => ['icon' => 'bi-graph-up-arrow', 'label' => 'Bidang akademik'],
        'renang' => ['icon' => 'bi-water', 'label' => 'Cabang olahraga'],
        'badminton' => ['icon' => 'bi-trophy', 'label' => 'Cabang olahraga'],
        'silat' => ['icon' => 'bi-shield', 'label' => 'Cabang olahraga'],
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
    $visual = $visualMap[$normalizedName] ?? ['icon' => 'bi-stars', 'label' => $groupLabel];
    $coachText = $activity->coach_names ?: 'Belum tersedia';
    $scheduleText = $activity->schedule_overview
        ?: ($firstSchedule ? $firstSchedule->title.' - '.(optional($firstSchedule->activity_date)->format('d-m-Y') ?: '-') : 'Jadwal belum ditentukan');
    $description = \Illuminate\Support\Str::of($activity->description ?: 'Belum ada deskripsi singkat untuk kegiatan ini.')
        ->trim()
        ->toString();
    $groupKey = \Illuminate\Support\Str::lower($groupLabel);
    $cardTone = match (true) {
        $groupKey === 'osn' => 'is-osn',
        $groupKey === 'o2sn' => 'is-o2sn',
        default => 'is-extracurricular',
    };
    $registration = $activity->current_registration ?? null;
    $user = auth()->user();
    $isStudent = $user?->hasRole(\App\Models\User::ROLE_STUDENT) ?? false;
    $actionLabel = 'Daftar';
    $actionClass = 'btn-primary';
    $actionHref = route('public.extracurriculars.register', $activity);
    $actionDisabled = false;

    if (!$activity->is_active) {
        $actionLabel = 'Pendaftaran Ditutup';
        $actionClass = 'btn-outline-secondary';
        $actionDisabled = true;
        $actionHref = null;
    } elseif ($isStudent && $registration) {
        if ($registration->status === \App\Models\Registration::STATUS_PENDING) {
            $actionLabel = 'Lihat Status';
            $actionClass = 'btn-outline-warning';
            $actionHref = route('student.registrations.index');
        } elseif ($registration->status === \App\Models\Registration::STATUS_APPROVED) {
            $actionLabel = 'Sudah Terdaftar';
            $actionClass = 'btn-outline-success';
            $actionDisabled = true;
            $actionHref = null;
        } else {
            $actionLabel = 'Daftar';
            $actionClass = 'btn-primary';
            $actionHref = route('student.extracurriculars.register', $activity);
        }
    } elseif ($isStudent) {
        $actionHref = route('student.extracurriculars.register', $activity);
    } elseif ($user && !$isStudent) {
        $actionLabel = 'Buka Dashboard';
        $actionClass = 'btn-outline-primary';
        $actionHref = route('dashboard');
    }
@endphp

<article class="public-activity-card {{ $cardTone }}">
    <div class="public-activity-card-media">
        @if(!empty($activity->preview_image))
            <img src="{{ $activity->preview_image }}" alt="{{ $catalogName }}" class="public-activity-card-image" width="640" height="360" loading="lazy" decoding="async">
        @else
            <div class="public-activity-card-fallback" aria-hidden="true">
                <div class="public-activity-card-fallback-inner">
                    <span class="public-activity-card-icon"><i class="bi {{ $visual['icon'] }}"></i></span>
                    <div>
                        <span class="public-activity-card-kicker">{{ $groupLabel }}</span>
                        <span class="public-activity-card-fallback-title">{{ $catalogName }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="public-activity-card-body">
        <div class="public-activity-card-category">{{ $groupLabel }}</div>
        <div class="public-activity-card-meta">
            <span class="{{ $activity->is_active ? 'is-open' : 'is-closed' }}"><i class="bi bi-circle-fill"></i>{{ $activity->is_active ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup' }}</span>
        </div>
        <h3 class="public-activity-card-title">{{ $catalogName }}</h3>
        <p class="public-activity-card-description">{{ $description }}</p>
        <div class="public-activity-card-info">
            <div><i class="bi bi-person-workspace"></i><span>{{ $coachText }}</span></div>
            <div><i class="bi bi-calendar3"></i><span>{{ $scheduleText }}</span></div>
        </div>
        <div class="public-activity-card-actions">
            <a href="{{ route('public.extracurriculars.show', $activity) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i>Lihat Detail</a>
            @if($actionDisabled)
                <button type="button" class="btn {{ $actionClass }}" disabled><i class="bi bi-lock"></i>{{ $actionLabel }}</button>
            @else
                <a href="{{ $actionHref }}" class="btn {{ $actionClass }}"><i class="bi {{ $actionLabel === 'Lihat Status' ? 'bi-clipboard-check' : ($actionLabel === 'Buka Dashboard' ? 'bi-arrow-right-circle' : 'bi-send-check') }}"></i>{{ $actionLabel }}</a>
            @endif
        </div>
    </div>
</article>
