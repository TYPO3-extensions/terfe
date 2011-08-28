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
	 * A SOAP Extension Provider
	 */
	class Tx_TerFe2_ExtensionProvider_SoapProvider extends Tx_TerFe2_ExtensionProvider_AbstractExtensionProvider {

		/**
		 * Inititalize Provider, create SOAP connection
		 *
		 * @return void
		 */
		public function initialize() {
			if (empty($this->configuration['wsdlUrl'])) {
				throw new Exception('No wsdl URL (wsdlUrl) defined for SOAP Extension Provider');
			}

				// Connect with login
			if (!empty($this->configuration['username']) && !empty($this->configuration['password'])) {
				Tx_TerFe2_Utility_Soap::connect(
					$this->configuration['wsdlUrl'],
					$this->configuration['username'],
					$this->configuration['password']
				);
			} else {
				Tx_TerFe2_Utility_Soap::connect($this->configuration['wsdlUrl']);
			}
		}


		/**
		 * Returns an array with information about all updated Extensions
		 *
		 * @param integer $lastUpdate Last update of the extension list
		 * @return array Update information
		 */
		public function getUpdateInfo($lastUpdate) {
			if (empty($this->configuration['updateFunc'])) {
				throw new Exception('No update function (updateFunc) defined for SOAP Extension Provider');
			}

				// Get update information
			$params    = array('lastUpdate' => $lastUpdate);
			$dataArray = Tx_TerFe2_Utility_Soap::call($this->configuration['updateFunc'], $params);
			if (empty($dataArray)) {
				return array();
			}

				// Generate Extension information
			$updateInfoArray = array();
			foreach ($dataArray as $extensionData) {
				$extensionInfo = $this->getExtensionInfo($extensionData);
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
			if (empty($this->configuration['getFileFunc'])) {
				throw new Exception('No function (getFileFunc) defined to get files from SOAP Extension Provider');
			}

				// Get URL
			$params = array('fileName' => $fileName);
			$dataArray = Tx_TerFe2_Utility_Soap::call($this->configuration['getFileFunc'], $params);
			if (empty($dataArray['url'])) {
				return '';
			}

			return (string) $dataArray['url'];
		}

	}
?>