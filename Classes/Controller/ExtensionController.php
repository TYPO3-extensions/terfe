<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
	 *       and Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
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
	 * Controller for the Extension object
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Controller_ExtensionController extends Tx_Extbase_MVC_Controller_ActionController {

		/**
		 * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
		 */
		protected $extensionRepository;

		/**
		 * @var Tx_TerFe2_Domain_Repository_CategoryRepository
		 */
		protected $categoryRepository;

		/**
		 * @var Tx_TerFe2_Domain_Repository_TagRepository
		 */
		protected $tagRepository;


		/**
		 * Initializes the current action
		 *
		 * @return void
		 */
		protected function initializeAction() {
			$this->extensionRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			$this->categoryRepository  = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_CategoryRepository');
			$this->tagRepository       = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_TagRepository');

			// Pre-parse TypoScript setup
			$this->settings = Tx_TerFe2_Utility_TypoScript::parse($this->settings);
		}


		/**
		 * Index action
		 *
		 * @return void
		 */
		public function indexAction() {
			// Can be replaced by another action/view later
			//$this->forward('listLatest');
		}


		/**
		 * List action, displays all extensions
		 *
		 * @return void
		 */
		public function listAction() {
			$rawData = ('json' == $this->request->getFormat());
			$this->view->assign('extensions', $this->extensionRepository->findAll($rawData));
		}


		/**
		 * List latest action, displays new and updated extensions
		 *
		 * @return void
		 */
		public function listLatestAction() {
			$rawData     = ('json' == $this->request->getFormat());
			$latestCount = (!empty($this->settings['latestCount']) ? $this->settings['latestCount'] : 20);
			$extensions  = $this->extensionRepository->findNewAndUpdated($latestCount, $rawData);
			$this->view->assign('extensions', $extensions);
		}


		/**
		 * List by category action, displays all extensions in a category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $category The Category to search in
		 * @return void
		 */
		public function listByCategoryAction(Tx_TerFe2_Domain_Model_Category $category) {
			$this->view->assign('extensions', $this->extensionRepository->findByCategory($category));
		}


		/**
		 * List by tag action, displays all extensions with a tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag The Tag to search for
		 * @return void
		 */
		public function listByTagAction(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->view->assign('extensions', $this->extensionRepository->findByTag($tag));
		}


		/**
		 * Action that displays a single Extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The Extension to display
		 * @return void
		 */
		public function showAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->view->assign('extension', $extension);
		}


		/**
		 * Displays a form for creating a new Extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $newExtension A fresh Extension object taken as a basis for the rendering
		 * @return void
		 * @dontvalidate $newExtension
		 */
		public function newAction(Tx_TerFe2_Domain_Model_Extension $newExtension = NULL) {
			$this->view->assign('newExtension', $newExtension);
			$this->view->assign('categories', $this->categoryRepository->findAll());
			$this->view->assign('tags', $this->tagRepository->findAll());
		}


		/**
		 * Creates a new Extension and forwards to the index action
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $newExtension A fresh Extension object which has not yet been added to the repository
		 * @return void
		 */
		public function createAction(Tx_TerFe2_Domain_Model_Extension $newExtension) {
			$this->extensionRepository->add($newExtension);
			$this->flashMessageContainer->add($this->translate('msg_extension_created'));
			$this->redirect('index');
		}


		/**
		 * Displays a form to edit an existing Extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The Extension to display
		 * @return void
		 * @dontvalidate $extension
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->view->assign('extension', $extension);
		}


		/**
		 * Updates an existing Extension and forwards to the index action afterwards
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension Extension to update
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->extensionRepository->update($extension);
			$this->flashMessageContainer->add($this->translate('msg_extension_updated'));
			$this->redirect('index');
		}


		/**
		 * Deletes an existing Extension and all Versions
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The Extension to be deleted
		 * @return void
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->extensionRepository->remove($extension);
			$this->flashMessageContainer->add($this->translate('msg_extension_deleted'));
			$this->redirect('index');
		}


		/**
		 * Creates a new Version of an existing Extension and forwards to the index action afterwards
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension An existing Extension object
		 * @param Tx_TerFe2_Domain_Model_Version $newVersion A fresh Version object which has not yet been added to the repository
		 * @return void
		 */
		public function createVersionAction(Tx_TerFe2_Domain_Model_Extension $extension, Tx_TerFe2_Domain_Model_Version $newVersion) {
			// Get file hash
			$fileName = t3lib_div::getFileAbsFileName($newVersion->getFilename());
			$fileHash = Tx_TerFe2_Utility_Files::getFileHash($fileName);

			if (!empty($fileHash)) {
				$newVersion->setFileHash($fileHash);
				$newVersion->setExtension($extension);
				$extension->addVersion($newVersion);
				$extension->setLastUpdate(new DateTime());
			} else {
				$this->flashMessageContainer->add($this->translate('msg_file_not_valid'));
			}

			$this->redirect('index');
		}


		/**
		 * Check file hash and increment counter while downloading
		 *
		 * TODO:
		 *   - Show a "please wait" massage until file will be downloaded into output buffer
		 *     or find an other way to send external file to browser
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $newVersion An existing Version object
		 * @return void
		 */
		public function downloadAction(Tx_TerFe2_Domain_Model_Version $version) {
			// Load Extension Provider
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			$extensionProvider = $objectManager->get('Tx_TerFe2_ExtensionProvider_ExtensionProvider');

			// Get URL to file
			$urlToFile = $extensionProvider->getExtensionFile($version);
			if (empty($urlToFile)) {
				$this->flashMessageContainer->add($this->translate('msg_file_not_found'));
				$this->redirect('index');
			}

			// Check file hash
			$fileHash = Tx_TerFe2_Utility_Files::getFileHash($urlToFile);
			if ($fileHash != $version->getFileHash()) {
				$this->flashMessageContainer->add($this->translate('msg_file_hash_not_equal'));
				$this->redirect('index');
			}

			// Check session if user has already downloaded this file today
			$extKey = $version->getExtension()->getExtKey();
			Tx_TerFe2_Utility_Session::load();
			if (!Tx_TerFe2_Utility_Session::hasDownloaded($extKey)) {
				// Add +1 to download counter
				$version->incrementDownloadCounter();
				$persistenceManager = t3lib_div::makeInstance('Tx_Extbase_Persistence_Manager');
				$persistenceManager->persistAll();

				// Add extension key to session
				Tx_TerFe2_Utility_Session::addDownload($extKey);
				Tx_TerFe2_Utility_Session::save();
			}

			// Download as ZIP file
			//$fileName = Tx_TerFe2_Utility_Files::createT3xZipArchive($urlToFile);
			//Tx_TerFe2_Utility_Files::transferFile($fileName, basename($fileName));
			//$this->redirect('index');

			// Send file to browser
			$newFileName = $extensionProvider->getExtensionFileName($version, 't3x');
			Tx_TerFe2_Utility_Files::transferFile($urlToFile, $newFileName);

			// Fallback
			$this->redirect('index');
		}


		/**
		 * Translate a label
		 *
		 * @param string $label Label to translate
		 * @param array $arguments Optional arguments array
		 * @return string Translated label
		 */
		protected function translate($label, array $arguments = array()) {
			$extKey = $this->request->getControllerExtensionKey();
			return Tx_Extbase_Utility_Localization::translate($label, $extKey, $arguments);
		}

	}
?>