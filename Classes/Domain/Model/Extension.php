<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
	 *           Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	 * Extension container
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Domain_Model_Extension extends Tx_Extbase_DomainObject_AbstractEntity {

		/**
		 * extKey
		 * @var string
		 * @validate NotEmpty
		 */
		protected $extKey;

		/**
		 * forgeLink
		 * @var string
		 */
		protected $forgeLink;

		/**
		 * hudsonLink
		 * @var string
		 */
		protected $hudsonLink;

		/**
		 * lastUpdate
		 * @var DateTime
		 */
		protected $lastUpdate;

		/**
		 * categories
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Category>
		 */
		protected $categories;

		/**
		 * tags
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Tag>
		 */
		protected $tags;

		/**
		 * versions
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Version>
		 */
		protected $versions;


		/**
		 * Constructor. Initializes all Tx_Extbase_Persistence_ObjectStorage instances.
		 */
		public function __construct() {
			$this->categories = new Tx_Extbase_Persistence_ObjectStorage();
			$this->tags       = new Tx_Extbase_Persistence_ObjectStorage();
			$this->versions   = new Tx_Extbase_Persistence_ObjectStorage();
		}


		/**
		 * Setter for extKey
		 *
		 * @param string $extKey extKey
		 * @return void
		 */
		public function setExtKey($extKey) {
			$this->extKey = $extKey;
		}


		/**
		 * Getter for extKey
		 *
		 * @return string extKey
		 */
		public function getExtKey() {
			return $this->extKey;
		}


		/**
		 * Setter for forgeLink
		 *
		 * @param string $forgeLink forgeLink
		 * @return void
		 */
		public function setForgeLink($forgeLink) {
			$this->forgeLink = $forgeLink;
		}


		/**
		 * Getter for forgeLink
		 *
		 * @return string forgeLink
		 */
		public function getForgeLink() {
			return $this->forgeLink;
		}


		/**
		 * Setter for hudsonLink
		 *
		 * @param string $hudsonLink hudsonLink
		 * @return void
		 */
		public function setHudsonLink($hudsonLink) {
			$this->hudsonLink = $hudsonLink;
		}


		/**
		 * Getter for hudsonLink
		 *
		 * @return string hudsonLink
		 */
		public function getHudsonLink() {
			return $this->hudsonLink;
		}


		/**
		 * Setter for lastUpdate
		 *
		 * @param DataTime $lastUpdate lastUpdate
		 * @return void
		 */
		public function setLastUpdate($lastUpdate) {
			$this->lastUpdate = $lastUpdate;
		}


		/**
		 * Getter for lastUpdate
		 *
		 * @return DateTime lastUpdate
		 */
		public function getLastUpdate() {
			return $this->lastUpdate;
		}


		/**
		 * Getter for categories
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Category> categories
		 */
		public function getCategories() {
			return $this->categories;
		}


		/**
		 * Adds a category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category The Category to be added
		 * @return void
		 */
		public function addCategory(Tx_TerFe2_Domain_Model_Category $category) {
			$this->categories->attach($category);
		}


		/**
		 * Removes a category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category The Category to be removed
		 * @return void
		 */
		public function removeCategory(Tx_TerFe2_Domain_Model_Category $category) {
			$this->categories->detach($category);
		}


		/**
		 * Getter for tags
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Tag> tags
		 */
		public function getTags() {
			return $this->tags;
		}


		/**
		 * Adds a Tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag The Tag to be added
		 * @return void
		 */
		public function addTag(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tags->attach($tag);
		}


		/**
		 * Removes a Tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag The Tag to be removed
		 * @return void
		 */
		public function removeTag(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tags->detach($tag);
		}


		/**
		 * Getter for versions
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Version> versions
		 */
		public function getVersions() {
			return $this->versions;
		}


		/**
		 * Adds a Version
		 *
		 * @param Tx_TerFe2_Domain_Model_Version The Version to be added
		 * @return void
		 */
		public function addVersion(Tx_TerFe2_Domain_Model_Version $version) {
			$this->versions->attach($version);
		}


		/**
		 * Removes a Version
		 *
		 * @param Tx_TerFe2_Domain_Model_Version The Version to be removed
		 * @return void
		 */
		public function removeVersion(Tx_TerFe2_Domain_Model_Version $version) {
			$this->versions->detach($version);
		}

	}
?>