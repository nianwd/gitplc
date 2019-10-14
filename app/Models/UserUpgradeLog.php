<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserUpgradeLog extends Model
{
    //用户升星日志

    protected $primaryKey = 'id';

    /*表名称*/
    protected $table = 'user_upgrade_log';

    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class,'user_id','user_id');
    }

}
