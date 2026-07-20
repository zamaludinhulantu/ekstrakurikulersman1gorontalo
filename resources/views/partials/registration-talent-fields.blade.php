@php
    $formExtracurricular = $extracurricular ?? $registration?->extracurricular;
    $positionKeywords = ['futsal', 'basket', 'voli', 'sepak bola', 'paskibra', 'pmr', 'band', 'paduan suara', 'tari', 'drumband'];
    $positionText = \Illuminate\Support\Str::lower(($formExtracurricular->name ?? '').' '.($formExtracurricular->category ?? '').' '.($formExtracurricular->type ?? ''));
    $showPreferredPosition = collect($positionKeywords)->contains(fn ($keyword) => str_contains($positionText, $keyword));
    $motivationValue = old('motivation_reason', $registration->motivation_reason ?? $registration->goal_statement ?? '');
    $experienceValue = old('current_skills', $registration->current_skills ?? $registration->prior_experience ?? $registration->primary_talent ?? '');
    $allowPublicProfile = old('allow_public_profile', $registration->allow_public_profile ?? false);
    $branchOptions = collect($formExtracurricular?->branch_options ?? [])->filter()->values();
@endphp

<input type="hidden" name="goal_statement" value="{{ old('goal_statement', $registration->goal_statement ?? $motivationValue) }}">
<input type="hidden" name="prior_experience" value="{{ old('prior_experience', $registration->prior_experience ?? $experienceValue) }}">
<input type="hidden" name="primary_talent" value="{{ old('primary_talent', $registration->primary_talent ?? '') }}">
<input type="hidden" name="allow_public_profile" value="{{ $allowPublicProfile ? '1' : '0' }}">

<div class="row g-3">
    <div class="col-12">
        <div class="form-section-card">
            <h3 class="form-section-title">2. Minat dan kemampuan opsional</h3>
            <p class="form-section-copy">Isi jika ingin memberi gambaran tambahan kepada pembina tentang alasan mendaftar dan kemampuan awalmu.</p>
            <div class="row g-3">
                @if($branchOptions->isNotEmpty())
                    <div class="col-md-6">
                        <label class="form-label" for="selected_branch">Pilih cabang kegiatan</label>
                        <select id="selected_branch" name="selected_branch" class="form-select" required>
                            <option value="">- Pilih cabang -</option>
                            @foreach($branchOptions as $branch)
                                <option value="{{ $branch }}" @selected(old('selected_branch', $registration->selected_branch ?? '') === $branch)>{{ $branch }}</option>
                            @endforeach
                        </select>
                        <div class="helper-text">Pilih cabang yang ingin kamu ikuti pada program ini.</div>
                        @error('selected_branch')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                @endif
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <label class="form-label mb-0" for="motivation_reason">Mengapa kamu ingin mengikuti ekstrakurikuler ini? <span class="helper-text">(Opsional)</span></label>
                        <span class="helper-text"><span id="motivationCount">{{ \Illuminate\Support\Str::length($motivationValue) }}</span>/280</span>
                    </div>
                    <textarea id="motivation_reason" name="motivation_reason" class="form-control registration-textarea" rows="4" maxlength="280" placeholder="Ceritakan singkat alasan dan tujuanmu mengikuti ekstrakurikuler ini.">{{ $motivationValue }}</textarea>
                </div>
                @if($showPreferredPosition)
                    <div class="col-md-6">
                        <label class="form-label" for="preferred_position">Posisi atau peran yang diminati</label>
                        <input id="preferred_position" name="preferred_position" type="text" class="form-control" value="{{ old('preferred_position', $registration->preferred_position ?? '') }}" placeholder="Opsional">
                    </div>
                @endif
                <div class="col-12">
                    <label class="form-label" for="current_skills">Pengalaman atau kemampuan yang dimiliki <span class="helper-text">(Opsional)</span></label>
                    <textarea id="current_skills" name="current_skills" class="form-control registration-textarea" rows="3" placeholder="Contoh: Pernah mengikuti latihan dasar dan memahami teknik dasar.">{{ $experienceValue }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="student_notes">Catatan tambahan <span class="helper-text">(Opsional)</span></label>
                    <textarea id="student_notes" name="student_notes" class="form-control" rows="2" placeholder="Opsional, misalnya kebutuhan jadwal atau hal penting lain.">{{ old('student_notes', $registration->student_notes ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" value="1" id="willing_to_take_test" name="willing_to_take_test" @checked(old('willing_to_take_test', $registration->willing_to_take_test ?? false))>
                        <label class="form-check-label" for="willing_to_take_test">Saya bersedia mengikuti tes apabila dijadwalkan pembina. <span class="helper-text">(Opsional)</span></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-section-card">
            <details class="registration-accordion" @open((bool) old('achievement_history') || $errors->has('achievement_proof'))>
                <summary>
                    <span>
                        <strong>3. Prestasi opsional</strong>
                        <small>Punya prestasi yang relevan? Tambahkan di sini.</small>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </summary>
                <div class="registration-accordion-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="achievement_history">Prestasi yang pernah diraih</label>
                            <textarea id="achievement_history" name="achievement_history" class="form-control registration-textarea" rows="3" placeholder="Opsional. Tambahkan prestasi yang relevan jika ada.">{{ old('achievement_history', $registration->achievement_history ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="achievement_proof">Bukti prestasi</label>
                            <input id="achievement_proof" name="achievement_proof" type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <div class="helper-text">PDF, JPG, PNG, atau WEBP maksimal 3 MB.</div>
                            @if(!empty($registration?->achievement_proof_path))
                                <div class="helper-text mt-2">Bukti saat ini: <a href="{{ route('registrations.achievement-proof', $registration) }}" target="_blank" rel="noopener">lihat file</a></div>
                            @endif
                        </div>
                    </div>
                </div>
            </details>
        </div>
    </div>

    <div class="col-12">
        <div class="form-section-card">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" id="confirm_registration" name="confirm_registration" @checked(old('confirm_registration'))>
                <label class="form-check-label" for="confirm_registration">Saya telah memeriksa data pendaftaran. <span class="helper-text">(Opsional)</span></label>
            </div>
        </div>
    </div>
</div>
