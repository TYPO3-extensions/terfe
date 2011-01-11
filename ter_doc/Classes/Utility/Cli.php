<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Susanne Moog <s.moog@neusta.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
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
 * ************************************************************* */

/**
 * The address controller for the Address package
 *
 * @version $Id: $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Tx_TerDoc_Utility_Cli {

	/**
	 * This method is used to log messages on the console.
	 *
	 * $param mixed $message: the message to be outputted on the console
	 * @return void
	 */
	public static function log($message = '') {
		if (is_array($message) || is_object($message)) {
			print_r($message);
		} elseif (is_bool($message) || $message === NULL) {
			var_dump($message);
		} else {
			print $message . chr(10);
		}
	}

	/**
	 * Makes sure the directory path ends with a trailling slash "/"
	 *
	 * $param mixed $path: the path to be sanitzed.
	 * @return void
	 */
	public static function sanitizeDirectoryPath($path = '') {
		if (substr($path, -1, 1) != '/') {
			$path .= '/';
		}
		return $path;
	}

	/**
	 * This method is used to return the configuration in Cli mode
	 *
	 * @return array
	 */
	public static function getSettings() {
		$defaultSettings = $settings = array();

		// instantiate parser
		$parseObj = t3lib_div::makeInstance('t3lib_TSparser');
		$parseObj->setup = array();
		$defaultConfigurationFile = t3lib_div::getFileAbsFileName('EXT:ter_doc/Configuration/TypoScript/static.ts');
		$parseObj->parse(file_get_contents($defaultConfigurationFile));
		$defaultSettings = $parseObj->setup['plugin.']['tx_terdoc.']['settings.'];


		// retrieve user configuration ...and throw error if configuration is not correct
		$configurationArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_doc']);
		if (empty($configurationArray['typoscriptFile'])) {
			throw new Exception('Exception thrown #1294655536 : no configuration file is defined. Update key "typoscriptFile" in EM', 1294655536);
		}
		if (!is_file($configurationArray['typoscriptFile'])) {
			throw new Exception('Exception thrown #1294657536: file does not exist "' . $configurationArray['typoscriptFile'] . '". Make sure key "typoscriptFile" in EM is correct', 1294657536);
		}

		// Fetch content from a typoscript file...
		$parseObj->setup = array();
		$parseObj->parse(file_get_contents($configurationArray['typoscriptFile']));
		$settings = $parseObj->setup['plugin.']['tx_terdoc.']['settings.'];


		$settings = array_merge($defaultSettings, $settings);
		if (empty($settings)) {
			throw new Exception('Exception thrown #1294659609: something went wrong, settings are empty. Can\'go any further', 1294659609);
		}
		
		return $settings;
	}

}

?>