<?php

use Illuminate\Support\Facades\Route;
use Mohdishrat\Autodeployment\Http\Controllers\AutoDeploymentController;

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
    });
});

Route::middleware(["api"])->controller(AutoDeploymentController::class)->group(function()
{
    Route::post('deploymentwebhook', 'deploymentWebhook');
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);