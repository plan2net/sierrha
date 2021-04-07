<?php

$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['tx_sierrha_notFoundContentSource'] = [
    'label' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_notFoundContentSource',
    'displayCond' => 'FIELD:errorCode:=:404',
    'config' => [
        'type' => 'input',
        'renderType' => 'inputLink',
        'placeholder' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha.inputLink.placeholder',
        'eval' => 'trim',
        'fieldControl' => [
            'linkPopup' => [
                'options' => [
                    'blindLinkOptions' => 'mail,file,spec,folder',
                    'blindLinkFields' => 'class,params,target,title',
                ],
            ],
        ],
    ],
];
$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['tx_sierrha_loginPage'] = [
    'label' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_loginPage',
    'displayCond' => 'FIELD:errorCode:=:403',
    'config' => [
        'type' => 'input',
        'renderType' => 'inputLink',
        'placeholder' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha.inputLink.placeholder',
        'eval' => 'trim',
        'fieldControl' => [
            'linkPopup' => [
                'options' => [
                    'blindLinkOptions' => 'mail,file,spec,folder',
                    'blindLinkFields' => 'class,params,target,title',
                ]
            ]
        ]
    ]
];

$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['tx_sierrha_loginUrlParameter'] = [
    'label' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_loginUrlParameter',
    'displayCond' => 'FIELD:errorCode:=:403',
    'config' => [
        'type' => 'input',
        'eval' => 'trim',
        'default' => 'return_url=###URL###', // the parameter used by extension "felogin"
    ],
];

$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['tx_sierrha_noPermissionsContentSource'] = [
    'label' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_noPermissionsContentSource',
    'displayCond' => 'FIELD:errorCode:=:403',
    'config' => [
        'type' => 'input',
        'renderType' => 'inputLink',
        'placeholder' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha.inputLink.placeholder',
        'eval' => 'trim',
        'fieldControl' => [
            'linkPopup' => [
                'options' => [
                    'blindLinkOptions' => 'mail,file,spec,folder',
                    'blindLinkFields' => 'class,params,target,title',
                ]
            ]
        ]
    ]
];

// TYPO3 bug (?): works only when the user changes the value using the value picker, but NOT when changing the value directly in the field  
$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['errorCode']['onChange'] = 'reload';

$GLOBALS['SiteConfiguration']['site_errorhandling']['palettes']['tx_sierrha_login']
    = ['showitem' => 'tx_sierrha_loginPage, tx_sierrha_loginUrlParameter'];

$GLOBALS['SiteConfiguration']['site_errorhandling']['types']['PHP']['showitem'] .= ', tx_sierrha_notFoundContentSource, --palette--;;tx_sierrha_login, tx_sierrha_noPermissionsContentSource';
