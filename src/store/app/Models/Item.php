<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $table = 'items';
    protected $fillable = ['name', 'point', 'user_id', 'description'];

    public const STATUS_SALE = 1;
    public const STATUS_SOLDOUT = 2;

    public static function status_name($status_code)
    {
        $status_code = $status_code ?: self::STATUS_SALE;
        $status_names = [
            self::STATUS_SALE => 'sale',
            self::STATUS_SOLDOUT => 'sold out',
        ];
        return $status_names[$status_code];
    }

}
