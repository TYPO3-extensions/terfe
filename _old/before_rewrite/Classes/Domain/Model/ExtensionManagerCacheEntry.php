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
	 * Extension manager cache entry
	 */
	class Tx_TerFe2_Domain_Model_ExtensionManagerCacheEntry extends Tx_Extbase_DomainObject_AbstractEntity {

		/**
		 * Extension key
		 * @var string
		 */
		protected $extKey;

		/**
		 * Repository id
		 * @var integer
		 */
		protected $repositoryId;

		/**
		 * Version string
		 * @var string
		 */
		protected $versionString;

		/**
		 * Version number
		 * @var integer
		 */
		protected $versionNumber;

		/**
		 * Sum of all downloads
		 * @var integer
		 */
		protected $allDownloads;

		/**
		 * Downloads of this version
		 * @var integer
		 */
		protected $downloads;

		/**
		 * Title
		 * @var string
		 */
		protected $title;

		/**
		 * Description
		 * @var string
		 */
		protected $description;

		/**
		 * State
		 * @var integer
		 */
		protected $state;

		/**
		 * Review state
		 * @var integer
		 */
		protected $reviewState;

		/**
		 * Category
		 * @var integer
		 */
		protected $category;

		/**
		 * Upload date
		 * @var integer
		 */
		protected $uploadDate;

		/**
		 * Upload comment
		 * @var string
		 */
		protected $uploadComment;

		/**
		 * Dependencies
		 * @var string
		 */
		protected $dependencies;

		/**
		 * Name of the author
		 * @var string
		 */
		protected $authorName;

		/**
		 * Email of the author
		 * @var string
		 */
		protected $authorEmail;

		/**
		 * Company of the author
		 * @var string
		 */
		protected $authorCompany;

		/**
		 * Username of the owner
		 * @var string
		 */
		protected $ownerUsername;

		/**
		 * File hash
		 * @var string
		 */
		protected $fileHash;

		/**
		 * Is current the last version
		 * @var boolean
		 */
		protected $isLastVersion;

		/**
		 * Last reviewed version
		 * @var integer
		 */
		protected $lastReviewedVersion;


		/**
		 * Setter for extKey
		 *
		 * @param string $extKey Extension key
		 * @return void
		 */
		public function setExtKey($extKey) {
			$this->extKey = $extKey;
		}


		/**
		 * Getter for extKey
		 *
		 * @return string Extension key
		 */
		public function getExtKey() {
			return $this->extKey;
		}


		/**
		 * Setter for repositoryId
		 *
		 * @param integer $repositoryId Repository id
		 * @return void
		 */
		public function setRepositoryId($repositoryId) {
			$this->repositoryId = $repositoryId;
		}


		/**
		 * Getter for repositoryId
		 *
		 * @return integer Repository id
		 */
		public function getRepositoryId() {
			return $this->repositoryId;
		}


		/**
		 * Setter for versionString
		 *
		 * @param string $versionString Version string
		 * @return void
		 */
		public function setVersionString($versionString) {
			$this->versionString = $versionString;
		}


		/**
		 * Getter for versionString
		 *
		 * @return string Version string
		 */
		public function getVersionString() {
			return $this->versionString;
		}


		/**
		 * Setter for versionNumber
		 *
		 * @param integer $versionNumber Version number
		 * @return void
		 */
		public function setVersionNumber($versionNumber) {
			$this->versionNumber = $versionNumber;
		}


		/**
		 * Getter for versionNumber
		 *
		 * @return integer Version number
		 */
		public function getVersionNumber() {
			return $this->versionNumber;
		}


		/**
		 * Setter for allDownloads
		 *
		 * @param integer $allDownloads Sum of all downloads
		 * @return void
		 */
		public function setAllDownloads($allDownloads) {
			$this->allDownloads = $allDownloads;
		}


		/**
		 * Getter for allDownloads
		 *
		 * @return integer Sum of all downloads
		 */
		public function getAllDownloads() {
			return $this->allDownloads;
		}


		/**
		 * Setter for downloads
		 *
		 * @param integer $downloads Download count
		 * @return void
		 */
		public function setDownloads($downloads) {
			$this->downloads = $downloads;
		}


		/**
		 * Getter for downloads
		 *
		 * @return integer Download count
		 */
		public function getDownloads() {
			return $this->downloads;
		}


		/**
		 * Setter for title
		 *
		 * @param string $title Title
		 * @return void
		 */
		public function setTitle($title) {
			$this->title = $title;
		}


		/**
		 * Getter for title
		 *
		 * @return string Title
		 */
		public function getTitle() {
			return $this->title;
		}


		/**
		 * Setter for description
		 *
		 * @param string $description Description
		 * @return void
		 */
		public function setDescription($description) {
			$this->description = $description;
		}


		/**
		 * Getter for description
		 *
		 * @return string Description
		 */
		public function getDescription() {
			return $this->description;
		}


		/**
		 * Setter for state
		 *
		 * @param integer $state State
		 * @return void
		 */
		public function setState($state) {
			$this->state = $state;
		}


		/**
		 * Getter for state
		 *
		 * @return integer State
		 */
		public function getState() {
			return $this->state;
		}


		/**
		 * Setter for reviewState
		 *
		 * @param integer $reviewState Review state
		 * @return void
		 */
		public function setReviewState($reviewState) {
			$this->reviewState = $reviewState;
		}


		/**
		 * Getter for reviewState
		 *
		 * @return integer Review state
		 */
		public function getReviewState() {
			return $this->reviewState;
		}


		/**
		 * Setter for category
		 *
		 * @param integer $category Category
		 * @return void
		 */
		public function setCategory($category) {
			$this->category = $category;
		}


		/**
		 * Getter for category
		 *
		 * @return integer Category
		 */
		public function getCategory() {
			return $this->category;
		}


		/**
		 * Setter for uploadDate
		 *
		 * @param integer $uploadDate Upload date
		 * @return void
		 */
		public function setUploadDate($uploadDate) {
			$this->uploadDate = $uploadDate;
		}


		/**
		 * Getter for uploadDate
		 *
		 * @return integer Upload date
		 */
		public function getUploadDate() {
			return $this->uploadDate;
		}


		/**
		 * Setter for uploadComment
		 *
		 * @param string $uploadComment Upload comment
		 * @return void
		 */
		public function setUploadComment($uploadComment) {
			$this->uploadComment = $uploadComment;
		}


		/**
		 * Getter for uploadComment
		 *
		 * @return string Upload comment
		 */
		public function getUploadComment() {
			return $this->uploadComment;
		}


		/**
		 * Setter for dependencies
		 *
		 * @param string $dependencies Dependencies
		 * @return void
		 */
		public function setDependencies($dependencies) {
			$this->dependencies = $dependencies;
		}


		/**
		 * Getter for dependencies
		 *
		 * @return array Dependencies
		 */
		public function getDependencies() {
			return unserialize($this->dependencies);
		}


		/**
		 * Setter for authorName
		 *
		 * @param string $authorName Author name
		 * @return void
		 */
		public function setAuthorName($authorName) {
			$this->authorName = $authorName;
		}


		/**
		 * Getter for authorName
		 *
		 * @return string Author name
		 */
		public function getAuthorName() {
			return $this->authorName;
		}


		/**
		 * Setter for authorEmail
		 *
		 * @param string $authorEmail Author email
		 * @return void
		 */
		public function setAuthorEmail($authorEmail) {
			$this->authorEmail = $authorEmail;
		}


		/**
		 * Getter for authorEmail
		 *
		 * @return string Author email
		 */
		public function getAuthorEmail() {
			return $this->authorEmail;
		}


		/**
		 * Setter for authorCompany
		 *
		 * @param string $authorCompany Author company
		 * @return void
		 */
		public function setAuthorCompany($authorCompany) {
			$this->authorCompany = $authorCompany;
		}


		/**
		 * Getter for authorCompany
		 *
		 * @return string Author company
		 */
		public function getAuthorCompany() {
			return $this->authorCompany;
		}


		/**
		 * Setter for ownerUsername
		 *
		 * @param string $ownerUsername Username of the owner
		 * @return void
		 */
		public function setOwnerUsername($ownerUsername) {
			$this->ownerUsername = $ownerUsername;
		}


		/**
		 * Getter for ownerUsername
		 *
		 * @return string Username of the owner
		 */
		public function getOwnerUsername() {
			return $this->ownerUsername;
		}


		/**
		 * Setter for fileHash
		 *
		 * @param string $fileHash File hash
		 * @return void
		 */
		public function setFileHash($fileHash) {
			$this->fileHash = $fileHash;
		}


		/**
		 * Getter for fileHash
		 *
		 * @return string File hash
		 */
		public function getFileHash() {
			return $this->fileHash;
		}


		/**
		 * Setter for isLastVersion
		 *
		 * @param boolean $isLastVersion File hash
		 * @return void
		 */
		public function setIsLastVersion($isLastVersion) {
			$this->isLastVersion = $isLastVersion;
		}


		/**
		 * Getter for isLastVersion
		 *
		 * @return boolean File hash
		 */
		public function getIsLastVersion() {
			return $this->isLastVersion;
		}


		/**
		 * Setter for lastReviewedVersion
		 *
		 * @param integer $fileHash Last reviewed version
		 * @return void
		 */
		public function setLastReviewedVersion($lastReviewedVersion) {
			$this->lastReviewedVersion = $lastReviewedVersion;
		}


		/**
		 * Getter for lastReviewedVersion
		 *
		 * @return integer Last reviewed version
		 */
		public function getLastReviewedVersion() {
			return $this->lastReviewedVersion;
		}

	}
?>