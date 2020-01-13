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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
//use TYPO3\CMS\Core\Database\DatabaseConnection;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageTreeCreatorService extends AbstractTemplateService
{
    /**
     * @var Connection
     */
    protected $database;

    /**
     * @var BackendUserAuthentication
     */
    protected $beUser;

    /**
     * @var integer
     */
    protected $now;

    /**
     * @var DataHandler
     */
    protected $dataHandler;

    /**
     * Constructor
     *
     * @param Template $template
     */
    public function __construct(Template $template)
    {
        parent::__construct($template);
        $this->database = $GLOBALS['TYPO3_DB'];
        $this->beUser = $GLOBALS['BE_USER'];
        $this->now = $GLOBALS['EXEC_TIME'];

        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $this->dataHandler->stripslashes_values = 0;
        // set default TCA values specific for the user
//        $TCADefaultOverride = $this->beUser->getTSConfigProp('TCAdefaults');
//        if (is_array($TCADefaultOverride)) {
//            $this->dataHandler->setDefaultsFromUserTS($TCADefaultOverride);
//        }
    }

    /**
     * Create page tree and join extension
     *
     * @throws ExtensionCreationException
     */
    public function execute()
    {
        $this->createTree();
        $this->updateTree();
    }

    /**
     * Create the full page tree and children records
     *
     * @throws ExtensionCreationException
     */
    protected function createTree(){
        $data = $this->buildPageTree();
        reset($data);
        $this->dataHandler->start($data, array());
        if($this->dataHandler->process_datamap() === FALSE){
            throw new ExtensionCreationException('Unable to create the full page tree.', 1445527262);
        }
    }

    /**
     * Update page tree and children records
     *
     * @throws ExtensionCreationException
     */
    protected function updateTree(){
        $parameters = [
            'now' => $this->now,
            'user' => $this->beUser->user['uid'],
        ];
        $pages = $this->getQueryBuilderForTable('pages')
            ->select('p.*')
            ->from('pages', 'p')
            ->where('p.crdate = :now AND p.pid=0 AND p.cruser_id= :user')
            ->setParameters($parameters)
            ->execute()
            ->fetchAll();
        if(count($pages) !== 1){
            throw new ExtensionCreationException('Unable to retrieve root page.', 1445527263);
        }
        $backendLayouts = $this->getQueryBuilderForTable('backend_layout')
            ->select('l.*')
            ->from('backend_layout', 'l')
            ->where('l.crdate = :now AND l.cruser_id= :user')
            ->setParameters($parameters)
            ->execute()
            ->fetchAll();
        if(count($backendLayouts) === 0){
            throw new ExtensionCreationException('Unable to retrieve default backend layout.', 1445527264);
        }
        $data['pages'][$pages[0]['uid']] = array(
            'backend_layout' => $backendLayouts[0]['uid'],
            'backend_layout_next_level' => $backendLayouts[0]['uid']
        );

        $this->dataHandler->start($data, array());
        if($this->dataHandler->process_datamap() === FALSE){
            throw new ExtensionCreationException('Unable to update page tree.', 1445527265);
        }
    }

    /**
     * @param string $table
     * @return QueryBuilder
     */
    protected function getQueryBuilderForTable($table)
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    /**
     * @return array
     */
    protected function buildPageTree(){
        return array(
            'pages' => array(
                'NEW_pages_0' => array(
                    'pid' => 0,
                    'tstamp' => $this->now,
                    'editlock' => 1,
                    'crdate' => $this->now,
                    'cruser_id' => $this->beUser->user['uid'],
                    'title' => 'Root',
                    'hidden' => 0,
                    'doktype' => 1,
                    'is_siteroot' => 1,
                    'tsconfig_includes' => 'EXT:' . $this->template->getKey() . '/Configuration/PageTS/main.txt',
                ),
                'NEW_pages_1' => array(
                    'pid' => 'NEW_pages_0',
                    'tstamp' => $this->now,
                    'crdate' => $this->now,
                    'cruser_id' => $this->beUser->user['uid'],
                    'title' => '---- System ----',
                    'hidden' => 0,
                    'doktype' => 199
                ),
                'NEW_pages_2' => array(
                    'pid' => 'NEW_pages_0',
                    'tstamp' => $this->now,
                    'crdate' => $this->now,
                    'cruser_id' => $this->beUser->user['uid'],
                    'title' => '---- Storage ----',
                    'hidden' => 0,
                    'doktype' => 199
                ),
                'NEW_pages_3' => array(
                    'pid' => 'NEW_pages_0',
                    'tstamp' => $this->now,
                    'crdate' => $this->now,
                    'cruser_id' => $this->beUser->user['uid'],
                    'title' => 'Home',
                    'hidden' => 0,
                    'doktype' => 4,
                    'shortcut_mode' => 3
                ),
                'NEW_pages_4' => array(
                    'pid' => 'NEW_pages_1',
                    'tstamp' => $this->now,
                    'crdate' => $this->now,
                    'cruser_id' => $this->beUser->user['uid'],
                    'title' => 'Templates',
                    'hidden' => 0,
                    'doktype' => 254
                )
            ),
            'tt_content' => array(
                'NEW_tt_content_0' => array(
                    'pid' => 'NEW_pages_0',
                    'tstamp' => $this->now,
                    'crdate' => $this->now,
                    'cruser_id' => $this->beUser->user['uid'],
                    'CType' => 'textmedia',
                    'header' => 'Hello world!',
                    'bodytext' => '<p>This is an example</p>'
                )
            ),
            'sys_template' => array(
                'NEW_sys_template_0' => array(
                    'pid' => 'NEW_pages_0',
                    'tstamp' => $this->now,
                    'crdate' => $this->now,
                    'cruser_id' => $this->beUser->user['uid'],
                    'title' => 'Default template',
                    'root' => 1,
                    'include_static_file' => 'EXT:fluid_styled_content/Configuration/TypoScript/,EXT:fluid_styled_content/Configuration/TypoScript/Styling/,EXT:form/Configuration/TypoScript/,EXT:'.$this->template->getKey().'/Configuration/TypoScript'
                )
            ),
            'backend_layout' => array(
                'NEW_backend_layout_0' => array(
                    'pid' => 'NEW_pages_4',
                    'tstamp' => $this->now,
                    'crdate' => $this->now,
                    'cruser_id' => $this->beUser->user['uid'],
                    'title' => 'Default',
                    'icon' => '',
                    'config' => 'backend_layout {
	colCount = 1
	rowCount = 1
	rows {
		1 {
			columns {
				1 {
					name = Main content
					colPos = 0
				}
			}
		}
	}
}
'
                )
            )
        );
    }
}