<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\InvestProduct;
use App\Services\InvestService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class InvestController extends ApiController
{
    //节点理财

    //理财产品列表
    public function index()
    {
        $builder = InvestProduct::query()->where('status',1)->where('can_buy',1);

        $data = $builder->orderBy('order','desc')->paginate();

        return $this->successWithData($data);
    }

    //获取实时PLC币价并缓存
    public function getInvestData(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'product_id' => 'required|integer',
        ])) return $vr;

        $user = $this->current_user();

        if($user['user_group'] <= 1){
            return $this->error(1055,'零星会员不可购买理财产品');
        }

        $plc_price = getCoinCnyPrice();//获取PLC当前币价和人民币的汇率
        $key = 'invest-plc_price:'.$user['user_id'];
        Cache::put($key, $plc_price,config('app.plc_price_ttl'));//缓存针对当前用户的PLC价格

        $data['plc_price'] = $plc_price;

        return $this->successWithData($data);
    }

    //购买理财产品
    public function invest(Request $request,InvestService $investService)
    {
        if ($vr = $this->verifyField($request->all(),[
            'product_id' => 'required|integer',
            'paypwd' => 'required',
            'invest_amount' => 'required|numeric',//投资数量
        ])) return $vr;

        $user = $this->current_user();

        if($user['user_group'] <= 1){
            return $this->error(1055,'零星会员不可购买理财产品');
        }

        $product_id = $request->product_id;
        $invest_amount = $request->invest_amount;

        $key = 'invest-plc_price:'.$user['user_id'];
        if (!Cache::has($key)){
            return $this->error(4001,'超时，请重新购买');
        }

        $plc_price = Cache::get($key);

        //检测支付密码是否正确
        $check_res = $user->verifyPassword($request->paypwd,$user->paypwd);
        if(!$check_res) {
            return $this->error(4001,'密码错误');
        }

        return $investService->invest($user,$product_id,$plc_price,$invest_amount);
    }

    //获取理财统计
    public function getInvestStatistics(Request $request)
    {
        $user = $this->current_user();

        $data['invest_count'] = $user->invest_orders->sum('order_money');//总投资
        $data['has_earnings'] = $user->invest_orders->sum('return_count_money') +
            $user->invest_orders()->whereHas('invest_product',function (Builder $query){
                $query->where('is_return',1);
            })->where('status',2)->sum('order_money');//已收益 = 所有订单收益+已到期订单的本金
        $data['but_count'] = $user->invest_orders->sum('return_anticipated_money') +
            $user->invest_orders()->whereHas('invest_product',function (Builder $query){
                $query->where('is_return',1);
            })->sum('order_money');//可收益
        $data['has_conversion'] = abs($user->user_wallet_log()->where(['log_type'=>10,'rich_type'=>'money'])->sum('amount'));//已兑换
        $data['invest_earnings'] = abs($user->user_wallet_log()->where(['log_type'=>7,'rich_type'=>'money'])->whereDate('created_at',date('Y-m-d'))->sum('amount'));//昨日理财收益
        $data['team_earnings'] = abs($user->user_wallet_log()->where(['log_type'=>8,'rich_type'=>'money'])->whereDate('created_at',date('Y-m-d'))->sum('amount'));//昨日团队收益
        $data['team_overflow'] = abs($user->user_wallet_log()->where(['log_type'=>9,'rich_type'=>'money'])->whereDate('created_at',date('Y-m-d'))->sum('amount'));//昨日团队溢出

        return $this->successWithData($data);
    }

    //理财订单
    public function getInvestOrders(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'status' => 'integer|in:1,2',
        ])) return $vr;

        $user = $this->current_user();

        $status = $request->input('status',1);
        $builder = $user->invest_orders()->with('invest_product')->where('status',$status);

        $data = $builder->latest()->paginate();

        return $this->successWithData($data);
    }

    //获取实时PLC币价并缓存--兑换PLC
    public function getConversionData(Request $request)
    {
        $user = $this->current_user();

        $plc_price = getCoinCnyPrice();//获取PLC当前币价和人民币的汇率
        $key = 'conversion-plc_price:'.$user['user_id'];
        Cache::put($key, $plc_price,config('app.plc_price_ttl'));//缓存针对当前用户的PLC价格

        $data['plc_price'] = $plc_price;

        return $this->successWithData($data);
    }

    //兑换PLC
    public function conversion(Request $request,InvestService $investService)
    {
        if ($vr = $this->verifyField($request->all(),[
            'paypwd' => 'required',
            'conversion_amount' => 'required|numeric',//兑换数量
        ])) return $vr;

        $user = $this->current_user();
        $conversion_amount = $request->conversion_amount;

        $key = 'conversion-plc_price:'.$user['user_id'];
        if (!Cache::has($key)){
            return $this->error(4001,'超时，请重新兑换');
        }

        $plc_price = Cache::get($key);

        //检测支付密码是否正确
        $check_res = $user->verifyPassword($request->paypwd,$user->paypwd);
        if(!$check_res) {
            return $this->error(4001,'密码错误');
        }

        return $investService->conversion($user,$plc_price,$conversion_amount);
    }

}
