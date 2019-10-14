<?php

namespace App\Providers;

use App\Models\ConversionOrder;
use App\Observers\ConversionOrderObserver;
use Encore\Admin\Config\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app('api.exception')->register(function (\Exception $exception) {
            $request = \Request::capture();
            return app('App\Exceptions\Handler')->render($request, $exception);
        });

        if (class_exists(Config::class)) {
            Config::load();
        }

        ConversionOrder::observe(ConversionOrderObserver::class);

    }
}
