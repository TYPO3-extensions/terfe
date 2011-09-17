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
		 * @var string
		 */
		protected $extensionRootPath = 'fileadmin/ter/';

		/**
		 * @var string
		 */
		protected $extensionListFile = 'typo3temp/extensions.xml.gz';


		/**
		 * Initialize provider
		 *
		 * @return void
		 */
		public function initializeProvider() {
				// Set extension root path
			if (!empty($this->configuration['extensionRootPath'])) {
				$this->extensionRootPath = rtrim($this->configuration['extensionRootPath'], '/ ') . '/';
			}

				// Set extension list file
			if (!empty($this->configuration['extensionListFile'])) {
				$this->extensionListFile = $this->configuration['extensionListFile'];
			}
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
			$filename = PATH_site . $this->extensionListFile;
			$extensions = $this->getExtensionsFromFile($filename, $lastRun, $offset, $count);
			if (empty($extensions)) {
				return array();
			}

				// Load missing information from ext_emconf.php
			foreach ($extensions as $extensionKey => $extension) {
				foreach ($extension['versions'] as $versionKey => $version) {
					$info = $this->getExtensionInfo($extension['ext_key'], $version['version_string'], $version['file_hash']);
					foreach ($info as $key => $value) {
						if (empty($version[$key])) {
							$extensions[$extensionKey]['versions'][$versionKey][$key] = $value;
						}
					}
				}
			}

			return $extensions;
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
			$filename = PATH_site . $this->extensionRootPath . $filename;

				// Check if file exists
			if (!Tx_TerFe2_Utility_File::fileExists($filename)) {
				throw new Exception('File "' . $filename . '" not found');
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
		 * Parse compressed extension list file and return updated extensions
		 *
		 * @param string $filename File name
		 * @param integer $lastRun Timestamp of last update
		 * @param integer $offset Offset to start with
		 * @param integer $count Extension count to load
		 * @return array All extension information
		 */
		protected function getExtensionsFromFile($filename, $lastRun = 0, $offset = 0, $count = 0) {
			if (empty($filename) || !Tx_TerFe2_Utility_File::fileExists($filename)) {
				throw new Exception('Given extension file does not exist');
			}

			$lastRun = (int) $lastRun;
			$filename = 'compress.zlib://' . $filename;
			$xmlContent = t3lib_div::getURL($filename);
			$xml = new SimpleXMLElement($xmlContent);
			if (empty($xml->extension)) {
				throw new Exception('No extensions found in file');
			}

			$extensions = array();
			$amount = 0;
			$versionCount = 0;
			foreach ($xml->extension as $extension) {
					// Versions
				$versions = array();
				foreach ($extension->version as $version) {
						// Check last update
					$uploadDate = (int) $version->lastuploaddate;
					if ($uploadDate <= $lastRun) {
						continue;
					}
						// Check offset
					$amount++;
					if ($amount < $offset) {
						continue;
					}
						// Check count
					$versionCount++;
					if ($versionCount > $count) {
						break 2;
					}

					$versionString = (string) $version->attributes()->version;
					$versions[$versionString] = array(
						'title'                 => (string) $version->title,
						'description'           => (string) $version->description,
						'version_number'        => t3lib_div::int_from_ver($versionString),
						'version_string'        => $versionString,
						'upload_date'           => $uploadDate,
						'upload_comment'        => (string) $version->uploadcomment,
						'state'                 => (string) $version->state,
						'em_category'           => (string) $version->category,
						'load_order'            => NULL,
						'priority'              => NULL,
						'shy'                   => NULL,
						'internal'              => NULL,
						'do_not_load_in_fe'     => NULL,
						'uploadfolder'          => NULL,
						'clear_cache_on_load'   => NULL,
						'module'                => NULL,
						'create_dirs'           => NULL,
						'modify_tables'         => NULL,
						'lock_type'             => NULL,
						'cgl_compliance'        => NULL,
						'cgl_compliance_note'   => NULL,
						'download_counter'      => (int) $version->downloadcounter,
						'manual'                => NULL,
						'repository'            => NULL,
						'review_state'          => NULL,
						'file_hash'             => (string) $version->t3xfilemd5,
						'frontend_user'         => (string) $version->ownerusername,
						'relations'             => array(),
					);

						// Author
					$versions[$versionString]['author'] = array(
						'name'     => (string) $version->authorname,
						'email'    => (string) $version->authoremail,
						'company'  => (string) $version->authorcompany,
						'username' => (string) $version->ownerusername,
					);

						// Relations
					$dependencies = unserialize((string) $version->dependencies);
					foreach ($dependencies as $dependency) {
						if (empty($dependency['extensionKey'])) {
							continue;
						}
						$versionArray = $this->getVersionByRange($dependency['versionRange']);
						$versions[$versionString]['relations'][] = array(
							'relation_type'   => $dependency['kind'],
							'software_type'   => NULL,
							'relation_key'    => $dependency['extensionKey'],
							'minimum_version' => $versionArray[0],
							'maximum_version' => $versionArray[1],
						);
					}
				}

					// Extension
				if (!empty($versions)) {
					$extensionKey = (string) $extension->attributes()->extensionkey;
					$extensions[$extensionKey] = array(
						'ext_key'   => $extensionKey,
						'downloads' => (int) $extension->downloadcounter,
						'versions'  => $versions,
					);
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

				// Fetch file from extension root path
			$filename = $this->generateFileName($extension, $version, 't3x');
			$filename = PATH_site . $this->extensionRootPath . $filename;
			$content = t3lib_div::getURL($filename);
			if (empty($content)) {
				throw new Exception('Could not fetch file "' . $filename . '"');
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
			unset($emConf['dependencies'], $emConf['conflicts'], $emConf['TYPO3_version'], $emConf['PHP_version']);

				// Remap keys
			$keyMap = array(
				'version'            => 'version_string',
				'category'           => 'em_category',
				'loadOrder'          => 'load_order',
				'doNotLoadInFE'      => 'do_not_load_in_fe',
				'clearcacheonload'   => 'clear_cache_on_load',
				'createDirs'         => 'create_dirs',
				'lockType'           => 'lock_type',
				'CGLcompliance'      => 'cgl_compliance',
				'CGLcompliance_note' => 'cgl_compliance_note',
				'author'             => 'name',
				'author_email'       => 'email',
				'author_company'     => 'company',
			);
			foreach ($emConf as $key => $value) {
				if (!empty($keyMap[$key])) {
					$emConf[$keyMap[$key]] = $value;
					unset($emConf[$key]);
				}
			}

			return $emConf;
		}

	}
?>