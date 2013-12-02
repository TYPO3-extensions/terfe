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
 * Extension provider using local files
 */
class Tx_TerFe2_Provider_FileProvider extends Tx_TerFe2_Provider_AbstractProvider {

	/**
	 * @var Tx_TerFe2_Domain_Repository_ExtensionManagerCacheEntryRepository
	 */
	protected $extensionManagerRepository;

	/**
	 * @var string
	 */
	protected $extensionRootPath = 'fileadmin/ter/';


	/**
	 * Initialize provider
	 *
	 * @return void
	 */
	public function initializeProvider() {
			// Check if extension manager is loaded
		if (!t3lib_extMgm::isLoaded('em')) {
			throw new Exception('Required system extension "em" is not loaded');
		}

			// Set extension root path
		if (!empty($this->configuration['extensionRootPath'])) {
			$this->extensionRootPath = $this->configuration['extensionRootPath'];
		}
		$this->extensionRootPath = Tx_TerFe2_Utility_File::getAbsoluteDirectory($this->extensionRootPath);

			// Get repository for extension manager cache entries
		$this->extensionManagerRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionManagerCacheEntryRepository');
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
		$extensions = $this->extensionManagerRepository->findLastUpdated($lastRun, $offset, $count);
		if (empty($extensions)) {
			return array();
		}

			// Load missing information from ext_emconf.php
		foreach ($extensions as $extensionKey => $extension) {
			$info = $this->getExtensionInfo($extension['extkey'], $extension['version'], $extension['t3xfilemd5']);
			if (empty($info) || !is_array($info)) {
				unset($extensions[$extensionKey]);
				continue;
			}
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
		$filename = $this->extensionRootPath . $filename;

			// Check if file exists
		if (!Tx_TerFe2_Utility_File::fileExists($filename)) {
			if ($fileType === 't3x' || $fileType === 'zip') {
				#throw new Exception('File "' . $filename . '" not found');
			}

			Tx_TerFe2_Utility_Log::addMessage('File "' . $filename . '" not found', 'ter_fe2', 2);
			return '';
		}

			// Get local url from absolute path
		return Tx_TerFe2_Utility_File::getUrlFromAbsolutePath($filename);
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
	 * Returns all information about an extension version
	 *
	 * @param Tx_TerFe2_Domain_Model_Version $version Version object
	 * @return array Version details
	 */
	public function getVersionDetails(Tx_TerFe2_Domain_Model_Version $version) {
		$extensionKey = $version->getExtension()->getExtKey();
		$versionString = $version->getVersionString();

		$entry = $this->extensionManagerRepository->findOneByExtKeyAndVersionString($extensionKey, $versionString);
		if (!empty($entry) && is_array($entry)) {
			return $entry;
		}

		return array();
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

			// Fetch file from extension root path
		$filename = $this->generateFileName($extension, $version, 't3x');
		$filename = $this->extensionRootPath . $filename;
		$content  = t3lib_div::getURL($filename);
		$filesize = strlen($content);
		if (empty($content)) {
			Tx_TerFe2_Utility_Log::addMessage('File "' . $filename . '" could not be fetched', 'ter_fe2', 2);
			return array();
		}

			// Check file hash
		if ($fileHash !== md5($content)) {
			Tx_TerFe2_Utility_Log::addMessage('File hash missmatch of file "' . $filename . '"', 'ter_fe2', 2);
			return array();
		}

			// Get EM_CONF array
		$extension = Tx_TerFe2_Utility_Archive::decompressT3xStream($content);
		$emConf = array();
		if (!empty($extension['EM_CONF'])) {
			$emConf = $extension['EM_CONF'];
		}
		unset($extension);
		unset($emConf['dependencies'], $emConf['conflicts'], $emConf['TYPO3_version'], $emConf['PHP_version']);

			// Remap keys
		$keyMap = array(
			'clearCacheOnLoad' => 'clearcacheonload',
			'author'           => 'authorname',
			'author_email'     => 'authoremail',
			'author_company'   => 'authorcompany',
		);
		foreach ($emConf as $key => $value) {
			if (!empty($keyMap[$key])) {
				$emConf[$keyMap[$key]] = $value;
				unset($emConf[$key]);
			}
		}

			// Add file size
		$emConf['t3xfilesize'] = (int) $filesize;

		return $emConf;
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
			$extensions[$extension['extkey']]['frontend_user'] = $extension['ownerusername'];
			$extensions[$extension['extkey']]['flattr_username'] = trim($extension['flattr_username']);

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
				't3x_file_size'         => $extension['t3xfilesize'],
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

}
?>