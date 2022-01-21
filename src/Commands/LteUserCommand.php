<?php

namespace Lar\LteAdmin\Commands;

use Illuminate\Console\Command;
use Lar\LteAdmin\Models\LteRole;
use Lar\LteAdmin\Models\LteUser as User;

class LteUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lte:user {email?: Email of user} {name?: Login of user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make admin user';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var User $user_model */
        $user_model = config('lte.auth.providers.lte.model');

        $email = $this->argument('email');

        $name = $this->argument('name');

        $password = null;

        $password2 = null;

        if (! $email) {
            $email = $this->ask('Enter a admin E-Mail');

            if ($user_model::where('email', $email)->first()) {
                $this->error("Admin with email [$email] is isset!");

                return $this->call('lte:make-user', array_filter([
                    'email' => $email,
                    'name' => $name ? $name : false,
                ]));
            }
        }

        if (! $name) {
            $name = $this->ask('Enter a admin Login', explode('@', $email)[0]);

            if ($user_model::where('username', $name)->first()) {
                $this->error("Admin with name [$name] is isset!");

                return $this->call('lte:make-user', [
                    'email' => $email,
                    'name' => $name,
                ]);
            }
        }

        if (! $password) {
            $password = $this->ask('Enter a password');
        }

        if (! $password2) {
            $password2 = $this->ask('Enter a confirmation password');
        }

        if ($password !== $password2) {
            $this->error('Admin passwords not match!');

            return $this->call('lte:make-user', [
                'email' => $email,
                'name' => $name,
            ]);
        }

        if ($user = $user_model::create([
            'username' => $name,
            'password' => bcrypt($password),
            'email' => $email,
            'name' => $name,
        ])) {
            $roles = LteRole::all();
            $role = $this->choice('Select role for new lte user', $roles->pluck('name', 'id')->toArray(), $roles->first()->id);
            $user->roles()->sync([$role]);

            $this->info('User success created.');
        } else {
            $this->error('Error on user create!');
        }
    }
}
