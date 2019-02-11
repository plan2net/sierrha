<?php

$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['tx_sierrha_loginPage'] = [
    'label' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_loginPage',
    'displayCond' => 'FIELD:errorCode:=:403',
    'config' => [
        'type' => 'input',
        'renderType' => 'inputLink',
        'placeholder' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_loginPage.placeholder',
        'eval' => 'trim',
        'fieldControl' => [
            'linkPopup' => [
                'options' => [
                    'title' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_loginPage.wizard_title',
                    'blindLinkOptions' => 'mail,file,spec,folder',
                    'blindLinkFields' => 'class,class,params,target,title',
                ]
            ]
        ]
    ]
];

$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['tx_sierrha_missingPermissionsPage'] = [
    'label' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_missingPermissionsPage',
    'displayCond' => 'FIELD:errorCode:=:403',
    'config' => [
        'type' => 'input',
        'renderType' => 'inputLink',
        'placeholder' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_missingPermissionsPage.placeholder',
        'eval' => 'trim',
        'fieldControl' => [
            'linkPopup' => [
                'options' => [
                    'title' => 'LLL:EXT:sierrha/Resources/Private/Language/locallang_tca.xlf:site_errorhandling.tx_sierrha_missingPermissionsPage.wizard_title',
                    'blindLinkOptions' => 'mail,file,spec,folder',
                    'blindLinkFields' => 'class,class,params,target,title',
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

// TYPO3 bug (?): works only when the user changes the value using the value picker, but NOT when changing the value directly in the field  
$GLOBALS['SiteConfiguration']['site_errorhandling']['columns']['errorCode']['onChange'] = 'reload';

$GLOBALS['SiteConfiguration']['site_errorhandling']['types']['PHP']['showitem'] .= ', tx_sierrha_loginPage, tx_sierrha_missingPermissionsPage, tx_sierrha_loginUrlParameter';

