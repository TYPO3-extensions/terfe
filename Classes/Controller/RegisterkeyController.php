<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Thomas Layh <thomas@layh.com>
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
	 * @var Tx_TerFe2_Service_Ter
	 */
	protected $terConnection;

	/**
	 * @var array
	 */
	protected $frontendUser = array();


	/**
	 * Initializes the controller
	 *
	 * @return void
	 */
	protected function initializeController() {
		$this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionRepository');
		$this->categoryRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_CategoryRepository');
		$this->frontendUser = (!empty($GLOBALS['TSFE']->fe_user->user) ? $GLOBALS['TSFE']->fe_user->user : array());
		$this->terConnection = $this->getTerConnection();
	}


	/**
	 * Initializes the view, check if a user is logged in and assign the loggedIn var
	 *
	 * @return void
	 */
	public function initializeView() {
			// Check if a user is logged in
		if (!empty($this->frontendUser)) {
			$this->view->assign('loggedIn', TRUE);
			$this->view->assign('userName', $this->frontendUser['username']);
			$this->view->assign('userId',   $this->frontendUser['uid']);
		} else {
			$this->view->assign('loggedIn', FALSE);
		}
	}


	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
			// get categories for register key
		$categories = $this->categoryRepository->findAll();
		$this->view->assign('categories', $categories);

			// get extensions by user if a user is logged in
		if (!empty($this->frontendUser)) {

				// get extensions from ter-server if there are some extension keys registered, but there was never an upload
			$extensionListFromTerService = $this->terConnection->getExtensionKeyList();

			$extensions = array();
			$addedNewExtensionKey = FALSE;

			foreach ($extensionListFromTerService as $extensonFromTer) {

				$extension = $this->extensionRepository->findOneByExtKey($extensonFromTer['extensionkey']);

				if ($extension && get_class($extension) === 'Tx_TerFe2_Domain_Model_Extension') {
					$extensions[] = $extension;
				} else {

					$addedNewExtensionKey = TRUE;

						/** @var $newExtension Tx_TerFe2_Domain_Model_Extension */
					$newExtension = $this->objectManager->create('Tx_TerFe2_Domain_Model_Extension');
					$newExtension->setExtKey($extensonFromTer['extensionkey']);
					$newExtension->setFrontendUser($extensonFromTer['ownerusername']);
					$extensions[] = $newExtension;

					$this->extensionRepository->add($newExtension);
				}

			}

				// if a new extension key was added, we need a redirect to persist the new keys
			if ($addedNewExtensionKey) {
				/* @var $persistenceManager Tx_Extbase_Persistence_Manager */
				$persistenceManager = t3lib_div::makeInstance('Tx_Extbase_Persistence_Manager');
				$persistenceManager->persistAll();
			}

			$this->view->assign('extensions', $extensions);
		}
	}


	/**
	 * Register a new extension
	 *
	 * @todo translate label in flashmessage container
	 * @param string $userName Username of the registered user
	 * @param string $extensionKey Extension key
	 * @param mixed $categories Categories
	 * @return void
	 */
	public function createAction($userName, $extensionKey, $categories) {

			// Remove spaces from extensionKey if there are some
		$extensionKey = trim($extensionKey);

			// Check if the extension exists in the ter
		if ($this->terConnection->checkExtensionKey($extensionKey, $error)) {
			$extensionData = array(
				'extensionKey' => $extensionKey,
				'title' => $extensionKey,
				'description' => '',
			);

				// Register the extension key at ter server, if successfull, add it to the extension table
			if ($this->terConnection->registerExtension($extensionData)) {
					// Create extension model
				$extension = $this->objectManager->create('Tx_TerFe2_Domain_Model_Extension');
				$extension->setExtKey($extensionKey);
				$extension->setFrontendUser($userName);

					// Add categories
				foreach ($categories as $category) {
					if (isset($category['__identity']) && is_numeric($category['__identity'])) {
						$myCat = $this->categoryRepository->findByUid((int) $category['__identity']);
						if ($myCat != NULL) {
							$extension->addCategory($myCat);
						}
					}
				}

				$this->extensionRepository->add($extension);
				$this->flashMessageContainer->add($this->translate('registerkey.key_registered'));
				$this->redirect('index', 'Registerkey');
			}
		} else {
			$this->flashMessageContainer->add($this->resolveWSErrorMessage($error));
		}

		$this->redirect('index', 'Registerkey', NULL, array());
	}


	/**
	 * Manage registered extensions
	 *
	 * @obsolete
	 * @return void
	 */
	public function manageAction() {
		$extensions = $this->extensionRepository->findByFrontendUser($this->frontendUser['username']);
		$this->view->assign('extensions', $extensions);
	}


	/**
	 * Display the edit form
	 *
	 * @param Tx_TerFe2_Domain_Model_Extension $extension Extension to modify
	 * @return void
	 */
	public function editAction(Tx_TerFe2_Domain_Model_Extension $extension) {

			// check if the extension belongs to the current user
		if ($extension->getFrontendUser() == $GLOBALS['TSFE']->fe_user->user['username']) {

				// Remove categories that are already set
			$setCategories = $extension->getCategories();

			// Get all categories
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
		} else {
			$this->flashMessageContainer->add($this->translate('registerkey.notyourextension'));
			$this->redirect('index', 'Registerkey');
		}


	}


	/**
	 * Update an existing extension
	 *
	 * @todo translate label in flashmessage container
	 * @param Tx_TerFe2_Domain_Model_Extension $extension Extension to modify
	 * @param mixed $categories Categories to add / remove
	 * @return void
	 */
	public function updateAction(Tx_TerFe2_Domain_Model_Extension $extension, $categories) {

		// check if the extension belongs to the current user
		if ($extension->getFrontendUser() == $GLOBALS['TSFE']->fe_user->user['username']) {

			/**
			 * TODO: Modification of the extension key is currently not allowed
			 */
			if ($extension->_isDirty('extKey')) {
				$this->redirect('index', 'Registerkey');
			}

				// Check if the extension key has changed
			if ($extension->_isDirty('extKey')) {
					// If extension key has changed, check if the new one is in the ter
				if ($this->terConnection->checkExtensionKey($extension->getExtKey(), $error)) {
					$error = '';
					if ($this->terConnection->assignExtensionKey($extension->getExtKey(), $this->frontendUser['username'], $error)) {
							// Update categories
						$this->extensionRepository->update($extension);
						$this->flashMessageContainer->add($this->translate('registerkey.key_updated'));
						$this->redirect('index', 'Registerkey');
					} else {
						// TODO: Show different message by $error code
						$this->flashMessageContainer->add($this->translate('registerkey.key_update_failed'));
					}
				} else {
					$this->flashMessageContainer->add($this->translate($this->resolveWSErrorMessage($error)));
				}
			} else {
					// Update categories
				$extension = $this->updateCategories($extension, $categories);
				$this->extensionRepository->update($extension);
				$this->flashMessageContainer->add($this->translate('registerkey.key_updated'));
				$this->redirect('index', 'Registerkey');
			}

			$this->redirect('edit', 'Registerkey', NULL, array('extension' => $extension));

		} else {
			$this->flashMessageContainer->add($this->translate('registerkey.notyourextension'));
			$this->redirect('index', 'Registerkey');
		}
	}


	/**
	 * Update the categories of an existing extension
	 *
	 * @param Tx_TerFe2_Domain_Model_Extension $extension
	 * @param mixed $categories Categories to update
	 * @return Tx_TerFe2_Domain_Model_Extension
	 */
	protected function updateCategories(Tx_TerFe2_Domain_Model_Extension $extension, $categories) {

			// Remove all categories
		$extension->removeAllCategories();

			// Add selected categories
		foreach($categories as $category) {
			if (isset($category['__identity']) && is_numeric($category['__identity'])) {
				$myCat = $this->categoryRepository->findByUid((int) $category['__identity']);
				if ($myCat != NULL) {
					$extension->addCategory($myCat);
				}
			}
		}

		return $extension;
	}


	/**
	 * Transfer an extension key to another user
	 *
	 * @param string $newUser Username of the assignee
	 * @param Tx_TerFe2_Domain_Model_Extension $extension Extension to transfer
	 * @return void
	 */
	public function transferAction($newUser, Tx_TerFe2_Domain_Model_Extension $extension) {

			// check if the extension belongs to the current user
		if ($extension->getFrontendUser() == $GLOBALS['TSFE']->fe_user->user['username']) {

			$error = '';

				// Is it possible to assign the key to a new user
			if ($this->terConnection->assignExtensionKey($extension->getExtKey(), $newUser, $error)) {
				$extension->setFrontendUser($newUser);
				$this->extensionRepository->update($extension);
				$this->flashMessageContainer->add($this->translate('registerkey.keyTransfered', array($extension->getExtKey(), $newUser)));
			} else {
				$this->flashMessageContainer->add(
					$this->translate('registerkey.transferError', array($extension->getExtKey(), $this->resolveWSErrorMessage($error)))
				);
			}

		} else {
			$this->flashMessageContainer->add($this->translate('registerkey.notyourextension'));
		}

		$this->redirect('index', 'Registerkey');

	}


	/**
	 * Delete an extension key from ter server
	 *
	 * @param Tx_TerFe2_Domain_Model_Extension $extension Extension to delete
	 * @return void
	 */
	public function deleteAction(Tx_TerFe2_Domain_Model_Extension $extension) {

			// check if the extension belongs to the current user
		if ($extension->getFrontendUser() == $GLOBALS['TSFE']->fe_user->user['username']) {

				// Deleted in ter, then delete the key in the ter_fe2 extension table
			if ($this->terConnection->deleteExtensionKey($extension->getExtKey())) {
				$this->extensionRepository->remove($extension);
				$this->flashMessageContainer->add($this->translate('registerkey.deleted', array($extension->getExtKey())));
			} else {
				$this->flashMessageContainer->add($this->translate('registerkey.cannotbedeleted', array($extension->getExtKey())));
			}

		} else {
			$this->flashMessageContainer->add($this->translate('registerkey.notyourextension'));
		}

		$this->redirect('index', 'Registerkey');
	}


	/**
	 * Create a connection to the ter server
	 *
	 * @return Tx_TerFe2_Service_Ter Connection to ter server
	 */
	protected function getTerConnection() {
			// Check the settings if a overwrite username and password are set
		if (empty($this->settings['terConnection']['username']) || empty($this->settings['terConnection']['password'])) {
			$username = $this->frontendUser['username'];
			$password = $this->frontendUser['password'];
		} else {
			$username = $this->settings['terConnection']['username'];
			$password = $this->settings['terConnection']['password'];
		}

			// Check the wsdl uri
		if (empty($this->settings['terConnection']['wsdl'])) {
			throw new Exception($this->translate('registerkey.noWsdl'));
		}

			// Create connection
		$wsdl = $this->settings['terConnection']['wsdl'];
		return $this->objectManager->create('Tx_TerFe2_Service_Ter', $wsdl, $username, $password);
	}


	/**
	 * resolve the error key and get the corresponding translation
	 *
	 * @param string $error
	 * @return string $message already translated
	 */
	protected function resolveWSErrorMessage($error) {
		return $this->translate('registerkey.error.'.$error);
	}

}
?>