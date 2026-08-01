@props(['student'])

@php
    $hasHistory = collect([
        $student->registrations_count ?? 0,
        $student->attendances_count ?? 0,
        $student->assessments_count ?? 0,
        $student->talent_test_participants_count ?? 0,
        $student->talent_test_results_count ?? 0,
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
            aria-label="Buka aksi untuk {{ $student->user?->name ?? 'siswa' }}"
        >
            <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a href="{{ route('admin.students.show', $student) }}" class="dropdown-item">
                <i class="bi bi-eye" aria-hidden="true"></i>Detail
            </a>
            <a href="{{ route('admin.students.edit', $student) }}" class="dropdown-item">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>Edit data
            </a>
            <a href="{{ route('admin.students.show', $student) }}" class="dropdown-item">
                <i class="bi bi-collection" aria-hidden="true"></i>Lihat keanggotaan
            </a>
            <div class="dropdown-divider"></div>
            @if($hasHistory)
                <a href="{{ route('admin.students.edit', $student) }}" class="dropdown-item">
                    <i class="bi bi-person-slash" aria-hidden="true"></i>Nonaktifkan akun
                </a>
                <span class="dropdown-item-text student-row-actions__hint">Riwayat tersimpan, akun tidak dapat dihapus.</span>
            @else
                <form
                    method="post"
                    action="{{ route('admin.students.destroy', $student) }}"
                    onsubmit="return confirm('Hapus permanen siswa ini? Akun ini belum memiliki riwayat kegiatan dan tidak dapat dipulihkan setelah dihapus.')"
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
