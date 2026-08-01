@extends('layouts.app')

@section('page_title', 'Berita dan Artikel')
@section('page_subtitle', 'Kelola konten untuk kegiatan ekstrakurikuler binaan.')

@include('articles._assets')

@section('content')
    @include('articles._management')
@endsection
