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
	 * Update extension list task
	 */
	class Tx_TerFe2_Task_UpdateExtensionListTask extends Tx_TerFe2_Task_AbstractTask {

		/**
		 * @var string
		 */
		public $providerName = 'extensionmanager';

		/**
		 * @var boolean
		 */
		public $createExtensions = FALSE;

		/**
		 * @var Tx_TerFe2_Provider_ProviderManager
		 */
		protected $providerManager;

		/**
		 * @var Tx_TerFe2_Object_ObjectBuilder
		 */
		protected $objectBuilder;

		/**
		 * @var Tx_Extbase_Persistence_Manager
		 */
		protected $persistenceManager;

		/**
		 * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
		 */
		protected $extensionRepository;

		/**
		 * @var Tx_TerFe2_Domain_Repository_AuthorRepository
		 */
		protected $authorRepository;


		/**
		 * Initialize task
		 *
		 * @return void
		 */
		public function initializeTask() {
			$this->providerManager     = $this->objectManager->get('Tx_TerFe2_Provider_ProviderManager');
			$this->objectBuilder       = $this->objectManager->get('Tx_TerFe2_Object_ObjectBuilder');
			$this->persistenceManager  = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
			$this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			$this->authorRepository    = $this->objectManager->get('Tx_TerFe2_Domain_Repository_AuthorRepository');

				// Set registry name to current provider name
			$this->registry->setName(get_class($this) . '_' . $this->providerName);
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
				// Check static setup
			if (empty($this->setup['settings.'])) {
				throw new Exception('Please include static setup "TER Frontend - Default Configuration (ter_fe2)" on root page');
			}

				// Check storage page
			if (!$this->storagePageConfigured()) {
				throw new Exception('Please configure "plugin.tx_terfe2.persistence.storagePid" in TypoScript setup');
			}

				// Get extension structure from provider
			$provider = $this->providerManager->getProvider($this->providerName);
			$extensions = $provider->getExtensions($lastRun, $offset, $count);
			if (empty($extensions)) {
				return FALSE;
			}

				// Build extensions...
			foreach ($extensions as $extensionRow) {
				$this->createOrUpdateExtension($extensionRow);
			}

			return TRUE;
		}


		/**
		 * Create or update an extension
		 *
		 * @param array $extensionRow Extension row
		 * @return void
		 */
		protected function createOrUpdateExtension(array $extensionRow) {
			$modified = FALSE;

			if (empty($extensionRow['ext_key'])) {
				Tx_TerFe2_Utility_Log::addMessage('Extension key was empty for extension "' . $extensionRow['title'] . '"', 'ter_fe2', 2);
				return;
			}

				// Extension
			if ($this->extensionRepository->countByExtKey($extensionRow['ext_key'])) {
				$extension = $this->extensionRepository->findOneByExtKey($extensionRow['ext_key']);
			} else if (!empty($this->createExtensions)) {
				$extension = $this->objectBuilder->create('Tx_TerFe2_Domain_Model_Extension', $extensionRow);
				$extension->setLastUpload(new DateTime());
				$extension->setLastMaintained(new DateTime());
				$modified = TRUE;
			} else {
				Tx_TerFe2_Utility_Log::addMessage('Extension "' . $extensionRow['ext_key'] . '" not found and not created', 'ter_fe2', 2);
				return;
			}

				// Versions
			foreach ($extensionRow['versions'] as $versionRow) {
					// Version already exists, so do nothing here
				if ($this->extensionRepository->countByExtKeyAndVersionNumber($extensionRow['ext_key'], $versionRow['version_number'])) {
					continue;
				}

				$version = $this->objectBuilder->create('Tx_TerFe2_Domain_Model_Version', $versionRow);
				$version->setExtension($extension);
				$version->setExtensionProvider($this->providerName);
				$modified = TRUE;

					// Relations
				foreach ($versionRow['relations'] as $relationRow) {
					$relation = $this->objectBuilder->create('Tx_TerFe2_Domain_Model_Relation', $relationRow);
					$version->addSoftwareRelation($relation);
				}

					// Author
				if (!empty($versionRow['author'])) {
					$authorRow = $versionRow['author'];
					if (!empty($authorRow['email']) && $this->authorRepository->findByEmailAndName($authorRow['email'], $authorRow['name'])->count() == 1) {
						$author = $this->authorRepository->findByEmailAndName($authorRow['email'], $authorRow['name']);
					} else {
						$author = $this->objectBuilder->create('Tx_TerFe2_Domain_Model_Author', $authorRow);
						$this->persistenceManager->getSession()->registerReconstitutedObject($author);
					}
					$version->setAuthor($author);
				}

				$extension->addVersion($version);
			}

				// Persist objects
			if ($modified) {
				$this->persistenceManager->getSession()->registerReconstitutedObject($extension);
				$this->persistenceManager->persistAll();
			}
		}


		/**
		 * Check whether a storage page is configured or not
		 *
		 * @return TRUE if a storage page was found
		 */
		protected function storagePageConfigured() {
			$setup = Tx_TerFe2_Utility_TypoScript::getSetup('config.tx_extbase.persistence');
			$setup = Tx_Extbase_Utility_Arrays::arrayMergeRecursiveOverrule($setup, $this->setup['persistence.'], FALSE, FALSE);
			if (!empty($setup['storagePid'])) {
				return TRUE;
			}
			if (!empty($setup['classes.']['Tx_TerFe2_Domain_Model_Extension.']['newRecordStoragePid'])) {
				return TRUE;
			}
			return FALSE;
		}


		/**
		 * Returns additional information
		 *
		 * @return string
		 */
		public function getAdditionalInformation() {
				// Get title
			$title = ucfirst($this->providerName);
			if (!empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'][$this->providerName]['title'])) {
				$title = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'][$this->providerName]['title'];
				$title = Tx_Extbase_Utility_Localization::translate($title, '');
			}

				// Load registry
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			$registry = $objectManager->get('Tx_TerFe2_Persistence_Registry');
			$registry->setName(get_class($this) . '_' . $this->providerName);

				// Get process information
			$lastRun = (int) $registry->get('lastRun');
			$offset  = (int) $registry->get('offset');

			return ' Provider: ' . $title . ' | Last run: ' . Tx_TerFe2_Utility_Datetime::getDateFromTimestamp($lastRun) . ' | Offset: ' . $offset . ' ';
		}

	}
?>