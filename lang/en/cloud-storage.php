<?php

return [
    'title' => 'title',
    'driver' => 'driver',
    'driver_select' => [
        [
            'label' => 'local',
            'value' => 'local',
        ],
        [
            'label' => 'AliYun OSS',
            'value' => 'oss',
        ],
        [
            'label' => 'TengXun COS',
            'value' => 'cos',
        ],
        [
            'label' => 'QiniuYun KODO',
            'value' => 'kodo',
        ],
    ],
    'access_key' => 'Access Key',
    'secret_key' => 'Secret Key',
    'endpoint' => 'Endpoint',
    'bucket' => 'Bucket',
    'domain' => 'Domain',
    'description' => 'Description',
    'sort' => 'Sort',
    'extension' => 'extension',
    'created_user' => 'Created User',
    'updated_user' => 'Updated User',
    'deleted_user' => 'Deleted User',
    'status' => 'status',
    'status_select' => [
        1 => 'enable',
        2 => 'forbidden',
    ],
    'is_default' => 'Default or not',
    'is_default_select' => [
        1 => 'yes',
        2 => 'no',
    ],
];
