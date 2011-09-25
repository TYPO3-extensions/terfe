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
	class Tx_TerFe2_Cache_FileCache {

		/**
		 * @var string
		 */
		protected static $cacheDirectory;


		/**
		 * Set cache directory path
		 *
		 * @return void
		 */
		public static function loadCacheDirectory() {
			$cacheDirectory = '';
			// TODO: Load cache directory from settings
			if (empty($cacheDirectory)) {
				throw new Exception('An empty cache directory is not allowed');
			}
			self::$cacheDirectory = Tx_TerFe2_Utility_File::getAbsoluteDirectory($cacheDirectory);
		}


		/**
		 * Get cache directory path
		 *
		 * @return string Path of the cache directory
		 */
		public static function getCacheDirectory() {
			return self::$cacheDirectory;
		}


		/**
		 * Get filename
		 *
		 * @param string $filename Name of the file
		 * @return string Local filename
		 */
		public static function getFile($filename) {
			if (empty($filename)) {
				return '';
			}
			if (empty(self::$cacheDirectory)) {
				self::loadCacheDirectory();
			}
			$filename = self::$cacheDirectory . $filename;
			if (Tx_TerFe2_Utility_File::fileExists($filename)) {
				return $filename;
			}
			return '';
		}


		/**
		 * Get url to file
		 *
		 * @param string $filename Name of the file
		 * @return string Url to local file
		 */
		public static function getUrl($filename) {
			$filename = self::getFile($filename);
			if (!empty($filename)) {
				return Tx_TerFe2_Utility_File::getUrlFromAbsolutePath($filename);
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
		public static function getExtensionFile($extensionKey, $filename) {
			$filename = self::getExtensionFilename($extensionKey, $filename);
			return self::getFile($filename);
		}


		/**
		 * Copy a file to local cache
		 *
		 * @param string $fileUrl Url to the file
		 * @param string $filename Name of the file
		 * @return string Local filename
		 */
		public static function addFile($fileUrl, $filename) {
			if (empty($fileUrl) || empty($filename)) {
				return '';
			}
			if (empty(self::$cacheDirectory)) {
				self::loadCacheDirectory();
			}
			if (!Tx_TerFe2_Utility_File::fileExists($fileUrl)) {
				return '';
			}
			$filename = self::$cacheDirectory . $filename;
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
		public static function addExtensionFile($extensionKey, $fileUrl, $filename) {
			$filename = self::getExtensionFilename($extensionKey, $filename);
			return self::addFile($fileUrl, $filename);
		}


		/**
		 * Remove a file from local cache
		 *
		 * @param string $filename Name of the file
		 * @return boolean TRUE if success
		 */
		public static function removeFile($filename) {
			if (empty($filename)) {
				return FALSE;
			}
			if (empty(self::$cacheDirectory)) {
				self::loadCacheDirectory();
			}
			$filename = self::$cacheDirectory . $filename;
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
		public static function addExtensionFile($extensionKey, $filename) {
			$filename = self::getExtensionFilename($extensionKey, $filename);
			return self::removeFile($filename);
		}


		/**
		 * Returns the filename of an extension file
		 *
		 * @param string $extensionKey Extension key
		 * @param string $filename Name of the file
		 * @return string Extension filename
		 */
		protected static function getExtensionFilename($extensionKey, $filename) {
			if (empty($extensionKey) || empty($filename)) {
				return '';
			}
			return strtolower($extensionKey) . '/' . basename($filename);
		}

	}
?>