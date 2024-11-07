<?php
namespace Mohdishrat\Autodeployment\Libraries;
use Illuminate\Support\Facades\Log;
use Mohdishrat\Autodeployment\Jobs\AutoDeploymentJob;
use Mohdishrat\Autodeployment\Libraries\Constants;
use \Throwable as Exception;
use Illuminate\Support\Facades\Hash;

class AutoDeploymentLib
{
    public static function handleMerged($result, $pointing, $insertId, $startDeployment)
    {
        try
        {
            $prodBranch = config('autodeploymentconfig.prod_default_branch');
            $stageBranch = config('autodeploymentconfig.dev_default_branch');
            $destinationBranch = $result["destination"]["branch"]["name"];
            if(in_array($destinationBranch, [$prodBranch, $stageBranch]))
            {
                if(($pointing == Constants::PRODPOINTING && $destinationBranch == $prodBranch) || ($pointing == Constants::DEVPOINTING && $destinationBranch == $stageBranch) && $startDeployment)
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

    public static function handleDeployment($result, $insertId, $startDeployment)
    {
        try
        {
            $pointing = env("APP_ENV");
            switch($result["state"])
            {
                case "MERGED":
                    self::handleMerged($result, $pointing, $insertId, $startDeployment);
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

    public static function fetchJsonOutput($processId, $unlinkFile)
    {
        $fileName = public_path("deployment/deployment_log_{$processId}.json");

        if(!file_exists($fileName))
        {
            return [
                "code" => 201,
                "msg" => "File Not Exists"
            ];
        }

        $records = file_get_contents($fileName);
        $records = json_decode($records, true);

        if(!is_array($records))
        {
            return [
                "code" => 201,
                "msg" => "Invalid Json File"
            ];
        }

        $result = [];
        foreach ($records as $key => $value)
        {
            $result[array_key_first($value)] = reset($value);
        }

        if(isset($result["composer_install"]) && is_array($result["composer_install"]))
        {
            if((isset($result["composer_install"]["skipped"]) && $result["composer_install"]["skipped"] == false) || isset($result["composer_install"]["failed"]))
            {
                $replacePattern = "/\\x1B\\[[0-9;]*[a-zA-Z]/";
                $result["composer_install"]["stdout"] = preg_replace($replacePattern, "", $result["composer_install"]["stdout"]);
            }
        }

        if($unlinkFile)
        {
            unlink($fileName);
        }
        return $result;
    }

    public static function checkForStartDeployment()
    {
        $pointing = env("APP_ENV");
        if($pointing == Constants::DEVPOINTING)
        {
            return config('autodeploymentconfig.dev_auto_deploy');
        }
        elseif ($pointing == Constants::PRODPOINTING)
        {
            return config('autodeploymentconfig.prod_auto_deploy');
        }

        return false;
    }
}