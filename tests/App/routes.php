<?php

use Illuminate\Support\Facades\Route;
use Tsekka\Prerender\Tests\App\Controllers\TestController;

// Route::middleware(['web'])->group(function () {
    Route::get('test/prerender/initial', TestController::class);
    Route::get('test/prerender/prerendered/{slug?}', TestController::class)
        ->middleware(\Tsekka\Prerender\Http\Middleware\PrerenderMiddleware::class);
// });
