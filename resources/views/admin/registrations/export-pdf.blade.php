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
        <div class="meta">Total data: {{ $students->count() }}</div>
    </div>

    <div class="filters">
        <div class="filters-row"><strong>Pencarian:</strong> {{ $filterSummary['search'] }}</div>
        <div class="filters-row"><strong>Kategori:</strong> {{ $filterSummary['category'] }}</div>
        <div class="filters-row"><strong>Kegiatan:</strong> {{ $filterSummary['extracurricular'] }}</div>
        <div class="filters-row"><strong>Kelas:</strong> {{ $filterSummary['class_name'] }}</div>
        <div class="filters-row"><strong>Jenis kelamin:</strong> {{ $filterSummary['gender'] }}</div>
        <div class="filters-row"><strong>Status:</strong> {{ $filterSummary['status'] }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Siswa</th>
                <th>Email</th>
                <th>No. Telepon</th>
                <th>NIS</th>
                <th>Kelas</th>
                <th>Jenis Kelamin</th>
                <th>Tanggal Lahir</th>
                <th>Alamat</th>
                <th>Nama Orang Tua / Wali</th>
                <th>No. Telepon Orang Tua</th>
                <th>Kegiatan yang Diikuti</th>
                <th>Tanggal Daftar</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                @php
                    $studentRegistrations = $student->registrations
                        ->sortByDesc(fn ($item) => optional($item->registration_date)->timestamp ?? 0)
                        ->values();
                    $latestRegistration = $studentRegistrations->first();
                    $activityNames = $studentRegistrations
                        ->map(fn ($item) => $item->extracurricular->name ?? null)
                        ->filter()
                        ->unique()
                        ->values()
                        ->implode(', ');
                    $statusLabels = $studentRegistrations
                        ->map(function ($registration) use ($statusMap) {
                            $displayStatus = $registration->status;
                            $hasPublishedResult = $registration->talentTestResults->contains(fn ($item) => $item->status === 'published');
                            $hasScheduledTest = $registration->talentTestParticipants->isNotEmpty();
                            if ($registration->status === 'approved' && $registration->willing_to_take_test && ! $hasPublishedResult) {
                                $displayStatus = $hasScheduledTest ? 'scheduled_test' : 'waiting_test';
                            }

                            return $statusMap[$displayStatus] ?? ucfirst($displayStatus);
                        })
                        ->unique()
                        ->values()
                        ->implode(', ');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->user->name ?? '-' }}</td>
                    <td>{{ $student->user->email ?? '-' }}</td>
                    <td>{{ $student->user->phone ?? '-' }}</td>
                    <td>{{ $student->nis ?? '-' }}</td>
                    <td>{{ $student->class_name ?? '-' }}</td>
                    <td>
                        @if(($student->gender ?? null) === 'L')
                            Laki-laki
                        @elseif(($student->gender ?? null) === 'P')
                            Perempuan
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ optional($student->date_of_birth)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $student->address ?: ($student->user->address ?? '-') }}</td>
                    <td>{{ $student->parent_name ?? '-' }}</td>
                    <td>{{ $student->parent_phone ?? '-' }}</td>
                    <td>{{ $activityNames !== '' ? $activityNames : '-' }}</td>
                    <td>{{ optional($latestRegistration?->registration_date)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $statusLabels !== '' ? $statusLabels : '-' }}</td>
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
