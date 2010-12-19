<?php

/***************************************************************
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
***************************************************************/

/**
 * Documentation renderer for the ter_doc extension
 *
 * $Id$
 *
 * @author	Fabien Udriot <fabien.udriot@ecodev.ch>
 */

if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');

/**
 * CLI class that handles TER documentation
 *
 */
class tx_terdoc_cli extends t3lib_cli {


	/**
	 * Constructor
	 *
	 * @return tx_terdoc_cli
	 */
	function __construct () {

		// Running parent class constructor
		parent::t3lib_cli();

		$this->cli_options = array();
		// Setting help texts:
		$this->cli_help['name'] = 'handles TER documentation';
		unset($this->cli_help['synopsis']);
		$this->cli_help['description'] = "Class that handles TER documentation";
		$this->cli_help['examples'] = "/var/www/typo3/cli_dispatch.phpsh ter_doc";
		$this->cli_help['options'] = "download";
		$this->cli_help['author'] = "Fabien Udriot <fabien.udriot@ecodev.ch>";
	}

	/**
	 * CLI engine
	 *
	 * @param array Command line arguments
	 * @return void
	 */
	function main($argv) {

		// get task (function)
		$task = (string)$this->cli_args['_DEFAULT'][1];

		if (!$task){
			$this->cli_validateArgs();
			$this->cli_help();
			exit;
		}

		if ($task == 'myFunction') {
			$this->cli_echo("\n\nmyFunction will be called:\n\n");
			$this->myFunction();
		}
	}

	/**
	 * myFunction which is called over cli
	 *
	 */
	function myFunction(){
		// Output
		$this->cli_echo("Whats your name:");

		// Input
		$input = $this->cli_keyboardInput();
		$this->cli_echo("\n\nHi ".$input.", your CLI script works :)\n\n");

		// Input yes/no
		$input = $this->cli_keyboardInput_yes('You want money?');
		if($b){
			$this->cli_echo("\nHaha.. go working! :)\n");
		}else{
			$this->cli_echo("\nOh ok.. are you ill?\n");
		}
	}
}

// Call the functionality
$ter = t3lib_div::makeInstance('tx_terdoc_cli');
$ter->main($_SERVER['argv']);
?>