<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Admin\AuditLogController;
use Modules\Core\Http\Controllers\Admin\UserController;
use Modules\Core\Http\Controllers\ApprovalController;
use Modules\Core\Http\Controllers\Auth\LoginController;
use Modules\Core\Http\Controllers\DashboardController;
use Modules\Core\Http\Controllers\DocumentController;
use Modules\Core\Http\Controllers\EmployeeController;
use Modules\Core\Http\Controllers\LandingController;
use Modules\Core\Http\Controllers\NotificationController;
use Modules\Core\Http\Controllers\OrganizationController;
use Modules\Core\Http\Controllers\ReportController;

Route::get('/', LandingController::class)->name('core.landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('core.dashboard');
    Route::get('/organization', [OrganizationController::class, 'index'])->name('core.organization.index');
    Route::get('/employees', [EmployeeController::class, 'index'])->name('core.employees.index');

    Route::get('/approvals', [ApprovalController::class, 'index'])->name('core.approvals.index');
    Route::post('/approvals/{instance}/approve', [ApprovalController::class, 'approve'])->name('core.approvals.approve');
    Route::post('/approvals/{instance}/reject', [ApprovalController::class, 'reject'])->name('core.approvals.reject');

    Route::get('/documents', [DocumentController::class, 'index'])->name('core.documents.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('core.documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('core.documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('core.documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('core.documents.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('core.reports.index');
    Route::get('/reports/{key}/download', [ReportController::class, 'download'])->name('core.reports.download');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('core.notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('core.notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('core.notifications.read');

    Route::get('/admin/audit', [AuditLogController::class, 'index'])->name('core.admin.audit.index');

    Route::prefix('admin/users')->name('core.admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
    });
});
