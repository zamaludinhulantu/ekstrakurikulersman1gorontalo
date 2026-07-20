@extends('layouts.app')

@section('page_title', 'Tambah Kegiatan')
@section('page_subtitle', 'Input data kegiatan baru, baik ekstrakurikuler maupun olimpiade')

@section('content')
    <div class="card">
        <div class="card-header">Form Tambah Kegiatan</div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.extracurriculars.store') }}" enctype="multipart/form-data">
                @include('admin.extracurriculars._form')
                <div class="form-actions mt-4">
                    <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan kegiatan..."><i class="bi bi-save"></i>Simpan</button>
                    <a href="{{ route('admin.extracurriculars.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
