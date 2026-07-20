@extends('layouts.app')

@section('page_title', 'Edit Siswa')
@section('page_subtitle', 'Perbarui data siswa')

@section('content')
    <div class="card">
        <div class="card-header">Form Edit Siswa</div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.students.update', $student) }}">
                @include('admin.students._form')
                <div class="form-actions mt-4">
                    <button class="btn btn-primary" type="submit" data-loading-text="Memperbarui siswa..."><i class="bi bi-save"></i>Update</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
