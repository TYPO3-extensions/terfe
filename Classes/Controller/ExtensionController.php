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
		 * Index action, displays extension list
		 *
		 * @param array $search Search params for extension list
		 * @return void
		 * @dontvalidate $search
		 */
		public function indexAction(array $search = array()) {
				// Get extension list
			if (!empty($this->settings['show']['extensionSearch'])) {
				$this->view->assign('extensions', $this->getExtensions($search));
				$this->view->assign('search',     $search);
			} else {
				$this->view->assign('extensions', $this->extensionRepository->findAll());
			}

				// Get all categories
			if (!empty($this->settings['show']['categoryOverview'])) {
				$categories = $this->categoryRepository->findAll();
				$this->view->assign('categories', $categories);
			}

				// Get all tags
			if (!empty($this->settings['show']['tagOverview'])) {
				$tags = $this->tagRepository->findAll();
				$this->view->assign('tags', $tags);
			}

				// Get authors
			if (!empty($this->settings['show']['authorOverview'])) {
				$authors = $this->authorRepository->findByLatestExtensionVersion();
				$this->view->assign('authors', $authors);
			}
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
			$this->redirectWithMessage($this->translate('msg.extension_created'), 'index');
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
			$actionParameters = array('extension' => $extension);
			$this->redirectWithMessage($this->translate('msg.extension_updated'), 'show', NULL, NULL, $actionParameters);
		}


		/**
		 * Deletes an existing extension and all versions
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to delete
		 * @return void
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->extensionRepository->remove($extension);
			$this->redirectWithMessage($this->translate('msg.extension_deleted'), 'index');
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
				$this->redirectWithMessage($this->translate('msg.file_not_found') . ': ' . $fileUrl, 'index');
				// $this->redirectWithMessage($this->translate('msg.file_not_found'), 'index');
			}

				// Check file hash of t3x packages
			if ($format === 't3x') {
				$fileHash = Tx_TerFe2_Utility_File::getFileHash($fileUrl);
				if ($fileHash != $version->getFileHash()) {
					$this->redirectWithMessage($this->translate('msg.file_hash_not_equal'), 'index');
				}
			}

				// Check session if user has already downloaded this file today
			if (!empty($this->settings['countDownloads'])) {
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
				$this->redirectWithMessage($this->translate('msg.could_not_transfer_file'), 'index');
			}

				// Fallback
			$this->redirect('index');
		}


		/**
		 * Returns all / filtered extensions
		 *
		 * @param array $options Options for extension list
		 * @return Tx_Extbase_Persistence_ObjectStorage Objects
		 */
		protected function getExtensions(array &$options) {
				// Direction
			$desc = Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING;
			$asc  = Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;
			$direction = $desc;
			if (!empty($options['direction'])) {
				$direction = ($options['direction'] === 'asc' ? $asc : $desc);
			}

				// Get last needle
			$session = $this->objectManager->get('Tx_TerFe2_Persistence_Session');
			$lastNeedle = $session->get('lastNeedle');

				// Sorting
			$sortings = array(
				'updated'   => 'lastVersion.uploadDate',
				'downloads' => 'downloads',
				'title'     => 'lastVersion.title',
			);
			$sorting = $sortings['updated'];
			if (!empty($options['sorting'])) {
					// Set direction to ASC when sorting by title
				if (!empty($sortings[$options['sorting']])) {
					$sorting = $sortings[$options['sorting']];
					if (empty($options['direction'])) {
						$direction = $desc;
						if ($options['sorting'] === 'title') {
							$direction = $asc;
							$options['direction'] = 'asc';
						}
					}
				}
					// Sort by downloads when searching
				if (!empty($options['needle']) && (empty($lastNeedle) || $lastNeedle !== $options['needle'])) {
					$sorting = $sortings['downloads'];
					$options['sorting'] = 'downloads';
					$direction = $desc;
				}
			}

				// Set new needle
			$session->set('lastNeedle', $options['needle']);

				// Ordering
			$ordering = array($sorting => $direction);

				// Return sorted list of all extensions
			if (empty($options['needle'])) {
				return $this->extensionRepository->findAll(0, 0, $ordering);
			}

				// Return search result
			return $this->extensionRepository->findBySearchWordsAndFilters($options['needle'], array(), $ordering);
		}

	}
?>