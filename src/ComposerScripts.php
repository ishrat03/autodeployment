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

        $logContent = <<<YML
        # log_task.yml
        -   name: Update Deployment Log Variable
            set_fact:
                deployment_log: "{{ deployment_log + [ { log_key: log_value } ] }}"

        -   name: Write Log to JSON File
            copy:
                dest: "{{ json_log_file }}"
                content: "{{ deployment_log | to_nice_json }}"
        YML;

        file_put_contents("$playbooksDir/log_task.yml", $logContent);

        $a = '\\\\1';
        $ymlContent = <<<YML
        # laraveldeployment.yml
        ---
        -   name: Auto Deployment
            hosts: localhost
            vars:
                env_file_path: "{{ project_path }}/.env"
                json_log_file: "{{ project_path }}/public/deployment/deployment_log_{{insert_id}}.json"
                initial_stage: '[{"deployment_id": {{insert_id}} },{"git_pull": "pending"},{"composer_install": "pending"},{"migration": "pending"},{"optimize_clear":"pending"}, {"restart_queue": "pending"}, {"log_permission": "pending"}, {"deployment_status": "failed"}]'
                ssh_key_path: "{{ (lookup('file', env_file_path) | regex_search('^SSH_KEY_PATH=(.*)$', '$a', multiline=True))[0].strip() | replace('\"', '') }}"
  
            tasks:
                -   name: Ensure Log Directory exists
                    ansible.builtin.file:
                        path: "{{project_path}}/public/deployment"
                        state: directory
                        mode: '0755'

                -   name: Parse Existing Log Content
                    set_fact:
                        deployment_log: "{{ (existing_log.content | b64decode | from_json) if existing_log is defined else initial_stage }}"

                -   name: Write Initial Log to File
                    copy:
                        dest: "{{ json_log_file }}"
                        content: "{{ deployment_log | to_nice_json }}"

                -   name: Add Project Directory to safe Directory
                    command: "git config --global --add safe.directory {{project_path}}"
                    args:
                        chdir: "{{project_path}}"
                    ignore_errors: yes

                -   name: Pull New Changes
                    command: "git pull origin {{branch}}"
                    args:
                        chdir: "{{project_path}}"
                    environment:
                        GIT_SSH_COMMAND: "ssh -i {{ssh_key_path}} -o StrictHostKeyChecking=no"
                    register: git_pull
                    ignore_errors: yes

                -   name: Log Optimize Clear
                    include_tasks: log_task.yml
                    vars:
                        log_key: 'git_pull'
                        log_value: "{{ git_pull }}"

                -   name: Remove Composer Lock
                    command: rm composer.lock
                    args:
                        chdir: "{{project_path}}"
                    register: remove_composer_lock
                    ignore_errors: yes
                    when:
                        - git_pull is succeeded

                -   name: Log Optimize Clear
                    include_tasks: log_task.yml
                    vars:
                        log_key: 'remove_composer_lock'
                        log_value: "{{ remove_composer_lock }}"

                -   name: Composer Install
                    command: composer install
                    args:
                        chdir: "{{project_path}}"
                    register: composer_install
                    ignore_errors: yes
                    when:
                        - git_pull is succeeded

                -   name: Log Composer Install
                    include_tasks: log_task.yml
                    vars:
                        log_key: 'composer_install'
                        log_value: "{{ composer_install }}"

                -   name: Run Migrations
                    command: php artisan migrate --force
                    args:
                        chdir: "{{project_path}}"
                    register: migration
                    ignore_errors: yes
                    when:
                        - git_pull is succeeded
                        - composer_install is succeeded

                -   name: Log Migrations
                    include_tasks: log_task.yml
                    vars:
                        log_key: 'migration'
                        log_value: "{{migration}}"

                -   name: Optimize Clear
                    command: php artisan optimize:clear
                    args:
                        chdir: "{{project_path}}"
                    register: optimize_clear
                    ignore_errors: yes
                    when:
                        - git_pull is succeeded
                        - composer_install is succeeded

                -   name: Log Optimize Clear
                    include_tasks: log_task.yml
                    vars:
                        log_key: 'optimize_clear'
                        log_value: "{{ optimize_clear }}"

                -   name: Restart Queue
                    command: php artisan queue:restart
                    args:
                        chdir: "{{project_path}}"
                    register: restart_queue
                    ignore_errors: yes
                    when:
                        - git_pull is succeeded
                        - composer_install is succeeded
                        - optimize_clear is succeeded

                -   name: Log Restart Queue
                    include_tasks: log_task.yml
                    vars:
                        log_key: 'restart_queue'
                        log_value: "{{restart_queue}}"

                -   name: Set Logs Permission
                    command: sudo chmod -R 777 storage/logs
                    args:
                        chdir: "{{project_path}}"
                    register: log_permission
                    ignore_errors: yes
                    when:
                        - git_pull is succeeded
                        - composer_install is succeeded
                        - optimize_clear is succeeded
                        - migration is succeeded
                        - restart_queue is succeeded

                -   name: Log Log Permission
                    include_tasks: log_task.yml
                    vars:
                        log_key: 'log_permission'
                        log_value: "{{log_permission}}"

                -   name: Set Session Freamework Permission
                    command: sudo chmod -R 777 storage/framework
                    args:
                        chdir: "{{project_path}}"
                    register: session_permission
                    ignore_errors: yes
                    when:
                        - git_pull is succeeded
                        - composer_install is succeeded
                        - optimize_clear is succeeded
                        - migration is succeeded
                        - restart_queue is succeeded

                -   name: Log session Permission
                    include_tasks: log_task.yml
                    vars:
                        log_key: 'session_permission'
                        log_value: "{{session_permission}}"

                -   name: Log Deployment Status
                    include_tasks: log_task.yml
                    vars:
                        log_key: 'deployment_status'
                        log_value: "success"
                    when:
                        - git_pull is succeeded
                        - composer_install is succeeded
                        - migration is succeeded
                        - optimize_clear is succeeded

        YML;

        file_put_contents("$playbooksDir/laraveldeployment.yml", $ymlContent);
    }
}
