<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Ṣıẹrrḥa - Site Error Handler',
    'description' => '404 "not found" and 403 "forbidden" error handlers. The 404 handler shows custom content for missing pages but not resources like CSS or JS. The 403 handler redirects to a login URL on unauthorized access.',
    'category' => 'fe',
    'author' => 'plan2net TYPO3 development team',
    'author_email' => 'office@plan2.net',
    'author_company' => 'plan2net GmbH',
    'state' => 'stable',
    'version' => '0.4.3',
    'constraints' => [
        'depends' => [
            'backend' => '13.4.0 - 13.4.99',
            'frontend' => '13.4.0 - 13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
