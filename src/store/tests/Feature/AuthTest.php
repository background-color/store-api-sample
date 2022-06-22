<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{

    use RefreshDatabase;

    /**
     * @test
     */
    public function ユーザー登録(): Void
    {
        $email = 'sample@example.com';
        $password = 'password';
        $name = 'test';

        $response = $this->post('/api/register', [
            'email' => $email,
            'password' => $password,
            'name' => $name,
        ]);
        $response->assertOk();

        $response = $this->post('/api/login', [
            'email' => $email,
            'password' => $password
        ]);
        $response->assertOk();
        $accessToken = $response->decodeResponseJson()['access_token'];

        // 認証成功
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer '.$accessToken
        ]);
        $response->assertOk()->assertJsonFragment([
            'name' => $name,
            'email' => $email
        ]);
    }

    /**
     * @test
     */
    public function ユーザー認証失敗(): Void
    {
        $response = $this->getJson('/api/user', [
            'Authorization' => 'Bearer none'
        ]);
        $response->assertUnauthorized();
    }
}
