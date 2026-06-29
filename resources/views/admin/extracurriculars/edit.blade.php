@extends('layouts.app')

@section('page_title', 'Edit Ekstrakurikuler')
@section('page_subtitle', 'Perbarui informasi ekstrakurikuler')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.extracurriculars.update', $extracurricular) }}" enctype="multipart/form-data">
                @include('admin.extracurriculars._form')
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Update</button>
                    <a href="{{ route('admin.extracurriculars.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
