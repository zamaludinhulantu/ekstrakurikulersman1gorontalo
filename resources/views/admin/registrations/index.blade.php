@extends('layouts.app')

@section('page_title', 'Pendaftar Ekstrakurikuler')
@section('page_subtitle', 'Periksa setiap pendaftaran kegiatan dan berikan keputusan sesuai proses seleksi.')

@section('content')
    @include('partials.registration-management-list', [
        'registrationRole' => 'admin',
        'registrationFilterDescription' => 'Cari dan saring pendaftaran berdasarkan siswa, kegiatan, kelas, status, atau tanggal daftar.',
        'registrationEmptyMessage' => 'Belum ada pendaftaran yang sesuai dengan filter ini.',
    ])

    @include('partials.registration-verification-modals')
@endsection
