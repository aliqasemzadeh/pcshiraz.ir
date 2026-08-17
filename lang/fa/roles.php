<?php

return [
    'super-admin' => [
        'label' => 'مدیر کل',
        'permissions' => ['*'],
    ],
    'administrator' => [
        'label' => 'مدیر سیستم',
        'permissions' => ['administrator.*'],
    ],
    'sale-manager' => [
        'label' => 'مدیر فروش',
        'permissions' => ['sale.*'],
    ],
    'organization-manager' => [
        'label' => 'مدیر سازمان',
        'permissions' => ['organization.*'],
    ],
];
