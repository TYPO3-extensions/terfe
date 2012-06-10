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


        protected
            $wsdlUrl,
            $username,
            $password,
            $returnExceptions
        ;

		/**
		 * Setup connection
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

            $this->wsdlUrl = $wsdlUrl;
            $this->username = $username;
            $this->password = $password;
            $this->returnExceptions = $returnExceptions;

            return $this->resetConnection();



		}

        /**
         * creates a new connection
         *
         * A new SoapClient has to be used to prevent unexpected behavior.
         * The bug occured when registering an extension key. The response to the second request to the server (createExtensionKey)
         * just returned pure jibberish and caused an exception.
         * This seems to be a bug in PHP (@see https://bugs.php.net/bug.php?id=42191) and the easiest way to fix it, is
         * to reset the connection.
         * Maybe this bug was fixed in a newer version of PHP (don't know), but it is a real issue on my local PHP
         * installation (5.2.10). So this *might* be reverted later on.
         *
         * @author Christian Zenker <christian.zenker@599media.de>
         */
        public function resetConnection() {
            // Create connection
            $this->soapConnection = new SoapClient($this->wsdlUrl, array(
                'trace'      => 1,
                'exceptions' => (int) $this->returnExceptions,
            ));

            // Get authentication header
            if (!empty($this->username) && !empty($this->password)) {
                $headerData = array('username' => $this->username, 'password' => $this->password);
                $this->authenticationHeader = new SoapHeader('', 'HeaderLogin', (object) $headerData, TRUE);
            }
        }


		/**
		 * Set connection object
		 *
		 * @param SoapClient $soapConnection SOAP connection object
         * @deprecated Christian Zenker: Seems not to be used anywhere on typo3.org and the method is useless if the connection is reset for each call
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
		 * @throws Exception
		 */
		public function __call($methodName, array $params = array()) {

            $this->resetConnection();

//				// Check for existing connection
//			if (empty($this->soapConnection)) {
//				throw new Exception('Create SOAP connection first');
//			}

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