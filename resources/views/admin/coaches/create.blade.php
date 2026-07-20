@extends('layouts.app')

@section('page_title', 'Tambah Pembina')
@section('page_subtitle', 'Input data pembina ekstrakurikuler')

@section('content')
    <div class="card">
        <div class="card-header">Form Tambah Pembina</div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.coaches.store') }}">
                @include('admin.coaches._form')
                <div class="form-actions mt-4">
                    <button class="btn btn-primary" type="submit" data-loading-text="Menyimpan pembina..."><i class="bi bi-save"></i>Simpan</button>
                    <a href="{{ route('admin.coaches.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
