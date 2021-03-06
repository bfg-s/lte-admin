<?php

namespace LteAdmin\Commands;

use App;
use Artisan;
use Exception;
use Illuminate\Console\Command;
use Log;
use LteAdmin\Commands\Generators\ExtensionNavigatorHelperGenerator;
use LteAdmin\Commands\Generators\GenerateBladeHelpers;
use LteAdmin\Commands\Generators\GenerateHelper;
use LteAdmin\Commands\Generators\GenerateNewJaxHelper;
use LteAdmin\Commands\Generators\GenerateRespondHelper;
use LteAdmin\Commands\Generators\GetterHelper;
use LteAdmin\Commands\Generators\MacroableHelperGenerator;
use LteAdmin\Interfaces\LteHelpGeneratorInterface;
use Throwable;

class LteHelpersCommand extends Command
{
    /**
     * Default executor list.
     *
     * @var array
     */
    protected static $executors = [
        GenerateHelper::class,
        GenerateRespondHelper::class,
        GenerateBladeHelpers::class,
        GenerateNewJaxHelper::class,
        ExtensionNavigatorHelperGenerator::class,
        MacroableHelperGenerator::class,
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lte:helpers {--class= : Execute this class}
                                        {--i|inner : Generate only inner handles, with out helpers}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generator helper from ide';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add class in to handle execute.
     *
     * @param  string  $class
     */
    public static function addToExecute(string $class)
    {
        static::$executors[] = $class;
    }

    /**
     * Add object in to handle execute.
     *
     * @param  object|string  $obj
     * @param  string  $method
     */
    public static function addObjToExecute($obj, string $method)
    {
        static::$executors[] = [$obj, $method];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Throwable
     */
    public function handle()
    {
        if ($class = $this->option('class')) {
            if (class_exists($class)) {
                $obj = new $class($this);

                if ($obj instanceof LteHelpGeneratorInterface) {
                    $this->info("> {$class}::handle");

                    try {
                        $out = null;

                        if (method_exists($obj, 'valid')) {
                            if ($obj->valid()) {
                                $out = $obj->handle($this);
                            }
                        } else {
                            $out = $obj->handle($this);
                        }

                        if ($out) {
                            dump($out);
                        }
                    } catch (Exception $exception) {
                        Log::error($exception);
                        $this->error("Error: [{$exception->getCode()}:{$exception->getMessage()}]");
                        $this->error(" > File: [{$exception->getFile()}:{$exception->getLine()}]");
                    }
                }
            }

            return;
        }

        if (!App::isLocal()) {
            return;
        }

        if (!$this->option('inner')) {
            Artisan::call('ide-helper:eloquent');
            $this->info('> artisan ide-helper:eloquent');

            Artisan::call('ide-helper:generate');
            $this->info('> artisan ide-helper:generate');

            Artisan::call('ide-helper:models --write');
            $this->info('> artisan ide-helper:models');

            Artisan::call('ide-helper:meta');
            $this->info('> artisan ide-helper:meta');
        }

        $file = base_path('_ide_helper_lar.php');
        $file_data = '';

        foreach (static::$executors as $executor) {
            if (is_string($executor)) {
                $obj = new $executor($this);

                if ($obj instanceof LteHelpGeneratorInterface) {
                    $this->info("> {$executor}::handle");

                    try {
                        $add = null;

                        if (method_exists($obj, 'valid')) {
                            if ($obj->valid()) {
                                $add = $obj->handle($this);
                            }
                        } else {
                            $add = $obj->handle($this);
                        }

                        if ($add) {
                            $file_data .= $add."\n\n";
                        }
                    } catch (Exception $exception) {
                        Log::error($exception);
                        $this->error("Error: [{$exception->getCode()}:{$exception->getMessage()}]");
                        $this->error(" > File: [{$exception->getFile()}:{$exception->getLine()}]");
                    }
                }
            } elseif (is_array($executor)) {
                $this->info("> {$executor[0]}::{$executor[1]}");

                embedded_call($executor, [static::class => $this]);
            }
        }

        if ($file_data) {
            file_put_contents($file, "<?php \n\n".$file_data);
            $this->info('> Helper [_ide_helper_lar.php] generated!');
        }
    }
}
