<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Order;

class OrderTest extends TestCase
{

    /**
     * @test
     */
    public function 注文取得()
    {
        $orders = Order::has('item')->get();
        dd($orders);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
