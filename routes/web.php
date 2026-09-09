<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    [ImportController::class, 'index']
)->name('import.index');

Route::post(
    '/import',
    [ImportController::class, 'upload']
)->name('import.upload');

Route::post(
    '/import/chunk',
    [ImportController::class, 'uploadChunk']
)->name('import.chunk');

Route::get(
    '/import/{import}/status',
    [ImportController::class, 'status']
)->name('import.status');
