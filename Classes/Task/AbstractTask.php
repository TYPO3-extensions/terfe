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
	 * Abstract task
	 */
	abstract class Tx_TerFe2_Task_AbstractTask extends tx_scheduler_Task {

		/**
		 * @var integer
		 */
		public $elementsPerRun = 10;

		/**
		 * @var string
		 */
		public $clearCachePages = 0;

		/**
		 * @var array
		 */
		protected $settings;

		/**
		 * @var Tx_Extbase_Configuration_ConfigurationManager
		 */
		protected $configurationManager;

		/**
		 * @var Tx_Extbase_Object_ObjectManager
		 */
		protected $objectManager;

		/**
		 * @var Tx_TerFe2_Persistence_Registry
		 */
		protected $registry;


		/**
		 * Public method, usually called by scheduler
		 *
		 * @return boolean TRUE on success
		 */
		public function execute() {
				// Load object manager
			$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');

				// Configuration is required to be loaded in object manager for persistence mapping
			$this->settings = Tx_TerFe2_Utility_TypoScript::getSetup('plugin.tx_terfe2');
			$this->configurationManager = $this->objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
			$this->configurationManager->setConfiguration($this->settings);

				// Load registry
			$this->registry = $this->objectManager->get('Tx_TerFe2_Persistence_Registry');
			$this->registry->setName(get_class($this));

				// Initialize task
			$this->initializeTask();

				// Get process information
			$lastRun = (int) $this->registry->get('lastRun');
			$offset = (int) $this->registry->get('offset');
			$count = (int) $this->elementsPerRun;

				// Run task
			$result = $this->executeTask($lastRun, $offset, $count);

				// Add new values to registry
			$offset = (!empty($result) ? $offset + $count : 0);
			$this->registry->add('lastRun', $GLOBALS['EXEC_TIME']);
			$this->registry->add('offset', $offset);

				// Clear page cache
			if (!empty($result) && !empty($this->clearCachePages)) {
				$this->clearPageCache($this->clearCachePages);
			}

			return TRUE;
		}


		/**
		 * Initialize task, override in concrete task
		 *
		 * @return void
		 */
		public function initializeTask() {
			
		}


		/**
		 * Execute the task, implement in concrete task
		 * 
		 * @param integer $lastRun Timestamp of the last run
		 * @param integer $offset Starting point
		 * @param integer $count Element count to process at once
		 * @return boolean TRUE on success
		 */
		abstract protected function executeTask($lastRun, $offset, $count);


		/**
		 * Clear cache of given pages
		 *
		 * @param string $pages List of page ids
		 * @return void
		 */
		protected function clearPageCache($pages) {
			if (!empty($pages)) {
				$pages = t3lib_div::intExplode(',', $pages, TRUE);
				Tx_Extbase_Utility_Cache::clearPageCache($pages);
			}
		}

	}
?>