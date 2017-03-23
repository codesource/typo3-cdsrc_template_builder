<?php
/**
 * @copyright Copyright (c) 2016 Code-Source
 */
$ll = 'LLL:EXT:{|EXTENSION_KEY|}/Resources/Private/Language/locallang_db.xml:tt_content.tx_template_excluded_for';
$gfx = 'EXT:{|EXTENSION_KEY|}/Resources/Public/Icons/';
$tempColumn = [
    'tx_template_excluded_for' => [
        'exclude' => 1,
        'l10n_mode' => 'exclude',
        'label' => $ll,
        'config' => [
            'type' => 'select',
            'renderType' => 'selectCheckBox',
            'items' => [
                [$ll . '.desktop', 'h-desktop', $gfx . 'h-desktop.png', $ll . '.desktop.description'],
                [$ll . '.tablet', 'h-tablet', $gfx . 'h-tablet.png', $ll . '.tablet.description'],
                [$ll . '.smartphone', 'h-smartphone', $gfx . 'h-smartphone.png', $ll . '.smartphone.description'],
            ],
            'size' => 5,
            'default' => 0,
            'showIconTable' => true
        ]
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumn);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'tx_template_excluded_for', '', 'after:layout');