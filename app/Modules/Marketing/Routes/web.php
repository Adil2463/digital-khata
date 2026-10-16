<?php

declare(strict_types=1);

use App\Modules\Marketing\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('marketing.home');
