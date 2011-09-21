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
	 * Service for TER operations
	 */
	class Tx_TerFe2_Service_Ter {

		/**
		 * @var array
		 */
		protected $userData = array();

		/**
		 * @var string
		 */
		protected $wsdlUrl;


		/**
		 * Load TER connection
		 *
		 * @return void
		 */
		public function __construct($wsdlUrl, $username, $password) {
			$this->wsdlUrl = $wsdlUrl;
			$this->userData = array(
				'username' => $username,
				'password' => $password,
			);
		}

		/**
		 * @return SoapClient
		 */
		protected function getSoapService() {
			return Tx_TerFe2_Service_Soap::connect($this->wsdlUrl, '', '', TRUE);
		}


		/**
		 * Check if an extension key is valid
		 *
		 * @param string $extensionKey Extension key
		 * @return boolean TRUE if extension key is valid
		 */
		public function checkExtensionKey($extensionKey, &$error) {
			$result = $this->getSoapService()->checkExtensionKey($this->userData, $extensionKey);

				// if the result is empty
			if (empty($result['resultCode'])) {
				$error = 'result_empty';
				return FALSE;
			}

				// result code invalid 10502 = TX_TER_RESULT_EXTENSIONKEYNOTVALID
			if ($result['resultCode'] === '10502') {
				$error = 'key_invalid';
				return FALSE;
			}
				// key exists 10500 = TX_TER_RESULT_EXTENSIONKEYALREADYEXISTS
			if ($result['resultCode'] === '10500') {
				$error = 'key_exists';
				return FALSE;
			}

				// 10501 = TX_TER_RESULT_EXTENSIONKEYDOESNOTEXIST
			return (!empty($result['resultCode']) && $result['resultCode'] === '10501');
		}


		/**
		 * Register extension
		 *
		 * @param array $extensionData Extension information
		 * @return boolean TRUE if success
		 */
		public function registerExtension(array $extensionData) {
			$result = $this->getSoapService()->registerExtensionKey($this->userData, $extensionData);

				// TODO: Implement a result check
			return TRUE;
		}


		/**
		 * Assign extension key to an other user
		 *
		 * @param string $extensionKey Extension key
		 * @param string $username New username
		 * @param string $error Contains the error
		 * @return boolean TRUE if success
		 */
		public function assignExtensionKey($extensionKey, $username, &$error = '') {
			$result = $this->getSoapService()->modifyExtensionKey($this->userData, array(
				'extensionKey'  => $extensionKey,
				'ownerUsername' => $username,
			));
			if (empty($result['resultCode'])) {
				$error = 'no_result';
				return FALSE;
			}
				// 102 = TX_TER_ERROR_GENERAL_USERNOTFOUND
			if ($result['resultCode'] === '102') {
				$error = 'user_not_found';
				return FALSE;
			}
				// 10000 = TX_TER_RESULT_GENERAL_OK
			return ($result['resultCode'] === '10000');
		}


		/**
		 * Remove an extension kex from system
		 *
		 * @param string $extensionKey Extension key
		 * @return boolean TRUE if success
		 */
		public function deleteExtensionKey($extensionKey) {
			$result = $this->getSoapService()->deleteExtensionKey($this->userData, $extensionKey);
				// 10000 = TX_TER_RESULT_GENERAL_OK
			return (!empty($result['resultCode']) && $result['resultCode'] === '10000');
		}

	}
?>