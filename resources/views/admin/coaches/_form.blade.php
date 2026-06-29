@csrf
@if(isset($coach))
    @method('put')
@endif
@php
    $selectedExtracurricularIds = collect(old('extracurricular_ids', isset($coach) ? $coach->extracurriculars->pluck('id')->all() : []))
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="coach_name">Nama</label>
        <input type="text" id="coach_name" name="name" value="{{ old('name', $coach->user->name ?? '') }}" class="form-control" placeholder="Nama lengkap pembina" required>
        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="coach_email">Email</label>
        <input type="email" id="coach_email" name="email" value="{{ old('email', $coach->user->email ?? '') }}" class="form-control" placeholder="contoh: pembina@sman1gorontalo.sch.id" required>
        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="coach_nip">NIP</label>
        <input type="text" id="coach_nip" name="nip" value="{{ old('nip', $coach->nip ?? '') }}" class="form-control" placeholder="Nomor induk pegawai" required>
        @error('nip')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="coach_phone">No. Telepon</label>
        <input type="text" id="coach_phone" name="phone" value="{{ old('phone', $coach->user->phone ?? '') }}" class="form-control" placeholder="08xxxxxxxxxx">
        @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label d-block">Status Akun</label>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="coach_active"
                   @checked(old('is_active', $coach->user->is_active ?? true))>
            <label class="form-check-label" for="coach_active">Aktif</label>
        </div>
        @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="coach_extracurricular_ids">Ekstrakurikuler Dibina</label>
        <div class="row g-2 align-items-start">
            <div class="col-md-8">
                <select id="coach_extracurricular_picker" class="form-select">
                    <option value="">Pilih satu ekstrakurikuler</option>
                    @foreach($extracurriculars as $item)
                        <option value="{{ $item->id }}">
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-outline-primary w-100" id="add_extracurricular_button">
                    <i class="bi bi-plus-circle"></i>Tambah ke Daftar
                </button>
            </div>
        </div>
        <div class="form-control mt-2" style="min-height: 88px;">
            <div class="d-flex flex-wrap gap-2" id="selected_extracurricular_list">
                <div class="text-muted small" id="selected_extracurricular_empty">Belum ada ekstrakurikuler yang dipilih.</div>
            </div>
        </div>
        <div id="selected_extracurricular_inputs">
            @foreach($extracurriculars as $item)
                @if(in_array((string) $item->id, $selectedExtracurricularIds, true))
                    <input type="hidden" name="extracurricular_ids[]" value="{{ $item->id }}" data-extracurricular-input="{{ $item->id }}">
                @endif
            @endforeach
        </div>
        <div class="helper-text">Pilih ekskul dari dropdown lalu klik tambah. Satu ekstrakurikuler dapat memiliki lebih dari satu pembina.</div>
        @error('extracurricular_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        @error('extracurricular_ids.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="coach_address">Alamat</label>
        <textarea id="coach_address" name="address" class="form-control" rows="2" placeholder="Alamat pembina">{{ old('address', $coach->user->address ?? '') }}</textarea>
        @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="coach_bio">Bio</label>
        <textarea id="coach_bio" name="bio" class="form-control" rows="3" placeholder="Profil singkat pembina">{{ old('bio', $coach->bio ?? '') }}</textarea>
        @error('bio')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="coach_password">Password {{ isset($coach) ? '(opsional)' : '' }}</label>
        <input type="password" id="coach_password" name="password" class="form-control" placeholder="Minimal 8 karakter" {{ isset($coach) ? '' : 'required' }}>
        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="coach_password_confirmation">Konfirmasi Password</label>
        <input type="password" id="coach_password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password" {{ isset($coach) ? '' : 'required' }}>
    </div>
</div>
@push('scripts')
    <script>
        (function () {
            const picker = document.getElementById('coach_extracurricular_picker');
            const addButton = document.getElementById('add_extracurricular_button');
            const list = document.getElementById('selected_extracurricular_list');
            const inputs = document.getElementById('selected_extracurricular_inputs');
            const emptyState = document.getElementById('selected_extracurricular_empty');

            if (!picker || !addButton || !list || !inputs) {
                return;
            }

            const selectedMap = new Map();

            const updateEmptyState = function () {
                const hasItem = list.querySelector('[data-extracurricular-badge]') !== null;
                if (emptyState) {
                    emptyState.classList.toggle('d-none', hasItem);
                }
            };

            const removeItem = function (id) {
                list.querySelector('[data-extracurricular-badge="' + id + '"]')?.remove();
                inputs.querySelector('[data-extracurricular-input="' + id + '"]')?.remove();
                selectedMap.delete(String(id));
                updateEmptyState();
            };

            const addItem = function (id, label) {
                const normalizedId = String(id);
                if (!normalizedId || selectedMap.has(normalizedId)) {
                    return;
                }

                selectedMap.set(normalizedId, label);

                const badge = document.createElement('span');
                badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-2 px-3 py-2';
                badge.dataset.extracurricularBadge = normalizedId;
                badge.innerHTML = '<span>' + label + '</span><button type="button" class="btn btn-sm p-0 border-0 bg-transparent text-primary" aria-label="Hapus pilihan"><i class="bi bi-x-lg"></i></button>';
                badge.querySelector('button')?.addEventListener('click', function () {
                    removeItem(normalizedId);
                });
                list.appendChild(badge);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'extracurricular_ids[]';
                input.value = normalizedId;
                input.dataset.extracurricularInput = normalizedId;
                inputs.appendChild(input);

                updateEmptyState();
            };

            Array.from(inputs.querySelectorAll('[data-extracurricular-input]')).forEach(function (input) {
                const option = picker.querySelector('option[value="' + input.value + '"]');
                if (option) {
                    addItem(input.value, option.textContent.trim());
                    input.remove();
                }
            });

            addButton.addEventListener('click', function () {
                const option = picker.options[picker.selectedIndex];
                if (!option || !option.value) {
                    return;
                }

                addItem(option.value, option.textContent.trim());
                picker.value = '';
            });

            updateEmptyState();
        })();
    </script>
@endpush
