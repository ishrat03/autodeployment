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

## ⚙️ Installation

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