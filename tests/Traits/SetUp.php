<?php

namespace LteAdmin\Tests\Traits;

use Illuminate\Support\Facades\Config;
use Laravel\Dusk\DuskServiceProvider;

trait SetUp
{
    protected function setUpAdmin(): void
    {
        Config::set('lte.app_namespace', 'LteAdmin\\Tests\\Admin');
        Config::set('lte.paths.app', __DIR__.'/../Admin');
        Config::set('layout.lang_mode', false);
        $this->artisan('cache:clear');
        $this->app->register(DuskServiceProvider::class);
//        Config::set('database.default', 'mysql');
        //Config::set('database.connections.mysql.database', \config('database.connections.mysql.database', 'lte') . '_test');
//        dd(\config('database.connections.mysql.database', 'lte'));

        //$this->app['config']->set('database.default', 'mysql');
        //$this->app['config']->set('database.connections.mysql.host', env('MYSQL_HOST', 'localhost'));
        //$this->app['config']->set('database.connections.mysql.database', \config('database.connections.mysql.database', 'lte') . '_test');
        //$this->app['config']->set('database.connections.mysql.username', env('MYSQL_USER', 'root'));
        //$this->app['config']->set('database.connections.mysql.password', env('MYSQL_PASSWORD', ''));
        //$this->app['config']->set('app.key', 'qFFqAqsweckfdSdECddsXssIvnK5adsd3r28GVIqwaFRmF');
    }
}
