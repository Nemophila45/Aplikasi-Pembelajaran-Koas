<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Root route should redirect into the authenticated area.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->app->handle(Request::create('/', 'GET'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/patients', $response->headers->get('Location'));
    }
}
