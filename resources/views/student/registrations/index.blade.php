@extends('layouts.app')

@section('page_title', 'Status Pendaftaran')
@section('page_subtitle', 'Pantau hasil verifikasi pendaftaran ekstrakurikuler')

@section('content')
    @if($hasLegacyRegistrationOverflow ?? false)
        <div class="alert alert-warning mb-3">
            <strong class="d-block mb-1">Data lama melebihi batas pendaftaran</strong>
            {{ $student->registrationLegacyOverflowMessage() }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">Riwayat Pendaftaran</div>
        <div class="card-body p-0">
            <div class="desktop-table table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                    <tr>
                        <th>Kegiatan</th>
                        <th>Cabang</th>
                        <th>Tanggal Daftar</th>
                        <th>Status</th>
                        <th>Tes Bakat</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($registrations as $row)
                        @php
                            $latestPublishedResult = $row->latestPublishedTalentTestResult();
                            $displayStatus = $row->displayStatus();
                            $requiresCancellationApproval = $row->requiresCancellationApproval();
                        @endphp
                        <tr>
                            <td>{{ $row->extracurricular->name ?? '-' }}</td>
                            <td>{{ $row->selected_branch_label }}</td>
                            <td>{{ optional($row->registration_date)->format('d-m-Y') }}</td>
                            <td><x-registration.status-badge :registration="$row" /></td>
                            <td>
                                @if($latestPublishedResult)
                                    <span class="badge badge-status-success">Dipublikasikan</span>
                                    <div class="small text-muted mt-1">Nilai: {{ $latestPublishedResult->overall_score !== null ? number_format((float) $latestPublishedResult->overall_score, 0, ',', '.') : '-' }}</div>
                                    <div class="small text-muted">{{ $latestPublishedResult->decisionLabel() }}</div>
                                @elseif($row->talentTestResults->isNotEmpty())
                                    <span class="badge badge-status-warning">Draft</span>
                                @else
                                    <span class="text-muted">Belum ada</span>
                                @endif
                            </td>
                            <td>{{ $row->notes ?? '-' }}</td>
                            <td>
                                @if($row->canStudentEdit())
                                    <a href="{{ route('student.registrations.edit', $row) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i>Ubah</a>
                                @endif
                                @if($row->canStudentCancel())
                                    <form method="post" action="{{ route('student.registrations.destroy', $row) }}" class="d-inline" onsubmit="return confirm('{{ $requiresCancellationApproval ? 'Kirim permintaan pembatalan kepada Admin atau Pembina?' : 'Batalkan keikutsertaan pada kegiatan ini?' }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-circle"></i>{{ $requiresCancellationApproval ? 'Ajukan Pembatalan' : 'Batal Ikut' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                        <td colspan="7">
                                <div class="empty-state">
                                    <div class="icon"><i class="bi bi-inbox"></i></div>
                                    <p class="mb-0">Belum ada pendaftaran.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mobile-stack-table p-3">
                @forelse($registrations as $row)
                    @php
                        $latestPublishedResult = $row->latestPublishedTalentTestResult();
                        $displayStatus = $row->displayStatus();
                        $requiresCancellationApproval = $row->requiresCancellationApproval();
                    @endphp
                    <div class="mobile-data-card">
                        <div class="mobile-data-card-header">
                            <h3 class="mobile-data-card-title">{{ $row->extracurricular->name ?? '-' }}</h3>
                            <x-registration.status-badge :registration="$row" />
                        </div>
                        <div class="mobile-data-list">
                            <div><span class="mobile-data-item-label">Cabang</span><p class="mobile-data-item-value">{{ $row->selected_branch_label }}</p></div>
                            <div><span class="mobile-data-item-label">Tanggal daftar</span><p class="mobile-data-item-value">{{ optional($row->registration_date)->format('d-m-Y') }}</p></div>
                            <div>
                                <span class="mobile-data-item-label">Status tes bakat</span>
                                <p class="mobile-data-item-value">
                                    @if($latestPublishedResult)
                                        Dipublikasikan | Nilai {{ $latestPublishedResult->overall_score !== null ? number_format((float) $latestPublishedResult->overall_score, 0, ',', '.') : '-' }} | {{ $latestPublishedResult->decisionLabel() }}
                                    @elseif($row->talentTestResults->isNotEmpty())
                                        Draft
                                    @else
                                        Belum ada
                                    @endif
                                </p>
                            </div>
                            <div><span class="mobile-data-item-label">Catatan</span><p class="mobile-data-item-value">{{ $row->notes ?? '-' }}</p></div>
                        </div>
                        @if($row->canStudentEdit())
                            <div class="form-actions mt-3">
                                <a href="{{ route('student.registrations.edit', $row) }}" class="btn btn-outline-primary w-100"><i class="bi bi-pencil-square"></i>Ubah Pendaftaran</a>
                            </div>
                        @endif
                        @if($row->canStudentCancel())
                            <form method="post" action="{{ route('student.registrations.destroy', $row) }}" class="mt-2" onsubmit="return confirm('{{ $requiresCancellationApproval ? 'Kirim permintaan pembatalan kepada Admin atau Pembina?' : 'Batalkan keikutsertaan pada kegiatan ini?' }}');">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-x-circle"></i>{{ $requiresCancellationApproval ? 'Ajukan Pembatalan' : 'Batal Ikut' }}
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="icon"><i class="bi bi-inbox"></i></div>
                        <p class="mb-0">Belum ada pendaftaran.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-body">{{ $registrations->links() }}</div>
    </div>
@endsection
