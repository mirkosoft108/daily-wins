<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use LogicException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') && DB::connection()->getDriverName() !== 'pgsql') {
            throw new LogicException('Production requires PostgreSQL. Set DB_CONNECTION=pgsql and a PostgreSQL DB_URL.');
        }
    }
}
