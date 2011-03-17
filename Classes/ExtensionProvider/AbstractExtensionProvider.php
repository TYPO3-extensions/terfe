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
	abstract class Tx_TerFe2_ExtensionProvider_AbstractExtensionProvider implements Tx_TerFe2_ExtensionProvider_ExtensionProviderInterface {

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
		 * @param Tx_Extbase_Persistence_Mapper_DataMapFactory $dataMapFactory
		 * @return void
		 */
		public function injectDataMapFactory(Tx_Extbase_Persistence_Mapper_DataMapFactory $dataMapFactory) {
			$this->dataMapFactory = $dataMapFactory;
		}


		/**
		 * Injects the Reflection Service
		 *
		 * @param Tx_Extbase_Reflection_Service $reflectionService
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
		 * Returns URL to a cached or new Extension icon
		 *
		 * @param Tx_TerFe2_Domain_Model_Version Version object
		 * @param string $fileType File type
		 * @return string URL to icon file
		 */
		public function getExtensionIcon(Tx_TerFe2_Domain_Model_Version $version, $fileType) {
			$fileName  = $this->getExtensionFileName($version, $fileType);
			$cachePath = Tx_TerFe2_Utility_Files::getAbsDirectory('typo3temp/pics/');

			// Check local cache first
			if (Tx_TerFe2_Utility_Files::fileExists($cachePath . $fileName)) {
				return t3lib_div::locationHeaderUrl('typo3temp/pics/' . $fileName);
			}

			// Get icon from concrete Extension Provider
			$urlToFile = $this->getUrlToFile($fileName);

			// Copy URL to local cache
			Tx_TerFe2_Utility_Files::copyFile($urlToFile, $cachePath . $fileName);

			return $urlToFile;
		}


		/**
		 * Returns URL to an Extension file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version Version object
		 * @param string $fileType File type
		 * @return string URL to file
		 */
		public function getExtensionFile(Tx_TerFe2_Domain_Model_Version $version, $fileType) {
			$fileName = $this->getExtensionFileName($version, $fileType);
			return $this->getUrlToFile($fileName);
		}


		/**
		 * Returns name of an Extension file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version Version object
		 * @param string $fileType File type
		 * @return string File name
		 */
		public function getExtensionFileName(Tx_TerFe2_Domain_Model_Version $version, $fileType) {
			$extKey = $version->getExtension()->getExtKey();
			$versionString = $version->getVersionString();
			$fileName = Tx_TerFe2_Utility_Files::generateFileName($extKey, $versionString, $fileType);

			if (empty($fileName)) {
				throw new Exception('Could not generate file name for this Version object');
			}

			return $fileName;
		}


		/**
		 * Returns the URL to a file
		 *
		 * @param string $fileName File name
		 * @return string URL to file
		 */
		abstract protected function getUrlToFile($fileName);


		/**
		 * Returns an array with information about all updated Extensions
		 *
		 * @param integer $lastUpdate Last update of the extension list
		 * @return array Update information
		 */
		abstract public function getUpdateInfo($lastUpdate);


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
			$classNames = array(
				'Tx_TerFe2_Domain_Model_Extension',
				'Tx_TerFe2_Domain_Model_Version',
				'Tx_TerFe2_Domain_Model_Author',
			);
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

					// Check for Author fields
					if ($className == 'Tx_TerFe2_Domain_Model_Author') {
						$propertyName = 'author' . ucfirst($propertyName);
					}

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

	}
?>