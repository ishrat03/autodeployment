<?php

use Illuminate\Support\Facades\Route;
use Mohdishrat\Autodeployment\Http\Controllers\AutoDeploymentController;

Route::get('inspire', function(AutoDeploymentController $autoDeploymentController)
{
    return $autoDeploymentController->index();
});