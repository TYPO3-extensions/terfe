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
 * Abstract extension provider
 */
abstract class Tx_TerFe2_Provider_AbstractProvider implements Tx_TerFe2_Provider_ProviderInterface
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory
     */
    protected $dataMapFactory;

    /**
     * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
     */
    protected $reflectionService;

    /**
     * @var array Configuration array
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $imageCachePath = 'typo3temp/tx_terfe2/images/';


    /**
     * Get or create absolute path to image cache directory
     *
     * @return void
     */
    public function __construct()
    {
        $this->imageCachePath = Tx_TerFe2_Utility_File::getAbsoluteDirectory($this->imageCachePath);
    }


    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }


    /**
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory $dataMapFactory
     * @return void
     */
    public function injectDataMapFactory(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory $dataMapFactory)
    {
        $this->dataMapFactory = $dataMapFactory;
    }


    /**
     * @param \TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService
     * @return void
     */
    public function injectReflectionService(\TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }


    /**
     * Set configuration for the DataProvider
     *
     * @param array $configuration TypoScript configuration
     * @return void
     */
    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }


    /**
     * Returns the url to an extension related icon
     *
     * @param Tx_TerFe2_Domain_Model_Version $version Version object
     * @param string $fileType File type
     * @return string Url to icon file
     */
    public function getIconUrl(Tx_TerFe2_Domain_Model_Version $version, $fileType)
    {
        $filename = $this->getFileName($version, $fileType);
        $localName = $this->imageCachePath . basename($filename);

        // Check local cache first
        if (Tx_TerFe2_Utility_File::fileExists($localName)) {
            return Tx_TerFe2_Utility_File::getUrlFromAbsolutePath($localName);
        }

        // Get icon from concrete extension provider
        $iconUrl = $this->getFileUrl($version, $fileType);

        // Copy icon to local cache
        if (!empty($iconUrl)) {
            Tx_TerFe2_Utility_File::copyFile($iconUrl, $localName);
        }

        return Tx_TerFe2_Utility_File::getUrlFromAbsolutePath($localName);
    }


    /**
     * Returns an array with minimum and maximum version number from range
     *
     * @param string $version Range of versions
     * @return array Minumum and maximum version number
     */
    protected function getVersionByRange($version)
    {
        $version = \TYPO3\CMS\Extbase\Utility\ArrayUtility::trimExplode('-', $version);
        $minimum = (!empty($version[0]) ? \TYPO3\CMS\Core\Utility\GeneralUtility::int_from_ver($version[0]) : 0);
        $maximum = (!empty($version[1]) ? \TYPO3\CMS\Core\Utility\GeneralUtility::int_from_ver($version[1]) : 0);

        return array($minimum, $maximum);
    }

}

?>