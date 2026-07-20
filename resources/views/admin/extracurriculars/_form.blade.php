@csrf
@if(isset($extracurricular))
    @method('put')
@endif
@php
    $branchOptionsText = old('branch_options', isset($extracurricular) ? collect($extracurricular->branch_options ?? [])->implode(PHP_EOL) : '');
@endphp
<div class="row g-3">
    <div class="col-12">
        <div class="page-summary-banner">
            <div class="data-point-label">Panduan</div>
            <p class="data-point-value mb-2">Lengkapi profil ekstrakurikuler agar tampil rapi di halaman publik, pendaftaran, dan dashboard admin.</p>
            <div class="helper-text mb-0">Pembina untuk ekstrakurikuler ini diatur dari menu <strong>Data Pembina</strong>, dan satu ekstrakurikuler bisa memiliki lebih dari satu pembina.</div>
        </div>
    </div>
    <div class="col-12">
        <div class="form-section-card">
            <h3 class="form-section-title">Profil Kegiatan</h3>
            <p class="form-section-copy">Isi jenis, nama, deskripsi, syarat, dan ringkasan jadwal untuk kebutuhan publikasi dan pendaftaran siswa.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="ex_type">Jenis Kegiatan</label>
                    <select id="ex_type" name="type" class="form-select" required>
                        @foreach(($types ?? []) as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $extracurricular->type ?? \App\Models\Extracurricular::TYPE_EXTRACURRICULAR) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="ex_name">Nama Kegiatan</label>
                    <input type="text" id="ex_name" name="name" value="{{ old('name', $extracurricular->name ?? '') }}" class="form-control" placeholder="Contoh: Pramuka atau OSN" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label d-block">Status Publikasi</label>
                    <div class="data-point h-100 d-flex align-items-center">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_excul" @checked(old('is_active', $extracurricular->is_active ?? true))>
                            <label class="form-check-label fw-semibold" for="is_active_excul">Aktif</label>
                        </div>
                    </div>
                    @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="ex_description">Deskripsi</label>
                    <textarea id="ex_description" name="description" rows="3" class="form-control" placeholder="Deskripsi singkat kegiatan" required>{{ old('description', $extracurricular->description ?? '') }}</textarea>
                    @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="ex_requirements">Persyaratan</label>
                    <textarea id="ex_requirements" name="requirements" rows="3" class="form-control" placeholder="Syarat pendaftaran siswa">{{ old('requirements', $extracurricular->requirements ?? '') }}</textarea>
                    @error('requirements')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="ex_schedule_overview">Ringkasan Jadwal</label>
                    <textarea id="ex_schedule_overview" name="schedule_overview" rows="3" class="form-control" placeholder="Contoh: Setiap Jumat 15.00-17.00">{{ old('schedule_overview', $extracurricular->schedule_overview ?? '') }}</textarea>
                    @error('schedule_overview')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="ex_branch_options">Pilihan Cabang atau Subkegiatan</label>
                    <textarea id="ex_branch_options" name="branch_options" rows="5" class="form-control" placeholder="Isi satu cabang per baris. Contoh:\nMatematika\nInformatika\nGeografi">{{ $branchOptionsText }}</textarea>
                    <div class="helper-text">Opsional. Jika diisi, siswa wajib memilih salah satu cabang saat mendaftar.</div>
                    @error('branch_options')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-section-card">
            <h3 class="form-section-title">Media Kegiatan</h3>
            <p class="form-section-copy">Unggah gambar utama agar tampilan kegiatan lebih menarik di halaman publik.</p>
            <label class="form-label" for="ex_image">Gambar Kegiatan</label>
            <input type="file" id="ex_image" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="helper-text">Opsional. Gunakan gambar JPG, PNG, atau WEBP dengan ukuran maksimal 3 MB.</div>
            @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

            @if(!empty($extracurricular?->image_path))
                <div class="mt-3">
                    <img src="{{ asset($extracurricular->image_path) }}" alt="{{ $extracurricular->name }}" width="180" height="120" loading="lazy" decoding="async" class="image-preview-card">
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image_excul">
                    <label class="form-check-label" for="remove_image_excul">Hapus gambar saat ini</label>
                </div>
            @endif
        </div>
    </div>
</div>
