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
        $res = $response->decodeResponseJson()['data'];
        $accessToken = $res['access_token'];

        // 認証成功
        $response = $this->getJson('/api/orders', [
            'Authorization' => 'Bearer '.$accessToken
        ]);
        $response->assertOk();
    }

    /**
     * @test
     */
    public function ユーザー認証失敗(): Void
    {
        $response = $this->getJson('/api/orders', [
            'Authorization' => 'Bearer none'
        ]);
        $response->assertUnauthorized();
    }
}
