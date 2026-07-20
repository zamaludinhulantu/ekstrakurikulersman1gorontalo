<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Pendaftar Ekstrakurikuler</title>
    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
            color: #1f2937;
        }

        .title {
            font-size: 16pt;
            font-weight: 700;
            color: #17365d;
            margin-bottom: 4px;
        }

        .meta {
            font-size: 10pt;
            color: #52637a;
            margin-bottom: 2px;
        }

        .spacer {
            height: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #cdd9e5;
            padding: 8px 10px;
            vertical-align: top;
        }

        th {
            background: #dfeeff;
            color: #17365d;
            font-weight: 700;
            text-align: center;
            white-space: nowrap;
        }

        .text {
            mso-number-format: "\\@";
        }

        .date {
            mso-number-format: "\\@";
            text-align: center;
            white-space: nowrap;
        }

        .status {
            text-transform: capitalize;
            text-align: center;
            white-space: nowrap;
        }

        .center {
            text-align: center;
        }

        .empty {
            text-align: center;
            color: #6b7280;
            padding: 18px;
        }
    </style>
</head>
<body>
    <div class="title">Data Pendaftar Ekstrakurikuler</div>
    <div class="meta">Diekspor pada {{ now()->format('d-m-Y H:i') }}</div>
    <div class="meta">Total data: {{ $registrations->count() }}</div>
    <div class="meta">Pencarian: {{ filled($filters['search'] ?? null) ? $filters['search'] : 'Semua siswa' }}</div>
    <div class="meta">Status: {{ filled($filters['status'] ?? null) ? $filters['status'] : 'Semua status' }}</div>
    <div class="meta">Filter kegiatan: {{ filled($filters['extracurricular_id'] ?? null) ? $filters['extracurricular_id'] : 'Semua kegiatan' }}</div>

    <div class="spacer"></div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
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
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $index => $registration)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $registration->student->user->name ?? '-' }}</td>
                    <td class="text">{{ $registration->student->user->email ?? '-' }}</td>
                    <td class="text">{{ $registration->student->user->phone ?? '-' }}</td>
                    <td class="text">{{ $registration->student->nis ?? '-' }}</td>
                    <td class="center">
                        @if(($registration->student->gender ?? null) === 'L')
                            Laki-laki
                        @elseif(($registration->student->gender ?? null) === 'P')
                            Perempuan
                        @else
                            -
                        @endif
                    </td>
                    <td class="date">{{ optional($registration->student->date_of_birth)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $registration->student->address ?: ($registration->student->user->address ?? '-') }}</td>
                    <td>{{ $registration->student->parent_name ?? '-' }}</td>
                    <td class="text">{{ $registration->student->parent_phone ?? '-' }}</td>
                    <td>{{ $registration->extracurricular->name ?? '-' }}</td>
                    <td>{{ $registration->selected_branch_label }}</td>
                    <td class="date">{{ optional($registration->registration_date)->format('d-m-Y') ?? '-' }}</td>
                    <td class="status">{{ $registration->status }}</td>
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
