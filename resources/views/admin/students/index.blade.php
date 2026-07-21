@extends('layouts.app')

@section('page_title', 'Data Siswa')
@section('page_subtitle', 'Kelola data siswa peserta ekstrakurikuler')

@push('styles')
    <style>
        .member-group-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1rem;
        }

        .member-group-card {
            border-radius: 24px;
            border: 1px solid #dbe5f0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
            box-shadow: 0 16px 30px rgba(16, 35, 63, 0.06);
            overflow: hidden;
        }

        .member-group-card__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem 1.1rem;
            border-bottom: 1px solid #e5edf6;
        }

        .member-group-card__title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #17324e;
        }

        .member-group-card__meta {
            margin-top: 0.22rem;
            color: #6a7f98;
            font-size: 0.8rem;
        }

        .member-group-card__body {
            display: grid;
            gap: 0.8rem;
            padding: 1rem 1.1rem 1.1rem;
        }

        .member-entry {
            border: 1px solid #e3edf7;
            border-radius: 18px;
            background: #fbfdff;
            padding: 0.9rem;
        }

        .member-entry__top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .member-entry__name {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: #17324e;
        }

        .member-entry__sub {
            margin-top: 0.18rem;
            color: #6a7f98;
            font-size: 0.8rem;
        }

        .member-entry__details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.7rem;
            margin-top: 0.8rem;
        }

        .member-entry__detail-label {
            display: block;
            margin-bottom: 0.18rem;
            color: #6a7f98;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .member-entry__detail-value {
            margin: 0;
            color: #17324e;
            font-size: 0.84rem;
            line-height: 1.45;
        }

        .member-entry__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.85rem;
        }

        @media (max-width: 767.98px) {
            .member-group-grid {
                grid-template-columns: 1fr;
            }

            .member-entry__details {
                grid-template-columns: 1fr;
            }

            .member-entry__actions .btn,
            .member-entry__actions form {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $memberGroups = $students->getCollection()
            ->flatMap(function ($student) {
                return $student->registrations
                    ->map(function ($registration) use ($student) {
                        $registration->setRelation('student', $student);

                        return $registration;
                    });
            })
            ->filter(fn ($registration) => $registration->extracurricular)
            ->groupBy('extracurricular_id')
            ->map(function ($registrations) {
                $activity = $registrations->first()->extracurricular;

                return [
                    'activity' => $activity,
                    'members' => $registrations
                        ->unique(fn ($registration) => $registration->student_id.'-'.$registration->extracurricular_id)
                        ->values(),
                ];
            })
            ->sortBy(fn ($group) => $group['activity']->name ?? '')
            ->values();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i>Tambah Siswa</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama, email, NIS, atau kelas">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">Kelas</label>
                    <select name="class_name" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($classOptions as $option)
                            <option value="{{ $option }}" @selected($className === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Kegiatan yang Diikuti</label>
                    <select name="extracurricular_id" class="form-select">
                        <option value="">Semua Kegiatan</option>
                        @foreach($extracurricularOptions as $activity)
                            <option value="{{ $activity->id }}" @selected(($extracurricularId ?? null) === $activity->id)>{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-select">
                        <option value="">Semua</option>
                        <option value="L" @selected($gender === 'L')>Laki-laki</option>
                        <option value="P" @selected($gender === 'P')>Perempuan</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i>Cari</button></div>
                <div class="col-lg-1 col-md-2"><a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="section-header-inline">
                <div>
                    <h2>Anggota per Ekskul</h2>
                    <p>Setiap kartu menampilkan daftar anggota berdasarkan kegiatan yang sedang diikuti.</p>
                </div>
            </div>

            @if($memberGroups->isEmpty())
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-person-badge"></i></div>
                    <p class="mb-0">Data anggota tidak ditemukan.</p>
                </div>
            @else
                <div class="member-group-grid">
                    @foreach($memberGroups as $group)
                        <section class="member-group-card">
                            <div class="member-group-card__header">
                                <div>
                                    <h3 class="member-group-card__title">
                                        <a href="{{ route('admin.extracurriculars.show', $group['activity']) }}" class="text-decoration-none">
                                            {{ $group['activity']->name }}
                                        </a>
                                    </h3>
                                    <div class="member-group-card__meta">{{ $group['members']->count() }} anggota tampil pada halaman ini</div>
                                </div>
                                <a href="{{ route('admin.extracurriculars.show', $group['activity']) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i>Detail Ekskul
                                </a>
                            </div>
                            <div class="member-group-card__body">
                                @foreach($group['members'] as $registration)
                                    @php
                                        $student = $registration->student;
                                    @endphp
                                    <article class="member-entry">
                                        <div class="member-entry__top">
                                            <div>
                                                <h4 class="member-entry__name">{{ $student->user->name }}</h4>
                                                <div class="member-entry__sub">{{ $student->class_name ?: 'Kelas belum diisi' }} · NIS {{ $student->nis ?: '-' }}</div>
                                            </div>
                                            <span class="badge" data-status="{{ $student->user->is_active ? 'active' : 'inactive' }}">
                                                {{ $student->user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </div>
                                        <div class="member-entry__details">
                                            <div>
                                                <span class="member-entry__detail-label">Email</span>
                                                <p class="member-entry__detail-value">{{ $student->user->email }}</p>
                                            </div>
                                            <div>
                                                <span class="member-entry__detail-label">Status Pendaftaran</span>
                                                <p class="member-entry__detail-value">{{ ucfirst($registration->status) }}</p>
                                            </div>
                                        </div>
                                        <div class="member-entry__actions">
                                            <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i>Detail Siswa</a>
                                            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil-square"></i>Edit</a>
                                            <form method="post" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Hapus siswa ini?')">
                                                @csrf
                                                @method('delete')
                                                <button class="btn btn-outline-danger btn-sm" type="submit"><i class="bi bi-trash"></i>Hapus</button>
                                            </form>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="card-body">{{ $students->links() }}</div>
    </div>
@endsection
