<?php
namespace Mohdishrat\Autodeployment\Console;

use Illuminate\Console\Command;

class UpdateDeploymentStatus extends Command
{
    protected $signature = 'update:deploymentstatus';
    protected $description = 'Update The deployment status in auto_deployment table.';

    public function handle()
    {
        info('Command is running!');
        // Add your command logic here
    }
}
