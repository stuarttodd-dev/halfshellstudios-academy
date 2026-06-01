<?php

declare(strict_types=1);

use App\Models\Note;
use App\Support\NoteDeepLink;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('field-notes:push-payload {note : Note ID}', function (string $noteId): int {
    $note = Note::query()->findOrFail($noteId);
    $payload = NoteDeepLink::pushPayload($note);

    $this->info('Use this payload when sending FCM (body = lock-screen alert text):');
    $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $this->newLine();
    $this->comment('Deep link URL: '.NoteDeepLink::url($note));
    $this->comment('In-app path for --url: '.NoteDeepLink::path($note));

    return self::SUCCESS;
})->purpose('Print push notification payload for a note deep link (development)');
