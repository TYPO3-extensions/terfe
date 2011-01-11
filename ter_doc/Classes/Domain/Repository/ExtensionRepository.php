<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Fabien Udriot <fabien.udriot@ecodev.ch>
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
 * A repository for extensions which does not extend Tx_Extbase_Persistence_Repository
 *
 * @copyright Copyright belongs to the respective authors
 */
class Tx_TerDoc_Domain_Repository_ExtensionRepository {

	/**
	 * constructor
	 *
	 * @param array $settings options passed from the CLI
	 */
	public function __construct($settings, $arguments) {
		$this->settings = $settings;
		$this->arguments = $arguments;
	}

	/******************************************************
	 *
	 * Extension index functions
	 *
	 * **************************************************** */

	/**
	 * Checks if the extension index file (extensions.xml.gz) was modified
	 * since the last built of the extension index in the database.
	 *
	 * @return	boolean		TRUE if the index has changed
	 * @access	protected
	 */
	public function wasModified() {
		$oldMD5Hash = $currentMD5Hash = '';
		if (file_exists($this->settings['md5File'])) {
			$oldMD5Hash = file_get_contents($this->settings['md5File']);
		}

		if (file_exists($this->settings['extensionFile'])) {
			$currentMD5Hash = md5_file($this->settings['extensionFile']);
		} else {
			throw new Exception('Exception thrown #1294747712: no data source has been found at "' . $this->settings['extensionFile'] . '"', 1294747712);
		}

		return ($oldMD5Hash != $currentMD5Hash);
	}

	/**
	 * Reads the extension index file (extensions.xml.gz) and updates
	 * the the manual caching table accordingly.
	 *
	 * @return	boolean		TRUE if operation was successful
	 * @access	protected
	 */
	public function updateDB() {

		Tx_TerDoc_Utility_Cli::log('* Deleting cached manual information from database');

		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_terdoc_manuals', '1');

		// Transfer data from extensions.xml.gz to database:
		$unzippedExtensionsXML = implode('', @gzfile($this->settings['repositoryDir'] . 'extensions.xml.gz'));
		$extensions = simplexml_load_string($unzippedExtensionsXML);
		if (!is_object($extensions)) {
			Tx_TerDoc_Utility_Cli::log('Error while parsing ' . $this->settings['extensionFile'] . ' - aborting!');
			return FALSE;
		}

		$loop = 0;
		foreach ($extensions as $extension) {

			foreach ($extension as $version) {
				if (strlen($version['version'])) {
					$documentDir = Tx_TerDoc_Utility_Cli::getDocumentDirOfExtensionVersion($this->settings['documentsCache'], $extension['extensionkey'], $version['version']);
					$abstract = @file_get_contents($documentDir . 'abstract.txt');
					$language = @file_get_contents($documentDir . 'language.txt');

					$extensionsRow = array(
						'extensionkey' => $extension['extensionkey'],
						'version' => $version['version'],
						'title' => $version->title,
						'language' => $language,
						'abstract' => $abstract,
						'modificationdate' => $version->lastuploaddate,
						'authorname' => $version->authorname,
						'authoremail' => $version->authoremail,
						't3xfilemd5' => $version->t3xfilemd5
					);
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_terdoc_manuals', $extensionsRow);
				}
			}
			
			// prevent the script to loop to many times in a development context
			// Otherwise will process more than 20000 extensions
			$loop ++;
			if ($loop == $this->arguments['limit']) {
				break;
			}
		}

		// Create new MD5 hash:
		#t3lib_div::writeFile($this->settings['extensionFile'], md5_file($this->settings['repositoryDir'] . 'extensions.xml.gz'));
		Tx_TerDoc_Utility_Cli::log('* Manual DB index was sucessfully reindexed');

		return TRUE;
	}


	/******************************************************
	 *
	 * Cache related functions
	 *
	 ******************************************************/

	/**
	 * Deletes rendered documents and directories of those extensions which don't
	 * exist in the extension index (anymore).
	 *
	 * @return	void
	 * @access	protected
	 */
	public function deleteOutdatedDocuments() {
		// FIXME
		Tx_TerDoc_Utility_Cli::log('* FIXME: deleteOutDatedDocuments not implemented');
	}

	/**
	 * Returns an array of extension keys and version numbers of those
	 * extensions which were modified since the last time the documents
	 * were rendered for this extension.
	 *
	 * @return	array		Array of extensionkey and version
	 * @access	protected
	 */
	public function getModifiedExtensionVersions() {

		$extensionKeysAndVersionsArr = array();
		Tx_TerDoc_Utility_Cli::log('* Checking for modified extension versions');

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'extensionkey,version,t3xfilemd5',
			'tx_terdoc_manuals',
			'1'
		);
		if ($res) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($res)) {
				$documentDir = Tx_TerDoc_Utility_Cli::getDocumentDirOfExtensionVersion ($row['extensionkey'], $row['version']);
				$t3xMD5OfRenderedDocuments = @file_get_contents ($documentDir.'t3xfilemd5.txt');
				if ($t3xMD5OfRenderedDocuments != $row['t3xfilemd5']) {
					$extensionKeysAndVersionsArr[] = array (
						'extensionkey' => $row['extensionkey'],
						'version' => $row['version'],
						't3xfilemd5' => $row['t3xfilemd5']
					);
				}
			}
		}
		Tx_TerDoc_Utility_Cli::log('* Found '.count($extensionKeysAndVersionsArr).' modified extension versions');
		return $extensionKeysAndVersionsArr;
	}
	/**
	 * Find all objects up to a certain limit with a given offset and a sorting order
	 *
	 * @param int $limit The maximum items to be displayed at once
	 * @param int $offset The offset (where to start)
	 * @param string $sortBy Field to sort the result set
	 * @return array An array of objects, an empty array if no objects found
	 */
	public function findLimit($limit, $offset, $sortBy='last_name') {
		$query = $this->createQuery();
		return $query->setOrderings(array($sortBy => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING))
				->setLimit($limit)
				->setOffset($offset)
				->execute();
	}

}

?>