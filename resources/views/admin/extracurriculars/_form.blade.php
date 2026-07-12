@csrf
@if(isset($extracurricular))
    @method('put')
@endif
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="ex_name">Nama Ekstrakurikuler</label>
        <input type="text" id="ex_name" name="name" value="{{ old('name', $extracurricular->name ?? '') }}" class="form-control" placeholder="Contoh: Pramuka" required>
        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="ex_description">Deskripsi</label>
        <textarea id="ex_description" name="description" rows="3" class="form-control" placeholder="Deskripsi singkat kegiatan" required>{{ old('description', $extracurricular->description ?? '') }}</textarea>
        @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="info-banner">
            <i class="bi bi-info-circle"></i>
            <div>Pembina untuk ekstrakurikuler ini sekarang diatur dari menu <strong>Data Pembina</strong>, dan satu ekstrakurikuler bisa memiliki lebih dari satu pembina.</div>
        </div>
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
        <label class="form-label" for="ex_achievements_overview">Ringkasan Prestasi/Kegiatan</label>
        <textarea id="ex_achievements_overview" name="achievements_overview" rows="3" class="form-control" placeholder="Contoh: Juara 2 Lomba Tingkat Kota">{{ old('achievements_overview', $extracurricular->achievements_overview ?? '') }}</textarea>
        <div class="helper-text">Jika ada banyak prestasi, tulis satu prestasi atau kegiatan per baris. Contoh: <code>Pembinaan tilawah untuk kegiatan keagamaan</code> lalu baris berikutnya <code>Juara 1 lomba tilawah tingkat sekolah</code>.</div>
        @error('achievements_overview')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="ex_image">Gambar Ekstrakurikuler</label>
        <input type="file" id="ex_image" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
        <div class="helper-text">Opsional. Gunakan gambar JPG, PNG, atau WEBP dengan ukuran maksimal 3 MB.</div>
        @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

        @if(!empty($extracurricular?->image_path))
            <div class="mt-3">
                <img src="{{ asset($extracurricular->image_path) }}" alt="{{ $extracurricular->name }}" style="width: 180px; height: 120px; object-fit: cover; border-radius: 14px; border: 1px solid #dbe5f0;">
            </div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image_excul">
                <label class="form-check-label" for="remove_image_excul">Hapus gambar saat ini</label>
            </div>
        @endif
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_excul"
                   @checked(old('is_active', $extracurricular->is_active ?? true))>
            <label class="form-check-label" for="is_active_excul">Aktif</label>
        </div>
        @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
</div>
