<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionOrder extends Model
{
    //兑换订单

    protected $table = 'conversion_order';

    protected $primaryKey = 'order_id';

    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class,'user_id','user_id');
    }

}
