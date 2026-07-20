<div class="info-list">
    @if($registration->selected_branch)
        <div class="info-item">
            <div class="title">Cabang yang dipilih</div>
            <div class="small mt-2">{{ $registration->selected_branch }}</div>
        </div>
    @endif
    <div class="info-item">
        <div class="title">Alasan memilih ekstrakurikuler</div>
        <div class="small mt-2">{{ $registration->motivation_reason ?: '-' }}</div>
    </div>
    <div class="info-item">
        <div class="title">Tujuan mengikuti ekstrakurikuler</div>
        <div class="small mt-2">{{ $registration->goal_statement ?: '-' }}</div>
    </div>
    <div class="info-item">
        <div class="title">Profil kemampuan awal</div>
        <div class="small mt-2">Bakat utama: {{ $registration->primary_talent ?: '-' }}</div>
        <div class="small mt-1">Posisi/peran yang diminati: {{ $registration->preferred_position ?: '-' }}</div>
        <div class="small mt-1">Kemampuan yang sudah dimiliki: {{ $registration->current_skills ?: '-' }}</div>
        <div class="small mt-1">Pengalaman: {{ $registration->prior_experience ?: '-' }}</div>
    </div>
    <div class="info-item">
        <div class="title">Prestasi dan catatan tambahan</div>
        <div class="small mt-2">Prestasi: {{ $registration->achievement_history ?: '-' }}</div>
        <div class="small mt-1">Catatan tambahan: {{ $registration->student_notes ?: '-' }}</div>
        <div class="small mt-1">Bersedia ikut tes: {{ $registration->willing_to_take_test ? 'Ya' : 'Tidak' }}</div>
        <div class="small mt-1">Izin profil publik: {{ $registration->allow_public_profile ? 'Ya' : 'Tidak' }}</div>
        @if($registration->achievement_proof_path)
            <div class="small mt-2"><a href="{{ route('registrations.achievement-proof', $registration) }}" target="_blank" rel="noopener">Lihat bukti prestasi</a></div>
        @endif
    </div>
</div>
