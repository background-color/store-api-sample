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
use Exception;

class OrderController extends Controller
{
    use ResponseTrait;
    protected $default_per_page = 10;

    /**
     * GET 自分の売買履歴を返すAPI
     *
     * @authenticated
     * @group Order
     * @queryParam per_page int 1ページの表示件数 Example: 10
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
     *
     * @authenticated
     * @group Order
     * @bodyParam item_id int  購入する商品のID
     * @response 200 {
     *    "message": "Your order has been completed.",
     *     "data": {
     *         "item": {
     *             "id": 1,
     *             "name": "登山靴",
     *             "point": 100,
     *             "description": "テキストテキスト",
     *             "user_id": 1,
     *             "status": "sold out"
     *         },
     *         "seller": {
     *             "id": 1,
     *             "name": "山田花子"
     *         },
     *         "buyer": {
     *             "id": 2,
     *             "name": "鈴木太郎"
     *         },
     *         "accepted_at": "2022-06-25 17:22:57"
     *     }
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

        DB::beginTransaction();
        try {
            $item = Item::lockForUpdate()->find($request->item_id);
            if (!$item
                || $item->status != Item::STATUS_SALE){
                throw new Exception(
                    'Not for sale Item.',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // 購入者
            $buyer = User::lockForUpdate()->find($request->user()->id);
            if (!$buyer
                || $buyer->point < $item->point) {
                throw new Exception(
                    'Not enough point.',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // 出品者
            $seller = User::lockForUpdate()->find($item->user_id);

            $item->status = Item::STATUS_SOLDOUT;
            $item->update();
            $buyer->decrement('point', $item->point);
            $seller->increment('point', $item->point);

            $order = new Order;
            $order->item_id = $item->id;
            $order->buyer_id = $buyer->id;
            $order->seller_id = $item->user_id;
            $order->accepted_at = now();
            $order->save();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return $this->getErrorResponse($e->getMessage(), $e->getCode());
        }

        $order = Order::find_relation()->find($order->id);
        return $this->getResponse(
            new OrderResource($order),
            'Your order has been completed.'
        );
    }

}
