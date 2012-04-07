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
	 * Extension provider for soap requests
	 */
	class Tx_TerFe2_Provider_SoapProvider extends Tx_TerFe2_Provider_AbstractProvider {

		/**
		 * @var string
		 */
		protected $getExtensionsFunc;

		/**
		 * @var string
		 */
		protected $getFileUrlFunc;

		/**
		 * @var string
		 */
		protected $getFileNameFunc;

		/**
		 * @var string
		 */
		protected $getDownloadCountFunc;

		/**
		 * @var Tx_TerFe2_Service_Soap
		 */
		protected $soapService;


		/**
		 * Initialize provider
		 *
		 * @return void
		 */
		public function initializeProvider() {
			if (empty($this->configuration['wsdlUrl'])) {
				throw new Exception('No wsdl url configured');
			}

			$username = (empty($this->configuration['username']) ? $this->configuration['username'] : '');
			$password = (empty($this->configuration['password']) ? $this->configuration['password'] : '');
			$this->soapService = $this->objectManager->get('Tx_TerFe2_Service_Soap');
			$this->soapService->connect($this->configuration['wsdlUrl'], $username, $password);

				// Set getExtensionsFunc
			if (!empty($this->configuration['getExtensionsFunc'])) {
				$this->getExtensionsFunc = $this->configuration['getExtensionsFunc'];
			}

				// Set getFileUrlFunc
			if (!empty($this->configuration['getFileUrlFunc'])) {
				$this->getFileUrlFunc = $this->configuration['getFileUrlFunc'];
			}

				// Set getFileNameFunc
			if (!empty($this->configuration['getFileNameFunc'])) {
				$this->getFileNameFunc = $this->configuration['getFileNameFunc'];
			}

				// Set getDownloadCountFunc
			if (!empty($this->configuration['getDownloadCountFunc'])) {
				$this->getDownloadCountFunc = $this->configuration['getDownloadCountFunc'];
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
			if (empty($this->getExtensionsFunc)) {
				throw new Exception('No configuration for "getExtensionsFunc" found');
			}
			$parameters = array(
				'lastRun' => (int) $lastRun,
				'offset'  => (int) $offset,
				'count'   => (int) $count,
			);
			$result = $this->soapService->__call($this->getExtensionsFunc, $parameters);
			return (!empty($result['extensions']) ? $result['extensions'] : array());
		}


		/**
		 * Returns the url to an extension related file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @param string $fileType File type
		 * @return string Url to file
		 */
		public function getFileUrl(Tx_TerFe2_Domain_Model_Version $version, $fileType) {
			if (empty($this->getFileUrlFunc)) {
				throw new Exception('No configuration for "getFileUrlFunc" found');
			}
			$parameters = array(
				'extension' => (string) $version->getExtension()->getExtKey(),
				'version'   => (string) $version->getVersionString(),
				'fileType'  => (string) $fileType,
			);
			$result = $this->soapService->__call($this->getFileUrlFunc, $parameters);
			if (empty($result['url'])) {
				throw new Exception('Could not get url to file from soap server');
			}
			return (string) $result['url'];
		}


		/**
		 * Returns name of an extension related file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @param string $fileType File type
		 * @return string File name
		 */
		public function getFileName(Tx_TerFe2_Domain_Model_Version $version, $fileType) {
			if (empty($this->getFileNameFunc)) {
				throw new Exception('No configuration for "getFileNameFunc" found');
			}
			$parameters = array(
				'extension' => (string) $version->getExtension()->getExtKey(),
				'version'   => (string) $version->getVersionString(),
				'fileType'  => (string) $fileType,
			);
			$result = $this->soapService->__call($this->getFileNameFunc, $parameters);
			if (empty($result['filename'])) {
				throw new Exception('Could not get filename from soap server');
			}
			return (string) $result['filename'];
		}


		/**
		 * Returns all information about an extension version
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @return array Version details
		 */
		public function getVersionDetails(Tx_TerFe2_Domain_Model_Version $version) {
			if (empty($this->getVersionDetailsFunc)) {
				throw new Exception('No configuration for "getVersionDetailsFunc" found');
			}
			$parameters = array(
				'extension' => (string) $version->getExtension()->getExtKey(),
				'version'   => (string) $version->getVersionString(),
			);
			$result = $this->soapService->__call($this->getVersionDetailsFunc, $parameters);
			if (empty($result) || !is_array($result)) {
				throw new Exception('Could not get version details from soap server');
			}
			return $result;
		}

	}
?>