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
 * Update version details
 */
class Tx_TerFe2_Task_UpdateDetailsTask extends Tx_TerFe2_Task_AbstractTask
{

    /**
     * @var boolean
     */
    public $recalculateDownloads = TRUE;

    /**
     * @var Tx_TerFe2_Domain_Repository_VersionRepository
     */
    protected $versionRepository;

    /**
     * @var Tx_TerFe2_Provider_ProviderManager
     */
    protected $providerManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;


    /**
     * Initialize task
     *
     * @return void
     */
    public function initializeTask()
    {
        $this->providerManager = $this->objectManager->get('Tx_TerFe2_Provider_ProviderManager');
        $this->objectBuilder = $this->objectManager->get('Tx_TerFe2_Object_ObjectBuilder');
        $this->persistenceManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
        $this->versionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
    }


    /**
     * Execute the task
     *
     * @param integer $lastRun Timestamp of the last run
     * @param integer $offset Starting point
     * @param integer $count Element count to process at once
     * @return boolean TRUE on success
     */
    protected function executeTask($lastRun, $offset, $count)
    {
        $versions = $this->versionRepository->findAll($offset, $count);
        if ($versions->count() === 0) {
            return FALSE;
        }

        foreach ($versions as $version) {
            if ($version instanceof Tx_TerFe2_Domain_Model_Version) {
                $provider = $version->getExtensionProvider();
                $persist = FALSE;

                $attributes = array();
                if (!empty($provider)) {
                    $attributes = $this->providerManager->getProvider($provider)->getVersionDetails($version);
                }

                if (!empty($attributes)) {
                    $version = $this->objectBuilder->update($version, $attributes);
                    $persist = TRUE;
                }

                if (!empty($this->recalculateDownloads) and $version->getExtension() instanceof Tx_TerFe2_Domain_Model_Extension) {
                    $version->getExtension()->recalculateDownloads();
                    $persist = TRUE;
                }

                if ($persist) {
                    $this->persistenceManager->persistAll();
                }
            }
        }

        return TRUE;
    }

}

?>