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
 * Utilities to manage and convert Typoscript Code
 */
class Tx_TerFe2_Utility_TypoScript
{

    /**
     * @var object
     */
    protected static $frontend;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected static $contentObject;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     */
    protected static $configurationManager;


    /**
     * Initialize configuration manager and content object
     *
     * @return void
     */
    protected static function initialize()
    {
        // Get configuration manager
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        self::$configurationManager = $objectManager->get(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);

        // Simulate Frontend
        if (TYPO3_MODE != 'FE') {
            self::simulateFrontend();
            self::$configurationManager->setContentObject($GLOBALS['TSFE']->cObj);
        }

        // Get content object
        self::$contentObject = self::$configurationManager->getContentObject();
        if (empty(self::$contentObject)) {
            self::$contentObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        }
    }


    /**
     * Simulate a frontend environment
     *
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj Instance of an content object
     * @return void
     */
    public static function simulateFrontend(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj = NULL)
    {
        // Make backup of current frontend
        self::$frontend = (!empty($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : NULL);

        // Create new frontend instance
        $GLOBALS['TSFE'] = new stdClass();
        $GLOBALS['TSFE']->cObjectDepthCounter = 100;
        $GLOBALS['TSFE']->cObj = (!empty($cObj) ? $cObj : \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class));

        if (empty($GLOBALS['TSFE']->sys_page)) {
            $GLOBALS['TSFE']->sys_page = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
        }

        if (empty($GLOBALS['TSFE']->tmpl)) {
            $GLOBALS['TSFE']->tmpl = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\TemplateService::class);
            $GLOBALS['TSFE']->tmpl->getFileName_backPath = PATH_site;
            $GLOBALS['TSFE']->tmpl->init();
        }

        if (empty($GLOBALS['TT'])) {
            $GLOBALS['TT'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\TimeTracker\NullTimeTracker::class);
        }

        if (empty($GLOBALS['TSFE']->config)) {
            $GLOBALS['TSFE']->config = \TYPO3\CMS\Core\Utility\GeneralUtility::removeDotsFromTS(self::getSetup());
        }
    }


    /**
     * Reset an existing frontend environment
     *
     * @param object $frontend Instance of a frontend environemnt
     * @return void
     */
    public static function resetFrontend($frontend = NULL)
    {
        $frontend = (!empty($frontend) ? $frontend : self::$frontend);
        if (!empty($frontend)) {
            $GLOBALS['TSFE'] = $frontend;
        }
    }


    /**
     * Returns unparsed TypoScript setup
     *
     * @param string $typoScriptPath TypoScript path
     * @return array TypoScript setup
     */
    public static function getSetup($typoScriptPath = '')
    {
        if (empty(self::$configurationManager)) {
            self::initialize();
        }

        $setup = self::$configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        if (empty($typoScriptPath)) {
            return $setup;
        }

        $path = explode('.', $typoScriptPath);
        foreach ($path as $segment) {
            if (empty($setup[$segment . '.'])) {
                return array();
            }
            $setup = $setup[$segment . '.'];
        }

        return $setup;
    }


    /**
     * Parse given TypoScript configuration
     *
     * @param array $configuration TypoScript configuration
     * @param boolean $isPlain Is a plain "Fluid like" configuration array
     * @return array Parsed configuration
     */
    public static function parse(array $configuration, $isPlain = TRUE)
    {
        if (empty(self::$contentObject)) {
            self::initialize();
        }

        // Convert to classic TypoScript array
        if ($isPlain) {
            /** @var \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService */
            $typoScriptService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\TypoScriptService::class);
            $configuration = $typoScriptService->convertPlainArrayToTypoScriptArray($configuration);
        }

        // Parse configuration
        return self::parseTypoScriptArray($configuration);
    }


    /**
     * Parse TypoScript array
     *
     * @param array $configuration TypoScript configuration array
     * @return array Parsed configuration
     * @api
     */
    public static function parseTypoScriptArray(array $configuration)
    {
        $typoScriptArray = array();

        if (is_array($configuration)) {
            foreach ($configuration as $key => $value) {
                $ident = rtrim($key, '.');
                if (is_array($value)) {
                    if (!empty($configuration[$ident])) {
                        $typoScriptArray[$ident] = self::$contentObject->cObjGetSingle($configuration[$ident], $value);
                    } else {
                        $typoScriptArray[$ident] = self::parseTypoScriptArray($value);
                    }
                    unset($configuration[$key]);
                } else if (is_string($value) && $key === $ident) {
                    $typoScriptArray[$key] = $value;
                }
            }
        }

        return $typoScriptArray;
    }

}

?>