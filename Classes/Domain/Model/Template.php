<?php
namespace CDSRC\CdsrcTemplateBuilder\Domain\Model;

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

use DateTime;
use SplObjectStorage;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;
use TYPO3\CMS\Extbase\Annotation\Validate;

class Template
{

    /**
     * @var string
     * @Validate("NotEmpty")
     */
    protected $key;

    /**
     * @var string
     * @Validate("NotEmpty")
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     * @Validate("NotEmpty")
     * @Validate("StringLength", options={"maximum":50})
     */
    protected $authorName;

    /**
     * @var string
     * @Validate("EmailAddress")
     */
    protected $authorEmail;

    /**
     * @var string
     * @Validate("StringLength", options={"maximum":50})
     */
    protected $authorCompany;

    /**
     * @var string
     */
    protected $authorCompanyWebsite;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var SplObjectStorage
     */
    protected $dependencies;

    /**
     * @var bool
     */
    protected $useExtendedRealUrlConfigurationFile;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->dependencies = new SplObjectStorage();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Template
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Template
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * @param string $authorName
     * @return Template
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    /**
     * @param string $authorEmail
     * @return Template
     */
    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail = $authorEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorCompany()
    {
        return $this->authorCompany;
    }

    /**
     * @param string $authorCompany
     * @return Template
     */
    public function setAuthorCompany($authorCompany)
    {
        $this->authorCompany = $authorCompany;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorCompanyWebsite()
    {
        return $this->authorCompanyWebsite;
    }

    /**
     * @param string $authorCompanyWebsite
     * @return Template
     */
    public function setAuthorCompanyWebsite($authorCompanyWebsite)
    {
        $this->authorCompanyWebsite = $authorCompanyWebsite;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Template
     */
    public function setKey($key)
    {
        $this->key = trim(strtolower($key));
        if($this->key && !preg_match('/^template_/', $this->key)){
            $this->key = 'template_' . $this->key;
        }
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param Extension $extension
     *
     * @return Template
     */
    public function addDependency(Extension $extension){
        if(!$this->dependencies->contains($extension)) {
            $this->dependencies->attach($extension);
        }
        return $this;
    }

    /**
     * @param Extension $extension
     *
     * @return Template
     */
    public function removeDependency(Extension $extension){
        if($this->dependencies->contains($extension)) {
            $this->dependencies->detach($extension);
        }
        return $this;
    }

    /**
     * @return SplObjectStorage
     */
    public function getDependencies(){
        return $this->dependencies;
    }

    /**
     * @param $extension
     * @return bool
     */
    public function dependsOn($extension){
        var_dump($extension);exit;
        return $this->dependencies->contains($extension);
    }

    /**
     * @return boolean
     */
    public function isUseExtendedRealUrlConfigurationFile()
    {
        return $this->useExtendedRealUrlConfigurationFile;
    }

    /**
     * @param boolean $useExtendedRealUrlConfigurationFile
     *
     * @return Template
     */
    public function setUseExtendedRealUrlConfigurationFile($useExtendedRealUrlConfigurationFile)
    {
        $this->useExtendedRealUrlConfigurationFile = $useExtendedRealUrlConfigurationFile;

        return $this;
    }
}