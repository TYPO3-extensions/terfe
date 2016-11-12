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
 * Controller for the media object
 */
class Tx_TerFe2_Controller_MediaController extends Tx_TerFe2_Controller_AbstractController
{

    /**
     * @var Tx_TerFe2_Domain_Repository_MediaRepository
     */
    protected $mediaRepository;


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
        $this->mediaRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_MediaRepository');
    }


    /**
     * List action, displays all media
     *
     * @return void
     */
    public function listAction()
    {
        $this->view->assign('media', $this->mediaRepository->findAll());
    }


    /**
     * Action that displays a single media
     *
     * @param Tx_TerFe2_Domain_Model_Category $media The media to display
     * @return void
     */
    public function showAction(Tx_TerFe2_Domain_Model_Media $media)
    {
        $this->view->assign('media', $media);
    }


    /**
     * Displays a form for creating a new media
     *
     * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to add the new media
     * @param Tx_TerFe2_Domain_Model_Media $newMedia New media object
     * @return void
     * @dontvalidate $newMedia
     */
    public function newAction(Tx_TerFe2_Domain_Model_Extension $extension, Tx_TerFe2_Domain_Model_Media $newMedia = NULL)
    {
        $this->view->assign('newMedia', $newMedia);
        $this->view->assign('extension', $extension);
    }


    /**
     * Creates a new media
     *
     * @param Tx_TerFe2_Domain_Model_Media $newMedia New media object
     * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to add the new media
     * @return void
     */
    public function createAction(Tx_TerFe2_Domain_Model_Media $newMedia, Tx_TerFe2_Domain_Model_Extension $extension)
    {
        if ($media = $this->mediaRepository->findByTitle($newMedia->getTitle())->getFirst()) {
            $extension->addMedia($media);
        } else {
            $this->mediaRepository->add($newMedia);
            $extension->addMedia($newMedia);
        }
        $actionParameters = array('extension' => $extension);
        $this->redirectWithMessage($this->translate('msg.media_created'), 'show', '', t3lib_FlashMessage::OK, 'Extension', NULL, $actionParameters);
    }


    /**
     * Displays a form to edit an existing media
     *
     * @param Tx_TerFe2_Domain_Model_Media $media The media to display
     * @return void
     * @dontvalidate $media
     */
    public function editAction(Tx_TerFe2_Domain_Model_Media $media)
    {
        $this->view->assign('media', $media);
    }


    /**
     * Updates an existing media
     *
     * @param Tx_TerFe2_Domain_Model_Media $media Media to update
     * @return void
     */
    public function updateAction(Tx_TerFe2_Domain_Model_Media $media)
    {
        $this->mediaRepository->update($media);
        // TODO: Update extension too
        $this->redirectWithMessage($this->translate('msg.media_updated'), 'list');
    }


    /**
     * Deletes an existing media
     *
     * @param Tx_TerFe2_Domain_Model_Media $media The media to delete
     * @return void
     */
    public function deleteAction(Tx_TerFe2_Domain_Model_Media $media)
    {
        $this->mediaRepository->remove($media);
        // TODO: Remove from extension too
        $this->redirectWithMessage($this->translate('msg.media_deleted'), 'list');
    }

}

?>