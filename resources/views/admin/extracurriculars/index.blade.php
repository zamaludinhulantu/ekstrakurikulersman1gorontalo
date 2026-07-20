@extends('layouts.app')

@section('page_title', 'Data Kegiatan')
@section('page_subtitle', 'Kelola informasi ekstrakurikuler, olimpiade, dan pembina')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('admin.extracurriculars.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Tambah Kegiatan</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama kegiatan atau pembina">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter kategori</label>
                    <select name="category" class="form-select">
                        <option value="all" @selected(($category ?? 'all') === 'all')>Semua kegiatan</option>
                        @foreach(($categories ?? collect()) as $option)
                            <option value="{{ $option['key'] }}" @selected(($category ?? 'all') === $option['key'])>{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i>Cari</button></div>
                <div class="col-md-2"><a href="{{ route('admin.extracurriculars.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>Pembina</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($extracurriculars as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td><span class="badge badge-status-secondary">{{ $item->category_label }}</span></td>
                        <td>{{ $item->coach_names }}</td>
                        <td><span class="badge" data-status="{{ $item->is_active ? 'active' : 'inactive' }}">{{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                        <td class="d-flex flex-wrap gap-1">
                            <a href="{{ route('admin.extracurriculars.show', $item) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i>Detail</a>
                            <a href="{{ route('admin.extracurriculars.edit', $item) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                            <form method="post" action="{{ route('admin.extracurriculars.destroy', $item) }}" onsubmit="return confirm('Hapus data ini?')">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-grid-1x2"></i></div>
                                <p class="mb-0">Data tidak ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $extracurriculars->links() }}</div>
    </div>
@endsection
