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
	class Tx_TerFe2_Task_UpdateExtensionListTask extends tx_scheduler_Task {

		/**
		 * @var integer
		 */
		public $extensionsPerRun = 10;

		/**
		 * @var string
		 */
		public $providerName = 'extensionmanager';

		/**
		 * @va string
		 */
		public $clearCachePages;

		/**
		 * @var array
		 */
		protected $settings;

		/**
		 * @var Tx_Extbase_Object_ObjectManager
		 */
		protected $objectManager;

		/**
		 * @var Tx_TerFe2_Provider_ProviderManager
		 */
		protected $providerManager;

		/**
		 * @var Tx_TerFe2_Persistence_Registry
		 */
		protected $registry;

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
				// Load object manager
			$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');

				// Load configuration manager and set extension setup,
				// it is required to be loaded in object manager for persistence mapping
			$configurationManager = $this->objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
			$configurationManager->setConfiguration(Tx_TerFe2_Utility_TypoScript::getSetup('plugin.tx_terfe2'));

				// Load provider manager
			$this->providerManager = $this->objectManager->get('Tx_TerFe2_Provider_ProviderManager');

				// Load registry
			$this->registry = $this->objectManager->get('Tx_TerFe2_Persistence_Registry');
			$this->registry->setName(get_class($this) . '_' . $this->providerName);

				// Load object builder
			$this->objectBuilder = $this->objectManager->get('Tx_TerFe2_Object_ObjectBuilder');

				// Load persistence manager
			$this->persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');

				// Load repositories
			$this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			$this->authorRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_AuthorRepository');
		}


		/**
		 * Public method, usually called by scheduler.
		 *
		 * @return boolean TRUE on success
		 */
		public function execute() {
			$this->initializeTask();

				// Get information
			$lastRun = (int) $this->registry->get('lastRun');
			$offset  = (int) $this->registry->get('offset');
			$count   = (int) $this->extensionsPerRun;

				// TODO: Remove testing values
			$lastRun = 1306920788;
			$offset  = 0;

				// Get extension structure from provider
			$provider = $this->providerManager->getProvider($this->providerName);
			$extensions = $provider->getExtensions($lastRun, $offset, $count);

				// Build extensions...
			if (!empty($extensions)) {
				foreach ($extensions as $extensionRow) {
					$this->createOrUpdateExtension($extensionRow);
				}
			}

				// Set new values to registry
			$offset = (!empty($extensions) ? $offset + $count : 0);
			$this->registry->add('lastRun', $GLOBALS['EXEC_TIME']);
			$this->registry->add('offset', $offset);

				// Clear page cache
			if (!empty($extensions) && !empty($this->clearCachePages)) {
				$this->clearPageCache($this->clearCachePages);
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

				// Extension
			if ($this->extensionRepository->countByExtKey($extensionRow['ext_key'])) {
				$extension = $this->extensionRepository->findOneByExtKey($extensionRow['ext_key']);
			} else {
					// TODO: Remove this later, only existing extensions (created in FE) are allowed
				$extension = $this->objectBuilder->create('Tx_TerFe2_Domain_Model_Extension', $extensionRow);
				$extension->setLastUpload(new DateTime());
				$extension->setLastMaintained(new DateTime());
				$modified = TRUE;
			}

				// Versions
			foreach ($extensionRow['versions'] as $versionRow) {
					// Version already exists, so do nothing here
				if ($this->extensionRepository->countByExtKeyAndVersionNumber($extensionRow['ext_key'], $versionRow['version_number'])) {
					continue;
				}

				$version = $this->objectBuilder->create('Tx_TerFe2_Domain_Model_Version', $versionRow);
				$version->setExtension($extension);
				$modified = TRUE;

					// Relations
				foreach ($versionRow['relations'] as $relationRow) {
					$relation = $this->objectBuilder->create('Tx_TerFe2_Domain_Model_Relation', $relationRow);
					$version->addSoftwareRelation($relation);
				}

					// Author
				if (!empty($versionRow['authors'])) {
					$authorRow = reset($versionRow['authors']);
					if ($this->authorRepository->countByEmail($authorRow['email'])) {
						$author = $this->authorRepository->findOneByEmail($authorRow['email']);
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
		 * Clear cache of given pages
		 *
		 * @param string $pages List of page ids
		 * @return void
		 */
		protected function clearPageCache($pages) {
			$pages = t3lib_div::intExplode(',', $pages, TRUE);
			Tx_Extbase_Utility_Cache::clearPageCache($pages);
		}


		/**
		 * Returns the name of selected extension provider
		 *
		 * @return string
		 */
		public function getAdditionalInformation() {
			$title = ucfirst($this->providerName);
			if (!empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'][$this->providerName]['title'])) {
				$title = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'][$this->providerName]['title'];
				$title = Tx_Extbase_Utility_Localization::translate($title);
			}
			return ' ' . $title . ' ';
		}

	}
?>