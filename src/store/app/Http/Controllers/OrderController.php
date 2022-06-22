<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use \Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Item;
use App\Models\Order;

class OrderController extends Controller
{
    public function getAllOrder(Request $request)
    {
        $orders = Order::find_relation($request->user()->id);
        return response()->json(['orders' => $orders], Response::HTTP_OK);
    }

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
            Item::where('id', $item->id)->lockForUpdate()
                ->update(['accepted_at' => now()]);

            // buyer user
            User::where('id', $buyer->id)->lockForUpdate()
                ->decrement('point', $point);

            // seller user
            User::where('id', $item->user_id)->lockForUpdate()
                ->increment('point', $point);

            $order = new Order;
            $order->item_id = $item->id;
            $order->buyer_id = $buyer->id;
            $order->seller_id = $item->user_id;
            $order->accepted_at = now();
            $order->save();

            return $order;
        });

        /*
        Order::create([
            'item_id' =>  1,
            'seller_id' => $request->user()->id,
            'buyer_id' => 1,
            'accepted_at' => now(),
        ]);
        */

        return response()->json([
            'message' => 'order created',
            'orders' => $order,
            ], Response::HTTP_OK);
    }

}
