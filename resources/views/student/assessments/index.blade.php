@extends('layouts.app')

@section('page_title', 'Prestasi/Penilaian Pribadi')
@section('page_subtitle', 'Lihat catatan prestasi dan penilaian dari pembina')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Filter Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select">
                        <option value="">Semua Ekstrakurikuler</option>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string)$extracurricularId === (string)$item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-funnel"></i>Filter</button></div>
                <div class="col-md-2"><a href="{{ route('student.assessments.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-repeat"></i>Reset</a></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Ekstrakurikuler</th>
                    <th>Jenis</th>
                    <th>Judul</th>
                    <th>Nilai</th>
                    <th>Tanggal</th>
                </tr>
                </thead>
                <tbody>
                @forelse($assessments as $row)
                    <tr>
                        <td>{{ $row->extracurricular->name ?? '-' }}</td>
                        <td class="text-capitalize">{{ $row->assessment_type }}</td>
                        <td>{{ $row->title }}</td>
                        <td>{{ $row->score ?? '-' }}</td>
                        <td>{{ optional($row->assessment_date)->format('d-m-Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="icon"><i class="bi bi-award"></i></div>
                                <p class="mb-0">Belum ada data prestasi/penilaian.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $assessments->links() }}</div>
    </div>
@endsection
