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
	 * Service to handle soap requests
	 */
	class Tx_TerFe2_Service_Soap implements t3lib_Singleton {

		/**
		 * @var SoapClient
		 */
		protected $soapConnection;

		/**
		 * @var SoapHeader
		 */
		protected $authenticationHeader;


		/**
		 * Open connection
		 *
		 * @param string $wsdlUrl URL of the wsdl
		 * @param string $username Login with this username
		 * @param string $password Login with this password
		 * @param boolean $returnExceptions Return exception in case of errors
		 * @return SoapClient
		 */
		public function connect($wsdlUrl, $username = '', $password = '', $returnExceptions = FALSE) {
			if (empty($wsdlUrl)) {
				throw new Exception('No valid wsdl URL given');
			}

			if (!class_exists('SoapClient')) {
				throw new Exception('PHP SOAP extension not available');
			}

				// Create connection
			$this->soapConnection = new SoapClient($wsdlUrl, array(
				'trace'      => 1,
				'exceptions' => (int) $returnExceptions,
			));

				// Get authentication header
			if (!empty($username) && !empty($password)) {
				$headerData = array('username' => $username, 'password' => $password);
				$this->authenticationHeader = new SoapHeader('', 'HeaderLogin', (object) $headerData, TRUE);
			}

			return $this->soapConnection;
		}


		/**
		 * Set connection object
		 *
		 * @param SoapClient $soapConnection SOAP connection object
		 * @return void
		 */
		public function setConnection(SoapClient $soapConnection) {
			$this->soapConnection = $soapConnection;
		}


		/**
		 * Returns current connection object
		 *
		 * @return SoapClient
		 */
		public function getConnection() {
			return $this->soapConnection;
		}


		/**
		 * Set authentication header
		 *
		 * @param SoapHeader $soapHeader SOAP header
		 * @return void
		 */
		public function setAuthenticationHeader(SoapHeader $authenticationHeader) {
			$this->authenticationHeader;
		}


		/**
		 * Returns current authentication header
		 *
		 * @return SoapHeader
		 */
		public function getAuthenticationHeader() {
			return $this->authenticationHeader;
		}


		/**
		 * Wrapper method for SOAP calls
		 *
		 * @param string $methodName Method name
		 * @param array $params Parameters
		 * @return array Result of the SOAP call
		 */
		public function call($methodName, array $params = array()) {
				// Check for existing connection
			if (empty($this->soapConnection)) {
				throw new Exception('Create SOAP connection first');
			}

				// Call given method
			$response = $this->soapConnection->__soapCall(
				$methodName,
				$params,
				NULL,
				$this->authenticationHeader
			);

				// Check for errors
			if (is_soap_fault($response)) {
				throw new Exception('Could not call function "' . $methodName . '" on soap server');
			}

			return $this->convertObjectToArray($response);
		}


		/**
		 * Convert an object to array
		 *
		 * @param object $object Object to convert
		 * @return array Converted object
		 */
		protected function convertObjectToArray($object) {
			if (is_object($object) || is_array($object)) {
				$object = (array) $object;
				foreach ($object as $key => $value) {
					$object[$key] = $this->convertObjectToArray($value);
				}
			}

			return $object;
		}


		/**
		 * Close connection
		 *
		 * @return void
		 */
		public function disconnect() {
			unset($this->soapConnection, $this->authenticationHeader);
		}

	}
?>