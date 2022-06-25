<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Traits\ResponseTrait;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    use ResponseTrait;
    protected $default_per_page = 10;

    /**
     * GET 自分の売買履歴を返すAPI
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @authenticated
     * @group Order
     * @queryParam per_page 1ページの表示件数 Example: 10
     * @apiResourceModel 200 App\Http\Resources\OrderResource
     */
    public function index(Request $request)
    {
        $appends = [];

        $user_id = $request->user()->id;
        $orders = Order::find_relation()
            ->where(function($query) use($user_id) {
                $query->orWhere('seller_id', '=', $user_id)
                    ->orWhere('buyer_id', '=', $user_id);
            });

        $per_page = $this->default_per_page;
        if (ctype_digit($request->per_page)) {
            $per_page = $request->per_page;
            $appends['per_page'] = $per_page;
        }

        return OrderResource::collection(
            $orders
                ->paginate($per_page)
                ->appends($appends)
        );
    }

    /**
     * POST 商品を購入するAPI
     * @param Request $request
     * @return JsonResponse
     *
     * @authenticated
     * @group Order
     * @bodyParam item_id string  購入する商品のID
     * @response 200 {
     *     "message": "Your order has been completed.",
     *     "orders": {
     *         "id": 18,
     *         "accepted_at": "2022-06-23 13:05:06"
     *     }
     * }
     * }
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'filled|integer',
        ]);

        if ($validator->fails()) {
            return $this->getErrorResponse($validator->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = Item::Where('id', $request->item_id)->whereNull('accepted_at')->first();
        if (!$item){
            return $this->getErrorResponse('Item not found.');
        }

        // 購入者
        $buyer = User::Where([
            ['id', '=', $request->user()->id],
            ['point', '>=', $item->point],
        ])->first();
        if (!$buyer) {
            return $this->getErrorResponse('Item not found.');
        }

        $order = DB::transaction(function () use($item, $buyer)
        {
            $point = $item->point;
            Item::where('id', $item->id)
                ->whereNull('accepted_at')
                ->lockForUpdate()
                ->update(['accepted_at' => now()]);

            // 購入者
            User::where([
                ['id', $buyer->id],
                ['point', '>=', $point],
            ])
                ->lockForUpdate()
                ->decrement('point', $point);

            // 出品者
            User::where('id', $item->user_id)
                ->lockForUpdate()
                ->increment('point', $point);

            $order = new Order;
            $order->item_id = $item->id;
            $order->buyer_id = $buyer->id;
            $order->seller_id = $item->user_id;
            $order->accepted_at = now();
            $order->save();

            return Order::find_relation()->find($order->id);
        });

        return $this->getResponse(
            new OrderResource($order),
            'Your order has been completed.'
        );
    }

}
