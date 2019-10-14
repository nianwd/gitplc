<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OverflowLog extends Model
{
    //团队溢出记录

    protected $table = 'overflow_log';

    protected $primaryKey = 'log_id';

    protected $guarded = [];
}
