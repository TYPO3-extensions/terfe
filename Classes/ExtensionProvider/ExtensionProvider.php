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
	 * Extension Provider Manager
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_ExtensionProvider_ExtensionProvider implements t3lib_Singleton {

		/**
		 * @var Tx_Extbase_Object_ObjectManagerInterface
		 */
		protected $objectManager;

		/**
		 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
		 */
		protected $configurationManager;

		/**
		 * @var array
		 */
		protected $settings;

		/**
		 * @var array
		 */
		protected $concreteExtensionProviders;


		/**
		 * Inject Object Manager
		 *
		 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
		 * @return void
		 */
		public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
			$this->objectManager = $objectManager;
		}


		/**
		 * Inject Configuration Manager
		 *
		 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
			$this->configurationManager = $configurationManager;
			$settings = $configurationManager->getConfiguration(
				Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
			);
			$this->settings = Tx_TerFe2_Utility_TypoScript::parse($settings, TRUE);
		}


		/**
		 * Returns an array with information about all updated Extensions
		 *
		 * @param integer $lastUpdate Last update of the extension list
		 * @return array Update information
		 */
		public function getUpdateInfo($lastRunTime) {
			if (empty($this->settings['extensionProviders']) || !is_array($this->settings['extensionProviders'])) {
				throw new Exception('No Extension Providers configured');
			}

			$updateInfoArray = array();
			foreach ($this->settings['extensionProviders'] as $providerIdent => $providerSettings) {
				// Get update info from one Extension Provider
				$extensionProvider = $this->getConcreteExtensionProvider($providerIdent);
				$updateInfo = $extensionProvider->getUpdateInfo($lastRunTime);

				// Set providerIdent recursively
				array_walk($updateInfo, array($this, 'setExtensionProvider'), $providerIdent);

				// Add to info array
				$updateInfoArray = array_merge($updateInfoArray, $updateInfo);
			}

			return $updateInfoArray;
		}


		/**
		 * Returns URL to an Extension icon
		 *
		 * @param Tx_TerFe2_Domain_Model_Version Version object
		 * @param string $fileType File type
		 * @return string URL to icon file
		 */
		public function getExtensionIcon(Tx_TerFe2_Domain_Model_Version $version, $fileType = 'gif') {
			$extensionProvider = $this->getConcreteExtensionProvider($version->getExtensionProvider());
			return $extensionProvider->getExtensionIcon($version, $fileType);
		}


		/**
		 * Returns URL to an Extension file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version Version object
		 * @param string $fileType File type
		 * @return string URL to file
		 */
		public function getExtensionFile(Tx_TerFe2_Domain_Model_Version $version, $fileType = 't3x') {
			$extensionProvider = $this->getConcreteExtensionProvider($version->getExtensionProvider());
			return $extensionProvider->getExtensionFile($version, $fileType);
		}


		/**
		 * Returns name of an Extension file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version Version object
		 * @param string $fileType File type
		 * @return string File name
		 */
		public function getExtensionFileName(Tx_TerFe2_Domain_Model_Version $version, $fileType) {
			$extensionProvider = $this->getConcreteExtensionProvider($version->getExtensionProvider());
			return $extensionProvider->getExtensionFileName($version, $fileType);
		}


		/**
		 * Load a concrete Extension provider by identifier
		 *
		 * @param string $providerIdent Identifier of the Extension Provider
		 * @return Tx_TerFe2_ExtensionProvider_ExtensionProviderInterface Extension Provider
		 */
		protected function getConcreteExtensionProvider($providerIdent) {
			if (empty($providerIdent)) {
				throw new Exception('No Extension Provider given');
			}

			if (!empty($this->concreteExtensionProviders[$providerIdent])) {
				return $this->concreteExtensionProviders[$providerIdent];
			}

			if (empty($this->settings['extensionProviders'][$providerIdent]['className'])) {
				throw new Exception('No className found for Extension Provider "' . $providerIdent . '"');
			}

			// Create new one from settings
			$providerSettings  = $this->settings['extensionProviders'][$providerIdent];
			$extensionProvider = $this->objectManager->get($providerSettings['className']);
			if ($extensionProvider instanceof Tx_TerFe2_ExtensionProvider_AbstractExtensionProvider) {
				$extensionProvider->setConfiguration($providerSettings);
				if (method_exists($extensionProvider, 'initialize')) {
					$extensionProvider->initialize();
				}
				$this->concreteExtensionProviders[$providerIdent] = $extensionProvider;
			}

			return $extensionProvider;
		}


		/**
		 * Add Extension Provider to Extension information array
		 *
		 * @param array $extInfo Extension information
		 * @param string $key Array key
		 * @param string $providerIdent Ident of the Extension Provider
		 * @return void
		 */
		protected function setExtensionProvider(array &$extInfo, $key, $providerIdent) {
			$extInfo['extensionProvider'] = $providerIdent;
		}

	}
?>