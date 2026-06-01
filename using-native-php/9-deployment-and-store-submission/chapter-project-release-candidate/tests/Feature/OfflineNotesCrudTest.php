<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Facades\Share;
use Tests\TestCase;

class OfflineNotesCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_saved_notes(): void
    {
        $note = Note::query()->create([
            'title' => 'Transformer pad inspection',
            'body' => 'Checked pad clearance and grounding.',
        ]);

        $this->get(route('notes.index'))
            ->assertOk()
            ->assertSee($note->title, false)
            ->assertSee('class="pb-20"', false);
    }

    public function test_compose_form_creates_note(): void
    {
        $this->get(route('notes.create'))
            ->assertOk()
            ->assertSee('Save note', false);

        $this->post(route('notes.store'), [
            'title' => 'Cable run photos',
            'body' => 'Documented conduit path to panel.',
        ])
            ->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'title' => 'Cable run photos',
            'body' => 'Documented conduit path to panel.',
        ]);
    }

    public function test_note_can_be_updated_and_deleted(): void
    {
        $note = Note::query()->create([
            'title' => 'Client sign-off',
            'body' => 'Pending signature.',
        ]);

        $this->put(route('notes.update', $note), [
            'title' => 'Client sign-off',
            'body' => 'Signed on site.',
        ])->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'body' => 'Signed on site.']);

        $this->delete(route('notes.destroy', $note))
            ->assertRedirect(route('notes.index'));

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_share_sends_saved_note_body(): void
    {
        $note = Note::query()->create([
            'title' => 'Share me',
            'body' => 'Signed on site.',
        ]);

        Share::shouldReceive('file')
            ->once()
            ->with('Field Notes', 'Signed on site.', '');

        $this->post(route('notes.share', $note))
            ->assertRedirect(route('notes.edit', $note))
            ->assertSessionHas('status', 'Share sheet opened.');
    }
}
