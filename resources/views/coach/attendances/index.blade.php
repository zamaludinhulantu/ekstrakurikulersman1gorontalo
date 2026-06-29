@extends('layouts.app')

@section('page_title', 'Kelola Presensi Peserta')
@section('page_subtitle', 'Isi kehadiran peserta berdasarkan jadwal kegiatan')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Pilih Jadwal</label>
                    <select name="schedule_id" class="form-select" required>
                        <option value="">- Pilih Jadwal -</option>
                        @foreach($schedules as $schedule)
                            <option value="{{ $schedule->id }}" @selected((string)request('schedule_id') === (string)$schedule->id)>
                                {{ optional($schedule->activity_date)->format('d-m-Y') }} | {{ $schedule->extracurricular->name ?? '-' }} | {{ $schedule->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-eye"></i>Tampilkan</button></div>
                <div class="col-md-2">
                    <div class="dropdown w-100">
                        <button class="btn btn-outline-success w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-download"></i>Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end w-100">
                            <li><a class="dropdown-item" href="{{ route('coach.attendances.export', array_merge(request()->query(), ['format' => 'csv'])) }}">Unduh CSV</a></li>
                            <li><a class="dropdown-item" href="{{ route('coach.attendances.export', array_merge(request()->query(), ['format' => 'xls'])) }}">Unduh Excel</a></li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedSchedule)
        <div class="card">
            <div class="card-header">
                Presensi: {{ $selectedSchedule->extracurricular->name }} - {{ $selectedSchedule->title }}
                ({{ optional($selectedSchedule->activity_date)->format('d-m-Y') }})
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('coach.attendances.save', $selectedSchedule) }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($participants as $index => $participant)
                                @php $attendance = $attendanceMap[$participant->student_id] ?? null; @endphp
                                <tr>
                                    <td>
                                        {{ $participant->student->user->name ?? '-' }}
                                        <input type="hidden" name="rows[{{ $index }}][student_id]" value="{{ $participant->student_id }}">
                                    </td>
                                    <td>
                                        <select name="rows[{{ $index }}][status]" class="form-select form-select-sm" required>
                                            <option value="present" @selected(($attendance->status ?? '') === 'present')>Hadir</option>
                                            <option value="absent" @selected(($attendance->status ?? '') === 'absent')>Alpa</option>
                                            <option value="sick" @selected(($attendance->status ?? '') === 'sick')>Sakit</option>
                                            <option value="permission" @selected(($attendance->status ?? '') === 'permission')>Izin</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="rows[{{ $index }}][notes]" value="{{ $attendance->notes ?? '' }}" class="form-control form-control-sm" placeholder="Catatan presensi"></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="empty-state py-3">
                                            <p class="mb-0">Belum ada peserta aktif.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($participants->isNotEmpty())
                        <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Simpan Presensi</button>
                    @endif
                </form>
            </div>
        </div>
    @endif
@endsection
