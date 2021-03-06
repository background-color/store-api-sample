<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $item1 = [
        'name' => 'test',
        'point' => 100,
        'description' => 'text',
    ];

    protected $item2 = [
        'name' => 'test2',
        'point' => 1000,
        'description' => 'text',
    ];

    protected $item3 = [
        'name' => 'test3',
        'point' => 10000,
        'description' => 'update',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    protected function loginUser()
    {
        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * @test
     */
    public function 商品登録()
    {
        $this->loginUser();

        // 成功
        $response = $this->post('/api/items', $this->item1);
        $response->assertOk();

        $response = $this->post('/api/items', $this->item2);
        $response->assertOk();

        // Validate
        $response = $this->post('/api/items', [
            'name' => 'test',
            'point' => 'test',
        ]);
        $response->assertStatus(422);


        // 全件取得
        $response = $this->get('/api/items');
        $response->assertOk()
                ->assertJsonCount(2, 'data')   // item2件
                ->assertJsonFragment($this->item1)
                ->assertJsonFragment($this->item2);

        // 更新
        $item = Item::first();
        $response = $this->put('/api/items/'.$item->id, $this->item3);
        $response->assertOk();

        // 更新内容取得
        $response = $this->get('/api/items/'.$item->id);
        $response->assertOk()
                ->assertJsonFragment($this->item3);

        // 一部更新
        $item = Item::first();
        $response = $this->put('/api/items/'.$item->id, ['name' => 'upd']);
        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'upd',
                'point' => $item->point,
                'description' => $item->description,
            ]);
    }

    /**
     * @test
     */
    public function 商品削除()
    {
        $this->loginUser();

        Item::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseCount('items', 2);

        // 削除
        $item = Item::first();
        $response = $this->delete('/api/items/'.$item->id);
        $response->assertOk();

        //削除されている
        $this->assertDeleted($item);
        $this->assertDatabaseCount('items', 1);
    }

    /**
     * @test
     */
    public function 他ユーザでの更新不可()
    {
        $regist_user = User::factory()->create();

        Item::factory()->create([
            'user_id' => $regist_user->id,
        ]);

        $this->loginUser();

        // 更新不可
        $item = Item::first();
        $response = $this->put('/api/items/'.$item->id, $this->item3);
        $response->assertStatus(400);

        // 更新不可
        $response = $this->delete('/api/items/'.$item->id);
        $response->assertStatus(400);
    }

    /**
     * @test
     */
    public function 購入済みは変更不可()
    {
        $this->loginUser();

        Item::factory()->create([
            'user_id' => $this->user->id,
            'status' => Item::STATUS_SOLDOUT,
        ]);

        // 変更不可
        $item = Item::first();
        $response = $this->put('/api/items/'.$item->id, $this->item3);
        $response->assertStatus(400);

        // 変更不可
        $response = $this->delete('/api/items/'.$item->id);
        $response->assertStatus(400);
    }
}
