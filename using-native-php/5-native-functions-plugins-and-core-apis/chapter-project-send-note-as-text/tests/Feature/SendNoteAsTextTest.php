<?php

declare(strict_types=1);

namespace Tests\Feature;

use Native\Mobile\Facades\Share;
use Tests\TestCase;

class SendNoteAsTextTest extends TestCase
{
    public function test_compose_renders_send_form(): void
    {
        $this->get(route('compose'))
            ->assertOk()
            ->assertSee('Send as text', false)
            ->assertSee('name="body"', false);
    }

    public function test_compose_send_validates_body_and_opens_share_sheet(): void
    {
        Share::shouldReceive('file')
            ->once()
            ->with('Field Notes', 'Transformer pad follow-up', '');

        $this->post(route('compose.send'), ['body' => 'Transformer pad follow-up'])
            ->assertRedirect(route('compose'))
            ->assertSessionHas('status', 'Share sheet opened.');
    }

    public function test_compose_send_requires_body(): void
    {
        $this->post(route('compose.send'), ['body' => ''])
            ->assertSessionHasErrors('body');
    }
}
