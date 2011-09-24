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
		 * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
		 */
		protected $extensionRepository;


		/**
		 * Initializes the controller
		 *
		 * @return void
		 */
		protected function initializeController() {
			$this->authorRepository    = $this->objectManager->get('Tx_TerFe2_Domain_Repository_AuthorRepository');
			$this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionRepository');
		}


		/**
		 * List action, displays all authors
		 *
		 * @return void
		 */
		public function listAction() {
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
			$authorExtensions = $this->extensionRepository->findByAuthor($author);
			$this->view->assign('authorExtensions', $authorExtensions);
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
			$this->redirectWithMessage('list', 'author_updated');
		}

	}
?>