<?php

namespace Mohdishrat\Autodeployment\Providers;

use Illuminate\Support\ServiceProvider;

class AutoDeploymentProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
        $this->mergeConfigFrom(__DIR__.'/../config/autodeploymentconfig.php', 'autodeploymentconfig');
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'autodeployemnt');
    }
}