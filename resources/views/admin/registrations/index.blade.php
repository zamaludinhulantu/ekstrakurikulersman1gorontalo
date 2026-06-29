@extends('layouts.app')

@section('page_title', 'Pendaftar Ekstrakurikuler')
@section('page_subtitle', 'Periksa pendaftaran siswa dan berikan keputusan dengan tampilan yang lebih mudah dipantau')

@section('content')
    <div class="card mb-3">
        <div class="card-body toolbar-card">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">Filter Data Pendaftar</h2>
                    <p class="toolbar-hint mb-0">Gunakan filter agar proses verifikasi lebih cepat, terutama saat data pendaftar mulai banyak.</p>
                </div>
                <div class="quick-actions">
                    <a href="{{ route('admin.registrations.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-outline-danger">
                        <i class="bi bi-file-earmark-pdf"></i>Unduh PDF
                    </a>
                    <a href="{{ route('admin.registrations.export', array_merge(request()->query(), ['format' => 'xls'])) }}" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel"></i>Unduh Excel
                    </a>
                </div>
            </div>
            <form class="toolbar-grid">
                <div class="toolbar-col-4">
                    <label class="form-label" for="search">Cari siswa</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="Masukkan nama siswa">
                </div>
                <div class="toolbar-col-4">
                    <label class="form-label" for="extracurricular_id">Ekstrakurikuler</label>
                    <select id="extracurricular_id" name="extracurricular_id" class="form-select">
                        <option value="">Semua ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string)$extracurricularId === (string)$item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="pending" @selected($status==='pending')>Menunggu</option>
                        <option value="approved" @selected($status==='approved')>Diterima</option>
                        <option value="rejected" @selected($status==='rejected')>Ditolak</option>
                    </select>
                </div>
                <div class="toolbar-col-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i>Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Pendaftar</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Siswa</th>
                        <th>Ekstrakurikuler</th>
                        <th>Tanggal Daftar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($registrations as $registration)
                        <tr>
                            <td>
                                <strong>{{ $registration->student->user->name ?? '-' }}</strong><br>
                                <small class="text-muted">NIS: {{ $registration->student->nis ?? '-' }}</small>
                            </td>
                            <td>{{ $registration->extracurricular->name ?? '-' }}</td>
                            <td>{{ optional($registration->registration_date)->format('d-m-Y') }}</td>
                            <td><span class="badge" data-status="{{ $registration->status }}">{{ $registration->status }}</span></td>
                            <td>
                                <form method="post" action="{{ route('admin.registrations.update-status', $registration) }}" class="row g-2">
                                    @csrf
                                    @method('patch')
                                    <div class="col-12">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="pending" @selected($registration->status==='pending')>Menunggu</option>
                                            <option value="approved" @selected($registration->status==='approved')>Diterima</option>
                                            <option value="rejected" @selected($registration->status==='rejected')>Ditolak</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" name="notes" value="{{ $registration->notes }}" class="form-control form-control-sm" placeholder="Catatan verifikasi">
                                    </div>
                                    <div class="col-12 row-actions">
                                        <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-eye"></i>Detail</button>
                                        <button class="btn btn-sm btn-primary" type="submit" onclick="this.form.querySelector('[name=status]').value='approved'"><i class="bi bi-check-circle"></i>Terima</button>
                                        <button class="btn btn-sm btn-outline-danger" type="submit" onclick="this.form.querySelector('[name=status]').value='rejected'"><i class="bi bi-x-circle"></i>Tolak</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                                    <p class="mb-0">Data belum tersedia.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mobile-stack-table p-3">
                @forelse($registrations as $registration)
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <div>
                                <h3 class="mobile-data-card-title">{{ $registration->student->user->name ?? '-' }}</h3>
                                <div class="small text-muted">NIS: {{ $registration->student->nis ?? '-' }}</div>
                            </div>
                            <span class="badge" data-status="{{ $registration->status }}">{{ $registration->status }}</span>
                        </div>
                        <div class="mobile-data-list mb-3">
                            <div><span class="mobile-data-item-label">Ekstrakurikuler</span><p class="mobile-data-item-value">{{ $registration->extracurricular->name ?? '-' }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal daftar</span><p class="mobile-data-item-value">{{ optional($registration->registration_date)->format('d-m-Y') }}</p></div>
                        </div>
                        <form method="post" action="{{ route('admin.registrations.update-status', $registration) }}">
                            @csrf
                            @method('patch')
                            <div class="mb-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending" @selected($registration->status==='pending')>Menunggu</option>
                                    <option value="approved" @selected($registration->status==='approved')>Diterima</option>
                                    <option value="rejected" @selected($registration->status==='rejected')>Ditolak</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <input type="text" name="notes" value="{{ $registration->notes }}" class="form-control" placeholder="Catatan verifikasi">
                            </div>
                            <div class="row-actions">
                                <button class="btn btn-outline-primary flex-fill" type="submit"><i class="bi bi-eye"></i>Detail</button>
                                <button class="btn btn-primary flex-fill" type="submit" onclick="this.form.querySelector('[name=status]').value='approved'"><i class="bi bi-check-circle"></i>Terima</button>
                                <button class="btn btn-outline-danger flex-fill" type="submit" onclick="this.form.querySelector('[name=status]').value='rejected'"><i class="bi bi-x-circle"></i>Tolak</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                        <p class="mb-0">Data belum tersedia.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $registrations->links() }}</div>
    </div>
@endsection
