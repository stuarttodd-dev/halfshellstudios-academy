<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Background sync stub — logs the note id and clears sync_pending when "processed".
 * Replace the log with HTTPS to your API when you add server sync.
 */
class SyncNoteToApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $noteId) {}

    public function handle(): void
    {
        if (class_exists(\Native\Mobile\Facades\Network::class)) {
            try {
                if (! \Native\Mobile\Facades\Network::status()->connected) {
                    $this->release(60);

                    return;
                }
            } catch (\Throwable) {
                // Network plugin optional in browser dev.
            }
        }

        $note = Note::query()->find($this->noteId);

        if ($note === null) {
            return;
        }

        Log::info('SyncNoteToApi stub — would POST note to server', [
            'note_id' => $note->id,
            'title' => $note->title,
        ]);

        $note->update(['sync_pending' => false]);
    }
}
