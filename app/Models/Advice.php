<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advice extends Model
{
    //用户意见反馈
    protected $primaryKey = 'id';

    /*表名称*/
    protected $table = 'advices';

    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class,'user_id','user_id');
    }
}
