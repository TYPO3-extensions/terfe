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
	 * Repository for Tx_TerFe2_Domain_Model_Version
	 */
	class Tx_TerFe2_Domain_Repository_VersionRepository extends Tx_TerFe2_Domain_Repository_AbstractRepository {

		/**
		 * Get all versions where media was not created for
		 *
		 * @param integer $offset Offset to start with
		 * @param integer $count Extension count to load
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findForMediaCreation($offset = 0, $count = 0) {
			$query = $this->createQuery($offset, $count);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->matching(
				$query->logicalOr(
					$query->equals('hasZipFile', FALSE),
					$query->equals('hasImages', FALSE)
				)
			);
			return $query->execute();
		}


		/**
		 * Get a version with the given extension and a related version string
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension object
		 * @param string $versionString The version string
		 * @return Tx_TerFe2_Domain_Model_Version Version object
		 */
		public function findOneByExtensionAndVersionString(Tx_TerFe2_Domain_Model_Extension $extension, $versionString) {
			$query = $this->createQuery(0, 1);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->matching(
				$query->logicalAnd(
					$query->equals('extension', $extension),
					$query->equals('versionString', $versionString)
				)
			);
			return $query->execute()->getFirst();
		}


		/**
		 * Get version history
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension object
		 * @param integer $count Count of versions to return
		 * @param boolean $skipLatest Skip latest version
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function getVersionHistory($extension, $count = 0, $skipLatest = TRUE) {
			$ordering = array('uploadDate' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING);
			$query = $this->createQuery(0, $count, $ordering);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);

			if (!empty($skipLatest)) {
				$query->matching(
					$query->logicalAnd(
						$query->greaterThan('reviewState', -1),
						$query->equals('extension', $extension),
						$query->logicalNot(
							$query->equals('uid', (int) $extension->getLastVersion()->getUid())
						)
					)
				);
			} else {
				$query->matching($query->equals('extension', $extension));
			}

			return $query->execute();
		}


		/**
		 * Retuns all versions before the given one
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension object
		 * @param integer $versionNumber Version number
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findAllBelowVersion($extension, $versionNumber) {
			$ordering = array('uploadDate' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING);
			$query = $this->createQuery(0, $count, $ordering);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);

			$query->matching(
				$query->logicalAnd(
					$query->equals('extension', $extension),
					$query->lessThanOrEqual('versionNumber', (int) $versionNumber)
				)
			);

			return $query->execute();
		}


		/**
		 * Get a version with the given extension key and version string
		 *
		 * @param string $extension The extension object
		 * @param string $versionString The version string
		 * @return Tx_TerFe2_Domain_Model_Version Version object
		 */
		public function findOneByExtensionKeyAndVersionString($extensionKey, $versionString) {
			$query = $this->createQuery(0, 1);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->matching(
				$query->logicalAnd(
					$query->equals('extension.extKey', $extensionKey),
					$query->equals('versionString', $versionString)
				)
			);
			return $query->execute()->getFirst();
		}

	}
?>