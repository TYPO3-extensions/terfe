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
	 * Utilities to manage SOAP requests
	 */
	class Tx_TerFe2_Utility_Soap {

		/**
		 * @var SoapClient
		 */
		static protected $soapConnection;

		/**
		 * @var SoapHeader
		 */
		static protected $authenticationHeader;


		/**
		 * Load connection
		 *
		 * @param string $wsdlUrl URL of the wsdl
		 * @param string $username Login with this username
		 * @param string $password Login with this password
		 * @return void
		 */
		static public function connect($wsdlUrl, $username = '', $password = '') {
			if (empty($wsdlUrl)) {
				throw new Exception('No valid wsdl URL given');
			}

			if (!class_exists('SoapClient')) {
				throw new Exception('PHP SOAP extension not available');
			}

				// Create connection
			self::$soapConnection = new SoapClient($wsdlUrl, array(
				'trace'      => 1,
				'exceptions' => 0,
			));

				// Get authentication header
			if (!empty($username) && !empty($password)) {
				$headerData = array('username' => $username, 'password' => $password);
				self::$authenticationHeader = new SoapHeader('', 'HeaderLogin', (object) $headerData, TRUE);
			}
		}


		/**
		 * Wrapper method for SOAP calls
		 *
		 * @param string $methodName Method name
		 * @param array $params Parameters
		 * @return array Result of the SOAP call
		 */
		static public function call($methodName, array $params = array()) {
				// Check for existing connection
			if (empty(self::$soapConnection)) {
				throw new Exception('Create SOAP connection first');
			}

				// Call given method
			$response = self::$soapConnection->__soapCall(
				$methodName,
				$params,
				NULL,
				self::$authenticationHeader
			);

				// Check for errors
			if (is_soap_fault($response)) {
				return array();
			}

			return self::convertObjectToArray($response);
		}


		/**
		 * Convert an object to array
		 *
		 * @param object $object Object to convert
		 * @return array Converted object
		 */
		static protected function convertObjectToArray($object) {
			if (is_object($object) || is_array($object)) {
				$object = (array) $object;
				foreach ($object as $key => $value) {
					$object[$key] = self::convertObjectToArray($value);
				}
			}

			return $object;
		}

	}
?>