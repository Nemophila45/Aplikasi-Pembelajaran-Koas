<?php

use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

// Redirect root langsung ke daftar pasien
Route::redirect('/', '/patients');

// ======== Guest (belum login) ========
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// ======== Logout (khusus user login) ========
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ======== Area yang butuh auth ========
Route::middleware('auth')->group(function (): void {

    // Daftar pasien (boleh semua user yang login)
    Route::get('/patients', [PatientController::class, 'index'])
        ->name('patients.index');

    // Tambah pasien (hanya admin, doctor)
    Route::get('/patients/create', [PatientController::class, 'create'])
        ->middleware('role:admin,doctor')
        ->name('patients.create');

    // Simpan pasien baru (hanya admin, doctor)
    Route::post('/patients', [PatientController::class, 'store'])
        ->middleware('role:admin,doctor')
        ->name('patients.store');

    // ======== Record Management (create, store, download) ========
    Route::middleware('role:admin,doctor,koas')->group(function (): void {
        Route::get('/patients/{patient}/records/create', [PatientController::class, 'createRecord'])
            ->name('patients.records.create');

        Route::post('/patients/{patient}/records', [PatientController::class, 'storeRecord'])
            ->name('patients.records.store');
    });

    Route::middleware('role:admin,doctor,koas,management')->group(function (): void {
        Route::get('/patients/{patient}/records/{medicalRecord}', [PatientController::class, 'showRecord'])
            ->name('patients.records.show');
    });

    Route::middleware('role:admin,doctor,koas')->group(function (): void {
        Route::get('/patients/{patient}/records/{medicalRecord}/download', [PatientController::class, 'downloadRecord'])
            ->name('patients.records.download');
    });

    // ======== Record Management (edit, update, destroy) ========
    Route::middleware('role:admin,doctor')->group(function (): void {
        Route::get('/patients/{patient}/records/{medicalRecord}/edit', [PatientController::class, 'editRecord'])
            ->name('patients.records.edit');

        Route::put('/patients/{patient}/records/{medicalRecord}', [PatientController::class, 'updateRecord'])
            ->name('patients.records.update');

        Route::delete('/patients/{patient}/records/{medicalRecord}', [PatientController::class, 'destroyRecord'])
            ->name('patients.records.destroy');
    });

    // Detail pasien, ini harus DI PALING AKHIR
    // supaya tidak nabrak /patients/create
    Route::get('/patients/{patient}', [PatientController::class, 'show'])
        ->name('patients.show');
});

// ======== Admin User Management ========
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

// History: tetap admin
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
        Route::delete('/history', [HistoryController::class, 'destroy'])->name('history.destroy');
    });

// Reports: admin & management
Route::middleware(['auth', 'role:admin,management'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/reports/disease-chart', [ReportController::class, 'diseaseChart'])
            ->name('reports.disease-chart');
    });
