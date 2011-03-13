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
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_ExtensionProvider_SoapProvider extends Tx_TerFe2_ExtensionProvider_AbstractExtensionProvider {

		/**
		 * @var tx_em_Connection_Soap
		 */
		protected $soapConnection;


		/**
		 * Returns all Extension information
		 *
		 * @param integer $lastUpdate Last update of the extension list
		 * @return array Extension information
		 */
		public function getUpdateInfo($lastUpdate) {
			if (empty($this->configuration['updateFunc'])) {
				throw new Exception('No update function (updateFunc) defined for SOAP Extension Provider');
			}

			// Get update information
			$params = array('lastUpdate' => $lastUpdate);
			$dataArray = $this->getSoapResult($this->configuration['updateFunc'], $params);
			if (empty($dataArray)) {
				return array();
			}

			// Generate Extension information
			$updateInfoArray = array();
			foreach ($dataArray as $extData) {
				$extInfo = $this->getExtensionInfo($extData);
				if (!empty($extInfo)) {
					$updateInfoArray[] = $extInfo;
				}
			}

			return $updateInfoArray;
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
			if (empty($this->configuration['getFileFunc'])) {
				throw new Exception('No function (getFileFunc) defined to get files from SOAP Extension Provider');
			}

			$params = array(
				'extKey'        => $extKey,
				'versionString' => $versionString,
				'fileType'      => $fileType,
			);

			// Get URL
			$dataArray = $this->getSoapResult($this->configuration['getFileFunc'], $params);
			if (empty($dataArray['urlToFile'])) {
				return '';
			}

			return (string) $dataArray['urlToFile'];

		}


		/**
		 * Wrapper method for SOAP calls
		 *
		 * @param string $methodName Method name
		 * @param array $params Parameters
		 * @return array Result of the SOAP call
		 */
		protected function getSoapResult($methodName, array $params = array()) {
			// Initialize SOAP connection
			if ($this->soapConnection === NULL) {
				if (!t3lib_extMgm::isLoaded('em')) {
					throw new Exception('System extension "em" must be loaded for SOAP Extension Provider');
				}
				if (empty($this->configuration['wsdlUrl'])) {
					throw new Exception('No wsdlUrl found for SOAP Extension Provider');
				}

				$username = (!empty($this->configuration['username']) ? $this->configuration['username'] : FALSE);
				$password = (!empty($this->configuration['password']) ? $this->configuration['password'] : FALSE);
				$options  = array(
					'wsdl'   => $this->configuration['wsdlUrl'],
					'format' => 'array',
					'soapoptions' => array(
						'trace'      => 1,
						'exceptions' => 0,
					)
				);

				// Load connection
				$this->soapConnection = t3lib_div::makeInstance('tx_em_Connection_Soap');
				$this->soapConnection->init($options, $username, $password);
			}

			// Call given method
			$response = $this->soapConnection->call($methodName, $params);
			if ($response === FALSE) {
				return array();
			}

			return (array) $response;
		}

	}
?>