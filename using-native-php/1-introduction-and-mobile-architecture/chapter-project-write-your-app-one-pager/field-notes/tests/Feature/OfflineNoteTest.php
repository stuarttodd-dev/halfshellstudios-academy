<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OfflineNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_notes_index_renders(): void
    {
        $this->get('/notes')->assertOk()->assertSee('Field notes');
    }

    public function test_store_creates_pending_note(): void
    {
        $this->post('/notes', ['body' => 'Checked transformer pad A.'])
            ->assertRedirect();

        $this->assertDatabaseHas('offline_notes', [
            'body' => 'Checked transformer pad A.',
            'sync_state' => OfflineNote::SYNC_PENDING,
        ]);
    }

    public function test_activity_shows_pending_count(): void
    {
        OfflineNote::create(['body' => 'One', 'sync_state' => OfflineNote::SYNC_PENDING]);
        OfflineNote::create(['body' => 'Two', 'sync_state' => OfflineNote::SYNC_SYNCED]);

        $this->get('/activity')
            ->assertOk()
            ->assertSee('Pending')
            ->assertSee('1', false);
    }
}
