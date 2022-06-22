<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $fillable = ['item_id', 'seller_id', 'buyer_id', 'accepted_at'];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }


    public static function find_relation($user_id, $order_id = null): \Illuminate\Support\Collection
    {
        return Order::with([
            'item:id,name,point',
            'seller:id,name',
            'buyer:id,name'
        ])->where(function($query) use($user_id) {
            $query->orWhere('seller_id', '=', $user_id)
                ->orWhere('buyer_id', '=', $user_id);
        })->get(['id','item_id','seller_id','buyer_id','accepted_at']);
    }
}
