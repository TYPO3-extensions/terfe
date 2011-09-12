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
	 * Utilities to manage cache content
	 */
	class Tx_TerFe2_Persistence_Cache extends Tx_TerFe2_Persistence_AbstractPersistence {

		/**
		 * @var t3lib_cache_frontend_Frontend
		 */
		protected $cacheFrontend;


		/**
		 * Load content
		 *
		 * @return void
		 */
		public function load() {
			if (!$this->isLoaded()) {
				$this->cacheFrontend = $this->getCacheFrontend($this->name);
				$this->setIsLoaded(TRUE);
			}
		}


		/**
		 * Save content
		 *
		 * @return void
		 */
		public function save() {
			// Cache entries are stored immii
		}


		/**
		 * Set entry
		 *
		 * @param string $key Name of the entry
		 * @param mixed $entry Entry content
		 * @param array $tags Tags for the entry
		 * @param integer $lifetime Lifetime of this cache entry in seconds
		 * @return void
		 */
		public function set($key, $entry, array $tags = array(), $lifetime = NULL) {
			$this->checkKey($tag);

			if (!$this->isLoaded()) {
				$this->load();
			}

			$entry = json_encode($entry);
			$this->cacheFrontend->set($key, $entry, $tags, $lifetime);
		}


		/**
		 * Add entry
		 *
		 * @param string $key Name of the entry
		 * @param mixed $entry Entry content
		 * @param array $tags Tags for the entry
		 * @param integer $lifetime Lifetime of this cache entry in seconds
		 * @return void
		 */
		public function add($key, $entry, array $tags = array(), $lifetime = NULL) {
			$this->set($key, $entry, $tags, $lifetime);
		}


		/**
		 * Add multiple entrys
		 *
		 * @param array $entries Key <-> Value (Tags) (Lifetime)
		 * @return void
		 */
		public function addMultiple(array $entries) {
			foreach ($entries as $key => $entry) {
				$value    = (!empty($entry['value'])    ? $entry['value']    : '');
				$tags     = (!empty($entry['tags'])     ? $entry['tags']     : array());
				$lifetime = (!empty($entry['lifetime']) ? $entry['lifetime'] : NULL);
				$this->add($key, $value, $tags, $lifetime);
			}
		}


		/**
		 * Check if content contains given key
		 *
		 * @param string $key Name of the entry
		 * @return boolean TRUE if exists
		 */
		public function has($key) {
			if (!$this->isLoaded()) {
				$this->load();
			}
			$this->checkKey($key);
			return $this->cacheFrontend->has($key);
		}


		/**
		 * Get entry
		 *
		 * @param string $key Name of the entry
		 * @return mixed Entry content
		 */
		public function get($key) {
			if (!$this->isLoaded()) {
				$this->load();
			}
			$this->checkKey($key);
			$this->cacheFrontend->get($key);
		}


		/**
		 * Get all values
		 *
		 * @return array Key <-> value pairs
		 */
		public function getAll() {
			throw new Exception('Method "getAll" is not supported in cache persistence');
		}


		/**
		 * Get all entries by tag
		 *
		 * @param string $tag Tag of the entry
		 * @return array Key <-> Value pairs
		 */
		public function getAllByTag($tag) {
			if (!$this->isLoaded()) {
				$this->load();
			}
			$this->checkTag($tag);
			$this->cacheFrontend->getByTag($tag);
		}


		/**
		 * Remove a entry
		 *
		 * @param string $key Name of the entry
		 * @return void
		 */
		public function remove($key) {
			if (!$this->isLoaded()) {
				$this->load();
			}
			$this->checkKey($key);
			$this->cacheFrontend->remove($key);
		}


		/**
		 * Remove all entries
		 *
		 * @return void
		 */
		public function removeAll() {
			if (!$this->isLoaded()) {
				$this->load();
			}
			$this->cacheFrontend->flush();
		}


		/**
		 * Remove all entries
		 *
		 * @param string $tag Tag of the entry
		 * @return void
		 */
		public function removeAllByTag($tag) {
			if (!$this->isLoaded()) {
				$this->load();
			}
			$this->checkTag($tag);
			$this->cacheFrontend->flushByTag($tag);
		}


		/**
		 * Returns an instance of the cache frontend
		 * 
		 * @param string $name Name of the cache to load
		 * @return Instance of the cache frontend
		 */
		public function getCacheFrontend($name) {
			if (empty($name)) {
				throw new Exception('Can not load caching frontend without a name');
			}

			t3lib_cache::initializeCachingFramework();

			try {
				return $GLOBALS['typo3CacheManager']->getCache($name);
			} catch (t3lib_cache_exception_NoSuchCache $exception) {
				if (empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$name])) {
					throw new Exception('A caching frontend with name "' . $name . '" is not configured');
				}

				return $GLOBALS['typo3CacheFactory']->create(
					$name,
					$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$name]['frontend'],
					$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$name]['backend'],
					$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$name]['options']
				);
			}
		}


		/**
		 * Check key
		 * 
		 * @param string $key Key to check
		 * @return boolean TRUE if success
		 */
		protected function checkKey($key) {
			if (empty($key) || !is_string($key)) {
				throw new Exception('Only non-empty keys of type "string" are allowed');
			}
			return TRUE;
		}


		/**
		 * Check tag
		 * 
		 * @param string $tag Tag to check
		 * @return boolean TRUE if success
		 */
		protected function checkTag($tag) {
			if (empty($tag) || !is_string($tag)) {
				throw new Exception('Only non-empty tags of type "string" are allowed');
			}
			return TRUE;
		}

	}
?>