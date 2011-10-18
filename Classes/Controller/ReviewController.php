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
	 * Controller for the extension review
	 */
	class Tx_TerFe2_Controller_ReviewController extends Tx_TerFe2_Controller_AbstractTerBasedController {

		/**
		 * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
		 */
		protected $extensionRepository;

		/**
		 * @var Tx_TerFe2_Domain_Repository_VersionRepository
		 */
		protected $versionRepository;

		/**
		 * @var Tx_TerFe2_Provider_ProviderManager
		 */
		protected $providerManager;

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
			$this->versionRepository   = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
			$this->providerManager     = $this->objectManager->get('Tx_TerFe2_Provider_ProviderManager');
			$this->persistenceManager  = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		}


		/**
		 * Display the version overview
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to display
		 * @param string $extensionKey Extension key
		 * @return void
		 * @dontvalidate $extension
		 * @dontvalidate $extensionKey
		 */
		public function indexAction(Tx_TerFe2_Domain_Model_Extension $extension = NULL, $extensionKey = NULL) {
			if (!empty($extensionKey)) {
				if (!is_string($extensionKey)) {
					throw new Exception('No valid extension key given');
				}
				$extension = $this->extensionRepository->findOneByExtKey($extensionKey);
			}
			if ($extension !== NULL && $extension instanceof Tx_TerFe2_Domain_Model_Extension) {
				$this->view->assign('extension', $extension);
			}
		}


		/**
		 * Displays a form to edit an existing extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version to modify
		 * @param integer $reviewState Review state
		 * @param boolean $inherit Inherit changes to all versions before current
		 * @return void
		 * @dontvalidate $version
		 * @dontvalidate $reviewState
		 * @dontvalidate $inherit
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Version $version = NULL, $reviewState = NULL, $inherit = FALSE) {
			$this->view->assign('version', $version);
			$this->view->assign('reviewState', $reviewState);
		}


		/**
		 * Updates an existing extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version to modify
		 * @param integer $reviewState Review state
		 * @param boolean $inherit Inherit changes to all versions before current
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Version $version, $reviewState, $inherit = FALSE) {
			$reviewStateDataArr = array (
				'extensionKey' => (string) $version->getExtension()->getExtKey(),
				'version'      => (string) $version->getVersionString(),
				'reviewState'  => (int) $reviewState,
			);

			if ($soapClientObj->setReviewState($accountDataArr, $reviewStateDataArr)) {
				if (empty($inherit)) {
					$version->setReviewState((int) $reviewState);
				} else {
					$versions = $this->versionRepository->findAllBelowVersion($version->getExtension(), $version->getVersionNumber());
				}
				$this->persistenceManager->persistAll();
			} else {
				$this->flashMessageContainer->add('msg.reviewstate_not_enabled');
			}
		}

	}
?>