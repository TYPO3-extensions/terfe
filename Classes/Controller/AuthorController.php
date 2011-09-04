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
	 * Controller for the author object
	 */
	class Tx_TerFe2_Controller_AuthorController extends Tx_TerFe2_Controller_AbstractController {

		/**
		 * @var Tx_TerFe2_Domain_Repository_AuthorRepository
		 */
		protected $authorRepository;


		/**
		 * Initializes the controller
		 *
		 * @return void
		 */
		protected function initializeController() {
			$this->authorRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_AuthorRepository');
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
		 * Action that displays a single author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author The author to display
		 * @return void
		 */
		public function showAction(Tx_TerFe2_Domain_Model_Author $author) {
			$this->view->assign('author', $author);
		}


		/**
		 * Displays a form for creating a new author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $newAuthor New author object
		 * @return void
		 * @dontvalidate $newAuthor
		 */
		public function newAction(Tx_TerFe2_Domain_Model_Author $newAuthor = NULL) {
			$this->view->assign('newAuthor', $newAuthor);
		}


		/**
		 * Creates a new author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $newAuthor New author object
		 * @return void
		 */
		public function createAction(Tx_TerFe2_Domain_Model_Author $newAuthor) {
			$this->authorRepository->add($newAuthor);
			$this->flashMessageContainer->add($this->translate('msg.author_created'));
			$this->redirect('index');
		}


		/**
		 * Displays a form to edit an existing author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author The author to display
		 * @return void
		 * @dontvalidate $author
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Author $author) {
			$this->view->assign('author', $author);
		}


		/**
		 * Updates an existing author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author Author to update
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Author $author) {
			$this->authorRepository->update($author);
			$this->flashMessageContainer->add($this->translate('msg.author_updated'));
			$this->redirect('index');
		}


		/**
		 * Deletes an existing author
		 *
		 * @param Tx_TerFe2_Domain_Model_Author $author The author to delete
		 * @return void
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Author $author) {
			$this->authorRepository->remove($author);
			$this->flashMessageContainer->add($this->translate('msg.author_deleted'));
			$this->redirect('index');
		}

	}
?>