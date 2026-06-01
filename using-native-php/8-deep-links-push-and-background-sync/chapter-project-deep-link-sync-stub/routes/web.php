<?php

declare(strict_types=1);

use App\Http\Controllers\NoteController;
use App\Http\Controllers\SettingsController;
use App\Livewire\UnlockGate;
use Illuminate\Support\Facades\Route;

Route::get('/locked', UnlockGate::class)->name('locked');

Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
Route::post('/settings/lock', [SettingsController::class, 'updateLock'])->name('settings.lock');

Route::middleware('biometric')->group(function (): void {
    Route::get('/', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes/{note}/edit', [NoteController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::post('/notes/{note}/share', [NoteController::class, 'share'])->name('notes.share');
});
