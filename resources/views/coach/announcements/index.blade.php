@extends('layouts.app')

@section('page_title', 'Pengumuman Pembina')
@section('page_subtitle', 'Sampaikan informasi penting untuk peserta ekstrakurikuler binaan')

@section('content')
    <div class="card mb-3">
        <div class="card-header">Tambah Pengumuman</div>
        <div class="card-body">
            <form method="post" action="{{ route('coach.announcements.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Judul Pengumuman</label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Latihan dipindah" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select">
                        <option value="">Semua ekstrakurikuler binaan</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label d-block">Status</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="coachAnnouncementActive" checked>
                        <label class="form-check-label" for="coachAnnouncementActive">Tampilkan ke siswa</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Isi Pengumuman</label>
                    <textarea name="content" class="form-control" rows="4" placeholder="Tulis informasi penting untuk peserta" required></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-megaphone"></i>Simpan Pengumuman</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Daftar Pengumuman Saya</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Ekstrakurikuler</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($announcements as $announcement)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $announcement->title }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($announcement->content, 90) }}</div>
                            </td>
                            <td>{{ $announcement->extracurricular->name ?? 'Semua ekstrakurikuler binaan' }}</td>
                            <td><span class="badge" data-status="{{ $announcement->is_active ? 'active' : 'inactive' }}">{{ $announcement->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <form method="post" action="{{ route('coach.announcements.destroy', $announcement) }}" onsubmit="return confirm('Hapus pengumuman ini?')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-megaphone"></i></div>
                                    <p class="mb-0">Belum ada pengumuman.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body">{{ $announcements->links() }}</div>
    </div>
@endsection
