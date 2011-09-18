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
		}

		/**
		 * save a new key
		 *
		 * @todo translate label in flashmessage container
		 * @param string $userId
		 * @param string $userName
		 * @param string $extensionKey
		 * @return void
		 */
		public function createAction($userId, $userName, $extensionKey) {

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
			$this->view->assign('extension', $extension);
		}

		/**
		 * update existing extension key
		 *
		 * @todo translate label in flashmessage container
		 * @param Tx_TerFe2_Domain_Model_Extension $extension
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Extension $extension) {

				// get ter connection object
			$terConnection = $this->getTerConnection();

				// check if the new extension key exists in the ter
			if ($terConnection->checkExtensionKey($extension->getExtKey())) {
				$this->extensionRepository->update($extension);
				$this->flashMessageContainer->add('Extension updated');
				$this->redirect('manage', 'Registerkey');
			} else {
				$this->flashMessageContainer->add('Extension Key exists');
				$this->redirect('edit', 'Registerkey', null, array('extension' => $extension));
			}
		}

		/**
		 * transfer extension key to another user
		 *
		 * @param string $username
		 * @param Tx_TerFe2_Domain_Model_Extension $extension
		 * @return void
		 */
		public function transferAction(string $username, Tx_TerFe2_Domain_Model_Extension $extension) {

			// use assignExtensionKey to check if the new user is valid, only transfer key if user is valid

		}

		/**
		 * delete extension key from ter
		 *
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