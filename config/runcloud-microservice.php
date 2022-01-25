<?php

return [
    "key"     => env('RUNCLOUD_COMMUNICATION_KEY'),

    "domains" => [
        "account"      => env('RUNCLOUD_ACCOUNT_DOMAIN'),
        "notification" => env('RUNCLOUD_NOTIFICATION_DOMAIN'),
        "server"       => env('RUNCLOUD_SERVER_DOMAIN'),
        "bigdata"      => env('RUNCLOUD_BIGDATA_DOMAIN'),
        "acme"         => env('RUNCLOUD_ACME_DOMAIN'),
        "support"      => env('RUNCLOUD_SUPPORT_DOMAIN'),
        "backup"       => env('RUNCLOUD_BACKUP_DOMAIN'),
        "iam"          => env('RUNCLOUD_IAM_DOMAIN'),
    ],
];
