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
 * Docbook download option for TER Doc 
 *
 * $Id: class.tx_terdocdocbook.php,v 1.1 2005/10/25 11:00:03 robert_typo3 Exp $
 *
 * @author	Robert Lemke <robert@typo3.org>
 */

require_once (t3lib_extMgm::extPath('ter_doc').'class.tx_terdoc_api.php');
require_once (t3lib_extMgm::extPath('ter_doc').'class.tx_terdoc_documentformat.php');

class tx_terdocdocbook extends tx_terdoc_documentformat_download {

	/**
	 * Renders the cache for a DocBook download version. The result consists
	 * of a zipped docbook file including images
	 * 
	 * @param	string		$documentDir: Absolute directory for the document currently being processed.
	 * @return	void		
	 * @access	public
	 */
	public function renderCache ($documentDir) {
		$staticConfArr = unserialize ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_doc_docbook']);
		if (is_array ($staticConfArr)) {		
			$zipCommand = $staticConfArr['zipCommand'];

				// zip the DocBook files:
			$zipCommand = str_replace ('###ARCHIVENAME###', $documentDir.'docbook/manual.zip', $zipCommand);
			$zipCommand = str_replace ('###DIRECTORY###', $documentDir.'docbook/', $zipCommand);
			exec($zipCommand);
			
		} else {
			return FALSE;	
		}
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
		return @is_file ($documentDir.'docbook/manual.zip');
	}

	/**
	 * Returns the download size in bytes
	 * 
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @return	integer		The download size		
	 * @access	public
	 */
	public function getDownloadFileSize ($extensionKey, $version) {
		$docApiObj = tx_terdoc_api::getInstance();		
		$documentDir = $docApiObj->getDocumentDirOfExtensionVersion ($extensionKey, $version);
		return @filesize ($documentDir.'docbook/manual.zip');
	}

	/**
	 * Returns the full (absolute) path including the file name of the file
	 * which can be downloaded
	 *
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @return	mixed		Absolute path including file name of the downloadable file or FALSE if the file does not exist		
	 * @access	public
	 */
	public function getDownloadFileFullPath ($extensionKey, $version) {
		$docApiObj = tx_terdoc_api::getInstance();		
		$documentDir = $docApiObj->getDocumentDirOfExtensionVersion ($extensionKey, $version);
		return @is_file ($documentDir.'docbook/manual.zip') ? $documentDir.'docbook/manual.zip' : FALSE;		
	}
}
?>