<div class="modal fade" id="registrationVerificationModal" tabindex="-1" aria-labelledby="registrationVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content verification-modal">
            <form method="post" action="#" id="registrationVerificationForm" data-schedule-options='@json($talentTestScheduleOptions ?? [])'>
                @csrf
                @method('patch')
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h2 class="modal-title h4 mb-1" id="registrationVerificationModalLabel">Verifikasi Pendaftar</h2>
                        <p class="text-muted mb-0">Tinjau profil singkat siswa sebelum menyimpan keputusan.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="verification-modal__summary">
                        <div class="data-point">
                            <div class="data-point-label">Siswa</div>
                            <p class="data-point-value mb-0" id="registrationVerificationStudent">-</p>
                            <div class="helper-text mb-0" id="registrationVerificationMeta">-</div>
                        </div>
                        <div class="data-point">
                            <div class="data-point-label">Ekstrakurikuler</div>
                            <p class="data-point-value mb-0" id="registrationVerificationExtracurricular">-</p>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <div class="info-item h-100">
                                <div class="title">Minat dan kemampuan awal</div>
                                <div class="small mt-1"><strong>Bakat utama:</strong> <span id="registrationVerificationPrimaryTalent">-</span></div>
                                <div class="small mt-1"><strong>Kemampuan awal:</strong> <span id="registrationVerificationCurrentSkills">-</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item h-100">
                                <div class="title">Pengalaman dan prestasi</div>
                                <div class="small mt-2"><strong>Pengalaman:</strong> <span id="registrationVerificationExperience">-</span></div>
                                <div class="small mt-1"><strong>Prestasi:</strong> <span id="registrationVerificationAchievements">-</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section-card mt-3">
                        <h3 class="form-section-title">Keputusan Verifikasi</h3>
                        <p class="form-section-copy">Pilih keputusan yang sesuai. Jadwalkan tes tidak langsung mengubah status menjadi diterima.</p>
                        <div class="verification-decision-group">
                            <label class="verification-decision-option">
                                <input type="radio" name="decision" value="approve" checked>
                                <span>
                                    <strong>Terima</strong>
                                    <small>Siswa langsung diterima ke ekstrakurikuler.</small>
                                </span>
                            </label>
                            <label class="verification-decision-option">
                                <input type="radio" name="decision" value="schedule_test">
                                <span>
                                    <strong>Jadwalkan Tes</strong>
                                    <small>Simpan sebagai proses tes dan siapkan jadwal tes bakat.</small>
                                </span>
                            </label>
                            <label class="verification-decision-option">
                                <input type="radio" name="decision" value="reject">
                                <span>
                                    <strong>Tolak</strong>
                                    <small>Pendaftaran ditolak dengan catatan verifikasi.</small>
                                </span>
                            </label>
                        </div>

                        <div class="mt-3">
                            <label class="form-label" for="registrationVerificationNotes">Catatan verifikasi</label>
                            <textarea name="notes" id="registrationVerificationNotes" class="form-control" rows="4" placeholder="Alasan keputusan atau catatan tindak lanjut"></textarea>
                        </div>

                        <div class="mt-3 d-none" id="registrationVerificationScheduleFields">
                            <div class="alert alert-danger d-none" id="registrationVerificationScheduleError" role="alert"></div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="registrationVerificationExistingSchedule">Gunakan jadwal yang sudah ada</label>
                                    <select name="existing_schedule_id" id="registrationVerificationExistingSchedule" class="form-select">
                                        <option value="">Buat jadwal baru</option>
                                    </select>
                                    <div class="helper-text mt-1" id="registrationVerificationExistingScheduleHelp">Pilih jadwal tes aktif dari kegiatan yang sama.</div>
                                </div>
                            </div>
                            <div class="row g-3 mt-0" id="registrationVerificationScheduleManualFields">
                                <div class="col-12">
                                    <label class="form-label" for="registrationVerificationScheduleTitle">Judul tes</label>
                                    <input type="text" name="schedule_title" id="registrationVerificationScheduleTitle" class="form-control" placeholder="Contoh: Tes Bakat Gelombang 1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="registrationVerificationScheduleDate">Tanggal tes</label>
                                    <input type="date" name="schedule_date" id="registrationVerificationScheduleDate" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="registrationVerificationScheduleStartTime">Jam mulai</label>
                                    <input type="time" name="schedule_start_time" id="registrationVerificationScheduleStartTime" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="registrationVerificationScheduleEndTime">Jam selesai</label>
                                    <input type="time" name="schedule_end_time" id="registrationVerificationScheduleEndTime" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="registrationVerificationScheduleLocation">Lokasi</label>
                                    <input type="text" name="schedule_location" id="registrationVerificationScheduleLocation" class="form-control" placeholder="Aula, lapangan, ruang musik, dan lainnya">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="registrationVerificationScheduleDescription">Keterangan jadwal</label>
                                    <textarea name="schedule_description" id="registrationVerificationScheduleDescription" class="form-control" rows="3" placeholder="Instruksi atau perlengkapan tes"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-loading-text="Menyimpan keputusan..."><i class="bi bi-save"></i>Simpan Keputusan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="registrationNoteModal" tabindex="-1" aria-labelledby="registrationNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content verification-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title h5 mb-1" id="registrationNoteModalLabel">Catatan Verifikasi</h2>
                    <p class="text-muted mb-0" id="registrationNoteModalMeta">-</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="info-item">
                    <div class="title">Catatan</div>
                    <div class="small mt-2 text-preline" id="registrationNoteModalBody">Belum ada catatan verifikasi.</div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
