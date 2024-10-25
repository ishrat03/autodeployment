<?php
namespace Mohdishrat\Autodeployment\Http\Controllers;

use App\Http\Controllers\Controller;

class AutoDeploymentController extends Controller
{
    public function index()
    {
        return view('autodeployemnt::index');
    }
}