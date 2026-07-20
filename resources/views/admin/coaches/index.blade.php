@extends('layouts.app')

@section('page_title', 'Data Pembina')
@section('page_subtitle', 'Kelola data pembina ekstrakurikuler')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('admin.coaches.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Tambah Pembina</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="toolbar-grid">
                <div class="toolbar-col-8">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama, email, atau NIP">
                </div>
                <div class="toolbar-col-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i>Cari</button></div>
                <div class="toolbar-col-2"><a href="{{ route('admin.coaches.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="desktop-table table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($coaches as $coach)
                    <tr>
                        <td>{{ $coach->user->name }}</td>
                        <td>{{ $coach->nip }}</td>
                        <td>{{ $coach->user->email }}</td>
                        <td><span class="badge" data-status="{{ $coach->user->is_active ? 'active' : 'inactive' }}">{{ $coach->user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                        <td class="d-flex flex-wrap gap-1">
                            <a href="{{ route('admin.coaches.show', $coach) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i>Detail</a>
                            <a href="{{ route('admin.coaches.edit', $coach) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                            <form method="post" action="{{ route('admin.coaches.destroy', $coach) }}" onsubmit="return confirm('Hapus pembina ini?')">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-person-workspace"></i></div>
                                <p class="mb-0">Data tidak ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mobile-stack-table p-3">
            @forelse($coaches as $coach)
                <div class="mobile-data-card">
                    <div class="mobile-data-card-header">
                        <h3 class="mobile-data-card-title">{{ $coach->user->name }}</h3>
                        <span class="badge" data-status="{{ $coach->user->is_active ? 'active' : 'inactive' }}">{{ $coach->user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                    <div class="mobile-data-list">
                        <div><span class="mobile-data-item-label">NIP</span><p class="mobile-data-item-value">{{ $coach->nip }}</p></div>
                        <div><span class="mobile-data-item-label">Email</span><p class="mobile-data-item-value">{{ $coach->user->email }}</p></div>
                    </div>
                    <div class="mobile-data-card-actions">
                        <a href="{{ route('admin.coaches.show', $coach) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i>Detail</a>
                        <a href="{{ route('admin.coaches.edit', $coach) }}" class="btn btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                        <form method="post" action="{{ route('admin.coaches.destroy', $coach) }}" onsubmit="return confirm('Hapus pembina ini?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-outline-danger w-100" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-person-workspace"></i></div>
                    <p class="mb-0">Data tidak ditemukan.</p>
                </div>
            @endforelse
        </div>
        <div class="card-body">{{ $coaches->links() }}</div>
    </div>
@endsection
