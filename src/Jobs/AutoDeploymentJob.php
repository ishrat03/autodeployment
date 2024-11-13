<?php

namespace Mohdishrat\Autodeployment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mohdishrat\Autodeployment\Libraries\AutoDeploymentLib;
use Mohdishrat\Autodeployment\Libraries\Constants;
use Mohdishrat\Autodeployment\Models\AutoDeployment;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AutoDeploymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public $branch;
    public $insertId;
    public $type;
    public function __construct($insertId, $type, $branch)
    {
        $this->branch = $branch;
        $this->insertId = $insertId;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set("max_execution_time", 6000);
        Log::info("@Deployment Started Deployment or sonar scan Now");
        AutoDeployment::where("id", $this->insertId)
            ->update(
                [
                    "name" => $this->type,
                    "status" => 'processing',
                    "deployment_start_time" => date(Constants::CURRENTDATETIME),
                    "updated_at" => date(Constants::CURRENTDATETIME),
                ]
            );

        $branch = config('autodeploymentconfig.prod_default_branch');
        $envPointing = "Production";

        if(env("APP_ENV") == 'local')
        {
            $branch = config('autodeploymentconfig.dev_default_branch');
            $envPointing = "Staging";
        }

        if($this->branch == "")
        {
            $playbookPath = base_path('playbooks/laraveldeployment.yml');
        }
        else
        {
            $playbookPath = base_path("playbooks/sonarscan.yml");
            $branch = $this->branch;
        }

        // Define the environment variables required for Ansible
        $envVars = [
            'ANSIBLE_CONFIG' => '/path/to/ansible.cfg', // Update this path as needed
            'HOME' => base_path(),
        ];

        $params = "-e project_path='{$envVars['HOME']}' -e branch='{$branch}' -e env_pointing='{$envPointing}' -e insert_id='{$this->insertId}'";

        Log::info("@Deployment sending calls for playbook", [$playbookPath, $params]);
        $process = new Process(['ansible-playbook', $playbookPath, $params]);
        
        // Set environment variables for the process
        $process->setEnv($envVars)->setTimeout(0);

        try
        {
            $process->mustRun();
            Log::info('@Deployment Ansible playbook executed successfully.', [$process->getOutput()]);

            if($this->branch == "")
            {
                Log::info("@Deployment Deployment Completed Now");
                $finalJsonOutput = AutoDeploymentLib::fetchJsonOutput($this->insertId, true);
                info("@Deployment final", $finalJsonOutput);
            }
            else
            {
                Log::info("@Deployment Sonar Scam Completed for branch {$this->branch}");
                $finalJsonOutput = [];
            }

            AutoDeployment::where("id", $this->insertId)
                ->update(
                    [
                        "process_output" => $process->getOutput(),
                        "status" => 'completed',
                        "updated_at" => date(Constants::CURRENTDATETIME),
                        "deployment_end_time" => date(Constants::CURRENTDATETIME),
                        "json_output" => json_encode($finalJsonOutput, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    ]
                );

            if($this->branch == "")
            {
                $result = [
                    "result" => $finalJsonOutput,
                    "env_pointing" => ucwords(env("APP_ENV")),
                    "branch" => $branch,
                    "deploymentStatus" => $finalJsonOutput["deployment_status"]
                ];

                info("@Deployment Sending deployment complete email notification");
                AutoDeploymentLib::sendDeploymentEmail($result);
                info("@Deployment Email Sent Successfully");
            }

            info("@Deployment Auto Deployment or Sonar Scan Completed now");
        }
        catch (ProcessFailedException $e)
        {
            Log::error('@Deployment Ansible playbook execution failed: ' . $e->getMessage());
            Log::error("@Deployment Deployment or sonar scan Failed.");
        }
    }
}
