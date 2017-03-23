<?php

namespace CDSRC\CdsrcTemplateBuilder\Domain\Repository;


use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository as BaseExtensionRepository;

/**
 * @copyright Copyright (c) 2016 Weekup
 */

class ExtensionRepository extends BaseExtensionRepository
{

    /**
     * @param array $extensionKeys
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByCurrentVersionByExtensionKey($extensionKeys)
    {
        $query = $this->createQuery();
        $logicalOr = [];
        foreach ($extensionKeys as $extensionKey) {
            $logicalOr[] = $query->equals('extensionKey', $extensionKey);
        }

        return $query->matching($query->logicalAnd([
            $query->logicalOr($logicalOr),
            $query->greaterThanOrEqual('reviewState', 0),
            $query->equals('currentVersion', 1),
        ]))->execute();
    }

    public function getRepositoryClassName()
    {
        return BaseExtensionRepository::class;
    }

}