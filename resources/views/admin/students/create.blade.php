@extends('layouts.app')

@section('page_title', 'Tambah Siswa')
@section('page_subtitle', 'Input data siswa baru')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.students.store') }}">
                @include('admin.students._form')
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Simpan</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
