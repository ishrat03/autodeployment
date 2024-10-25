<?php

use Illuminate\Support\Facades\Route;
use Mohdishrat\Autodeployment\Http\Controllers\AutoDeploymentController;

Route::get('deployments', function(AutoDeploymentController $autoDeploymentController)
{
    return $autoDeploymentController->index();
});