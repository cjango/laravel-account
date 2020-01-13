<?php

namespace Jason\Account;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{

    /**
     * 部署
     * @Author:<C.Jason>
     * @Date:2019-07-30T08:59:16+0800
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/config.php' => config_path('account.php')]);
        }
    }

    /**
     * 注册功能
     * @Author:<C.Jason>
     * @Date:2019-07-30T08:59:28+0800
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'account');
    }

}