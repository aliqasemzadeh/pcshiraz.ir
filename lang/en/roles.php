<?php

return [
    'super-admin' => [
        'label' => 'Super Admin',
        'permissions' => ['*'],
    ],
    'administrator' => [
        'label' => 'Administrator',
        'permissions' => ['administrator.*'],
    ],
    'sale-manager' => [
        'label' => 'Sale Manager',
        'permissions' => ['sale.*'],
    ],
    'organization-manager' => [
        'label' => 'Organization Manager',
        'permissions' => ['organization.*'],
    ],
];
