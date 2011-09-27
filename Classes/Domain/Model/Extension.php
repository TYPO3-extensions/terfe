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
	 * Extension container
	 */
	class Tx_TerFe2_Domain_Model_Extension extends Tx_TerFe2_Domain_Model_AbstractEntity {

		/**
		 * Extension key
		 * @var string
		 * @validate NotEmpty
		 */
		protected $extKey;

		/**
		 * Link to forge project
		 * @var string
		 */
		protected $forgeLink;

		/**
		 * Link to hudson
		 * @var string
		 */
		protected $hudsonLink;

		/**
		 * Last update
		 * @var DateTime
		 */
		protected $lastUpdate;

		/**
		 * Categories
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Category>
		 */
		protected $categories;

		/**
		 * Tags
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Tag>
		 */
		protected $tags;

		/**
		 * Versions
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Version>
		 * @lazy
		 */
		protected $versions;

		/**
		 * Last version
		 * @var Tx_TerFe2_Domain_Model_Version
		 */
		protected $lastVersion;

		/**
		 * Frontend user
		 * @var string
		 */
		protected $frontendUser;

		/**
		 * Sum of all version downloads
		 * @var integer
		 */
		protected $downloads;


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
		 * Setter for lastUpload
		 *
		 * @param DateTime $lastUpload lastUpload
		 * @return void
		 */
		public function setLastUpload(DateTime $lastUpload) {
			$this->lastUpload = $lastUpload;
		}


		/**
		 * Getter for lastUpdate
		 *
		 * @return DateTime lastUpload
		 */
		public function getLastUpload() {
			return $this->lastUpload;
		}


		/**
		 * Setter for lastMaintained
		 *
		 * @param DateTime $lastMaintained lastMaintained
		 * @return void
		 */
		public function setLastMaintained(DateTime $lastMaintained) {
			$this->lastMaintained = $lastMaintained;
		}


		/**
		 * Getter for lastMaintained
		 *
		 * @return DateTime lastMaintained
		 */
		public function getLastMaintained() {
			return $this->lastMaintained;
		}


		/**
		 * Getter for categories
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Category> Categories
		 */
		public function getCategories() {
			return $this->categories;
		}


		/**
		 * Adds a category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $category The category to be added
		 * @return void
		 */
		public function addCategory(Tx_TerFe2_Domain_Model_Category $category) {
			$this->categories->attach($category);
		}


		/**
		 * Removes a category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $category The category to be removed
		 * @return void
		 */
		public function removeCategory(Tx_TerFe2_Domain_Model_Category $category) {
			$this->categories->detach($category);
		}

		/**
		 * Removes all categories
		 *
		 * @return void
		 */
		public function removeAllCategories() {

			/**
			 * @see The removeAll function is not removing all objects so I added this feature like
			 * seen on: http://www.php.net/manual/en/splobjectstorage.detach.php
			 */

			$this->categories->rewind();
			while ($this->categories->valid()) {
				$tempObject = $this->categories->current();
				$this->categories->next();
				$this->categories->detach($tempObject);
			}
		}


		/**
		 * Getter for tags
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Tag> Tags
		 */
		public function getTags() {
			return $this->tags;
		}


		/**
		 * Adds a Tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag The tag to be added
		 * @return void
		 */
		public function addTag(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tags->attach($tag);
		}


		/**
		 * Removes a Tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag The tag to be removed
		 * @return void
		 */
		public function removeTag(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tags->detach($tag);
		}


		/**
		 * Getter for versions
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Version> Versions
		 */
		public function getVersions() {
			return $this->versions;
		}


		/**
		 * Get versions sorted by upload date
		 *
		 * @return array Versions
		 */
		public function getVersionsByDate() {
			$versions = array();
			foreach ($this->versions as $version) {
				$versions[$version->getUploadDate()] = $version;
			}
			ksort($versions);
			return $versions;
		}


		/**
		 * Get versions reverse sorted by upload date
		 *
		 * @return array Versions
		 */
		public function getReverseVersionsByDate() {
			$versions = array();
			foreach ($this->versions as $version) {
				$versions[$version->getUploadDate()] = $version;
			}
			krsort($versions);
			return $versions;
		}


		/**
		 * Returns the count of versions
		 *
		 * @return integer Version count
		 */
		public function getVersionCount() {
			return (!empty($this->versions) ? count($this->versions) : 0);
		}


		/**
		 * Adds a Version
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version The version to be added
		 * @return void
		 */
		public function addVersion(Tx_TerFe2_Domain_Model_Version $version) {
			$this->versions->attach($version);
			$this->setLastVersion($version);
			$this->addDownloads((int) $version->getAllDownloads());
		}


		/**
		 * Setter for lastVersion, will only set if given version is newer than existing one
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $lastVersion lastVersion
		 * @return void
		 */
		public function setLastVersion(Tx_TerFe2_Domain_Model_Version $lastVersion) {
			if (empty($this->lastVersion)) {
				$this->lastVersion = $lastVersion;
				return;
			}

			$curVersionNumber = (int) $this->lastVersion->getVersionNumber();
			$newVersionNumber = (int) $lastVersion->getVersionNumber();

				// Add lastVersion only if newer
			if ($newVersionNumber > $curVersionNumber) {
				$this->lastVersion = $lastVersion;
			}
		}


		/**
		 * Getter for lastVersion
		 *
		 * @return Tx_TerFe2_Domain_Model_Version lastVersion
		 */
		public function getLastVersion() {
			return $this->lastVersion;
		}


		/**
		 * Setter for frontendUser
		 *
		 * @param string $frontendUser Frontend user
		 * @return void
		 */
		public function setFrontendUser($frontendUser) {
			$this->frontendUser = $frontendUser;
		}


		/**
		 * Getter for frontendUser
		 *
		 * @return string Frontend user
		 */
		public function getFrontendUser() {
			return $this->frontendUser;
		}


		/**
		 * Returns all votes for the extension
		 *
		 * @return array Vote counts
		 */
		public function getVotes() {
			$votes = array(
				'positive' => 0,
				'negative' => 0,
			);

			foreach ($this->versions as $version) {
				$experiences = $version->getExperiences();
				if (!is_array($experiences)) {
					continue;
				}
				foreach ($experiences as $experience) {
					$rating = (int) $experience->getRating();
					if ($rating > 0) {
						$votes['positive'] += $rating;
					} else {
						$votes['negative'] += $rating;
					}
				}
			}

			return $votes;
		}


		/**
		 * Set all downloads sum
		 *
		 * @param integer $downloads Count of downloads
		 * @return void
		 */
		public function setDownloads($downloads) {
			$this->downloads = (int) $downloads;
		}


		/**
		 * Add downloads to all downloads sum
		 *
		 * @param integer $downloads Count of downloads to add
		 * @return void
		 */
		public function addDownloads($downloads) {
			$this->downloads += (int) $downloads;
		}


		/**
		 * Get sum of all version downloads
		 *
		 * @return integer All downloads
		 */
		public function getDownloads() {
			return (int) $this->downloads;
		}


		/**
		 * Recalculate sum of all downloads
		 *
		 * @return void
		 */
		public function recalculateDownloads() {
			$downloads = 0;
			$versions = $this->getVersions();

			foreach ($versions as $version) {
				$downloads += (int) $version->getAllDownloads();
			}

			if (!empty($downloads)) {
				$this->setDownloads($downloads);
			}
		}


	}
?>