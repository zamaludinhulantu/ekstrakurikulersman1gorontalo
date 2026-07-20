@csrf
@isset($talentTest)
    @method('put')
@endisset
@php
    $selectedParticipantRegistrationIds = collect(
        old('participant_registration_ids', isset($talentTest) ? $talentTest->talentTestParticipants->pluck('registration_id')->all() : [])
    )->values();
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="talent_test_extracurricular_id">Ekstrakurikuler</label>
        <select id="talent_test_extracurricular_id" name="extracurricular_id" class="form-select" required data-talent-test-selector data-selected='@json($selectedParticipantRegistrationIds)'>
            <option value="">- Pilih Ekstrakurikuler -</option>
            @foreach($extracurriculars as $item)
                @php
                    $registrationOptions = $item->registrations
                        ->whereIn('status', ['pending', 'approved'])
                        ->values()
                        ->map(function ($registration): array {
                            return [
                                'id' => $registration->id,
                                'name' => $registration->student->user->name ?? '-',
                                'status' => $registration->status,
                                'class_name' => $registration->student->class_name ?? '-',
                            ];
                        });
                @endphp
                <option value="{{ $item->id }}" data-registrations='@json($registrationOptions)' @selected((string) old('extracurricular_id', $talentTest->extracurricular_id ?? '') === (string) $item->id)>{{ $item->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="talent_test_title">Nama tes</label>
        <input id="talent_test_title" name="title" type="text" class="form-control" required value="{{ old('title', $talentTest->title ?? '') }}" placeholder="Contoh: Tes bakat gelombang 1">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="talent_test_date">Tanggal</label>
        <input id="talent_test_date" name="activity_date" type="date" class="form-control" required value="{{ old('activity_date', isset($talentTest) ? optional($talentTest->activity_date)->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="talent_test_start_time">Jam mulai</label>
        <input id="talent_test_start_time" name="start_time" type="time" class="form-control" required value="{{ old('start_time', $talentTest->start_time ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="talent_test_end_time">Jam selesai</label>
        <input id="talent_test_end_time" name="end_time" type="time" class="form-control" required value="{{ old('end_time', $talentTest->end_time ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="talent_test_location">Lokasi</label>
        <input id="talent_test_location" name="location" type="text" class="form-control" required value="{{ old('location', $talentTest->location ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="talent_test_equipment">Peralatan yang harus dibawa</label>
        <input id="talent_test_equipment" name="equipment" type="text" class="form-control" value="{{ old('equipment', $talentTest->equipment ?? '') }}" placeholder="Contoh: sepatu olahraga, alat tulis">
    </div>
    <div class="col-12">
        <label class="form-label" for="talent_test_description">Deskripsi tes</label>
        <textarea id="talent_test_description" name="description" class="form-control" rows="3">{{ old('description', $talentTest->description ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="talent_test_instructions">Instruksi untuk siswa</label>
        <textarea id="talent_test_instructions" name="instructions" class="form-control" rows="3">{{ old('instructions', $talentTest->instructions ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Peserta tes</label>
        <div class="participant-picker" data-talent-test-participants>
            <div class="helper-text mb-2">Pilih ekstrakurikuler terlebih dahulu untuk memuat siswa pendaftar atau peserta aktif.</div>
            <div class="d-flex flex-wrap gap-2 mb-2" data-talent-test-participant-actions hidden>
                <button type="button" class="btn btn-sm btn-outline-primary" data-talent-test-select-all>
                    <i class="bi bi-check2-square"></i>Pilih Semua Peserta
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-talent-test-clear-all>
                    <i class="bi bi-eraser"></i>Kosongkan Pilihan
                </button>
            </div>
            <div class="row g-2" data-talent-test-participant-list></div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-talent-test-participants]').forEach((picker) => {
                    const select = picker.querySelector('[data-talent-test-selector]') || document.querySelector('[data-talent-test-selector]');
                    const list = picker.querySelector('[data-talent-test-participant-list]');
                    const actions = picker.querySelector('[data-talent-test-participant-actions]');
                    const selectAllButton = picker.querySelector('[data-talent-test-select-all]');
                    const clearAllButton = picker.querySelector('[data-talent-test-clear-all]');

                    if (!select || !list || picker.dataset.bound === '1') {
                        return;
                    }

                    picker.dataset.bound = '1';

                    const selectedIds = new Set(JSON.parse(select.dataset.selected || '[]').map((value) => String(value)));

                    const bindCheckboxes = () => {
                        list.querySelectorAll('input[type="checkbox"][name="participant_registration_ids[]"]').forEach((checkbox) => {
                            checkbox.checked = selectedIds.has(String(checkbox.value));
                            checkbox.addEventListener('change', () => {
                                const id = String(checkbox.value);
                                if (checkbox.checked) {
                                    selectedIds.add(id);
                                } else {
                                    selectedIds.delete(id);
                                }
                            });
                        });
                    };

                    const renderParticipants = () => {
                        const option = select.options[select.selectedIndex];
                        const registrations = option?.dataset?.registrations ? JSON.parse(option.dataset.registrations) : [];

                        list.innerHTML = '';
                        if (actions) {
                            actions.hidden = registrations.length === 0;
                        }

                        registrations.forEach((registration) => {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'col-md-6';
                            wrapper.innerHTML = `
                                <label class="participant-picker__option">
                                    <input type="checkbox" name="participant_registration_ids[]" value="${registration.id}">
                                    <span>
                                        <strong>${registration.name}</strong>
                                        <small>${registration.class_name} | ${registration.status}</small>
                                    </span>
                                </label>
                            `;
                            list.appendChild(wrapper);
                        });

                        bindCheckboxes();
                    };

                    selectAllButton?.addEventListener('click', () => {
                        list.querySelectorAll('input[type="checkbox"][name="participant_registration_ids[]"]').forEach((checkbox) => {
                            checkbox.checked = true;
                            selectedIds.add(String(checkbox.value));
                        });
                    });

                    clearAllButton?.addEventListener('click', () => {
                        list.querySelectorAll('input[type="checkbox"][name="participant_registration_ids[]"]').forEach((checkbox) => {
                            checkbox.checked = false;
                            selectedIds.delete(String(checkbox.value));
                        });
                    });

                    select.addEventListener('change', renderParticipants);
                    renderParticipants();
                });
            });
        </script>
    @endpush
@endonce
