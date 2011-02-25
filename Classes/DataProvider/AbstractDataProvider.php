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
	 * Abstract Data Provider
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	abstract class Tx_TerFe2_DataProvider_AbstractDataProvider implements Tx_TerFe2_DataProvider_DataProviderInterface {

		/**
		 * @var array Configuration array
		 */
		protected $configuration;

		/**
		 * Inject configuration for the DataProvider
		 * 
		 * @param array $configuration TypoScript configuration
		 * @return void
		 */
		public function injectConfiguration(array $configuration) {
			$this->configuration = $configuration;
		}


		/**
		 * Returns all Extensions informations for the Scheduler Task
		 *
		 * @param integer $lastUpdate Last update of the extension list
		 * @return array Extension informations
		 */
		public function getUpdateInfo($lastUpdate) {}

	}
?>