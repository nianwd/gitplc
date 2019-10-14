<?php

//use App\Admin\Controllers\ArticleCategoryController;
//use App\Admin\Controllers\ArticleController;
//use App\Admin\Controllers\UserController;
use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    $router->resource('users', UserController::class);
    $router->get('user/{user_id}/viewTeam','UserController@viewTeam')->name('user.viewTeam');
    $router->resource('user-groups', UserGroupController::class);
    $router->resource('user-wallets', UserWalletController::class);
    $router->resource('user-wallet-logs', UserWalletLogController::class);

    $router->resource('articles', ArticleController::class);
    $router->resource('article-categories', ArticleCategoryController::class);

    $router->resource('banners', BannerController::class);

    $router->resource('invest-products', InvestProductController::class);
    $router->resource('invest-orders', InvestOrderController::class);

    $router->resource('advices', AdviceController::class);


});
