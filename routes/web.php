<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicApplicationStatusController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\RoleDashboardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Staff\AppointmentController;
use App\Http\Controllers\Staff\DocumentController;
use App\Http\Controllers\Staff\PersonController;
use App\Http\Controllers\Staff\QrBarcodeController;
use App\Http\Controllers\Staff\ServiceApplicationController;
use Illuminate\Support\Facades\Route;

Route::name('public.')->group(function () {
    Route::get('/', [PublicPageController::class, 'home'])->name('home');
    Route::get('/about', [PublicPageController::class, 'about'])->name('about');
    Route::get('/services', [PublicPageController::class, 'services'])->name('services');
    Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
    Route::post('/contact', [ContactMessageController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('contact.store');
    Route::get('/status-check', [PublicApplicationStatusController::class, 'index'])
        ->middleware('throttle:30,1')
        ->name('status');
});

Route::get('/dashboard', DashboardRedirectController::class)->middleware(['auth', 'active'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
});

Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::patch('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
    Route::patch('/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
    Route::get('/branches', [AdminBranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/{branch}', [AdminBranchController::class, 'show'])->name('branches.show');
    Route::resource('branches', AdminBranchController::class)->except(['index', 'show']);
    Route::patch('/branches/{branch}/activate', [AdminBranchController::class, 'activate'])->name('branches.activate');
    Route::patch('/branches/{branch}/deactivate', [AdminBranchController::class, 'deactivate'])->name('branches.deactivate');
    Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
    Route::resource('services', AdminServiceController::class)->except(['index', 'show']);
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::patch('/contact-messages/{contactMessage}/status', [AdminContactMessageController::class, 'updateStatus'])->name('contact-messages.status');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');
    Route::delete('/people/{person}', [PersonController::class, 'destroy'])->name('people.destroy');
    Route::patch('/people/{person}/codes/regenerate', [QrBarcodeController::class, 'regenerate'])->name('people.codes.regenerate');
});

Route::middleware(['auth', 'active', 'role:admin,management,reception,branch_head,branch_staff'])->group(function () {
    Route::get('/branches', [AdminBranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/{branch}', [AdminBranchController::class, 'show'])->middleware('branch.access')->name('branches.show');
    Route::get('/office/services', [AdminServiceController::class, 'index'])->middleware('branch.access')->name('office.services.index');
});

Route::middleware(['auth', 'active', 'management'])->prefix('management')->name('management.')->group(function () {
    Route::get('/dashboard', [RoleDashboardController::class, 'management'])->name('dashboard');
    Route::get('/applications', [ServiceApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}/receipt', [ServiceApplicationController::class, 'receipt'])->name('applications.receipt');
    Route::get('/applications/{application}', [ServiceApplicationController::class, 'show'])->name('applications.show');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
});

Route::middleware(['auth', 'active', 'role:admin,management,reception,branch_head,branch_staff'])
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export/{report}/{format}', [ReportController::class, 'export'])->name('export');
    });

Route::middleware(['auth', 'active', 'reception'])->prefix('reception')->name('reception.')->group(function () {
    Route::get('/dashboard', [RoleDashboardController::class, 'reception'])->name('dashboard');
});

Route::middleware(['auth', 'active', 'branch.head'])->prefix('branch-head')->name('branch-head.')->group(function () {
    Route::get('/dashboard', [RoleDashboardController::class, 'branchHead'])->name('dashboard');
});

Route::middleware(['auth', 'active', 'branch.staff'])->prefix('branch-staff')->name('branch-staff.')->group(function () {
    Route::get('/dashboard', [RoleDashboardController::class, 'branchStaff'])->name('dashboard');
});

Route::middleware(['auth', 'active', 'role:admin,reception,branch_head,branch_staff', 'branch.access'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/people', [PersonController::class, 'index'])->name('people.index');
    Route::get('/people/create', [PersonController::class, 'create'])->name('people.create');
    Route::post('/people', [PersonController::class, 'store'])->name('people.store');
    Route::get('/people/{person}/edit', [PersonController::class, 'edit'])->name('people.edit');
    Route::put('/people/{person}', [PersonController::class, 'update'])->name('people.update');
    Route::get('/people/{person}/card', [PersonController::class, 'card'])->name('people.card');
    Route::get('/people/{person}/report', [PersonController::class, 'report'])->name('people.report');
    Route::get('/people/{person}/qr/download', [QrBarcodeController::class, 'downloadQr'])->name('people.qr.download');
    Route::get('/people/{person}/qr', [QrBarcodeController::class, 'viewQr'])->name('people.qr.view');
    Route::get('/people/{person}/barcode/download', [QrBarcodeController::class, 'downloadBarcode'])->name('people.barcode.download');
    Route::get('/people/{person}/barcode', [QrBarcodeController::class, 'viewBarcode'])->name('people.barcode.view');
    Route::post('/people/{person}/documents', [DocumentController::class, 'storeForPerson'])->name('people.documents.store');
    Route::post('/people/{person}/notes', [NoteController::class, 'storeForPerson'])->name('people.notes.store');
    Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');

    Route::get('/scanner', [QrBarcodeController::class, 'scanner'])->name('scanner.index');
    Route::get('/scanner/resolve', [QrBarcodeController::class, 'resolve'])->name('scanner.resolve');
    Route::get('/scan/{code}', [QrBarcodeController::class, 'show'])->name('codes.scan');

    Route::get('/applications', [ServiceApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/create', [ServiceApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [ServiceApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}/edit', [ServiceApplicationController::class, 'edit'])->name('applications.edit');
    Route::put('/applications/{application}', [ServiceApplicationController::class, 'update'])->name('applications.update');
    Route::patch('/applications/{application}/status', [ServiceApplicationController::class, 'updateStatus'])->name('applications.status');
    Route::patch('/applications/{application}/assign', [ServiceApplicationController::class, 'assign'])->name('applications.assign');
    Route::get('/applications/{application}/receipt', [ServiceApplicationController::class, 'receipt'])->name('applications.receipt');
    Route::get('/applications/{application}', [ServiceApplicationController::class, 'show'])->name('applications.show');

    Route::post('/applications/{application}/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/applications/{application}/appointments', [AppointmentController::class, 'storeForApplication'])->name('appointments.store');
    Route::post('/applications/{application}/notes', [NoteController::class, 'storeForApplication'])->name('applications.notes.store');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.general.store');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::patch('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/{note}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
});

require __DIR__.'/auth.php';
