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
		 * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
		 */
		protected $extensionRepository;

		/**
		 * @var t3lib_Registry
		 */
		protected $registry;

		/**
		 * @var Tx_TerFe2_Service_FileHandlerService
		 */
		protected $fileHandler;

		/**
		 * @var Tx_Extbase_Persistence_Manager
		 */
		protected $persistenceManager;

		/**
		 * @var Tx_Extbase_Persistence_Mapper_DataMapper
		 */
		protected $dataMapper;

		/**
		 * @var Tx_Extbase_Persistence_Session
		 */
		protected $session;


		/**
		 * Public method, usually called by scheduler.
		 *
		 * TODO:
		 *  - Cache Extensions (?)
		 *  - Prevent duplicate Version and Relations
		 *  - Use a ValueRange object for version requirements in relation objects
		 *  - Check how to handle author information
		 *  - Add upload comment to version object (requires a connection to ter extension?)
		 *
		 * @return boolean TRUE on success
		 */
		public function execute() {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_fe2']);
			$extPath = ($extConf['terDirectory'] ? $extConf['terDirectory'] : 'fileadmin/ter/');

			// Get last run
			$this->registry = t3lib_div::makeInstance('t3lib_Registry');
			$lastRun = $this->registry->get('tx_scheduler', 'lastRun');

			// Get all T3X files in the target directory changed since last run
			$this->fileHandler = t3lib_div::makeInstance('Tx_TerFe2_Service_FileHandlerService');
			$files = $this->fileHandler->getFiles($extPath, 't3x', 99999, TRUE); // $lastRun['end']
			if (empty($files)) {
				return TRUE;
			}

			// Load dispatcher and get data mapper and session
			t3lib_div::makeInstance('Tx_Extbase_Dispatcher');
			$this->persistenceManager = Tx_Extbase_Dispatcher::getPersistenceManager();
			$this->dataMapper = $this->persistenceManager->getBackend()->getDataMapper();
			$this->session = $this->persistenceManager->getSession();

			// Get Extension repository and add extension objects
			$this->extensionRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			foreach ($files as $key => $fileName) {
				// Generate Extension information
				$extInfo = $this->getExtensionInfo($fileName);
				if (empty($extInfo)) {
					// Write to log ?
					continue;
				}

				// Get new Version if version number has changed
				$version = $this->getVersion($extInfo);
				if ($version === NULL) {
					continue;
				}

				// Load Extension if already exists, else create new one
				$extension = $this->getExtension($extInfo);
				$version->setExtension($extension);
				$extension->addVersion($version);

				// Register Extension
				$this->session->registerReconstitutedObject($extension);
			}

			// Persist all Extension objects
			$this->persistenceManager->persistAll();

			return TRUE;
		}


		/**
		 * Generates an array with all Extension information
		 *
		 * @param string $fileName Filename of the relating t3x file
		 * @return array Extension information
		 */
		protected function getExtensionInfo($fileName) {
			if (empty($fileName)) {
				return array();
			}

			// Unpack file and get extension details
			$extContent = $this->fileHandler->unpackT3xFile($fileName);
			unset($extContent['FILES']);

			$extInfo = array(
				'extKey'            => $extContent['extKey'],
				'forgeLink'         => '',
				'hudsonLink'        => '',
				'title'             => $extContent['EM_CONF']['title'],
				'icon'              => $this->fileHandler->getT3xRelPath($extContent['extKey'], $extContent['EM_CONF']['version'], '.gif'),
				'description'       => $extContent['EM_CONF']['description'],
				'filename'          => $this->fileHandler->getT3xRelPath($extContent['extKey'], $extContent['EM_CONF']['version']),
				'author'            => $extContent['EM_CONF']['author'],
				'authorEmail'       => $extContent['EM_CONF']['author_email'],   // Missing in version object
				'authorCompany'     => $extContent['EM_CONF']['author_company'], // Missing in version object
				'versionNumber'     => t3lib_div::int_from_ver($extContent['EM_CONF']['version']),
				'versionString'     => $extContent['EM_CONF']['version'],
				'uploadComment'     => '',
				'state'             => $extContent['EM_CONF']['state'],
				'emCategory'        => $extContent['EM_CONF']['category'],
				'loadOrder'         => $extContent['EM_CONF']['loadOrder'],
				'priority'          => $extContent['EM_CONF']['priority'],
				'shy'               => $extContent['EM_CONF']['shy'],
				'internal'          => $extContent['EM_CONF']['internal'],
				'module'            => $extContent['EM_CONF']['module'],
				'doNotLoadInFe'     => $extContent['EM_CONF']['doNotLoadInFE'],
				'uploadfolder'      => (bool) $extContent['EM_CONF']['uploadfolder'],
				'createDirs'        => $extContent['EM_CONF']['createDirs'],
				'modifyTables'      => $extContent['EM_CONF']['modify_tables'],
				'clearCacheOnLoad'  => (bool) $extContent['EM_CONF']['clearcacheonload'],
				'lockType'          => $extContent['EM_CONF']['lockType'],
				'cglCompliance'     => $extContent['EM_CONF']['CGLcompliance'],
				'cglComplianceNote' => $extContent['EM_CONF']['CGLcompliance_note'],
				'fileHash'          => $this->fileHandler->getFileHash($fileName),
				'softwareRelation'  => array(), // dependencies, conflicts, suggests, TYPO3_version, PHP_version
			);

			// Add TYPO3 version requirement
			if (!empty($extContent['EM_CONF']['PHP_version'])) {
				$extInfo['softwareRelation'][] = array(
					'relationType'  => 'dependancy',
					'relationKey'   => 'typo3',
					'softwareType'  => 'system',
					'versionString' => $extContent['EM_CONF']['TYPO3_version'],
				);
			}

			// Add PHP version requirement
			if (!empty($extContent['EM_CONF']['PHP_version'])) {
				$extInfo['softwareRelation'][] = array(
					'relationType'  => 'dependancy',
					'relationKey'   => 'php',
					'softwareType'  => 'system',
					'versionString' => $extContent['EM_CONF']['PHP_version'],
				);
			}

			return $extInfo;
		}


		/**
		 * Create Version object and add to Extension
		 *
		 * @param array $extInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Version New Version object
		 */
		public function getVersion(array $extInfo) {
			// Check if a Version exists with given version number
			$versionNumber = t3lib_div::int_from_ver($extInfo['versionNumber']);
			if ($this->extensionRepository->countByExtKeyAndVersion($extInfo['extKey'], $versionNumber)) {
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
			// Get version string
			$versionString = $relationInfo['versionString'];
			if (strpos($versionString, '-') !== FALSE) {
				$versionParts = t3lib_div::trimExplode('-', $versionString);
				if (array_search('0.0.0', $versionParts) == 1) {
					$versionString = '>' . $versionParts[0];
				} else {
					$versionString = '<' . $versionParts[1];
				}
			}

			// Get Relation object
			$relationObject = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Relation');
			$relationObject->setRelationType($relationInfo['relationType']);
			$relationObject->setRelationKey($relationInfo['relationKey']);
			$relationObject->setSoftwareType($relationInfo['softwareType']);
			$relationObject->setVersionString($versionString);

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
				$extension->setLastUpdate(new DateTime());
			}

			return $extension;
		}

	}
?>