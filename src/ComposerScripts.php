<?php

namespace Mohdishrat\Autodeployment;
class ComposerScripts
{
    public static function createPlaybooksDirectory()
    {
        $projectRoot = getcwd();
        echo '<pre>';print_r($projectRoot);echo ' proje$projectRoot</pre>';
        $logfile = '/tmp/composer_script.log'; // Adjust path if needed
        file_put_contents($logfile, "Executing createPlaybooksDirectory\n", FILE_APPEND);

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
