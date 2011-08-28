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
	 * An Extension Provider for local Extension Manager
	 */
	class Tx_TerFe2_ExtensionProvider_ExtensionManagerProvider extends Tx_TerFe2_ExtensionProvider_AbstractExtensionProvider {

		/**
		 * @var string
		 */
		protected $mirrorUrl;


		/**
		 * Returns an array with information about all updated Extensions
		 *
		 * @param integer $lastUpdate Last update of the extension list
		 * @return array Update information
		 */
		public function getUpdateInfo($lastUpdate) {
			$extensionPath = (!empty($this->configuration['extensionRootPath']) ? $this->configuration['extensionRootPath'] : 'fileadmin/ter/');
			$files = Tx_TerFe2_Utility_Files::getFiles($extensionPath, 't3x', (int) $lastUpdate, TRUE);
			if (empty($files)) {
				return array();
			}

				// Generate Extension information
			$updateInfoArray = array();
			foreach ($files as $fileName) {
				$extensionInfo = $this->getExtensionInfo($fileName);
				if (!empty($extensionInfo)) {
					$updateInfoArray[] = $extensionInfo;
				}
			}

			return $updateInfoArray;
		}


		/**
		 * Returns the URL to a file
		 *
		 * @param string $fileName File name
		 * @return string URL to file
		 */
		public function getUrlToFile($fileName) {
				// Path: /t/e/ter_fe2
			$fileName = $fileName[0] . '/' . $fileName[1] . '/' . $fileName;

				// Use mirror system from local Extension Manager
			if (!empty($this->configuration['useEmMirrors'])) {
				$mirrorUrl = $this->getMirrorUrl();
				if (!empty($mirrorUrl)) {
					$urlToFile = $mirrorUrl . $fileName;
					if (Tx_TerFe2_Utility_Files::isLocalUrl($urlToFile)) {
						$urlToFile = Tx_TerFe2_Utility_Files::getLocalUrlPath($urlToFile);
					}
					if (Tx_TerFe2_Utility_Files::fileExists($urlToFile)) {
						return $urlToFile;
					}
				}
			}

				// Get path to local Extension directory
			$extensionRootPath = 'fileadmin/ter/';
			if (!empty($this->configuration['extensionRootPath'])) {
				$extensionRootPath = rtrim($this->configuration['extensionRootPath'], '/ ') . '/';
			}

				// Check if file exists and is readable
			if (Tx_TerFe2_Utility_Files::fileExists(PATH_site . $extensionRootPath . $fileName)) {
				return t3lib_div::locationHeaderUrl($extensionRootPath . $fileName);
			}

			return '';
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
			$extensionContent = Tx_TerFe2_Utility_Files::unpackT3xFile($fileName);
			unset($extensionContent['FILES']);

				// Map fields
			$extensionInfo = $extensionContent['EM_CONF'];
			$extensionInfo['extKey']            = $extensionContent['extKey'];
			$extensionInfo['forgeLink']         = '';
			$extensionInfo['hudsonLink']        = '';
			$extensionInfo['uploadComment']     = '';
			$extensionInfo['fileName']          = $fileName;
			$extensionInfo['versionString']     = $extensionInfo['version'];
			$extensionInfo['authorName']        = $extensionInfo['author'];
			$extensionInfo['authorEmail']       = $extensionInfo['author_email'];
			$extensionInfo['authorCompany']     = $extensionInfo['author_company'];
			$extensionInfo['authorForgeLink']   = '';
			$extensionInfo['emCategory']        = $extensionInfo['category'];
			$extensionInfo['doNotLoadInFe']     = $extensionInfo['doNotLoadInFE'];
			$extensionInfo['modifyTables']      = $extensionInfo['modify_tables'];
			$extensionInfo['clearCacheOnLoad']  = $extensionInfo['clearcacheonload'];
			$extensionInfo['cglCompliance']     = $extensionInfo['CGLcompliance'];
			$extensionInfo['cglComplianceNote'] = $extensionInfo['CGLcompliance_note'];

				// Add TYPO3 version requirement
			if (!empty($extensionInfo['TYPO3_version'])) {
				$extensionInfo['relations'][] = array(
					'relationType'  => 'dependancy',
					'relationKey'   => 'typo3',
					'softwareType'  => 'system',
					'versionRange'  => $extensionInfo['TYPO3_version'],
				);
			}

				// Add PHP version requirement
			if (!empty($extensionInfo['PHP_version'])) {
				$extensionInfo['relations'][] = array(
					'relationType'  => 'dependancy',
					'relationKey'   => 'php',
					'softwareType'  => 'system',
					'versionRange'  => $extensionInfo['PHP_version'],
				);
			}

			return parent::getExtensionInfo($extensionInfo);
		}


		/**
		 * Returns mirror URL from local Extension Manager
		 *
		 * @return string Mirror URL
		 */
		protected function getMirrorUrl() {
			if (empty($this->mirrorUrl) && t3lib_extMgm::isLoaded('em')) {

					// Get EM settings
				$emSettings = array(
					'rep_url'            => '',
					'extMirrors'         => '',
					'selectedRepository' => 1,
					'selectedMirror'     => 0,
				);
				if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['em'])) {
					$emSettings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['em']);
				}

				if (!empty($emSettings['rep_url'])) {
						// Force manually added URL
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

						// Build URL
					$selectedMirror = (!empty($emSettings['selectedMirror']) ? $emSettings['selectedMirror'] : array_rand($mirrors));
					$mirrorUrl      = 'http://' . $mirrors[$selectedMirror]['host'] . $mirrors[$selectedMirror]['path'];
				}

				$this->mirrorUrl = rtrim($mirrorUrl, '/ ') . '/';
			}

			return $this->mirrorUrl;
		}

	}
?>