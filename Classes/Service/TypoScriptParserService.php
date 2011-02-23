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
	 * Parses TypoScript configuration
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Service_TypoScriptParserService implements t3lib_Singleton {

		/**
		 * @var tslib_cObj
		 */
		protected $cObj;

		/**
		 * @var Tx_Extbase_Configuration_ConfigurationManager
		 */
		protected $configurationManager;


		/**
		 * Contructor for the TypoScript parser
		 *
		 * @return void
		 */
		public function __construct() {
			$this->configurationManager = t3lib_div::makeInstance('Tx_Extbase_Configuration_ConfigurationManager');

			if (TYPO3_MODE == 'BE') {
				$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
				$this->configurationManager->injectObjectManager($objectManager);
				$this->configurationManager->setContentObject(t3lib_div::makeInstance('tslib_cObj'));
			}

			// Get cObj instance
			if (empty($this->cObj)) {
				$this->cObj = $this->configurationManager->getContentObject();
			}
		}


		/**
		 * Returns completely parsed TypoScript configuration
		 *
		 * TODO: cObjGetSingle doesn't work here with 4.5.0 Backend
		 *
		 * @param array $configuration TypoScript configuration
		 * @return array Parsed configuration
		 */
		public function getParsedConfiguration(array $configuration = array()) {
			if (TYPO3_MODE == 'FE') {
				$configuration = $this->parsePlainArray($configuration);
			} else {
				$setup = $this->configurationManager->getConfiguration(
					Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
				);
				$configuration = (!empty($setup['plugin.']['tx_terfe2.']['settings.']) ? $setup['plugin.']['tx_terfe2.']['settings.'] : array());
				$configuration = $this->parseTypoScriptArray($configuration);
			}

			return t3lib_div::removeDotsFromTS($configuration);
		}


		/**
		 * Parse plain "Fluid like" TypoScript configuration
		 *
		 * @param array $configuration TypoScript configuration
		 * @param boolean $parseSub Parse child nodes
		 * @return array Parsed configuration
		 */
		public function parsePlainArray(array $configuration, $parseSub = TRUE) {
			$typoScriptArray = array();

			foreach ($configuration as $key => $value) {
				if (is_array($value)) {
					// Get TypoScript like configuration array
					if (!empty($value['_typoScriptNodeValue'])) {
						$typoScriptArray[$key] = $value['_typoScriptNodeValue'];
						unset($value['_typoScriptNodeValue']);
						$typoScriptArray[$key . '.'] = $this->parsePlainArray($value, FALSE);

						// Parse TypoScript object
						if ($parseSub) {
							$typoScriptArray[$key] = $this->cObj->cObjGetSingle(
								$typoScriptArray[$key],
								$typoScriptArray[$key . '.']
							);
							unset($typoScriptArray[$key . '.']);
						}
					} else {
						$typoScriptArray[$key . '.'] = $this->parsePlainArray($value, $parseSub);
					}
				} else {
					$typoScriptArray[$key] = $value;
				}
			}

			return $typoScriptArray;
		}


		/**
		 * Parse classic TypoScript configuration
		 *
		 * @param array $configuration TypoScript configuration
		 * @return array Parsed configuration
		 */
		public function parseTypoScriptArray(array $configuration) {
			$typoScriptArray = array();

			foreach ($configuration as $key => $value) {
				$ident = rtrim($key, '.');
				if (is_array($value)) {
					if (!empty($configuration[$ident])) {
						$typoScriptArray[$ident] = $this->cObj->cObjGetSingle($configuration[$ident], $value);
						unset($configuration[$key]);
					} else {
						$typoScriptArray[$key] = $this->parseTypoScriptArray($value);
					}
				} else if (is_string($value) && $key == $ident) {
					$typoScriptArray[$key] = $value;
				}
			}

			return $typoScriptArray;
		}

	}
?>