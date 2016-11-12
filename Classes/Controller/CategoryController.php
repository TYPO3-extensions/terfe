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
 * Controller for the category object
 */
class Tx_TerFe2_Controller_CategoryController extends Tx_TerFe2_Controller_AbstractController
{

    /**
     * @var Tx_TerFe2_Domain_Repository_CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
     */
    protected $extensionRepository;


    /**
     * Initializes the controller
     *
     * @return void
     */
    protected function initializeController()
    {
        $this->categoryRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_CategoryRepository');
        $this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionRepository');
    }


    /**
     * List action, displays all categories
     *
     * @return void
     */
    public function listAction()
    {
        $this->view->assign('categories', $this->categoryRepository->findAll());
    }


    /**
     * Action that displays a single category
     *
     * @param Tx_TerFe2_Domain_Model_Category $category The category to display
     * @return void
     */
    public function showAction(Tx_TerFe2_Domain_Model_Category $category)
    {
        $this->view->assign('category', $category);
        $categoryExtensions = $this->extensionRepository->findByCategory($category);
        $this->view->assign('categoryExtensions', $categoryExtensions);
    }


    /**
     * Displays a form for creating a new category
     *
     * @param Tx_TerFe2_Domain_Model_Category $newCategory New category object
     * @return void
     * @dontvalidate $newCategory
     */
    public function newAction(Tx_TerFe2_Domain_Model_Category $newCategory = NULL)
    {
        $this->view->assign('newCategory', $newCategory);
    }


    /**
     * Creates a new category
     *
     * @param Tx_TerFe2_Domain_Model_Category $newCategory New category object
     * @return void
     */
    public function createAction(Tx_TerFe2_Domain_Model_Category $newCategory)
    {
        $this->categoryRepository->add($newCategory);
        $this->redirectWithMessage($this->translate('msg.category_created'), 'list');
    }


    /**
     * Displays a form to edit an existing category
     *
     * @param Tx_TerFe2_Domain_Model_Category $category The category to display
     * @return void
     * @dontvalidate $category
     */
    public function editAction(Tx_TerFe2_Domain_Model_Category $category)
    {
        $this->view->assign('category', $category);
    }


    /**
     * Updates an existing category
     *
     * @param Tx_TerFe2_Domain_Model_Category $category Category to update
     * @return void
     */
    public function updateAction(Tx_TerFe2_Domain_Model_Category $category)
    {
        $this->categoryRepository->update($category);
        $this->redirectWithMessage($this->translate('msg.category_updated'), 'list');
    }


    /**
     * Deletes an existing category
     *
     * @param Tx_TerFe2_Domain_Model_Category $category The category to delete
     * @return void
     */
    public function deleteAction(Tx_TerFe2_Domain_Model_Category $category)
    {
        $this->categoryRepository->remove($category);
        $this->redirectWithMessage($this->translate('msg.category_deleted'), 'list');
    }

}

?>