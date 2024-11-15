<?php
namespace Mohdishrat\Autodeployment\Libraries;
use Illuminate\Support\Facades\Log;
use Mohdishrat\Autodeployment\Jobs\AutoDeploymentJob;
use Mohdishrat\Autodeployment\Libraries\Constants;
use Mohdishrat\Autodeployment\Mail\DeploymentMail;
use \Throwable as Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
                    AutoDeploymentLib::createCustomLog("Starting Deployent Process for branch {$result["destination"]["branch"]["name"]}");
                    AutoDeploymentJob::dispatch($insertId, "pr_merged", "")->onQueue("cicd");
                }
            }
        }
        catch(Exception $e)
        {
            AutoDeploymentLib::createCustomLog("AutoDeploymentLib->handleMerged catch error", [$e->getMessage(), $e->getLine(), $e->getFile()], "error");
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

                    AutoDeploymentLib::createCustomLog("Starting Sonar Scan Process for branch {$result['source']['branch']['name']}");
                    AutoDeploymentJob::dispatch($insertId, "sonar_scan", $result['source']['branch']['name'])->onQueue("cicd");
                }
            }
        }
        catch(Exception $e)
        {
            AutoDeploymentLib::createCustomLog("AutoDeploymentLib->handleOpenState catch error", [$e->getMessage(), $e->getLine(), $e->getFile()], "error");
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
                    AutoDeploymentLib::createCustomLog("No Need to deployment for this webhook");
            }

            return true;
        }
        catch(Exception $e)
        {
            AutoDeploymentLib::createCustomLog("AutoDeploymentLib->handleDeployment catch error", [$e->getMessage(), $e->getLine(), $e->getFile()], "error");
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
            AutoDeploymentLib::createCustomLog("AutoDeploymentLib->convertDataToApiData catch error", [$e->getMessage(), $e->getLine(), $e->getFile()], "error");
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
            AutoDeploymentLib::createCustomLog("AutoDeploymentLib->convertDataToApiData catch error", [$e->getMessage(), $e->getLine(), $e->getFile()], "error");
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

    public static function setMiddleWare()
    {
        $middleware = [];
        if((env("APP_ENV") == 'local' && config('autodeploymentconfig.dev_auth_required')) || (env("APP_ENV") == 'production' && config('autodeploymentconfig.prod_auth_required')))
        {
            $middleware = ["web", "auth"];
        }

        return $middleware;
    }

    public static function sendDeploymentEmail($data)
    {
        $steps = [
            "deployment_id",
            "git_pull",
            "remove_composer_lock",
            "composer_install",
            "migration",
            "optimize_clear",
            "restart_queue",
            "log_permission",
            "session_permission"
        ];

        $result = $data["result"];

        $finalResult = [];

        foreach ($steps as $step)
        {
            if(isset($result[$step]))
            {
                $finalResult[$step] = $result[$step];
            }
        }

        unset($result);

        $data["result"] = $finalResult;
        unset($finalResult);
        $data["subject"] = $data["deploymentStatus"] == "success" ? "Deployment Completed Successfully on ".$data["env_pointing"] : "Deployment Failed on ".$data["env_pointing"];

        // return View('autodeployment::deploymentmail', $data);
        Mail::to(env("MAIL_TO"))
            ->send(new DeploymentMail($data));
    }

    public static function createCustomLog($string, $msg = "", $type = "success")
    {
        $name = "auto_deployment/auto_deployment_".date(Constants::CURRENTDATE).".log";
        $channel = Log::build([
            'driver' => 'single',
            'path' => storage_path("logs/auto_deployment/{$name}"),
            'permission' => 0666,
            "days" => 4
        ]);

        if($type == "success")
        {
            if(is_array($msg))
            {
                Log::stack([$channel])->info($string, $msg);
            }
            elseif($msg == "")
            {
                Log::stack([$channel])->info($string);
            }
            else
            {
                Log::stack([$channel])->info($string, [$msg]);
            }
        }
        elseif ($type == "error")
        {
            if(is_array($msg))
            {
                Log::stack([$channel])->error($string, $msg);
            }
            elseif($msg == "")
            {
                Log::stack([$channel])->error($string);
            }
            else
            {
                Log::stack([$channel])->error($string, [$msg]);
            }
        }

        return $channel;
    }
}