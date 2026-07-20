@csrf
@if(isset($student))
    @method('put')
@endif
<div class="row g-3">
    <div class="col-md-6">
        <div class="page-summary-banner h-100">
            <div class="data-point-label">Panduan</div>
            <p class="data-point-value mb-2">Lengkapi identitas siswa, kontak orang tua, dan status akun dalam satu alur.</p>
            <div class="helper-text mb-0">Password hanya wajib saat membuat akun baru. Saat edit, kosongkan jika tidak ingin mengubah password.</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="page-summary-banner h-100">
            <div class="data-point-label">Akses Akun</div>
            <p class="data-point-value mb-2">Data email dan password dipakai siswa untuk login ke sistem.</p>
            <div class="helper-text mb-0">Pastikan email aktif dan mudah diingat agar proses pendampingan lebih mudah.</div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-section-card">
            <h3 class="form-section-title">Identitas Siswa</h3>
            <p class="form-section-copy">Isi data utama siswa yang akan dipakai di dashboard, pendaftaran, dan laporan.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="student_name">Nama</label>
                    <input type="text" id="student_name" name="name" value="{{ old('name', $student->user->name ?? '') }}" class="form-control" placeholder="Nama lengkap siswa" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="student_email">Email</label>
                    <input type="email" id="student_email" name="email" value="{{ old('email', $student->user->email ?? '') }}" class="form-control" placeholder="contoh: siswa@sman1gorontalo.sch.id" required>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="student_nis">NIS</label>
                    <input type="text" id="student_nis" name="nis" value="{{ old('nis', $student->nis ?? '') }}" class="form-control" placeholder="Nomor induk siswa" required>
                    @error('nis')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="student_class_name">Kelas</label>
                    <input type="text" id="student_class_name" name="class_name" value="{{ old('class_name', $student->class_name ?? '') }}" class="form-control" placeholder="Contoh: X IPA 1" required>
                    @error('class_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="student_gender">Jenis Kelamin</label>
                    <select id="student_gender" name="gender" class="form-select" required>
                        <option value="L" @selected(old('gender', $student->gender ?? '') === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('gender', $student->gender ?? '') === 'P')>Perempuan</option>
                    </select>
                    @error('gender')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="student_date_of_birth">Tanggal Lahir</label>
                    <input type="date" id="student_date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', isset($student) && $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}" class="form-control">
                    @error('date_of_birth')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="student_phone">No. Telepon</label>
                    <input type="text" id="student_phone" name="phone" value="{{ old('phone', $student->user->phone ?? '') }}" class="form-control" placeholder="08xxxxxxxxxx">
                    @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label d-block">Status Akun</label>
                    <div class="data-point h-100 d-flex align-items-center">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="student_active" @checked(old('is_active', $student->user->is_active ?? true))>
                            <label class="form-check-label fw-semibold" for="student_active">Aktif</label>
                        </div>
                    </div>
                    @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="student_address">Alamat</label>
                    <textarea id="student_address" name="address" class="form-control" rows="2" placeholder="Alamat siswa">{{ old('address', $student->address ?? $student->user->address ?? '') }}</textarea>
                    @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-section-card">
            <h3 class="form-section-title">Kontak Orang Tua atau Wali</h3>
            <p class="form-section-copy">Informasi ini dipakai untuk kebutuhan komunikasi saat pendaftaran dan pembinaan siswa.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="student_parent_name">Nama Orang Tua</label>
                    <input type="text" id="student_parent_name" name="parent_name" value="{{ old('parent_name', $student->parent_name ?? '') }}" class="form-control" placeholder="Nama orang tua/wali">
                    @error('parent_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="student_parent_phone">No. Telepon Orang Tua</label>
                    <input type="text" id="student_parent_phone" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone ?? '') }}" class="form-control" placeholder="08xxxxxxxxxx">
                    @error('parent_phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="form-section-card">
            <h3 class="form-section-title">Keamanan Akun</h3>
            <p class="form-section-copy">Buat password awal siswa atau perbarui password jika diperlukan.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="student_password">Password {{ isset($student) ? '(opsional)' : '' }}</label>
                    <input type="password" id="student_password" name="password" class="form-control" placeholder="Minimal 8 karakter" {{ isset($student) ? '' : 'required' }}>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="student_password_confirmation">Konfirmasi Password</label>
                    <input type="password" id="student_password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password" {{ isset($student) ? '' : 'required' }}>
                </div>
            </div>
        </div>
    </div>
</div>
