<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_landing_page_renders_the_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertViewIs('dashboard.overview');
    }
}
