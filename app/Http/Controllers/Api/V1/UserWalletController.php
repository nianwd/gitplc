<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CoinTradeOrder;
use App\Models\UserWallet;
use App\Models\UserWalletLog;
use App\Traits\Tools;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserWalletController extends ApiController
{
    use Tools;
    //

    public function getWalletInfo()
    {
        $user = $this->current_user();

        $data = $user->user_wallet;

        return $this->successWithData($data);
    }

    //获取充值信息
    public function getRechargeMsg(Request $request,UserWallet $userWallet)
    {
        if ($vr = $this->verifyField($request->all(),[
            'wallet_id' => 'required|integer',
        ])) return $vr;

        $wallet = $userWallet->getWallet($request->wallet_id);
        if ($wallet->user_id != currenctUser()->user_id) return apiResponse()->zidingyiError('查询失败');

        return apiResponse()->successWithData(['record' => $wallet]);

    }

    //提币
    public function withdraw(Request $request,UserWallet $userWallet,CoinTradeOrder $coinTradeOrder)
    {
        if ($vr = $this->verifyField($request->all(),[
            'wallet_id' => 'required|integer',
            'to_address' => 'required|string',
            'amount' => 'required|numeric'
        ])) return $vr;
        $wallet = $userWallet->getWallet($request->wallet_id);
        if ($wallet->user_id != currenctUser()->user_id) return apiResponse()->zidingyiError('查询失败');
        if (! $this->isETHAddress($request->to_address) || $request->to_address == $wallet->wallet_address) return apiResponse()->zidingyiError('地址不合法');
        if (preg_match('/^[0-9]+[.][0-9]{1,10}$/', $request->amount)) return apiResponse()->zidingyiError('限制整数');

        $fee = $wallet->coin->withdraw_fee;
        if ($request->amount <= $fee) return apiResponse()->zidingyiError('数量过低');

        if ($wallet->withdraw_balance < $request->amount) return apiResponse()->zidingyiError('余额不足');

        DB::beginTransaction();
        if (
            $wallet->dec_withdraw_balance($request->amount)
            && $wallet->add_freeze_balance($request->amount)
            && $coinTradeOrder->insertOne(3,$wallet->user_id,$wallet->id,1,(string)$wallet->wallet_address,$request->to_address,$request->amount,$fee,$wallet->coin_id)//需要增加转出地址检测的功能
            && (new UserWalletLog())->insertOne($wallet->user_id,'withdraw_balance',-1*$request->amount,6)
        ){
            DB::commit();
            return apiResponse()->success();

        }

        DB::rollBack();return apiResponse()->error();

    }


    //获取PLC钱包收入流水类型
    public function walletIncomeTypes(Request $request)
    {
        $data = [
            ['type' => 1,'type_name'=>'充币'],
            ['type' => 2,'type_name'=>'兑换'],
            ['type' => 3,'type_name'=>'奖金'],
        ];

        return $this->successWithData($data);
    }

    //获取PLC钱包支出流水类型
    public function walletExpendTypes(Request $request)
    {
        $data = [
            ['type' => 4,'type_name'=>'升星'],
            ['type' => 5,'type_name'=>'理财'],
            ['type' => 6,'type_name'=>'提币'],
        ];

        return $this->successWithData($data);
    }

    //获取理财收益流水类型
    public function walletInvestTypes(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'start_time' => 'date',
            'end_time' => 'date',
        ])) return $vr;

        $user = $this->current_user();

        $builder7 = $user->user_wallet_log()->where(['log_type'=>7,'rich_type'=>'money']);
        $builder8 = $user->user_wallet_log()->where(['log_type'=>8,'rich_type'=>'money']);
        $builder9 = $user->user_wallet_log()->where(['log_type'=>9,'rich_type'=>'money']);
        $builder10 = $user->user_wallet_log()->where(['log_type'=>10,'rich_type'=>'money']);

        if(
            ($start_time = $request->input('start_time'))
            && ($end_time = $request->input('end_time'))
        ){
            if($start_time == $end_time){
                $builder7->whereDate('created_at',$start_time);
                $builder8->whereDate('created_at',$start_time);
                $builder9->whereDate('created_at',$start_time);
                $builder10->whereDate('created_at',$start_time);
            }else{
                $builder7->whereDate('created_at', '>',$start_time)->whereDate('created_at', '<=',$end_time);
                $builder8->whereDate('created_at', '>',$start_time)->whereDate('created_at', '<=',$end_time);
                $builder9->whereDate('created_at', '>',$start_time)->whereDate('created_at', '<=',$end_time);
                $builder10->whereDate('created_at', '>',$start_time)->whereDate('created_at', '<=',$end_time);
            }
        }

        $data = [
            ['type' => 7,'type_name' => '理财收益','amount_count' => $builder7->sum('amount')],
            ['type' => 8,'type_name' => '团队收益','amount_count' => $builder8->sum('amount')],
            ['type' => 9,'type_name' => '团队溢出','amount_count' => $builder9->sum('amount')],
            ['type' => 10,'type_name' => '收益兑换','amount_count' => $builder10->sum('amount')],
        ];

        return $this->successWithData($data);
    }

    //获取PLC钱包流水
    public function getWalletLogs(Request $request)
    {
        if ($vr = $this->verifyField($request->all(),[
            'log_type' => 'required|integer',
            'start_time' => 'date',
            'end_time' => 'date',
        ])) return $vr;

        $user = $this->current_user();

        $builder = $user->user_wallet_log()->with('logable')->where('log_type',$request->log_type);

        if($start_time = $request->input('start_time') && $end_time = $request->input('end_time')){
            $builder->whereBetween('created_at', [$start_time,$end_time]);
        }

        $data = $builder->latest()->paginate();

        return $this->successWithData($data);
    }

}
