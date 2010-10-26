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
 * SXW download class for TER_DOC 
 *
 * $Id: class.tx_terdocpdf_readonline.php,v 1.1 2005/10/25 11:27:44 robert_typo3 Exp $
 *
 * @author	Robert Lemke <robert@typo3.org>
 */

require_once (t3lib_extMgm::extPath('ter_doc').'class.tx_terdoc_api.php');
require_once (t3lib_extMgm::extPath('ter_doc').'class.tx_terdoc_documentformat.php');

class tx_terdocsxw extends tx_terdoc_documentformat_download {	

	/**
	 * The SXW files should exist already, so this function does nothing. 
	 * 
	 * @param	string		$documentDir: Absolute directory for the document currently being processed.
	 * @return	void		
	 * @access	public
	 */
	public function renderCache ($documentDir) {

	}

	/**
	 * Returns TRUE if a rendered document for the given extension version is
	 * available.
	 * 
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @return	boolean		TRUE if rendered version is available, otherwise FALSE		
	 * @access	public
	 */
	public function isAvailable ($extensionKey, $version) {
		$docApiObj = tx_terdoc_api::getInstance();		
		$documentDir = $docApiObj->getDocumentDirOfExtensionVersion ($extensionKey, $version);
		return @is_file ($documentDir.'manual.sxw');
	}

	/**
	 * Returns the download file size of the OO writer manual from the specified
	 * extensions version
	 * 
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @return	mixed		File size of the manual (integer) or FALSE if the file does not exist		
	 * @access	public
	 */
	public function getDownloadFileSize ($extensionKey, $version) {
		$docApiObj = tx_terdoc_api::getInstance();		
		$documentDir = $docApiObj->getDocumentDirOfExtensionVersion ($extensionKey, $version);
		return @filesize ($documentDir.'manual.sxw');		
	}

	/**
	 * Returns the full (absolute) path including the file name of the file
	 * which can be downloaded (manual.sxw)
	 *
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @return	mixed		Absolute path including file name of the downloadable file or FALSE if the file does not exist		
	 * @access	public
	 */
	public function getDownloadFileFullPath ($extensionKey, $version) {
		$docApiObj = tx_terdoc_api::getInstance();		
		$documentDir = $docApiObj->getDocumentDirOfExtensionVersion ($extensionKey, $version);
		return @is_file ($documentDir.'manual.sxw') ? $documentDir.'manual.sxw' : FALSE;		
	}
}
?>