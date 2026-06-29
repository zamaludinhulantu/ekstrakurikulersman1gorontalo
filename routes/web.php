<?php

use App\Http\Controllers\Admin\CoachController as AdminCoachController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\ExtracurricularController as AdminExtracurricularController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
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
use App\Http\Controllers\Student\AssessmentController as StudentAssessmentController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\ExtracurricularController as StudentExtracurricularController;
use App\Http\Controllers\Student\RegistrationController as StudentRegistrationController;
use App\Http\Controllers\Student\ScheduleController as StudentScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicLandingController::class, 'index'])->name('landing');
Route::get('/informasi-sistem', [PublicLandingController::class, 'information'])->name('public.information');
Route::get('/pengumuman', [PublicLandingController::class, 'announcements'])->name('public.announcements');
Route::get('/extracurriculars/{extracurricular}/daftar', [PublicLandingController::class, 'beginRegistration'])->name('public.extracurriculars.register');
Route::get('/extracurriculars/{extracurricular}', [PublicLandingController::class, 'show'])->name('public.extracurriculars.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware(['auth', 'role:admin,coach,student,principal'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

    Route::resource('users', AdminUserController::class);
    Route::resource('students', AdminStudentController::class);
    Route::resource('coaches', AdminCoachController::class);
    Route::resource('extracurriculars', AdminExtracurricularController::class);
    Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('/registrations', [AdminRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/export', [AdminRegistrationController::class, 'export'])->name('registrations.export');
    Route::match(['post', 'patch'], '/registrations/{registration}/status', [AdminRegistrationController::class, 'updateStatus'])->name('registrations.update-status');
    Route::get('/registrations/{registration}/status', fn () => redirect()->route('admin.registrations.index'))
        ->name('registrations.update-status.redirect');
    Route::get('/participants', [AdminReportController::class, 'participants'])->name('participants.index');
    Route::get('/schedules', [AdminReportController::class, 'schedules'])->name('schedules.index');
    Route::get('/attendances', [AdminReportController::class, 'attendances'])->name('attendances.index');
    Route::get('/assessments', [AdminReportController::class, 'assessments'])->name('assessments.index');
    Route::get('/reports/export/{type}', [AdminReportController::class, 'export'])->name('reports.export');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');

    Route::get('/extracurriculars', [StudentExtracurricularController::class, 'index'])->name('extracurriculars.index');
    Route::get('/extracurriculars/{extracurricular}', [StudentExtracurricularController::class, 'show'])->name('extracurriculars.show');
    Route::post('/extracurriculars/{extracurricular}/register', [StudentRegistrationController::class, 'store'])->name('registrations.store');

    Route::get('/registrations', [StudentRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/schedules', [StudentScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/attendances', [StudentAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/export', [StudentAttendanceController::class, 'export'])->name('attendances.export');
    Route::get('/assessments', [StudentAssessmentController::class, 'index'])->name('assessments.index');
});

Route::middleware(['auth', 'role:coach'])->prefix('coach')->name('coach.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'coach'])->name('dashboard');

    Route::get('/extracurriculars', [CoachExtracurricularController::class, 'index'])->name('extracurriculars.index');
    Route::get('/extracurriculars/{extracurricular}/participants', [CoachExtracurricularController::class, 'participants'])->name('extracurriculars.participants');
    Route::get('/registrations', [CoachRegistrationController::class, 'index'])->name('registrations.index');
    Route::match(['post', 'patch'], '/registrations/{registration}/status', [CoachRegistrationController::class, 'updateStatus'])->name('registrations.update-status');
    Route::get('/registrations/{registration}/status', fn () => redirect()->route('coach.registrations.index'))
        ->name('registrations.update-status.redirect');
    Route::get('/announcements', [CoachAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [CoachAnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [CoachAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::resource('schedules', CoachScheduleController::class)->except(['show']);

    Route::get('/attendances', [CoachAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/export', [CoachAttendanceController::class, 'export'])->name('attendances.export');
    Route::post('/attendances/{schedule}/save', [CoachAttendanceController::class, 'save'])->name('attendances.save');

    Route::get('/assessments', [CoachAssessmentController::class, 'index'])->name('assessments.index');
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
