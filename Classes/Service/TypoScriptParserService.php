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
	 * Parses TypoScript configuration
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Service_TypoScriptParserService implements t3lib_Singleton {

		/**
		 * @var tslib_cObj
		 */
		protected $cObj;


		/**
		 * Contructor for the TypoScript parser
		 *
		 * @return void
		 */
		public function __construct() {
			if (empty($this->cObj)) {
				$this->cObj = t3lib_div::makeInstance('tslib_cObj');
			}
		}


		/**
		 * Returns completely parsed TypoScript configuration
		 * 
		 * @param array $configuration TypoScript configuration
		 * @return array Parsed configuration
		 */
		public function getParsed(array $configuration) {
			if (!empty($configuration)) {
				$configuration = $this->parse($configuration);
				return t3lib_div::removeDotsFromTS($configuration);
			}

			return array();
		}


		/**
		 * Parse TypoScript configuration
		 * 
		 * @param array $configuration TypoScript configuration
		 * @param boolean $parseSub Parse child nodes
		 * @return array Parsed configuration
		 */
		public function parse(array $configuration, $parseSub = TRUE) {
			$typoScriptArray = array();

			foreach ($configuration as $key => $value) {
				if (is_array($value)) {
					// Get TypoScript like configuration array
					if (!empty($value['_typoScriptNodeValue'])) {
						$typoScriptArray[$key] = $value['_typoScriptNodeValue'];
						unset($value['_typoScriptNodeValue']);
						$typoScriptArray[$key . '.'] = $this->parse($value, FALSE);

						// Parse TypoScript object
						if ($parseSub) {
							$typoScriptArray[$key] = $this->cObj->cObjGetSingle(
								$typoScriptArray[$key],
								$typoScriptArray[$key . '.']
							);
							unset($typoScriptArray[$key . '.']);
						}
					} else {
						$typoScriptArray[$key . '.'] = $this->parse($value, $parseSub);
					}
				} else {
					$typoScriptArray[$key] = $value;
				}
			}

			return $typoScriptArray;
		}

	}
?>