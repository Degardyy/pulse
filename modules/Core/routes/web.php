<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Auth\LoginController;
use Modules\Core\Http\Controllers\DashboardController;
use Modules\Core\Http\Controllers\EmployeeController;
use Modules\Core\Http\Controllers\LandingController;
use Modules\Core\Http\Controllers\OrganizationController;

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
});
