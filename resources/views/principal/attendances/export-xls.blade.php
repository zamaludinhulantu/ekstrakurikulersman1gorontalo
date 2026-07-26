<table border="1">
    <thead>
        <tr>
            <th colspan="8" style="font-size: 16px; font-weight: 700; text-align: left; background: #eef5ff;">Laporan Presensi Kepala Sekolah</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Dicetak pada {{ now()->format('d-m-Y H:i') }} | Total data: {{ $attendances->count() }}</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Pencarian: {{ $filterSummary['search'] }}</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Ekstrakurikuler: {{ $filterSummary['extracurricular'] }} | Kategori: {{ $filterSummary['category'] }} | Pembina: {{ $filterSummary['coach'] }}</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Kelas: {{ $filterSummary['class_name'] }} | Status: {{ $filterSummary['status'] }} | Periode: {{ $filterSummary['period'] }}</th>
        </tr>
        <tr>
            <th style="background: #eef5ff;">No</th>
            <th style="background: #eef5ff;">Siswa</th>
            <th style="background: #eef5ff;">Ekstrakurikuler</th>
            <th style="background: #eef5ff;">Pembina</th>
            <th style="background: #eef5ff;">Jadwal</th>
            <th style="background: #eef5ff;">Tanggal</th>
            <th style="background: #eef5ff;">Status</th>
            <th style="background: #eef5ff;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($attendances as $attendance)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $controller->exportValue($attendance->student->user->name ?? '-') }}</td>
                <td>{{ $controller->exportValue($attendance->extracurricular->name ?? '-') }}</td>
                <td>{{ $controller->exportValue($attendance->schedule->coach->user->name ?? $attendance->extracurricular->coach_names) }}</td>
                <td>{{ $controller->exportValue($attendance->schedule->title ?? '-') }}</td>
                <td>{{ $controller->exportValue(optional($attendance->schedule->activity_date)->format('d-m-Y') ?: '-') }}</td>
                <td>{{ $controller->exportValue($controller->mapStatusLabel($attendance->status)) }}</td>
                <td>{{ $controller->exportValue($attendance->notes ?? '-') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Data presensi tidak ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
</table>
