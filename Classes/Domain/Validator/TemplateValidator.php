<?php
namespace CDSRC\CdsrcTemplateBuilder\Domain\Validator;

/* **********************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Matthias Toscanelli <m.toscanelli@code-source.ch>
 *
 *  All rights reserved
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
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class TemplateValidator extends AbstractValidator
{

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param Template $value
     *
     * @return void
     */
    protected function isValid($value)
    {
        /** @var PackageManager $packageManager */
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        if ($packageManager->isPackageAvailable($value->getKey())) {
            $this->result->forProperty('key')->addError(new Error(
                $this->translateErrorMessage(
                    'error.template.key.exists',
                    'cdsrc_template_builder',
                    array($value->getKey())
                ),
                1452303044
            ));
        }
    }
}