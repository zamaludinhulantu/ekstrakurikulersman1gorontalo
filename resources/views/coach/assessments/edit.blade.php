@extends('layouts.app')

@section('page_title', 'Edit Prestasi/Penilaian')
@section('page_subtitle', 'Perbarui data prestasi atau penilaian peserta')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('coach.assessments.update', $assessment) }}" class="row g-3">
                @csrf
                @method('put')
                <div class="col-md-4">
                    <label class="form-label">Ekstrakurikuler</label>
                    <select name="extracurricular_id" class="form-select" required>
                        @foreach($extracurriculars as $item)
                            <option value="{{ $item->id }}" @selected((string)old('extracurricular_id', $assessment->extracurricular_id) === (string)$item->id)>{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Siswa</label>
                    <select name="student_id" class="form-select" required>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" @selected((string)old('student_id', $assessment->student_id) === (string)$student->id)>
                                {{ $student->user->name }} ({{ $student->nis }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jenis</label>
                    <select name="assessment_type" class="form-select" required>
                        <option value="achievement" @selected(old('assessment_type', $assessment->assessment_type)==='achievement')>Prestasi</option>
                        <option value="assessment" @selected(old('assessment_type', $assessment->assessment_type)==='assessment')>Penilaian</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" value="{{ old('title', $assessment->title) }}" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nilai</label>
                    <input type="number" step="0.01" min="0" max="100" name="score" value="{{ old('score', $assessment->score) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="assessment_date" value="{{ old('assessment_date', optional($assessment->assessment_date)->format('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $assessment->description) }}</textarea>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i>Update</button>
                    <a href="{{ route('coach.assessments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
