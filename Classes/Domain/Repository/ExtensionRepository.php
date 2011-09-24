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
	 * Repository for Tx_TerFe2_Domain_Model_Extension
	 */
	class Tx_TerFe2_Domain_Repository_ExtensionRepository extends Tx_TerFe2_Domain_Repository_AbstractRepository {

		/**
		 * Returns all extensions
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findAll() {
			$query = $this->createQuery();
			$query->setOrderings(
				array('lastVersion.title' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
			);
			return $query->execute();
		}


		/**
		 * Returns new and updated extensions
		 *
		 * @param integer $latestCount Count of extensions
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findNewAndUpdated($latestCount) {
			$query = $this->createQuery();
			$query->setLimit((int) $latestCount);
			$query->setOrderings(
				array('lastVersion.uploadDate' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING)
			);
			return $query->execute();
		}


		/**
		 * Returns top rated extensions
		 *
		 * @param integer $topRatedCount Count of extensions
		 * @param boolean $rawResult Return raw data
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findTopRated($topRatedCount, $rawResult = FALSE) {
			$query = $this->createQuery();
			$query->getQuerySettings()->setReturnRawQueryResult($rawResult);
			$query->setLimit((int) $topRatedCount);
			$query->setOrderings(
				array('lastVersion.experience.rating' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
			);
			return $query->execute();
		}


		/**
		 * Returns all extensions in a category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $category The Category to search in
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findByCategory(Tx_TerFe2_Domain_Model_Category $category) {
			$query = $this->createQuery();
			$query->matching($query->contains('categories', $category));
			$query->setOrderings(
				array('lastVersion.title' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
			);
			return $query->execute();
		}


		/**
		 * Returns all extensions with a tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag The Tag to search for
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findByTag(Tx_TerFe2_Domain_Model_Tag $tag) {
			$query = $this->createQuery();
			$query->matching($query->contains('tags', $tag));
			$query->setOrderings(
				array('lastVersion.title' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
			);
			return $query->execute();
		}


		/**
		 * Returns all extensions by an author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author The Author to search for
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findByAuthor(Tx_TerFe2_Domain_Model_Author $author) {
			$query = $this->createQuery();
			$query->matching($query->contains('versions.author', $author));
			$query->setOrderings(
				array('extKey' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
			);
			return $query->execute();
		}


		/**
		 * Returns all extensions limited by offset and count
		 *
		 * @param string $offset Offset to start with
		 * @param string $count Count of results
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findByOffsetAndCount($offset, $count) {
			$query = $this->createQuery();
			$query->setOffset((int) $offset);
			$query->setLimit((int) $count);
			$query->setOrderings(
				array('lastVersion.uploadDate' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING)
			);
			return $query->execute();
		}


		/**
		 * Returns count of extensions with given extKey and versionNumber
		 *
		 * @param string $extKey Extension Key
		 * @param integer $versionNumber Version of the extension
		 * @return integer Result count
		 */
		public function countByExtKeyAndVersionNumber($extKey, $versionNumber) {
			$query = $this->createQuery();
			$query->matching(
				$query->logicalAnd(
					$query->equals('extKey', $extKey),
					$query->greaterThanOrEqual('lastVersion.versionNumber', (int) $versionNumber)
				)
			);
			return $query->execute()->count();
		}

	}
?>