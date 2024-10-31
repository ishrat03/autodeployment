<?php
namespace Mohdishrat\Autodeployment\Libraries;
use Illuminate\Support\Facades\Log;
use Mohdishrat\Autodeployment\Jobs\AutoDeploymentJob;
use Mohdishrat\Autodeployment\Libraries\Constants;
use \Throwable as Exception;
use Illuminate\Support\Facades\Hash;

class AutoDeploymentLib
{
    public static function handleMerged($result, $pointing, $insertId)
    {
        try
        {
            $prodBranch = config('autodeploymentconfig.prod_default_branch');
            $stageBranch = config('autodeploymentconfig.dev_default_branch');
            $destinationBranch = $result["destination"]["branch"]["name"];
            if(in_array($destinationBranch, [$prodBranch, $stageBranch]))
            {
                if(($pointing == Constants::PRODPOINTING && $destinationBranch == $prodBranch) || ($pointing == Constants::DEVPOINTING && $destinationBranch == $stageBranch))
                {
                    info("@Deployment Starting Deployent Process for branch {$result["destination"]["branch"]["name"]}");
                    AutoDeploymentJob::dispatch($insertId, "pr_merged", "")->onQueue("cicd");
                }
            }
        }
        catch(Exception $e)
        {
            Log::error("@Deployment AutoDeploymentLib->handleMerged catch error", [$e->getMessage(), $e->getLine(), $e->getFile()]);
            return [];
        }
    }

    public static function handleOpenState($result, $pointing, $insertId)
    {
        try
        {
            $prodBranch = config('autodeploymentconfig.prod_sonarqube_branch');
            $stageBranch = config('autodeploymentconfig.dev_sonarqube_branch');
            $destinationBranch = $result["destination"]["branch"]["name"];

            if(in_array($destinationBranch, [$prodBranch, $stageBranch]))
            {
                if(($pointing == Constants::PRODPOINTING && $destinationBranch == $prodBranch) || ($pointing == Constants::DEVPOINTING && $destinationBranch == $stageBranch))
                {

                    Log::info("@Deployment Starting Sonar Scan Process for branch {$result['source']['branch']['name']}");
                    AutoDeploymentJob::dispatch($insertId, "sonar_scan", $result['source']['branch']['name'])->onQueue("cicd");
                }
            }
        }
        catch(Exception $e)
        {
            Log::error("@Deployment AutoDeploymentLib->handleOpenState catch error", [$e->getMessage(), $e->getLine(), $e->getFile()]);
            return [];
        }
    }

    public static function handleDeployment($result, $insertId)
    {
        try
        {
            $pointing = env("APP_ENV");
            switch($result["state"])
            {
                case "MERGED":
                    self::handleMerged($result, $pointing, $insertId);
                    break;
                case "OPEN":
                    self::handleOpenState($result, $pointing, $insertId);
                    break;
                default:
                    Log::info("@Deployment No Need to deployment for this webhook");
            }

            return true;
        }
        catch(Exception $e)
        {
            Log::error("@Deployment AutoDeploymentLib->handleDeployment catch error", [$e->getMessage(), $e->getLine(), $e->getFile()]);
            return false;
        }
    }

    public static function convertDataToApiData($data)
    {
        try
        {
            foreach ($data as $key => $value)
            {
                $value["name"] = ucwords(str_replace("_", " ", $value["name"]));
                $value["webhook_time"] = date("d M Y H:i A", strtotime($value["webhook_time"]));
                $value["deployment_start_time"] = $value["status"] == 'pending' || $value['deployment_start_time'] == null ? '-' : date("d M Y H:i A", strtotime($value["deployment_start_time"]));
                $value["deployment_end_time"] = $value["status"] == 'pending' || $value['deployment_end_time'] == null ? '-' : date("d M Y H:i A", strtotime($value["deployment_end_time"]));
                $value["status"] = ucwords($value["status"]);
                $data[$key] = $value;
            }

            return $data;
        }
        catch(Exception $e)
        {
            Log::error("@Deployment AutoDeploymentLib->convertDataToApiData catch error", [$e->getMessage(), $e->getLine(), $e->getFile()]);
            return [];
        }
    }

    public static function verifyDeploymentPassword($password)
    {
        try
        {
            $hashedPassword = env("PROD_DEPLOYMENT_PASSWORD");
            if(env("APP_ENV") == "local")
            {
                $hashedPassword = env("DEV_DEPLOYMENT_PASSWORD");
            }

            return Hash::check($password, $hashedPassword);
        }
        catch(Exception $e)
        {
            Log::error("@Deployment AutoDeploymentLib->convertDataToApiData catch error", [$e->getMessage(), $e->getLine(), $e->getFile()]);
            return false;
        }
    }
}