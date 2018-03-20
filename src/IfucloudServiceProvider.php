<?php
/**
 * Created by IntelliJ IDEA.
 * User: zfm
 * Date: 2018/3/20
 * Time: 上午11:04
 */

namespace Ifucloud\Module;

use Illuminate\Support\ServiceProvider;

class IfucloudServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'ifucloud-migrations');
        }
    }
}
