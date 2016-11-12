<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Thomas Loeffler <loeffler@spooner-web.de>
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
 * Service for notification
 */
class Tx_TerFe2_Service_Notification implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * notifies the solr index queue about ext changes
     *
     * @param int $extensionUid
     */
    public function notifySolrIndexQueue($extensionUid)
    {
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('solr')) {
            /** @var tx_solr_indexqueue_Queue $indexQueue */
            $indexQueue = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_solr_indexqueue_Queue');
            $indexQueue->updateItem('tx_terfe2_domain_model_extension', $extensionUid);
        }
    }

}

?>