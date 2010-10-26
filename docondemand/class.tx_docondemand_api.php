<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Robert Lemke (robert@typo3.org)
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
 * API for other TYPO3 extensions
 *
 * $Id: class.tx_docondemand_api.php,v 1.2 2006/03/06 00:15:20 robert_typo3 Exp $
 *
 * @author	Robert Lemke <robert@typo3.org>
 */

class tx_docondemand_api {

	protected	$repositoryDir = '';								// Full path to the local extension repository. Configured in the Extension Manager
	protected	$localLangArr = array();							// Contains the locallang strings for this API
	
	private static $instance = FALSE;								// Holds an instance of this class


	/**
	 * This constructor is private because you may only instantiate this class by calling
	 * the function getInstance() which returns a unique instance of this class (Singleton).
	 * 
	 * @return		void
	 * @access		private
	 */
	private function __construct() {
	}

	/**
	 * Returns a unique instance of this class. Call this function instead of creating a new
	 * instance manually!
	 * 
	 * @return		object		Unique instance of tx_docondemand_renderdocuments
	 * @access		public
	 */
	public function getInstance() {
		if (self::$instance === FALSE) {
			self::$instance = new tx_docondemand_api;
			self::$instance->init();	
		}
		return self::$instance;	
	} 

	/**
	 * Initializes this class
	 * 
	 * @return	void
	 * @access	protected
	 */
	protected function init() {
			// Fetch static configuration from Extension Manager:
		$staticConfArr = unserialize ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['docondemand']);
		if (is_array ($staticConfArr)) {
			$this->repositoryDir = $staticConfArr['repositoryDir'];
			if (substr($this->repositoryDir, -1, 1) != '/') $this->repositoryDir .= '/';
		}
	}

	/**
	 * Returns the page ID of the page where the category of the specified extension
	 * version can be read online. Of course that requires that a docondemand frontend
	 * plugin is installed on that page.
	 * 
	 * The PIDs for each category must be set in the docondemand_categories record.  
	 * 
	 * If docondemand_html is not installed, FALSE will be returned.
	 * 
	 * @param	string		$extensionKey: The extension key
	 * @param	string		$version: The version string
	 * @return	mixed		Page ID (integer) or FALSE
	 * @access	public
	 */
	public function getViewPageIdForExtensionVersion ($extensionKey, $version) {
		return false;	
	}

	/**
	 * Processes the given string with htmlspecialchars and converts the result
	 * from utf-8 to the charset of the current frontend
	 * page 
	 * 
	 * @param	string	$string: The utf-8 string to convert
	 * @return	string	The converted string
	 * @access	public
	 */
	public function csConvHSC ($string) {
		return $GLOBALS['TSFE']->csConv(htmlspecialchars($string), 'utf-8');
	}

}

?>