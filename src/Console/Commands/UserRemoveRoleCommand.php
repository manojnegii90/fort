<?php

declare(strict_types=1);

namespace Rinvex\Fort\Console\Commands;

use Rinvex\Fort\Models\Role;
use Rinvex\Fort\Models\User;
use Illuminate\Console\Command;

class UserRemoveRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fort:user:removerole
                            {user? : The user identifier}
                            {role? : The role identifier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a role from a user.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $userField = $this->argument('user') ?: $this->ask(trans('rinvex.fort::artisan.user.identifier'));

        if ((int) $userField) {
            $user = User::find($userField);
        } elseif (filter_var($userField, FILTER_VALIDATE_EMAIL)) {
            $user = User::where(['email' => $userField])->first();
        } else {
            $user = User::where(['username' => $userField])->first();
        }

        if (! $user) {
            return $this->error(trans('rinvex.fort::artisan.user.invalid', ['field' => $userField]));
        }

        $roleField = $this->argument('role') ?: $this->anticipate(trans('rinvex.fort::artisan.user.role'), Role::all()->pluck('slug', 'id')->toArray());

        if ((int) $roleField) {
            $role = Role::find($roleField);
        } else {
            $role = Role::where(['slug' => $roleField])->first();
        }

        if (! $role) {
            return $this->error(trans('rinvex.fort::artisan.role.invalid', ['field' => $roleField]));
        }

        // Remove role to user
        $user->removeRole($role);

        $this->info(trans('rinvex.fort::artisan.user.roleremoved', ['user' => $user->id, 'role' => $role->id]));
    }
}
