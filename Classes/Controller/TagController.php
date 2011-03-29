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
	 * Controller for the Tag object
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Controller_TagController extends Tx_Extbase_MVC_Controller_ActionController {

		/**
		 * @var Tx_TerFe2_Domain_Repository_TagRepository
		 */
		protected $tagRepository;


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
			$this->tagRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_TagRepository');

			// Pre-parse TypoScript setup
			$this->settings = Tx_TerFe2_Utility_TypoScript::parse($this->settings);
		}


		/**
		 * Index action, displays all categories
		 *
		 * @return void
		 */
		public function indexAction() {
			$this->view->assign('tags', $this->tagRepository->findAll());
		}


		/**
		 * Displays a form for creating a new Tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to add the new tag
		 * @param Tx_TerFe2_Domain_Model_Tag $newTag A fresh Tag object taken as a basis for the rendering
		 * @return void
		 * @dontvalidate $newTag
		 */
		public function newAction(Tx_TerFe2_Domain_Model_Extension $extension, Tx_TerFe2_Domain_Model_Tag $newTag = NULL) {
			$this->view->assign('newTag', $newTag);
			$this->view->assign('extension', $extension);
		}


		/**
		 * Creates a new Tag and forwards to the index action
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $newTag A fresh Tag object which has not yet been added to the repository
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to add the new tag
		 * @return void
		 */
		public function createAction(Tx_TerFe2_Domain_Model_Tag $newTag, Tx_TerFe2_Domain_Model_Extension $extension) {
			if ($tag = $this->tagRepository->findByTitle($newTag->getTitle())->getFirst()) {
				$extension->addTag($tag);
			} else {
				$this->tagRepository->add($newTag);
				$extension->addTag($newTag);
			}
			$this->flashMessageContainer->add($this->translate('msg.tag_created'));
			$this->redirect('show', 'Extension', NULL, array('extension' => $extension));
		}


		/**
		 * Displays a form to edit an existing Tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag The Tag to display
		 * @return void
		 * @dontvalidate $tag
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->view->assign('tag', $tag);
		}


		/**
		 * Updates an existing Tag and forwards to the index action afterwards
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag Tag to update
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tagRepository->update($tag);
			$this->flashMessageContainer->add($this->translate('msg.tag_updated'));
			$this->redirect('index');
		}


		/**
		 * Deletes an existing Tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag The Tag to be deleted
		 * @return void
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tagRepository->remove($tag);
			$this->flashMessageContainer->add($this->translate('msg.tag_deleted'));
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
			$extensionKey = $this->request->getControllerExtensionKey();
			return Tx_Extbase_Utility_Localization::translate($label, $extensionKey, $arguments);
		}

	}
?>