<?php

require_once __DIR__.'/vendor/dgncan/init/src/functions.php';

return  [
    'application-name'=>'api-erp',
    'update-assets'=>
    [
    ],
    'update-tasks'=>
    [
        'example task'=>
            function () {
                echo "hello";
            },
    ],
    'update-http-conf'=>
    [
        'confPath'=> [
            'local'=>'/usr/local/httpd_docs/conf/',
            'prod'=>'/work/conf/'
        ]
    ],
    'prod-ini-file' => '',
    'permission' =>
    [
        'chown'=>'www.www',
        'chmod'=>'755'
    ]
];
