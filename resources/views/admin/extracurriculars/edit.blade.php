@extends('layouts.app')

@section('page_title', 'Edit Kegiatan')
@section('page_subtitle', 'Perbarui informasi kegiatan')

@section('content')
    <div class="card">
        <div class="card-header">Form Edit Kegiatan</div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.extracurriculars.update', $extracurricular) }}" enctype="multipart/form-data">
                @include('admin.extracurriculars._form')
                <div class="form-actions mt-4">
                    <button class="btn btn-primary" type="submit" data-loading-text="Memperbarui kegiatan..."><i class="bi bi-save"></i>Update</button>
                    <a href="{{ route('admin.extracurriculars.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
