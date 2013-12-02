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
	 * Controller for the tag object
	 */
	class Tx_TerFe2_Controller_TagController extends Tx_TerFe2_Controller_AbstractController {

		/**
		 * @var Tx_TerFe2_Domain_Repository_TagRepository
		 */
		protected $tagRepository;


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
			$this->tagRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_TagRepository');
		}


		/**
		 * List action, displays all tags
		 *
		 * @return void
		 */
		public function listAction() {
			$this->view->assign('tags', $this->tagRepository->findAll());
		}


		/**
		 * Action that displays a single tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Category $tag The tag to display
		 * @return void
		 */
		public function showAction(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->view->assign('tag', $tag);
		}


		/**
		 * Displays a form for creating a new tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to add the new tag
		 * @param Tx_TerFe2_Domain_Model_Tag $newTag New tag object
		 * @return void
		 * @dontvalidate $newTag
		 */
		public function newAction(Tx_TerFe2_Domain_Model_Extension $extension, Tx_TerFe2_Domain_Model_Tag $newTag = NULL) {
			$this->view->assign('newTag', $newTag);
			$this->view->assign('extension', $extension);
		}


		/**
		 * Creates a new tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $newTag New tag object
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
		 * Displays a form to edit an existing tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag The tag to display
		 * @return void
		 * @dontvalidate $tag
		 */
		public function editAction(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->view->assign('tag', $tag);
		}


		/**
		 * Updates an existing tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag Tag to update
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tagRepository->update($tag);
			// TODO: Update extension too
			$this->flashMessageContainer->add($this->translate('msg.tag_updated'));
			$this->redirect('list');
		}


		/**
		 * Deletes an existing tag
		 *
		 * @param Tx_TerFe2_Domain_Model_Tag $tag The tag to delete
		 * @return void
		 */
		public function deleteAction(Tx_TerFe2_Domain_Model_Tag $tag) {
			$this->tagRepository->remove($tag);
			// TODO: Remove from extension too
			$this->flashMessageContainer->add($this->translate('msg.tag_deleted'));
			$this->redirect('list');
		}

	}
?>