<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Pendaftar Ekstrakurikuler</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        .header {
            margin-bottom: 14px;
        }

        .header h1 {
            margin: 0 0 6px;
            font-size: 18px;
        }

        .meta {
            font-size: 10px;
            color: #4b5563;
            margin-bottom: 4px;
        }

        .filters {
            margin-top: 10px;
            padding: 10px 12px;
            border: 1px solid #dbe5f0;
            background: #f8fbff;
        }

        .filters-row {
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #dbe5f0;
            padding: 6px 7px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        th {
            background: #eef4ff;
            font-size: 10px;
        }

        .empty {
            text-align: center;
            color: #6b7280;
            padding: 18px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Pendaftar Kegiatan</h1>
        <div class="meta">Dicetak pada {{ now()->format('d-m-Y H:i') }}</div>
        <div class="meta">Total data: {{ $registrations->count() }}</div>
    </div>

    <div class="filters">
        <div class="filters-row"><strong>Pencarian:</strong> {{ $filters['search'] ?? 'Semua siswa' }}</div>
        <div class="filters-row"><strong>Status:</strong> {{ $filters['status'] ?? 'Semua status' }}</div>
        <div class="filters-row"><strong>ID Kegiatan:</strong> {{ $filters['extracurricular_id'] ?? 'Semua kegiatan' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Siswa</th>
                <th>Email</th>
                <th>No. Telepon</th>
                <th>NIS</th>
                <th>Jenis Kelamin</th>
                <th>Tanggal Lahir</th>
                <th>Alamat</th>
                <th>Nama Orang Tua / Wali</th>
                <th>No. Telepon Orang Tua</th>
                <th>Kegiatan</th>
                <th>Cabang Dipilih</th>
                <th>Tanggal Daftar</th>
                <th>Status</th>
                <th>Catatan Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $registration)
                <tr>
                    <td>{{ $registration->student->user->name ?? '-' }}</td>
                    <td>{{ $registration->student->user->email ?? '-' }}</td>
                    <td>{{ $registration->student->user->phone ?? '-' }}</td>
                    <td>{{ $registration->student->nis ?? '-' }}</td>
                    <td>
                        @if(($registration->student->gender ?? null) === 'L')
                            Laki-laki
                        @elseif(($registration->student->gender ?? null) === 'P')
                            Perempuan
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ optional($registration->student->date_of_birth)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $registration->student->address ?: ($registration->student->user->address ?? '-') }}</td>
                    <td>{{ $registration->student->parent_name ?? '-' }}</td>
                    <td>{{ $registration->student->parent_phone ?? '-' }}</td>
                    <td>{{ $registration->extracurricular->name ?? '-' }}</td>
                    <td>{{ $registration->selected_branch_label }}</td>
                    <td>{{ optional($registration->registration_date)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $registration->status }}</td>
                    <td>{{ $registration->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="empty">Tidak ada data pendaftar untuk filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
