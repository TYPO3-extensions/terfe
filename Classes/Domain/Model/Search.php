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
 * Search index entry
 */
class Tx_TerFe2_Domain_Model_Search extends Tx_TerFe2_Domain_Model_AbstractEntity
{

    /**
     * The extension key
     * @var string
     */
    protected $extensionKey;

    /**
     * The version title
     * @var string
     */
    protected $title;

    /**
     * The version description
     * @var string
     */
    protected $description;

    /**
     * The authors of the version
     * @var string
     */
    protected $authorList;

    /**
     * The upload comment
     * @var string
     */
    protected $uploadComment;

    /**
     * The version string
     * @var string
     */
    protected $versionString;

    /**
     * The version state
     * @var string
     */
    protected $state;

    /**
     * The extension manager category
     * @var string
     */
    protected $emCategory;

    /**
     * The software relations of the version
     * @var string
     */
    protected $softwareRelationList;

    /**
     * The categories of the extension
     * @var string
     */
    protected $categoryList;

    /**
     * The tags of the extension
     * @var string
     */
    protected $tagList;

    /**
     * The uid of the version
     * @var integer
     */
    protected $versionUid;

    /**
     * The uid of the extension
     * @var integer
     */
    protected $extensionUid;


    /**
     * Setter for extensionKey
     *
     * @param string $extensionKey
     * @return void
     */
    public function setExtensionKey($extensionKey)
    {
        $this->extensionKey = $extensionKey;
    }


    /**
     * Getter for extensionKey
     *
     * @return string
     */
    public function getExtensionKey()
    {
        return $this->extensionKey;
    }


    /**
     * Setter for title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * Getter for title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Setter for description
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


    /**
     * Getter for description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Setter for authorList
     *
     * @param string $authorList
     * @return void
     */
    public function setAuthorList($authorList)
    {
        $this->authorList = $authorList;
    }


    /**
     * Getter for authorList
     *
     * @return string
     */
    public function getAuthorList()
    {
        return $this->authorList;
    }


    /**
     * Setter for uploadComment
     *
     * @param string $uploadComment
     * @return void
     */
    public function setUploadComment($uploadComment)
    {
        $this->uploadComment = $uploadComment;
    }


    /**
     * Getter for uploadComment
     *
     * @return string
     */
    public function getUploadComment()
    {
        return $this->uploadComment;
    }


    /**
     * Setter for versionString
     *
     * @param string $versionString
     * @return void
     */
    public function setVersionString($versionString)
    {
        $this->versionString = $versionString;
    }


    /**
     * Getter for versionString
     *
     * @return string
     */
    public function getVersionString()
    {
        return $this->versionString;
    }


    /**
     * Setter for state
     *
     * @param string $state
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }


    /**
     * Getter for state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }


    /**
     * Setter for emCategory
     *
     * @param string $emCategory
     * @return void
     */
    public function setEmCategory($emCategory)
    {
        $this->emCategory = $emCategory;
    }


    /**
     * Getter for emCategory
     *
     * @return string
     */
    public function getEmCategory()
    {
        return $this->emCategory;
    }


    /**
     * Setter for softwareRelationList
     *
     * @param string $softwareRelationList
     * @return void
     */
    public function setSoftwareRelationList($softwareRelationList)
    {
        $this->softwareRelationList = $softwareRelationList;
    }


    /**
     * Getter for softwareRelationList
     *
     * @return string
     */
    public function getSoftwareRelationList()
    {
        return $this->softwareRelationList;
    }


    /**
     * Setter for categoryList
     *
     * @param string $categoryList
     * @return void
     */
    public function setCategoryList($categoryList)
    {
        $this->categoryList = $categoryList;
    }


    /**
     * Getter for categoryList
     *
     * @return string
     */
    public function getCategoryList()
    {
        return $this->categoryList;
    }


    /**
     * Setter for tagList
     *
     * @param string $tagList
     * @return void
     */
    public function setTagList($tagList)
    {
        $this->tagList = $tagList;
    }


    /**
     * Getter for tagList
     *
     * @return string
     */
    public function getTagList()
    {
        return $this->tagList;
    }


    /**
     * Setter for versionUid
     *
     * @param integer $versionUid
     * @return void
     */
    public function setVersionUid($versionUid)
    {
        $this->versionUid = $versionUid;
    }


    /**
     * Getter for extensionUid
     *
     * @return integer
     */
    public function getVersionUid()
    {
        return $this->versionUid;
    }


    /**
     * Setter for extensionUid
     *
     * @param integer $extensionUid
     * @return void
     */
    public function setExtensionUid($extensionUid)
    {
        $this->extensionUid = $extensionUid;
    }


    /**
     * Getter for extensionUid
     *
     * @return integer
     */
    public function getExtensionUid()
    {
        return $this->extensionUid;
    }

}

?>