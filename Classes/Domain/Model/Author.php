<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
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
 ******************************************************************/

/**
 * Author of an extension
 */
class Tx_TerFe2_Domain_Model_Author extends Tx_TerFe2_Domain_Model_AbstractEntity
{

    /**
     * Name of the author
     * @var string
     * @validate NotEmpty
     */
    protected $name;

    /**
     * Email address
     * @var string
     */
    protected $email;

    /**
     * Company name
     * @var string
     */
    protected $company;

    /**
     * Link to forge profile
     * @var string
     */
    protected $forgeLink;

    /**
     * Owner username
     * @var string
     */
    protected $username;

    /**
     * versions
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Tx_TerFe2_Domain_Model_Version>
     * @lazy
     */
    protected $versions;

    /**
     * Frontend user
     * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    protected $frontendUser;

    /**
     * Author type
     *
     * 0 = Maintainer (has uploaded)
     * 1 = Project leader
     * 2 = Developer
     * 3 = Support
     *
     * @var integer
     */
    protected $authorType;

    /**
     * Constructor. Initializes all ObjectStorage instances.
     */
    public function __construct()
    {
        $this->versions = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }


    /**
     * Setter for name
     *
     * @param string $name Name of the author
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * Getter for name
     *
     * @return string Name of the author
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Setter for email
     *
     * @param string $email Email address
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    /**
     * Getter for email
     *
     * @return string Email address
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * Setter for company
     *
     * @param string $company Company name
     * @return void
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }


    /**
     * Getter for company
     *
     * @return string Company name
     */
    public function getCompany()
    {
        return $this->company;
    }


    /**
     * Setter for forgeLink
     *
     * @param string $forgeLink Link to forge profile
     * @return void
     */
    public function setForgeLink($forgeLink)
    {
        $this->forgeLink = $forgeLink;
    }


    /**
     * Getter for forgeLink
     *
     * @return string Link to forge profile
     */
    public function getForgeLink()
    {
        return $this->forgeLink;
    }


    /**
     * Setter for username
     *
     * @param string $username Owner username
     * @return void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }


    /**
     * Getter for username
     *
     * @return string Owner username
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * Getter for versions
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<Tx_TerFe2_Domain_Model_Version> versions
     */
    public function getVersions()
    {
        return $this->versions;
    }


    /**
     * Adds a version
     *
     * @param Tx_TerFe2_Domain_Model_Version $version The Version to be added
     * @return void
     */
    public function addVersion(Tx_TerFe2_Domain_Model_Version $version)
    {
        $this->versions->attach($version);
    }


    /**
     * Removes a version
     *
     * @param Tx_TerFe2_Domain_Model_Version $version The Version to be removed
     * @return void
     */
    public function removeVersion(Tx_TerFe2_Domain_Model_Version $version)
    {
        $this->versions->detach($version);
    }

    /**
     * Getter for frontendUser
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }

    /**
     * Setter for frontendUser
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     */
    public function setFrontendUser($frontendUser)
    {
        $this->frontendUser = $frontendUser;
    }

    /**
     * Setter for authorType
     *
     * @param integer $authorType Type of the author
     * @return void
     */
    public function setAuthorType($type)
    {
        $this->authorType = $type;
    }


    /**
     * Getter for authorType
     *
     * @return integer Type of the author
     */
    public function getAuthorType()
    {
        return $this->authorType;
    }

}

?>