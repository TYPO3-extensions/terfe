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
			$configurationManager->setConfiguration(Tx_TerFe2_Utility_TypoScript::getSetup());

				// Load provider manager
			$this->providerManager = $this->objectManager->get('Tx_TerFe2_ExtensionProvider_ProviderManager');

				// Load registry
			$this->registry = $this->objectManager->get('Tx_TerFe2_Persistence_Registry');
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
			$extensions = $provider->getExtensions($lastRun, $offset, $count);

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
			t3lib_div::writeFile(PATH_site . 'debug.txt', print_r($structure, TRUE));

			/**
			 * Ablauf:
			 *  1. Alle Autoren anlegen und in Array mit hash des autors als key und uid als value
			 *  2. Das selbe für relations
			 *  3. Das selbe für extensions
			 *  4. Versionen anlegen und uids nachtragen
			 *
			tx_terfe2_domain_model_extension
			tx_terfe2_domain_model_version
			tx_terfe2_domain_model_relation
			tx_terfe2_domain_model_author
			*
			$dataMapper = $this->objectManager->get('Tx_Extbase_Persistence_Mapper_DataMapper');
			$rows = $dataMapper->map('Tx_TerFe2_Domain_Model_Version', array($extensions));
			*/
		}


		/**
		 * Returns the name of selected extension provider
		 *
		 * @return string
		 */
		public function getAdditionalInformation() {
			return ' ' . $this->providerName . ' ';
		}

	}
?>