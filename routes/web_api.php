<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/30
 * Time: 17:09
 */

$api->version('v1', function ($api) {

    require_once "web_api_yx.php";
    require_once "web_api_zt.php";

    $api->group(['namespace' => 'App\Http\Controllers\Api\V1'], function ($api) {

        $api->get('test','UserController@test');


    });
    $api->group(['namespace' => 'App\Http\Controllers\Api\V1','middleware'=>'auth.api'], function ($api) {



    });
});
