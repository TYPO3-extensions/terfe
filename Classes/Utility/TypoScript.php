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
	 * Utilities to manage and convert Typoscript Code
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Utility_TypoScript implements t3lib_Singleton {

		/**
		 * @var tslib_cObj
		 */
		static protected $contentObject;

		/**
		 * @var Tx_Extbase_Configuration_ConfigurationManager
		 */
		static protected $configurationManager;


		/**
		 * Returns unparsed TypoScript setup
		 *
		 * @return array TypoScript setup
		 */
		static public function getSetup() {
			if (empty(self::$configurationManager)) {
				self::initialize();
			}

			$setup = self::$configurationManager->getConfiguration(
				Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
			);

			if (empty($setup['plugin.']['tx_terfe2.'])) {
				return array();
			}

			return $setup['plugin.']['tx_terfe2.'];
		}


		/**
		 * Parse given TypoScript configuration
		 *
		 * @param array $configuration TypoScript configuration
		 * @param boolean $isPlain Is a plain "Fluid like" configuration array
		 * @return array Parsed configuration
		 */
		static public function parse(array $configuration, $isPlain = TRUE) {
			if (empty(self::$contentObject)) {
				self::initialize();
			}

			if ($isPlain) {
				$configuration = self::parsePlainArray($configuration);
			} else {
				$configuration = self::parseTypoScriptArray($configuration);
			}

			return t3lib_div::removeDotsFromTS($configuration);
		}


		/**
		 * Initialize configuration manager and content object
		 *
		 * @return void
		 */
		static protected function initialize() {
			// Load configuration manager
			self::$configurationManager = t3lib_div::makeInstance('Tx_Extbase_Configuration_ConfigurationManager');
			if (TYPO3_MODE == 'BE') {
				$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
				self::$configurationManager->injectObjectManager($objectManager);
				self::$configurationManager->setContentObject(t3lib_div::makeInstance('tslib_cObj'));

				// Load basic Frontend to make tslib_cObj::cObjGetSingle available in Backend
				if (empty($GLOBALS['TSFE'])) {
					$currentPageId = self::getCurrentPageId();

					// Load TimeTrack object
					if (!is_object($GLOBALS['TT'])) {
						$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_timeTrack');
						$GLOBALS['TT']->start();
					}

					// Load Frontend
					if (!empty($GLOBALS['TYPO3_CONF_VARS'])) {
						$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $currentPageId, 0);
						$GLOBALS['TSFE']->connectToDB();
						$GLOBALS['TSFE']->initFEuser();
						$GLOBALS['TSFE']->determineId();
						$GLOBALS['TSFE']->initTemplate();
						$GLOBALS['TSFE']->getConfigArray();
					}
				}
			}

			// Get content object
			self::$contentObject = self::$configurationManager->getContentObject();
			if (empty(self::$contentObject)) {
				self::$contentObject = t3lib_div::makeInstance('tslib_cObj');
			}
		}


		/**
		 * Parse plain "Fluid like" TypoScript configuration
		 *
		 * @param array $configuration TypoScript configuration
		 * @param boolean $parseSub Parse child nodes
		 * @return array Parsed configuration
		 */
		static protected function parsePlainArray(array $configuration, $parseSub = TRUE) {
			$typoScriptArray = array();

			foreach ($configuration as $key => $value) {
				if (is_array($value)) {
					// Get TypoScript like configuration array
					if (!empty($value['_typoScriptNodeValue'])) {
						$typoScriptArray[$key] = $value['_typoScriptNodeValue'];
						unset($value['_typoScriptNodeValue']);
						$typoScriptArray[$key . '.'] = self::parsePlainArray($value, FALSE);

						// Parse TypoScript object
						if ($parseSub) {
							$typoScriptArray[$key] = self::$contentObject->cObjGetSingle(
								$typoScriptArray[$key],
								$typoScriptArray[$key . '.']
							);
							unset($typoScriptArray[$key . '.']);
						}
					} else {
						$typoScriptArray[$key . '.'] = self::parsePlainArray($value, $parseSub);
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
		static protected function parseTypoScriptArray(array $configuration) {
			$typoScriptArray = array();

			foreach ($configuration as $key => $value) {
				$ident = rtrim($key, '.');
				if (is_array($value)) {
					if (!empty($configuration[$ident])) {
						$typoScriptArray[$ident] = self::$contentObject->cObjGetSingle($configuration[$ident], $value);
						unset($configuration[$key]);
					} else {
						$typoScriptArray[$key] = self::parseTypoScriptArray($value);
					}
				} else if (is_string($value) && $key == $ident) {
					$typoScriptArray[$key] = $value;
				}
			}

			return $typoScriptArray;
		}


		/**
		 * Returns the UID of current page
		 *
		 * @return integer Current page id, if no page is selected current root page id is returned
		 * @see Tx_Extbase_Configuration_BackendConfigurationManager::getCurrentPageId
		 */
		static protected function getCurrentPageId() {
			$pageId = (int) t3lib_div::_GP('id');
			if (!empty($pageId)) {
				return $pageId;
			}

			// Get current site root
			$rootPages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', 'pages', 'deleted=0 AND hidden=0 AND is_siteroot=1', '', '', '1');
			if (!empty($rootPages[0]['uid'])) {
				return $rootPages[0]['uid'];
			}

			// Get root template
			$rootTemplates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pid', 'sys_template', 'deleted=0 AND hidden=0 AND root=1', '', '', '1');
			if (!empty($rootTemplates[0]['pid'])) {
				return $rootTemplates[0]['pid'];
			}

			return 0;
		}

	}
?>