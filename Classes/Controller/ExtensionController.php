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
 * Controller for the extension object
 */
class Tx_TerFe2_Controller_ExtensionController extends Tx_TerFe2_Controller_AbstractController
{

    /**
     * @var Tx_TerFe2_Domain_Repository_ExtensionRepository
     */
    protected $extensionRepository;

    /**
     * @var Tx_TerFe2_Domain_Repository_CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var Tx_TerFe2_Domain_Repository_TagRepository
     */
    protected $tagRepository;

    /**
     * @var Tx_TerFe2_Domain_Repository_AuthorRepository
     */
    protected $authorRepository;

    /**
     * @var Tx_TerFe2_Domain_Repository_VersionRepository
     */
    protected $versionRepository;

    /**
     * @var Tx_TerFe2_Provider_ProviderManager
     */
    protected $providerManager;

    /**
     * @var Tx_TerFe2_Persistence_Session
     */
    protected $session;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var Tx_T3oAjaxlogin_Domain_Repository_UserRepository
     */
    protected $ownerRepository;

    /**
     * @var array
     */
    protected $frontendUser;

    /**
     * Initializes the controller
     *
     * @return void
     */
    protected function initializeController()
    {
        $this->extensionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_ExtensionRepository');
        $this->categoryRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_CategoryRepository');
        $this->tagRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_TagRepository');
        $this->versionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
        $this->authorRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_AuthorRepository');
        $this->ownerRepository = $this->objectManager->get('Tx_T3oAjaxlogin_Domain_Repository_UserRepository');
        $this->providerManager = $this->objectManager->get('Tx_TerFe2_Provider_ProviderManager');
        $this->session = $this->objectManager->get('Tx_TerFe2_Persistence_Session');
        $this->persistenceManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);

        // Show insecure extensions only for reviewers
        $this->extensionRepository->setShowInsecure($this->securityRole->isReviewer());
        $this->versionRepository->setShowInsecure($this->securityRole->isReviewer());

