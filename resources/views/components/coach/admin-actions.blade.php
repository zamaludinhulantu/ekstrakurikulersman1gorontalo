@props(['coach'])

@php
    $hasHistory = collect([
        $coach->extracurriculars_count ?? 0,
        $coach->schedules_count ?? 0,
        $coach->assessments_count ?? 0,
        $coach->talent_test_results_count ?? 0,
        $coach->user?->announcements_count ?? 0,
        $coach->user?->articles_count ?? 0,
        $coach->user?->generated_reports_count ?? 0,
    ])->contains(fn ($count) => (int) $count > 0);
@endphp

<div {{ $attributes->class(['student-row-actions']) }}>
    <div class="dropdown">
        <button
            class="btn btn-sm btn-light student-row-actions__menu"
            type="button"
            data-bs-toggle="dropdown"
            data-bs-display="static"
            aria-expanded="false"
            aria-label="Buka aksi untuk {{ $coach->user?->name ?? 'pembina' }}"
        >
            <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a href="{{ route('admin.coaches.show', $coach) }}" class="dropdown-item">
                <i class="bi bi-eye" aria-hidden="true"></i>Detail
            </a>
            <a href="{{ route('admin.coaches.edit', $coach) }}" class="dropdown-item">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>Edit data
            </a>
            <a href="{{ route('admin.coaches.edit', $coach) }}#coach_extracurricular_picker" class="dropdown-item">
                <i class="bi bi-diagram-3" aria-hidden="true"></i>Atur penugasan
            </a>
            <div class="dropdown-divider"></div>
            @if($hasHistory)
                <a href="{{ route('admin.coaches.edit', $coach) }}#coach_active" class="dropdown-item">
                    <i class="bi bi-person-slash" aria-hidden="true"></i>{{ $coach->user?->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}
                </a>
                <span class="dropdown-item-text student-row-actions__hint">Penugasan atau riwayat tersimpan, akun tidak dapat dihapus.</span>
            @else
                <form
                    method="post"
                    action="{{ route('admin.coaches.destroy', $coach) }}"
                    onsubmit="return confirm('Hapus permanen pembina ini? Akun belum memiliki penugasan atau riwayat dan tidak dapat dipulihkan setelah dihapus.')"
                >
                    @csrf
                    @method('delete')
                    <button class="dropdown-item text-danger" type="submit">
                        <i class="bi bi-trash" aria-hidden="true"></i>Hapus permanen
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
