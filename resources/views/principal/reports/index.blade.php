@extends('layouts.app')

@section('page_title', 'Pusat Laporan Kepala Sekolah')
@section('page_subtitle', 'Laporan read-only dengan filter, unduh PDF/Excel, dan tampilan cetak')

@php
    $exportParams = array_filter($filters, fn ($value) => $value !== null && $value !== '');
@endphp

@section('content')
    <div class="surface-card toolbar-card mb-3">
        <div class="section-header-inline">
            <div>
                <h2>{{ $reportTitle }}</h2>
                <p>{{ $reportDescription }}</p>
            </div>
            <div class="quick-actions">
                <a href="{{ route('principal.dashboard', ['tab' => 'reports']) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali ke dashboard</a>
            </div>
        </div>
        <form method="get" class="toolbar-grid">
            <div class="toolbar-col-3">
                <label class="form-label" for="report_type">Jenis laporan</label>
                <select class="form-select" id="report_type" name="report_type">
                    @foreach($reportOptions as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected($reportType === $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="toolbar-col-3">
                <label class="form-label" for="extracurricular_id">Ekstrakurikuler</label>
                <select class="form-select" id="extracurricular_id" name="extracurricular_id">
                    <option value="">Semua ekstrakurikuler</option>
                    @foreach($extracurriculars as $option)
                        <option value="{{ $option->id }}" @selected((string) ($filters['extracurricular_id'] ?? '') === (string) $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="toolbar-col-2">
                <label class="form-label" for="class_name">Kelas</label>
                <select class="form-select" id="class_name" name="class_name">
                    <option value="">Semua kelas</option>
                    @foreach($classOptions as $option)
                        <option value="{{ $option }}" @selected(($filters['class_name'] ?? null) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="toolbar-col-2">
                <label class="form-label" for="gender">Jenis kelamin</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">Semua</option>
                    <option value="L" @selected(($filters['gender'] ?? null) === 'L')>Laki-laki</option>
                    <option value="P" @selected(($filters['gender'] ?? null) === 'P')>Perempuan</option>
                </select>
            </div>
            <div class="toolbar-col-2">
                <label class="form-label" for="month">Bulan</label>
                <select class="form-select" id="month" name="month">
                    <option value="">Semua bulan</option>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}" @selected((int) ($filters['month'] ?? 0) === $month)>{{ \Illuminate\Support\Carbon::create(2026, $month, 1)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="toolbar-col-3">
                <label class="form-label" for="school_year">Tahun ajaran</label>
                <select class="form-select" id="school_year" name="school_year">
                    @foreach($schoolYearOptions as $option)
                        <option value="{{ $option }}" @selected(($filters['school_year'] ?? null) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            <div class="toolbar-col-3">
                <label class="form-label" for="semester">Semester</label>
                <select class="form-select" id="semester" name="semester">
                    <option value="all" @selected(($filters['semester'] ?? 'all') === 'all')>Semua semester</option>
                    <option value="odd" @selected(($filters['semester'] ?? null) === 'odd')>Ganjil</option>
                    <option value="even" @selected(($filters['semester'] ?? null) === 'even')>Genap</option>
                </select>
            </div>
            <div class="toolbar-col-6">
                <div class="quick-actions w-100">
                    <button type="submit" class="btn btn-primary flex-fill" data-loading-text="Memuat laporan..."><i class="bi bi-funnel"></i>Terapkan filter</button>
                    <a href="{{ route('principal.reports.index') }}" class="btn btn-outline-secondary flex-fill"><i class="bi bi-arrow-counterclockwise"></i>Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="surface-card mb-3">
        <div class="surface-card-body">
            <div class="quick-actions">
                <a href="{{ route('principal.reports.export', array_merge(['type' => $reportType, 'format' => 'pdf'], $exportParams)) }}" class="btn btn-primary"><i class="bi bi-file-earmark-pdf"></i>Unduh PDF</a>
                <a href="{{ route('principal.reports.export', array_merge(['type' => $reportType, 'format' => 'xls'], $exportParams)) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i>Ekspor Excel</a>
                <a href="{{ route('principal.reports.print', array_merge(['type' => $reportType], $exportParams)) }}" class="btn btn-outline-primary" target="_blank" rel="noopener"><i class="bi bi-printer"></i>Cetak</a>
            </div>
        </div>
    </div>

    <div class="surface-card">
        <div class="surface-card-header">Hasil laporan</div>
        <div class="surface-card-body">
            @if($rows->count())
                <div class="table-responsive desktop-table">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $row)
                            <tr>
                                @foreach($columns as $column)
                                    <td>{{ data_get($row, $column['key'], '-') ?: '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mobile-stack-table">
                    @foreach($rows as $row)
                        <article class="mobile-data-card">
                            <div class="mobile-data-list">
                                @foreach($columns as $column)
                                    <div>
                                        <span class="mobile-data-item-label">{{ $column['label'] }}</span>
                                        <span class="mobile-data-item-value">{{ data_get($row, $column['key'], '-') ?: '-' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-3">{{ $rows->links() }}</div>
            @else
                <div class="empty-state">
                    <div class="icon"><i class="bi bi-folder2-open"></i></div>
                    <p class="mb-0">Tidak ada data yang cocok dengan filter laporan saat ini.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
