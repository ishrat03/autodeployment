<?php

namespace Mohdishrat\Autodeployment\Providers;

use Illuminate\Support\ServiceProvider;
use Mohdishrat\Autodeployment\Console\UpdateDeploymentStatus;

class AutoDeploymentProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/autodeploymentconfig.php', 'autodeploymentconfig');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'autodeployment');

        if ($this->app->runningInConsole()) {
            $this->commands([
                UpdateDeploymentStatus::class,
            ]);
        }
    }
}