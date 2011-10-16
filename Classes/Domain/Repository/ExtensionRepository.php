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
		 * @param string $offset Offset to start with
		 * @param string $count Count of result
		 * @param string $ordering Ordering <-> Direction
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findAll($offset = 0, $count = 0, array $ordering = array()) {
			if (empty($ordering)) {
				$ordering = array('lastVersion.title' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING);
			}
			$query = $this->createQuery($offset, $count, $ordering);
				// Filter empty title
			$query->matching($query->logicalNot($query->equals('lastVersion.title', '')));
			return $query->execute();
		}


		/**
		 * Returns new and updated extensions
		 *
		 * @param integer $latestCount Count of extensions
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findLatest($latestCount = 0) {
			$ordering = array('lastVersion.uploadDate' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING);
			return $this->findAll(0, $latestCount, $ordering);
		}


		/**
		 * Returns top rated extensions
		 *
		 * @param integer $topRatedCount Count of extensions
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findTopRated($topRatedCount = 0) {
			$ordering = array('lastVersion.experience.rating' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING);
			return $this->findAll(0, $topRatedCount, $ordering);
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
			return $query->execute();
		}


		/**
		 * Returns all extensions by an author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author The Author to search for
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findByAuthor(Tx_TerFe2_Domain_Model_Author $author) {
			$statement = '
				SELECT extension FROM tx_terfe2_domain_model_version
				WHERE tx_terfe2_domain_model_version.author = ' . (int) $author->getUid() . '
			';

				// Workaround while extbase doesn't support JOIN
			$query = $this->createQuery();
			$query->getQuerySettings()->setReturnRawQueryResult(TRUE);
			$query->statement($statement, array());
			$rows = $query->execute();
			unset($query);

				// Workaround to enable paginate
			$uids = array();
			foreach ($rows as $row) {
				$uids[] = (int) $row['extension'];
			}
			$query = $this->createQuery();
			$query->setOrderings(
				array('extKey' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
			);
			$query->matching($query->in('uid', $uids));

			return $query->execute();
		}


		/**
		 * Search extensions by search words and filters
		 *
		 * TODO:
		 * - Implement filters
		 * - Use real relevance ordering from uid order
		 *
		 * @param string $needle Search string
		 * @param array $filters Filter extension list
		 * @param array $ordering $ordering Ordering <-> Direction
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findBySearchWordsAndFilters($searchWords = NULL, array $filters = NULL, array $ordering = NULL) {
			$statement = '
				SELECT DISTINCT extension, MATCH (extension_key,title,description,upload_comment,software_relation_list) AGAINST (?) AS score
				FROM tx_terfe2_domain_model_version
				WHERE MATCH (extension_key,title,description,upload_comment,software_relation_list) AGAINST (?)
				GROUP BY extension
				ORDER BY score DESC
			';

				// Workaround while extbase doesn't support MATCH
			$query = $this->createQuery();
			$query->getQuerySettings()->setReturnRawQueryResult(TRUE);
			$query->statement($statement, array($searchWords, $searchWords));
			$rows = $query->execute();
			unset($query);

				// Workaround to enable paginate
			$uids = array();
			foreach ($rows as $row) {
				$uids[] = (int) $row['extension'];
			}
			$query = $this->createQuery();
			$query->setOrderings($ordering);
			$query->matching($query->in('uid', $uids));

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