# Autodeployment Package

## 📜 Overview
This Laravel package `mohdishrat/autodeployment` provides a simplified solution to automatically deploy code through predefined routes and logic. It includes:
- Routes for managing deployment tasks
- A controller to handle logic
- Models and migrations to store deployment data
- A view to show records of the deployments.
- Use Ansible playbooks to deploy laravel projects
- Use git webhook to trigger deployment.

## 🚀 Features
- Automatic deployment routes
- Integrated controller and model for smooth handling
- Migration and view for easy data management
- Laravel compatibility

---

## ⚙️ Project Installation

### Step 1: Install via Composer
```bash
composer require mohdishrat/autodeployment
```

### Step 2: Update Provider
    Open bootstrap/providers.php
        add  Mohdishrat\Autodeployment\Providers\AutoDeploymentProvider::class in bootstrap/providers.php
    If bootstrap/provider.php is not present in project then
        add Mohdishrat\Autodeployment\Providers\AutoDeploymentProvider::class in config/app.php under providers key

### Step 3: Update Composer.json
    Open project composer.json
    add "Mohdishrat\\Autodeployment\\ComposerScripts::createPlaybooksDirectory" under scripts.post-autoload-dump key
        It will create necessary playbooks to start deployment

### Step 4: Setup .env
    These are the supported .env variables for deployment
        dv = default value
        PRODUCTION .env variables
            PROD_AUTODEPLOY_DEFAULT_BRANCH  // [dv = master] Default branch name for deployment.[take pull for this branch]
            PROD_DEPLOYMENT_PASSWORD   // Password to start deployment (password should be hashed with laravel Hash::mak())
            PROD_AUTO_DEPLOY  // [dv = false] [true, false] true if set for start deployment when PR merge and false when need human interaction for deployment.
            SSH_KEY_PATH    // full path of ssh_key which will be used to pull the key. Please make sure ssh_key don't have passphrase.
            PROD_AUTODEPLOY_AUTH  // [dv = true][true, false] true when need authentication to open deployment page and false for no authentication required for deployment pages.
            PROD_SONARQUBE_SCAN_BRANCH  // if added sonar scan in project

            // Mandatory .env_variables to send email
            MAIL_MAILER
            MAIL_HOST
            MAIL_PORT
            MAIL_USERNAME
            MAIL_PASSWORD
            MAIL_ENCRYPTION
            MAIL_FROM_ADDRESS
            MAIL_FROM_NAME
            MAIL_TO

        DEVELOPMENT .env variables
            DEV_AUTODEPLOY_DEFAULT_BRANCH
            DEV_DEPLOYMENT_PASSWORD
            DEV_AUTO_DEPLOY
            SSH_KEY_PATH
            DEV_AUTODEPLOY_AUTH
            DEV_SONARQUBE_SCAN_BRANCH

            // Mandatory .env_variables to send email
            MAIL_MAILER
            MAIL_HOST
            MAIL_PORT
            MAIL_USERNAME
            MAIL_PASSWORD
            MAIL_ENCRYPTION
            MAIL_FROM_ADDRESS
            MAIL_FROM_NAME
            MAIL_TO

### Step 5: Run Artisan command and Compoder Autload
    run command
        composer dump-autoload
        php artisan optimize:clear

    this will load .env values and create necessary playbooks

### Step 6: Update .gitignore
    add playbooks/*   // it will ignore the playbooks in git.

## ⚙️ Server Installation

### Step 1: Install Ansible Playbook
    Linux
        $ sudo apt update
        $ sudo apt install software-properties-common
        $ sudo add-apt-repository --yes --update ppa:ansible/ansible
        $ sudo apt install ansible

    For Other OS installation please follow the documentation
        https://docs.ansible.com/ansible/latest/installation_guide/installation_distros.html

### Step 2: Install supervisor
    Linux
        $ sudo apt update
        $ sudo apt install supervisor
    
    Add config to run playbooks
        goto /etc/supervisor/conf.d
            nano cicd.conf
                past below lines [before save update projectpath]

                [program:cicd]
                process_name=%(program_name)s_%(process_num)02d
                command=php app/to/projectpath/artisan queue:work --queue=cicd --timeout=6000 --max-jobs=2
                autostart=true
                autorestart=true
                user=root
                numprocs=1
                redirect_stderr=true
                stdout_logfile=/var/log/supervisor/laravel-queue.log

    Load config file
        $ sudo supervisorctl update

## ⚙️ Git Setup

## Step 1: Add Webhook
    Add webhook to send webhook notification for Pull Request create or merge.
        URL: domain/deploymentwebhook
        METHOD: POST
    
    Webhook Setting
        enable Pull request Setting
            Pull request Created  // this will be used for sonar qube scan
            Pull request Merged   // this will be used for deployment