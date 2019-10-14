<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    //用户资产

    protected $primaryKey = 'id';

    /*表名称*/
    protected $table = 'user_wallet';

    protected $guarded = [];

//    protected $attributes = [
//
//    ];


    public function getWallet($walletId)
    {
        return $this->with(['coin'])->where(['id'=>$walletId])->first();
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','user_id');
    }

    public function coin()
    {
        return $this->belongsTo(CoinList::class,'coin_id','coin_id');
    }

    public static function rich_types(){
        return [
            'usable_balance' => '可用PLC',
            'withdraw_balance' => '可提PLC',
            'money' => '理财收益',
            'lcz_money' => '理财中',
        ];
    }


    //获取已存在的eth地址
    public function getExistsEthAddress($userId)
    {
        $coinIds = CoinList::query()->whereIn('type',[1,2])->pluck('coin_id')->toArray();

        $re = $this->where(['user_id'=>$userId])->whereIn('coin_id',$coinIds)->whereNotNull('wallet_address')->where('wallet_address','!=','')->first();

        if ($re) return $re;return false;

    }


    public function getRecordById($wid,$uid)
    {
        return $this->where(['id'=>$wid,'user_id'=>$uid])->first();
    }


    //减少可提余额
    public function dec_withdraw_balance($amount)
    {
        return $this->where(['id'=>$this->id])->decrement('withdraw_balance',$amount);
    }

//增加冻结余额
    public function add_freeze_balance($amount)
    {
        return $this->where(['id'=>$this->id])->increment('freeze_balance',$amount);
    }



}
