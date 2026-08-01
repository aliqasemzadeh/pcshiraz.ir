<?php

return [
    'token' => env('SMS_TOKEN'),
    'gateway' => env('SMS_GATEWAY', '100001860'),
    'url' => env('SMS_URL', 'https://srscrm.ir/api/sms/send'),
];
