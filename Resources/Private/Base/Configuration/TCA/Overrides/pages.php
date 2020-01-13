<?php
/**
 * @copyright Copyright (c) 2017 Code-Source
 */

// Load dynamically PageTS
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$extensionPath = ExtensionManagementUtility::extPath('{|EXTENSION_KEY|}');
$pagesTS = GeneralUtility::getFilesInDir($extensionPath . 'Configuration/PageTS', 'txt', TRUE, '', '^_.*$');
foreach ($pagesTS as $file) {
    $name = ucwords(preg_replace('/[^a-z0-9]/i', ' ', pathinfo($file, PATHINFO_FILENAME)));
    $path = substr($file, strlen($extensionPath));
    ExtensionManagementUtility::registerPageTSConfigFile(
        '{|EXTENSION_KEY|}',
        $path,
        $name . ' configuration'
    );
}