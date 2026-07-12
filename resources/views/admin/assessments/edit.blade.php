@extends('layouts.app')

@section('page_title', 'Edit Prestasi dan Penilaian')
@section('page_subtitle', 'Perbarui prestasi kegiatan atau penilaian siswa dari panel admin')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.assessments.update', $assessment) }}" class="row g-3">
                @csrf
                @method('put')
                @include('admin.assessments._form')
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Update</button>
                    <a href="{{ route('admin.assessments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
