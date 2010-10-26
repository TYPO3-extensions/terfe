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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   45: class rlmp_officelib_officemeta
 *   76:     function setProperty ($key, $value, $type='string')
 *   86:     function getProperty ($key)
 *   96:     function getType ($key)
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_officemeta {

	var $properties = array(
		'title' => array ('value' => 'Untitled', 'type' => 'string'),
		'generator' => array ('value' => 'TYPO3 Documents Suite', 'type' => 'string'),
		'creator' => array ('value' => '', 'type' => 'string'),
		'creation-date' => array ('value' => null, 'type' => 'date'),
		'modification-date' => array ('value' => null, 'type' => 'date'),
		'statistics_tables' => array ('value' => 0, 'type' => 'integer'),
		'statistics_images' => array ('value' => 0, 'type' => 'integer'),
		'statistics_objects' => array ('value' => 0, 'type' => 'integer'),
		'statistics_pages' => array ('value' => 0, 'type' => 'integer'),
		'statistics_paragraphs' => array ('value' => 0, 'type' => 'integer'),
		'statistics_words' => array ('value' => 0, 'type' => 'integer'),
		'statistics_characters' => array ('value' => 0, 'type' => 'integer'),

			// Properties starting with '_' contain values set at run-time (by someone, eg. a frontent plugin)
			// and should only be used internally. They are used while the tcmeta textcomposite is rendered.
		'_page-number' => array ('value' => 0, 'type' => 'integer'),
	);

	var $toc = array();

	/**
	 * Sets a meta property
	 *
	 * @param	string		$key: The property's key
	 * @param	mixed		$value: The value
	 * @param	string		$type: Type (string|integer|date|time). Default is 'string'
	 * @return	void
	 */
	function setProperty ($key, $value, $type='string') {
		$this->properties[$key]['value'] = $value;
		$this->properties[$key]['type'] = $type;
	}

	/**
	 * Returns the value and type of a meta property
	 *
	 * @param	string		$key: The property's key
	 * @return	array		The value and type
	 */
	function getProperty ($key) {
		return $this->properties[$key];
	}
}

?>