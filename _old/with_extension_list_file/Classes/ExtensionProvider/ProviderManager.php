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
	 * Manager for extension providers
	 */
	class Tx_TerFe2_ExtensionProvider_ProviderManager implements t3lib_Singleton {

		/**
		 * @var Tx_Extbase_Object_ObjectManagerInterface
		 */
		protected $objectManager;

		/**
		 * @var array
		 */
		protected $providers;


		/*
		 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
		 * @return void
		 */
		public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
			$this->objectManager = $objectManager;
		}


		/**
		 * Get an instance of a concrete extension provider
		 * 
		 * @param string $name Name of the provider
		 * @return Tx_TerFe2_ExtensionProvider_ExtensionProviderInterface Extension provider
		 */
		public function getProvider($name) {
			$name = strtolower(trim($name));

			if (!empty($this->providers[$name])) {
				return $this->providers[$name];
			}

			if (empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'][$name])) {
				throw new Exception('No configuration found for an extension provider with name "' . $name . '"');
			}

			$configuration = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'][$name];
			if (empty($configuration['class'])) {
				throw new Exception('No class name found in configuration for the extension provider with name "' . $name . '"');
			}

			$provider = $this->objectManager->get($configuration['class']);
			if (!empty($configuration['configuration']) && method_exists($provider, 'setConfiguration')) {
				$provider->setConfiguration($configuration['configuration']);
			}
			if (method_exists($provider, 'initializeProvider')) {
				$provider->initializeProvider();
			}

			return $this->providers[$name] = $provider;
		}

	}
?>