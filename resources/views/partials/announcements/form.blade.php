@php
    $editing = isset($announcement);
    $formPrefix = $editing ? 'edit_announcement' : 'create_announcement';
    $selectedTarget = old(
        'target_scope',
        $editing
            ? ($announcement->extracurricular_id ? 'single' : 'all_students')
            : ($canTargetAllStudents ? 'all_students' : 'single')
    );
    $selectedPublication = old('publication_action', $editing ? $announcement->publication_status : 'draft');
@endphp

<form
    method="post"
    action="{{ $editing ? route($routePrefix.'.update', $announcement) : route($routePrefix.'.store') }}"
    enctype="multipart/form-data"
    class="announcement-form"
    data-announcement-form
>
    @csrf
    @if($editing)
        @method('put')
    @endif

    <div class="announcement-form-grid">
        <div class="announcement-field announcement-field--wide">
            <div class="announcement-label-row">
                <label class="form-label" for="{{ $formPrefix }}_title">Judul Pengumuman</label>
                <span class="announcement-counter" data-character-count-for="{{ $formPrefix }}_title">0/{{ \App\Support\AnnouncementManager::TITLE_MAX_LENGTH }}</span>
            </div>
            <input
                id="{{ $formPrefix }}_title"
                type="text"
                name="title"
                value="{{ old('title', $announcement->title ?? '') }}"
                class="form-control @error('title') is-invalid @enderror"
                maxlength="{{ \App\Support\AnnouncementManager::TITLE_MAX_LENGTH }}"
                placeholder="Contoh: Perubahan jadwal latihan"
                required
                aria-describedby="{{ $formPrefix }}_title_error"
            >
            @error('title')<div id="{{ $formPrefix }}_title_error" class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="announcement-field">
            <label class="form-label" for="{{ $formPrefix }}_target_scope">Target Pengumuman</label>
            <select id="{{ $formPrefix }}_target_scope" name="target_scope" class="form-select @error('target_scope') is-invalid @enderror" data-announcement-target required>
                @if($canTargetAllStudents)
                    <option value="all_students" @selected($selectedTarget === 'all_students')>Semua siswa</option>
                @endif
                <option value="single" @selected($selectedTarget === 'single')>Kegiatan tertentu</option>
            </select>
            @error('target_scope')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="announcement-field" data-announcement-activity-group>
            <label class="form-label" for="{{ $formPrefix }}_extracurricular">Kegiatan Tujuan</label>
            <select id="{{ $formPrefix }}_extracurricular" name="extracurricular_id" class="form-select @error('extracurricular_id') is-invalid @enderror" data-announcement-activity>
                <option value="">Pilih kegiatan</option>
                @foreach($extracurriculars as $item)
                    <option value="{{ $item->id }}" @selected((string) old('extracurricular_id', $announcement->extracurricular_id ?? '') === (string) $item->id)>
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
            @error('extracurricular_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="announcement-field">
            <label class="form-label" for="{{ $formPrefix }}_priority">Prioritas</label>
            <select id="{{ $formPrefix }}_priority" name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                <option value="normal" @selected(old('priority', $announcement->priority ?? 'normal') === 'normal')>Biasa</option>
                <option value="important" @selected(old('priority', $announcement->priority ?? '') === 'important')>Penting</option>
                <option value="urgent" @selected(old('priority', $announcement->priority ?? '') === 'urgent')>Mendesak</option>
            </select>
            @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="announcement-field announcement-field--full">
            <div class="announcement-label-row">
                <label class="form-label" for="{{ $formPrefix }}_content">Isi Pengumuman</label>
                <span class="announcement-counter" data-character-count-for="{{ $formPrefix }}_content">0/{{ \App\Support\AnnouncementManager::CONTENT_MAX_LENGTH }}</span>
            </div>
            <textarea
                id="{{ $formPrefix }}_content"
                name="content"
                class="form-control announcement-textarea @error('content') is-invalid @enderror"
                maxlength="{{ \App\Support\AnnouncementManager::CONTENT_MAX_LENGTH }}"
                rows="4"
                placeholder="Tulis informasi penting untuk siswa"
                required
                aria-describedby="{{ $formPrefix }}_content_help {{ $formPrefix }}_content_error"
            >{{ old('content', $announcement->content ?? '') }}</textarea>
            <div id="{{ $formPrefix }}_content_help" class="helper-text">Sertakan tanggal, waktu, dan lokasi bila diperlukan.</div>
            @error('content')<div id="{{ $formPrefix }}_content_error" class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="announcement-field">
            <label class="form-label" for="{{ $formPrefix }}_publication">Status Publikasi</label>
            <select id="{{ $formPrefix }}_publication" name="publication_action" class="form-select @error('publication_action') is-invalid @enderror" data-announcement-publication required>
                <option value="draft" @selected($selectedPublication === 'draft')>Simpan sebagai draft</option>
                <option value="published" @selected($selectedPublication === 'published')>Publikasikan sekarang</option>
                <option value="scheduled" @selected($selectedPublication === 'scheduled')>Jadwalkan publikasi</option>
            </select>
            <div class="helper-text">Draft tidak ditampilkan dan tidak mengirim notifikasi.</div>
            @error('publication_action')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="announcement-field" data-announcement-schedule>
            <label class="form-label" for="{{ $formPrefix }}_publish_date">Tanggal Tayang</label>
            <input id="{{ $formPrefix }}_publish_date" type="date" name="publish_date" value="{{ old('publish_date', isset($announcement) ? $announcement->publish_at?->format('Y-m-d') : '') }}" class="form-control @error('publish_date') is-invalid @enderror">
            @error('publish_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="announcement-field" data-announcement-schedule>
            <label class="form-label" for="{{ $formPrefix }}_publish_time">Jam Tayang</label>
            <input id="{{ $formPrefix }}_publish_time" type="time" name="publish_time" value="{{ old('publish_time', isset($announcement) ? $announcement->publish_at?->format('H:i') : '') }}" class="form-control @error('publish_time') is-invalid @enderror">
            @error('publish_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="announcement-field">
            <label class="form-label" for="{{ $formPrefix }}_ends_at">Berakhir Ditampilkan</label>
            <input id="{{ $formPrefix }}_ends_at" type="date" name="ends_at_date" value="{{ old('ends_at_date', isset($announcement) ? $announcement->ends_at?->format('Y-m-d') : '') }}" class="form-control @error('ends_at_date') is-invalid @enderror">
            <div class="helper-text">Opsional. Kosongkan jika tidak dibatasi.</div>
            @error('ends_at_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="announcement-field">
            <label class="form-label" for="{{ $formPrefix }}_attachment">Lampiran</label>
            <input id="{{ $formPrefix }}_attachment" type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
            <div class="helper-text">
                PDF/JPG/PNG maksimal 2 MB.
                @if($editing && $announcement->attachment_name)
                    Saat ini: {{ $announcement->attachment_name }}.
                @endif
            </div>
            @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="announcement-form-actions">
        @if($editing)
            <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-secondary">Batal</a>
        @else
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#announcementCreatePanel">Batal</button>
        @endif
        <button type="button" class="btn btn-outline-primary" data-announcement-preview>
            <i class="bi bi-eye"></i>Pratinjau
        </button>
        <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan...">
            <i class="bi bi-save"></i>{{ $editing ? 'Simpan Perubahan' : 'Simpan Pengumuman' }}
        </button>
    </div>
</form>
