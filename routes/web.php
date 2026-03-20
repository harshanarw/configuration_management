<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    DashboardController,
    ConfigurationController,
    ApprovalController,
    AuditController,
    RbacController,
    ThreatController,
};

// ── Auth ──────────────────────────────────────────────
Route::get('/',      [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Authenticated Routes ──────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Configuration Repository — FR1.1, FR1.2, FR1.3
    Route::resource('configurations', ConfigurationController::class);
    Route::get('/configurations/{configuration}/history',         [ConfigurationController::class, 'history'])->name('configurations.history');
    Route::get('/configurations/{configuration}/version/{versionId}', [ConfigurationController::class, 'version'])->name('configurations.version');

    // Approval Workflow — FR3.1, FR3.2, FR3.3
    Route::resource('approvals', ApprovalController::class)->only(['index','show'])->parameters(['approvals' => 'changeRequest']);
    Route::post('/approvals/{changeRequest}/review',  [ApprovalController::class, 'review'])->name('approvals.review');
    Route::post('/approvals/{changeRequest}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');

    // Audit Logs — FR2.3, NFR3.1
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');

    // Threat Model
    Route::get('/threats', [ThreatController::class, 'index'])->name('threats.index');

    // RBAC Management — FR2.1, FR2.2 (Admin only)
    Route::get('/rbac',             [RbacController::class, 'index'])->name('rbac.index');
    Route::post('/rbac',            [RbacController::class, 'store'])->name('rbac.store');
    Route::patch('/rbac/{user}',    [RbacController::class, 'update'])->name('rbac.update');
    Route::patch('/rbac/{user}/toggle', [RbacController::class, 'toggle'])->name('rbac.toggle');
});
