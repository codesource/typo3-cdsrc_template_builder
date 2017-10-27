<?php
/**
 * @copyright Copyright (c) 2017 Code-Source
 */

// Load dynamically PageTS
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('{|EXTENSION_KEY|}');
$pagesTS = \TYPO3\CMS\Core\Utility\GeneralUtility::getFilesInDir($extensionPath . 'Configuration/PageTS', 'txt', TRUE, '', '^_.*$');
foreach ($pagesTS as $file) {
    $name = ucwords(preg_replace('/[^a-z0-9]/i', ' ', pathinfo($file, PATHINFO_FILENAME)));
    $path = substr($file, strlen($extensionPath));
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
        '{|EXTENSION_KEY|}',
        $path,
        $name . ' configuration'
    );
}