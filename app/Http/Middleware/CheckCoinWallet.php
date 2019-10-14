<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/4
 * Time: 15:06
 */

namespace App\Http\Middleware;

use App\Models\CoinList;
use App\Models\UserWallet;
use App\Services\CoinService;
use App\Services\CoinServices\BitCoinService;
use App\Services\CoinServices\GethService;
use App\Traits\RedisTool;
use Closure;
use GuzzleHttp\Exception\BadResponseException;

class CheckCoinWallet
{
    use RedisTool;
//此中间件用于检测用户钱包账户地址是否存在,若不存在则创建之
    private $coinType;
    private $walletDetail;
    private $coinServer;

    public function __construct(CoinList $coinType,UserWallet $walletDetail,CoinService $coinServer)
    {
        $this->coinType = $coinType;
        $this->walletDetail = $walletDetail;
        $this->coinServer = $coinServer;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = currenctUser();
        $wallet = $this->walletDetail->getRecordById($request->input('wallet_id'),$user->user_id);
        if (!$wallet) return response()->json(['status_code'=>1004,'message'=>'参数错误']);
//        dd($wallet);


        if ($wallet['wallet_address'] == '' && strlen($wallet['wallet_address']) <= 0){
            $key = 'created:wallet:'.$user->user_id;
            if (! $this->setKeyLock($key,10)) return $next($request);
            switch ($wallet->coin->type){
                case 3:
                    $this->coinServer->createBlockAccount((new BitCoinService()),$wallet['wallet_id'],$wallet['coin_name']['coin_name'],$wallet['user_id']);
                    break;
                case 1:
                    $this->coinServer->createBlockAccount((new GethService()),$wallet,$wallet->coin,$wallet->user_id);
                    break;
                case 2:
                    $this->coinServer->createBlockAccount((new GethService()),$wallet,$wallet->coin,$wallet->user_id);
                    break;
            }
            $this->redisDelete($key);
        }

        //查询用户账户的实际余额,与数据库的余额进行同步
//        $this->coinServer


        return $next($request);

    }



}