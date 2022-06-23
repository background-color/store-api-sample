<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use \Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\OrderResource;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * GET 自分の売買履歴を返すAPI
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @authenticated
     * @group Order
     * @apiResourceModel 200 App\Http\Resources\OrderResource
     */
    public function index(Request $request)
    {
        $orders = Order::find_relation($request->user()->id);
        return OrderResource::collection($orders);
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
            return response()->json(errmsg($validator->messages()), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = Item::Where('id', $request->item_id)->whereNull('accepted_at')->first();
        if (!$item){
            return response()->json(errmsg('Item not found.'), Response::HTTP_NOT_FOUND);
        }

        // 購入者
        $buyer = User::Where([
            ['id', '=', $request->user()->id],
            ['point', '>=', $item->point],
        ])->first();
        if (!$buyer) {
            return response()->json(errmsg('Not enough point.'), Response::HTTP_NOT_FOUND);
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

            return $order;
        });

        /////////////////////////
        // TODO 戻り地に item_id などが入ってない。リソースの設定で入れていないから。with関数で取得し直すか？
        ///////////////////////// 
        return response()->json([
            'message' => 'Your order has been completed.',
            'orders' => new OrderResource($order),
            ], Response::HTTP_OK);
    }

}
