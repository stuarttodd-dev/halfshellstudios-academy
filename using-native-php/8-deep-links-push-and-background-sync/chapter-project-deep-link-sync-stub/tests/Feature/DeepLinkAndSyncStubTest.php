<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SyncNoteToApi;
use App\Models\Note;
use App\Support\NoteDeepLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DeepLinkAndSyncStubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('nativephp.deeplink_scheme', 'fieldnotes');
        Config::set('nativephp.deeplink_host', 'app');
        session(['unlocked' => true]);
    }

    public function test_note_show_renders_read_only_view(): void
    {
        $note = Note::query()->create([
            'title' => 'Cable run',
            'body' => 'Conduit path documented.',
        ]);

        $this->get(route('notes.show', $note))
            ->assertOk()
            ->assertSee('Conduit path documented.', false)
            ->assertSee('fieldnotes://app/notes/', false);
    }

    public function test_deep_link_helper_builds_scheme_url_and_push_payload(): void
    {
        $note = Note::query()->create([
            'title' => 'Site visit',
            'body' => 'First line of the note for the lock screen.',
        ]);

        $this->assertSame('fieldnotes://app/notes/'.$note->id, NoteDeepLink::url($note));
        $this->assertSame('/notes/'.$note->id, NoteDeepLink::path($note));

        $payload = NoteDeepLink::pushPayload($note);
        $this->assertSame('/notes/'.$note->id, $payload['url']);
        $this->assertSame((string) $note->id, $payload['data']['note_id']);
        $this->assertStringContainsString('First line', $payload['body']);
    }

    public function test_saving_note_dispatches_sync_stub_job(): void
    {
        Bus::fake();

        $this->post(route('notes.store'), [
            'title' => 'Queued sync',
            'body' => 'Saved locally first.',
        ])->assertRedirect(route('notes.index'));

        $note = Note::query()->first();
        $this->assertNotNull($note);
        $this->assertTrue($note->sync_pending);

        Bus::assertDispatched(SyncNoteToApi::class, fn (SyncNoteToApi $job): bool => $job->noteId === $note->id);
    }

    public function test_sync_job_clears_pending_flag(): void
    {
        $note = Note::query()->create([
            'title' => 'Sync me',
            'body' => 'Body',
            'sync_pending' => true,
        ]);

        (new SyncNoteToApi($note->id))->handle();

        $this->assertFalse($note->fresh()->sync_pending);
    }
}
