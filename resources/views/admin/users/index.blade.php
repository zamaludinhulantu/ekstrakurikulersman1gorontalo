@extends('layouts.app')

@section('page_title', 'Data Pengguna')
@section('page_subtitle', 'Kelola akun admin, siswa, pembina, dan kepala sekolah')

@section('content')
    @php
        $activeFilters = [
            ['label' => 'Cari', 'value' => $search ?: null],
            ['label' => 'Role', 'value' => $role ? ($roleLabels[$role] ?? strtoupper($role)) : null],
            ['label' => 'Status', 'value' => ($status ?? '') === 'active' ? 'Aktif' : (($status ?? '') === 'inactive' ? 'Tidak aktif' : null)],
        ];
    @endphp

    <x-filter.card class="mb-3" title="Filter Pengguna" description="Cari akun berdasarkan nama, email, telepon, role, dan status.">
        <x-slot:actions>
            <a href="{{ route($routePrefix.'.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Tambah Pengguna</a>
        </x-slot:actions>

        <x-slot:active>
            <x-filter.active-filters :items="$activeFilters" :reset-url="route($routePrefix.'.index')" />
        </x-slot:active>

        <form class="toolbar-grid" method="get">
            <x-filter.field label="Pencarian" for="user_search" col="toolbar-col-6">
                <input id="user_search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama atau email">
            </x-filter.field>
            <x-filter.field label="Role" for="user_role" col="toolbar-col-3">
                <select id="user_role" name="role" class="form-select">
                    <option value="">Semua role</option>
                    @foreach($roles as $roleOption)
                        <option value="{{ $roleOption }}" @selected($role === $roleOption)>{{ $roleLabels[$roleOption] ?? strtoupper($roleOption) }}</option>
                    @endforeach
                </select>
            </x-filter.field>
            <x-filter.field label="Status" for="user_status" col="toolbar-col-3">
                <select id="user_status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="active" @selected(($status ?? '') === 'active')>Aktif</option>
                    <option value="inactive" @selected(($status ?? '') === 'inactive')>Tidak aktif</option>
                </select>
            </x-filter.field>
            <x-filter.actions col="toolbar-col-12">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>Terapkan Filter</button>
                <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i>Reset</a>
            </x-filter.actions>
        </form>
    </x-filter.card>

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
