<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Ṣıẹrrḥa - Site Error Handler',
    'description' => 'A 403 "forbidden" error handler that redirects to a login URL.',
    'category' => 'fe',
    'author' => '',
    'author_email' => '',
    'author_company' => 'plan2net',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => 'pages',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'backend' => '9.5',
            'frontend' => '9.5',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
