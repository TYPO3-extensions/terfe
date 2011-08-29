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
	 */
	class Tx_TerFe2_Utility_TypoScript {

		/**
		 * @var tslib_cObj
		 */
		protected static $contentObject;

		/**
		 * @var Tx_Extbase_Configuration_ConfigurationManager
		 */
		protected static $configurationManager;


		/**
		 * Initialize configuration manager and content object
		 *
		 * @return void
		 */
		protected static function initialize() {
				// Get configuration manager
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			self::$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');

				// Simulate Frontend
			if (TYPO3_MODE == 'BE') {
				Tx_Extbase_Utility_FrontendSimulator::simulateFrontendEnvironment();
				if (empty($GLOBALS['TSFE']->sys_page)) {
					$GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
				}
				if (empty($GLOBALS['TT'])) {
					$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_TimeTrackNull');
				}
				self::$configurationManager->setContentObject($GLOBALS['TSFE']->cObj);
			}

				// Get content object
			self::$contentObject = self::$configurationManager->getContentObject();
			if (empty(self::$contentObject)) {
				self::$contentObject = t3lib_div::makeInstance('tslib_cObj');
			}

				// Reset Frontend if modified
			if (TYPO3_MODE == 'BE') {
				Tx_Extbase_Utility_FrontendSimulator::resetFrontendEnvironment();
			}
		}


		/**
		 * Returns unparsed TypoScript setup
		 *
		 * @return array TypoScript setup
		 */
		public static function getSetup() {
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
		public static function parse(array $configuration, $isPlain = TRUE) {
			if (empty(self::$contentObject)) {
				self::initialize();
			}

				// Convert to classic TypoScript array
			if ($isPlain) {
				$configuration = Tx_Extbase_Utility_TypoScript::convertPlainArrayToTypoScriptArray($configuration);
			}

				// Parse configuration
			$configuration = self::parseTypoScriptArray($configuration);
			$configuration = t3lib_div::removeDotsFromTS($configuration);

			return $configuration;
		}


		/**
		 * Parse TypoScript configuration
		 *
		 * @param array $configuration TypoScript configuration
		 * @return array Parsed configuration
		 */
		protected static function parseTypoScriptArray(array $configuration) {
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

	}
?>