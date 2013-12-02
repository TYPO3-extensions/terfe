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
 * Update search index
 */
class Tx_TerFe2_Task_SearchIndexTask extends Tx_TerFe2_Task_AbstractTask {

	/**
	 * @var Tx_TerFe2_Object_ObjectBuilder
	 */
	protected $objectBuilder;

	/**
	 * @var Tx_Extbase_Persistence_Manager
	 */
	protected $persistenceManager;

	/**
	 * @var Tx_TerFe2_Domain_Repository_VersionRepository
	 */
	protected $versionRepository;

	/**
	 * @var Tx_TerFe2_Domain_Repository_SearchRepository
	 */
	protected $searchRepository;


	/**
	 * Initialize task
	 *
	 * @return void
	 */
	public function initializeTask() {
		$this->objectBuilder      = $this->objectManager->get('Tx_TerFe2_Object_ObjectBuilder');
		$this->persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		$this->versionRepository  = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
		$this->searchRepository   = $this->objectManager->get('Tx_TerFe2_Domain_Repository_SearchRepository');
	}


	/**
	 * Execute the task
	 *
	 * @param integer $lastRun Timestamp of the last run
	 * @param integer $offset Starting point
	 * @param integer $count Element count to process at once
	 * @return boolean TRUE on success
	 */
	protected function executeTask($lastRun, $offset, $count) {
		$versions = $this->versionRepository->findAll($offset, $count);

		foreach ($versions as $version) {
			$extension  = $version->getExtension();
			$author     = $version->getAuthor();
			$relations  = $this->getStorageAttributes($version->getSoftwareRelations(), 'relationKey');
			$categories = $this->getStorageAttributes($extension->getCategories(), 'title');
			$tags       = $this->getStorageAttributes($extension->getTags(), 'title');
			$authorInfo = $author->getName() . ',' . $author->getEmail();

			$attributes = array(
				'extension_key'          => $extension->getExtKey(),
				'title'                  => $version->getTitle(),
				'description'            => $version->getDescription(),
				'author_list'            => $authorInfo,
				'upload_comment'         => $version->getUploadComment(),
				'version_string'         => $version->getVersionString(),
				'state'                  => $version->getState(),
				'em_category'            => $version->getEmCategory(),
				'software_relation_list' => implode(',', $relations),
				'category_list'          => implode(',', $categories),
				'tag_list'               => implode(',', $tags),
				'version_uid'            => $version->getUid(),
				'extension_uid'          => $extension->getUid(),
			);

				// Get existing entry
			$entry = $this->searchRepository->findOneByUid($version->getUid());
			if ($entry instanceof Tx_TerFe2_Domain_Model_Search) {
				$entry = $this->objectBuilder->update($entry, $attributes);
			} else {
				$entry = $this->objectBuilder->create('Tx_TerFe2_Domain_Model_Search', $attributes);
				$this->persistenceManager->getSession()->registerReconstitutedObject($entry);
			}

			$this->persistenceManager->persistAll();
		}

		return TRUE;
	}


	/**
	 * Returns the "$attribute" of each object in a storage
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $storage The storage object
	 * @param string $attribute The attribute name
	 * @return array The resulting array
	 */
	protected function getStorageAttributes(Tx_Extbase_Persistence_ObjectStorage $storage, $attribute = 'uid') {
		$result = array();

		$method = 'get' . ucfirst($attribute);
		foreach ($storage as $object) {
			$result[] = $object->$method();
		}

		return $result;
	}

}
?>