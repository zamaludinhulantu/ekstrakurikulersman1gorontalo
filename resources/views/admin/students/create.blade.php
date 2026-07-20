@extends('layouts.app')

@section('page_title', 'Tambah Siswa')
@section('page_subtitle', 'Input data siswa baru')

@section('content')
    <div class="card">
        <div class="card-header">Form Tambah Siswa</div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.students.store') }}">
                @include('admin.students._form')
                <div class="form-actions mt-4">
                    <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan siswa..."><i class="bi bi-save"></i>Simpan</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
