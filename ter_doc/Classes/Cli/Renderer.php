<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Fabien Udriot <fabien.udriot@ecodev.ch>
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
 * Documentation renderer for the ter_doc extension
 * mini-howto:
 * php typo3/cli_dispatch.phpsh ter_doc help
 *
 * $Id: class.Tx_TerDoc_Cli_Renderer.php 41260 2010-12-19 21:26:54Z fab1en $
 *
 * @author	Fabien Udriot <fabien.udriot@ecodev.ch>
 */
if (!defined('TYPO3_cliMode'))
	die('You cannot run this script directly!');

/**
 * CLI class that handles TER documentation
 *
 */
class Tx_TerDoc_Cli_Renderer {

	/**
	 * Constructor
	 *
	 * @return Tx_TerDoc_Cli_Renderer
	 */
	function __construct() {

	}

	/**
	 * CLI dispatcher
	 *
	 * @param array Command line arguments
	 * @return void
	 */
	function main($argv) {
		$controller = t3lib_div::makeInstance('Tx_TerDoc_Controller_CliController');

		$options = $commands = array();
		$options['help'] = $options['force'] = FALSE;

		// process the command's arguments
		array_shift($argv);
		$argv = array_map('trim', $argv);
		foreach ($argv as $argument) {
			if (preg_match('/^-/is', $argument)) {
				switch ($argument) {
					case '--force':
					case '-f':
						$options['force'] = TRUE;
						break;
					case '--help':
					case '-h':
						$options['help'] = TRUE;
						break;
				}
			}
			else {
				$commands[] = $argument;
			}
		}

		// displays help if necessary
		if (count($argv) == 0 || $options['help']) {
			$controller->helpAction();
			die();
		}

		// call the right command
		if ($commands[0] == 'render') {
			try {
				$controller->renderAction($options);
			} catch (Exception $e) {
				Tx_TerDoc_Utility_Cli::log($e->getMessage());
			}
		} else {
			Tx_TerDoc_Utility_Cli::log('Unknown command');
			Tx_TerDoc_Utility_Cli::log('Type "help" for usage.');
		}

		// get task (function)
		#$task = (string)$this->cli_args['_DEFAULT'][1];
		// DocumentCache
		//   deleteOutdatedDocuments
		//   getModifiedExtensionVersions
		//   transformManualToDocBook
		//   
		// extensionIndex
		//   updateDB
		//   wasModified
		//
		// pageCache
		//   clearForAll
		//   getCacheUidsForExtension
		//   clearForExtension
		//
		// t3x
		//   extractFileFromT3X
		//
		// downloadT3X
		//
		//
		// registerOutputFormat
		// renderCache
		//
		// 
		// getDocumentDirOfExtensionVersion
		// getExtensionVersionPathAndBaseName
		// removeDirRecursively
		// log
//		if (!$task){
//			$this->cli_validateArgs();
//			$this->cli_help();
//			exit;
//		}
//
//		if ($task == 'myFunction') {
//			$this->cli_echo("\n\nmyFunction will be called:\n\n");
//			$this->myFunction();
//		}
	}

	/**
	 * myFunction which is called over cli
	 *
	 */
	function myFunction() {
		// Output
		$this->cli_echo("Whats your name:");

		// Input
		$input = $this->cli_keyboardInput();
		$this->cli_echo("\n\nHi " . $input . ", your CLI script works :)\n\n");

		// Input yes/no
		$input = $this->cli_keyboardInput_yes('You want money?');
		if ($b) {
			$this->cli_echo("\nHaha.. go working! :)\n");
		} else {
			$this->cli_echo("\nOh ok.. are you ill?\n");
		}
	}

}

// Call the functionality
$ter = t3lib_div::makeInstance('Tx_TerDoc_Cli_Renderer');
$ter->main($_SERVER['argv']);
?>