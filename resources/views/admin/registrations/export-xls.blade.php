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

        .date {
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

        .excel-text {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="title">Data Pendaftar Ekstrakurikuler</div>
    <div class="meta">Diekspor pada {{ now()->format('d-m-Y H:i') }}</div>
    <div class="meta">Total data: {{ $students->count() }}</div>
    <div class="meta">Pencarian: {{ $filterSummary['search'] }}</div>
    <div class="meta">Kategori: {{ $filterSummary['category'] }}</div>
    <div class="meta">Kegiatan: {{ $filterSummary['extracurricular'] }}</div>
    <div class="meta">Kelas: {{ $filterSummary['class_name'] }}</div>
    <div class="meta">Jenis kelamin: {{ $filterSummary['gender'] }}</div>
    <div class="meta">Status: {{ $filterSummary['status'] }}</div>

    <div class="spacer"></div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
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
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $student->user->name ?? '-' }}</td>
                    <td class="excel-text" style='mso-number-format:"\@";'>{{ $student->user->email ?? '-' }}</td>
                    <td class="excel-text" style='mso-number-format:"\@";'>{{ $student->user->phone ?? '-' }}</td>
                    <td class="excel-text" style='mso-number-format:"\@";'>{{ $student->nis ?? '-' }}</td>
                    <td>{{ $student->class_name ?? '-' }}</td>
                    <td class="center">
                        @if(($student->gender ?? null) === 'L')
                            Laki-laki
                        @elseif(($student->gender ?? null) === 'P')
                            Perempuan
                        @else
                            -
                        @endif
                    </td>
                    <td class="date">{{ optional($student->date_of_birth)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $student->address ?: ($student->user->address ?? '-') }}</td>
                    <td>{{ $student->parent_name ?? '-' }}</td>
                    <td class="excel-text" style='mso-number-format:"\@";'>{{ $student->parent_phone ?? '-' }}</td>
                    <td>{{ $activityNames !== '' ? $activityNames : '-' }}</td>
                    <td class="date">{{ optional($latestRegistration?->registration_date)->format('d-m-Y') ?? '-' }}</td>
                    <td class="status">{{ $statusLabels !== '' ? $statusLabels : '-' }}</td>
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
