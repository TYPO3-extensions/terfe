<?php

class Tx_TerFe2_ViewHelpers_UserImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{

    /**
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
     * @param string $size
     * @param string $width
     * @param string $height
     *
     * @throws Tx_Fluid_Core_ViewHelper_Exception
     * @return string image HTML code
     */
    public function render(\TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user, $size = 'big', $width = '', $height = '')
    {
        if (!is_callable(array($user, 'getUserImageHash'))) {
            return '';
        }

        if ($size !== 'big' && $size !== 'mid' && $size !== 'small') {
            return '';
        }

        $userImageHash = $user->getUserImageHash();

        if (empty($userImageHash)) {
            return '';
        }

        if (TYPO3_MODE === 'BE') {
            \TYPO3\CMS\Extbase\Utility\FrontendSimulatorUtility::simulateFrontendEnvironment($this->getContentObject());
        }

        $src = 'uploads/tx_t3ouserimage/' . $userImageHash . '-' . $size . '.jpg';

        $setup = array(
            'width' => $width,
            'height' => $height,
        );
        $imageInfo = $this->getContentObject()->getImgResource($src, $setup);
        $GLOBALS['TSFE']->lastImageInfo = $imageInfo;
        if (!is_array($imageInfo)) {
            return '';
        }
        $imageInfo[3] = \TYPO3\CMS\Core\Imaging\GraphicalFunctions::pngToGifByImagemagick($imageInfo[3]);
        $GLOBALS['TSFE']->imagesOnPage[] = $imageInfo[3];

        $imageSource = $GLOBALS['TSFE']->absRefPrefix . \TYPO3\CMS\Core\Utility\GeneralUtility::rawUrlEncodeFP($imageInfo[3]);
        if (TYPO3_MODE === 'BE') {
            $imageSource = '../' . $imageSource;
            \TYPO3\CMS\Extbase\Utility\FrontendSimulatorUtility::resetFrontendEnvironment();
        }
        $this->tag->addAttribute('src', $imageSource);
        $this->tag->addAttribute('width', $imageInfo[0]);
        $this->tag->addAttribute('height', $imageInfo[1]);
        if ($this->arguments['title'] === '') {
            $this->tag->addAttribute('title', $this->arguments['alt']);
        }

        return $this->tag->render();
    }

    /**
     * @return \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    private function getContentObject()
    {
        /** @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $TSFE */
        $TSFE = $GLOBALS['TSFE'];
        return $TSFE->cObj;
    }

}

?>