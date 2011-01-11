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
	public function __construct($settings) {
		$this->settings = $settings;
	}

	/*	 * ****************************************************
	 *
	 * Extension index functions (protected)
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
		
		if (file_exists($this->settings['extensionDatasource'])) {
			$currentMD5Hash = md5_file($this->settings['extensionDatasource']);
		} else {
			throw new Exception('Exception thrown #1294747712: no data source has been found at "' .  $this->settings['extensionDatasource'] . '"', 1294747712);
		}

		return ($oldMD5Hash != $currentMD5Hash);
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