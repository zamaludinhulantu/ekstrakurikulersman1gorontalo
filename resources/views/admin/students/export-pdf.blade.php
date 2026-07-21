<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Data Siswa</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        .meta { margin-bottom: 16px; color: #64748b; font-size: 10px; }
        .summary { margin-bottom: 16px; padding: 10px 12px; border: 1px solid #dbe5f0; background: #f8fbff; }
        .summary p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dbe5f0; padding: 7px 8px; vertical-align: top; text-align: left; }
        th { background: #eef5ff; font-size: 10px; }
        td { font-size: 10px; }
    </style>
</head>
<body>
    <h1>Laporan Data Siswa</h1>
    <div class="meta">
        Dicetak pada {{ now()->format('d-m-Y H:i') }}<br>
        Total data: {{ $students->count() }}
    </div>

    <div class="summary">
        <p><strong>Pencarian:</strong> {{ $filterSummary['search'] }}</p>
        <p><strong>Kelas:</strong> {{ $filterSummary['class_name'] }}</p>
        <p><strong>Kategori:</strong> {{ $filterSummary['category'] }}</p>
        <p><strong>Kegiatan:</strong> {{ $filterSummary['extracurricular'] }}</p>
        <p><strong>Jenis Kelamin:</strong> {{ $filterSummary['gender'] }}</p>
        <p><strong>Status:</strong> {{ $filterSummary['status'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 36px;">No</th>
                <th>Nama</th>
                <th>NIS</th>
                <th>Kelas</th>
                <th>Email</th>
                <th>Jenis Kelamin</th>
                <th>Ekskul Diikuti</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $controller->exportValue($student->user->name ?? '-') }}</td>
                    <td>{{ $controller->exportValue($student->nis) }}</td>
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
</body>
</html>
