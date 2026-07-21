<table border="1">
    <thead>
        <tr>
            <th colspan="8" style="font-size: 16px; font-weight: 700; text-align: left; background: #eef5ff;">Laporan Data Siswa</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Dicetak pada {{ now()->format('d-m-Y H:i') }} | Total data: {{ $students->count() }}</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Pencarian: {{ $filterSummary['search'] }}</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Kelas: {{ $filterSummary['class_name'] }} | Kategori: {{ $filterSummary['category'] }} | Kegiatan: {{ $filterSummary['extracurricular'] }}</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: left;">Jenis Kelamin: {{ $filterSummary['gender'] }} | Status: {{ $filterSummary['status'] }}</th>
        </tr>
        <tr>
            <th style="background: #eef5ff;">No</th>
            <th style="background: #eef5ff;">Nama</th>
            <th style="background: #eef5ff;">NIS</th>
            <th style="background: #eef5ff;">Kelas</th>
            <th style="background: #eef5ff;">Email</th>
            <th style="background: #eef5ff;">Jenis Kelamin</th>
            <th style="background: #eef5ff;">Ekskul Diikuti</th>
            <th style="background: #eef5ff;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $student)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $controller->exportValue($student->user->name ?? '-') }}</td>
                <td style="mso-number-format:'\@';">{{ $controller->exportValue($student->nis) }}</td>
                <td>{{ $controller->exportValue($student->class_name) }}</td>
                <td>{{ $controller->exportValue($student->user->email ?? '-') }}</td>
                <td>{{ $controller->exportValue($controller->genderLabel($student->gender)) }}</td>
                <td>{{ $controller->exportValue($controller->studentActivityNames($student, $extracurricularId ?? null)) }}</td>
                <td>{{ $controller->exportValue($controller->studentStatusLabel($student)) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Data siswa tidak ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
</table>
