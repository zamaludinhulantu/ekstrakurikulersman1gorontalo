@csrf
@if(isset($schedule))
    @method('put')
@endif
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="schedule_extracurricular_id">Ekstrakurikuler</label>
        <select id="schedule_extracurricular_id" name="extracurricular_id" class="form-select" required>
            <option value="">- Pilih Ekstrakurikuler -</option>
            @foreach($extracurriculars as $item)
                <option value="{{ $item->id }}" @selected((string)old('extracurricular_id', $schedule->extracurricular_id ?? '') === (string)$item->id)>{{ $item->name }}</option>
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
</div>
