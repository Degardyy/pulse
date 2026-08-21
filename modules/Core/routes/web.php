<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\LandingController;

Route::get('/', LandingController::class)->name('core.landing');
