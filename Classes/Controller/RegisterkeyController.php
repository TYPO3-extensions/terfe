<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Thomas Layh <thomas@layh.com>
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
	class Tx_TerFe2_Controller_RegisterkeyController extends Tx_TerFe2_Controller_AbstractController {

		/**
		 * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
		 */
		protected $extensionRepository;

		/**
		 * @var Tx_TerFe2_Domain_Repository_CategoryRepository
		 */
		protected $categoryRepository;

		/**
		 * @var Tx_Extbase_Domain_Repository_FrontendUserRepository
		 */
		protected $frontendUserRepository;

		/**
		 * Initializes the controller
		 *
		 * @return void
		 */
		protected function initializeController() {
			$this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionRepository');
			$this->categoryRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_CategoryRepository');
			$this->frontendUserRepository = $this->objectManager->get('Tx_Extbase_Domain_Repository_FrontendUserRepository');
		}

		/**
		 * initializeView, check if a user is logged in and assign the loggedIn var
		 *
		 * @return void
		 */
		public function initializeView() {
				// check if a user is logged in
			if($GLOBALS['TSFE']->fe_user->user) {
				$this->view->assign('loggedIn', TRUE);
				$this->view->assign('userName', $GLOBALS['TSFE']->fe_user->user['username']);
				$this->view->assign('userId', $GLOBALS['TSFE']->fe_user->user['uid']);
			} else {
				$this->view->assign('loggedIn', FALSE);
			}
		}

		/**
		 * indexAction
		 *
		 * @return void
		 */
		public function indexAction() {
			$categories = $this->categoryRepository->findAll();
			$this->view->assign('categories', $categories);
		}

		/**
		 * save a new key
		 *
		 * @todo translate label in flashmessage container
		 * @param string $userName
		 * @param string $extensionKey
		 * @param mixed $categories
		 * @return void
		 */
		public function createAction($userName, $extensionKey, $categories) {

				// remove spaces from extensionKey if there are some
			$extensionKey = trim($extensionKey);

				// get ter connection object
			$terConnection = $this->getTerConnection();

				// check if the extension exists in the ter
			if ($terConnection->checkExtensionKey($extensionKey)) {

				$extensionData = array(
					'extensionKey' => $extensionKey,
					'title' => $extensionKey,
					'description' => '',
				);

				//$terConnection = null;
				//$terConnection = $this->getTerConnection();

					// register the extension key at the ter, if successfull, add it to the extension table
				if ($terConnection->registerExtension($extensionData)) {

					/** @var $extension Tx_TerFe2_Domain_Model_Extension */
					$extension = $this->objectManager->create('Tx_TerFe2_Domain_Model_Extension');
					$extension->setExtKey($extensionKey);
					$extension->setFrontendUser($userName);

						// add categories
					foreach($categories as $category) {
						if (isset($category['__identity']) && is_numeric($category['__identity'])) {
							$myCat = $this->categoryRepository->findByUid(intval($category['__identity']));
							if($myCat != NULL) {
								$extension->addCategory($myCat);
							}
						}
					}

					$this->extensionRepository->add($extension);
					$this->flashMessageContainer->add('Extension key registered');
					$this->redirect('manage', 'Registerkey');
				}
			}

			$this->flashMessageContainer->add('Extension Key exists');
			$this->redirect('index', 'Registerkey', NULL, array());
		}

		/**
		 * manage registered extensions
		 * @return void
		 */
		public function manageAction() {
			$extensions = $this->extensionRepository->findByFrontendUser($GLOBALS['TSFE']->fe_user->user['username']);
			$this->view->assign('extensions', $extensions);
		}

		/**
		 * display the edit form
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension
		 * @return void
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Extension $extension) {

			// remove categories that are already set
			$setCategories = $extension->getCategories();

			// get all categories
			/** @var $categories Tx_Extbase_Persistence_QueryResult */
			$categories = $this->categoryRepository->findAll();

			$categoryArray = array();
			foreach ($categories as $key => $category) {
				$categoryArray[] = array(
					'object' => $category,
					'isChecked' => $setCategories->contains($category),
				);
			}

			$this->view->assign('categories', $categoryArray);
			$this->view->assign('extension', $extension);
		}

		/**
		 * update existing extension key
		 *
		 * @todo translate label in flashmessage container
		 * @param Tx_TerFe2_Domain_Model_Extension $extension
		 * @param mixed $categories
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Extension $extension, $categories) {

				// get ter connection object
			$terConnection = $this->getTerConnection();

				// check if the extension key changed
			if ( $extension->_isDirty('extKey')) {
					// if the extension key changed, check if the new one is in the ter
				if ( $terConnection->checkExtensionKey($extension->getExtKey())) {

					$error = '';
					if ($terConnection->assignExtensionKey($extension->getExtKey(), $GLOBALS['TSFE']->fe_user->user['username'], $error)) {

							// update categories
						$this->extensionRepository->update($extension);

						$this->extensionRepository->update($extension);
						$this->flashMessageContainer->add('Extension updated');
						$this->redirect('manage', 'Registerkey');
					} else {
						$this->flashMessageContainer->add('Could not update extension');
					}


				} else {
					$this->flashMessageContainer->add('Extension key already exists!!');
				}

			} else {

					// update categories
				$extension = $this->updateCategories($extension, $categories);

				$this->extensionRepository->update($extension);
				$this->flashMessageContainer->add('Extension updated');
				$this->redirect('manage', 'Registerkey');

			}

			$this->redirect('edit', 'Registerkey', NULL, array('extension' => $extension));
		}

		/**
		 * Update the categories of an existing extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension
		 * @param mixed $categories
		 * @return Tx_TerFe2_Domain_Model_Extension
		 */
		protected function updateCategories(Tx_TerFe2_Domain_Model_Extension $extension, $categories) {

				// remove all categories
			$extension->removeAllCategories();

				// add categories
			foreach($categories as $category) {
				if (isset($category['__identity']) && is_numeric($category['__identity'])) {
					$myCat = $this->categoryRepository->findByUid(intval($category['__identity']));
					if($myCat != NULL) {
						$extension->addCategory($myCat);
					}
				}
			}

			return $extension;
		}

		/**
		 * transfer extension key to another user
		 *
		 * @param string $newUser
		 * @param Tx_TerFe2_Domain_Model_Extension $extension
		 * @return void
		 */
		public function transferAction($newUser, Tx_TerFe2_Domain_Model_Extension $extension) {

				// container for the error message
			$error = '';

				// get ter connection
			$terConnection = $this->getTerConnection();

				// is it possible to assign the key to a new user
			if ($terConnection->assignExtensionKey($extension->getExtKey(), $newUser, $error)) {
				$extension->setFrontendUser($newUser);
				$this->extensionRepository->update($extension);
				$this->flashMessageContainer->add('Transfered the extension ' . $extension->getExtKey() . ' to ' .$newUser );
			} else {
				$this->flashMessageContainer->add('Error transfering the extension ' . $extension->getExtKey() . ' Error: ' . $error);
			}

			$this->redirect('manage', 'Registerkey');

		}

		/**
		 * delete extension key from ter
		 *
		 * @todo currently we delete without asking again
		 * @param Tx_TerFe2_Domain_Model_Extension $extension
		 * @return void
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Extension $extension) {

				// get ter connection
			$terConnection = $this->getTerConnection();

				// deleted in ter, then delete the key in the ter_fe2 extension table
			if ($terConnection->deleteExtensionKey($extension->getExtKey())) {
				$this->extensionRepository->remove($extension);
				$this->flashMessageContainer->add('Extension ' . $extension->getExtKey() . ' deleted!!');
			} else {
				$this->flashMessageContainer->add('Extension ' . $extension->getExtKey() . ' could not be deleted!!');
			}


			$this->redirect('manage', 'Registerkey');
		}

		/**
		 * create a connection to the ter
		 *
		 * @return Tx_TerFe2_Service_Ter
		 */
		protected function getTerConnection() {
				// check the settings if a overwrite username and password are set
			if (empty($this->settings['terConnection']['username']) || empty($this->settings['terConnection']['password'])) {
				$terUsername = $GLOBALS['TSFE']->fe_user->user['username'];
				$terPassword = $GLOBALS['TSFE']->fe_user->user['password'];
			} else {
				$terUsername = $this->settings['terConnection']['username'];
				$terPassword = $this->settings['terConnection']['password'];
			}

				// set the wsdl file
			$terWsdl = $this->settings['terConnection']['wsdl'];

			/** @var Tx_TerFe2_Service_Ter $terConnection */
			$terConnection = $this->objectManager->create('Tx_TerFe2_Service_Ter', $terWsdl, $terUsername, $terPassword);

			return $terConnection;
		}

	}