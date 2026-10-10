<?php

namespace Tests\Feature;

use Tests\TestCase;

class AskTest extends TestCase
{
    // The search itself needs MariaDB 11.7+ and an embedding provider, so it is
    // exercised with `php artisan ask` / curl against the real stack. This only
    // guards the input contract of the endpoint.
    public function test_the_ask_endpoint_requires_a_question(): void
    {
        $this->postJson('/api/ask', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('question');
    }
}
