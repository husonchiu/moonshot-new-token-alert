<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\DevController::class, 'index']);
Route::post('/{token}/webhook', [App\Http\Controllers\WebhookController::class, 'index'])->name('webhook');