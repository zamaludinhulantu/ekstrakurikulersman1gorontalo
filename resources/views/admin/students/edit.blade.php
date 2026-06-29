@extends('layouts.app')

@section('page_title', 'Edit Siswa')
@section('page_subtitle', 'Perbarui data siswa')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.students.update', $student) }}">
                @include('admin.students._form')
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Update</button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
