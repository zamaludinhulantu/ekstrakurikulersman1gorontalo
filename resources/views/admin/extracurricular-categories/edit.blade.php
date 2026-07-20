@extends('layouts.app')

@section('page_title', 'Edit Kategori Ekskul')
@section('page_subtitle', $category->label)

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.extracurricular-categories.update', $category) }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                @method('put')

                <div class="col-md-4">
                    <label class="form-label" for="category_label">Nama Kategori</label>
                    <input id="category_label" type="text" name="label" value="{{ old('label', $category->label) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="category_slug">Slug</label>
                    <input id="category_slug" type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="category_icon">Ikon Bootstrap</label>
                    <input id="category_icon" type="text" name="icon" value="{{ old('icon', $category->icon) }}" class="form-control" placeholder="bi-grid-1x2" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="category_title">Judul Halaman</label>
                    <input id="category_title" type="text" name="catalog_title" value="{{ old('catalog_title', $category->catalog_title) }}" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="category_tone">Tone Kartu</label>
                    <select id="category_tone" name="tone" class="form-select" required>
                        @foreach($tones as $value => $label)
                            <option value="{{ $value }}" @selected(old('tone', $category->tone) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="category_order">Urutan</label>
                    <input id="category_order" type="number" min="1" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label" for="category_description">Deskripsi Kartu</label>
                    <textarea id="category_description" name="description" rows="3" class="form-control" required>{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label" for="category_subtitle">Subjudul Halaman Kategori</label>
                    <textarea id="category_subtitle" name="catalog_subtitle" rows="3" class="form-control" required>{{ old('catalog_subtitle', $category->catalog_subtitle) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="category_image">Gambar Kategori</label>
                    <input id="category_image" type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    @if($category->image_path)
                        <div class="mt-2">
                            <img src="{{ asset($category->image_path) }}" alt="{{ $category->label }}" style="width: 220px; max-width: 100%; height: 140px; object-fit: cover; border-radius: 16px; border: 1px solid #dbe5f0;">
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="remove_image" name="remove_image">
                            <label class="form-check-label" for="remove_image">Hapus gambar kategori</label>
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label d-block">Status</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $category->is_active))>
                        <label class="form-check-label" for="is_active">Tampilkan kategori ini di publik</label>
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i>Simpan Perubahan</button>
                    <a href="{{ route('admin.extracurricular-categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
