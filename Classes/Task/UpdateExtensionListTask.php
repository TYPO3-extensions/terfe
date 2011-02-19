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

	require_once(PATH_typo3conf.'ext/ter_fe2/Classes/Service/FileHandlerService.php');

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
		 * @var Tx_Extbase_Persistence_Mapper_DataMapper
		 */
		protected $dataMapper;


		/**
		 * Public method, usually called by scheduler.
		 *
		 * @return boolean TRUE on success
		 */
		public function execute() {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_fe2']);
			$extPath = ($extConf['terDirectory'] ? $extConf['terDirectory'] : 'fileadmin/ter/');

			t3lib_div::makeInstance('Tx_Extbase_Dispatcher');

			// Get last run
			$this->registry = t3lib_div::makeInstance('t3lib_Registry');
			$lastRun = $this->registry->get('tx_scheduler', 'lastRun');

			// Get all T3X files in the target directory changed since last run
			$this->fileHandler = t3lib_div::makeInstance('Tx_TerFe2_Service_FileHandlerService');
			$files = $this->fileHandler->getFiles($extPath, 't3x', 99999, TRUE);
			if (empty($files)) {
				return TRUE;
			}

			// Get Extension repository and add extension objects
			$this->extensionRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			foreach ($files as $key => $fileName) {
				// Generate Extension information
				$extInfo = $this->getExtensionInfo($fileName);
				unset($files[$key]);

				// Load Extension object if already exists, else create new one
				$extension = $this->extensionRepository->findOneByExtKey($extInfo['extKey']);
				if ($extension === NULL) {
					$extension = $this->createExtensionObject($extInfo);
					$this->extensionRepository->add($extension);
				}

				// Create Version object and add to Extension
				if ($extension !== NULL) {
					$version = $this->createVersionObject($extension, $extInfo);
					$extension->addVersion($version);
					$this->extensionRepository->update($extension);
				}
			}

			// Finally persist Extension objects
			$this->dataMapper = Tx_Extbase_Dispatcher::getPersistenceManager();
			$this->dataMapper->persistAll();

			return TRUE;
		}


		/**
		 * Generates an array with all Extension information
		 *
		 * @param string $fileName Filename of the relating T3X file
		 * @return array Extension information
		 */
		protected function getExtensionInfo($fileName) {
			if (empty($fileName)) {
				return array();
			}

			// Unpack files and get extension details
			$extContent = $this->fileHandler->unpackT3xFile($fileName);
			unset($extContent['FILES']);

			$extInfo = array(
				'extKey'            => $extContent['extKey'],
				'forgeLink'         => '',
				'hudsonLink'        => '',
				'title'             => $extContent['EM_CONF']['title'],
				'icon'              => '',
				'description'       => $extContent['EM_CONF']['description'],
				'filename'          => $fileName,
				'author'            => $extContent['EM_CONF']['author'],
				'authorEmail'       => $extContent['EM_CONF']['author_email'],   // Missing in version object
				'authorCompany'     => $extContent['EM_CONF']['author_company'], // Missing in version object
				'versionNumber'     => $extContent['EM_CONF']['version'],
				'uploadComment'     => '',
				'state'             => $extContent['EM_CONF']['state'],
				'emCategory'        => $extContent['EM_CONF']['category'],
				'loadOrder'         => $extContent['EM_CONF']['loadOrder'],
				'priority'          => $extContent['EM_CONF']['priority'],       // Missing in version object
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
				'categories'        => array(),
				'tags'              => array(),
				'media'             => array(),
				'experience'        => array(),
				'softwareRelation'  => array(), // dependencies, conflicts, suggests, TYPO3_version, PHP_version
			);

			return $extInfo;
		}


		/**
		 * Create an Extension object
		 *
		 * @param array $extInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Extension Extension object
		 */
		protected function createExtensionObject(array $extInfo) {
			if (empty($extInfo)) {
				return NULL;
			}

			$extension = new Tx_TerFe2_Domain_Model_Extension();
			$extension->setExtKey($extInfo['extKey']);
			$extension->setForgeLink($extInfo['forgeLink']);
			$extension->setHudsonLink($extInfo['hudsonLink']);
			$extension->setLastUpdate(new DateTime());

			// Add Category objects
			foreach ($extInfo['categories'] as $category) {
				$extension->addCategory($category);
			}

			// Add Tag objects
			foreach ($extInfo['tags'] as $tag) {
				$extension->addTag($tag);
			}

			return $extension;
		}


		/**
		 * Create an Version object
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension Parent Extension object
		 * @param array $extInfo Extension information
		 * @return Tx_TerFe2_Domain_Model_Version Version object
		 */
		protected function createVersionObject(Tx_TerFe2_Domain_Model_Extension $extension, array $extInfo) {
			if (empty($extInfo)) {
				return NULL;
			}

			$version = new Tx_TerFe2_Domain_Model_Version();
			$version->setTitle($extInfo['title']);
			$version->setIcon($extInfo['icon']);
			$version->setDescription($extInfo['description']);
			$version->setFilename($extInfo['filename']);
			$version->setAuthor($extInfo['author']);
			$version->setVersionNumber($extInfo['versionNumber']);
			$version->setUploadDate(new DateTime());
			$version->setUploadComment($extInfo['uploadComment']);
			$version->setDownloadCounter(0);
			$version->setState($extInfo['state']);
			$version->setEmCategory($extInfo['emCategory']);
			$version->setLoadOrder($extInfo['loadOrder']);
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
			$version->setFileHash($this->fileHandler->getFileHash($extInfo['filename']));

			// Add Media objects
			foreach ($extInfo['media'] as $media) {
				$version->addMedia($media);
			}

			// Add Expirience objects
			foreach ($extInfo['experience'] as $experience) {
				$version->addExperience($experience);
			}

			// Add SoftwareRelation objects
			foreach ($extInfo['softwareRelation'] as $softwareRelation) {
				$version->addSoftwareRelation($softwareRelation);
			}

			// Set Extension object for back reference
			$version->setExtension($extension);

			return $version;
		}

	}
?>