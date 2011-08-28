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
	class Tx_TerFe2_ExtensionProvider_ExtensionManagerProvider extends Tx_TerFe2_ExtensionProvider_AbstractProvider {

		/**
		 * @var string
		 */
		protected $extensionRootPath = 'fileadmin/ter/';

		/**
		 * @var string
		 */
		protected $extensionListFile = 'typo3temp/extensions.xml.gz';

		/**
		 * @var integer
		 */
		protected $maxMirrorChecks = 2;

		/**
		 * @var string
		 */
		protected $mirrorUrl;

		/**
		 * @var Tx_TerFe2_Domain_Repository_ExtensionManagerCacheEntryRepository
		 */
		protected $cacheEntryRepository;


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

				// Set extension root path
			if (!empty($this->configuration['extensionRootPath'])) {
				$this->extensionRootPath = rtrim($this->configuration['extensionRootPath'], '/ ') . '/';
			}

				// Set extension list file
			if (!empty($this->configuration['extensionListFile'])) {
				$this->extensionListFile = $this->configuration['extensionListFile'];
			}

				// Set maximal mirror check count
			if (!empty($this->configuration['maxMirrorChecks'])) {
				$this->maxMirrorChecks = (int) $this->configuration['maxMirrorChecks'];
			}

				// Get repository for extension manager cache entries
			$this->extensionRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_ExtensionManagerCacheEntryRepository');
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
			$filename = $this->getMirrorFileUrl($filename);
			if (Tx_TerFe2_Utility_Files::isLocalUrl($filename)) {
				$filename = Tx_TerFe2_Utility_Files::getAbsolutePathFromUrl($filename);
			}

				// Check if file exists
			if (!Tx_TerFe2_Utility_Files::fileExists($filename)) {
				throw new Exception('File "' . $filename . '" not found');
			}

				// Get local url from absolute path
			if (Tx_TerFe2_Utility_Files::isAbsolutePath($filename)) {
				return Tx_TerFe2_Utility_Files::getUrlFromAbsolutePath($filename);
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
			$this->generateFileName($extension, $version, $fileType);
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
					'name'                  => $extension['authorname'],
					'email'                 => $extension['authoremail'],
					'company'               => $extension['authorcompany'],
					'username'              => $extension['ownerusername'],
					'repository'            => $extension['repository'],
					'review_state'          => $extension['reviewstate'],
					'file_hash'             => $extension['t3xfilemd5'],
					'is_last_version'       => $extension['lastversion'],
					'last_reviewed_version' => $extension['lastreviewedversion'],
					'relations'             => array(),
				);

					// Dependencies
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
		 * Returns mirror url from local extension manager
		 *
		 * @param integer $repositoryId Id of the repository to fetch mirrors from
		 * @return string Mirror url
		 */
		protected function getMirror($repositoryId = 1) {
				// Get extension manager settings
			$emSettings = array(
				'rep_url'            => '',
				'extMirrors'         => '',
				'selectedRepository' => (int) $repositoryId,
				'selectedMirror'     => 0,
			);
			if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['em'])) {
				$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['em']);
				$emSettings = array_merge($emSettings, $extConf);
			}

			if (!empty($emSettings['rep_url'])) {
					// Force manually added url
				$mirrorUrl = $emSettings['rep_url'];
			} else {
					// Set selected repository to "1" if no mirrors found
				$mirrors = unserialize($emSettings['extMirrors']);
				if (!is_array($mirrors)) {
					if ($emSettings['selectedRepository'] < 1) {
						$emSettings['selectedRepository'] = 1;
					}
				}

					// Get mirrors from repository object
				$repository = t3lib_div::makeInstance('tx_em_Repository', $emSettings['selectedRepository']);
				if ($repository->getMirrorListUrl()) {
					$repositoryUtility = t3lib_div::makeInstance('tx_em_Repository_Utility', $repository);
					$mirrors = $repositoryUtility->getMirrors(TRUE)->getMirrors();
					unset($repositoryUtility);
					if (!is_array($mirrors)) {
						return '';
					}
				}

					// Build url
				$selectedMirror = (!empty($emSettings['selectedMirror']) ? $emSettings['selectedMirror'] : array_rand($mirrors));
				$mirrorUrl = 'http://' . $mirrors[$selectedMirror]['host'] . $mirrors[$selectedMirror]['path'];
			}

			return rtrim($mirrorUrl, '/ ') . '/';
		}


		/**
		 * Generate the url a file on mirror server
		 *
		 * @param string $filename File name to fetch
		 * @return string Url to file on mirror server
		 */
		protected function getMirrorFileUrl($filename) {
			if (empty($filename)) {
				throw new Exception('No filename given to generate url');
			}

				// Get first mirror url
			if (empty($this->mirrorUrl)) {
				$this->mirrorUrl = $this->getMirror();
			}

				// Check mirrors if file exits
			$count = 1;
			while (!Tx_TerFe2_Utility_Files::fileExists($this->mirrorUrl . $filename)) {
				$count++;
				if ($count > $this->maxMirrorChecks) {
					throw new Exception('File "' . $filename . '" could not be found on ' . $this->maxMirrorChecks . ' mirrors, break');
					break;
				}
				$this->mirrorUrl = $this->getMirror();
			}

			return $this->mirrorUrl . $filename;
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
			$filename = $this->getMirrorFileUrl($filename);
			if (Tx_TerFe2_Utility_Files::isLocalUrl($filename)) {
				$filename = Tx_TerFe2_Utility_Files::getAbsolutePathFromUrl($filename);
				$content = t3lib_div::getURL($filename);
			} else {
				$content = t3lib_div::getURL($filename, 0, array(TYPO3_user_agent));
			}
			if (empty($content)) {
				throw new Exception('Can not fetch file "' . $filename . '"');
			}

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