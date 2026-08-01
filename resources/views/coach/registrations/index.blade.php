@extends('layouts.app')

@section('page_title', 'Pendaftar Ekstrakurikuler')
@section('page_subtitle', 'Periksa pendaftaran siswa hanya pada kegiatan yang menjadi tanggung jawab Anda.')

@section('content')
    @include('partials.registration-management-list', [
        'registrationRole' => 'coach',
        'registrationFilterDescription' => 'Cari dan saring pendaftar dari kegiatan binaan berdasarkan kelas, status, atau tanggal daftar.',
        'registrationEmptyMessage' => $extracurriculars->isEmpty()
            ? 'Anda belum memiliki kegiatan yang dapat dikelola.'
            : 'Belum ada pendaftaran pada kegiatan binaan yang sesuai dengan filter ini.',
    ])

    @include('partials.registration-verification-modals')
@endsection
