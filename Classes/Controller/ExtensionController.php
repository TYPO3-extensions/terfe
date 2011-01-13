<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
	 *           Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
		 * Initializes the current action
		 *
		 * @return void
		 */
		protected function initializeAction() {
			$this->extensionRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_ExtensionRepository');
		}


		/**
		 * List action for this controller. Displays all Extensions.
		 */
		public function indexAction() {
			$extensions = $this->extensionRepository->findAll();
			$this->view->assign('extensions', $extensions);
		}


		/**
		 * Action that displays a single Extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The Extension to display
		 */
		public function showAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->view->assign('extension', $extension);
		}


		/**
		 * Displays a form for creating a new Extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $newExtension A fresh Extension object taken as a basis for the rendering
		 * @dontvalidate $newExtension
		 */
		public function newAction(Tx_TerFe2_Domain_Model_Extension $newExtension = NULL) {
			$this->view->assign('newExtension', $newExtension);
		}


		/**
		 * Creates a new Extension and forwards to the index action.
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $newExtension A fresh Extension object which has not yet been added to the repository
		 */
		public function createAction(Tx_TerFe2_Domain_Model_Extension $newExtension) {
			$this->extensionRepository->add($newExtension);
			$this->flashMessageContainer->add('Your new Extension was created.');
			$this->redirect('index');
		}


		/**
		 * Displays a form to edit an existing Extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The Extension to display
		 * @dontvalidate $extension
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->view->assign('extension', $extension);
		}


		/**
		 * Updates an existing Extension and forwards to the index action afterwards.
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The Extension to display
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->extensionRepository->update($extension);
			$this->flashMessageContainer->add('Your Extension was updated.');
			$this->redirect('index');
		}


		/**
		 * Deletes an existing Extension
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The Extension to be deleted
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$this->extensionRepository->remove($extension);
			$this->flashMessageContainer->add('Your Extension was removed.');
			$this->redirect('index');
		}

	}
?>