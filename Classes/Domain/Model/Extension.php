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
		 * category
		 * @var Tx_TerFe2_Domain_Model_Category
		 */
		protected $category;

		/**
		 * tag
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Tag>
		 */
		protected $tag;

		/**
		 * version
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Version>
		 */
		protected $version;


		/**
		 * Constructor. Initializes all Tx_Extbase_Persistence_ObjectStorage instances.
		 */
		public function __construct() {
			$this->tag     = new Tx_Extbase_Persistence_ObjectStorage();
			$this->version = new Tx_Extbase_Persistence_ObjectStorage();
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
		 * @param DateTime $lastUpdate
		 * @return void
		 */
		public function setLastUpdate(DateTime $lastUpdate) {
			$this->lastUpdate = $lastUpdate;
		}


		/**
		 * Getter for lastUpdate
		 *
		 * @return DateTime
		 */
		public function getLastUpdate() {
			return $this->lastUpdate;
		}


		/**
		 * Setter for category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $category category
		 * @return void
		 */
		public function setCategory(Tx_TerFe2_Domain_Model_Category $category) {
			$this->category = $category;
		}


		/**
		 * Getter for category
		 *
		 * @return Tx_TerFe2_Domain_Model_Category category
		 */
		public function getCategory() {
			return $this->category;
		}


		/**
		 * Setter for tag
		 *
		 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Tag> $tag tag
		 * @return void
		 */
		public function setTag(Tx_Extbase_Persistence_ObjectStorage $tag) {
			$this->tag = $tag;
		}


		/**
		 * Getter for tag
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Tag> tag
		 */
		public function getTag() {
			return $this->tag;
		}


		/**
		 * Adds a Tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag The Tag to be added
		 * @return void
		 */
		public function addTag(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tag->attach($tag);
		}


		/**
		 * Removes a Tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag The Tag to be removed
		 * @return void
		 */
		public function removeTag(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tag->detach($tag);
		}


		/**
		 * Setter for version
		 *
		 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Version> $version version
		 * @return void
		 */
		public function setVersion(Tx_Extbase_Persistence_ObjectStorage $version) {
			$this->version = $version;
		}


		/**
		 * Getter for version
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Version> version
		 */
		public function getVersion() {
			return $this->version;
		}


		/**
		 * Adds a Version
		 *
		 * @param Tx_TerFe2_Domain_Model_Version The Version to be added
		 * @return void
		 */
		public function addVersion(Tx_TerFe2_Domain_Model_Version $version) {
			$this->version->attach($version);
		}


		/**
		 * Removes a Version
		 *
		 * @param Tx_TerFe2_Domain_Model_Version The Version to be removed
		 * @return void
		 */
		public function removeVersion(Tx_TerFe2_Domain_Model_Version $version) {
			$this->version->detach($version);
		}

	}
?>