@extends('layouts.app')

@section('page_title', 'Data Pengguna')
@section('page_subtitle', 'Kelola akun admin, siswa, pembina, dan kepala sekolah')

@section('content')
    <div class="card mb-3">
        <div class="card-body toolbar-card">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Filter Pengguna</h2>
                    <p class="toolbar-hint mb-0">Cari akun berdasarkan nama, email, telepon, atau peran pengguna.</p>
                </div>
                <a href="{{ route($routePrefix.'.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Tambah Pengguna</a>
            </div>

            <form class="toolbar-grid">
                <div class="toolbar-col-6">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama, email, atau telepon">
                </div>
                <div class="toolbar-col-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">Semua Role</option>
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption }}" @selected($role === $roleOption)>{{ $roleLabels[$roleOption] ?? strtoupper($roleOption) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(($status ?? '') === 'active')>Aktif</option>
                        <option value="inactive" @selected(($status ?? '') === 'inactive')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button>
                </div>
                <div class="toolbar-col-1">
                    <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>Daftar Pengguna</span>
            <span class="small text-muted">{{ $users->total() }} data</span>
        </div>
        <div class="desktop-table table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Telepon</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <div class="small text-muted">{{ $user->address ?? 'Alamat belum diisi' }}</div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge badge-status-secondary">{{ $user->roleLabel() }}</span></td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td><span class="badge" data-status="{{ $user->is_active ? 'active' : 'inactive' }}">{{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route($routePrefix.'.show', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i>Detail</a>
                                <a href="{{ route($routePrefix.'.edit', $user) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                                <form method="post" action="{{ route($routePrefix.'.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-inbox"></i></div>
                                <p class="mb-0">Data pengguna tidak ditemukan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mobile-stack-table p-3">
            @forelse($users as $user)
                <div class="mobile-data-card">
                    <div class="mobile-data-card-header">
                        <h3 class="mobile-data-card-title">{{ $user->name }}</h3>
                        <span class="badge" data-status="{{ $user->is_active ? 'active' : 'inactive' }}">{{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                    <div class="mobile-data-list">
                        <div><span class="mobile-data-item-label">Role</span><p class="mobile-data-item-value">{{ $user->roleLabel() }}</p></div>
                        <div><span class="mobile-data-item-label">Email</span><p class="mobile-data-item-value">{{ $user->email }}</p></div>
                        <div><span class="mobile-data-item-label">Telepon</span><p class="mobile-data-item-value">{{ $user->phone ?? '-' }}</p></div>
                        <div><span class="mobile-data-item-label">Alamat</span><p class="mobile-data-item-value">{{ $user->address ?? 'Alamat belum diisi' }}</p></div>
                    </div>
                    <div class="mobile-data-card-actions">
                        <a href="{{ route($routePrefix.'.show', $user) }}" class="btn btn-outline-primary"><i class="bi bi-eye"></i>Detail</a>
                        <a href="{{ route($routePrefix.'.edit', $user) }}" class="btn btn-outline-warning"><i class="bi bi-pencil-square"></i>Edit</a>
                        <form method="post" action="{{ route($routePrefix.'.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                            @csrf
                            @method('delete')
                            <button class="btn btn-outline-danger w-100" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-inbox"></i></div>
                    <p class="mb-0">Data pengguna tidak ditemukan.</p>
                </div>
            @endforelse
        </div>
        <div class="card-footer">{{ $users->links() }}</div>
    </div>
@endsection
