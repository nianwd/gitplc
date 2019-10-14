<?php
/**
 * Created by PhpStorm.
 * User: 77507
 * Date: 2019/9/29
 * Time: 16:51
 */

namespace App\Http\Controllers\Api\V1;


use App\Services\CoinExchangeService;

class TestController extends ApiController
{


    public function test(CoinExchangeService $coinExchangeService)
    {
        dd($coinExchangeService->getCoinCnyPrice());




    }



}