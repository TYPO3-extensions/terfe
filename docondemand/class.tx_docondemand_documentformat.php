<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2006 Robert Lemke (robert@typo3.org)
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
 * Abstract classes for TER DOC document format classes 
 *
 * $Id$
 *
 * @author	Robert Lemke <robert@typo3.org>
 */

/**
 * Mother of all document format classes. Don't implement this class directly, use
 * a specialized class (tx_docondemand_documentformat_display or tx_docondemand_documentformat_download) 
 * instead.
 *  
 * @abstract
 */
abstract class tx_docondemand_documentformat {
	
	/**
	 * This function is called during the rendering process. Implement it for creating
	 * a cached version of your output format. 
	 * 
	 * @param	string		$documentDir: Absolute directory for the document currently being processed.
	 * @return	void		
	 * @access	public
	 * @abstract
	 */
	abstract public function render ($documentDir);

	/**
	 * Returns TRUE if a rendered document for the given extension version is
	 * available.
	 * 
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @return	boolean		TRUE if rendered version is available, otherwise FALSE		
	 * @access	public
	 * @abstract
	 */
	abstract public function isAvailable ($extensionKey, $version);
}


/**
 * Class which is specialized on output formats which are available for download
 * 
 * @abstract
 */
abstract class tx_docondemand_documentformat_download extends tx_docondemand_documentformat {

	/**
	 * Returns the download file size of the downloadable file from the specified
	 * extensions version
	 * 
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @return	mixed		File size of the file (integer) or FALSE if the file does not exist		
	 * @access	public
	 * @abstract
	 */
	abstract public function getDownloadFileSize ($extensionKey, $version);	

	/**
	 * Returns the full (absolute) path including the file name of the file
	 * which can be downloaded
	 *
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @return	mixed		Absolute path including file name of the downloadable file or FALSE if the file does not exist		
	 * @access	public
	 * @abstract
	 */
	abstract public function getDownloadFileFullPath ($extensionKey, $version);		
}
