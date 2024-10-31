<?php

namespace Mohdishrat\Autodeployment;
class ComposerScripts
{
    public static function createPlaybooksDirectory()
    {
        $projectRoot = getcwd();
        $playbooksDir = "{$projectRoot}/playbooks";

        if (!is_dir($playbooksDir))
        {
          mkdir($playbooksDir, 0755, true);
        }

        $ymlContent = <<<YML
        # laraveldeployment.yml
        ---
        -   name: Auto Deployment
            hosts: localhost
            vars:
                env_file_path: "{{project_path}}/.env"
                json_log_file: "{{ project_path }}/public/deployment/deployment_log.json"
            
            tasks:
                -   name: Remove Composer File
                    command: rm composer.lock
                    args:
                        chdir: "{{project_path}}"
                    register: remove_composerlock
                    when:
                        - git_pull is succeeded
                    ignore_errors: yes

                -   name: Remove Composer File Log
                    copy:
                        dest: "{{ json_log_file }}"
                        content: "{{ { 'remove_composerlock': remove_composerlock | to_json } | to_nice_json }}"
                    when: remove_composerlock is defined

                -   name: Composer Install
                    command: composer install
                    args:
                        chdir: "{{project_path}}"
                    register: composer_install
                    ignore_errors: yes
                    when:
                        - remove_composerlock is succeeded

                -   name: Composer Install Log
                    copy:
                        dest: "{{ json_log_file }}"
                        content: "{{ { 'composer_install': remove_composerlock | to_json } | to_nice_json }}"
                    when:
                        - remove_composerlock is defined
                        - composer_install is defined
        YML;

        file_put_contents("$playbooksDir/laraveldeployment.yml", $ymlContent);
    }
}
