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
    }
}