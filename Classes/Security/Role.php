<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Helmut Hummel <helmut.hummel@typo3.org>
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
 * Service to handle security roles
 */
class Tx_TerFe2_Security_Role implements t3lib_Singleton
{

    /**
     * @var Tx_Extbase_Configuration_ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
     * @return void
     */
    public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
    }

    /**
     * Checks whether FE User is reviewer or not
     *
     * @return boolean
     */
    public function isReviewer()
    {
        if (empty($this->settings['reviewerGroupUid'])) {
            return FALSE;
        }
        return $this->hasRole($this->settings['reviewerGroupUid']);
    }

    /**
     * Checks whether FE User is reviewer or not
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if (empty($this->settings['terAdminGroupUid'])) {
            return FALSE;
        }
        return $this->hasRole($this->settings['terAdminGroupUid']);
    }

    /**
     * Determines whether the currently logged in FE user belongs to the specified usergroup
     *
     * @param string $role The usergroup (either the usergroup uid or its title)
     * @return boolean TRUE if the currently logged in FE user belongs to $role
     */
    public function hasRole($role)
    {
        if (empty($role) || !isset($GLOBALS['TSFE']) || !$GLOBALS['TSFE']->loginUser) {
            return FALSE;
        }
        if (t3lib_div::testInt($role)) {
            return (is_array($GLOBALS['TSFE']->fe_user->groupData['uid']) && in_array($role, $GLOBALS['TSFE']->fe_user->groupData['uid']));
        } else {
            return (is_array($GLOBALS['TSFE']->fe_user->groupData['title']) && in_array($role, $GLOBALS['TSFE']->fe_user->groupData['title']));
        }
    }
}
