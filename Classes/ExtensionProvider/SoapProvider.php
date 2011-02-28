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
	 *  A SOAP Extension Provider for the Scheduler Task
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_ExtensionProvider_SoapProvider extends Tx_TerFe2_ExtensionProvider_AbstractExtensionProvider {

		/**
		 * Returns all Extension information for the Scheduler Task
		 *
		 * @param integer $lastUpdate Last update of the extension list
		 * @return array Extension information
		 */
		public function getUpdateInfo($lastUpdate) {

			// TODO: Add SOAP request
			return array();


			// Generate Extension information
			$updateInfoArray = array();
			foreach ($dataArray as $extData) {
				$extInfo = $this->getExtensionInfo($extData);
				if (!empty($extInfo)) {
					$updateInfoArray[] = $extInfo;
					continue;
				}
			}

			return $updateInfoArray;
		}


		/**
		 * Generates an array with all Extension information
		 *
		 * @param array $extData Extension data
		 * @return array Extension information
		 */
		protected function getExtensionInfo(array $extData) {
			$extInfo = array(
				'extKey'            => '',
				'forgeLink'         => '',
				'hudsonLink'        => '',
				'title'             => '',
				'description'       => '',
				'fileHash'          => '',
				'author'            => '',
				'authorEmail'       => '',
				'authorCompany'     => '',
				'versionNumber'     => '',
				'versionString'     => '',
				'uploadComment'     => '',
				'state'             => '',
				'emCategory'        => '',
				'loadOrder'         => '',
				'priority'          => '',
				'shy'               => '',
				'internal'          => '',
				'module'            => '',
				'doNotLoadInFe'     => '',
				'uploadfolder'      => '',
				'createDirs'        => '',
				'modifyTables'      => '',
				'clearCacheOnLoad'  => '',
				'lockType'          => '',
				'cglCompliance'     => '',
				'cglComplianceNote' => '',
				'softwareRelation'  => array(),
			);

			return $extInfo;
		}


		/**
		 * Returns URL to a file via extKey, version and fileType
		 * 
		 * @param string $extKey Extension key
		 * @param string $versionString Version string
		 * @param string $fileType File type
		 * @return string URL to file
		 */
		public function getUrlToFile($extKey, $versionString, $fileType) {

			// TODO: Add SOAP request
			return '';

		}

	}
?>