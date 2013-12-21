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
		 * @var Tx_TerFe2_Domain_Repository_SearchRepository
		 */
		protected $searchRepository;

		/**
		 * @var boolean
		 */
		protected $showInsecure = TRUE;

		/**
		 * @param Tx_TerFe2_Domain_Repository_SearchRepository $searchRepository
		 * @return void
		 */
		public function injectSearchRepository(Tx_TerFe2_Domain_Repository_SearchRepository $searchRepository) {
			$this->searchRepository = $searchRepository;
		}


		/**
		 * Allow the listing of insecure extensions or not
		 *
		 * @param boolean $showInsecure
		 * @return void
		 */
		public function setShowInsecure($showInsecure) {
			$this->showInsecure = (bool) $showInsecure;
		}



		/**
		 * Returns the showInsecure state
		 *
		 * @return boolean
		 */
		public function getShowInsecure() {
			return (bool) $this->showInsecure;
		}



		/**
		 * Build basis constraint
		 *
		 * @param Tx_Extbase_Persistence_QueryInterface $query
		 * @param Tx_Extbase_Persistence_QOM_ConstraintInterface $constraint
		 * @return Tx_Extbase_Persistence_QueryInterface
		 */
		protected function match(Tx_Extbase_Persistence_QueryInterface $query, Tx_Extbase_Persistence_QOM_ConstraintInterface $constraint) {
			if ($this->showInsecure) {
				$query->matching($constraint);
				return;
			}

			$query->matching($query->logicalAnd(
				$query->greaterThanOrEqual('lastVersion.reviewState', 0),
				$constraint
			));
		}


		/**
		 * Returns all extensions
		 *
		 * @param int $offset
		 * @param int $count
		 * @param array $ordering
		 * @return array|Tx_Extbase_Persistence_QueryResultInterface
		 */
		public function findAll($offset = 0, $count = 0, array $ordering = array()) {
			if (empty($ordering)) {
				$ordering = array('lastVersion.title' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING);
			}
			$query = $this->createQuery($offset, $count, $ordering);
				// Filter empty title
			$this->match($query, $query->logicalNot($query->equals('lastVersion.title', '')));
			return $query->execute();
		}
	
		/**
		 * Returns all objects of this repository
		 *
		 * @return array|Tx_Extbase_Persistence_QueryResultInterface
		 */
		public function findAllAdmin() {
			$result = $this->createQuery()->execute();
			return $result;
		}

		/**
		 * Returns one extension by extension key respecting the reviewstate
		 *
		 * @param $extensionKey
		 * @return object
		 */
		public function findOneByExtKey($extensionKey) {
			$query = $this->createQuery(0, 1);
			$this->match($query, $query->equals('extKey', $extensionKey));
			return $query->execute()->getFirst();
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
			$this->match($query, $query->contains('categories', $category));
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
			$this->match($query, $query->contains('tags', $tag));
			return $query->execute();
		}


		/**
		 * Returns all extensions by an author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author The Author to search for
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findByAuthor(Tx_TerFe2_Domain_Model_Author $author) {
			$uids = $this->searchRepository->findUidsByAuthor($author);

				// Workaround to enable paginate
			$query = $this->createQuery();
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->setOrderings(
				array('extKey' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
			);
			$this->match($query, $query->in('uid', $uids));

			return $query->execute();
		}

		/**
		 * 
		 * @param string $frontendUser
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findByFrontendUser($frontendUser) {
			$query = $this->createQuery();
			$query->setOrderings(
				array('extKey' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
			);
			
			$this->match($query, $query->like('frontendUser', $frontendUser));
			return $query->execute();
		}

		/**
		 * Search extensions by search words and filters
		 *
		 * TODO:
		 * - Use real relevance ordering from uid order
		 *
		 * @param string $needle Search string
		 * @param array $filters Filter extension list
		 * @param array $ordering $ordering Ordering <-> Direction
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		public function findBySearchWordsAndFilters($searchWords = NULL, array $filters = NULL, array $ordering = NULL) {
			$uids = $this->searchRepository->findUidsBySearchWordsAndFilters($searchWords, $filters);

				// Workaround to enable paginate
			$query = $this->createQuery();
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->setOrderings($ordering);
			$this->match($query, $query->in('uid', $uids));

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
			$this->match($query, $query->logicalAnd(
				$query->equals('extKey', $extKey),
				$query->greaterThanOrEqual('lastVersion.versionNumber', (int) $versionNumber)
			));
			return $query->execute()->count();
		}



		/**
		 * Get domain models given by an array of extension keys
		 *
		 * Unknown keys are silently ignored. So check later if you have all the models you need.
		 *
		 * @param array $extKeys
		 * @return Tx_Extbase_Persistence_QueryResultInterface
		 */
		public function findByExtKeys(array $extKeys) {
				// Workaround to enable paginate
			$query = $this->createQuery();
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->matching($query->in('ext_key', $extKeys));

			return $query->execute();
		}

		/**
		 * @param Tx_TerFe2_Domain_Model_Extension $extension
		 * @param string $frontendUser
		 *
		 * @return array|Tx_Extbase_Persistence_QueryResultInterface
		 */
		public function findAllOtherFromFrontendUser(Tx_TerFe2_Domain_Model_Extension $extension, $frontendUser) {
			$query = $this->createQuery();
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$constraints = array(
				$query->equals('frontendUser', $frontendUser),
				$query->logicalNot(
					$query->equals('uid', $extension->getUid())
				),
				$query->greaterThanOrEqual('versions', '1')
			);
			$query->matching($query->logicalAnd($constraints));

			return $query->execute();
		}
	}
?>