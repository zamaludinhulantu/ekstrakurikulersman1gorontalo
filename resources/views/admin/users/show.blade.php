@extends('layouts.app')

@section('page_title', 'Detail Pengguna')
@section('page_subtitle', $user->name)

@section('content')
    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <span>Informasi Pengguna</span>
                    <span class="badge" data-status="{{ $user->is_active ? 'active' : 'inactive' }}">{{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="data-point h-100">
                                <div class="data-point-label">Nama</div>
                                <p class="data-point-value"><strong>{{ $user->name }}</strong></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-point h-100">
                                <div class="data-point-label">Email</div>
                                <p class="data-point-value">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-point h-100">
                                <div class="data-point-label">Role</div>
                                <p class="data-point-value"><span class="badge badge-status-secondary">{{ strtoupper($user->role) }}</span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="data-point h-100">
                                <div class="data-point-label">Telepon</div>
                                <p class="data-point-value">{{ $user->phone ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="data-point">
                                <div class="data-point-label">Alamat</div>
                                <p class="data-point-value">{{ $user->address ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="form-actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary"><i class="bi bi-pencil-square"></i>Edit Pengguna</a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header">Ringkasan</div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <div class="title">Status Akun</div>
                            <div class="small text-muted mt-1">Akun saat ini {{ $user->is_active ? 'aktif dan dapat digunakan untuk login.' : 'nonaktif dan tidak dapat digunakan untuk login.' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="title">Peran Pengguna</div>
                            <div class="small text-muted mt-1">Role <strong>{{ strtoupper($user->role) }}</strong> menentukan akses dashboard dan menu sistem.</div>
                        </div>
                        <div class="info-item">
                            <div class="title">Kontak</div>
                            <div class="small text-muted mt-1">{{ $user->phone ?: 'Nomor telepon belum diisi.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
