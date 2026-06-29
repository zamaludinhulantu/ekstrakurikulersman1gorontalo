@extends('layouts.app')

@section('page_title', 'Tambah Pembina')
@section('page_subtitle', 'Input data pembina ekstrakurikuler')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.coaches.store') }}">
                @include('admin.coaches._form')
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Simpan</button>
                    <a href="{{ route('admin.coaches.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
