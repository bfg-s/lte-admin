<?php

namespace Lar\LteAdmin\Middlewares;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Lar\Layout\Core\LConfigs;
use Lar\Layout\Respond;
use Lar\Layout\Tags\TABLE;
use Lar\LteAdmin\Core\TableMacros;
use Lar\LteAdmin\Models\LtePermission;

/**
 * Class Authenticate
 *
 * @package Lar\LteAdmin\Middlewares
 */
class Authenticate
{
    /**
     * @var Collection
     */
    protected static $menu;

    /**
     * @var bool
     */
    static $access = true;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('lte')->guest() && $this->shouldPassThrough($request)) {

            session()->flash("respond", Respond::glob()->toJson());

            return redirect()->route('lte.dashboard');
        }
        
        if (Auth::guard('lte')->guest() && !$this->shouldPassThrough($request)) {

            session()->flash("respond", Respond::glob()->toJson());

            return redirect()->route('lte.login');
        }

        TABLE::addMacroClass(TableMacros::class);

        if (is_file(lte_app_path('bootstrap.php'))) {

            include lte_app_path('bootstrap.php');
        }

        include __DIR__ . '/../bootstrap.php';

        LConfigs::add('uploader', route('lte.uploader'));

        if (!$this->access()) {

            if ($request->ajax() && !$request->pjax()) {

                return response()->json(["0:toast::error" => [__('lte::admin.access_denied'), __('lte::admin.error')]]);
            }

            else if (!$request->isMethod('get')) {

                session()->flash("respond", respond()->toast_error([__('lte::admin.access_denied'), __('lte::admin.error')])->toJson());

                return back();
            }

            static::$access = false;
        }

        return $next($request);
    }

    /**
     * @return bool
     */
    protected function access()
    {
        $now = lte_now();

        if (isset($now['roles']) && !lte_user()->hasRoles($now['roles'])) {

            return false;
        }

        return LtePermission::check();
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        $excepts = [
            lte_uri('login'),
            lte_uri('logout'),
        ];

        foreach ($excepts as $except) {

            if ($except !== '/') {

                $except = trim($except, '/');
            }

            if ($request->is($except)) {

                return true;
            }
        }

        return false;
    }
}
