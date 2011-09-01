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
	 * Extension provider using local files
	 */
	class Tx_TerFe2_Object_ObjectBuilder implements t3lib_Singleton {

		/**
		 * @var Tx_Extbase_Reflection_Service
		 */
		protected $reflectionService;

		/**
		 * @var Tx_Extbase_Persistence_IdentityMap
		 */
		protected $identityMap;

		/**
		 * @var array
		 */
		protected $classSchemata;


		/**
		 * Injects the identity map
		 *
		 * @param Tx_Extbase_Persistence_IdentityMap $identityMap
		 * @return void
		 */
		public function injectIdentityMap(Tx_Extbase_Persistence_IdentityMap $identityMap) {
			$this->identityMap = $identityMap;
		}


		/**
		 * Injects the reflection service
		 *
		 * @param Tx_Extbase_Reflection_Service $reflectionService
		 * @return void
		 */
		public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
			$this->reflectionService = $reflectionService;
		}


		/**
		 * Create an object from given class and attributes
		 * 
		 * @param string $className Name of the class
		 * @param array $attributes Array of all class attributes
		 * @return Tx_Extbase_DomainObject_DomainObjectInterface Stored object
		 */
		public function create($className, array $attributes) {
			if (empty($className) || empty($attributes)) {
				throw new Exception('No valid params given to create an object');
			}

				// Check identity map
			$identifier = md5(json_encode($attributes));
			if ($this->identityMap->hasIdentifier($identifier, $className)) {
				return $this->identityMap->getObjectByIdentifier($identifier, $className);
			}

				// Build object
			$classSchema = $this->getClassSchema($className);
			$object = new $className();
			foreach ($attributes as $key => $value) {
				$propertyName = t3lib_div::underscoredToLowerCamelCase($key);
				$protertyInfo = $classSchema->getProperty($propertyName);
				if (empty($protertyInfo) || stripos($protertyInfo['type'], 'Tx_') === 0) {
					continue;
				}
				$value = $this->convertValue($value, $protertyInfo['type']);
				$method = 'set' . ucfirst($propertyName);
				if (method_exists($object, $method)) {
					$object->$method($value);
				}
			}

				// Register object in identity map
			$this->identityMap->registerObject($object, $identifier);

			return $object;
		}


		/**
		 * Returns the schema of a class
		 * 
		 * @param string $className Name of the class
		 * @return Tx_Extbase_Reflection_ClassSchema Class schema
		 */
		protected function getClassSchema($className) {
			if (empty($className)) {
				throw new Exception('No valid class name given to create a class schema');
			}
			if (empty($this->classSchemata[$className])) {
				$this->classSchemata[$className] = $this->reflectionService->getClassSchema($className);
			}
			return $this->classSchemata[$className];
		}


		/**
		 * Convert value into correct type
		 *
		 * @param mixed $value Value to convert
		 * @param string $type Type of the conversation
		 * @return mixed Converted value
		 */
		protected function convertValue($value, $type) {
			switch ($type) {
				case 'int':
				case 'integer':
					return (int) $value;
				case 'float':
					return (float) $value;
				case 'bool':
				case 'boolean':
					return (boolean) $value;
				case 'array':
					return (array) $value;
				case 'string':
				default:
					return (string) $value;
			}
		}

	}
?>