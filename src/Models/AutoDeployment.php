<?php
namespace Mohdishrat\Autodeployment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mohdishrat\Autodeployment\Libraries\AutoDeploymentLib;
use \Throwable as Exception;

class AutoDeployment extends Model
{
    public static function getAllDeployments()
    {
        try
        {
            return self::select(
                    "id",
                    DB::raw('SEC_TO_TIME(TIMESTAMPDIFF(SECOND ,deployment_start_time, deployment_end_time)) as timediff'),
                    "name",
                    "status",
                    "webhook_time",
                    "deployment_start_time",
                    "deployment_end_time",
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(webhook_payload , '$.pullrequest.source.branch.name')) AS source_branch_name,
	                JSON_UNQUOTE(JSON_EXTRACT(webhook_payload , '$.pullrequest.destination.branch.name')) AS destination_branch_name")
                )
                ->where("name", "!=", "code push")
                ->orderBy("id", 'DESC')
                ->limit(100)
                ->get()
                ->toArray();
        }
        catch(Exception $e)
        {
            AutoDeploymentLib::createCustomLog("AutoDeployment->getAllDeployments catch error", [$e->getMessage(), $e->getLine(), $e->getFile()], "error");
            return [];
        }
    }

    public static function fetchDeploymentById($id)
    {
        try
        {
            return self::select("webhook_payload")->where("id", $id)->first();
        }
        catch(Exception $e)
        {
            AutoDeploymentLib::createCustomLog("AutoDeployment->fetchDeploymentById catch error", [$e->getMessage(), $e->getLine(), $e->getFile()], "error");
            return null;
        }
    }
}