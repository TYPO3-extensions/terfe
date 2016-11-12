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
 * Extension image view helper
 */
class Tx_TerFe2_ViewHelpers_ExtensionImageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'img';

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $contentObject;


    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     * @return void
     */
    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager)
    {
        /*$this->settings = Tx_TerFe2_Utility_TypoScript::getSetup('plugin.tx_terfe2.settings');
        $this->settings = Tx_TerFe2_Utility_TypoScript::parse($this->settings);
        $this->contentObject = $configurationManager->getContentObject();*/
    }


    /**
     * Renders an extension icon for given version object
     *
     * @param Tx_TerFe2_Domain_Model_Extension $extension Extension object
     * @param Tx_TerFe2_Domain_Model_Media $media Media object
     * @param string $size Image size to render
     * @param boolean $lightbox Enable lighbox
     * @return string Rendered image tag
     */
    public function render(Tx_TerFe2_Domain_Model_Extension $extension, Tx_TerFe2_Domain_Model_Media $media, $size = 'small', $lightbox = TRUE)
    {
        /*$size = strtolower($size);
        if ($size !== 'small' && $size !== 'large') {
            throw new Exception('Image size "' . $size . '" is not implemented yet');
        }
        if (empty($this->settings[$size . 'Image'])) {
            throw new Exception('Image size "' . $size . '" is not configured');
        }
        if (empty($this->settings['mediaRootPath'])) {
            throw new Exception('Setting "mediaRootPath" is not configured');
        }

        $fileName = $media->getSource();
        if (empty($fileName)) {
            return '';
        }

        $extensionKey = $extension->getExtKey();
        $mediaRootPath = rtrim($this->settings['mediaRootPath'], '/') . '/' . $extensionKey;
        $mediaRootPath = Tx_TerFe2_Utility_File::getAbsoluteDirectory($mediaRootPath);
        $fileName = $mediaRootPath . $fileName;

        $imageSettings = $this->settings[$size . 'Image'];
        $fileName = $this->contentObject->getImgResource($fileName, $imageSettings);

        $this->tag->addAttribute('src', $imageUrl);
        return $this->tag->render();*/

        throw new Exception('Image view helper is not implemented yet');
    }

}

?>