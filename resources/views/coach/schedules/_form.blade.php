@csrf
@if(isset($schedule))
    @method('put')
@endif
@php
    $selectedParticipantRegistrationIds = collect(
        old('participant_registration_ids', isset($schedule) ? $schedule->scheduleParticipants->pluck('registration_id')->filter()->all() : [])
    )->values();
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="schedule_extracurricular_id">Ekstrakurikuler</label>
        <select
            id="schedule_extracurricular_id"
            name="extracurricular_id"
            class="form-select"
            required
            data-schedule-participant-selector
            data-selected='@json($selectedParticipantRegistrationIds)'
        >
            <option value="">- Pilih Ekstrakurikuler -</option>
            @foreach($extracurriculars as $item)
                @php
                    $registrationOptions = $item->registrations
                        ->values()
                        ->map(function ($registration): array {
                            return [
                                'id' => $registration->id,
                                'name' => $registration->student->user->name ?? '-',
                                'class_name' => $registration->student->class_name ?? '-',
                                'status' => $registration->status,
                            ];
                        });
                @endphp
                <option
                    value="{{ $item->id }}"
                    data-registrations='@json($registrationOptions)'
                    @selected((string)old('extracurricular_id', $schedule->extracurricular_id ?? '') === (string)$item->id)
                >{{ $item->name }}</option>
            @endforeach
        </select>
        @error('extracurricular_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="schedule_title">Judul Kegiatan</label>
        <input type="text" id="schedule_title" name="title" value="{{ old('title', $schedule->title ?? '') }}" class="form-control" placeholder="Contoh: Latihan Rutin" required>
        @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="schedule_activity_date">Tanggal</label>
        <input type="date" id="schedule_activity_date" name="activity_date" value="{{ old('activity_date', isset($schedule) && $schedule->activity_date ? $schedule->activity_date->format('Y-m-d') : '') }}" class="form-control" required>
        @error('activity_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="schedule_start_time">Jam Mulai</label>
        <input type="time" id="schedule_start_time" name="start_time" value="{{ old('start_time', $schedule->start_time ?? '') }}" class="form-control" required>
        @error('start_time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="schedule_end_time">Jam Selesai</label>
        <input type="time" id="schedule_end_time" name="end_time" value="{{ old('end_time', $schedule->end_time ?? '') }}" class="form-control" required>
        @error('end_time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="schedule_location">Lokasi</label>
        <input type="text" id="schedule_location" name="location" value="{{ old('location', $schedule->location ?? '') }}" class="form-control" placeholder="Contoh: Lapangan Utama" required>
        @error('location')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="schedule_description">Deskripsi</label>
        <textarea id="schedule_description" name="description" rows="3" class="form-control" placeholder="Catatan kegiatan (opsional)">{{ old('description', $schedule->description ?? '') }}</textarea>
        @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">Peserta Jadwal</label>
        <div class="participant-picker" data-schedule-participants>
            <div class="helper-text mb-2">Pilih ekstrakurikuler terlebih dahulu untuk memuat siswa yang sudah disetujui.</div>
            <div class="d-flex flex-wrap gap-2 mb-2" data-schedule-participant-actions hidden>
                <button type="button" class="btn btn-sm btn-outline-primary" data-schedule-select-all>
                    <i class="bi bi-check2-square"></i>Pilih Semua Peserta
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-schedule-clear-all>
                    <i class="bi bi-eraser"></i>Kosongkan Pilihan
                </button>
            </div>
            <div class="row g-2" data-schedule-participant-list></div>
        </div>
        @error('participant_registration_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        @error('participant_registration_ids.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-schedule-participants]').forEach((picker) => {
                    const select = picker.querySelector('[data-schedule-participant-selector]') || document.querySelector('[data-schedule-participant-selector]');
                    const list = picker.querySelector('[data-schedule-participant-list]');
                    const actions = picker.querySelector('[data-schedule-participant-actions]');
                    const selectAllButton = picker.querySelector('[data-schedule-select-all]');
                    const clearAllButton = picker.querySelector('[data-schedule-clear-all]');

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
