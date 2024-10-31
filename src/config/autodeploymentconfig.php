<?php

return [
    'prod_default_branch' => env('PROD_AUTODEPLOY_DEFAULT_BRANCH', 'masters'),
    'prod_sonarqube_branch' => env('PROD_SONARQUBE_SCAN_BRANCH', 'master'),
    'prod_auth_required' => env('PROD_AUTODEPLOY_AUTH', true),
    'dev_default_branch' => env('DEV_AUTODEPLOY_DEFAULT_BRANCH', 'dev'),
    'dev_sonarqube_branch' => env('DEV_SONARQUBE_SCAN_BRANCH', 'dev'),
    'dev_auth_required' => env('DEV_AUTODEPLOY_AUTH', true),
    'dev_auto_deploy' => env('DEV_AUTO_DEPLOY', true),
    'prod_auto_deploy' => env('PROD_AUTO_DEPLOY', false),
];