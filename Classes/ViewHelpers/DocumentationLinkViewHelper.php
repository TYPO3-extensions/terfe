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
 * Documentation link view helper
 */
class Tx_TerFe2_ViewHelpers_DocumentationLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Disable the escaping interceptor
     */
    protected $escapingInterceptorEnabled = FALSE;

    /**
     * @var Tx_TerFe2_Service_Documentation
     */
    protected $documentationService;

    /**
     * Initialize the arguments.
     *
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
    }

    /**
     * Inject the documentation service
     *
     * @param Tx_TerFe2_Service_Documentation $documentationService Service for extension manuals
     * @return void
     */
    public function injectDocumentationService(Tx_TerFe2_Service_Documentation $documentationService)
    {
        $this->documentationService = $documentationService;
    }


    /**
     * Renders the documentation link for a version object
     *
     * @param Tx_TerFe2_Domain_Model_Version $version Version object
     * @param string $format Output format
     * @return string Rendered image tag
     */
    public function render(Tx_TerFe2_Domain_Model_Version $version = NULL, $format = '')
    {
        if ($version === NULL) {
            $version = $this->renderChildren();
        }

        $extensionKey = $version->getExtension()->getExtKey();
        $versionString = $version->getVersionString();

        return $this->documentationService->getDocumentationUrl($extensionKey, $versionString, $format);
    }

}

?>