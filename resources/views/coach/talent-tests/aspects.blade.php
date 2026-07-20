@extends('layouts.app')

@section('page_title', 'Aspek Penilaian Tes')
@section('page_subtitle', 'Atur aspek tes bakat berbeda untuk setiap ekstrakurikuler')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select" onchange="this.form.submit()">
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected($extracurricular->id === $item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header">Tambah Aspek Baru</div>
                <div class="card-body">
                    <form method="post" action="{{ route('coach.talent-test-aspects.store', $extracurricular) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama aspek</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nilai maksimum</label>
                                <input type="number" name="max_score" class="form-control" value="100" min="1" max="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Urutan tampil</label>
                                <input type="number" name="display_order" class="form-control" value="0" min="0">
                            </div>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" value="1" id="aspect_active" name="is_active" checked>
                            <label class="form-check-label" for="aspect_active">Aktif digunakan</label>
                        </div>
                        <button class="btn btn-primary mt-3" type="submit"><i class="bi bi-plus-circle"></i>Tambah Aspek</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header">Daftar Aspek {{ $extracurricular->name }}</div>
                <div class="card-body">
                    <div class="info-list">
                        @forelse($extracurricular->talentTestAspects as $aspect)
                            <div class="info-item">
                                <form method="post" action="{{ route('coach.talent-test-aspects.update', [$extracurricular, $aspect]) }}">
                                    @csrf
                                    @method('put')
                                    <div class="row g-2">
                                        <div class="col-md-5"><input type="text" name="name" class="form-control" value="{{ $aspect->name }}" required></div>
                                        <div class="col-md-2"><input type="number" name="max_score" class="form-control" value="{{ $aspect->max_score }}" min="1" max="100"></div>
                                        <div class="col-md-2"><input type="number" name="display_order" class="form-control" value="{{ $aspect->display_order }}" min="0"></div>
                                        <div class="col-md-3">
                                            <select name="is_active" class="form-select">
                                                <option value="1" @selected($aspect->is_active)>Aktif</option>
                                                <option value="0" @selected(! $aspect->is_active)>Nonaktif</option>
                                            </select>
                                        </div>
                                        <div class="col-12"><textarea name="description" class="form-control" rows="2">{{ $aspect->description }}</textarea></div>
                                        <div class="col-12 row-actions">
                                            <button class="btn btn-sm btn-primary" type="submit"><i class="bi bi-save"></i>Simpan</button>
                                        </div>
                                    </div>
                                </form>
                                <form method="post" action="{{ route('coach.talent-test-aspects.destroy', [$extracurricular, $aspect]) }}" class="mt-2" onsubmit="return confirm('Hapus aspek ini?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                </form>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <p class="mb-0">Belum ada aspek penilaian untuk ekstrakurikuler ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
