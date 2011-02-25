<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
	 *           Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Task_UpdateExtensionListTask extends tx_scheduler_Task {

		/**
		 * @var array
		 */
		protected $settings;

		/**
		 * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
		 */
		protected $extensionRepository;

		/**
		 * @var t3lib_Registry
		 */
		protected $registry;

		/**
		 * @var Tx_Extbase_Persistence_Manager
		 */
		protected $persistenceManager;

		/**
		 * @var Tx_Extbase_Persistence_Session
		 */
		protected $session;


		/**
		 * Public method, usually called by scheduler.
		 *
		 * TODO:
		 *  - Check how to handle author information
		 *  - Add upload comment to version object (requires a connection to ter extension?)
		 *
		 * @return boolean TRUE on success
		 */
		public function execute() {
			// Initialize environment
			$this->initialize();

			// Get all updated Extensions since last run
			$lastRun = $this->registry->get('tx_scheduler', 'lastRun');
			$updateInfoArray = $this->dataProvider->getUpdateInfo(99999); // $lastRun['end']

			// Create new Version and Extension objects
			foreach ($updateInfoArray as $extInfo) {
				// Get new Version if version number has changed
				$version = $this->getVersion($extInfo);
				if ($version === NULL) {
					continue;
				}

				// Load Extension if already exists, else create new one
				$extension = $this->getExtension($extInfo);
				$version->setExtension($extension);
				$extension->addVersion($version);

				// Persist Extension object now to prevent duplicates
				$this->session->registerReconstitutedObject($extension);
				$this->persistenceManager->persistAll();
			}

			return TRUE;
		}


		/**
		 * Initialize environment
		 *
		 * @return void
		 */
		protected function initialize() {
			// Dummy Extension configuration for Dispatcher
			$configuration = array(
				'extensionName' => 'TerFe2',
				'pluginName'    => 'Pi1',
			);

			// Get TypoScript configuration
			$setup = Tx_TerFe2_Utility_TypoScript::getSetup();
			$this->settings = Tx_TerFe2_Utility_TypoScript::parse($setup['settings.'], FALSE);
			$configuration = array_merge($configuration, $setup);

			// Add Extension configuration
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_fe2']);
			$this->settings = Tx_Extbase_Utility_Arrays::arrayMergeRecursiveOverrule($this->settings, $extConf, FALSE, FALSE);

			// Load Dispatcher
			$dispatcher = t3lib_div::makeInstance('Tx_Extbase_Core_Bootstrap');
			$dispatcher->initialize($configuration);

			// Load required objects
			$this->extensionRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			$this->registry            = t3lib_div::makeInstance('t3lib_Registry');
			$this->persistenceManager  = t3lib_div::makeInstance('Tx_Extbase_Persistence_Manager');
			$this->session             = $this->persistenceManager->getSession();

			// Get DataProvider
			// TODO: Allow multiple Data Providers and merge their update informations
			// e.g. JSON, XML / RSS, other DB, ...
			$this->dataProvider = t3lib_div::makeInstance('Tx_TerFe2_DataProvider_FileProvider');
			$this->dataProvider->injectConfiguration($this->settings);
		}


		/**
		 * Create Version object and add to Extension
		 *
		 * @param array $extInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Version New Version object
		 */
		public function getVersion(array $extInfo) {
			// Check if a Version exists with given version number
			if ($this->extensionRepository->countByExtKeyAndVersion($extInfo['extKey'], $extInfo['versionNumber'])) {
				return NULL;
			}

			// Create new Version
			$version = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Version');
			$version->setTitle($extInfo['title']);
			$version->setIcon($extInfo['icon']);
			$version->setDescription($extInfo['description']);
			$version->setFilename($extInfo['filename']);
			$version->setAuthor($extInfo['author']);
			$version->setVersionNumber($extInfo['versionNumber']);
			$version->setVersionString($extInfo['versionString']);
			$version->setUploadDate(new DateTime());
			$version->setUploadComment($extInfo['uploadComment']);
			$version->setDownloadCounter(0);
			$version->setState($extInfo['state']);
			$version->setEmCategory($extInfo['emCategory']);
			$version->setLoadOrder($extInfo['loadOrder']);
			$version->setPriority($extInfo['priority']);
			$version->setShy($extInfo['shy']);
			$version->setInternal($extInfo['internal']);
			$version->setModule($extInfo['module']);
			$version->setDoNotLoadInFe($extInfo['doNotLoadInFe']);
			$version->setUploadfolder($extInfo['uploadfolder']);
			$version->setCreateDirs($extInfo['createDirs']);
			$version->setModifyTables($extInfo['modifyTables']);
			$version->setClearCacheOnLoad($extInfo['clearCacheOnLoad']);
			$version->setLockType($extInfo['lockType']);
			$version->setCglCompliance($extInfo['cglCompliance']);
			$version->setCglComplianceNote($extInfo['cglComplianceNote']);
			$version->setFileHash($extInfo['fileHash']);

			// Add software relations
			foreach ($extInfo['softwareRelation'] as $relationInfo) {
				$softwareRelation = $this->createSoftwareRelation($relationInfo);
				$version->addSoftwareRelation($softwareRelation);
			}

			return $version;
		}


		/**
		 * Create a Software relation
		 *
		 * @param array $relationInfo Relation information
		 * @return Tx_TerFe2_Domain_Model_Relation New releation object
		 */
		protected function createSoftwareRelation(array $relationInfo) {
			// Get version range
			$versionParts = Tx_Extbase_Utility_Arrays::trimExplode('-', $relationInfo['versionRange']);
			$minimumVersion = (!empty($versionParts[0]) ? t3lib_div::int_from_ver($versionParts[0]) : 0);
			$maximumVersion = (!empty($versionParts[1]) ? t3lib_div::int_from_ver($versionParts[1]) : 0);

			// Get Relation object
			$relationObject = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Relation');
			$relationObject->setRelationType($relationInfo['relationType']);
			$relationObject->setRelationKey($relationInfo['relationKey']);
			$relationObject->setSoftwareType($relationInfo['softwareType']);
			$relationObject->setMinimumVersion($minimumVersion);
			$relationObject->setMaximumVersion($maximumVersion);

			return $relationObject;
		}


		/**
		 * Load Extension object if already exists, else create new one
		 *
		 * @param array $extInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Extension New or existing Extension object
		 */
		public function getExtension(array $extInfo) {
			$extension = $this->extensionRepository->findOneByExtKey($extInfo['extKey']);
			if ($extension === NULL) {
				// Create new Extension
				$extension = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Extension');
				$extension->setExtKey($extInfo['extKey']);
				$extension->setForgeLink($extInfo['forgeLink']);
				$extension->setHudsonLink($extInfo['hudsonLink']);
				$extension->setLastUpload(new DateTime());
				$extension->setLastMaintained(new DateTime());
			}

			return $extension;
		}

	}
?>