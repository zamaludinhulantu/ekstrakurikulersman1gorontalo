@extends('layouts.app')

@section('page_title', 'Edit Pembina')
@section('page_subtitle', 'Perbarui data pembina')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.coaches.update', $coach) }}">
                @include('admin.coaches._form')
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Update</button>
                    <a href="{{ route('admin.coaches.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
