<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;

use Illuminate\Testing\Fluent\AssertableJson;

class ExampleFeatureTest extends TestCase
{
    /**
     * @test
     */

    use RefreshDatabase;
    private $accessToken = null;

    protected function setUp(): Void
    {
        parent::setUp();

        User::create([
            "name" => 'sample@example.com',
            "password" => bcrypt('password'),
        ]);
        /*
        $response = $this->post('/api/register', [
            'email' => 'sample@example.com',
            'password' => 'sample123'
        ]);
        $response = $this->post('/api/login', [
            'email' => 'sample@example.com',
            'password' => 'password'
        ]);
        $response->assertOk();
        $this->accessToken = $response->decodeResponseJson('access_token');
        */
        $this->assertTrue(true);
    }
}
