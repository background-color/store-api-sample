<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ItemResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'id' => $this->id,
            //'item_id' => $this->item_id,
            'item' => new ItemResource($this->whenLoaded('item')),
            //'seller_id' => $this->seller_id,
            'seller' => new UserResource($this->whenLoaded('seller')),
            //'buyer_id' => $this->buyer_id,
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'accepted_at' => $this->accepted_at,
        ];
    }
}
