<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/23
 * Time: 9:40
 */

namespace App\Services;


use App\Exceptions\ApiException;
use App\Models\InvestOrder;
use App\Models\InvestProduct;
use App\Models\UserGroup;
use App\Models\UserUpgradeLog;
use App\Models\UserWalletLog;
use App\Notifications\DirectUserUpgrade;
use Illuminate\Support\Facades\DB;

class UpgradeService
{
    public function upgrade($user,$group_id,$plc_price)
    {
        $user_group = $user['user_group'];

        if($group_id != ($user_group+1)) return apiResponse()->error(4001,'操作错误');
        $group = UserGroup::query()->findOrFail($group_id);

        DB::beginTransaction();
        try{

            $rich_type = 'usable_balance';
            $amount = $group['up_plc'] / $plc_price;
            $log_type = 4;
            $log_note = $group['group_name'];

            $user_upgrade_log = $user->user_upgrade_log()->create([
                'user_group' => $user_group,
                'target_user_group' => $group_id,
                'plc_price' => $plc_price,
                'amount' => $amount,
            ]);

            $user->update_wallet_and_log($rich_type,-$amount,$log_type,$log_note);

            $res = $user->update([
                'user_group' => $group_id,
            ]);

            $this->setUpgradeBouns($user,$group,$amount);

            DB::commit();

            //发送直推用户注册通知
            if($parent_user = $user->parent_user){
                $parent_user->notify(new DirectUserUpgrade($user,$group));
            }

            //零星升一星时 赠送体验版理财产品
            if($group_id == 2){
                $this->giveInvestProduct($user);
            }

            return apiResponse()->success();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    //结算上级邀请人升星奖金
    public function setUpgradeBouns($user,$group,$amount)
    {
        if(!$user['pid'])
        {
            return apiResponse()->error(0,'当前用户无邀请人');
        }

        $floor = 1;//层级初始值 排除0星的层级
        $real_floor = 1;//层级初始值 不排除0星的层级
        $max_floor = $group['group_id'] == 2 ? 15 : 9;//层级最大值
        $superior_award_flag = 0;
        $ultimate_award_flag = 0;
        $group_award_flag = 0;

        $superior_award = $amount * $group['superior_award'];//上级推荐奖
        $ultimate_award = $amount * $group['ultimate_award'];//九星推荐奖
        $group_award = $amount * $group['group_award'];//见点奖

        $inviter = $user;
        while ($inviter = $inviter->parent_user)
        {
            if($floor > $max_floor)
            {
                break;
            }

            if($inviter['user_group'] == 1)
            {
                //记录漏单
                if($superior_award > 0){
                    UserWalletLog::query()->create([
                        'user_id' => $inviter['user_id'],
                        'rich_type' => 'withdraw_balance',
                        'amount' => 0,
                        'log_type' => 3,
                        'log_note' => "{$real_floor}代会员{$user['username']}升{$group['group_name']}奖励(上级推荐奖)漏单，获得奖金0",
                    ]);
                }

                $real_floor++;
                continue;
            }else{
                if($superior_award > 0 && $superior_award_flag == 0 && $floor <= 9)
                {
                    if($inviter['user_group'] >= $group['group_id']){
                        $rich_type = 'withdraw_balance';
                        $log_type = 3;
                        $log_note = "{$real_floor}代会员{$user['username']}升{$group['group_name']}奖励(上级推荐奖)";
                        $inviter->update_wallet_and_log($rich_type,$superior_award,$log_type,$log_note);
                        $superior_award_flag += $superior_award;
                    }else{
                        UserWalletLog::query()->create([
                            'user_id' => $inviter['user_id'],
                            'rich_type' => 'withdraw_balance',
                            'amount' => 0,
                            'log_type' => 3,
                            'log_note' => "{$real_floor}代会员{$user['username']}升{$group['group_name']}奖励(上级推荐奖)漏单，获得奖金0",
                        ]);
                    }
                }

                if($ultimate_award > 0 && $inviter['user_group'] == 10 && $ultimate_award_flag == 0 && $floor <= 9)
                {
                    $rich_type = 'withdraw_balance';
                    $log_type = 3;
                    $log_note = "{$real_floor}代会员{$user['username']}升{$group['group_name']}奖励(九星推荐奖)";
                    $inviter->update_wallet_and_log($rich_type,$ultimate_award,$log_type,$log_note);
                    $ultimate_award_flag += $ultimate_award;
                }

                if($group_award > 0 && $inviter['user_group'] > 1)
                {
                    $rich_type = 'withdraw_balance';
                    $log_type = 3;
                    $log_note = "{$real_floor}代会员{$user['username']}升{$group['group_name']}奖励(见点奖)";
                    $inviter->update_wallet_and_log($rich_type,$group_award,$log_type,$log_note);
                    $group_award_flag += $group_award;
                }
            }

            $real_floor++;
            $floor++;
        }

        //漏单的转到系统账户
        if($superior_award_flag == 0){
            $leakage = $superior_award;//
            UserWalletLog::query()->create([
                'user_id' => 0,
                'rich_type' => 'withdraw_balance',
                'amount' => $leakage,
                'log_type' => 3,
                'log_note' => "用户{$user['user_id']}升{$group['group_name']}(漏单)",
            ]);
            DB::table('coin_setting')->where('coin_id',1)->increment('plc_system',$leakage);
        }

        //用户升星部分转账到系统账户并记录
        $system = $amount - $superior_award_flag - $ultimate_award_flag - $group_award_flag;//
        UserWalletLog::query()->create([
            'user_id' => 0,
            'rich_type' => 'withdraw_balance',
            'amount' => $system,
            'log_type' => 3,
            'log_note' => "用户{$user['user_id']}升{$group['group_name']}(系统回收)",
        ]);
        $res = DB::table('coin_setting')->where('coin_id',1)->increment('plc_system',$system);

        return $res;
    }

    //零星升一星时赠送体验版理财产品
    public function giveInvestProduct($user)
    {
        $invest_product = InvestProduct::query()->find(1);

        DB::beginTransaction();
        try {
            //创建理财订单
            $plc_price = getCoinCnyPrice();
            $order_plc = $invest_product['min_amount'] / $plc_price;
            $invest_order = $user->invest_orders()->create([
                'order_sn' => get_order_sn('invest'),
                'product_id' => $invest_product['id'],
                'product_income' => $invest_product['income'],
                'product_name' => $invest_product['name'],
                'order_money' => $invest_product['min_amount'],
                'plc_price' => $plc_price,
                'order_plc' => $order_plc,
                'return_max_day' => $invest_product['day'],
                'return_day_money' => $invest_product['min_amount'] * $invest_product['income'],
                'return_anticipated_money' => $invest_product['day'] * ($invest_product['min_amount'] * $invest_product['income']),
            ]);

            //用户资产 增加理财资产
            $user->update_wallet_and_log('lcz_money', $invest_product['min_amount'], 5, '系统赠送体验理财产品', $invest_order['order_id'],InvestOrder::class);

            DB::commit();

        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

}
