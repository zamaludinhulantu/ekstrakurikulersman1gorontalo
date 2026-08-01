@extends('layouts.app')

@section('page_title', 'Kategori Ekskul')
@section('page_subtitle', 'Kelola judul, deskripsi, gambar, dan urutan kartu kategori publik.')

@section('content')
    <div class="card">
        <div class="desktop-table table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Urutan</th>
                    <th>Kategori</th>
                    <th>Slug</th>
                    <th>Tone</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            <div class="fw-semibold">{{ $category->label }}</div>
                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($category->description, 90) }}</div>
                        </td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ $category->tone }}</td>
                        <td>
                            <span class="badge" data-status="{{ $category->is_active ? 'active' : 'inactive' }}">
                                {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.extracurricular-categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil-square"></i>Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-collection"></i></div>
                                <p class="mb-0">Belum ada kategori ekskul.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mobile-stack-table p-3">
            @forelse($categories as $category)
                <div class="mobile-data-card">
                    <div class="mobile-data-card-header">
                        <div>
                            <h3 class="mobile-data-card-title">{{ $category->label }}</h3>
                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($category->description, 90) }}</div>
                        </div>
                        <span class="badge" data-status="{{ $category->is_active ? 'active' : 'inactive' }}">
                            {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="mobile-data-list mb-3">
                        <div><span class="mobile-data-item-label">Urutan</span><p class="mobile-data-item-value">{{ $category->sort_order }}</p></div>
                        <div><span class="mobile-data-item-label">Slug</span><p class="mobile-data-item-value">{{ $category->slug }}</p></div>
                        <div><span class="mobile-data-item-label">Tone</span><p class="mobile-data-item-value">{{ $category->tone }}</p></div>
                    </div>
                    <div class="mobile-data-card-actions">
                        <a href="{{ route('admin.extracurricular-categories.edit', $category) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil-square"></i>Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-collection"></i></div>
                    <p class="mb-0">Belum ada kategori ekskul.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
