<?php

use Illuminate\Support\Facades\Route;
use Mohdishrat\Autodeployment\Http\Controllers\AutoDeploymentController;
use Illuminate\Support\Facades\Hash;

$middleware = Mohdishrat\Autodeployment\Libraries\AutoDeploymentLib::setMiddleWare();

Route::middleware($middleware)->group(function()
{
    Route::controller(AutoDeploymentController::class)->group(function()
    {
        Route::get("deployments", "index");
        Route::get("startdeployment/{id}", 'startDeployment');
        Route::get("deploymentdata", 'deploymentData');
        Route::get("deploymentstatus/{id}", "deploymentStatus");
        Route::get("deploymentemail/{id}", "deploymentEmail");
        Route::get("hashpassword", function()
        {
            echo Hash::make("thisisrandomstring");
        });
    
        Route::get("testfunc", function()
        {
            return Mohdishrat\Autodeployment\Libraries\AutoDeploymentLib::fetchJsonOutput(18);
        });
    });
});

Route::controller(AutoDeploymentController::class)->group(function()
{
    Route::post('deploymentwebhook', 'deploymentWebhook');
});