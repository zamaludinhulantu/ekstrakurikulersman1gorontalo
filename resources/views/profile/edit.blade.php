@extends('layouts.app')

@section('page_title', 'Profil Pengguna')
@section('page_subtitle', 'Perbarui informasi akun dan keamanan')

@section('content')
    <div class="card">
        <div class="card-header">Pengaturan Profil</div>
        <div class="card-body">
            <form method="post" action="{{ route('profile.update') }}" class="row g-3">
                @csrf
                @method('put')

                <div class="col-12">
                    <div class="data-point">
                        <div class="data-point-label">Ringkasan</div>
                        <p class="data-point-value mb-0">Perbarui data kontak dan keamanan akun. Kosongkan password bila tidak ingin mengubahnya.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control" placeholder="Contoh: 08xxxxxxxxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}" class="form-control" placeholder="Alamat rumah">
                </div>

                @if($user->hasRole(\App\Models\User::ROLE_STUDENT) && $user->student)
                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" class="form-select">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="L" @selected(old('gender', $user->student->gender) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender', $user->student->gender) === 'P')>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($user->student->date_of_birth)->format('Y-m-d')) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Orang Tua / Wali</label>
                        <input type="text" name="parent_name" value="{{ old('parent_name', $user->student->parent_name) }}" class="form-control" placeholder="Nama orang tua atau wali">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No. Telepon Orang Tua</label>
                        <input type="text" name="parent_phone" value="{{ old('parent_phone', $user->student->parent_phone) }}" class="form-control" placeholder="08xxxxxxxxxx">
                    </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                </div>
                <div class="col-12">
                    <div class="form-actions mt-2">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Simpan Perubahan</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
