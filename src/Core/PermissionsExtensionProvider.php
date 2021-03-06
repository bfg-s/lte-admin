<?php

namespace LteAdmin\Core;

use Exception;
use Illuminate\Console\Command;
use LteAdmin\ExtendProvider;
use LteAdmin\Models\LteRole;

class PermissionsExtensionProvider
{
    /**
     * @var Command
     */
    public $command;
    /**
     * @var ExtendProvider
     */
    public $provider;

    /**
     * InstallExtensionProvider constructor.
     * @param  Command  $command
     * @param  ExtendProvider  $provider
     */
    public function __construct(Command $command, ExtendProvider $provider)
    {
        $this->command = $command;
        $this->provider = $provider;
    }

    /**
     * Make all extension permissions.
     */
    public function up()
    {
        if (method_exists($this, 'roles')) {
            $roles = $this->roles();
            if (is_array($roles)) {
                ModelSaver::doMany(LteRole::class, $roles);
                if (count($roles)) {
                    $this->command->info('Created '.count($roles).' roles.');
                }
            }
        }
    }

    /**
     * Drop all extension permissions.
     * @throws Exception
     */
    public function down()
    {
        if (method_exists($this, 'roles')) {
            $roles = $this->roles();
            if (is_array($roles)) {
                $roles_count = 0;
                foreach ($roles as $role) {
                    if (LteRole::where('slug', $role['slug'])->delete()) {
                        $roles_count++;
                    }
                }
                if ($roles_count) {
                    $this->command->info('Deleted '.$roles_count.' roles.');
                }
            }
        }
    }
}
