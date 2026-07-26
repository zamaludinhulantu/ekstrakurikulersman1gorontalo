<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #17324e; margin: 24px; font-size: 12px; }
        h1 { margin: 0 0 6px; font-size: 22px; }
        p { margin: 0 0 4px; color: #566b84; }
        .meta { margin: 16px 0 18px; padding: 12px 14px; border: 1px solid #d8e2ef; border-radius: 12px; background: #f8fbff; }
        .meta strong { color: #17324e; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d8e2ef; padding: 8px 10px; vertical-align: top; text-align: left; }
        th { background: #eef5ff; font-size: 11px; text-transform: uppercase; color: #49617e; }
        .footer { margin-top: 16px; font-size: 11px; color: #6b7f96; }
        @media print {
            body { margin: 14px; }
            .print-actions { display: none; }
        }
    </style>
</head>
<body>
    @if(!empty($printMode))
        <div class="print-actions" style="margin-bottom: 16px;">
            <button onclick="window.print()" style="padding: 10px 14px; border-radius: 10px; border: 1px solid #bfd3fb; background: #edf4ff; color: #1849cb; font-weight: 700;">Cetak sekarang</button>
        </div>
    @endif

    <h1>{{ $reportTitle }}</h1>
    <p>Sistem Informasi Ekstrakurikuler SMA Negeri 1 Gorontalo</p>
    <p>Dibuat pada {{ now()->translatedFormat('d F Y H:i') }}</p>

    <div class="meta">
        <p><strong>Tahun ajaran:</strong> {{ $filters['school_year'] ?? '-' }}</p>
        <p><strong>Semester:</strong>
            {{ match($filters['semester'] ?? 'all') {
                'odd' => 'Ganjil',
                'even' => 'Genap',
                default => 'Semua semester',
            } }}
        </p>
        <p><strong>Bulan:</strong>
            {{ !empty($filters['month']) ? \Illuminate\Support\Carbon::create(2026, (int) $filters['month'], 1)->translatedFormat('F') : 'Semua bulan' }}
        </p>
    </div>

    <table>
        <thead>
        <tr>
            @foreach($columns as $column)
                <th>{{ $column['label'] }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($columns as $column)
                    <td>{{ data_get($row, $column['key'], '-') ?: '-' }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columns) }}">Tidak ada data untuk filter yang dipilih.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini bersifat baca-saja untuk kepala sekolah. Tidak ada akses ubah data dari halaman laporan ini.
    </div>
</body>
</html>
