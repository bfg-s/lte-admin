<?php

namespace Lar\LteAdmin;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as ServiceProviderIlluminate;
use Lar\Developer\Commands\DumpAutoload;
use Lar\Layout\Layout;
use Lar\LJS\JaxController;
use Lar\LJS\JaxExecutor;
use Lar\LteAdmin\Commands\LteControllerCommand;
use Lar\LteAdmin\Commands\LteDbDumpCommand;
use Lar\LteAdmin\Commands\LteExtensionCommand;
use Lar\LteAdmin\Commands\LteInstallCommand;
use Lar\LteAdmin\Commands\LteJaxCommand;
use Lar\LteAdmin\Commands\LteModalCommand;
use Lar\LteAdmin\Commands\LteUserCommand;
use Lar\LteAdmin\Core\BladeDirectiveAlpineStore;
use Lar\LteAdmin\Core\Generators\ExtensionNavigatorHelperGenerator;
use Lar\LteAdmin\Core\Generators\FunctionsHelperGenerator;
use Lar\LteAdmin\Core\Generators\MacroableHelperGenerator;
use Lar\LteAdmin\Exceptions\Handler;
use Lar\LteAdmin\Middlewares\Authenticate;

class ServiceProvider extends ServiceProviderIlluminate
{
    /**
     * @var array
     */
    protected $commands = [
        LteInstallCommand::class,
        LteControllerCommand::class,
        LteUserCommand::class,
        LteExtensionCommand::class,
        LteModalCommand::class,
        LteJaxCommand::class,
        LteDbDumpCommand::class,
    ];

