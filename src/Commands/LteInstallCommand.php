<?php

namespace LteAdmin\Commands;

use App\Admin\Delegates\CommonTrait;
use File;
use Illuminate\Console\Command;
use LteAdmin\ApplicationServiceProvider;
use LteAdmin\Core\ConfigExtensionProvider;
use LteAdmin\Core\JsonFormatter;
use LteAdmin\Core\NavigatorExtensionProvider;
use LteAdmin\Interfaces\ActionWorkExtensionInterface;
use LteAdmin\Models\LteSeeder;
use LteAdmin\Models\LteUser;
use Schema;
use Symfony\Component\Console\Input\InputOption;

class LteInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'lte:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install or update admin LTE';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('vendor:publish', [
            '--tag' => 'lte-migrations',
            '--force' => true,
        ]);

        $this->call('migrate', array_filter([
            '--force' => true,
        ]));

        if ($this->option('migrate')) {
            return 0;
        }

        $make_seeds = false;

        if (!Schema::hasTable('lte_users')) {
            $make_seeds = true;
        } elseif (!LteUser::count()) {
            $make_seeds = true;
        }

        if ($make_seeds) {
            $this->call('db:seed', [
                '--class' => LteSeeder::class,
            ]);
        }

        $base_dirs = ['/', '/Controllers', '/Delegates'];

        foreach ($base_dirs as $base_dir) {
            if (!is_dir($dir = lte_app_path($base_dir))) {
                mkdir($dir, 0777, true);

                $this->info("Directory {$dir} created!");
            }
        }

        $public_dirs = ['/uploads/images', 'uploads/files'];

        foreach ($public_dirs as $public_dir) {
            if (!is_dir($dir = public_path($public_dir))) {
                mkdir($dir, 0777, true);

                $this->info("Directory {$dir} created!");
            }
        }

        $this->makeApp();

        $extensions = storage_path('lte_extensions.php');

        if (!is_file($extensions)) {
            file_put_contents(
                $extensions,
                "<?php\n\nreturn [\n\t\n];"
            );

            $this->info("File {$extensions} created!");

            $base_composer = json_decode(file_get_contents(base_path('composer.json')), 1);

            if (
                !isset($base_composer['scripts']['post-autoload-dump'])
                || !in_array('@php artisan lar:dump', $base_composer['scripts']['post-autoload-dump'])
            ) {
                $base_composer['scripts']['post-autoload-dump'][] = '@php artisan lte:helpers';

                file_put_contents(
                    base_path('composer.json'),
                    JsonFormatter::format(json_encode($base_composer), false, true)
                );

                $this->info('File composer.json updated!');
            }

            $gitignore = file_get_contents(base_path('.gitignore'));

            $add_to_ignore = '';

            if (!str_contains($gitignore, 'public/lte-asset')) {
                $add_to_ignore .= "public/lte-asset\n";
                $this->info('Add folder [public/lte-asset] to .gitignore');
            }

            if (!str_contains($gitignore, 'public/lte-admin')) {
                $add_to_ignore .= "public/lte-admin\n";
                $this->info('Add folder [public/lte-admin] to .gitignore');
            }

            if (!str_contains($gitignore, 'public/ljs')) {
                $add_to_ignore .= "public/ljs\n";
                $this->info('Add folder [public/ljs] to .gitignore');
            }

            if ($add_to_ignore) {
                file_put_contents(base_path('.gitignore'), trim($gitignore)."\n".$add_to_ignore);
            }
        }

        $controller = lte_app_path('Controllers/Controller.php');

        if (!is_file($controller)) {
            file_put_contents(
                $controller,
                "<?php\n\nnamespace ".lte_app_namespace('Controllers').";\n\nuse LteAdmin\Controllers\Controller as LteController;\n\nclass Controller extends LteController\n{\n\t\n}"
            );

            $this->info("File {$controller} created!");
        }

        $delegates = lte_app_path('Delegates');

        $currentDelegates = File::allFiles(__DIR__.'/../Delegates');

        if (!trait_exists(CommonTrait::class)) {
            $file = lte_app_path('Delegates/CommonTrait.php');
            $pageClass = class_entity('CommonTrait')->traitObject();
            $pageClass->namespace(lte_app_namespace('Delegates'));
            $pageClass->doc(function ($doc) {
            });
            file_put_contents($file, $pageClass->wrap('php')->render());
            $this->info('Common delegate created!');
        }

        foreach ($currentDelegates as $currentDelegate) {
            $file = $delegates.'/'.$currentDelegate->getFilename();
            if (!is_file($file)) {
                $parentClass = class_in_file($currentDelegate->getPathname());
                $class = class_basename($parentClass);
                $delegateClass = class_entity($class);
                $delegateClass->namespace(lte_app_namespace('Delegates'));
                $delegateClass->use("$parentClass as Lte$class");
                $delegateClass->extend("Lte$class");
                $delegateClass->addTrait('CommonTrait');
                file_put_contents($file, $delegateClass->wrap('php')->render());
                $this->info("Delegate {$class} created!");
            }
        }

        $this->call('vendor:publish', [
            '--tag' => 'lte-lang',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'lte-assets',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'lte-adminlte-assets',
            '--force' => $this->option('force'),
        ]);

        if (!is_file(config_path('layout.php'))) {
            $this->call('vendor:publish', [
                '--tag' => 'lar-layout-config',
            ]);
        }

        if (!is_file(config_path('lte.php'))) {
            $this->call('vendor:publish', [
                '--tag' => 'lte-config',
            ]);
        }

        if ($make_seeds) {
            $this->call('lte:extension', ['--reinstall' => true, '--yes' => true, '--force' => true]);
        }

        $this->info('Lar Admin LTE Installed');

        return 0;
    }

    /**
     * Make app classes.
     */
    protected function makeApp()
    {
        $nav = lte_app_path('Navigator.php');

        if (!is_file($nav)) {
            $class = class_entity('Navigator');
            $class->namespace(lte_app_namespace());
            $class->wrap('php');
            $class->extend(NavigatorExtensionProvider::class);
            $class->implement(ActionWorkExtensionInterface::class);

            $class->method('handle')
                ->returnType('void')
                ->line('$this->makeMenu();')
                ->line()
                ->line('$this->makeDefaults();')
                ->line()
                ->line('$this->makeExtensions();');

            file_put_contents(
                $nav,
                $class->render()
            );

            $this->info("Navigator {$nav} created!");
        }

        $config = lte_app_path('Config.php');

        if (!is_file($config)) {
            $class = class_entity('Config');
            $class->namespace(lte_app_namespace());
            $class->wrap('php');
            $class->extend(ConfigExtensionProvider::class);
            $class->method('boot')
                ->line('parent::boot();')
                ->line()
                ->line('//');

            file_put_contents(
                $config,
                $class->render()
            );

            $this->info("Config {$config} created!");
        }

        $provider = app_path('Providers/AdminServiceProvider.php');

        if (!is_file($provider)) {
            $class = class_entity('AdminServiceProvider');
            $class->namespace('App\Providers');
            $class->wrap('php');
            $class->use(lte_app_namespace('Config'));
            $class->use(lte_app_namespace('Navigator'));
            $class->extend(ApplicationServiceProvider::class);

            $class->prop('protected:navigator', entity('Navigator::class'));
            $class->prop('protected:config', entity('Config::class'));

            file_put_contents(
                $provider,
                $class->render()
            );

            $this->info("Provider {$provider} created!");
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Publish the assets even if already exists'],
            ['migrate', 'm', InputOption::VALUE_NONE, 'Publish and run only migrations'],
            ['extension', 'e', InputOption::VALUE_OPTIONAL, 'Run install extension'],
        ];
    }
}
