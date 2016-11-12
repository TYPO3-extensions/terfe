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
 * Utilities to manage registry content
 */
class Tx_TerFe2_Persistence_Registry extends Tx_TerFe2_Persistence_AbstractPersistence
{

    /**
     * @var \TYPO3\CMS\Core\Registry
     */
    protected $registry;


    /**
     * Load content
     *
     * @return void
     */
    public function load()
    {
        if (!$this->isLoaded()) {
            $this->registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Registry::class);
            $this->content = $this->registry->get($this->getName(), 'content');
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
        if (empty($this->registry)) {
            $this->registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Registry::class);
        }
        $this->registry->set($this->getName(), 'content', $this->content);
    }

}

?>