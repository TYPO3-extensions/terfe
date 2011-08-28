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
	 * Abstract persistence handler
	 */
	abstract class Tx_TerFe2_Persistence_AbstractPersistence implements Tx_TerFe2_Persistence_PersistenceInterface {

		/**
		 * @var string
		 */
		protected $name = 'Tx_TerFe2';

		/**
		 * @var array
		 */
		protected $content = array();

		/**
		 * @var boolean
		 */
		protected $isLoaded = FALSE;

		/**
		 * @var boolean
		 */
		protected $saveImmediately = TRUE;


		/**
		 * Set name
		 *
		 * @param string $name Name to set
		 * @return void
		 */
		public function setName($name) {
			$this->name = $name;
		}


		/**
		 * Set saveImmediately
		 *
		 * @param boolean $saveImmediately Save immediately
		 * @return void
		 */
		public function setSaveImmediately($saveImmediately) {
			$this->saveImmediately = (bool) $saveImmediately;
		}


		/**
		 * Add value
		 *
		 * @param string $key Name of the value
		 * @param mixed $value Value content
		 * @return void
		 */
		public function add($key, $value) {
			if (empty($key)) {
				throw new Exception('Empty keys are not allowed');
			}
			if (!$this->isLoaded) {
				$this->load();
			}
			$this->content[$key] = $value;
			if ($this->saveImmediately) {
				$this->save();
			}
		}


		/**
		 * Add multiple values
		 *
		 * @param array $value Key <-> value pairs
		 * @return void
		 */
		public function addMultiple(array $values) {
			foreach ($values as $key => $value) {
				$this->add($key, $value);
			}
		}


		/**
		 * Check if content contains given key
		 *
		 * @param string $key Name of the value
		 * @return boolean TRUE if exists
		 */
		public function has($key) {
			if (!$this->isLoaded) {
				$this->load();
			}
			return isset($this->content[$key]);
		}


		/**
		 * Get value
		 *
		 * @param string $key Name of the value
		 * @return mixed Value content
		 */
		public function get($key) {
			if ($this->has($key)) {
				return $this->content[$key];
			}
			return NULL;
		}


		/**
		 * Get all values
		 *
		 * @return array Key <-> value pairs
		 */
		public function getAll() {
			if (!$this->isLoaded) {
				$this->load();
			}
			return $this->content;
		}


		/**
		 * Remove a value
		 *
		 * @param string $key Name of the value
		 * @return void
		 */
		public function remove($key) {
			if ($this->has($key)) {
				unset($this->content[$key]);
			}
			if ($this->saveImmediately) {
				$this->save();
			}
		}


		/**
		 * Remove all values
		 *
		 * @return void
		 */
		public function removeAll() {
			if (!$this->isLoaded) {
				$this->load();
			}
			$this->content = array();
			if ($this->saveImmediately) {
				$this->save();
			}
		}

	}
?>