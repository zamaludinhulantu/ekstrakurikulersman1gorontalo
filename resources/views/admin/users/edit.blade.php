@extends('layouts.app')

@section('page_title', 'Edit Pengguna')
@section('page_subtitle', 'Perbarui data akun pengguna')

@section('content')
    <div class="card">
        <div class="card-header">Form Edit Pengguna</div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.users.update', $user) }}">
                @include('admin.users._form')
                <div class="form-actions mt-4">
                    <button class="btn btn-primary" type="submit" data-loading-text="Memperbarui pengguna..."><i class="bi bi-save"></i>Update</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
