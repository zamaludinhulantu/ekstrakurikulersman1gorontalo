@extends('layouts.app')

@section('page_title', 'Berita dan Artikel')
@section('page_subtitle', 'Kelola konten informasi, kegiatan, dan publikasi sekolah.')

@include('articles._assets')

@section('content')
    @include('articles._management')
@endsection
