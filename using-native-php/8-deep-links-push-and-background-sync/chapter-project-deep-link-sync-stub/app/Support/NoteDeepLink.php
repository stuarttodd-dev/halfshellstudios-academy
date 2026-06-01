<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Note;

final class NoteDeepLink
{
    public static function url(Note $note): string
    {
        $scheme = config('nativephp.deeplink_scheme', 'fieldnotes');
        $host = config('nativephp.deeplink_host', 'app');

        return sprintf('%s://%s/notes/%d', $scheme, $host, $note->id);
    }

    public static function path(Note $note): string
    {
        return '/notes/'.$note->id;
    }

    public static function pushPayload(Note $note): array
    {
        $preview = \Illuminate\Support\Str::limit($note->body, 120);

        return [
            'title' => 'New note: '.$note->title,
            'body' => $preview,
            'url' => self::path($note),
            'data' => [
                'note_id' => (string) $note->id,
            ],
        ];
    }
}
