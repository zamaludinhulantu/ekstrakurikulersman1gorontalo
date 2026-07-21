<?php

use App\Http\Controllers\Admin\CoachController as AdminCoachController;
use App\Http\Controllers\Admin\ExtracurricularCategoryController as AdminExtracurricularCategoryController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Admin\ExtracurricularController as AdminExtracurricularController;
use App\Http\Controllers\Admin\ExtracurricularAchievementController as AdminExtracurricularAchievementController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\TalentTestController as AdminTalentTestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Coach\AnnouncementController as CoachAnnouncementController;
use App\Http\Controllers\Coach\AssessmentController as CoachAssessmentController;
use App\Http\Controllers\Coach\AttendanceController as CoachAttendanceController;
use App\Http\Controllers\Coach\ExtracurricularController as CoachExtracurricularController;
use App\Http\Controllers\Coach\RegistrationController as CoachRegistrationController;
use App\Http\Controllers\Coach\ScheduleController as CoachScheduleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Principal\DashboardController as PrincipalDashboardController;
use App\Http\Controllers\Principal\AttendanceController as PrincipalAttendanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicLandingController;
use App\Http\Controllers\RegistrationAchievementProofController;
use App\Http\Controllers\RegistrationProfilePreviewController;
use App\Http\Controllers\Student\AssessmentController as StudentAssessmentController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\ExtracurricularController as StudentExtracurricularController;
use App\Http\Controllers\Student\RegistrationController as StudentRegistrationController;
use App\Http\Controllers\Student\ScheduleController as StudentScheduleController;
use App\Http\Controllers\Student\TalentTestController as StudentTalentTestController;
use App\Http\Controllers\Coach\TalentTestController as CoachTalentTestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicLandingController::class, 'index'])->name('landing');
Route::get('/kegiatan', [PublicLandingController::class, 'activities'])->name('public.activities.index');
Route::get('/kegiatan/semua', [PublicLandingController::class, 'catalog'])->name('public.activities.all');
Route::get('/kegiatan/{slug}', [PublicLandingController::class, 'categoryCatalog'])->name('public.activities.category');
Route::get('/informasi-sistem', [PublicLandingController::class, 'information'])->name('public.information');
Route::get('/pengumuman', [PublicLandingController::class, 'announcements'])->name('public.announcements');
Route::get('/extracurriculars/{extracurricular}/daftar', [PublicLandingController::class, 'beginRegistration'])->name('public.extracurriculars.register');
Route::get('/extracurriculars/{extracurricular}', [PublicLandingController::class, 'show'])->name('public.extracurriculars.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware(['auth', 'role:admin,coach,student,principal'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/registrations/{registration}/profile-preview', [RegistrationProfilePreviewController::class, 'show'])
        ->name('registrations.profile-preview');
    Route::get('/registrations/{registration}/achievement-proof', [RegistrationAchievementProofController::class, 'show'])
        ->name('registrations.achievement-proof');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    Route::resource('users', AdminUserController::class);
    Route::get('/students/export', [AdminStudentController::class, 'export'])->name('students.export');
    Route::resource('students', AdminStudentController::class);
    Route::resource('coaches', AdminCoachController::class);
    Route::get('/extracurricular-categories', [AdminExtracurricularCategoryController::class, 'index'])->name('extracurricular-categories.index');
    Route::get('/extracurricular-categories/{extracurricularCategory}/edit', [AdminExtracurricularCategoryController::class, 'edit'])->name('extracurricular-categories.edit');
    Route::put('/extracurricular-categories/{extracurricularCategory}', [AdminExtracurricularCategoryController::class, 'update'])->name('extracurricular-categories.update');
    Route::resource('extracurriculars', AdminExtracurricularController::class);
    Route::post('/extracurriculars/{extracurricular}/achievements', [AdminExtracurricularAchievementController::class, 'store'])->name('extracurricular-achievements.store');
    Route::put('/extracurriculars/{extracurricular}/achievements/{achievement}', [AdminExtracurricularAchievementController::class, 'update'])->name('extracurricular-achievements.update');
    Route::delete('/extracurriculars/{extracurricular}/achievements/{achievement}', [AdminExtracurricularAchievementController::class, 'destroy'])->name('extracurricular-achievements.destroy');
    Route::get('/assessments', [AdminAssessmentController::class, 'index'])->name('assessments.index');
    Route::post('/assessments', [AdminAssessmentController::class, 'store'])->name('assessments.store');
    Route::get('/assessments/{assessment}/edit', [AdminAssessmentController::class, 'edit'])->name('assessments.edit');
    Route::put('/assessments/{assessment}', [AdminAssessmentController::class, 'update'])->name('assessments.update');
    Route::delete('/assessments/{assessment}', [AdminAssessmentController::class, 'destroy'])->name('assessments.destroy');
    Route::get('/assessments/report', [AdminReportController::class, 'assessments'])->name('assessments.report');
    Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('/registrations', [AdminRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/export', [AdminRegistrationController::class, 'export'])->name('registrations.export');
    Route::match(['post', 'patch'], '/registrations/{registration}/status', [AdminRegistrationController::class, 'updateStatus'])->name('registrations.update-status');
    Route::get('/registrations/{registration}/status', [AdminRegistrationController::class, 'redirectStatus'])
        ->name('registrations.update-status.redirect');
    Route::get('/registrations/{registration}', [AdminRegistrationController::class, 'show'])->name('registrations.show');
    Route::get('/talent-tests', [AdminTalentTestController::class, 'index'])->name('talent-tests.index');
    Route::get('/participants', [AdminReportController::class, 'participants'])->name('participants.index');
    Route::get('/schedules', [AdminReportController::class, 'schedules'])->name('schedules.index');
    Route::get('/attendances', [AdminReportController::class, 'attendances'])->name('attendances.index');
    Route::get('/reports/export/{type}', [AdminReportController::class, 'export'])->name('reports.export');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');

    Route::get('/extracurriculars', [StudentExtracurricularController::class, 'index'])->name('extracurriculars.index');
    Route::get('/extracurriculars/{extracurricular}/register', [StudentRegistrationController::class, 'create'])->name('extracurriculars.register');
    Route::get('/extracurriculars/{extracurricular}', [StudentExtracurricularController::class, 'show'])->name('extracurriculars.show');
    Route::post('/extracurriculars/{extracurricular}/register', [StudentRegistrationController::class, 'store'])->name('registrations.store');

    Route::get('/registrations', [StudentRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/{registration}/edit', [StudentRegistrationController::class, 'edit'])->name('registrations.edit');
    Route::put('/registrations/{registration}', [StudentRegistrationController::class, 'update'])->name('registrations.update');
    Route::get('/schedules', [StudentScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/attendances', [StudentAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/export', [StudentAttendanceController::class, 'export'])->name('attendances.export');
    Route::get('/assessments', [StudentAssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/talent-tests', [StudentTalentTestController::class, 'index'])->name('talent-tests.index');
});

Route::middleware(['auth', 'role:coach'])->prefix('coach')->name('coach.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'coach'])->name('dashboard');

    Route::get('/extracurriculars', [CoachExtracurricularController::class, 'index'])->name('extracurriculars.index');
    Route::get('/extracurriculars/{extracurricular}/participants', [CoachExtracurricularController::class, 'participants'])->name('extracurriculars.participants');
    Route::get('/registrations', [CoachRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/{registration}', [CoachRegistrationController::class, 'show'])->name('registrations.show');
    Route::match(['post', 'patch'], '/registrations/{registration}/status', [CoachRegistrationController::class, 'updateStatus'])->name('registrations.update-status');
    Route::get('/registrations/{registration}/status', [CoachRegistrationController::class, 'redirectStatus'])
        ->name('registrations.update-status.redirect');
    Route::get('/announcements', [CoachAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [CoachAnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}/edit', [CoachAnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [CoachAnnouncementController::class, 'update'])->name('announcements.update');
    Route::patch('/announcements/{announcement}/publish', [CoachAnnouncementController::class, 'publish'])->name('announcements.publish');
    Route::patch('/announcements/{announcement}/deactivate', [CoachAnnouncementController::class, 'deactivate'])->name('announcements.deactivate');
    Route::delete('/announcements/{announcement}', [CoachAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::resource('schedules', CoachScheduleController::class)->except(['show']);
    Route::get('/talent-test-aspects', [CoachTalentTestController::class, 'aspectIndex'])->name('talent-test-aspects.index');
    Route::post('/extracurriculars/{extracurricular}/talent-test-aspects', [CoachTalentTestController::class, 'storeAspect'])->name('talent-test-aspects.store');
    Route::put('/extracurriculars/{extracurricular}/talent-test-aspects/{aspect}', [CoachTalentTestController::class, 'updateAspect'])->name('talent-test-aspects.update');
    Route::delete('/extracurriculars/{extracurricular}/talent-test-aspects/{aspect}', [CoachTalentTestController::class, 'destroyAspect'])->name('talent-test-aspects.destroy');
    Route::get('/talent-tests', [CoachTalentTestController::class, 'index'])->name('talent-tests.index');
    Route::get('/talent-tests/create', [CoachTalentTestController::class, 'create'])->name('talent-tests.create');
    Route::post('/talent-tests', [CoachTalentTestController::class, 'store'])->name('talent-tests.store');
    Route::get('/talent-tests/{talentTest}/edit', [CoachTalentTestController::class, 'edit'])->name('talent-tests.edit');
    Route::put('/talent-tests/{talentTest}', [CoachTalentTestController::class, 'update'])->name('talent-tests.update');
    Route::get('/talent-tests/{talentTest}/manage', [CoachTalentTestController::class, 'manage'])->name('talent-tests.manage');
    Route::post('/talent-tests/{talentTest}/duplicate', [CoachTalentTestController::class, 'duplicate'])->name('talent-tests.duplicate');
    Route::post('/talent-tests/{talentTest}/results', [CoachTalentTestController::class, 'saveResults'])->name('talent-tests.results.save');
    Route::patch('/talent-tests/{talentTest}/cancel', [CoachTalentTestController::class, 'cancel'])->name('talent-tests.cancel');

    Route::get('/attendances', [CoachAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/export', [CoachAttendanceController::class, 'export'])->name('attendances.export');
    Route::post('/attendances/{schedule}/save', [CoachAttendanceController::class, 'save'])->name('attendances.save');

    Route::get('/assessments', [CoachAssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/assessments/export', [CoachAssessmentController::class, 'export'])->name('assessments.export');
    Route::post('/assessments', [CoachAssessmentController::class, 'store'])->name('assessments.store');
    Route::get('/assessments/{assessment}/edit', [CoachAssessmentController::class, 'edit'])->name('assessments.edit');
    Route::put('/assessments/{assessment}', [CoachAssessmentController::class, 'update'])->name('assessments.update');
    Route::delete('/assessments/{assessment}', [CoachAssessmentController::class, 'destroy'])->name('assessments.destroy');
});

Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function (): void {
    Route::get('/dashboard', [PrincipalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendances', [PrincipalAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/export', [PrincipalAttendanceController::class, 'export'])->name('attendances.export');
});
