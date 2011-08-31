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
	 * Update extension list task
	 */
	class Tx_TerFe2_Task_UpdateExtensionListTask extends tx_scheduler_Task {

		/**
		 * @var integer
		 */
		public $extensionsPerRun = 10;

		/**
		 * @var string
		 */
		public $providerName = 'extensionmanager';

		/**
		 * @var array
		 */
		protected $settings;

		/**
		 * @var Tx_Extbase_Object_ObjectManager
		 */
		protected $objectManager;

		/**
		 * @var Tx_TerFe2_ExtensionProvider_ProviderManager
		 */
		protected $providerManager;

		/**
		 * @var Tx_TerFe2_Persistence_Registry
		 */
		protected $registry;


		/**
		 * Initialize task
		 *
		 * @return void
		 */
		public function initializeTask() {
				// Load object manager
			$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');

				// Load configuration manager and set extension setup
				// required to be loaded in object manager for persistence mapping
			$configurationManager = $this->objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
			$configurationManager->setConfiguration(Tx_TerFe2_Utility_TypoScript::getSetup('plugin.tx_terfe2'));

				// Load provider manager
			$this->providerManager = $this->objectManager->get('Tx_TerFe2_ExtensionProvider_ProviderManager');

				// Load registry
			$this->registry = $this->objectManager->get('Tx_TerFe2_Persistence_Registry');

				// Load object builder
			$this->objectBuilder = $this->objectManager->get('Tx_TerFe2_Object_ObjectBuilder');
		}


		/**
		 * Public method, usually called by scheduler.
		 *
		 * @return boolean TRUE on success
		 */
		public function execute() {
			$this->initializeTask();

				// Get information
			$lastRun = (int) $this->registry->get('lastRun');
			$offset  = (int) $this->registry->get('offset');
			$count   = (int) $this->extensionsPerRun;

				// TODO: Remove testing values
			$lastRun = 1306920788;
			$offset  = 0;

				// Get extension structure from provider
			$provider = $this->providerManager->getProvider($this->providerName);
			//$extensions = $provider->getExtensions($lastRun, $offset, $count);
			$extensions = array();

				// Build models from extension structure
			$this->createObjects($extensions);

				// Set new values to registry
			$this->registry->add('lastRun', $GLOBALS['EXEC_TIME']);
			$this->registry->add('offset', $offset + $count);

			return TRUE;
		}


		/**
		 * Create models from extension structure
		 *
		 * @param array $structure Extension structure
		 * @return void
		 */
		protected function createObjects(array $structure) {
			/**
			 * 1. Author     Tx_TerFe2_Domain_Model_Author
			 * 2. Relation   Tx_TerFe2_Domain_Model_Relation
			 * 3. Version    Tx_TerFe2_Domain_Model_Version
			 * 4. Extension  Tx_TerFe2_Domain_Model_Extension
			 */


			$structure = array(
				'ext_key' => 'test'
			);

			$this->objectBuilder->create('Tx_TerFe2_Domain_Model_Extension', $structure['ext_key'], $structure);
			$this->objectBuilder->get('Tx_TerFe2_Domain_Model_Extension', $structure['ext_key'])->setExtKey('blub');

			$objects = $this->objectBuilder->getAll();
			t3lib_div::writeFile(PATH_site . 'debug.txt', print_r($objects, TRUE));
		}


		/**
		 * Returns the name of selected extension provider
		 *
		 * @return string
		 */
		public function getAdditionalInformation() {
			$title = ucfirst($this->providerName);
			if (!empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'][$this->providerName]['title'])) {
				$title = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'][$this->providerName]['title'];
				$title = Tx_Extbase_Utility_Localization::translate($title);
			}
			return ' ' . $title . ' ';
		}

	}
?>