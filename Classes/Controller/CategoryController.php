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
	 * Controller for the Category object
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Controller_CategoryController extends Tx_Extbase_MVC_Controller_ActionController {

		/**
		 * @var Tx_TerFe2_Domain_Repository_CategoryRepository
		 */
		protected $categoryRepository;

		/**
		 * @var Tx_TerFe2_Service_TypoScriptParserService
		 */
		protected $typoScriptParser;


		/**
		 * Initializes the current action
		 *
		 * @return void
		 */
		protected function initializeAction() {
			$this->categoryRepository  = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_CategoryRepository');
			$this->typoScriptParser    = t3lib_div::makeInstance('Tx_TerFe2_Service_TypoScriptParserService');

			// Pre-parse TypoScript setup
			$this->settings = $this->typoScriptParser->getParsed($this->settings);
		}


		/**
		 * Index action, displays all categories
		 */
		public function indexAction() {
			$this->view->assign('categories', $this->categoryRepository->findAll());
		}


		/**
		 * Displays a form for creating a new Category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $newCategory A fresh Category object taken as a basis for the rendering
		 * @dontvalidate $newCategory
		 */
		public function newAction(Tx_TerFe2_Domain_Model_Category $newCategory = NULL) {
			$this->view->assign('newCategory', $newCategory);
		}


		/**
		 * Creates a new Category and forwards to the index action
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $newCategory A fresh Category object which has not yet been added to the repository
		 */
		public function createAction(Tx_TerFe2_Domain_Model_Category $newCategory) {
			$this->categoryRepository->add($newCategory);
			$this->flashMessageContainer->add($this->translate('msg_category_created'));
			$this->redirect('index');
		}


		/**
		 * Displays a form to edit an existing Category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $category The Category to display
		 * @dontvalidate $category
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Category $category) {
			$this->view->assign('category', $category);
		}


		/**
		 * Updates an existing Category and forwards to the index action afterwards
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $category Category to update
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Category $category) {
			$this->categoryRepository->update($category);
			$this->flashMessageContainer->add($this->translate('msg_category_updated'));
			$this->redirect('index');
		}


		/**
		 * Deletes an existing Category
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $category The Category to be deleted
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Category $category) {
			$this->categoryRepository->remove($category);
			$this->flashMessageContainer->add($this->translate('msg_category_deleted'));
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