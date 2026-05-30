<?php

declare(strict_types=1);

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/notes');

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
