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
	 * Abstract Extension Provider
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	abstract class Tx_TerFe2_ExtensionProvider_AbstractExtensionProvider implements Tx_TerFe2_ExtensionProvider_ExtensionProviderInterface, t3lib_Singleton {

		/**
		 * @var Tx_Extbase_Persistence_Mapper_DataMapFactory
		 */
		protected $dataMapFactory;

		/**
		 * @var Tx_Extbase_Reflection_Service
		 */
		protected $reflectionService;

		/**
		 * @var array Configuration array
		 */
		protected $configuration;

		/**
		 * @var array Extension information schema
		 */
		protected $extInfoSchema = array();


		/**
		 * Injects the DataMap Factory
		 *
		 * @param Tx_Extbase_Persistence_Mapper_DataMapFactory
		 * @return void
		 */
		public function injectDataMapFactory(Tx_Extbase_Persistence_Mapper_DataMapFactory $dataMapFactory) {
			$this->dataMapFactory = $dataMapFactory;
		}


		/**
		 * Injects the Reflection Service
		 *
		 * @param Tx_Extbase_Reflection_Service
		 * @return void
		 */
		public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService) {
			$this->reflectionService = $reflectionService;
		}


		/**
		 * Set configuration for the DataProvider
		 *
		 * @param array $configuration TypoScript configuration
		 * @return void
		 */
		public function setConfiguration(array $configuration) {
			$this->configuration = $configuration;
		}


		/**
		 * Generates an array with all Extension information
		 *
		 * @param array $extData Extension data
		 * @param array $extInfoSchema Extension information schema
		 * @return array Extension information
		 */
		protected function getExtensionInfo(array $extData) {
			$extInfoSchema = $this->getExtensionInfoSchema();
			$extInfo       = array('softwareRelation' => array());

			// Get field value
			foreach ($extInfoSchema as $fieldName => $fieldConf) {
				$extInfo[$fieldName] = '';
				if (!empty($extData[$fieldName])) {
					$extInfo[$fieldName] = $this->convertValue($extData[$fieldName], $fieldConf['type']);
				}
			}

			// Get upload date
			if (empty($extInfo['uploadDate'])) {
				$extInfo['uploadDate'] = (int) $GLOBALS['SIM_EXEC_TIME'];
			}

			// Get file hash
			if (empty($extInfo['fileHash']) && !empty($extData['fileName'])) {
				$extInfo['fileHash'] = Tx_TerFe2_Utility_Files::getFileHash($extData['fileName']);
			}

			// Get version number
			if (empty($extInfo['versionNumber']) && !empty($extInfo['versionString'])) {
				$extInfo['versionNumber'] = t3lib_div::int_from_ver($extInfo['versionString']);
			}

			// Check required information afterwards
			foreach ($extInfoSchema as $fieldName => $fieldConf) {
				if (empty($extInfo[$fieldName]) && $fieldConf['required']) {
					return array();
				}
			}

			// Add relations
			if (!empty($extData['relations']) && is_array($extData['relations'])) {
				foreach ($extData['relations'] as $relation) {
					if (!empty($relation['relationType']) && !empty($relation['relationKey']) && !empty($relation['softwareType'])) {
						$relation['versionRange'] = (!empty($relation['versionRange']) ? $relation['versionRange'] : '');
						$extInfo['softwareRelation'][] = $relation;
					}
				}
			}

			return $extInfo;
		}


		/**
		 * Returns properties of the Extension and Version object
		 *
		 * @return array Properties
		 */
		protected function getExtensionInfoSchema() {
			if (!empty($this->extInfoSchema)) {
				return $this->extInfoSchema;
			}

			// Get class structure and property options
			$classNames = array('Tx_TerFe2_Domain_Model_Extension', 'Tx_TerFe2_Domain_Model_Version');
			foreach ($classNames as $className) {
				$classSchema = $this->reflectionService->getClassSchema($className);
				$dataMap     = $this->dataMapFactory->buildDataMap($className);
				$object      = t3lib_div::makeInstance($className);
				$properties  = $object->_getProperties();

				foreach ($properties as $propertyName => $propertyValue) {
					// Check if property is persitable
					if (!$dataMap->isPersistableProperty($propertyName)) {
						continue;
					}

					// Get property type
					$propertyData = $classSchema->getProperty($propertyName);
					if (strpos($propertyData['type'], 'Tx_') !== FALSE) {
						continue;
					}

					// Check if property is required
					$tagValues = $this->reflectionService->getPropertyTagValues($className, $propertyName, 'validate');
					$required  = (stripos(implode(',', $tagValues), 'NotEmpty') !== FALSE);

					// Build field
					$this->extInfoSchema[$propertyName] = array(
						'type'     => $propertyData['type'],
						'required' => $required,
					);
				}
			}

			unset($this->extInfoSchema['manual']);
			unset($this->extInfoSchema['extensionProvider']);

			return $this->extInfoSchema;
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
				case 'integer':
					return (int) $value;
					break;
				case 'float':
					return (float) $value;
					break;
				case 'boolean':
					return (boolean) $value;
					break;
				case 'array':
					return (array) $value;
					break;
				case 'string':
				default:
					return (string) $value;
					break;
			}
		}


		/**
		 * Get URL to a cached or new Extension icon file
		 *
		 * @param string $extKey Extension key
		 * @param string $versionString Version string
		 * @param string $fileType File type
		 * @return string URL to icon file
		 */
		public function getUrlToIcon($extKey, $versionString, $fileType = 'gif') {
			// Check local cache first
			$iconRelPath = Tx_TerFe2_Utility_Files::getIconRelCachePath($extKey, $versionString, $fileType);
			if (Tx_TerFe2_Utility_Files::fileExists(PATH_site . $iconRelPath)) {
				return t3lib_div::locationHeaderUrl($iconRelPath);
			}

			// Get new file from concrete provider
			$urlToFile = $this->getUrlToFile($extKey, $versionString, $fileType);

			// Copy URL to local cache
			Tx_TerFe2_Utility_Files::copyFile($urlToFile, $iconRelPath);

			return $urlToFile;
		}


		/**
		 * Returns all Extension information for the Scheduler Task
		 *
		 * @param integer $lastUpdate Last update of the extension list
		 * @return array Extension information
		 */
		abstract public function getUpdateInfo($lastUpdate);


		/**
		 * Returns URL to a file via extKey, version and fileType
		 *
		 * @param string $extKey Extension key
		 * @param string $versionString Version string
		 * @param string $fileType File type
		 * @return string URL to file
		 */
		abstract public function getUrlToFile($extKey, $versionString, $fileType);

	}
?>