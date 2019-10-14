<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWithdrawRecord extends Model
{
    //用户提现记录

    protected $primaryKey = 'id';

    /*表名称*/
    protected $table = 'user_withdraw_record';

    protected $guarded = [];
}
