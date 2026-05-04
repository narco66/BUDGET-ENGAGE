<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_url_shows_landing_page_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('BUDGET-ENGAGE', false);
    }
}
