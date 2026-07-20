@csrf
@if(isset($user))
    @method('put')
@endif

<div class="row g-3">
    <div class="col-12">
        <div class="page-summary-banner">
            <div class="data-point-label">Informasi Utama</div>
            <p class="data-point-value mb-2">Isi data akun pengguna dengan lengkap. Password hanya wajib saat membuat akun baru.</p>
            <div class="helper-text mb-0">Pilih role dengan benar karena ini menentukan dashboard dan izin akses pengguna.</div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-section-card">
            <h3 class="form-section-title">Profil Pengguna</h3>
            <p class="form-section-copy">Atur identitas, role, dan status aktif untuk akun pengguna ini.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="user_name">Nama</label>
                    <input type="text" id="user_name" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control" placeholder="Nama lengkap pengguna" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="user_email">Email</label>
                    <input type="email" id="user_email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-control" placeholder="contoh: admin@gmail.com" required>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="user_role">Role</label>
                    <select id="user_role" name="role" class="form-select" required>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $user->role ?? '') === $role)>{{ strtoupper($role) }}</option>
                        @endforeach
                    </select>
                    @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="user_phone">No. Telepon</label>
                    <input type="text" id="user_phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="form-control" placeholder="08xxxxxxxxxx">
                    @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label d-block">Status Akun</label>
                    <div class="data-point h-100 d-flex align-items-center">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $user->is_active ?? true))>
                            <label class="form-check-label fw-semibold" for="is_active">Aktif</label>
                        </div>
                    </div>
                    @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="user_address">Alamat</label>
                    <textarea id="user_address" name="address" class="form-control" rows="3" placeholder="Alamat pengguna">{{ old('address', $user->address ?? '') }}</textarea>
                    @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-section-card">
            <h3 class="form-section-title">Keamanan Akses</h3>
            <p class="form-section-copy">Buat password baru atau perbarui password lama jika diperlukan.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="user_password">Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '' }}</label>
                    <input type="password" id="user_password" name="password" class="form-control" placeholder="Minimal 8 karakter" {{ isset($user) ? '' : 'required' }}>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="user_password_confirmation">Konfirmasi Password</label>
                    <input type="password" id="user_password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password" {{ isset($user) ? '' : 'required' }}>
                </div>
            </div>
        </div>
    </div>
</div>
