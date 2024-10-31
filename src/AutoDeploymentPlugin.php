<?php
namespace Mohdishrat\Autodeployment;

use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;

class AutoDeploymentPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate()
    {
        // Called when the plugin is activated
    }

    public static function getSubscribedEvents()
    {
        return [
            'post-autoload-dump' => 'onPostAutoloadDump',
        ];
    }

    public static function onPostAutoloadDump()
    {
        $projectRoot = getcwd();
        $playbooksDir = $projectRoot . '/playbooks';

        if (!is_dir($playbooksDir)) {
            mkdir($playbooksDir, 0755, true);
            file_put_contents($playbooksDir . '/laraveldeployment.yml', "# Laravel deployment config\n");
        }
    }
}