        $this->frontendUser = (!empty($GLOBALS['TSFE']->fe_user->user) ? $GLOBALS['TSFE']->fe_user->user : array());
    }

    /**
     * Index action, displays extension list (USER)
     *
     * @return void
     * @dontvalidate $search
     */
    public function indexAction()
    {
        $this->searchAction();
    }


    /**
     * search action (USER_INT)
     *
     * @param array $search Search params for extension list
     * @param array $restoreSearch Restore last search from session
     * @return void
     * @dontvalidate $search
     */
    public function searchAction(array $search = array(), $restoreSearch = FALSE)
    {
        // Get extension list
        if (!empty($this->settings['show']['extensionSearch'])) {
            $this->view->assign('extensions', $this->getExtensions($search, $restoreSearch));
            $this->view->assign('search', $search);
        } else {
            $this->view->assign('extensions', $this->extensionRepository->findAll());
        }

        // Get all categories
        if (!empty($this->settings['show']['categoryOverview'])) {
            $categories = $this->categoryRepository->findAll();
            $this->view->assign('categories', $categories);
        }

        // Get all tags
        if (!empty($this->settings['show']['tagOverview'])) {
            $tags = $this->tagRepository->findAll();
            $this->view->assign('tags', $tags);
        }

        // Get authors
        if (!empty($this->settings['show']['authorOverview'])) {
            $authors = $this->authorRepository->findByLatestExtensionVersion();
            $this->view->assign('authors', $authors);
        }
    }


    /**
     * List action, displays all extensions
     *
     * Note: Required for RSS / JSON output
     *
     * @return void
     */
    public function listAction()
    {
        $this->view->assign('extensions', $this->extensionRepository->findAll());
    }


    /**
     * List latest action, displays new and updated extensions
     *
     * Note: Required for RSS / JSON output
     *
     * @return void
     */
    public function listLatestAction()
    {
        $latestCount = (!empty($this->settings['latestCount']) ? $this->settings['latestCount'] : 20);
        $extensions = $this->extensionRepository->findLatest($latestCount);
        if ($extensions->count() > 0) {
            $this->updateSysLastChanged($extensions[0]->getLastVersion()->getUploadDate());
        }
        $this->view->assign('extensions', $extensions);
    }


    /**
     * Action that displays a single extension
     *
     * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to display
     * @param string $extensionKey Extension key
     * @return void
     * @dontvalidate $extension
     * @dontvalidate $extensionKey
     */
    public function showAction(Tx_TerFe2_Domain_Model_Extension $extension = NULL, $extensionKey = NULL)
    {
        if (!empty($extensionKey)) {
            if (!is_string($extensionKey)) {
                throw new Exception('No valid extension key given');
            }
            $extension = $this->extensionRepository->findOneByExtKey($extensionKey);
        }
        $owner = array();
        if ($extension instanceof Tx_TerFe2_Domain_Model_Extension and $extension->getFrontendUser()) {
            $owner = $this->ownerRepository->findOneByUsername($extension->getFrontendUser());
        }
        $versionHistoryCount = (!empty($this->settings['versionHistoryCount']) ? $this->settings['versionHistoryCount'] : 5);
        $skipLatestVersion = (isset($this->settings['skipLatestVersion']) ? $this->settings['skipLatestVersion'] : TRUE);

        $loggedInUser = $this->ownerRepository->findCurrent();

        if ($extension !== NULL &&
            $extension instanceof Tx_TerFe2_Domain_Model_Extension &&
            (
                $this->securityRole->isReviewer() ||
                ($extension->getLastVersion() && $extension->getLastVersion()->getReviewState() != -1)
            )
        ) {
            $versionHistory = $this->versionRepository->getVersionHistory($extension, $versionHistoryCount, $skipLatestVersion);
            $this->view->assign('owner', $owner);
            $this->view->assign('extension', $extension);
            $this->view->assign('versionHistory', $versionHistory);
            $this->view->assign('loggedInUser', $loggedInUser);

            /** @var Tx_TerFe2_Service_Documentation $documentationService */
            $documentationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_TerFe2_Service_Documentation');
            $documentationLink = $documentationService->getDocumentationLink($extension->getExtKey(), $extension->getLastVersion()->getVersionString());
            $this->view->assign('documentationLink', $documentationLink);

            $GLOBALS['TSFE']->getPageRenderer()->addMetaTag('<meta name="description" content="' . htmlspecialchars($extension->getLastVersion()->getDescription()) . '" />');
            if ($extension->getTags()->count() > 0) {
                $GLOBALS['TSFE']->getPageRenderer()->addMetaTag('<meta name="keywords" content="' . htmlspecialchars(implode(',', $extension->getTags()->toArray())) . '" />');
            }

            // gets all other extensions from the owner
            $this->extensionRepository->setDefaultOrderings(
                array(
                    'lastVersion.uploadDate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
                )
            );
            $otherExtensionsByUser = $this->extensionRepository->findAllOtherFromFrontendUser($extension, $extension->getFrontendUser());
            $this->view->assign('extensionsByUser', $otherExtensionsByUser);

            // flattr check
            if ($extension->getFlattrUsername() !== '') {
                // build flattr url with "auto-submit"
                $url = 'https://flattr.com/submit/auto?';
                // adds username
                $url .= '&user_id=' . urlencode($extension->getFlattrUsername());
                // adds current url
                /** @var \TYPO3\CMS\Backend\Routing\UriBuilder $uriBuilder */
                $uriBuilder = $this->controllerContext->getUriBuilder();
                $uriBuilder->setArguments(
                    array(
                        'tx_terfe2_pi1' => array(
                            'action' => 'show',
                            'extension' => $extension->getUid()
                        )
                    )
                );
                $uriBuilder->setCreateAbsoluteUri(TRUE);
                $url .= '&url=' . urlencode($uriBuilder->buildFrontendUri());
                // adds title
                $url .= '&title=' . urlencode($extension->getLastVersion()->getTitle());
                // adds description
                $url .= '&description=' . urlencode($extension->getLastVersion()->getDescription());
                // adds language
                $url .= '&language=en_GB';
                // adds tags
                // @todo maybe add extension tags from user?
                $url .= '&tags=';
                // adds hidden tag
                $url .= '&hidden=0';
                // adds category
                $url .= '&category=software';

                $this->view->assign('flattrUrl', $url);
            }
        }


    }


    /**
     * Displays a form for creating a new extension
     *
     * @param Tx_TerFe2_Domain_Model_Extension $newExtension New extension object
     * @return void
     * @dontvalidate $newExtension
     */
    public function newAction(Tx_TerFe2_Domain_Model_Extension $newExtension = NULL)
    {
        $this->view->assign('newExtension', $newExtension);
        $this->view->assign('categories', $this->categoryRepository->findAll());
        $this->view->assign('tags', $this->tagRepository->findAll());
    }


    /**
     * Creates a new extension
     *
     * @param Tx_TerFe2_Domain_Model_Extension $newExtension New extension object
     * @return void
     */
    public function createAction(Tx_TerFe2_Domain_Model_Extension $newExtension)
    {
        $this->extensionRepository->add($newExtension);
        $this->redirectWithMessage($this->translate('msg.extension_created'), 'index');
    }


    /**
     * Displays a form to edit an existing extension
     *
     * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to display
     * @dontvalidate $extension
     * @return void
     */
    public function editAction(Tx_TerFe2_Domain_Model_Extension $extension)
    {
        $extensionOwner = $this->ownerRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
        if ($this->securityRole->isAdmin()
            || $extension->getFrontendUser() == $extensionOwner->getUsername()
        ) {
            $this->view->assign('isLoggedIn', 1);
            $this->view->assign('extension', $extension);
        }
    }


    /**
     * Updates an existing extension
     *
     * @param Tx_TerFe2_Domain_Model_Extension $extension extension to update
     * @param string $tag
     * @param string $save
     * @return void
     */
    public function updateAction(Tx_TerFe2_Domain_Model_Extension $extension, $tag = '', $save = '')
    {
        /** @var Tx_T3oAjaxlogin_Domain_Model_User $currentUser */
        $currentUser = $this->ownerRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
        if ($extension->getFrontendUser() !== $currentUser->getUsername()) {
            $this->redirectWithError(
                'You are not the owner of the extension you wanted to update.',
                'index',
                'Registerkey'
            );
        }
        if (!empty($tag)) {
            $tags = array();
            $intermediateTags = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(' ', $tag, TRUE);
            foreach ($intermediateTags as $tag) {
                $tag = trim($tag, ',');
                if (!empty($tag)) {
                    $tags[] = $tag;
                }
            }
            $tags = array_unique($tags);
            foreach ($tags as $tag) {
                /** @var Tx_TerFe2_Domain_Model_Tag $newTag */
                $newTag = $this->tagRepository->findByTitle($tag)->getFirst();
                if ($newTag !== NULL) {
                    if (!$extension->getTags()->contains($newTag)) {
                        $extension->addTag($newTag);
                    } else {
                        continue;
                    }
                } else {
                    $newTag = $this->objectManager->get('Tx_TerFe2_Domain_Model_Tag');
                    $newTag->setTitle($tag);
                    $extension->addTag($newTag);
                }
                $this->flashMessageContainer->add('Tag "' . htmlspecialchars($tag) . '" added to extension');
            }
        }
        $this->extensionRepository->update($extension);
        if (!empty($save)) {
            $this->redirectWithMessage(
                $this->translate('msg.extension_updated'),
                'edit',
                '',
                \TYPO3\CMS\Core\Messaging\FlashMessage::OK,
                'Extension',
                NULL,
                array('extension' => $extension)
            );
        } else {
            $this->redirectWithMessage(
                $this->translate('msg.extension_updated'),
                'index',
                '',
                \TYPO3\CMS\Core\Messaging\FlashMessage::OK,
                'Registerkey'
            );
        }
    }

    /**
     * @param Tx_TerFe2_Domain_Model_Extension $extension
     * @param Tx_TerFe2_Domain_Model_Tag $tag
     *
     * @return void
     */
    public function removeTagAction(Tx_TerFe2_Domain_Model_Extension $extension, Tx_TerFe2_Domain_Model_Tag $tag)
    {
        if ($extension->getTags()->contains($tag)) {
            $extension->removeTag($tag);
        }
        $this->redirectWithMessage(
            'Tag "' . htmlspecialchars($tag->getTitle()) . '" was removed',
            'edit',
            '',
            \TYPO3\CMS\Core\Messaging\FlashMessage::OK,
            'Extension',
            NULL,
            array('extension' => $extension)
        );
    }


    /**
     * Deletes an existing extension and all versions
     *
     * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to delete
     * @return void
     */
    public function deleteAction(Tx_TerFe2_Domain_Model_Extension $extension)
    {
        $this->extensionRepository->remove($extension);
        $this->redirectWithMessage($this->translate('msg.extension_deleted'), 'index');
    }


    /**
     * Check file hash, increment download counter and send file to client browser
     *
     * @param Tx_TerFe2_Domain_Model_Extension $extension The extension object
     * @param string $versionString An existing version string
     * @param string $format Format of the file output
     * @return void
     */
    public function downloadAction(Tx_TerFe2_Domain_Model_Extension $extension, $versionString, $format)
    {
        if (!$format) {
            $format = 't3x';
        }
        if ($format !== 't3x' && $format !== 'zip') {
            throw new Exception('A download action for the format "' . $format . '" is not implemented');
        }

        $version = $this->versionRepository->findOneByExtensionAndVersionString($extension, $versionString);
        if (!$version) {
            $this->redirectWithMessage($this->translate('msg.version_not_found'), 'show', '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR, NULL, NULL, array('extension' => $extension));
        }

        // Get file path
        $provider = $this->providerManager->getProvider($version->getExtensionProvider());
        $fileUrl = $provider->getFileUrl($version, $format);

        if ($format === 'zip') {
            // If ZIP does not exist, create it
            if ($fileUrl === '') {
                $t3xFileUrl = $provider->getFileUrl($version, 't3x');
                $zipFileUrl = str_replace('.t3x', '.zip', $t3xFileUrl);
                Tx_TerFe2_Utility_Archive::convertT3xToZip(
                    Tx_TerFe2_Utility_File::getAbsolutePathFromUrl($t3xFileUrl),
                    Tx_TerFe2_Utility_File::getAbsolutePathFromUrl($zipFileUrl)
                );

                // update ZIP filesize
                $version->setZipFileSize(filesize(Tx_TerFe2_Utility_File::getAbsolutePathFromUrl($zipFileUrl)));
                $this->versionRepository->update($version);
                $this->persistenceManager->persistAll();

                $fileUrl = $zipFileUrl;
            }
        }

        // Check if file exists
        if (empty($fileUrl) || !Tx_TerFe2_Utility_File::fileExists($fileUrl)) {
            $this->redirectWithMessage($this->translate('msg.file_not_found') . ': ' . basename($fileUrl), 'show', '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR, NULL, NULL, array('extension' => $extension));
        }

        // Check file hash of t3x packages
        if ($format === 't3x') {
            $fileHash = Tx_TerFe2_Utility_File::getFileHash($fileUrl);
            if ($fileHash != $version->getFileHash()) {
                $this->redirectWithMessage($this->translate('msg.file_hash_not_equal'), 'show', '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR, NULL, NULL, array('extension' => $extension));
            }
        }

        // Check session if user has already downloaded this file today
        if (!empty($this->settings['countDownloads'])) {
            $extensionKey = $extension->getExtKey();
            $downloads = $this->session->get('downloads');
            if (empty($downloads) || !in_array($extensionKey, $downloads)) {
                // Add +1 to download counter and save immediately
                $version->incrementDownloadCounter();
                $this->persistenceManager->persistAll();

                // Add extension key to session
                $downloads[] = $extensionKey;
                $this->session->add('downloads', $downloads);
            }
        }

        // Send file to browser
        if (!Tx_TerFe2_Utility_File::transferFile($fileUrl)) {
            $this->redirectWithMessage($this->translate('msg.could_not_transfer_file'), 'show', '', \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR, NULL, NULL, array('extension' => $extension));
        }

        // Fallback
        $this->redirect('index');
    }


    /**
     * Show upload form for a new extension version
     *
     * @param Tx_TerFe2_Domain_Model_Extension $extension The extension object
     * @param array $form Form information for the new version
     * @return void
     * @dontvalidate $extension
     * @dontvalidate $form
     */
    public function uploadVersionAction(Tx_TerFe2_Domain_Model_Extension $extension, array $form = array())
    {
        if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ter')) {
            $this->flashMessageContainer->add($this->translate('msq.createVersionTerNotLoaded'));
        }
        $this->view->assign('extension', $extension);
        $this->view->assign('form', $form);
    }


    /**
     * Disable HMAC validation for createVersionAction to prevent validation
     * errors with modified form fields
     *
     * @return void
     */
    public function initializeCreateVersionAction()
    {
        $this->request->setHmacVerified(TRUE);
    }


    /**
     * Create new version of an extension
     *
     * @param Tx_TerFe2_Domain_Model_Extension $extension The extension object
     * @param array $form Form information for the new version
     * @return void
     * @dontvalidate $extension
     * @dontvalidate $form
     */
    public function createVersionAction(Tx_TerFe2_Domain_Model_Extension $extension, array $form)
    {
        if (!$form['gplCompliant']) {
            $this->forwardWithError($this->translate('msg.acceptGPL'), 'uploadVersion');
        }

        if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ter')) {
            $this->forwardWithError($this->translate('msg.createVersionTerNotLoaded'), 'uploadVersion');
        }
        if (empty($this->frontendUser['username'])) {
            $this->forwardWithError($this->translate('msg.createVersionNotLoggedIn'), 'uploadVersion');
        }
        if (empty($form['comment'])) {
            $this->forwardWithError($this->translate('msg.createVersionCommentEmpty'), 'uploadVersion');
        }
        $fileInfo = Tx_TerFe2_Utility_File::getFileInfo('tx_terfe2_pi1.form.file');
        if (empty($fileInfo) || empty($fileInfo['tmp_name']) || $fileInfo['error'] != UPLOAD_ERR_OK) {
            $this->forwardWithError($this->translate('msg.createVersionFileEmpty'), 'uploadVersion');
        }
        if (empty($fileInfo['name']) || substr($fileInfo['name'], -3) !== 'zip') {
            $this->forwardWithError($this->translate('msg.createVersionFileNoZip'), 'uploadVersion');
        }
        $files = array();
        try {
            $extensionInfo = Tx_TerFe2_Utility_Archive::getExtensionDetailsFromZipArchive($fileInfo['tmp_name'], $files);
        } catch (Exception $exception) {
            $this->forwardWithError($exception->getMessage(), 'uploadVersion');
        }
        unlink($fileInfo['tmp_name']);
        if (empty($extensionInfo->version)) {
            $this->forwardWithError($this->translate('msg.createVersionVersionEmpty'), 'uploadVersion');
        }
        $extensionKey = preg_replace('/_(\d+)(\.|\-)(\d+)(\.|\-)(\d+).*/i', '', strtolower($fileInfo['name']));
        if ($extensionKey !== $extension->getExtKey()) {
            $this->forwardWithError($this->translate('msg.createVersionFilenameNotValid'), 'uploadVersion');
        }
        if (!$this->userIsAllowedToUploadExtension($extensionKey)) {
            $this->forwardWithError($this->translate('msg.createVersionUploadNotAllowed'), 'uploadVersion');
        }
        if (!$this->versionIsPossibleForExtension($extensionKey, $extensionInfo->version)) {
            $this->forwardWithError($this->translate('msg.createVersionVersionExists'), 'uploadVersion');
        }
        $extensionInfo->extensionKey = $extensionKey;
        $extensionInfo->infoData->uploadComment = $form['comment'];
        $filesData = (object)array('fileData' => $files);
        try {
            $result = tx_ter_api::uploadExtensionWithoutSoap($this->frontendUser['username'], $extensionInfo, $filesData);
            if ($result) {
                $this->redirect('index', 'Registerkey', NULL, array('uploaded' => TRUE), $this->settings['pages']['manageKeysPID']);
            }
        } catch (Exception $exception) {
            $this->forwardWithError($exception->getMessage(), 'uploadVersion');
        }
        $this->forwardWithError($this->translate('msg.createVersionUploadFailed'), 'uploadVersion');
    }


    /**
     * Returns all / filtered extensions
     *
     * @param array $options Options for extension list
     * @param array $restoreSearch Restore last search from session
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage Objects
     */
    protected function getExtensions(array &$options, $restoreSearch = FALSE)
    {
        // Get last search
        $session = $this->objectManager->get('Tx_TerFe2_Persistence_Session');
        $lastSearch = $session->get('lastSearch');

        // Revert last search if set
        if (!empty($restoreSearch) && !empty($lastSearch)) {
            $options = $lastSearch;
        }

        // Direction
        $desc = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;
        $asc = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING;
        $direction = $desc;
        if (!empty($options['direction'])) {
            $direction = ($options['direction'] === 'asc' ? $asc : $desc);
        }

        // Sorting
        $sortings = array(
            'updated' => 'lastVersion.uploadDate',
            'downloads' => 'downloads',
            'title' => 'lastVersion.title',
        );
        $sorting = $sortings['updated'];
        if (!empty($options['sorting'])) {
            // Set direction to ASC when sorting by title
            if (!empty($sortings[$options['sorting']])) {
                $sorting = $sortings[$options['sorting']];
                if (empty($options['direction'])) {
                    $direction = $desc;
                    if ($options['sorting'] === 'title') {
                        $direction = $asc;
                        $options['direction'] = 'asc';
                    }
                }
            }
            // Sort by downloads when searching
            if (!empty($options['needle']) && (empty($lastSearch['needle']) || $lastSearch['needle'] !== $options['needle'])) {
                $sorting = $sortings['downloads'];
                $options['sorting'] = 'downloads';
                $direction = $desc;
            }
        }

        // Set new search params
        $searchParams = array();
        if (!empty($options['needle'])) {
            $searchParams = array(
                'needle' => $options['needle'],
                'sorting' => $sorting,
                'direction' => $direction,
            );
        }
        $session->set('lastSearch', $searchParams);

        // Ordering
        $ordering = array($sorting => $direction);

        // Return sorted list of all extensions
        if (empty($options['needle'])) {
            return $this->extensionRepository->findAll(0, 0, $ordering);
        }

        // Return search result
        return $this->extensionRepository->findBySearchWordsAndFilters($options['needle'], array(), $ordering);
    }


    /**
     * gets the number of extensions in TER (for ajax call)
     *
     * @return int $number of extensions
     */
    public function getExtensionNumberAction()
    {
        $number = $this->extensionRepository->findAll()->count();
        return (int)$number;
    }


    /**
     * Check if current frontend user can upload given extension
     *
     * There is no better (and faster) way to do this at the momement.
     *
     * @param string $extensionKey The extension key
     * @return boolean TRUE if upload is allowed
     */
    protected function userIsAllowedToUploadExtension($extensionKey)
    {
        if (empty($this->frontendUser['username'])) {
            return FALSE;
        }
        $isAllowedToUploadKey = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
            'uid',
            'tx_ter_extensionkeys',
            'ownerusername LIKE "' . $GLOBALS['TYPO3_DB']->quoteStr($this->frontendUser['username'], 'foo') . '"
				AND extensionkey LIKE "' . $GLOBALS['TYPO3_DB']->quoteStr($extensionKey, 'foo') . '"'
        );
        return !empty($isAllowedToUploadKey);
    }


    /**
     * Check if an version does not exist for extension
     *
     * There is no better (and faster) way to do this at the momement.
     *
     * @param string $extensionKey The extension key
     * @param string $versionString The extension version
     * @return boolean TRUE if version already exists
     */
    protected function versionIsPossibleForExtension($extensionKey, $versionString)
    {
        if (empty($extensionKey) || empty($versionString)) {
            return FALSE;
        }
        $versionExistsForExtension = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows(
            'uid',
            'tx_ter_extensions',
            'extensionkey = "' . $GLOBALS['TYPO3_DB']->quoteStr($extensionKey, 'foo') . '"
				AND version LIKE "' . $GLOBALS['TYPO3_DB']->quoteStr($versionString, 'foo') . '"'
        );
        return empty($versionExistsForExtension);
    }

    /**
     * sets SYS_LASTCHANGED to this date if it is newer than the currently set
     * @param integer $dateTime
     */
    protected function updateSysLastChanged($dateTime)
    {
        if ($dateTime instanceof \DateTime) {
            $dateTime = $dateTime->getTimestamp();
        }
        if ($GLOBALS['TSFE']->register['SYS_LASTCHANGED'] < $dateTime) {
            $GLOBALS['TSFE']->register['SYS_LASTCHANGED'] = $dateTime;
        }
    }
}

?>
