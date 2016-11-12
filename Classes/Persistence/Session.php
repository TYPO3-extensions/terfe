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
 * Utilities to manage session content
 */
class Tx_TerFe2_Persistence_Session extends Tx_TerFe2_Persistence_AbstractPersistence
{

    /**
     * Load content
     *
     * @return void
     */
    public function load()
    {
        if (empty($GLOBALS['TSFE']->fe_user)) {
            throw new Exception('Could not load session without frontend user');
        }
        if (!$this->isLoaded) {
            $this->content = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->getName());
            $this->setIsLoaded(TRUE);
        }
    }


    /**
     * Save content
     *
     * @return void
     */
    public function save()
    {
        if (empty($GLOBALS['TSFE']->fe_user)) {
            throw new Exception('Could not save session without frontend user');
        }
        $GLOBALS['TSFE']->fe_user->setKey('ses', $this->getName(), $this->content);
        $GLOBALS['TSFE']->storeSessionData();
    }

}

?>