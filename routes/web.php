<?php

use Illuminate\Support\Facades\Route;
use DhanikKeraliya\SecurityScanner\Http\Controllers\ScanController;

Route::prefix(config('securescan.route_prefix', '_securescan'))
    ->middleware(config('securescan.middleware', ['web']))
    ->group(function () {

        Route::get('/', [ScanController::class, 'index'])->name('securescan.index');
        Route::get('/stream', [ScanController::class, 'stream'])->name('securescan.stream');
        Route::get('/status', [ScanController::class, 'status'])->name('securescan.status');

    });