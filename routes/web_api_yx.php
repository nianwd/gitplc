<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/30
 * Time: 17:09
 */

$api->version('v1', function ($api) {

    $api->group(['namespace' => 'App\Http\Controllers\Api\V1'], function ($api) {

        $api->post('mockLogin','UserController@mockLogin');

        $api->get('getBanners','IndexController@getBanners');
        $api->get('upgradePlan','IndexController@upgradePlan');

        $api->get('getPlcPrice','CommonController@getPlcPrice');

        //文章
        $api->get('article/faq','ArticleController@faq');
        $api->get('article/about_us','ArticleController@about_us');
        $api->get('article/information','ArticleController@information');
        $api->get('article/notice','ArticleController@notice');
        $api->get('article/newest_notice','ArticleController@newest_notice');
        $api->get('article/detail','ArticleController@detail');

        $api->get('sendSmsCode','UserController@sendSmsCode');
        $api->get('sendRegisterCode','UserController@sendRegisterCode');
        $api->post('register','UserController@register');
        $api->post('login','UserController@login');
        $api->post('forget_password','UserController@forget_password');

    });
    $api->group(['namespace' => 'App\Http\Controllers\Api\V1','middleware'=>'auth.api'], function ($api) {

        //用户升星
        $api->get('getUpgradeData','IndexController@getUpgradeData');
        $api->post('upgrade','IndexController@upgrade')->middleware('CheckPaypwd');

        //用户消息通知
        $api->get('user/myNotifiablesCount','UserController@myNotifiablesCount');
        $api->get('user/myNotifiables','UserController@myNotifiables');
        $api->get('user/readNotifiable','UserController@readNotifiable');

        //用户意见反馈
        $api->get('user/advices','UserController@advices');
        $api->get('user/advice_detail','UserController@advice_detail');
        $api->post('user/add_advice','UserController@add_advice');

        $api->post('logout','UserController@logout');

        //个人中心
        $api->get('home/getUserInfo','HomeController@getUserInfo');
        $api->post('home/updateUserInfo','HomeController@updateUserInfo');
        $api->get('home/myTeamInfo','HomeController@myTeamInfo');
        $api->get('home/getTeamUser','HomeController@getTeamUser');
        $api->get('home/getIncomeStatistics','HomeController@getIncomeStatistics');
        $api->get('home/invitation','HomeController@invitation');

        $api->get('home/teamOrder','HomeController@teamOrder');

        $api->post('update_password','UserController@update_password');
        $api->post('set_paypwd','UserController@set_paypwd');
        $api->post('update_paypwd','UserController@update_paypwd');

        //钱包流水
        $api->get('wallet/getWalletInfo','UserWalletController@getWalletInfo');
        $api->get('wallet/incomeTypes','UserWalletController@walletIncomeTypes');
        $api->get('wallet/expendTypes','UserWalletController@walletExpendTypes');
        $api->get('wallet/investTypes','UserWalletController@walletInvestTypes');
        $api->get('wallet/getWalletLogs','UserWalletController@getWalletLogs');

        //节点理财
        $api->get('invest/products','InvestController@index');
        $api->get('invest/getInvestData','InvestController@getInvestData');
        $api->post('invest/buy','InvestController@invest')->middleware('CheckPaypwd');
        $api->get('invest/getInvestOrders','InvestController@getInvestOrders');
        $api->get('invest/getInvestStatistics','InvestController@getInvestStatistics');
        //兑换PLC
        $api->get('invest/getConversionData','InvestController@getConversionData');
        $api->post('invest/conversion','InvestController@conversion');

    });
});
