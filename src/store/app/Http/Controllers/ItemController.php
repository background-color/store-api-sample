<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use \Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\ItemResource;

class ItemController extends Controller
{
    /**
     * GET 商品一覧を返すAPI
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @group Item
     * @queryParam is_sale 1=販売商品のみ表示 Example: 1
     * @response 200 {
     * "data": [
     *         {
     *             "id": 1,
     *             "name": "登山靴",
     *             "point": 100,
     *             "description": "テキストテキスト",
     *             "user_id": 1,   // 登録者ID
     *             "accepted_at": null  // 注文日
     *         }
     *     ]
     * }
     */
    public function index(Request $request)
    {
        if ($request->is_sale){
            $items = Item::whereNull('accepted_at')->get();
        } else {
            $items = Item::all();
        }
        return ItemResource::collection($items);
    }

    /**
     * GET 指定の商品を返すAPI
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @group Item
     * @urlParam id int  商品のID Example: 1
     * @response 200 {
     * "data": {
     *             "id": 1,
     *             "name": "登山靴",
     *             "point": 100,
     *             "description": "テキストテキスト",
     *             "user_id": 1,   // 登録者ID
     *             "accepted_at": null  // 注文日
     *         }
     * }
     * @apiResourceModel 200 App\Http\Resources\ItemResource
     */
    public function show($id)
    {
        $item = Item::where('id', $id)
                ->whereNull('accepted_at')
                ->first();

        if (!$item){
            return response()->json(errmsg('Item not found.'), Response::HTTP_NOT_FOUND);
        }

        return (new ItemResource($item));
    }

    /**
     * POST 商品を登録するAPI
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @authenticated
     * @group Item
     * @bodyParam name string required 商品名 Example: 登山靴
     * @bodyParam point int required 価格（ポイント） Example: 1000
     * @bodyParam description string 説明文 Example: 防水加工です
     * @response 200 {
     *     "message": "Your item has been successfully created.",
     *     "data": {
     *         "id": 1,
     *         "name": "登山靴",
     *         "point": 1000,
     *         "description": "防水加工です",
     *         "user_id": 1,  // 登録者ID
     *         "accepted_at": null  // 受注日
     *     }
     * }
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'point' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(errmsg($validator->messages()), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = Item::create([
            'name' =>  $request->name,
            'point' => $request->point,
            'user_id' => $request->user()->id,
            'description' => $request->description,
        ]);

        return response()->json([
                'message' => 'Your item has been successfully created.',
                'data' => new ItemResource($item),
            ], Response::HTTP_OK);
    }


    /**
     * PUT 商品を更新するAPI
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @authenticated
     * @group Item
     * @urlParam id int  更新する商品のID Example: 1
     * @bodyParam name string required 商品名 Example: シェラカップ
     * @bodyParam point int required 価格（ポイント） Example: 800
     * @bodyParam description string 説明文 Example: ステンレスです
     * @response 200 {
     *     "message": "Your item has been successfully updated.",
     *     "data": {
     *         "id": 1,
     *         "name": "シェラカップ",
     *         "point": 800,
     *         "description": "ステンレスです",
     *         "user_id": 1,  // 登録者ID
     *         "accepted_at": null  // 受注日
     *     }
     * }
     */
    public function update(Request $request, $id)
    {
          $validator = Validator::make($request->all(), [
              'name' => 'filled',
              'point' => 'filled|integer',
          ]);

          if ($validator->fails()) {
              return response()->json(errmsg($validator->messages()), Response::HTTP_UNPROCESSABLE_ENTITY);
          }

          if (item::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->whereNull('accepted_at')
            ->doesntExist()) {
                return response()->json(errmsg('Item not found.'), Response::HTTP_NOT_FOUND);
          }

          $item = Item::find($id);
          $item->name = $request->name;
          $item->point = $request->point;
          $item->description = $request->description;
          $item->save();

          return response()->json([
                  'message' => 'Your item has been successfully updated.',
                  'data' => new ItemResource($item),
              ], Response::HTTP_OK);
    }


    /**
     * DELETE 商品を削除するAPI
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @authenticated
     * @group Item
     * @urlParam id int  削除する商品のID Example: 1
     * @response 200 {
     *     "message": "Your item has been successfully updated.",
     *     "data": {
     *         "id": 1,
     *         "name": "シェラカップ",
     *         "point": 800,
     *         "description": "ステンレスです",
     *         "user_id": 1,  // 登録者ID
     *         "accepted_at": null  // 受注日
     *     }
     * }
     */
    public function destroy(Request $request, $id)
    {
        if (item::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->whereNull('accepted_at')
            ->doesntExist()) {
                return response()->json(errmsg('Item not found.'), Response::HTTP_NOT_FOUND);
        }

        $item = Item::find($id);
        $item->delete();

        return response()->json([
            'message' => 'Your item has been successfully deleted.',
            'data' => new ItemResource($item),
        ], Response::HTTP_OK);
    }
    //
}
