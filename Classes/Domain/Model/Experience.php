<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
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
 * User experience with an extension
 */
class Tx_TerFe2_Domain_Model_Experience extends Tx_TerFe2_Domain_Model_AbstractValueObject
{

    /**
     * Timestamp of the experience
     * @var integer
     * @validate NotEmpty
     */
    protected $dateTime;

    /**
     * Comment
     * @var string
     */
    protected $comment;

    /**
     * User rating
     * @var integer
     */
    protected $rating;

    /**
     * Frontend user
     * @var Tx_Extbase_Domain_Model_FrontendUser
     */
    protected $frontendUser;

    /**
     * Setter for dateTime
     *
     * @param integer $dateTime Timestamp of the experience
     * @return void
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
    }


    /**
     * Getter for dateTime
     *
     * @return integer Timestamp of the experience
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }


    /**
     * Setter for comment
     *
     * @param string $comment Comment
     * @return void
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }


    /**
     * Getter for comment
     *
     * @return string Comment
     */
    public function getComment()
    {
        return $this->comment;
    }


    /**
     * Setter for rating
     *
     * @param integer $rating User rating
     * @return void
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }


    /**
     * Getter for rating
     *
     * @return integer User rating
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Getter for frontendUser
     *
     * @return Tx_Extbase_Persistence_ObjectStorage
     */
    public function getFrontendUser()
    {
        return $this->frontendUser;
    }

    /**
     * Setter for frontendUser
     *
     * @param Tx_Extbase_Persistence_ObjectStorage $frontendUser
     */
    public function setFrontendUser($frontendUser)
    {
        $this->frontendUser = $frontendUser;
    }
}

?>