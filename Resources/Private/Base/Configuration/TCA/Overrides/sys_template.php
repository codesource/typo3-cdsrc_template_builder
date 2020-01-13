<?php
/**
 * @copyright Copyright (c) 2017 Code-Source
 */



// Load default extension template
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addStaticFile('{|EXTENSION_KEY|}', 'Configuration/TypoScript', 'Default Template');