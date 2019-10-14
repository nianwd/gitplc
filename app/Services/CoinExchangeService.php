<?php
/**
 * Created by PhpStorm.
 * User: 77507
 * Date: 2019/9/29
 * Time: 15:51
 */

namespace App\Services;


use App\Traits\RedisTool;

class CoinExchangeService
{

    use
    RedisTool;

    //获取币种相对于cny的价格
    public function getCoinCnyPrice($coinName = 'PLC')
    {

        try{
            $api = 'api/getCoinCnyPrice/' . $coinName;
            $rkey = $coinName . '_to_cny_price';
            $rv = $this->stringGet($rkey);
            if ($rv !== null) return $rv;
            $re = json_decode(file_get_contents(env('ICIC_IP') . $api),true);
            if ($re['status_code'] != 200) return false;

            $this->stringSetex($rkey,30,$re['data']['price']);
            return $re['data']['price'];
        }catch (\Exception $exception){
            return false;
        }


    }




}