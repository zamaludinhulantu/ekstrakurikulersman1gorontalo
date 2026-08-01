@extends('layouts.app')

@section('page_title', 'Edit Pengumuman')
@section('page_subtitle', 'Perbarui target, isi, periode, dan status publikasi.')

@section('content')
    <div class="announcement-surface announcement-edit-surface" data-announcement-page>
        <div class="announcement-surface-header">
            <div><h2>Edit Pengumuman</h2><p>Perubahan pada pengumuman aktif tidak mengirim notifikasi ulang.</p></div>
        </div>
        <div class="announcement-create-panel">
            @include('partials.announcements.form')
        </div>
        @include('partials.announcements.preview-modal')
    </div>
@endsection
