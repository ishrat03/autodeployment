<?php

return [
    'default_branch' => env('AUTODEPLOY_DEFAULT_BRANCH', 'masters'),
    'sonarqube_branch' => env('SONARQUBE_SCAN_BRANCH', 'master'),
    'auth_required' => env('AUTODEPLOY_AUTH', true),
];