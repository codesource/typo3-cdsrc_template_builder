<?php

if (!defined('TYPO3_MODE'))
    die('Access denied.');

// Load default extension template
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Default Template');

// Load dynamically PageTS
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY);
$pagesTS = \TYPO3\CMS\Core\Utility\GeneralUtility::getFilesInDir($extensionPath . 'Configuration/PageTS', 'txt', TRUE, '', '^_.*$');
foreach ($pagesTS as $file) {
    $name = ucwords(preg_replace('/[^a-z0-9]/i', ' ', pathinfo($file, PATHINFO_FILENAME)));
    $path = substr($file, strlen($extensionPath));
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerPageTSConfigFile(
        $_EXTKEY,
        $path,
        $name . ' configuration'
    );
}