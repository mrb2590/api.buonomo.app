<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Ramsey\Uuid\Uuid;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Auto generate uuid when creating new oauth client
        Client::creating(function (Client $client) {
            $client->incrementing = false;
            $client->id = Uuid::uuid4()->toString();
        });

        // Database debugging
        if (config('database.debug') == 'true' && !App::environment('production')) {
            DB::listen(function ($query) {
                $q = "Query: (" . $query->time . " ms)\r\n";
                $q .= $query->sql . "\r\n";
                $q .= implode(', ', $query->bindings) . "\r\n\r\n";
                $q .= "-----------------------\r\n\r\n";
                echo $q;
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();
    }
}
