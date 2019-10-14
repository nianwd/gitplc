<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWalletLog extends Model
{
    //用户资产流水

    protected $primaryKey = 'id';

    /*表名称*/
    protected $table = 'user_wallet_log';

    protected $guarded = [];

    protected $appends = ['log_type_name'];

    public function user(){
        return $this->belongsTo(User::class,'user_id','user_id');
    }

    public function logable()
    {
        return $this->morphTo();
    }

    public function getLogTypeNameAttribute(){
        return self::log_types()[$this->log_type];
    }

    public static function log_types(){
        //PLC收入流水类型：1充币，2兑换，3奖金
        //PLC支出流水类型：4升星，5理财，6提币
        //理财收入流水类型：7理财收益，8团队收益，
        //理财支出流水类型：9团队溢出,10收益兑换
        return [
            1 => '充币',
            2 => '兑换',
            3 => '奖金',
            4 => '升星',
            5 => '理财',
            6 => '提币',
            7 => '理财收益',
            8 => '团队收益',
            9 => '团队溢出',
            10 => '收益兑换',
        ];
    }


    public function insertOne($u_id,$rich_type,$amount,$log_type,$log_note = '')
    {
        $this->user_id = $u_id;
        $this->rich_type = $rich_type;
        $this->amount = $amount;
        $this->log_type = $log_type;
        $this->log_note = $log_note;
        return $this->save();



    }

}
