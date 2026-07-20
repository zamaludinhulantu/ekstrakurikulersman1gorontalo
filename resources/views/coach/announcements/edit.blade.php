@extends('layouts.app')

@section('page_title', 'Edit Pengumuman')
@section('page_subtitle', 'Perbarui isi, prioritas, target, dan status publikasi pengumuman.')

@section('content')
    <div class="card">
        <div class="card-body toolbar-card">
            <form method="post" action="{{ route('coach.announcements.update', $announcement) }}" class="row g-3" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="col-md-4">
                    <label class="form-label" for="edit_target_scope">Tujuan pengumuman</label>
                    <select id="edit_target_scope" name="target_scope" class="form-select" required>
                        <option value="single" @selected(old('target_scope', $announcement->extracurricular_id ? 'single' : 'all_managed') === 'single')>Pilih ekstrakurikuler tujuan</option>
                        <option value="all_managed" @selected(old('target_scope', $announcement->extracurricular_id ? 'single' : 'all_managed') === 'all_managed')>Semua ekstrakurikuler binaan</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="edit_extracurricular_id">Ekstrakurikuler tujuan</label>
                    <select id="edit_extracurricular_id" name="extracurricular_id" class="form-select">
                        <option value="">Pilih ekstrakurikuler tujuan</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string) old('extracurricular_id', $announcement->extracurricular_id) === (string) $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="edit_priority">Prioritas</label>
                    <select id="edit_priority" name="priority" class="form-select" required>
                        <option value="normal" @selected(old('priority', $announcement->priority) === 'normal')>Biasa</option>
                        <option value="important" @selected(old('priority', $announcement->priority) === 'important')>Penting</option>
                        <option value="urgent" @selected(old('priority', $announcement->priority) === 'urgent')>Mendesak</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="edit_title">Judul pengumuman</label>
                    <input id="edit_title" type="text" name="title" value="{{ old('title', $announcement->title) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="edit_attachment">Lampiran opsional</label>
                    <input id="edit_attachment" type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="helper-text">Lampiran saat ini: {{ $announcement->attachment_name ?? 'Tidak ada lampiran' }}</div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="edit_content">Isi pengumuman</label>
                    <textarea id="edit_content" name="content" class="form-control" rows="5" required>{{ old('content', $announcement->content) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="edit_publication_action">Status publikasi</label>
                    <select id="edit_publication_action" name="publication_action" class="form-select" required>
                        <option value="draft" @selected(old('publication_action', $announcement->publication_status) === 'draft')>Simpan sebagai Draft</option>
                        <option value="published" @selected(old('publication_action', $announcement->publication_status) === 'published')>Publikasikan Sekarang</option>
                        <option value="scheduled" @selected(old('publication_action', $announcement->publication_status) === 'scheduled')>Jadwalkan Publikasi</option>
                    </select>
                </div>
                <div class="col-md-4 @if(old('publication_action', $announcement->publication_status) !== 'scheduled') d-none @endif" data-edit-publish-schedule-group>
                    <label class="form-label" for="edit_publish_date">Tanggal tayang</label>
                    <input id="edit_publish_date" type="date" name="publish_date" value="{{ old('publish_date', $announcement->publish_at?->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-4 @if(old('publication_action', $announcement->publication_status) !== 'scheduled') d-none @endif" data-edit-publish-schedule-group>
                    <label class="form-label" for="edit_publish_time">Jam tayang</label>
                    <input id="edit_publish_time" type="time" name="publish_time" value="{{ old('publish_time', $announcement->publish_at?->format('H:i')) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="edit_ends_at_date">Tanggal berakhir opsional</label>
                    <input id="edit_ends_at_date" type="date" name="ends_at_date" value="{{ old('ends_at_date', $announcement->ends_at?->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-12 @if(old('target_scope', $announcement->extracurricular_id ? 'single' : 'all_managed') !== 'all_managed') d-none @endif" data-edit-confirm-all-managed>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="confirm_all_managed" value="1" id="editConfirmAllManaged" @checked(old('confirm_all_managed', ! $announcement->extracurricular_id))>
                        <label class="form-check-label" for="editConfirmAllManaged">Saya mengonfirmasi pengumuman ini ditujukan ke semua ekstrakurikuler binaan.</label>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-actions justify-content-between">
                        <a href="{{ route('coach.announcements.index', ['tab' => 'list']) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const targetScope = document.getElementById('edit_target_scope');
            const extracurricularSelect = document.getElementById('edit_extracurricular_id');
            const confirmAllManaged = document.querySelector('[data-edit-confirm-all-managed]');
            const publicationAction = document.getElementById('edit_publication_action');
            const scheduleGroups = document.querySelectorAll('[data-edit-publish-schedule-group]');

            const syncTargetScope = () => {
                const isAllManaged = targetScope.value === 'all_managed';
                extracurricularSelect.disabled = isAllManaged;
                if (isAllManaged) extracurricularSelect.value = '';
                confirmAllManaged.classList.toggle('d-none', !isAllManaged);
            };

            const syncPublicationAction = () => {
                const isScheduled = publicationAction.value === 'scheduled';
                scheduleGroups.forEach((group) => group.classList.toggle('d-none', !isScheduled));
            };

            targetScope?.addEventListener('change', syncTargetScope);
            publicationAction?.addEventListener('change', syncPublicationAction);
            syncTargetScope();
            syncPublicationAction();
        });
    </script>
@endpush
