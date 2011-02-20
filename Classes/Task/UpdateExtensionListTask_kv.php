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
		 * Public method, usually called by scheduler.
		 *
		 * TODO: Fix object storing, cache Extensions, fix version check, add relations
		 * 
		 * @return boolean TRUE on success
		 */
		public function execute() {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_fe2']);
			$extPath = ($extConf['terDirectory'] ? $extConf['terDirectory'] : 'fileadmin/ter/');

			// Get last run
			$this->registry = t3lib_div::makeInstance('t3lib_Registry');
			$lastRun = $this->registry->get('tx_scheduler', 'lastRun'); // $lastRun['end']

			// Get all T3X files in the target directory changed since last run
			$this->fileHandler = t3lib_div::makeInstance('Tx_TerFe2_Service_FileHandlerService');
			$files = $this->fileHandler->getFiles($extPath, 't3x', 99999, TRUE);
			if (empty($files)) {
				return TRUE;
			}

			// Load dispatcher and get data mapper instance
			t3lib_div::makeInstance('Tx_Extbase_Dispatcher');
			$this->persistenceManager = Tx_Extbase_Dispatcher::getPersistenceManager();
			$this->dataMapper = $this->persistenceManager->getBackend()->getDataMapper();

			// Get Extension repository and add extension objects
			$this->extensionRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			foreach ($files as $key => $fileName) {
				// Generate Extension information
				$extInfo = $this->getExtensionInfo($fileName);
				if (empty($extInfo)) {
					// Write to log ?
					continue;
				}

				// Load Extension if already exists, else create new one
				$extension = $this->getExtension($extInfo);

				// Get new Version if version number has changed
				$version = $this->getVersion($extension, $extInfo);
				if ($version !== NULL) {
					$version->setExtension($extension);
					$extension->addVersion($version);
				}
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
				'ext_key'             => $extContent['extKey'],
				'forge_link'          => '',
				'hudson_link'         => '',
				'categories'          => 0,
				'tags'                => 0,
				'versions'            => 0,
				'last_version'        => 0,
				'last_update'         => $GLOBALS['SIM_EXEC_TIME'],
				'title'               => $extContent['EM_CONF']['title'],
				'icon'                => $this->fileHandler->getT3xRelPath($extContent['extKey'], $extContent['EM_CONF']['version'], '.gif'),
				'description'         => $extContent['EM_CONF']['description'],
				'filename'            => $fileName,
				'author'              => $extContent['EM_CONF']['author'],
				'author_email'        => $extContent['EM_CONF']['author_email'],   // Missing in version object
				'author_company'      => $extContent['EM_CONF']['author_company'], // Missing in version object
				'version_number'      => $extContent['EM_CONF']['version'],
				'upload_comment'      => '',
				'upload_date'         => $GLOBALS['SIM_EXEC_TIME'],
				'download_counter'    => 0,
				'state'               => $extContent['EM_CONF']['state'],
				'em_category'         => $extContent['EM_CONF']['category'],
				'load_order'          => $extContent['EM_CONF']['loadOrder'],
				'priority'            => $extContent['EM_CONF']['priority'],
				'shy'                 => $extContent['EM_CONF']['shy'],
				'internal'            => $extContent['EM_CONF']['internal'],
				'module'              => $extContent['EM_CONF']['module'],
				'do_not_load_in_fe'   => $extContent['EM_CONF']['doNotLoadInFE'],
				'uploadfolder'        => (bool) $extContent['EM_CONF']['uploadfolder'],
				'create_dirs'         => $extContent['EM_CONF']['createDirs'],
				'modify_tables'       => $extContent['EM_CONF']['modify_tables'],
				'clear_cache_on_load' => (bool) $extContent['EM_CONF']['clearcacheonload'],
				'lock_type'           => $extContent['EM_CONF']['lockType'],
				'cgl_compliance'      => $extContent['EM_CONF']['CGLcompliance'],
				'cgl_compliance_note' => $extContent['EM_CONF']['CGLcompliance_note'],
				'file_hash'           => $this->fileHandler->getFileHash($fileName),
				'media'               => 0,
				'experience'          => 0,
				'software_relation'   => 0, // dependencies, conflicts, suggests, TYPO3_version, PHP_version
				'extension'           => 0,
			);
/*
			// build relation object from TYPO3_version
			if ($extContent['EM_CONF']['TYPO3_version']) {
				$relationObject = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Relation');
				$relationObject->setRelationType('dependancy');
				$relationObject->setKey('typo3');
				$relationObject->setSoftwareType('system');
				$versionParts = t3lib_div::trimExplode('-', $extContent['EM_CONF']['TYPO3_version']);
				if (sizeof($versionParts) > 1) {
					if (array_search('0.0.0', $versionParts) == 1) {
						$version = '>'.$versionParts[0];
					} else {
						$version = '<'.$versionParts[1];
					}
				} else {
					$version = $versionParts;
				}
				$relationObject->setVersion($version);
				array_push($extInfo['softwareRelation'], $relationObject);
			}

			// build relation object from PHP_version
			if ($extContent['EM_CONF']['PHP_version']) {
				$relationObject = t3lib_div::makeInstance('Tx_TerFe2_Domain_Model_Relation');
				$relationObject->setRelationType('dependancy');
				$relationObject->setKey('php');
				$relationObject->setSoftwareType('system');
				$versionParts = t3lib_div::trimExplode('-', $extContent['EM_CONF']['PHP_version']);
				if (sizeof($versionParts) > 1) {
					if (array_search('0.0.0', $versionParts) == 1) {
						$version = '>'.$versionParts[0];
					} else {
						$version = '<'.$versionParts[1];
					}
				} else {
					$version = $versionParts;
				}
				$relationObject->setVersion($version);
				array_push($extInfo['softwareRelation'], $relationObject);
			}
*/
			return $extInfo;
		}


		/**
		 * Load Extension object if already exists, else create new one
		 *
		 * @param array $extInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Extension New or existing Extension object
		 */
		public function getExtension(array $extInfo) {
			$extension = $this->extensionRepository->findOneByExtKey($extInfo['ext_key']);
			if ($extension === NULL) {
				$extension = $this->createObject('Tx_TerFe2_Domain_Model_Extension', $extInfo);
			}

			return $extension;
		}


		/**
		 * Create Version object and add to Extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension Parent Extension object
		 * @param array $extInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Version New Version object
		 */
		public function getVersion(Tx_TerFe2_Domain_Model_Extension $extension, array $extInfo) {
			$newVersionNumber  = t3lib_div::int_from_ver($extInfo['version_number']);
			$lastVersionNumber = 0;

			// Get last Version number
			$lastVersion = $extension->getLastVersion();
			if ($lastVersion !== NULL) {
				$lastVersionNumber = t3lib_div::int_from_ver($lastVersion->getVersionNumber());
			}

			// Check version number
			if ($lastVersionNumber >= $newVersionNumber) {
				return NULL;
			}

			// Create new Version
			$version = $this->createObject('Tx_TerFe2_Domain_Model_Version', $extInfo);

			// Add software relations
			/*foreach ($extInfo['software_relation'] as $softwareRelation) {
				$version->addSoftwareRelation($softwareRelation);
			}*/

			return $version;
		}


		/**
		 * Create an object of given class and map attributes
		 *
		 * @param string $className The name of the class
		 * @param array  $extInfo Extension information
		 * @return object New instance of given class name
		 */
		protected function createObject($className, array $extInfo) {
			if (empty($className) || empty($extInfo)) {
				return NULL;
			}

			// Map attributes to object
			$this->dataMapper->injectIdentityMap(new Tx_Extbase_Persistence_IdentityMap);
			$objects = $this->dataMapper->map($className, array($extInfo));
			if (empty($objects)) {
				return NULL;
			}

			// Reset uid
			$object = reset($objects);
			$object->_setProperty('uid', NULL);
			$object->_memorizeCleanState();

			return $object;
		}

	}
?>