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
 * Cache for extension files
 */
class Tx_TerFe2_Cache_FileCache implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var string
     */
    protected $cacheDirectory;


    /**
     * Set cache directory path
     *
     * @param string $cacheDirectory Path to cache directory
     * @return void
     */
    public function setCacheDirectory($cacheDirectory)
    {
        if (empty($cacheDirectory)) {
            throw new Exception('An empty cache directory is not allowed');
        }
        $this->cacheDirectory = Tx_TerFe2_Utility_File::getAbsoluteDirectory($cacheDirectory);
    }


    /**
     * Get cache directory path
     *
     * @return string Path of the cache directory
     */
    public function getCacheDirectory()
    {
        return $this->cacheDirectory;
    }


    /**
     * Get filename
     *
     * @param string $filename Name of the file
     * @return string Local filename
     */
    public function getFile($filename)
    {
        $filename = $this->getFilePath($filename);
        if (Tx_TerFe2_Utility_File::fileExists($filename)) {
            return $filename;
        }
        return '';
    }


    /**
     * Returns an extension related filename
     *
     * @param string $extensionKey Extension key
     * @param string $filename Name of the file
     * @return string Local filename
     */
    public function getExtensionFile($extensionKey, $filename)
    {
        $filename = $this->getExtensionFilename($extensionKey, $filename);
        return $this->getFile($filename);
    }


    /**
     * Copy a file to local cache
     *
     * @param string $fileUrl Url to the file
     * @param string $filename Name of the file
     * @return string Local filename
     */
    public function addFile($fileUrl, $filename)
    {
        if (empty($fileUrl)) {
            return '';
        }
        $filename = $this->getFilePath($filename);
        if (Tx_TerFe2_Utility_File::copyFile($fileUrl, $filename)) {
            return $filename;
        }
        return '';
    }


    /**
     * Copy an extension file to local cache
     *
     * @param string $extensionKey Extension key
     * @param string $fileUrl Url to the file
     * @param string $filename Name of the file
     * @return string Local filename
     */
    public function addExtensionFile($extensionKey, $fileUrl, $filename)
    {
        $filename = $this->getExtensionFilename($extensionKey, $filename);
        return $this->addFile($fileUrl, $filename);
    }


    /**
     * Remove a file from local cache
     *
     * @param string $filename Name of the file
     * @return boolean TRUE if success
     */
    public function removeFile($filename)
    {
        $filename = $this->getFilePath($filename);
        if (!Tx_TerFe2_Utility_File::fileExists($fileUrl)) {
            return FALSE;
        }
        return unlink($filename);
    }


    /**
     * Remove an extension file from local cache
     *
     * @param string $extensionKey Extension key
     * @param string $filename Name of the file
     * @return string Local filename
     */
    public function removeExtensionFile($extensionKey, $filename)
    {
        $filename = $this->getExtensionFilename($extensionKey, $filename);
        return $this->removeFile($filename);
    }


    /**
     * Returns the filename of a file
     *
     * @param string $filename Name of the file
     * @return string Absolute path to file
     */
    protected function getFilePath($filename)
    {
        if (empty($filename)) {
            return '';
        }
        $cacheDirectory = $this->getCacheDirectory();
        if (empty($cacheDirectory)) {
            throw new Exception('No cache directory defined');
        }
        return $cacheDirectory . $filename;
    }


    /**
     * Returns the filename of an extension file
     *
     * @param string $extensionKey Extension key
     * @param string $filename Name of the file
     * @return string Extension filename
     */
    protected function getExtensionFilename($extensionKey, $filename)
    {
        if (empty($extensionKey) || empty($filename)) {
            return '';
        }
        return strtolower($extensionKey) . '/' . basename($filename);
    }

}

?>