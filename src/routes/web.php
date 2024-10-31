<?php

use Illuminate\Support\Facades\Route;
use Mohdishrat\Autodeployment\Http\Controllers\AutoDeploymentController;
use Illuminate\Support\Facades\Hash;

Route::controller(AutoDeploymentController::class)->group(function()
{
    Route::get("deployments", "index");
    Route::post('cicdwebhook', 'cicdWebhook');
    Route::get("startdeployment/{id}", 'startDeployment');
    Route::get("deploymentdata", 'deploymentData');
    Route::get("hashpassword", function()
    {
        echo Hash::make("thisisrandomstring");
    });
});