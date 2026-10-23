<?php

namespace Tests\Feature\Frontend;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HomeAgendaVideoTest extends TestCase
{
    use DatabaseTransactions;

    public function test_home_page_agenda_video_plays_inline_in_the_section(): void
    {
        $response = $this->get(route('frontend.home'));

        $response->assertOk()
            ->assertSee('rel="preload" href="css/bootstrap.min.css" as="style"', false)
            ->assertSee('rel="preload" href="css/vendors.css" as="style"', false)
            ->assertSee('rel="preload" href="css/style.css" as="style"', false)
            ->assertSee('data-agenda-open-video', false)
            ->assertSee('data-agenda-video-shell', false)
            ->assertSee('agendaVideoFrame', false)
            ->assertDontSee('agendaVideoModal', false);
    }
}
