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
	 */
	class Tx_TerFe2_Service_Documentation implements t3lib_Singleton {

		/**
		 * Get documentation url
		 *
		 * @param string $extension Extension key
		 * @param string $version Version string
		 * @return string Url to documentation
		 */
		public function getDocumentationUrl($extension, $version) {
			if (empty($extension) || empty($version)) {
				throw new Exception('Extension key and version string are required to build a documentation url');
			}

				// TODO: Get url from ter_doc extension
			return '';
		}

	}
?>