<?php

namespace LteAdmin\Jax;

use Illuminate\Support\Facades\App;
use Lar\LJS\JaxController;
use Lar\LJS\JaxExecutor;
use Request;
use Route;

class LteAdminExecutor extends JaxExecutor
{
    public function __construct()
    {
        JaxController::on_mounted(static function ($executor, $method, $params, $executor_class_name) {
            lte_log_warning('Call executing command', "{$executor_class_name}@{$method}", 'fas fa-exchange-alt');
        });
    }

    /**
     * Public method access.
     *
     * @return bool
     */
    public function access()
    {
        return !\LteAdmin::guest();
    }

    public function refererEmit()
    {
        $refUrl = str_replace(
            '/'.App::getLocale(), '/en',
            Request::server('HTTP_REFERER')
        );

        Route::dispatch(
            Request::create(
                $refUrl
            )
        )->getContent();
    }
}
