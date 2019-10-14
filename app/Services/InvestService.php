<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/23
 * Time: 16:03
 */

namespace App\Services;


use App\Models\ConversionOrder;
use App\Models\InvestOrder;
use App\Models\InvestProduct;
use Illuminate\Support\Facades\DB;

class InvestService
{
    public function invest($user,$product_id,$plc_price,$invest_amount)
    {
        $invest_product = InvestProduct::query()->findOrFail($product_id);

        if($invest_product['can_buy'] == 0){
            return apiResponse()->zidingyiError('该产品不可购买');
        }

        if($invest_amount < $invest_product['min_amount']){
            return apiResponse()->zidingyiError('购买数量不可低于理财产品最小值'.$invest_product['min_amount']);
        }

        if($invest_amount > $invest_product['max_amount']){
            return apiResponse()->zidingyiError('购买数量不可高于理财产品最大值'.$invest_product['max_amount']);
        }

        DB::beginTransaction();
        try{

            //创建理财订单
            $order_plc = $invest_amount / $plc_price;
            $invest_order = $user->invest_orders()->create([
                'order_sn' => get_order_sn('invest'),
                'product_id' => $invest_product['id'],
                'product_income' => $invest_product['income'],
                'product_name' => $invest_product['name'],
                'order_money' => $invest_amount,
                'plc_price' => $plc_price,
                'order_plc' => $order_plc,
                'return_max_day' => $invest_product['day'],
                'return_day_money' => $invest_amount * $invest_product['income'],
                'return_anticipated_money' => $invest_product['day'] * ($invest_amount * $invest_product['income']),
            ]);

            //购买理财产品 减少PLC
            $rich_type = 'usable_balance';
            $amount = $order_plc;
            $log_type = 5;
            $log_note = '购买理财产品';
            $user->update_wallet_and_log($rich_type,-$amount,$log_type,$log_note,$invest_order['order_id'],InvestOrder::class);

            //用户资产 增加理财资产
            $user->update_wallet_and_log('lcz_money',$invest_amount,5,'购买理财产品',$invest_order['order_id'],InvestOrder::class);

            DB::commit();

            return apiResponse()->success();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    public function conversion($user,$plc_price,$conversion_amount)
    {
        DB::beginTransaction();
        try{

            $plc_num = $conversion_amount / $plc_price;

            //创建兑换订单
            $conversion_order = $user->conversion_orders()->create([
                'order_sn' => get_order_sn('conversion'),
                'order_money' => $conversion_amount,
                'plc_price' => $plc_price,
                'order_plc' => $plc_num,
            ]);

            //用户增加PLC可提资产
            $user->update_wallet_and_log('withdraw_balance',$plc_num,2,'兑换PLC',$conversion_order['order_id'],ConversionOrder::class);
            //用户减少理财资产
            $user->update_wallet_and_log('money',-$conversion_amount,10,'兑换PLC',$conversion_order['order_id'],ConversionOrder::class);

            DB::commit();

            return apiResponse()->success();
        }catch (\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

}
