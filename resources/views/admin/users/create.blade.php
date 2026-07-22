@extends('layouts.app')

@section('page_title', 'Tambah Pengguna')
@section('page_subtitle', 'Buat akun baru sesuai role')

@section('content')
    <div class="card">
        <div class="card-header">Form Tambah Pengguna</div>
        <div class="card-body">
            <form method="post" action="{{ route($routePrefix.'.store') }}">
                @include('admin.users._form')
                <div class="form-actions mt-4">
                    <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan pengguna..."><i class="bi bi-save"></i>Simpan</button>
                    <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
