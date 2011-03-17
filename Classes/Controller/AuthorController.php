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
	 * Controller for the Author object
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Controller_AuthorController extends Tx_Extbase_MVC_Controller_ActionController {

		/**
		 * @var Tx_TerFe2_Domain_Repository_AuthorRepository
		 */
		protected $authorRepository;


		/**
		 * Initializes the current action
		 *
		 * @return void
		 */
		protected function initializeAction() {
			$this->authorRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_AuthorRepository');

			// Pre-parse TypoScript setup
			$this->settings = Tx_TerFe2_Utility_TypoScript::parse($this->settings);
		}


		/**
		 * Index action, displays all authors
		 *
		 * @return void
		 */
		public function indexAction() {
			$this->view->assign('authors', $this->authorRepository->findAll());
		}


		/**
		 * Displays a form for creating a new Author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $newAuthor A fresh Author object taken as a basis for the rendering
		 * @return void
		 * @dontvalidate $newAuthor
		 */
		public function newAction(Tx_TerFe2_Domain_Model_Author $newAuthor = NULL) {
			$this->view->assign('newAuthor', $newAuthor);
		}


		/**
		 * Creates a new Author and forwards to the index action
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $newAuthor A fresh Author object which has not yet been added to the repository
		 * @return void
		 */
		public function createAction(Tx_TerFe2_Domain_Model_Author $newAuthor) {
			$this->authorRepository->add($newAuthor);
			$this->flashMessageContainer->add($this->translate('msg_author_created'));
			$this->redirect('index');
		}


		/**
		 * Displays a form to edit an existing Author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author The Author to display
		 * @return void
		 * @dontvalidate $author
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Author $author) {
			$this->view->assign('author', $author);
		}


		/**
		 * Updates an existing Author and forwards to the index action afterwards
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author Author to update
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Author $author) {
			$this->authorRepository->update($author);
			$this->flashMessageContainer->add($this->translate('msg_author_updated'));
			$this->redirect('index');
		}


		/**
		 * Deletes an existing Author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author The Author to be deleted
		 * @return void
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Author $author) {
			$this->authorRepository->remove($author);
			$this->flashMessageContainer->add($this->translate('msg_author_deleted'));
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