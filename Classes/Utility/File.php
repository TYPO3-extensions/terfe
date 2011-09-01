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
	 * Utilities to manage files
	 */
	class Tx_TerFe2_Utility_File {

		/**
		 * Check if a file, URL or directory exists
		 *
		 * @param string $filename Path to the file
		 * @return boolean TRUE if file exists
		 */
		public static function fileExists($filename) {
			if (empty($filename)) {
				return FALSE;
			}

			if (is_dir($filename)) {
				return (bool) file_exists($filename);
			}

			$result = @fopen($filename, 'r');
			return ($result !== FALSE);
		}


		/**
		 * Returns absolute path to given directory
		 *
		 * @return string Absolute path
		 */
		public static function getAbsoluteDirectory($path) {
			if (empty($path)) {
				return PATH_site;
			}

			if (!self::fileExists(PATH_site . $path)) {
				t3lib_div::mkdir_deep(PATH_site . $path);
			}

			return PATH_site . rtrim($path, '/') . '/';
		}


		/**
		 * Returns the MD5 hash of a file
		 *
		 * @param string $filename Path to the file
		 * @return string Generated hash or an empty string if file not found
		 */
		public static function getFileHash($filename) {
				// Get md5 from local file
			if (self::isLocalUrl($filename)) {
				$filename = self::getAbsolutePathFromUrl($filename);
				return md5_file($filename);
			}

				// Get md5 from external file
			$contents = t3lib_div::getURL($filename);
			if (!empty($contents)) {
				return md5($contents);
			}

			return '';
		}


		/**
		 * Get last modification time of a file or directory
		 *
		 * @param string $filename Path to the file
		 * @return integer Timestamp of the modification time
		 */
		public static function getModificationTime($filename) {
			// clearstatcache();
			return (int) @filemtime($filename);
		}


		/**
		 * Transfers a file to client browser
		 *
		 * This function must be called before any HTTP headers have been sent
		 *
		 * @param string $filename Path to the file
		 * @param string $visibleFileName Override real file name with this one for download
		 * @return boolean FALSE if file not exists
		 */
		public static function transferFile($filename, $visibleFileName = '') {
			if (self::isLocalUrl($filename)) {
				$filename = self::getAbsolutePathFromUrl($filename);
			}

				// Check if file exists
			if (!self::fileExists($filename)) {
				return FALSE;
			}

				// Get file name for download
			if (empty($visibleFileName)) {
				$visibleFileName = basename($filename);
			}

				// Set headers
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Sat, 10 Jan 1970 00:00:00 GMT');
			header('Content-Disposition: attachment; filename=' . (string) $visibleFileName);
			header('Content-type: x-application/octet-stream');
			header('Content-Transfer-Encoding: binary');

				// Send file contents
			readfile($filename);
			exit;
		}


		/**
		 * Get a list of all files in a directory
		 *
		 * @param string $directory Path to the directory
		 * @param string $fileType Type of the files to find
		 * @param integer $timestamp Timestamp of the last file change
		 * @param boolean $recursive Get subfolder content too
		 * @return array All contained files
		 */
		public static function getFiles($directory, $fileType = '', $timestamp = 0, $recursive = FALSE) {
			$directory = t3lib_div::getFileAbsFileName($directory);
			if (!self::fileExists($directory)) {
				return array();
			}

			$fileType  = ltrim($fileType, '.');
			$timestamp = (int) $timestamp;
			$result    = array();

			if ($recursive) {
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
			} else {
				$files = new DirectoryIterator($directory);
			}

			foreach ($files as $file) {
				if ($file->isFile()) {
					$filename = $file->getPathname();

						// Check file type
					if ($fileType) {
						if (substr($filename, strrpos($filename, '.') + 1) != $fileType) {
							continue;
						}
					}

						// Check timestamp
					if ($timestamp) {
						$modificationTime = self::getModificationTime($filename);
						if ($modificationTime < $timestamp) {
							continue;
						}
					}

					$result[] = $filename;
				}
			}

			return $result;
		}


		/**
		 * Copy a file
		 *
		 * @param string $fromFileName Existing file
		 * @param string $toFileName File name of the new file
		 * @param boolean $overwrite Existing A file with new name will be overwritten if set
		 * @return boolean TRUE if success
		 */
		public static function copyFile($fromFileName, $toFileName, $overwrite = FALSE) {
			if (self::isLocalUrl($fromFileName)) {
				$fromFileName = self::getAbsolutePathFromUrl($fromFileName);
			}

			$fromFile = t3lib_div::getURL($fromFileName);

				// Check files
			if ($fromFile === FALSE || ($toFileExists && !$overwrite)) {
				return FALSE;
			}

				// Remove existing
			if (self::fileExists($toFileName) && $overwrite) {
				unlink($toFileName);
			}

				// Copy file to new name
			$result = t3lib_div::writeFile($toFileName, $fromFile);
			return ($result !== FALSE);
		}


		/**
		 * Move a file or folder
		 *
		 * @param string $fromFileName Existing file
		 * @param string $toFileName File name of the new file
		 * @param boolean $overwrite Existing A file with new name will be overwritten if set
		 * @return boolean TRUE if success
		 */
		public static function moveFile($fromFileName, $toFileName, $overwrite = FALSE) {
			$result = self::copyFile($fromFileName, $toFileName, $overwrite);
			if ($result && self::isAbsolutePath($fromFileName)) {
				unlink($fromFileName);
			}
			return $result;
		}


		/**
		 * Check if a URL is located to current server
		 *
		 * @param string $url UUrl to file
		 * @return boolean TRUE if given file is local
		 */
		public static function isLocalUrl($url) {
			return t3lib_div::isOnCurrentHost($url);
		}


		/**
		 * Check if a filename is an absolute path in local file system
		 *
		 * @param string $path Path to file
		 * @return boolean TRUE if given path is absolute
		 */
		public static function isAbsolutePath($path) {
			return (strpos(PATH_site, $path) === 0);
		}


		/**
		 * Returns absolute path on local file system from an url
		 *
		 * @param string $url Url to file
		 * @return string Absolute path to file
		 */
		public static function getAbsolutePathFromUrl($url) {
			$hostUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/';
			return PATH_site . str_ireplace($hostUrl, '', $url);
		}


		/**
		 * Returns url from an absolute path on local file system
		 *
		 * @param string $path Absolute path to file
		 * @return string Url to file
		 */
		public static function getUrlFromAbsolutePath($path) {
			$hostUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/';
			return $hostUrl . str_ireplace(PATH_site, '', $path);
		}


		/**
		 * Compiles the ext_emconf.php file
		 *
		 * @param string $extKey Extension key
		 * @param array $emConfArray Content of the file
		 * @return string PHP file content, ready to write to ext_emconf.php file
		 * @see tx_em_Extensions_Details::construct_ext_emconf_file()
		 */
		public static function createExtEmconfFile($extKey, array $emConfArray) {
			if (!t3lib_extMgm::isLoaded('em')) {
				throw new Exception('System extension "em" is required to generate ext_emconf.php');
			}

			$content = '<?php

########################################################################
# Extension Manager/Repository config file for ext "' . $extKey . '".
#
# Auto generated ' . date('d-m-Y H:i') . '
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = ' . tx_em_Tools::arrayToCode($emConfArray, 0) . ';

?>';

			return str_replace(CR, '', $content);
		}

	}
?>