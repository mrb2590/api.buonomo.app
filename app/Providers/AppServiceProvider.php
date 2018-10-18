<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Database debugging
        if (config('database.debug') == 'true' && !App::environment('production')) {
            DB::listen(function ($query) {
                $q = "Query: (".$query->time." ms)\r\n";
                $q .= $query->sql."\r\n";
                $q .= implode(', ', $query->bindings)."\r\n\r\n";
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
        //
    }
}
