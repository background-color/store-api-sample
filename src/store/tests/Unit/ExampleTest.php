<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class ExampleTest extends TestCase
{
    /**
     * @return void
     */
    use RefreshDatabase;
    private $accessToken = null;
    public function test_example()
    {
        $this->assertTrue(true);
    }
/*
    public function setUp() :void
    {
        parent::setUp();

        $response = $this->post('/api/register', [
            'email' => 'sample@example.com',
            'password' => 'sample123'
        ]);
        $response = $this->post('/api/login', [
            'email' => 'sample@example.com',
            'password' => 'sample123'
        ]);
        $response->assertOk();
        $this->accessToken = $response->decodeResponseJson('access_token');
    }
*/
}
