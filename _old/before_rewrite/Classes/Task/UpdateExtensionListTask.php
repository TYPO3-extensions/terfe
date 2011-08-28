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
		 * @var array
		 */
		protected $settings;

		/**
		 * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
		 */
		protected $extensionRepository;

		/**
		 * @var Tx_TerFe2_Domain_Repository_AuthorRepository
		 */
		protected $authorRepository;

		/**
		 * @var Tx_Extbase_Object_ObjectManagerInterface
		 */
		protected $objectManager;

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
		 *  - Add upload comment to version object (get via SOAP connection from ter)
		 *  - Create new Versions only if Extension was already registered via Frontend,
		 *    do not create new Extensions if not
		 *  - Additonal Version Info:
		 *    - Codelines
		 *    - Codebytes
		 *
		 * @return boolean TRUE on success
		 */
		public function execute() {
				// Initialize environment
			$this->initialize();

				// Get all updated Extensions
			$updateInfoArray = $this->getUpdateInfo();
			if (empty($updateInfoArray)) {
				return TRUE;
			}

				// Create new Version and Extension objects
			foreach ($updateInfoArray as $extensionInfo) {
					// Get new Version if version number has changed
				$version = $this->getVersion($extensionInfo);
				if ($version === NULL) {
					continue;
				}

					// Load Author if already exists, else create new one
				$author = $this->getAuthor($extensionInfo);
				$version->setAuthor($author);

					// Load Extension if already exists, else create new one
				$extension = $this->getExtension($extensionInfo);
				$version->setExtension($extension);

					// Add Version to Object Storages
				$author->addVersion($version);
				$extension->addVersion($version);

					// Persist Extension object now to prevent duplicates
				$this->session->registerReconstitutedObject($author);
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
			$setup          = Tx_TerFe2_Utility_TypoScript::getSetup();
			$this->settings = Tx_TerFe2_Utility_TypoScript::parse($setup['settings.'], FALSE);
			$configuration  = array_merge($configuration, $setup);

				// Load Dispatcher
			$dispatcher = t3lib_div::makeInstance('Tx_Extbase_Core_Bootstrap');
			$dispatcher->initialize($configuration);

				// Load required objects
			$this->objectManager       = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			$this->extensionRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			$this->authorRepository    = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_AuthorRepository');
			$this->registry            = t3lib_div::makeInstance('t3lib_Registry');
			$this->persistenceManager  = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
			$this->session             = $this->persistenceManager->getSession();
		}


		/**
		 * Get all updated Extensions since last run from several Extension Providers
		 *
		 * @return array Update information
		 */
		protected function getUpdateInfo() {
			if (empty($this->settings['extensionProviders']) || !is_array($this->settings['extensionProviders'])) {
				throw new Exception('No Extension Providers found to get update info');
			}

			$lastRunInfo = $this->registry->get('tx_scheduler', 'lastRun');
			$lastRunTime = (!empty($lastRunInfo['end']) ? (int) $lastRunInfo['end'] : 0);

				// TODO: Remove testing value
			$lastRunTime = 99999;

				// Load Extension Provider and get update information
			$extensionProvider = $this->objectManager->get('Tx_TerFe2_ExtensionProvider_ExtensionProvider');
			$updateInfoArray   = $extensionProvider->getUpdateInfo($lastRunTime);

			return $updateInfoArray;
		}


		/**
		 * Create Version object and add to Extension
		 *
		 * @param array $extensionInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Version New Version object
		 */
		public function getVersion(array $extensionInfo) {
				// Check if a Version exists with given version number
			if ($this->extensionRepository->countByExtKeyAndVersion($extensionInfo['extKey'], $extensionInfo['versionNumber'])) {
				return NULL;
			}

				// Create new Version
			$version = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Version');
			$version->setTitle(            $extensionInfo['title']);
			$version->setDescription(      $extensionInfo['description']);
			$version->setFileHash(         $extensionInfo['fileHash']);
			$version->setVersionNumber(    $extensionInfo['versionNumber']);
			$version->setVersionString(    $extensionInfo['versionString']);
			$version->setUploadDate(       new DateTime());
			$version->setUploadComment(    $extensionInfo['uploadComment']);
			$version->setDownloadCounter(  0);
			$version->setState(            $extensionInfo['state']);
			$version->setEmCategory(       $extensionInfo['emCategory']);
			$version->setLoadOrder(        $extensionInfo['loadOrder']);
			$version->setPriority(         $extensionInfo['priority']);
			$version->setShy(              $extensionInfo['shy']);
			$version->setInternal(         $extensionInfo['internal']);
			$version->setModule(           $extensionInfo['module']);
			$version->setDoNotLoadInFe(    $extensionInfo['doNotLoadInFe']);
			$version->setUploadfolder(     $extensionInfo['uploadfolder']);
			$version->setCreateDirs(       $extensionInfo['createDirs']);
			$version->setModifyTables(     $extensionInfo['modifyTables']);
			$version->setClearCacheOnLoad( $extensionInfo['clearCacheOnLoad']);
			$version->setLockType(         $extensionInfo['lockType']);
			$version->setCglCompliance(    $extensionInfo['cglCompliance']);
			$version->setCglComplianceNote($extensionInfo['cglComplianceNote']);
			$version->setExtensionProvider($extensionInfo['extensionProvider']);

				// Add software relations
			foreach ($extensionInfo['softwareRelation'] as $relationInfo) {
				$softwareRelation = $this->createSoftwareRelation($relationInfo);
				$version->addSoftwareRelation($softwareRelation);
			}

			return $version;
		}


		/**
		 * Create a Software relation
		 *
		 * @param array $relationInfo Relation information
		 * @return Tx_TerFe2_Domain_Model_Relation New Relation object
		 */
		protected function createSoftwareRelation(array $relationInfo) {
				// Get version range
			$versionParts   = Tx_Extbase_Utility_Arrays::trimExplode('-', $relationInfo['versionRange']);
			$minimumVersion = (!empty($versionParts[0]) ? t3lib_div::int_from_ver($versionParts[0]) : 0);
			$maximumVersion = (!empty($versionParts[1]) ? t3lib_div::int_from_ver($versionParts[1]) : 0);

				// Get Relation object
			$relationObject = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Relation');
			$relationObject->setRelationType(  $relationInfo['relationType']);
			$relationObject->setRelationKey(   $relationInfo['relationKey']);
			$relationObject->setSoftwareType(  $relationInfo['softwareType']);
			$relationObject->setMinimumVersion($minimumVersion);
			$relationObject->setMaximumVersion($maximumVersion);

			return $relationObject;
		}


		/**
		 * Load Extension object if already exists, else create new one
		 *
		 * @param array $extensionInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Extension New or existing Extension object
		 */
		public function getExtension(array $extensionInfo) {
			$extension = $this->extensionRepository->findOneByExtKey($extensionInfo['extKey']);
			if ($extension === NULL) {
					// Create new Extension
				$dateTime = new DateTime();
				$extension = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Extension');
				$extension->setExtKey(        $extensionInfo['extKey']);
				$extension->setForgeLink(     $extensionInfo['forgeLink']);
				$extension->setHudsonLink(    $extensionInfo['hudsonLink']);
				$extension->setLastUpload(    $dateTime);
				$extension->setLastMaintained($dateTime);

					// Add current frontend user uid if logged in
					// TODO: Implement community extension here!
				if (!empty($GLOBALS['TSFE']->fe_user->user['uid'])) {
					$extension->setFrontendUser((int) $GLOBALS['TSFE']->fe_user->user['uid']);
				}
			}

			return $extension;
		}


		/**
		 * Load Author object if already exists, else create new one
		 *
		 * @param array $extensionInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Author New or existing Author object
		 */
		public function getAuthor(array $extensionInfo) {
			$author = $this->authorRepository->findOneByEmail($extensionInfo['authorEmail']);
			if ($author === NULL) {
					// Create new Author
				$author = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Author');
				$author->setName(     $extensionInfo['authorName']);
				$author->setEmail(    $extensionInfo['authorEmail']);
				$author->setCompany(  $extensionInfo['authorCompany']);
				$author->setForgeLink($extensionInfo['authorForgeLink']);
			}

			return $author;
		}

	}
?>