<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\DepartmentOfficer\DashboardController as OfficerDashboardController;
use App\Http\Controllers\DepartmentOfficer\ApplicationProcessingController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\ExportController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\AppointmentController;
use App\Http\Controllers\Staff\DocumentController;
use App\Http\Controllers\Staff\PaymentController;
use App\Http\Controllers\Staff\PersonController;
use App\Http\Controllers\Staff\QrBarcodeController;
use App\Http\Controllers\Staff\ServiceApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardRedirectController::class)->middleware(['auth', 'active'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::patch('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
    Route::patch('/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
    Route::resource('departments', AdminDepartmentController::class)->except(['show']);
    Route::patch('/departments/{department}/activate', [AdminDepartmentController::class, 'activate'])->name('departments.activate');
    Route::patch('/departments/{department}/deactivate', [AdminDepartmentController::class, 'deactivate'])->name('departments.deactivate');
    Route::resource('services', AdminServiceController::class)->except(['show']);
    Route::delete('/people/{person}', [PersonController::class, 'destroy'])->name('people.destroy');
    Route::patch('/people/{person}/codes/regenerate', [QrBarcodeController::class, 'regenerate'])->name('people.codes.regenerate');
});

Route::middleware(['auth', 'active', 'role:admin,staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', StaffDashboardController::class)->name('dashboard');
    Route::get('/people', [PersonController::class, 'index'])->name('people.index');
    Route::get('/people/create', [PersonController::class, 'create'])->name('people.create');
    Route::post('/people', [PersonController::class, 'store'])->name('people.store');
    Route::get('/people/{person}/edit', [PersonController::class, 'edit'])->name('people.edit');
    Route::put('/people/{person}', [PersonController::class, 'update'])->name('people.update');
    Route::get('/people/{person}/card', [PersonController::class, 'card'])->name('people.card');
    Route::get('/people/{person}/qr/download', [QrBarcodeController::class, 'downloadQr'])->name('people.qr.download');
    Route::get('/people/{person}/barcode/download', [QrBarcodeController::class, 'downloadBarcode'])->name('people.barcode.download');
    Route::get('/people/{person}', [PersonController::class, 'show'])->name('people.show');

    Route::get('/scanner', [QrBarcodeController::class, 'scanner'])->name('scanner.index');
    Route::get('/scanner/resolve', [QrBarcodeController::class, 'resolve'])->name('scanner.resolve');
    Route::get('/scan/{code}', [QrBarcodeController::class, 'show'])->name('codes.scan');

    Route::get('/applications', [ServiceApplicationController::class, 'index'])->name('applications.index');
    Route::post('/applications', [ServiceApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}', [ServiceApplicationController::class, 'show'])->name('applications.show');

    Route::post('/applications/{application}/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::post('/applications/{application}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/applications/{application}/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
});

Route::middleware(['auth', 'active', 'role:admin,department_officer'])->prefix('officer')->name('officer.')->group(function () {
    Route::get('/dashboard', OfficerDashboardController::class)->name('dashboard');
    Route::get('/applications', [ApplicationProcessingController::class, 'index'])->name('applications.index');
    Route::patch('/applications/{application}/status', [ApplicationProcessingController::class, 'updateStatus'])->name('applications.status');
});

Route::middleware(['auth', 'active', 'role:admin,manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', ManagerDashboardController::class)->name('dashboard');
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/exports/applications.pdf', [ExportController::class, 'applicationsPdf'])->name('exports.applications.pdf');
    Route::get('/exports/applications.xlsx', [ExportController::class, 'applicationsExcel'])->name('exports.applications.excel');
});

require __DIR__.'/auth.php';
