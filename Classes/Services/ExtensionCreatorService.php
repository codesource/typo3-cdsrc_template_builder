<?php

namespace CDSRC\CdsrcTemplateBuilder\Services;


/* **********************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * ******************************************************************** */
use CDSRC\CdsrcTemplateBuilder\Domain\Model\Template;
use CDSRC\CdsrcTemplateBuilder\Exceptions\ExtensionCreationException;
use CDSRC\CdsrcTemplateBuilder\Exceptions\ExtensionExistsException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtensionCreatorService
 *
 * @package CDSRC\CdsrcTemplateBuilder\Services
 */
class ExtensionCreatorService extends AbstractTemplateService
{

    /**
     * @var array
     */
    protected $substitutionKeys;

    /**
     * @var array
     */
    protected $substitutionData;


    /**
     * Constructor
     *
     * @param Template $template
     */
    public function __construct(Template $template)
    {
        parent::__construct($template);
        $substitutionData = array(
            '{|EXTENSION_KEY|}' => $this->template->getKey(),
            '{|EXTENSION_PATH|}' => 'EXT:' . substr(
                    realpath(ExtensionManagementUtility::extPath('cdsrc_template_builder') . '../' . $this->template->getKey()),
                    strlen(PATH_site)
                ),
            '{|EXTENSION_NAME|}' => $this->template->getName(),
            '{|EXTENSION_DESCRIPTION|}' => $this->template->getDescription(),
            '{|EXTENSION_GENERATE_YEAR|}' => $this->template->getCreatedAt()->format('Y'),
            '{|EXTENSION_GENERATE_DATE|}' => $this->template->getCreatedAt()->format('d-m-Y H:i'),
            '{|EXTENSION_AUTHOR_NAME|}' => $this->template->getAuthorName(),
            '{|EXTENSION_AUTHOR_EMAIL|}' => $this->template->getAuthorEmail(),
            '{|EXTENSION_AUTHOR_COMPANY|}' => $this->template->getAuthorCompany(),
            '{|EXTENSION_AUTHOR_COMPANY_WEBSITE|}' => $this->template->getAuthorCompanyWebsite(),
            '{|EXTENSION_TYPO3_DEPENDENCY|}' => TYPO3_branch . '.0-' . TYPO3_branch . '.99',
            '//{|EXTENSION_DEPENDENCIES|}' => $this->buildExtensionDependencies(),
        );
        $this->substitutionKeys = array_keys($substitutionData);
        $this->substitutionData = array_values($substitutionData);
    }

    /**
     * Create the new extension based on template object
     *
     * @throws ExtensionExistsException
     */
    public function execute()
    {
        if (ExtensionManagementUtility::getExtensionKeyByPrefix($this->template->getKey()) !== false) {
            throw new ExtensionExistsException('This extension key already exists.', 1445515206);
        }

        // Create root directory
        $extensionRootPath = PATH_site . 'typo3conf/ext/' . $this->template->getKey();
        if (is_dir($extensionRootPath)) {
            throw new ExtensionCreationException('Extension\'s root directory already exists.', 1445515207);
        }
        if (!GeneralUtility::mkdir($extensionRootPath)) {
            throw new ExtensionCreationException('Unable to create extension\'s root directory.', 1445515208);
        }

        // Copy base to directory
        try {
            $this->copyBaseDirectory($extensionRootPath);
        } catch (\Exception $e) {
            $this->rollback($extensionRootPath);
            throw $e;
        }
    }

    /**
     * Return the dependencies PHP array part
     *
     * @return string
     */
    protected function buildExtensionDependencies()
    {
        $dependencies = array();
        /** @var \TYPO3\CMS\Extensionmanager\Domain\Model\Extension $dependency */
        foreach ($this->template->getDependencies() as $dependency) {
            $dependencies[] = sprintf("   			'%s' => '%s',", $dependency->getExtensionKey(),
                $dependency->getVersion() . '-');
        }

        return implode("\n", $dependencies);
    }

    /**
     * Copy directory and substitute variables in files
     *
     * @param $extensionRootPath
     *
     * @throws ExtensionCreationException
     */
    protected function copyBaseDirectory($extensionRootPath)
    {
        $source = ExtensionManagementUtility::extPath('cdsrc_template_builder') . 'Resources/Private/Base';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $target = $extensionRootPath . '/' . $iterator->getSubPathName();
            if ($item->isDir()) {
                GeneralUtility::mkdir($target);
                if (!is_dir($target)) {
                    throw new ExtensionCreationException('Unable to copy directory \'' . $iterator->getSubPathName() . '\'.',
                        1445515209);
                }
            } else {
                GeneralUtility::upload_copy_move($item, $target);
                if (file_exists($target)) {
                    $this->substituteTemplateInFile($target);
                } else {
                    throw new ExtensionCreationException('Unable to copy file \'' . $iterator->getSubPathName() . '\'.',
                        1445515210);
                }
            }
        }
        if ($this->template->isUseExtendedRealUrlConfigurationFile()) {
            $realUrlConfiguration = $source . '/../Extension/realurl/realurl_conf.php';
            $target = ExtensionManagementUtility::extPath('cdsrc_template_builder') . '../../realurl_conf.php';
            GeneralUtility::upload_copy_move($realUrlConfiguration, $target);
            if (file_exists($target)) {
                $this->substituteTemplateInFile($target);
            } else {
                throw new ExtensionCreationException('Unable to copy file \'' . $realUrlConfiguration . '\'.',
                    1445515210);
            }
        }
    }

    /**
     * Substitute all available variables in file
     *
     * @param $filename
     *
     * @throws ExtensionCreationException
     */
    protected function substituteTemplateInFile($filename)
    {
        $content = file_get_contents($filename);
        if ($content === false) {
            throw new ExtensionCreationException('Unable to read file \'' . substr($filename,
                    strlen(PATH_site)) . '\'.', 1445515212);
        }
        if (file_put_contents($filename,
                str_replace($this->substitutionKeys, $this->substitutionData, $content)) === false
        ) {
            throw new ExtensionCreationException('Unable to substitute data in file \'' . substr($filename,
                    strlen(PATH_site)) . '\'.', 1445515213);
        }
    }

    /**
     * Remove create root directory
     *
     * @param $extensionRootPath
     *
     * @throws ExtensionCreationException
     */
    protected function rollback($extensionRootPath)
    {
        if (!GeneralUtility::rmdir($extensionRootPath)) {
            throw new ExtensionCreationException('Unable to delete extension\'s root directory.', 1445515211);
        }
    }
}