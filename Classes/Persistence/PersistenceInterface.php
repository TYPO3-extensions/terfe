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
	 * Persistence handler interface
	 */
	interface Tx_TerFe2_Persistence_PersistenceInterface extends t3lib_Singleton {

		/**
		 * Load content
		 *
		 * @return void
		 */
		public function load();


		/**
		 * Save content
		 *
		 * @return void
		 */
		public function save();


		/**
		 * Set value
		 *
		 * @param string $key Name of the value
		 * @param mixed $value Value content
		 * @return void
		 */
		public function set($key, $value);


		/**
		 * Add value
		 *
		 * @param string $key Name of the value
		 * @param mixed $value Value content
		 * @return void
		 */
		public function add($key, $value);


		/**
		 * Add multiple values
		 *
		 * @param array $value Key <-> value pairs
		 * @return void
		 */
		public function addMultiple(array $values);


		/**
		 * Check if content contains given key
		 *
		 * @param string $key Name of the value
		 * @return boolean TRUE if exists
		 */
		public function has($key);


		/**
		 * Get value
		 *
		 * @param string $key Name of the value
		 * @return mixed Value content
		 */
		public function get($key);


		/**
		 * Get all values
		 *
		 * @return array Key <-> value pairs
		 */
		public function getAll();


		/**
		 * Remove a value
		 *
		 * @param string $key Name of the value
		 * @return void
		 */
		public function remove($key);


		/**
		 * Remove all values
		 *
		 * @return void
		 */
		public function removeAll();

	}
?>