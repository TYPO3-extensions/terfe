<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Robert Lemke (robert@typo3.org)
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
 * Factory Method class for general usage of office documents. Use this class to instantiate
 * the neccessary object for using the office library in your extensions.
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_officefactory {

	/**
	 * This function returns a globally unique instance of this class (-> Singleton). Always use this
	 * function in order to create / get an instance of this class, don't instantiate it yourself.
	 *
	 * @return	object	An instance of this class
	 * @access	public
	 */
	function &getInstance()  {
		static $instance;
		if (!isset ($instance)) {
			$instance = t3lib_div::makeInstance ('rlmp_officelib_officefactory');
		}
		return $instance;
	}

	/**
	 * Creates a new document by instantiating the given document class.
	 *
	 * @param	string		Name of the document class
	 * @return	object		The instance of the given class or NULL if it didn't exist.
	 */
	function createDocument ($documentClass) {
		if (class_exists($documentClass)) {
			return new $documentClass;
		} else {
			return null;
		}
	}
}

?>