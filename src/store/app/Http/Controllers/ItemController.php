<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\ResponseTrait;
use App\Models\Item;
use App\Http\Resources\ItemResource;
use Exception;

class ItemController extends Controller
{
    use ResponseTrait;
    protected $default_per_page = 10;

    /**
     * GET 商品一覧を返すAPI
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @group Item
     * @queryParam is_sale int 1=販売商品のみ表示 Example: 1
     * @queryParam per_page int 1ページの表示件数 Example: 10
     * @response 200 {
     * "data": [
     *         {
     *             "id": 1,
     *             "name": "登山靴",
     *             "point": 100,
     *             "description": "テキストテキスト",
     *             "user_id": 1,   // 登録者ID
     *             "status": "sale"  // ステータス
     *         }
     *     ]
     * }
     */
    public function index(Request $request)
    {
        $appends = [];

        $items = new Item();
        if ($request->is_sale) {
            $items = $items->where('status', Item::STATUS_SALE);
            $appends['is_sale'] = '1';
        }

        $per_page = $this->default_per_page;
        if (ctype_digit($request->per_page)) {
            $per_page = $request->per_page;
            $appends['per_page'] = $per_page;
        }

        $items = $items->paginate($per_page);
        $items->appends($appends);

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
     *             "status": "sale"  // ステータス
     *         }
     * }
     * @apiResourceModel 200 App\Http\Resources\ItemResource
     */
    public function show($id)
    {
        $item = Item::find($id);
        if (!$item) {
            return $this->getErrorResponse('Item not found.');
        }

        return new ItemResource($item);
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
     *         "status": "sale"  // ステータス
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
            return $this->getErrorResponse($validator->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $item = Item::create([
            'name' =>  $request->name,
            'point' => $request->point,
            'user_id' => $request->user()->id,
            'description' => $request->description,
        ]);

        return $this->getResponse(
            new ItemResource($item),
            'Your item has been successfully created.'
        );
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
     *         "status": "sale"  // ステータス
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
            return $this->getErrorResponse(
                $validator->messages(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        DB::beginTransaction();
        try {
            $item = Item::lockForUpdate()->find($id);
            if (!$item
                || $item->user_id != $request->user()->id
                || $item->status != Item::STATUS_SALE) {
                throw new Exception(
                    'Item could not be changed.',
                    Response::HTTP_BAD_REQUEST
                );
            }

            if ($request->name) {
                $item->name = $request->name;
            }
            if ($request->point) {
                $item->point = $request->point;
            }
            if($request->description) {
                $item->description = $request->description;
            }
            $item->save();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            $status_code = $e->getCode() ? $e->getCode() : Response::HTTP_NOT_FOUND;
            return $this->getErrorResponse($e->getMessage(), $status_code);
        }

        return $this->getResponse(
            new ItemResource($item),
            'Your item has been successfully updated.'
        );
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
     *     "message": "Your item has been successfully deleted.",
     *     "data": {
     *         "id": 1,
     *         "name": "シェラカップ",
     *         "point": 800,
     *         "description": "ステンレスです",
     *         "user_id": 1,  // 登録者ID
     *         "status": "sale"  // ステータス
     *     }
     * }
     */
    public function destroy(Request $request, $id)
    {
        if (item::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->where('status', Item::STATUS_SALE)
            ->doesntExist()) {
                return $this->getErrorResponse(
                    'Item could not be deleted.',
                    Response::HTTP_BAD_REQUEST
                );
        }

        $item = Item::find($id);
        $item->delete();

        return $this->getResponse(
            new ItemResource($item),
            'Your item has been successfully deleted.'
        );
    }
    //
}
