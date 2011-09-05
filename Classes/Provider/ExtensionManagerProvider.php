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
	 * Extension provider for local extension manager
	 */
	class Tx_TerFe2_Provider_ExtensionManagerProvider extends Tx_TerFe2_Provider_AbstractProvider {

		/**
		 * @var integer
		 */
		protected $repositoryId = 1;

		/**
		 * @var Tx_TerFe2_Domain_Repository_ExtensionManagerCacheEntryRepository
		 */
		protected $extensionRepository;

		/**
		 * @var Tx_TerFe2_Service_Mirror
		 */
		protected $mirrorService;



		/**
		 * Initialize provider
		 *
		 * @return void
		 */
		public function initializeProvider() {
				// Check if extension manager is loaded
			if (!t3lib_extMgm::isLoaded('em')) {
				throw new Exception('Requierd system extension "em" is not loaded');
			}

				// Set repository id
			if (!empty($this->configuration['repositoryId'])) {
				$this->repositoryId = (int) $this->configuration['repositoryId'];
			}

				// Get repository for extension manager cache entries
			$this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionManagerCacheEntryRepository');

				// Get mirror service
			$this->mirrorService = $this->objectManager->get('Tx_TerFe2_Service_Mirror');
			$this->mirrorService->setRepositoryId($this->repositoryId);
		}


		/**
		 * Returns all extensions since last run
		 *
		 * @param integer $lastRun Timestamp of last update
		 * @param integer $offset Offset to start with
		 * @param integer $count Extension count to load
		 * @return array Extension rows
		 */
		public function getExtensions($lastRun, $offset, $count) {
				// Get extension list
			$extensions = $this->extensionRepository->findLastUpdated($lastRun, $offset, $count);
			if (empty($extensions)) {
				return array();
			}

				// Load missing information from ext_emconf.php
			foreach ($extensions as $extensionKey => $extension) {
				$info = $this->getExtensionInfo($extension['extkey'], $extension['version'], $extension['t3xfilemd5']);
				foreach ($info as $key => $value) {
					if (empty($extension[$key])) {
						$extensions[$extensionKey][$key] = $value;
					}
				}
			}

			return $this->buildExtensionStructure($extensions);
		}


		/**
		 * Returns the url to an extension related file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @param string $fileType File type
		 * @return string Url to file
		 */
		public function getFileUrl(Tx_TerFe2_Domain_Model_Version $version, $fileType) {
			$filename = $this->getFileName($version, $fileType);

				// Get filename on mirror server
			$filename = $this->mirrorService->getUrlToFile($filename);
			if (Tx_TerFe2_Utility_File::isLocalUrl($filename)) {
				$filename = Tx_TerFe2_Utility_File::getAbsolutePathFromUrl($filename);
			}

				// Check if file exists
			if (!Tx_TerFe2_Utility_File::fileExists($filename)) {
				throw new Exception('File "' . $filename . '" not found');
			}

				// Get local url from absolute path
			if (Tx_TerFe2_Utility_File::isAbsolutePath($filename)) {
				return Tx_TerFe2_Utility_File::getUrlFromAbsolutePath($filename);
			}

			return $filename;
		}


		/**
		 * Returns name of an extension related file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @param string $fileType File type
		 * @return string File name
		 */
		public function getFileName(Tx_TerFe2_Domain_Model_Version $version, $fileType) {
			$extension = $version->getExtension()->getExtKey();
			$version = $version->getVersionString();
			return $this->generateFileName($extension, $version, $fileType);
		}


		/**
		 * Generates the name of an extension related file
		 *
		 * @param string $extension Extension key
		 * @param string $version Version string
		 * @param string $fileType File type
		 * @return string File name
		 */
		protected function generateFileName($extension, $version, $fileType) {
			if (empty($extension) || empty($version) || empty($fileType)) {
				return '';
			}
			$extension = strtolower($extension);
			$fileType = strtolower(trim($fileType, '. '));
			return $extension[0] . '/' . $extension[1] . '/' . $extension . '_' . $version . '.' . $fileType;
		}


		/**
		 * Build multidimensional array of extension information
		 *
		 * @param array $extensionRows Extension rows from repository
		 * @return array All extension information
		 */
		protected function buildExtensionStructure(array $extensionRows) {
			if (empty($extensionRows)) {
				return array();
			}

			$states = tx_em_Tools::getDefaultState(NULL);
			$states = array_flip($states);
			$categories = tx_em_Tools::getDefaultCategory(NULL);
			$categories = array_flip($categories);

			$extensions = array();
			foreach ($extensionRows as $extension) {
					// Extension
				$extensions[$extension['extkey']]['ext_key'] = $extension['extkey'];
				$extensions[$extension['extkey']]['downloads'] = (int) $extension['alldownloadcounter'];

					// Versions
				$versionString = $extension['version'];
				$extensions[$extension['extkey']]['versions'][$versionString] = array(
					'title'                 => $extension['title'],
					'description'           => $extension['description'],
					'version_number'        => $extension['intversion'],
					'version_string'        => $versionString,
					'upload_date'           => $extension['lastuploaddate'],
					'upload_comment'        => $extension['uploadcomment'],
					'state'                 => $states[(int) $extension['state']],
					'em_category'           => $categories[(int) $extension['category']],
					'load_order'            => $extension['loadOrder'],
					'priority'              => $extension['priority'],
					'shy'                   => $extension['shy'],
					'internal'              => $extension['internal'],
					'do_not_load_in_fe'     => $extension['doNotLoadInFE'],
					'uploadfolder'          => $extension['uploadfolder'],
					'clear_cache_on_load'   => $extension['clearcacheonload'],
					'module'                => $extension['module'],
					'create_dirs'           => $extension['createDirs'],
					'modify_tables'         => $extension['modify_tables'],
					'lock_type'             => $extension['lockType'],
					'cgl_compliance'        => $extension['CGLcompliance'],
					'cgl_compliance_note'   => $extension['CGLcompliance_note'],
					'download_counter'      => (int) $extension['downloadcounter'],
					'manual'                => NULL,
					'repository'            => $extension['repository'],
					'review_state'          => $extension['reviewstate'],
					'file_hash'             => $extension['t3xfilemd5'],
					'relations'             => array(),
				);

					// Author
				$extensions[$extension['extkey']]['versions'][$versionString]['author'] = array(
					'name'     => $extension['authorname'],
					'email'    => $extension['authoremail'],
					'company'  => $extension['authorcompany'],
					'username' => $extension['ownerusername'],
				);

					// Relations
				$dependencies = unserialize($extension['dependencies']);
				foreach ($dependencies as $relationType => $relations) {
					foreach ($relations as $relationKey => $versionRange) {
						$version = $this->getVersionByRange($versionRange);
						$extensions[$extension['extkey']]['versions'][$versionString]['relations'][] = array(
							'relation_type'   => $relationType,
							'software_type'   => '',
							'relation_key'    => $relationKey,
							'minimum_version' => $version[0],
							'maximum_version' => $version[1],
						);
					}
				}
			}

			return $extensions;
		}


		/**
		 * Returns the content of an ext_emconf.php file
		 *
		 * @param string $extension Extension key
		 * @param string $version Version string
		 * @return array Extension info array
		 */
		protected function getExtensionInfo($extension, $version, $fileHash) {
			if (empty($extension) || empty($version) || empty($fileHash)) {
				throw new Exception('Extension key, version and file hash are required to get extension info');
			}

				// Fetch file from server
			$filename = $this->generateFileName($extension, $version, 't3x');
			$content = $this->mirrorService->getFile($filename);

				// Check file hash
			if ($fileHash !== md5($content)) {
					// TODO: Log the file hash missmatch
				return array();
			}

				// Get EM_CONF array
			$extension = Tx_TerFe2_Utility_Archive::decompressT3xStream($content);
			$emConf = array();
			if (!empty($extension['EM_CONF'])) {
				$emConf = $extension['EM_CONF'];
			}
			unset($extension);

			return $emConf;
		}

	}
?>