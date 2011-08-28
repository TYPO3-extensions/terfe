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
	 * Single version of an extension
	 */
	class Tx_TerFe2_Domain_Model_Version extends Tx_Extbase_DomainObject_AbstractEntity {

		/**
		 * Title of the extension
		 * @var string
		 * @validate NotEmpty
		 */
		protected $title;

		/**
		 * Description of the extension
		 * @var string
		 */
		protected $description;

		/**
		 * Hash of the t3x file
		 * @var string
		 */
		protected $fileHash;

		/**
		 * Author
		 * @var Tx_TerFe2_Domain_Model_Author
		 * @lazy
		 */
		protected $author;

		/**
		 * The version number as integer
		 * @var integer
		 * @validate NotEmpty
		 */
		protected $versionNumber;

		/**
		 * The version number in format "x.x.x"
		 * @var string
		 * @validate NotEmpty
		 */
		protected $versionString;

		/**
		 * Upload date and time
		 * @var integer
		 * @validate NotEmpty
		 */
		protected $uploadDate;

		/**
		 * The user comment for this version
		 * @var string
		 */
		protected $uploadComment;

		/**
		 * How many downloads for this version
		 * @var integer
		 */
		protected $downloadCounter;

		/**
		 * State of the extension (beta, stable, obsolete, etc.)
		 * @var string
		 * @validate NotEmpty
		 */
		protected $state;

		/**
		 * The category of the extension manager (frontend plugin, backend module, etc.)
		 * @var string
		 * @validate NotEmpty
		 */
		protected $emCategory;

		/**
		 * Loading order in Extension Manager
		 * @var string
		 */
		protected $loadOrder;

		/**
		 * Priority
		 * @var string
		 */
		protected $priority;

		/**
		 * Information if extension is shy
		 * @var boolean
		 */
		protected $shy;

		/**
		 * Internal
		 * @var boolean
		 */
		protected $internal;

		/**
		 * Whether extension will be loaded in Frontend or not
		 * @var boolean
		 */
		protected $doNotLoadInFe;

		/**
		 * Whether an upload folder will be created or not
		 * @var boolean
		 */
		protected $uploadfolder;

		/**
		 * Whether to clear cache on load or not
		 * @var boolean
		 */
		protected $clearCacheOnLoad;

		/**
		 * Module identifier
		 * @var string
		 */
		protected $module;

		/**
		 * Names of dirs to create on load
		 * @var string
		 */
		protected $createDirs;

		/**
		 * Information which existing DB tables will be modified
		 * @var string
		 */
		protected $modifyTables;

		/**
		 * Lock type
		 * @var string
		 */
		protected $lockType;

		/**
		 * CGL compliance
		 * @var string
		 */
		protected $cglCompliance;

		/**
		 * Note for the CGL compliance
		 * @var string
		 */
		protected $cglComplianceNote;

		/**
		 * Review state
		 * @var integer
		 */
		protected $reviewState;

		/**
		 * Relation to manual object of ter_doc extension
		 * @var string
		 */
		protected $manual;

		/**
		 * media
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Media>
		 */
		protected $media;

		/**
		 * experience
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Experience>
		 */
		protected $experience;

		/**
		 * softwareRelation
		 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Relation>
		 */
		protected $softwareRelation;

		/**
		 * extension
		 * @var Tx_TerFe2_Domain_Model_Extension
		 * @lazy
		 */
		protected $extension;

		/**
		 * Extension Provider
		 * @var string
		 */
		protected $extensionProvider;


		/**
		 * Constructor. Initializes all Tx_Extbase_Persistence_ObjectStorage instances.
		 */
		public function __construct() {
			$this->media            = new Tx_Extbase_Persistence_ObjectStorage();
			$this->experience       = new Tx_Extbase_Persistence_ObjectStorage();
			$this->softwareRelation = new Tx_Extbase_Persistence_ObjectStorage();
		}


		/**
		 * Setter for title
		 *
		 * @param string $title Title of the extension
		 * @return void
		 */
		public function setTitle($title) {
			$this->title = $title;
		}


		/**
		 * Getter for title
		 *
		 * @return string Title of the extension
		 */
		public function getTitle() {
			return $this->title;
		}


		/**
		 * Setter for description
		 *
		 * @param string $description Description of the extension
		 * @return void
		 */
		public function setDescription($description) {
			$this->description = $description;
		}


		/**
		 * Getter for description
		 *
		 * @return string Description of the extension
		 */
		public function getDescription() {
			return $this->description;
		}


		/**
		 * Setter for fileHash
		 *
		 * @param string $fileHash Hash of the t3x file
		 * @return void
		 */
		public function setFileHash($fileHash) {
			$this->fileHash = $fileHash;
		}


		/**
		 * Getter for fileHash
		 *
		 * @return string Hash of the t3x file
		 */
		public function getFileHash() {
			return $this->fileHash;
		}

		/**
		 * Setter for Author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author Author
		 * @return void
		 */
		public function setAuthor(Tx_TerFe2_Domain_Model_Author $author) {
			$this->author = $author;
		}


		/**
		 * Getter for Author
		 *
		 * @return Tx_TerFe2_Domain_Model_Author Author
		 */
		public function getAuthor() {
			return $this->author;
		}


		/**
		 * Setter for versionNumber
		 *
		 * @param integer $versionNumber The version number
		 * @return void
		 */
		public function setVersionNumber($versionNumber) {
			$this->versionNumber = $versionNumber;
		}


		/**
		 * Getter for versionNumber
		 *
		 * @return integer The version number
		 */
		public function getVersionNumber() {
			return $this->versionNumber;
		}


		/**
		 * Setter for versionString
		 *
		 * @param string $versionString The version number in format "x.x.x"
		 * @return void
		 */
		public function setVersionString($versionString) {
			$this->versionString = $versionString;
		}


		/**
		 * Getter for versionString
		 *
		 * @return string The version number in format "x.x.x"
		 */
		public function getVersionString() {
			return $this->versionString;
		}


		/**
		 * Setter for uploadDate
		 *
		 * @param integer $uploadDate Upload date and time
		 * @return void
		 */
		public function setUploadDate($uploadDate) {
			$this->uploadDate = $uploadDate;
		}


		/**
		 * Getter for uploadDate
		 *
		 * @return integer Upload date and time
		 */
		public function getUploadDate() {
			return $this->uploadDate;
		}


		/**
		 * Setter for uploadComment
		 *
		 * @param string $uploadComment The user comment for this version
		 * @return void
		 */
		public function setUploadComment($uploadComment) {
			$this->uploadComment = $uploadComment;
		}


		/**
		 * Getter for uploadComment
		 *
		 * @return string The user comment for this version
		 */
		public function getUploadComment() {
			return $this->uploadComment;
		}


		/**
		 * Setter for downloadCounter
		 *
		 * @param integer $downloadCounter How many downloads for this version
		 * @return void
		 */
		public function setDownloadCounter($downloadCounter) {
			$this->downloadCounter = $downloadCounter;
		}


		/**
		 * Increment downloadCounter
		 *
		 * @return void
		 */
		public function incrementDownloadCounter() {
			$this->downloadCounter++;
		}


		/**
		 * Getter for downloadCounter
		 *
		 * @return integer How many downloads for this version
		 */
		public function getDownloadCounter() {
			return $this->downloadCounter;
		}


		/**
		 * Setter for state
		 *
		 * @param string $state State of the extension (beta, stable, obsolete, etc.)
		 * @return void
		 */
		public function setState($state) {
			$this->state = $state;
		}


		/**
		 * Getter for state
		 *
		 * @return string State of the extension (beta, stable, obsolete, etc.)
		 */
		public function getState() {
			return $this->state;
		}


		/**
		 * Setter for emCategory
		 *
		 * @param string $emCategory The category of the extension manager (frontend plugin, backend module, etc.)
		 * @return void
		 */
		public function setEmCategory($emCategory) {
			$this->emCategory = $emCategory;
		}


		/**
		 * Getter for emCategory
		 *
		 * @return string The category of the extension manager (frontend plugin, backend module, etc.)
		 */
		public function getEmCategory() {
			return $this->emCategory;
		}


		/**
		 * Setter for loadOrder
		 *
		 * @param string $loadOrder Loading order in Extension Manager
		 * @return void
		 */
		public function setLoadOrder($loadOrder) {
			$this->loadOrder = $loadOrder;
		}


		/**
		 * Getter for loadOrder
		 *
		 * @return string Loading order in Extension Manager
		 */
		public function getLoadOrder() {
			return $this->loadOrder;
		}


		/**
		 * Setter for priority
		 *
		 * @param string $priority Priority
		 * @return void
		 */
		public function setPriority($priority) {
			$this->priority = $priority;
		}


		/**
		 * Getter for priority
		 *
		 * @return string Priority
		 */
		public function getPriority() {
			return $this->priority;
		}


		/**
		 * Setter for shy
		 *
		 * @param boolean $shy Information if extension is shy
		 * @return void
		 */
		public function setShy($shy) {
			$this->shy = $shy;
		}


		/**
		 * Getter for shy
		 *
		 * @return boolean Information if extension is shy
		 */
		public function getShy() {
			return $this->shy;
		}


		/**
		 * Setter for internal
		 *
		 * @param boolean $internal Internal
		 * @return void
		 */
		public function setInternal($internal) {
			$this->internal = $internal;
		}


		/**
		 * Getter for internal
		 *
		 * @return boolean Internal
		 */
		public function getInternal() {
			return $this->internal;
		}


		/**
		 * Setter for module
		 *
		 * @param string $module Module identifier
		 * @return void
		 */
		public function setModule($module) {
			$this->module = $module;
		}


		/**
		 * Getter for module
		 *
		 * @return string Module identifier
		 */
		public function getModule() {
			return $this->module;
		}


		/**
		 * Setter for doNotLoadInFe
		 *
		 * @param boolean $doNotLoadInFe Whether extension will be loaded in Frontend or not
		 * @return void
		 */
		public function setDoNotLoadInFe($doNotLoadInFe) {
			$this->doNotLoadInFe = $doNotLoadInFe;
		}


		/**
		 * Getter for doNotLoadInFe
		 *
		 * @return boolean Whether extension will be loaded in Frontend or not
		 */
		public function getDoNotLoadInFe() {
			return $this->doNotLoadInFe;
		}


		/**
		 * Setter for uploadfolder
		 *
		 * @param boolean $uploadfolder Whether an upload folder will be created or not
		 * @return void
		 */
		public function setUploadfolder($uploadfolder) {
			$this->uploadfolder = $uploadfolder;
		}


		/**
		 * Getter for uploadfolder
		 *
		 * @return boolean Whether an upload folder will be created or not
		 */
		public function getUploadfolder() {
			return $this->uploadfolder;
		}


		/**
		 * Setter for createDirs
		 *
		 * @param string $createDirs Names of dirs to create on load
		 * @return void
		 */
		public function setCreateDirs($createDirs) {
			$this->createDirs = $createDirs;
		}


		/**
		 * Getter for createDirs
		 *
		 * @return string Names of dirs to create on load
		 */
		public function getCreateDirs() {
			return $this->createDirs;
		}


		/**
		 * Setter for modifyTables
		 *
		 * @param string $modifyTables Information which existing DB tables will be modified
		 * @return void
		 */
		public function setModifyTables($modifyTables) {
			$this->modifyTables = $modifyTables;
		}


		/**
		 * Getter for modifyTables
		 *
		 * @return string Information which existing DB tables will be modified
		 */
		public function getModifyTables() {
			return $this->modifyTables;
		}


		/**
		 * Setter for clearCacheOnLoad
		 *
		 * @param boolean $clearCacheOnLoad Whether to clear cache on load or not
		 * @return void
		 */
		public function setClearCacheOnLoad($clearCacheOnLoad) {
			$this->clearCacheOnLoad = $clearCacheOnLoad;
		}


		/**
		 * Getter for clearCacheOnLoad
		 *
		 * @return boolean Whether to clear cache on load or not
		 */
		public function getClearCacheOnLoad() {
			return $this->clearCacheOnLoad;
		}


		/**
		 * Setter for lockType
		 *
		 * @param string $lockType Lock type
		 * @return void
		 */
		public function setLockType($lockType) {
			$this->lockType = $lockType;
		}


		/**
		 * Getter for lockType
		 *
		 * @return string Lock type
		 */
		public function getLockType() {
			return $this->lockType;
		}


		/**
		 * Setter for cglCompliance
		 *
		 * @param string $cglCompliance CGL compliance
		 * @return void
		 */
		public function setCglCompliance($cglCompliance) {
			$this->cglCompliance = $cglCompliance;
		}


		/**
		 * Getter for cglCompliance
		 *
		 * @return string CGL compliance
		 */
		public function getCglCompliance() {
			return $this->cglCompliance;
		}


		/**
		 * Setter for cglComplianceNote
		 *
		 * @param string $cglComplianceNote Note for the CGL compliance
		 * @return void
		 */
		public function setCglComplianceNote($cglComplianceNote) {
			$this->cglComplianceNote = $cglComplianceNote;
		}


		/**
		 * Getter for cglComplianceNote
		 *
		 * @return string Note for the CGL compliance
		 */
		public function getCglComplianceNote() {
			return $this->cglComplianceNote;
		}


		/**
		 * Setter for reviewState
		 *
		 * @param string $reviewState Review state
		 * @return void
		 */
		public function setReviewState($reviewState) {
			$this->reviewState = $reviewState;
		}


		/**
		 * Getter for reviewState
		 *
		 * @return string Review state
		 */
		public function getReviewState() {
			return $this->reviewState;
		}


		/**
		 * Setter for manual
		 *
		 * @param string $manual Relation to manual object of ter_doc extension
		 * @return void
		 */
		public function setManual($manual) {
			$this->manual = $manual;
		}


		/**
		 * Getter for manual
		 *
		 * @return string Relation to manual object of ter_doc extension
		 */
		public function getManual() {
			return $this->manual;
		}


		/**
		 * Setter for media
		 *
		 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Media> $media media
		 * @return void
		 */
		public function setMedia(Tx_Extbase_Persistence_ObjectStorage $media) {
			$this->media = $media;
		}


		/**
		 * Getter for media
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Media> media
		 */
		public function getMedia() {
			return $this->media;
		}


		/**
		 * Adds a Media
		 *
		 * @param Tx_TerFe2_Domain_Model_Media $medium The Media to be added
		 * @return void
		 */
		public function addMedia(Tx_TerFe2_Domain_Model_Media $medium) {
			$this->media->attach($medium);
		}


		/**
		 * Removes a Media
		 *
		 * @param Tx_TerFe2_Domain_Model_Media $medium The Media to be removed
		 * @return void
		 */
		public function removeMedia(Tx_TerFe2_Domain_Model_Media $medium) {
			$this->media->detach($medium);
		}


		/**
		 * Setter for experience
		 *
		 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Experience> $experience experience
		 * @return void
		 */
		public function setExperience(Tx_Extbase_Persistence_ObjectStorage $experience) {
			$this->experience = $experience;
		}


		/**
		 * Getter for experience
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Experience> experience
		 */
		public function getExperience() {
			return $this->experience;
		}


		/**
		 * Adds a Experience
		 *
		 * @param Tx_TerFe2_Domain_Model_Experience $experience The Experience to be added
		 * @return void
		 */
		public function addExperience(Tx_TerFe2_Domain_Model_Experience $experience) {
			$this->experience->attach($experience);
		}


		/**
		 * Removes a Experience
		 *
		 * @param Tx_TerFe2_Domain_Model_Experience $experience The Experience to be removed
		 * @return void
		 */
		public function removeExperience(Tx_TerFe2_Domain_Model_Experience $experience) {
			$this->experience->detach($experience);
		}


		/**
		 * Setter for softwareRelation
		 *
		 * @param Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Relation> $softwareRelation softwareRelation
		 * @return void
		 */
		public function setSoftwareRelation(Tx_Extbase_Persistence_ObjectStorage $softwareRelation) {
			$this->softwareRelation = $softwareRelation;
		}


		/**
		 * Getter for softwareRelation
		 *
		 * @return Tx_Extbase_Persistence_ObjectStorage<Tx_TerFe2_Domain_Model_Relation> softwareRelation
		 */
		public function getSoftwareRelation() {
			return $this->softwareRelation;
		}


		/**
		 * Adds a Relation
		 *
		 * @param Tx_TerFe2_Domain_Model_Relation $softwareRelation The Relation to be added
		 * @return void
		 */
		public function addSoftwareRelation(Tx_TerFe2_Domain_Model_Relation $softwareRelation) {
			$this->softwareRelation->attach($softwareRelation);
		}


		/**
		 * Removes a Relation
		 *
		 * @param Tx_TerFe2_Domain_Model_Relation $softwareRelation The Relation to be removed
		 * @return void
		 */
		public function removeSoftwareRelation(Tx_TerFe2_Domain_Model_Relation $softwareRelation) {
			$this->softwareRelation->detach($softwareRelation);
		}


		/**
		 * Setter for extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension extension
		 * @return void
		 */
		public function setExtension(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->extension = $extension;
		}


		/**
		 * Getter for extension
		 *
		 * @return Tx_TerFe2_Domain_Model_Extension extension
		 */
		public function getExtension() {
			return $this->extension;
		}


		/**
		 * Setter for extensionProvider
		 *
		 * @param string $extensionProvider Extension Provider
		 * @return void
		 */
		public function setExtensionProvider($extensionProvider) {
			$this->extensionProvider = $extensionProvider;
		}


		/**
		 * Getter for extensionProvider
		 *
		 * @return string Extension Provider
		 */
		public function getExtensionProvider() {
			return $this->extensionProvider;
		}

	}
?>