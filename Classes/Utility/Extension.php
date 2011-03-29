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
	 * Utilities to manage extension information
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Utility_Extension {

		/**
		 * Get extension information from module getters
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension Extension object
		 * @return array Extension information
		 */
		static public function discloseExtension(Tx_TerFe2_Domain_Model_Extension $extension) {
			$extensionInfo = array();
			$classObjects  = array(
				'Tx_TerFe2_Domain_Model_Extension' => $extension,
				'Tx_TerFe2_Domain_Model_Version'   => $extension->getLastVersion(),
				'Tx_TerFe2_Domain_Model_Author'    => $extension->getLastVersion()->getAuthor(),
			);

			foreach ($classObjects as $className => $object) {
				$methods = get_class_methods($className);
				foreach ($methods as $methodName) {
					if (strpos($methodName, 'get') === 0) {
						$arrayKey = str_replace('get', '', $methodName);
						$arrayKey[0] = strtolower($arrayKey[0]);
						if ($className == 'Tx_TerFe2_Domain_Model_Author') {
							$arrayKey = 'author' . ucfirst($arrayKey);
						}
						$extensionInfo[$arrayKey] = $object->$methodName();
					}
				}
			}

			return $extensionInfo;
		}

	}
?>