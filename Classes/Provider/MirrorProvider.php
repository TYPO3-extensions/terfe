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
	 * Extension provider using external mirror servers
	 */
	class Tx_TerFe2_Provider_MirrorProvider extends Tx_TerFe2_Provider_FileProvider {

		/**
		 * @var Tx_TerFe2_Service_Mirror
		 */
		protected $mirrorService;

		/**
		 * @var string
		 */
		protected $fileCachePath = 'typo3temp/tx_terfe2/files/';


		/**
		 * Initialize provider
		 *
		 * @return void
		 */
		public function initializeProvider() {
			parent::initializeProvider();

				// Set local file cache path
			if (!empty($this->configuration['fileCachePath'])) {
				$this->fileCachePath = $this->configuration['fileCachePath'];
			}
			$this->fileCachePath = Tx_TerFe2_Utility_File::getAbsoluteDirectory($this->fileCachePath);

				// Get repository id
			$repositoryId = 1;
			if (!empty($this->configuration['repositoryId'])) {
				$repositoryId = (int) $this->configuration['repositoryId'];
			}

				// Get mirror service
			$this->mirrorService = $this->objectManager->get('Tx_TerFe2_Service_Mirror');
			$this->mirrorService->setRepositoryId($repositoryId);
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
			$localName = '';

				// Get local filename for t3x files
			if (!empty($this->fileCachePath) && $fileType === 't3x') {
				$localName = $this->fileCachePath . basename($filename);
			}

				// Check local cache first
			if (!empty($localName) && $fileType === 't3x' && Tx_TerFe2_Utility_File::fileExists($localName)) {
				return Tx_TerFe2_Utility_File::getUrlFromAbsolutePath($localName);
			}

				// Get filename on mirror server
			$filename = $this->mirrorService->getUrlToFile($filename);
			if (Tx_TerFe2_Utility_File::isLocalUrl($filename)) {
				$filename = Tx_TerFe2_Utility_File::getAbsolutePathFromUrl($filename);
			}

				// Copy file to local cache and return it
			if (!empty($localName)) {
				Tx_TerFe2_Utility_File::copyFile($filename, $localName);
				return Tx_TerFe2_Utility_File::getUrlFromAbsolutePath($localName);
			}

				// Get local url from absolute path
			if (Tx_TerFe2_Utility_File::isAbsolutePath($filename)) {
				return Tx_TerFe2_Utility_File::getUrlFromAbsolutePath($filename);
			}

			return $filename;
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
			$content  = $this->mirrorService->getFile($filename);
			$filesize = strlen($content);

				// Check file hash
			if ($fileHash !== md5($content)) {
					// TODO: Log the file hash missmatch
				return array();
			}

				// Write file to local cache
			if (!empty($this->fileCachePath)) {
				$localName = $this->fileCachePath . basename($filename);
				t3lib_div::writeFile($localName, $content);
			}

				// Get EM_CONF array
			$extension = Tx_TerFe2_Utility_Archive::decompressT3xStream($content);
			$emConf = array();
			if (!empty($extension['EM_CONF'])) {
				$emConf = $extension['EM_CONF'];
			}
			unset($extension);

				// Add file size
			$emConf['t3xfilesize'] = (int) $filesize;

			return $emConf;
		}

	}
?>