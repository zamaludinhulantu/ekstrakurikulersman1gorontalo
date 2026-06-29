@extends('layouts.app')

@section('page_title', 'Tambah Jadwal Kegiatan')
@section('page_subtitle', 'Isi detail jadwal untuk ekstrakurikuler binaan')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('coach.schedules.store') }}">
                @include('coach.schedules._form')
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Simpan</button>
                    <a href="{{ route('coach.schedules.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
