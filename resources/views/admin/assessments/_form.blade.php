@php
    $selectedExtracurricularId = (string) old('extracurricular_id', $assessment->extracurricular_id ?? '');
    $selectedStudentId = (string) old('student_id', $assessment->student_id ?? '');
    $selectedCoachId = (string) old('coach_id', $assessment->coach_id ?? '');
@endphp

<div class="col-md-4">
    <label class="form-label" for="assessment_extracurricular_id">Ekstrakurikuler</label>
    <select id="assessment_extracurricular_id" name="extracurricular_id" class="form-select" required>
        <option value="">Pilih Ekstrakurikuler</option>
        @foreach($extracurriculars as $item)
            <option value="{{ $item->id }}" @selected($selectedExtracurricularId === (string) $item->id)>{{ $item->name }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label class="form-label" for="assessment_student_id">Siswa</label>
    <select id="assessment_student_id" name="student_id" class="form-select" required>
        <option value="">Pilih Siswa</option>
        @foreach($approvedRegistrations as $registration)
            <option
                value="{{ $registration->student_id }}"
                data-extracurricular-id="{{ $registration->extracurricular_id }}"
                @selected(
                    $selectedStudentId === (string) $registration->student_id
                    && ($selectedExtracurricularId === '' || $selectedExtracurricularId === (string) $registration->extracurricular_id)
                )
            >
                {{ $registration->student->user->name ?? 'Siswa' }} ({{ $registration->student->nis ?? '-' }}) - {{ $registration->extracurricular->name ?? 'Ekskul' }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label class="form-label" for="assessment_coach_id">Pembina</label>
    <select id="assessment_coach_id" name="coach_id" class="form-select">
        <option value="">Pilih Pembina (Opsional)</option>
        @foreach($coaches as $coach)
            <option
                value="{{ $coach->id }}"
                data-extracurricular-ids="{{ $coach->extracurriculars->pluck('id')->implode(',') }}"
                @selected($selectedCoachId === (string) $coach->id)
            >
                {{ $coach->user->name ?? 'Pembina' }}
            </option>
        @endforeach
    </select>
    <div class="helper-text">Hanya pembina yang terhubung ke ekskul terpilih yang bisa dipilih.</div>
</div>

<div class="col-md-4">
    <label class="form-label" for="assessment_type">Jenis</label>
    <select id="assessment_type" name="assessment_type" class="form-select" required>
        <option value="achievement" @selected(old('assessment_type', $assessment->assessment_type ?? 'achievement') === 'achievement')>Prestasi</option>
        <option value="assessment" @selected(old('assessment_type', $assessment->assessment_type ?? '') === 'assessment')>Penilaian</option>
    </select>
</div>

<div class="col-md-4">
    <label class="form-label" for="assessment_title">Judul</label>
    <input id="assessment_title" type="text" name="title" value="{{ old('title', $assessment->title ?? '') }}" class="form-control" placeholder="Contoh: Juara Lomba" required>
</div>

<div class="col-md-2">
    <label class="form-label" for="assessment_score">Nilai</label>
    <input id="assessment_score" type="number" step="0.01" min="0" max="100" name="score" value="{{ old('score', $assessment->score ?? '') }}" class="form-control" placeholder="Opsional">
</div>

<div class="col-md-2">
    <label class="form-label" for="assessment_date">Tanggal</label>
    <input id="assessment_date" type="date" name="assessment_date" value="{{ old('assessment_date', optional($assessment->assessment_date ?? null)->format('Y-m-d')) }}" class="form-control" required>
</div>

<div class="col-12">
    <label class="form-label" for="assessment_description">Deskripsi</label>
    <textarea id="assessment_description" name="description" class="form-control" rows="3" placeholder="Uraian singkat">{{ old('description', $assessment->description ?? '') }}</textarea>
</div>

@push('scripts')
    <script>
        (function () {
            const extracurricularSelect = document.getElementById('assessment_extracurricular_id');
            const studentSelect = document.getElementById('assessment_student_id');
            const coachSelect = document.getElementById('assessment_coach_id');

            if (!extracurricularSelect || !studentSelect || !coachSelect) {
                return;
            }

            const filterStudentOptions = function () {
                const extracurricularId = extracurricularSelect.value;
                Array.from(studentSelect.options).forEach(function (option, index) {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    const matches = !extracurricularId || option.dataset.extracurricularId === extracurricularId;
                    option.hidden = !matches;
                });

                const selected = studentSelect.options[studentSelect.selectedIndex];
                if (selected && selected.hidden) {
                    studentSelect.value = '';
                }
            };

            const filterCoachOptions = function () {
                const extracurricularId = extracurricularSelect.value;
                Array.from(coachSelect.options).forEach(function (option, index) {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    const ids = String(option.dataset.extracurricularIds || '')
                        .split(',')
                        .map(function (value) { return value.trim(); })
                        .filter(Boolean);

                    const matches = !extracurricularId || ids.includes(extracurricularId);
                    option.hidden = !matches;
                });

                const selected = coachSelect.options[coachSelect.selectedIndex];
                if (selected && selected.hidden) {
                    coachSelect.value = '';
                }
            };

            extracurricularSelect.addEventListener('change', function () {
                filterStudentOptions();
                filterCoachOptions();
            });

            filterStudentOptions();
            filterCoachOptions();
        })();
    </script>
@endpush
