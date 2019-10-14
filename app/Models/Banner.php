<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //轮播图

    protected $table = 'banner';

    protected $primaryKey = 'banner_id';

    protected $guarded = [];

    public function getImgAttribute($value)
    {
        return env('IMG_URL') . $value;
    }

    public static function banner_position(){
        return [
            1 => '首页',
        ];
    }

}
