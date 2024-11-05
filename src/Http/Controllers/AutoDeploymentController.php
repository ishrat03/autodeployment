<?php
namespace Mohdishrat\Autodeployment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Mohdishrat\Autodeployment\Libraries\AutoDeploymentLib;
use Mohdishrat\Autodeployment\Models\AutoDeployment;
use \Throwable as Exception;
use Mohdishrat\Autodeployment\Libraries\Constants;
use Illuminate\Support\Facades\Session;

class AutoDeploymentController extends Controller
{
    public function index()
    {
        return view('autodeployment::index', [
            "data" => [],
            'headers' => [
                "Deployment ID",
                "Deployment Name",
                "Status",
                "Webhook Time",
                "Source Branch",
                "Destination Branch",
                "Deployment Start Time",
                "Deployment End Time",
                "Time Taken",
                "Action"
            ]
        ]);
    }

    public function cicdWebhook(Request $request)
    {
        try
        {
            $result = $request->all();

            if(isset($result["pullrequest"]))
            {
                $obj = new AutoDeployment();

                $obj->webhook_payload = json_encode($result);
                $obj->created_at = date(Constants::CURRENTDATETIME);
                $obj->updated_at = date(Constants::CURRENTDATETIME);
                $obj->webhook_time = date(Constants::CURRENTDATETIME);
                $obj->name = "code push";
                $obj->status = "pending";
                $obj->process_output = "";
                $obj->save();
                $insertId = $obj->id;
                unset($obj);
                $result = $result["pullrequest"];
                AutoDeploymentLib::handleDeployment($result, $insertId);
            }
            else
            {
                Log::info("@Deployment No Deployment Needed for this webhook");
            }

            return response(
                [
                    "header" => [
                        "status" => "success",
                        "code" => 200,
                        "msg" => "Webhook Recieved Successfully"
                    ]
                ]
            );
        }
        catch(Exception $e)
        {
            Log::error("@Deployment AutoDeploymentController->cicdWebhook catch error", [$e->getMessage(), $e->getLine(), $e->getFile()]);
            return response(
                [
                    "header" => [
                        "status" => "error",
                        "code" => 201,
                        "msg" => "Facing some techinal error",
                    ]
                ]
            );
        }
    }

    public function startDeployment(Request $request, $id)
    {
        try
        {
            if(!AutoDeploymentLib::verifyDeploymentPassword(base64_decode($request->get("password"))))
            {
                return response(
                    [
                        "header" => [
                            "code" => 401,
                            "status" => "error",
                            "msg" => "Invalid deployment password"
                        ]
                    ]
                );
            }

            $data = AutoDeployment::fetchDeploymentById($id);

            if($data == null)
            {
                return response(
                    [
                        "header" => [
                            "code" => 201,
                            "status" => "error",
                            "msg" => "Invalid deployment id provided"
                        ]
                    ]
                );
            }

            AutoDeployment::where('id', $id)
                ->update(
                    [
                        "status" => "pending",
                        "deployment_start_time" => null,
                        "deployment_end_time" => null,
                    ]
                );
            $data = json_decode($data->webhook_payload, true);
            $data = $data["pullrequest"];

            AutoDeploymentLib::handleDeployment($data, $id);
            
            return response(
                [
                    "header" => [
                        "code" => 200,
                        "status" => "success",
                        "msg" => "Deployment Started Now"
                    ]
                ]
            );
        }
        catch(Exception $e)
        {
            Log::error("@Deployment AutoDeploymentController->retryDeployment catch error", [$e->getMessage(), $e->getLine(), $e->getFile()]);
            return redirect()->back()->with("error", "Facing some Techinal Carrier");
        }
    }

    public function deploymentData()
    {
        $data = AutoDeployment::getAllDeployments();
        $data = AutoDeploymentLib::convertDataToApiData($data);
        return response(
            [
                "header" => [
                    "code" => 200,
                    "status" => "success",
                    "msg" => "OK"
                ],
                "body" => $data
            ]
        );
    }

    public function deploymentStatus($id)
    {
        $status = AutoDeployment::select("status", "json_output")
            ->where("id", $id)
            ->first();

        if($status == null)
        {
            return response(
                [
                    "header" => [
                        "code" => 201,
                        "status" => "error",
                        "msg" => "Invalid deployment ID"
                    ]
                ]
            );
        }

        $processing = false;
        if($status->status == "processing")
        {
            $processing = true;
            $result = AutoDeploymentLib::fetchJsonOutput($id);
        }
        else
        {
            $result = $status->json_output;
            $result = json_decode($result, true);
        }

        $steps = [
            "insert_id",
            "git_pull",
            "remove_composer_lock",
            "composer_install",
            "optimize_clear",
            "restart_queue",
            "log_permission",
            "session_permission"
        ];

        $finalResult = [];

        foreach ($steps as $step)
        {
            if(isset($result[$step]))
            {
                $finalResult[$step] = $result[$step];
            }
        }

        $result = View('autodeployment::deploymentstatus', ['result' => $finalResult])->render();

        return response(
            [
                "header" => [
                    "code" => 200,
                    "status" => "success",
                    "msg" => "Ok"
                ],
                "body" => [
                    "processing" => $processing,
                    "data" => $result
                ]
            ]
        );
    }
}