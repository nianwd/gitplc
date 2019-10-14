<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvestOrder extends Model
{
    //理财订单

    protected $table = 'invest_order';

    protected $primaryKey = 'order_id';

    protected $guarded = [];

    protected $attributes = [
        'status' => 1,
        'return_count_money' => 0,
        'return_count_day' => 0,
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id','user_id');
    }

    public function invest_product(){
        return $this->belongsTo(InvestProduct::class,'product_id','id');
    }

    public function setInvestOrder()
    {
        if(!$this->canSetInvestOrder()){
            return;
        }

        DB::beginTransaction();
        try {

            $is_set = 0;
            if($this->return_count_day+1 >= $this->return_max_day)
            {
                $is_set = 1;
            }

            //更新用户资产
            $user = $this->user;
            $user->update_wallet_and_log('money',$this->return_day_money,7,'理财收益',$this->order_id,InvestOrder::class);

            //更新订单
            $this->return_count_day = $this->return_count_day + 1;
            $this->return_count_money = $this->return_count_money + $this->return_day_money;
            $this->set_last_time = Carbon::now()->toDateTimeString();
            if($is_set == 1) //订单结算完成
            {
                $this->status = 2;
                $this->is_set = 1;
                $this->set_time = Carbon::now()->toDateTimeString();

                //退还本金
                $invest_product = InvestProduct::query()->findOrFail($this->product_id);
                $user->update_wallet_and_log('lcz_money',-$this->order_money,7,'理财(订单结算完成，扣除理财中)',$this->order_id,InvestOrder::class);
                if($invest_product['is_return'] == 1){
                    $user->update_wallet_and_log('money',$this->order_money,7,'理财(订单结算完成，退还本金)',$this->order_id,InvestOrder::class);
                }
            }
            $this->save();
//dd($this);
            //结算团队收益
            $this->setInvestBouns($user,$this);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    //结算团队理财收益
    public function setInvestBouns($user,$invest_order)
    {
        if(!$user['pid'])
        {
            return apiResponse()->error(0,'当前用户无邀请人');
        }

        $floor = 1;//层级初始值
        $max_floor = 10;//层级最大值
        $inviter = $user;
        while ($inviter = $inviter->parent_user)
        {
            if($floor > $max_floor)
            {
                break;
            }

            if($inviter['user_group'] == 1)
            {
                $floor++;
                continue;
            }

            $group = UserGroup::query()->find($inviter['user_group']);

            $rich_type = 'money';
            $amount = $invest_order['order_money'] * $group['team_invest_award'];
            $log_type = 8;
            $log_note = "{$floor}代会员{$user['username']}理财获得团队收益";
            $inviter->update_wallet_and_log($rich_type,$amount,$log_type,$log_note,$invest_order['order_id'],InvestOrder::class);

            $floor++;
        }
    }

    /**
     * 是否可以结算理财订单
     *
     * @return bool
     */
    public function canSetInvestOrder()
    {
        if ($this->status != 1) {
            return false;
        }

        if($this->set_last_time == date('Y-m-d')){
            return false;
        }

        return true;
    }

}
