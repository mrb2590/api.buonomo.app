<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        Passport::tokensExpireIn(now()->addHours(2));

        // Passport::refreshTokensExpireIn(now()->addMonths(6));

        // Loop through all permissions and create a gate for each one.
        // The gate checks if the role(s) a user has, has a particular permission
        if (Schema::hasTable('permissions')) {
            foreach ($this->getPermissions() as $permission) {
                Gate::define($permission->name, function ($user) use ($permission) {
                    return $user->hasRole($permission->roles);
                });
            }
        }
    }

    /**
     * Return all permissions
     *
     * @return array
     */
    protected function getPermissions()
    {
        return Permission::with('roles')->get();
    }
}
