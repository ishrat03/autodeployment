<?php

namespace Mohdishrat\Autodeployment;

use Composer\Script\Event;

class ComposerScripts
{
    public static function createPlaybooksDirectory(Event $event)
    {
        $projectRoot = getcwd();
        $playbooksDir = $projectRoot . '/playbooks';

        if (!is_dir($playbooksDir))
        {
          mkdir($playbooksDir, 0755, true);
        }

        $ymlContent = <<<YML
        # laraveldeployment.yml
        ---
        - hosts: localhost
          tasks:
            - name: Deploy Laravel Application
              shell: |
                php artisan cache:clear
        YML;

        file_put_contents("$playbooksDir/laraveldeployment.yml", $ymlContent);
    }
}