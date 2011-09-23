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
	 * Controller for the extension object
	 */
	class Tx_TerFe2_Controller_ExtensionController extends Tx_TerFe2_Controller_AbstractController {

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
		 * @var Tx_TerFe2_Domain_Repository_AuthorRepository
		 */
		protected $authorRepository;

		/**
		 * @var Tx_TerFe2_Domain_Repository_VersionRepository
		 */
		protected $versionRepository;

		/**
		 * @var Tx_TerFe2_Provider_ProviderManager
		 */
		protected $providerManager;

		/**
		 * @var Tx_TerFe2_Persistence_Session
		 */
		protected $session;

		/**
		 * @var Tx_Extbase_Persistence_Manager
		 */
		protected $persistenceManager;


		/**
		 * Initializes the controller
		 *
		 * @return void
		 */
		protected function initializeController() {
			$this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			$this->categoryRepository  = $this->objectManager->get('Tx_TerFe2_Domain_Repository_CategoryRepository');
			$this->tagRepository       = $this->objectManager->get('Tx_TerFe2_Domain_Repository_TagRepository');
			$this->versionRepository   = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
			$this->authorRepository    = $this->objectManager->get('Tx_TerFe2_Domain_Repository_AuthorRepository');
			$this->providerManager     = $this->objectManager->get('Tx_TerFe2_Provider_ProviderManager');
			$this->session             = $this->objectManager->get('Tx_TerFe2_Persistence_Session');
			$this->persistenceManager  = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		}


		/**
		 * Index action, shows an overview
		 *
		 * @param string $sorting Sort extensions by this key
		 * @param string $direction Sorting order
		 * @return void
		 */
		public function indexAction($sorting = 'updated', $direction = 'desc') {
				// Get all extensions
			$this->view->assign('extensions', $this->getExtensions($sorting, $direction));
			$this->view->assign('sorting',    $sorting);
			$this->view->assign('direction',  $direction);

				// Get latest extensions
			/*$latestCount = (!empty($this->settings['latestCount']) ? $this->settings['latestCount'] : 10);
			$latestExtensions = $this->extensionRepository->findNewAndUpdated($latestCount);
			$this->view->assign('latestExtensions', $latestExtensions);*/

				// Get top rated extensions
			/*$topRatedCount = (!empty($this->settings['topRatedCount']) ? $this->settings['topRatedCount'] : 10);
			$topRatedExtensions = $this->extensionRepository->findTopRated($topRatedCount);
			$this->view->assign('topRatedExtensions', $topRatedExtensions);*/

				// Get all categories
			$categories = $this->categoryRepository->findAll();
			$this->view->assign('categories', $categories);

				// Get all tags
			/*$tags = $this->tagRepository->findAll();
			$this->view->assign('tags', $tags);*/

				// Get random authors
			/*$randomAuthorCount = (!empty($this->settings['randomAuthorCount']) ? $this->settings['randomAuthorCount'] : 10);
			$randomAuthors = $this->authorRepository->findRandom($randomAuthorCount);
			$this->view->assign('randomAuthors', $randomAuthors);*/

				// Get authors
			$authors = $this->authorRepository->findByLatestExtensionVersion();
			$this->view->assign('authors', $authors);
		}


		/**
		 * List action, displays all extensions
		 *
		 * Note: Required for RSS / JSON output
		 *
		 * @return void
		 */
		public function listAction() {
			$this->view->assign('extensions', $this->extensionRepository->findAll());
		}


		/**
		 * List latest action, displays new and updated extensions
		 *
		 * Note: Required for RSS / JSON output
		 *
		 * @return void
		 */
		public function listLatestAction() {
			$latestCount = (!empty($this->settings['latestCount']) ? $this->settings['latestCount'] : 20);
			$extensions  = $this->extensionRepository->findNewAndUpdated($latestCount);
			$this->view->assign('extensions', $extensions);
		}


		/**
		 * Action that displays a single extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to display
		 * @param string $extensionKey Extension key
		 * @return void
		 * @dontvalidate $extension
		 * @dontvalidate $extensionKey
		 */
		public function showAction(Tx_TerFe2_Domain_Model_Extension $extension = NULL, $extensionKey = NULL) {
			if (!empty($extensionKey)) {
				if (!is_string($extensionKey)) {
					throw new Exception('No valid extension key given');
				}
				$extension = $this->extensionRepository->findOneByExtKey($extensionKey);
			}

			$versionHistoryCount = (!empty($this->settings['versionHistoryCount']) ? $this->settings['versionHistoryCount'] : 5);
			$skipLatestVersion   = (isset($this->settings['skipLatestVersion'])    ? $this->settings['skipLatestVersion']   : TRUE);

			if ($extension !== NULL && $extension instanceof Tx_TerFe2_Domain_Model_Extension) {
				$versionHistory = $this->versionRepository->getVersionHistory($extension, $versionHistoryCount, $skipLatestVersion);
				$this->view->assign('extension', $extension);
				$this->view->assign('versionHistory', $versionHistory);
			}
		}


		/**
		 * Displays a form for creating a new extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $newExtension New extension object
		 * @return void
		 * @dontvalidate $newExtension
		 */
		public function newAction(Tx_TerFe2_Domain_Model_Extension $newExtension = NULL) {
			$this->view->assign('newExtension', $newExtension);
			$this->view->assign('categories', $this->categoryRepository->findAll());
			$this->view->assign('tags', $this->tagRepository->findAll());
		}


		/**
		 * Creates a new extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $newExtension New extension object
		 * @return void
		 */
		public function createAction(Tx_TerFe2_Domain_Model_Extension $newExtension) {
			$this->extensionRepository->add($newExtension);
			$this->redirectWithMessage('index', 'extension_created');
		}


		/**
		 * Displays a form to edit an existing extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to display
		 * @return void
		 * @dontvalidate $extension
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->view->assign('extension', $extension);
		}


		/**
		 * Updates an existing extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension extension to update
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->extensionRepository->update($extension);
			$this->flashMessageContainer->add($this->translate('msg.extension_updated'));
			$this->redirect('show', NULL, NULL, array('extension' => $extension->getUid()));
		}


		/**
		 * Deletes an existing extension and all versions
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to delete
		 * @return void
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->extensionRepository->remove($extension);
			$this->redirectWithMessage('index', 'extension_deleted');
		}


		/**
		 * Check file hash, increment download counter and send file to client browser
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension object
		 * @param String $versionString An existing version string
		 * @param string $format Format of the file output
		 * @return void
		 */
		public function downloadAction(Tx_TerFe2_Domain_Model_Extension $extension, $versionString, $format = 't3x') {
			if ($format !== 't3x' && $format !== 'zip') {
				throw new Exception('A download action for the format "' . $format . '" is not implemented');
			}

			$version = $this->versionRepository->findOneByExtensionAndVersionString($extension, $versionString);
			if (!$version) {
				throw new Exception('Invalid version request', 1316542246);
			}

				// Get file path
			$provider = $this->providerManager->getProvider($version->getExtensionProvider());
			if ($format === 't3x') {
				$fileUrl = $provider->getFileUrl($version, $format);
			} else if ($format === 'zip') {
				if (empty($this->settings['mediaRootPath'])) {
					throw new Exception('No directory for extension media files configured');
				}
				$extKey = $extension->getExtKey();
				$extensionMediaPath = rtrim($this->settings['mediaRootPath'], '/') . '/';
				$extensionMediaPath = Tx_TerFe2_Utility_File::getAbsoluteDirectory($extensionMediaPath . $extKey);
				$fileUrl = $extensionMediaPath . basename($provider->getFileName($version, $format));
			}

				// Check if file exists
			if (empty($fileUrl) || !Tx_TerFe2_Utility_File::fileExists($fileUrl)) {
				if (Tx_TerFe2_Utility_File::isAbsolutePath($fileUrl)) {
					$fileUrl = Tx_TerFe2_Utility_File::getUrlFromAbsolutePath($fileUrl);
				}
				$this->flashMessageContainer->add($this->translate('msg.file_not_found') . ': ' . $fileUrl);
				$this->redirect('index');
				//$this->redirectWithMessage('index', 'file_not_found');
			}

				// Check file hash of t3x packages
			if ($format === 't3x') {
				$fileHash = Tx_TerFe2_Utility_File::getFileHash($fileUrl);
				if ($fileHash != $version->getFileHash()) {
					$this->redirectWithMessage('index', 'file_hash_not_equal');
				}
			}

				// Check session if user has already downloaded this file today
			if (!empty($this->settings['enableDownloadCounter'])) {
				$extensionKey = $extension->getExtKey();
				$downloads = $this->session->get('downloads');
				if (empty($downloads) || !in_array($extensionKey, $downloads)) {
						// Add +1 to download counter and save immediately
					$version->incrementDownloadCounter();
					$this->persistenceManager->persistAll();

						// Add extension key to session
					$downloads[] = $extensionKey;
					$this->session->add('downloads', $downloads);
				}
			}

				// Send file to browser
			if (!Tx_TerFe2_Utility_File::transferFile($fileUrl)) {
				$this->redirectWithMessage('index', 'could_not_transfer_file');
			}

				// Fallback
			$this->redirect('index');
		}


		/**
		 * Returns all extensions by
		 *
		 * @param string $sorting Sort extensions by this key
		 * @param string $direction Sorting order
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		protected function getExtensions(&$sorting, &$direction) {
			$sortings = array(
				'updated'   => 'lastVersion.uploadDate',
				'downloads' => 'versions.downloadCounter',
				'title'     => 'lastVersion.title',
			);
			$directions = array(
				'desc'      => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING,
				'asc'       => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING,
			);
			if (empty($sortings[$sorting]) || empty($directions[$direction])) {
				$sorting   = 'updated';
				$direction = 'desc';
			}
			return $this->extensionRepository->findAllBySortingAndDirection($sortings[$sorting], $directions[$direction]);
		}

	}
?>