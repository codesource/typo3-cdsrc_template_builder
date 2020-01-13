<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE'))
    die('Access denied.');

if (TYPO3_MODE === 'BE') {
    ExtensionUtility::registerModule(
        'CDSRC.cdsrc_template_builder',
        'tools',
        'templatebuilder',
        '',
        array(
            'Template' => 'index,preview,new,create',
        ),
        array(
            'access' => 'user,group',
            'icon' => 'EXT:cdsrc_template_builder/Resources/Public/Icons/module.svg',
            'labels' => 'LLL:EXT:cdsrc_template_builder/Resources/Private/Language/locallang_mod.xlf',
        )
    );
}