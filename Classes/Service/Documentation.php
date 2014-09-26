<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
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
 ******************************************************************/

/**
 * Service to handle documentations
 *
 * @package TerFe2
 * @author Thomas Löffler <thomas.loeffler@typo3.org>
 */
class Tx_TerFe2_Service_Documentation implements t3lib_Singleton {

	/**
	 * @var string
	 */
	protected $baseUrl = '';

	/**
	 * @var array
	 */
	protected $availableFormats = array();

	/**
	 * @var string
	 */
	protected $docsInformation = NULL;

	/**
	 * Initialize the service
	 */
	public function __construct() {
		$this->baseUrl = 'http://docs.typo3.org/typo3cms/extensions/';
		$this->availableFormats = array(
			'sxw',
			'html',
			'rst',
			'pdf'
		);
		$this->docsInformation = json_decode(file_get_contents('http://docs.typo3.org/typo3cms/extensions/manuals.json'));
	}


	/**
	 * Get documentation link
	 *
	 * @throws Exception
	 * @param string $extensionKey Extension key
	 * @param string $versionString Version string
	 * @return string|NULL HTML link to the documentation
	 */
	public function getDocumentationLink($extensionKey, $versionString) {
		if (empty($extensionKey) || empty($versionString)) {
			throw new Exception('Extension key and version string are required to build a documentation url');
		}

		$manualExists = isset($this->docsInformation->$extensionKey);
		$documentationLink = NULL;

		if ($manualExists) {
			// link to extension to get the latest manual
			$url = $this->baseUrl . $extensionKey . '/';
			// check if link is not broken
			if (strpos(t3lib_div::getURL($url, TRUE), 'HTTP/1.1 200 OK') !== FALSE) {
				$documentationLink = '<a href="' . $url . '">Extension Manual</a>';
			}
		}

		return $documentationLink;
	}
}
?>