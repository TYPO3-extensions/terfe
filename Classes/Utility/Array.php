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
	 * Utilities to manage arrays
	 */
	class Tx_TerFe2_Utility_Array {

		/**
		 * Build an array from an object
		 * 
		 * @param object $object The object
		 * @return array Array of all attributes
		 */
		public static function objectToArray($object) {
			if (empty($object)) {
				return array();
			}

			$attributesArray = array();
			$className       = get_class($object);
			$classVars       = get_class_vars($className);
			$classMethods    = get_class_methods($className);

			foreach($classVars as $attributeName => $attributeValue) {
				if (strpos($attributeName, '_') === 0) {
					continue;
				}

				$method = 'get' . ucfirst($attributeName);
				if (!in_array($method, $classMethods)) {
					continue;
				}

				$value = $object->$method();
				if ($value instanceof Tx_Extbase_Persistence_ObjectStorage) {
					$valueArray = array();
					foreach($value as $model) {
						$valueArray[] = $model->toArray();
					}
					$value = $valueArray;
				}

				if ($value instanceof Tx_TerFe2_Domain_Model_AbstractEntity) {
					$value = $value->toArray();
				}

				if ($value instanceof Tx_TerFe2_Domain_Model_AbstractValueObject) {
					$value = $value->toArray();
				}

				$attributesArray[$attributeName] = $value;
			}

			return $attributesArray;
		}

	}
?>