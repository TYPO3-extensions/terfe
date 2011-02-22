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
		 * Returns completely parsed TypoScript configuration
		 *
		 * @param array $configuration TypoScript configuration
		 * @return array Parsed configuration
		 */
		public function getParsedConfiguration(array $configuration = array()) {
			if (!defined('TYPO3_MODE')) {
				return array();
			}

			// Parse TypoScript configuration for the Frontend
			if (TYPO3_MODE == 'FE') {
				if (empty($this->cObj)) {
					$this->cObj = t3lib_div::makeInstance('tslib_cObj');
				}
				$configuration = $this->parsePlainArray($configuration);
			}

			// Parse TypoScript configuration for the Backend
			if (TYPO3_MODE == 'BE') {
				if (empty($this->cObj)) {
					$this->cObj = $this->getBackendCObj();
				}
				if (empty($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_terfe2.'])) {
					return array();
				}
				$configuration = $this->parseTypoScriptArray($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_terfe2.']);
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
					} else {
						$typoScriptArray[$key] = $this->parseTypoScriptArray($value);
					}
				} else if (is_string($value) && $key == $ident) {
					$typoScriptArray[$key] = $value;
				}
			}

			return $typoScriptArray;
		}


		/**
		 * Create a cObj within Backend
		 *
		 * @param integer $pid Load configuration for this page
		 * @return tslib_cObj New cObj instance
		 */
		protected function getBackendCObj($pid = 1) {
			if (!empty($GLOBALS['TSFE'])) {
				return t3lib_div::makeInstance('tslib_cObj');
			}

			// Load required TimeTrack object
			if (!is_object($GLOBALS['TT'])) {
				$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_timeTrack');
				$GLOBALS['TT']->start();
			}

			// Load basic Frontend and TypoScript configuration
			if (!empty($GLOBALS['TYPO3_CONF_VARS'])) {
				$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], (int) $pid, 0);
				$GLOBALS['TSFE']->connectToDB();
				$GLOBALS['TSFE']->initFEuser();
				$GLOBALS['TSFE']->determineId();
				if (empty($GLOBALS['TCA'])) {
					$GLOBALS['TSFE']->getCompressedTCarray();
				}
				$GLOBALS['TSFE']->initTemplate();
				$GLOBALS['TSFE']->getConfigArray();
			}

			return t3lib_div::makeInstance('tslib_cObj');
		}

	}
?>