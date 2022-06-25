<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Order;
use App\Models\User;
use App\Models\Item;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function loginUser()
    {
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * @test
     */
    public function 注文()
    {
        $this->loginUser();

        $seller = User::factory()->create();
        Item::factory()->create([
            'user_id' => $seller->id,
        ]);

        $item = Item::first();
        $this->post('/api/orders/', ['item_id' => $item->id])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'item' => [
                        'id' => $item->id,
                    ],
                    'buyer' => [
                        'id' => $this->user->id,
                    ],
                    'seller' => [
                        'id' => $seller->id,
                    ],
                ],
            ]);

        // 注文が登録されている
        $this->assertDatabaseCount('orders', 1);

        // 商品が購入済みになっている
        $this->assertNotNull(Item::find($item->id)->accepted_at);

        // 購入者のポイントが減っている
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'point' => ($this->user->point - $item->point)
        ]);

        // 出品者のポイントが増えている
        $this->assertDatabaseHas('users', [
            'id' => $seller->id,
            'point' => ($seller->point + $item->point)
        ]);
    }

    /**
     * @test
     */
    public function 注文済みの場合購入できない()
    {
        $this->loginUser();

        $seller = User::factory()->create();
        Item::factory()->create([
            'user_id' => $seller->id,
            'accepted_at' => now(),
        ]);

        $item = Item::first();
        $this->post('/api/orders/', ['item_id' => $item->id])
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function ポイントが足りない場合購入できない()
    {
        $this->loginUser();
        $this->user->update(['point' => 0]);

        $seller = User::factory()->create();
        Item::factory()->create([
            'user_id' => $seller->id,
        ]);

        $item = Item::first();
        $this->post('/api/orders/', ['item_id' => $item->id])
            ->assertStatus(404);
    }

    /**
     * @test
     */
    public function 売買履歴()
    {
        $this->loginUser();

        $seller1 = User::factory()->create();
        Item::factory()->create([
            'user_id' => $seller1->id,
        ]);
        $item1 = Item::first();

        $seller2 = User::factory()->create();
        Item::factory()->create([
            'user_id' => $seller2->id,
        ]);
        $item2 = Item::Where('user_id', $seller2->id)->first();

        $this->post('/api/orders/', ['item_id' => $item1->id]);
        $this->post('/api/orders/', ['item_id' => $item2->id]);

        // 購入履歴が取得できる
        $this->get('/api/orders/')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'data' => [
                    [
                        'item' => [
                            'id' => $item1->id,
                        ],
                        'buyer' => [
                            'id' => $this->user->id,
                        ],
                        'seller' => [
                            'id' => $seller1->id,
                        ],
                    ],
                    [
                        'item' => [
                            'id' => $item2->id,
                        ],
                        'buyer' => [
                            'id' => $this->user->id,
                        ],
                        'seller' => [
                            'id' => $seller2->id,
                        ],
                    ],
                ]
            ]);

        // 出品者側も販売履歴が確認できる
        $this->actingAs($seller1, 'sanctum');
        $res = $this->get('/api/orders/')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'item' => [
                            'id' => $item1->id,
                        ],
                        'buyer' => [
                            'id' => $this->user->id,
                        ],
                        'seller' => [
                            'id' => $seller1->id,
                        ],
                    ],
                ]
            ]);
    }
}