    /**
     * Simple bind in app service provider.
     * @var array
     */
    protected $bind = [

    ];

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'lte-auth' => Authenticate::class,
    ];

    /**
     * @var ApplicationServiceProvider
     */
    protected $app_provider;

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws \Exception
     */
    public function boot()
    {
        /**
         * Register AdminLte Events.
         */
        foreach ($this->listen as $event => $listeners) {
            foreach (array_unique($listeners) as $listener) {
                Event::listen($event, $listener);
            }
        }

        /**
         * Register app routes.
         */
        if (is_file(lte_app_path('routes.php'))) {
            \Road::domain(config('lte.route.domain', ''))
                ->web()
                ->middleware(['lte-auth'])
                ->lang(config('layout.lang_mode', true))
                ->gets('lte')
                ->layout(config('lte.route.layout'))
                ->namespace(lte_app_namespace('Controllers'))
                ->prefix(config('lte.route.prefix'))
                ->name(config('lte.route.name'))
                ->group(lte_app_path('routes.php'));
        }

        /**
         * Register web routes.
         */
        if (is_file(base_path('routes/admin.php'))) {
            \Road::domain(config('lte.route.domain', ''))
                ->web()
                ->middleware(['lte-auth'])
                ->lang(config('layout.lang_mode', true))
                ->gets('lte')
                ->layout(config('lte.route.layout'))
                ->namespace(lte_app_namespace('Controllers'))
                ->prefix(config('lte.route.prefix'))
                ->name(config('lte.route.name'))
                ->group(base_path('routes/admin.php'));
        }

        /**
         * Register Lte Admin basic routes.
         */
        \Road::domain(config('lte.route.domain', ''))
            ->web()
            ->lang(config('layout.lang_mode', true))
            ->gets('lte')
            ->middleware(['lte-auth'])
            ->prefix(config('lte.route.prefix'))
            ->name(config('lte.route.name'))
            ->group(__DIR__.'/routes.php');

        /**
         * Register publishers configs.
         */
        $this->publishes([
            __DIR__.'/../config/lte.php' => config_path('lte.php'),
        ], 'lte-config');

        /**
         * Register publishers lang.
         */
        $this->publishes([
            __DIR__.'/../translations' => resource_path('lang'),
        ], 'lte-lang');

        /**
         * Register publishers assets.
         */
        $this->publishes([
            base_path('/vendor/almasaeed2010/adminlte/dist') => public_path('/lte-asset'),
            base_path('/vendor/almasaeed2010/adminlte/plugins') => public_path('/lte-asset/plugins'),
            __DIR__.'/../assets' => public_path('/lte-admin'),
        ], ['lte-assets', 'laravel-assets']);

        /**
         * Register publishers adminlte assets.
         */
        $this->publishes([
            base_path('/vendor/almasaeed2010/adminlte/dist') => public_path('/lte-asset'),
            base_path('/vendor/almasaeed2010/adminlte/plugins') => public_path('/lte-asset/plugins'),
        ], ['lte-adminlte-assets', 'laravel-assets']);

        /**
         * Register publishers migrations.
         */
        $this->publishes([
            __DIR__.'/../migrations' => database_path('migrations'),
        ], ['lte-migrations', 'laravel-assets']);

        /**
         * Register publishers html examples.
         */
        $this->publishes([
            base_path('/vendor/almasaeed2010/adminlte/pages') => public_path('/lte-html'),
        ], 'lte-html');

        /**
         * Load AdminLte views.
         */
        $this->loadViewsFrom(__DIR__.'/../views', 'lte');

        if ($this->app->runningInConsole()) {
            /**
             * Register lte admin getter for console.
             */
            \Get::create('lte');

            /**
             * Run lte boots.
             */
            LteBoot::run();

            /**
             * Helper registration.
             */
            DumpAutoload::addToExecute(FunctionsHelperGenerator::class);
            DumpAutoload::addToExecute(ExtensionNavigatorHelperGenerator::class);
            DumpAutoload::addToExecute(MacroableHelperGenerator::class);
        }

        /**
         * Make lte view variables.
         */
        $this->viewVariables();

        /**
         * Register getters.
         */
        \Get::register(\Lar\LteAdmin\Getters\Menu::class);
        \Get::register(\Lar\LteAdmin\Getters\Role::class);
        \Get::register(\Lar\LteAdmin\Getters\Functions::class);

        /**
         * Simple bind in service container.
         */
        foreach ($this->bind as $key => $item) {
            if (is_numeric($key)) {
                $key = $item;
            }
            $this->app->bind($key, $item);
        }

        /**
         * Run lte with jax on admin page.
         */
        JaxController::on_start(static function () {
            $ref = request()->server->get('HTTP_REFERER');
            if ($ref && \Str::is(url(config('lte.route.prefix').'*'), $ref)) {
                LteBoot::run();
            }
        });

        /**
         * Register Jax namespace.
         */
        \LJS::jaxNamespace(lte_relative_path('Jax'), lte_app_namespace('Jax'));

        /**
         * Register AlpineJs Blade directive.
         */
        \Blade::directive('alpineStore', [BladeDirectiveAlpineStore::class, 'directive']);
    }

    /**
     * Register services.
     *
     * @return void
     * @throws \Exception
     */
    public function register()
    {
        $this->app->singleton(Page::class, function ($app) {
            return class_exists(\App\LteAdmin\Page::class)
                ? new \App\LteAdmin\Page($app->router)
                : new Page($app->router);
        });

        /**
         * App register provider.
         */
        if (class_exists('App\Providers\LteServiceProvider')) {
            $this->app->register('App\Providers\LteServiceProvider');
        }

        /**
         * Override errors.
         */
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            Handler::class
        );

        /**
         * Merge config from having by default.
         */
        $this->mergeConfigFrom(
            __DIR__.'/../config/lte.php', 'lte'
        );

        /**
         * Register Lte middleware.
         */
        $this->registerRouteMiddleware();

        /**
         * Register Lte commands.
         */
        $this->commands($this->commands);

        /**
         * Setup auth and disc configuration.
         */
        $this->loadAuthAndDiscConfig();

        /**
         * Register Lte layout.
         */
        Layout::registerComponent('lte_layout', \Lar\LteAdmin\Layouts\LteLayout::class);

        /**
         * Register Lte Login layout.
         */
        Layout::registerComponent('lte_auth_layout', \Lar\LteAdmin\Layouts\LteAuthLayout::class);

        /**
         * Register lte jax executors.
         */
        $this->registerJax();
    }

    /**
     * Register jax executors.
     */
    protected function registerJax()
    {
        JaxExecutor::addNamespace(__DIR__.'/Jax', 'Lar\\LteAdmin\\Jax');
    }

    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }
    }

    /**
     * Setup auth and disc configuration.
     *
     * @return void
     */
    private function loadAuthAndDiscConfig()
    {
        config(\Arr::dot(config('lte.auth', []), 'auth.'));
        config(\Arr::dot(config('lte.disks', []), 'filesystems.disks.'));
    }

    /**
     * Make lte view variables.
     */
    private function viewVariables()
    {
        app('view')->share([
            'lte' => config('lte'),
            'default_page' => config('lte.paths.view', 'admin').'.page',
        ]);
    }
}
