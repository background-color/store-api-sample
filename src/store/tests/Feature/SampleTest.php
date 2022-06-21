<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class SampleTest extends TestCase
{

    use RefreshDatabase;
    private $accessToken = null;
    private $user1_pass = 'password';

    /**
     * @test
     */
    protected function setUp(): Void
    {
        parent::setUp();
        User::create([
            "name" => 'test',
            "email" => 'sample@example.com',
            "password" => bcrypt('password'),
        ]);
        $response = $this->post('/api/login', [
            'email' => 'sample@example.com',
            'password' => 'password'
        ]);
        $response->assertOk();
        $this->accessToken = $response->decodeResponseJson()['access_token'];
/*
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer '.$this->accessToken
        ]);
        $response->assertOk()->assertJsonFragment([
            'name' => 'test',
            'email' => 'sample@example.com'
        ]);

        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer AAA'
        ]);
        $response->assertUnauthorized();
        print_r($response->decodeResponseJson());
*/
    }

    /**
     * @test
     */
    public function canNotWithAuth()
    {
        $response = $this->get('/api/me', [
            'Authorization' => 'Bearer test'
        ]);
        $response->assertUnauthorized();
    }

    /**
     * @test
     */
    public function 認証が通り()
    {
        $response = $this->get('/api/user', [
            'Authorization' => 'Bearer '.$this->accessToken
        ]);
        $response->assertOk()->assertJsonFragment([
            'name' => 'test',
            'email' => 'sample@example.com'
        ]);
    }
}
