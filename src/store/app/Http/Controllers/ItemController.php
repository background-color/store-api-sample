<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use \Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    public function getAllItems()
    {
        $items = Item::whereNull('accepted_at')->get(['id', 'name', 'point', 'user_id', 'description']);
        return response()->json(['items' => $items], Response::HTTP_OK);
    }

    public function createItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'point' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Item::create([
            'name' =>  $request->name,
            'point' => $request->point,
            'user_id' => $request->user()->id,
            'description' => $request->description,
        ]);

        return response()->json('item created', Response::HTTP_OK);
    }

    public function getItem($id)
    {
        $item = Item::where('id', $id)
                ->whereNull('accepted_at')
                ->get(['id', 'name', 'point', 'user_id', 'description']);

        if ($item->isEmpty()){
            return response()->json('item not found', Response::HTTP_NOT_FOUND);
        }
        return response()->json(['items' => $item], Response::HTTP_OK);
    }

    public function updateItem(Request $request, $id)
    {
          $validator = Validator::make($request->all(), [
              'name' => 'filled',
              'point' => 'filled|integer',
          ]);

          if ($validator->fails()) {
              return response()->json($validator->messages(), Response::HTTP_UNPROCESSABLE_ENTITY);
          }

          if (item::where('id', $id)->where('user_id', $request->user()->id)
                                    ->whereNull('accepted_at')
                                    ->doesntExist()) {
              return response()->json(errmsg('Item not found.'), Response::HTTP_NOT_FOUND);
          }

          $item = Item::find($id);
          $item->name = $request->name;
          $item->point = $request->point;
          $item->description = $request->description;
          $item->save();

          return response()->json('Item updated', Response::HTTP_OK);
    }


    public function deleteItem (Request $request, $id)
    {
          if (item::where('id', $id)->where('user_id', $request->user()->id)
                                    ->whereNull('accepted_at')
                                    ->doesntExist()) {
              return response()->json('item not found', Response::HTTP_NOT_FOUND);
          }
          $item = Item::find($id);
          $item->delete();
          return response()->json('item deleted', Response::HTTP_OK);
    }
    //
}
