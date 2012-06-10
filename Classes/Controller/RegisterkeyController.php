<?php

/* * *****************************************************************
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
 * **************************************************************** */

/**
 * Controller for the extension key registration
 */
class Tx_TerFe2_Controller_RegisterkeyController extends Tx_TerFe2_Controller_AbstractTerBasedController {

	/**
	 * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
	 */
	protected $extensionRepository;

	/**
	 * @var Tx_TerFe2_Domain_Repository_VersionRepository
	 */
	protected $versionRepository;

	/**
	 * @var Tx_TerFe2_Domain_Repository_CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * Initializes the controller
	 *
	 * @return void
	 */
	protected function initializeController() {
		$this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionRepository');
		$this->versionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
		$this->categoryRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_CategoryRepository');
	}

	/**
	 * Initialize all actions
	 *
	 * @return void
	 */
	public function indexAction() {
		// get categories for register key
//			$categories = $this->categoryRepository->findAll();
//			$this->view->assign('categories', $categories);
		// get extensions by user if a user is logged in
		if (!empty($this->frontendUser)) {
			$extensions = $this->extensionRepository->findByFrontendUser($this->frontendUser['username']);
			$this->view->assign('extensions', $extensions);
		}
	}

	/**
	 * Register a new extension
	 *
	 * @param string $extensionKey Extension key
	 * @return void
	 */
	public function createAction($extensionKey/* , $categories */) {

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
				$extension->setFrontendUser($this->frontendUser['username']);

				// Add categories
//					foreach ($categories as $category) {
//						if (isset($category['__identity']) && is_numeric($category['__identity'])) {
//							$myCat = $this->categoryRepository->findByUid((int) $category['__identity']);
//							if ($myCat != NULL) {
//								$extension->addCategory($myCat);
//							}
//						}
//					}

				$this->extensionRepository->add($extension);
				$this->flashMessageContainer->add(
						'', $this->translate('registerkey.key_registered'), t3lib_FlashMessage::OK
				);
				$this->redirect('index', 'Registerkey');
			} else {
				$this->flashMessageContainer->add(
						$this->resolveWSErrorMessage('not_register.message'), $this->resolveWSErrorMessage('not_register.title'), t3lib_FlashMessage::ERROR
				);
			}
		} else {
			$this->flashMessageContainer->add(
					$this->resolveWSErrorMessage($error . '.message'), $this->resolveWSErrorMessage($error . '.title'), t3lib_FlashMessage::ERROR
			);
		}

		$this->redirect('index', 'Registerkey', NULL, array());
	}

	/**
	 * an action to salvage the keys that were registered on the old TYPO3.org, but never had uploads
	 *
	 * @author Christian Zenker <christian.zenker@599media.de>
	 */
	public function salvageAction() {
		$error = null;
		$registeredExtensions = $this->terConnection->getExtensionKeysByUser($error);
		if ($error) {
			$this->flashMessageContainer->add(
					$this->resolveWSErrorMessage($error), '', t3lib_FlashMessage::ERROR
			);
		} elseif (!is_array($registeredExtensions)) {
			$this->flashMessageContainer->add(
					$this->resolveWSErrorMessage('result_empty.message'), $this->resolveWSErrorMessage('result_empty.title'), t3lib_FlashMessage::ERROR
			);
		} elseif (empty($registeredExtensions)) {
			$this->flashMessageContainer->add(
					$this->resolveWSErrorMessage('nothing_found'), '', t3lib_FlashMessage::WARNING
			);
		} else {

			$countSkipped = 0;
			$countSalvaged = 0;

			// get an array of the already existent extension keys
			$existingExtensionKeys = $this->getExistingExtensionsByList($registeredExtensions);

			foreach ($registeredExtensions as $extension) {
				$extensionKey = $extension['extensionkey'];
				if (in_array($extensionKey, $existingExtensionKeys)) {
					// if: key already exists
//                        $this->flashMessageContainer->add(
//                            sprintf('%s already exists.', $extensionKey),
//                            '',
//                            t3lib_FlashMessage::NOTICE
//                        );
					$countSkipped++;
				} else {
					$extensionModel = $this->objectManager->create('Tx_TerFe2_Domain_Model_Extension');
					$extensionModel->setExtKey($extensionKey);
					$extensionModel->setFrontendUser($extension['ownerusername']);

					$this->extensionRepository->add($extensionModel);
//                        $this->flashMessageContainer->add(
//                            '',
//                            sprintf('%s salvaged.', $extensionKey ),
//                            t3lib_FlashMessage::OK
//                        );

					$countSalvaged++;
				}
			}
			if ($countSalvaged > 0) {
				$this->flashMessageContainer->add(
						$this->translate('registerkey.salvage.success', array($countSalvaged)), '', t3lib_FlashMessage::OK
				);
			} elseif ($countSkipped == 1) {
				$this->flashMessageContainer->add(
						$this->translate('registerkey.salvage.pass1.message'), $this->translate('registerkey.salvage.pass.title'), t3lib_FlashMessage::WARNING
				);
			} elseif ($countSkipped > 1) {
				$this->flashMessageContainer->add(
						$this->translate('registerkey.salvage.pass.message', array($countSkipped)), $this->translate('registerkey.salvage.pass.title'), t3lib_FlashMessage::WARNING
				);
			} else {
				$this->flashMessageContainer->add(
						$this->resolveWSErrorMessage('nothing_found'), '', t3lib_FlashMessage::WARNING
				);
			}
		}



		$this->redirect('index', 'Registerkey');
	}

	protected function getExistingExtensionsByList($extensions) {
		/**
		 * @var array that just holds the given extensionkeys and no meta data
		 */
		$keys = array();
		foreach ($extensions as $extension) {
			$keys[] = $extension['extensionkey'];
		}

		if (empty($keys)) {
			return array();
		}


		// query database for existing keys
		$e = $this->extensionRepository->findByExtKeys($keys);
		// strip only the keys from the model
		$existingExtensionKeys = array();
		foreach ($e as $extension) {
			$existingExtensionKeys[] = $extension->getExtKey();
		}

		return $existingExtensionKeys;
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
					$this->flashMessageContainer->add(
							$this->resolveWSErrorMessage($error . '.message'), $this->resolveWSErrorMessage($error . '.title'), t3lib_FlashMessage::ERROR
					);
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
		foreach ($categories as $category) {
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

		$newUser = trim($newUser);
		if ($newUser == '') {
			$this->flashMessageContainer->add(
					'', $this->translate('registerkey.newuserempty'), t3lib_FlashMessage::ERROR
			);
		} elseif ($extension->getFrontendUser() == $GLOBALS['TSFE']->fe_user->user['username']) {

			// check if the extension belongs to the current user

			$error = '';

			// Is it possible to assign the key to a new user
			if ($this->terConnection->assignExtensionKey($extension->getExtKey(), $newUser, $error)) {
				$extension->setFrontendUser($newUser);
				$this->extensionRepository->update($extension);
				$this->flashMessageContainer->add($this->translate('registerkey.keyTransfered', array($extension->getExtKey(), $newUser)));
			} else {
				$this->flashMessageContainer->add(
						$this->resolveWSErrorMessage($error), $this->translate('registerkey.transferError.title', array($extension->getExtKey())), t3lib_FlashMessage::ERROR
				);
			}
		} else {
			$this->flashMessageContainer->add(
					'', $this->translate('registerkey.notyourextension'), t3lib_FlashMessage::ERROR
			);
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

		if ($extension->getVersionCount() > 0) {
			$this->flashMessageContainer->add(
					$this->translate('registerkey.deleting_prohibited', array($extension->getExtKey())), '', t3lib_FlashMessage::ERROR
			);
		} elseif ($extension->getFrontendUser() == $GLOBALS['TSFE']->fe_user->user['username']) {

			// Deleted in ter, then delete the key in the ter_fe2 extension table
			if ($this->terConnection->deleteExtensionKey($extension->getExtKey())) {
				$this->extensionRepository->remove($extension);
				$this->flashMessageContainer->add(
						'', $this->translate('registerkey.deleted', array($extension->getExtKey())), t3lib_FlashMessage::OK
				);
			} else {
				$this->flashMessageContainer->add(
						$this->resolveWSErrorMessage('cannotbedeleted.message', array($extension->getExtKey())), $this->resolveWSErrorMessage('cannotbedeleted.title', array($extension->getExtKey())), t3lib_FlashMessage::ERROR
				);
			}
		} else {
			$this->flashMessageContainer->add($this->translate('registerkey.notyourextension'));
		}

		$this->redirect('index', 'Registerkey');
	}

	/**
	 * Delete an extension version from ter server
	 *
	 * @param Tx_TerFe2_Domain_Model_Version $version Extension to delete
	 * @dontvalidate $version
	 * @return void
	 */
	public function deleteExtensionVersionAction(Tx_TerFe2_Domain_Model_Version $version) {
		if (!$this->securityRole->isAdmin()) {
			$this->flashMessageContainer->add(
					$this->resolveWSErrorMessage('not_admin.message'), $this->resolveWSErrorMessage('not_admin.title'), t3lib_FlashMessage::ERROR
			);
			$this->redirect('index');
		}

		// Deleted in ter, then delete the version (and probably the extension) in the ter_fe2 extension table
		if ($this->terConnection->deleteExtension($version->getExtension()->getExtKey(), $version->getVersionString())) {
			$version->getExtension()->removeVersion($version);
			$this->versionRepository->remove($version);
			if ($version->getExtension()->getLastVersion() === NULL) {
				$this->extensionRepository->remove($version->getExtension());
			}
			$this->flashMessageContainer->add(
					'', $this->translate('registerkey.version_deleted', array($version->getVersionString(), $version->getExtension()->getExtKey())), t3lib_FlashMessage::OK
			);
		} else {
			$this->flashMessageContainer->add(
					$this->resolveWSErrorMessage('extensioncannotbedeleted.message', array($version->getExtension()->getExtKey())), $this->resolveWSErrorMessage('extensioncannotbedeleted.title', array($version->getExtension()->getExtKey())), t3lib_FlashMessage::ERROR
			);
		}

		$this->redirect('admin', 'Registerkey', NULL, array('extKey'=>$version->getExtension()->getExtKey()));
	}

	/**
	 * Show all extensions for ter admins
	 *
	 * @param string $extensionKey
	 */
	public function adminAction($extensionKey = '') {
		if (!$this->securityRole->isAdmin()) {
			$this->flashMessageContainer->add(
					$this->resolveWSErrorMessage('no_admin.message'), $this->resolveWSErrorMessage('no_admin.title'), t3lib_FlashMessage::ERROR
			);
			$this->redirect('index');
		}

		$this->extensionRepository->setDefaultOrderings(
				array('extKey' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING)
		);
		if (!$extensionKey) {
			$this->view->assign('adminExtensions', $this->extensionRepository->findAllAdmin());
		} else {
			$this->view->assign('adminExtensions', $this->extensionRepository->findByExtKey($extensionKey));
		}
	}

	/**
	 * resolve the error key and get the corresponding translation
	 *
	 * @param string $error
	 * @param array $arguments
	 * @return string $message already translated
	 */
	protected function resolveWSErrorMessage($error, $arguments = array()) {
		return $this->translate('registerkey.error.' . $error, $arguments);
	}

}

?>