@extends('layouts.app')

@section('page_title', 'Buat Tes Bakat')
@section('page_subtitle', 'Jadwalkan tes langsung untuk pendaftar atau peserta ekstrakurikuler')

@section('content')
    <div class="card">
        <div class="card-header">Form Jadwal Tes Bakat</div>
        <div class="card-body">
            <form method="post" action="{{ route('coach.talent-tests.store') }}">
                @include('coach.talent-tests._form')
                <div class="form-actions mt-3">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Simpan Jadwal Tes</button>
                    <a href="{{ route('coach.talent-tests.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
