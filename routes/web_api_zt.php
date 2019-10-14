<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/30
 * Time: 17:09
 */

$api->version('v1', function ($api) {

    $api->group(['namespace' => 'App\Http\Controllers\Api\V1'], function ($api) {


        $api->get('test1','TestController@test');


    });
    $api->group(['namespace' => 'App\Http\Controllers\Api\V1','middleware'=>'auth.api'], function ($api) {


        $api->get('getRechargeMsg','UserWalletController@getRechargeMsg')->middleware('CheckCoinWallet');
//提币
        $api->post('withdraw','UserWalletController@withdraw');

    });
});
