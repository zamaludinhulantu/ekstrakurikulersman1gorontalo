@extends('layouts.app')

@section('page_title', 'Ubah Tes Bakat')
@section('page_subtitle', 'Perbarui jadwal, instruksi, dan peserta tes')

@section('content')
    <div class="card">
        <div class="card-header">Edit Jadwal Tes Bakat</div>
        <div class="card-body">
            <form method="post" action="{{ route('coach.talent-tests.update', $talentTest) }}">
                @include('coach.talent-tests._form', ['talentTest' => $talentTest])
                <div class="form-actions mt-3">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Simpan Perubahan</button>
                    <a href="{{ route('coach.talent-tests.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
