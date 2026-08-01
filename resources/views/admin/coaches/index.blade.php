@extends('layouts.app')

@section('page_title', 'Data Pembina')
@section('page_subtitle', 'Kelola akun dan penugasan pembina ekstrakurikuler')

@section('content')
    @php
        $selectedActivity = $extracurricularOptions->firstWhere('id', (int) $extracurricularId);
        $hasFilters = filled($search) || filled($status) || filled($extracurricularId) || filled($assignment) || filled($profileStatus);
        $filterUrl = function (array $remove) {
            $query = request()->except([...$remove, 'page']);
            return route('admin.coaches.index').($query ? '?'.http_build_query($query) : '');
        };
    @endphp

    <div class="student-summary-grid mb-3" aria-label="Ringkasan pembina">
        <a href="{{ route('admin.coaches.index') }}" class="dashboard-stat-card text-decoration-none">
            <span class="dashboard-stat-label">Total Pembina</span>
            <strong>{{ $coachSummary['total'] }}</strong>
            <span class="dashboard-stat-caption">Seluruh akun pembina</span>
        </a>
        <a href="{{ route('admin.coaches.index', ['status' => 'active']) }}" class="dashboard-stat-card text-decoration-none">
            <span class="dashboard-stat-label">Pembina Aktif</span>
            <strong>{{ $coachSummary['active'] }}</strong>
            <span class="dashboard-stat-caption">Akun dapat digunakan</span>
        </a>
        <a href="{{ route('admin.coaches.index', ['status' => 'inactive']) }}" class="dashboard-stat-card text-decoration-none">
            <span class="dashboard-stat-label">Tidak Aktif</span>
            <strong>{{ $coachSummary['inactive'] }}</strong>
            <span class="dashboard-stat-caption">Akun dinonaktifkan</span>
        </a>
        <a href="{{ route('admin.coaches.index', ['assignment' => 'unassigned']) }}" class="dashboard-stat-card text-decoration-none">
            <span class="dashboard-stat-label">Belum Ditugaskan</span>
            <strong>{{ $coachSummary['unassigned'] }}</strong>
            <span class="dashboard-stat-caption">Tanpa kegiatan binaan</span>
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="section-title mb-1">Cari dan Filter</h2>
                <p class="text-muted small mb-0">Temukan pembina berdasarkan identitas, akun, dan penugasan.</p>
            </div>
            <a href="{{ route('admin.coaches.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle" aria-hidden="true"></i>Tambah Pembina
            </a>
        </div>
        <div class="card-body">
            <form method="get" class="coach-filter-grid">
                <div class="coach-filter-grid__search">
                    <label class="form-label" for="coach_search">Nama, email, atau NIP</label>
                    <input id="coach_search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Ketik kata pencarian">
                </div>
                <div>
                    <label class="form-label" for="coach_status">Status akun</label>
                    <select id="coach_status" name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="coach-filter-grid__activity">
                    <label class="form-label" for="coach_activity">Kegiatan binaan</label>
                    <select id="coach_activity" name="extracurricular_id" class="form-select">
                        <option value="">Semua kegiatan</option>
                        @foreach($extracurricularOptions as $activity)
                            <option value="{{ $activity->id }}" @selected((int) $extracurricularId === $activity->id)>{{ $activity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="coach_assignment">Penugasan</label>
                    <select id="coach_assignment" name="assignment" class="form-select">
                        <option value="">Semua penugasan</option>
                        <option value="assigned" @selected($assignment === 'assigned')>Sudah ditugaskan</option>
                        <option value="unassigned" @selected($assignment === 'unassigned')>Belum ditugaskan</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="coach_profile">Kelengkapan profil</label>
                    <select id="coach_profile" name="profile_status" class="form-select">
                        <option value="">Semua profil</option>
                        <option value="complete" @selected($profileStatus === 'complete')>Lengkap</option>
                        <option value="incomplete" @selected($profileStatus === 'incomplete')>Belum lengkap</option>
                    </select>
                </div>
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="coach-filter-grid__actions">
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search" aria-hidden="true"></i>Terapkan</button>
                    <a href="{{ route('admin.coaches.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat" aria-hidden="true"></i>Reset</a>
                </div>
            </form>

            @if($hasFilters)
                <div class="active-filter-list mt-3" aria-label="Filter aktif">
                    @if(filled($search))
                        <a href="{{ $filterUrl(['search']) }}" class="active-filter-chip"><strong>Pencarian:</strong> {{ $search }} <i class="bi bi-x-lg" aria-hidden="true"></i></a>
                    @endif
                    @if(filled($status))
                        <a href="{{ $filterUrl(['status']) }}" class="active-filter-chip"><strong>Status:</strong> {{ $status === 'active' ? 'Aktif' : 'Tidak Aktif' }} <i class="bi bi-x-lg" aria-hidden="true"></i></a>
                    @endif
                    @if($selectedActivity)
                        <a href="{{ $filterUrl(['extracurricular_id']) }}" class="active-filter-chip"><strong>Kegiatan:</strong> {{ $selectedActivity->name }} <i class="bi bi-x-lg" aria-hidden="true"></i></a>
                    @endif
                    @if(filled($assignment))
                        <a href="{{ $filterUrl(['assignment']) }}" class="active-filter-chip"><strong>Penugasan:</strong> {{ $assignment === 'assigned' ? 'Sudah ditugaskan' : 'Belum ditugaskan' }} <i class="bi bi-x-lg" aria-hidden="true"></i></a>
                    @endif
                    @if(filled($profileStatus))
                        <a href="{{ $filterUrl(['profile_status']) }}" class="active-filter-chip"><strong>Profil:</strong> {{ $profileStatus === 'complete' ? 'Lengkap' : 'Belum lengkap' }} <i class="bi bi-x-lg" aria-hidden="true"></i></a>
                    @endif
                    <a href="{{ route('admin.coaches.index') }}" class="btn btn-sm btn-link">Hapus semua filter</a>
                </div>
            @endif
        </div>
    </div>

    <div class="card student-directory-card coach-directory-card directory-table-card">
        <div class="card-header dashboard-panel-header">
            <div class="directory-table-card__heading">
                <span class="directory-table-card__icon"><i class="bi bi-person-workspace" aria-hidden="true"></i></span>
                <div>
                    <strong>Daftar Pembina</strong>
                    <small>Identitas, kegiatan binaan, status akun, dan penugasan</small>
                </div>
            </div>
            <span class="directory-table-card__count">{{ number_format($coaches->total()) }} pembina</span>
        </div>
        <div class="desktop-table table-responsive">
            <table class="table mb-0 student-directory-table coach-directory-table directory-data-table">
                <colgroup>
                    <col class="directory-col-coach">
                    <col class="directory-col-coach-nip">
                    <col class="directory-col-coach-contact">
                    <col class="directory-col-coach-activity">
                    <col class="directory-col-coach-status">
                    <col class="directory-col-coach-actions">
                </colgroup>
                <thead>
                <tr>
                    <th class="student-directory-table__name"><x-student.sort-link column="name" label="Pembina" :current-sort="$sort" :direction="$direction" /></th>
                    <th><x-student.sort-link column="nip" label="NIP" :current-sort="$sort" :direction="$direction" /></th>
                    <th class="student-directory-table__contact">Kontak</th>
                    <th class="student-directory-table__activities"><x-student.sort-link column="activities_count" label="Kegiatan Binaan" :current-sort="$sort" :direction="$direction" /></th>
                    <th class="directory-status-heading"><x-student.sort-link column="status" label="Status Akun" :current-sort="$sort" :direction="$direction" /></th>
                    <th class="text-center table-action-col table-action-col--compact">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($coaches as $coach)
                    <tr>
                        <td><x-coach.identity :coach="$coach" /></td>
                        <td><span class="coach-nip" title="{{ $coach->nip ?: 'NIP belum diisi' }}">{{ $coach->nip ?: 'NIP belum diisi' }}</span></td>
                        <td>
                            <div class="student-contact">
                                <span title="{{ $coach->user?->email }}">{{ $coach->user?->email ?: '-' }}</span>
                                <small title="{{ $coach->user?->phone }}">{{ $coach->user?->phone ?: 'Telepon belum diisi' }}</small>
                            </div>
                        </td>
                        <td><x-coach.activity-badges :coach="$coach" /></td>
                        <td class="directory-status-cell"><span class="badge" data-status="{{ $coach->user?->is_active ? 'active' : 'inactive' }}">{{ $coach->user?->is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                        <td class="directory-actions-cell table-action-col table-action-col--compact"><x-coach.admin-actions :coach="$coach" class="justify-content-center" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-person-workspace" aria-hidden="true"></i></div>
                                <p class="mb-2">{{ $hasFilters ? 'Tidak ada pembina yang sesuai dengan filter.' : 'Belum ada data pembina.' }}</p>
                                <div class="d-flex justify-content-center gap-2">
                                    @if($hasFilters)<a href="{{ route('admin.coaches.index') }}" class="btn btn-outline-secondary">Reset Filter</a>@endif
                                    <a href="{{ route('admin.coaches.create') }}" class="btn btn-primary">Tambah Pembina</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mobile-stack-table p-3">
            @forelse($coaches as $coach)
                <article class="mobile-data-card">
                    <div class="mobile-data-card-header">
                        <x-coach.identity :coach="$coach" />
                        <span class="badge" data-status="{{ $coach->user?->is_active ? 'active' : 'inactive' }}">{{ $coach->user?->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                    <div class="mobile-data-list">
                        <div><span class="mobile-data-item-label">NIP</span><p class="mobile-data-item-value coach-nip">{{ $coach->nip ?: 'NIP belum diisi' }}</p></div>
                        <div><span class="mobile-data-item-label">Kontak</span><p class="mobile-data-item-value text-break">{{ $coach->user?->email ?: '-' }}<br><small>{{ $coach->user?->phone ?: 'Telepon belum diisi' }}</small></p></div>
                        <div><span class="mobile-data-item-label">Kegiatan Binaan</span><x-coach.activity-badges :coach="$coach" :limit="1" /></div>
                    </div>
                    <div class="student-directory-card__footer"><x-coach.admin-actions :coach="$coach" /></div>
                </article>
            @empty
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-person-workspace" aria-hidden="true"></i></div>
                    <p class="mb-2">{{ $hasFilters ? 'Tidak ada pembina yang sesuai dengan filter.' : 'Belum ada data pembina.' }}</p>
                    @if($hasFilters)<a href="{{ route('admin.coaches.index') }}" class="btn btn-outline-secondary">Reset Filter</a>@endif
                </div>
            @endforelse
        </div>

        <div class="card-body student-directory-card__footer">
            <x-student.pagination :paginator="$coaches" noun="pembina" :per-page="$perPage" />
        </div>
    </div>
@endsection
