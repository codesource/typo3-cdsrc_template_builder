<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


/**
 * Configure realURL based on languages and domains
 *
 * @author    Matthias Toscanelli <m.toscanelli@code-source.ch>
 * @version     1.0.4
 */
// Configuration
call_user_func(function () {
    $localConfiguration = include('LocalConfiguration.php');
    $realUrlData = array(
        // MD5 hash of current file for modification check
        'md5' => md5_file(__FILE__),
        // Append .html by default to URLs
        'appendHTMLsuffix' => true,
        // Disable language rewrite
        'disableLanguages' => true,
        // Default language set to 0
        'languages' => array('fr' => 0),
        // Language key
        'langKey' => 'L',
        // Should system use keyboard detection for language
        'detectKeyboard' => true,
        // Default domain root page configuration
        'domains' => array('_DEFAULT' => 1),
        // Make sure that Zlib is loaded for compression
        'cacheCompression' => function_exists('gzcompress') && function_exists('gzuncompress'),
        // Compression level
        'cacheCompressionLevel' => 9,
        // 1 hour
        'cacheDelay' => 2592000,
        // prefix ".ht_" will prevent file to be downloaded from most of webserver configuration
        'cacheFile' => PATH_site . 'typo3temp/.ht_realurlcache',
        // Domains specific options
        'domains_options' => array(
            // "defaultLanguage": Default language for domaine
        ),
    );


    // Check if file is cached.
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'] = array();
    if (is_file($realUrlData['cacheFile']) && ($GLOBALS['EXEC_TIME'] - filemtime($realUrlData['cacheFile'])) < $realUrlData['cacheDelay']) {
        try {
            if ($realUrlData['cacheCompression']) {
                $realUrlData['cacheContent'] = unserialize(gzuncompress(file_get_contents($realUrlData['cacheFile'])));
            } else {
                $realUrlData['cacheContent'] = unserialize(file_get_contents($realUrlData['cacheFile']));
            }
        } catch (Exception $e) {
        }
        if (is_array($realUrlData['cacheContent']) &&
            is_array($realUrlData['cacheContent']['realurl']) &&
            isset($realUrlData['cacheContent']['md5']) &&
            $realUrlData['cacheContent']['md5'] === $realUrlData['md5']
        ) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'] =& $realUrlData['cacheContent']['realurl'];
        }
    }


    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']) || count($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']) === 0) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'] = array();

        if (isset($localConfiguration['DB']['host'])) {
            $realUrlData['connection'] = new mysqli(
                $localConfiguration['DB']['host'],
                $localConfiguration['DB']['username'],
                $localConfiguration['DB']['password'],
                $localConfiguration['DB']['database'],
                $localConfiguration['DB']['port']
            );
        } else {
            $realUrlData['connection'] = new mysqli(
                $localConfiguration['DB']['Connections']['Default']['host'],
                $localConfiguration['DB']['Connections']['Default']['user'],
                $localConfiguration['DB']['Connections']['Default']['password'],
                $localConfiguration['DB']['Connections']['Default']['dbname'],
                $localConfiguration['DB']['Connections']['Default']['port']
            );
        }

        // Get datas from database
        if (!$realUrlData['connection']->connect_errno) {
            if (!$realUrlData['disableLanguages']) {
                if (($realUrlData['res'] = $realUrlData['connection']->query("SELECT uid, language_isocode FROM sys_language WHERE hidden = 0")) !== false) {
                    while (($realUrlData['language'] = $realUrlData['res']->fetch_assoc())) {
                        $realUrlData['languages'][strtolower($realUrlData['language']['language_isocode'])] = $realUrlData['language']['uid'];
                    }
                    $realUrlData['res']->close();
                }
            }

            if (($realUrlData['res'] = $realUrlData['connection']->query("SELECT pid, domainName FROM sys_domain WHERE hidden=0 ORDER BY sorting")) !== false) {
                while (($realUrlData['domain'] = $realUrlData['res']->fetch_assoc())) {
                    $realUrlData['domains'][$realUrlData['domain']['domainName']] = $realUrlData['domain']['pid'];
                }
                $realUrlData['res']->close();
            }
            $realUrlData['connection']->close();
        }

        if ($realUrlData['disableLanguages']) {
            $realUrlData['languages'] = array();
        }

        // Set realurl configuration array
        foreach ($realUrlData['domains'] as $domain => $pid) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'][$domain] = array(
                'init' => array(
                    'enableCHashCache' => 1,
                    'enableUrlDecodeCache' => 0,
                    'enableUrlEncodeCache' => 0,
                    'appendMissingSlash' => 'ifNotFile',
                    'respectSimulateStaticURLs' => 0,
                    'postVarSet_failureMode' => 'redirect_goodUpperDir',
                ),
                'redirects_regex' => array(
                    '.*robots.txt' => '?type=2',
                    '.*sitemap.xml' => '?type=3',
                ),
                'preVars' => array(
                    array(
                        'GETvar' => $realUrlData['langKey'],
                        'valueMap' => $realUrlData['languages'],
                        'noMatch' => 'bypass',
                    ),
                    array(
                        'GETvar' => 'no_cache',
                        'valueMap' => array(
                            'nc' => 1,
                        ),
                        'noMatch' => 'bypass',
                    ),
                ),
                'pagePath' => array(
                    'type' => 'user',
                    'userFunc' => 'EXT:realurl/class.tx_realurl_advanced.php:&tx_realurl_advanced->main',
                    'spaceCharacter' => '-',
                    'languageGetVar' => 'L',
                    'rootpage_id' => $pid,
                    'disablePathCache' => 0,
                    'expireDays' => 7,
                    'segTitleFieldList' => 'tx_realurl_pathsegment,alias,nav_title,title',
                    'excludePageIds' => null,
                ),
                'postVarSets' => array(
                    '_DEFAULT' => array(),
                ),
                'fixedPostVars' => array(),
                'fileName' => array(
                    'defaultToHTMLsuffixOnPrev' => $realUrlData['appendHTMLsuffix'],
                    'index' => array(
                        'robots.txt' => array(
                            'keyValues' => array(
                                'type' => 2,
                            ),
                        ),
                        'sitemap.xml' => array(
                            'keyValues' => array(
                                'type' => 3,
                            ),
                        ),
                    ),
                ),
            );
        }

        // Store in cached file
        if ($realUrlData['cacheCompression']) {
            GeneralUtility::writeFile($realUrlData['cacheFile'], gzcompress(serialize(array(
                'md5' => $realUrlData['md5'],
                'realurl' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'],
            )), $realUrlData['cacheCompressionLevel']));
        } else {
            GeneralUtility::writeFile($realUrlData['cacheFile'], serialize(array(
                'md5' => $realUrlData['md5'],
                'realurl' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'],
            )));
        }
    }

    // Post processes

    // Set language based on keyboard detection or domains options
    if (!$realUrlData['disableLanguages'] && !isset($_GET[$realUrlData['langKey']]) && !isset($_POST[$realUrlData['langKey']])) {
        $keyboardLang = '';
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'] as $d => $domain) {
            $lang = '';
            if (is_array($domain['preVars'])) {
                foreach ($domain['preVars'] as $p => $preVars) {
                    if ($preVars['GETvar'] === $realUrlData['langKey']) {
                        if (strlen($lang) === 0 && is_array($preVars['valueMap'])) {
                            // Get default language from domain's options
                            if (isset($realUrlData['domains_options'][$d]['defaultLanguage']) && isset($preVars['valueMap'][$realUrlData['domains_options'][$d]['defaultLanguage']])) {
                                $lang = $realUrlData['domains_options'][$d]['defaultLanguage'];
                            } elseif ($realUrlData['detectKeyboard']) {
                                if (strlen($keyboardLang) > 0) {
                                    $lang = $keyboardLang;
                                } else {
                                    foreach ($preVars['valueMap'] as $l => $luid) {
                                        if (strlen($l) > 0 && preg_match('/^' . $l . '.*$/',
                                                $_SERVER['HTTP_ACCEPT_LANGUAGE'])
                                        ) {
                                            $lang = $l;
                                            $keyboardLang = $l;
                                        }
                                    }
                                }
                            }
                        }
                        if (strlen($lang) > 0) {
                            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'][$d]['preVars'][$p]['noMatch'])) {
                                unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'][$d]['preVars'][$p]['noMatch']);
                            }
                            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'][$d]['preVars'][$p]['valueDefault'] = $lang;
                        }
                        break;
                    }
                }
            }
        }
    }
});
