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
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Utility_Files {

		/**
		 * Check if a file, URL or directory exists
		 *
		 * @param string $fileName Path to the file
		 * @return boolean TRUE if file exists
		 */
		static public function fileExists($fileName) {
			if (empty($fileName)) {
				return FALSE;
			}

			if (is_dir($fileName)) {
				return (bool) file_exists($fileName);
			}

			$result = @fopen($fileName, 'r');
			return ($result !== FALSE);
		}


		/**
		 * Generates a file name via extension key, version and type
		 *
		 * @param string $extensionKey Extension Key
		 * @param string $version Version of the extension
		 * @param string $fileType File type of the returning path
		 * @return string Path and file name
		 */
		static public function generateFileName($extensionKey, $version, $fileType = 't3x') {
			if (empty($extensionKey) || empty($version) || empty($fileType)) {
				return '';
			}

			$extensionKey = strtolower($extensionKey);
			$version      = Tx_Extbase_Utility_Arrays::integerExplode('.', $version);
			$fileName     = '%s_%d.%d.%d.' . strtolower(trim($fileType, '. '));

			return sprintf($fileName, $extensionKey, $version[0], $version[1], $version[2]);
		}


		/**
		 * Returns absolute path to given directory
		 *
		 * @return string Absolute path
		 */
		static public function getAbsoluteDirectory($path) {
			if (empty($path)) {
				return PATH_site;
			}

			if (!self::fileExists(PATH_site . $path)) {
				t3lib_div::mkdir_deep(PATH_site . $path);
			}

			return PATH_site . $path;
		}


		/**
		 * Unpack an extension from T3X file
		 *
		 * @param string $fileName Path to T3X file
		 * @return array Unpacked extension files
		 */
		static public function unpackT3xFile($fileName) {
			if (empty($fileName)) {
				return array();
			}

			// Get local file name if on same server
			if (self::isLocalUrl($fileName)) {
				$fileName = self::getLocalUrlPath($fileName);
			}

			// Get file content
			$contents = t3lib_div::getURL($fileName);
			if (empty($contents)) {
				return array();
			}

			// Get content parts
			list($hash, $compression, $data) = explode(':', $contents, 3);
			unset($contents);

			// Get extension files
			$files = gzuncompress($data);
			if (empty($files) || $hash != md5($files)) {
				return array();
			}

			// Unserialize files array
			return unserialize($files);
		}


		/**
		 * Returns the MD5 hash of a file
		 *
		 * @param string $fileName Path to the file
		 * @return string Generated hash or an empty string if file not found
		 */
		static public function getFileHash($fileName) {
			// Get md5 from local file
			if (self::isLocalUrl($fileName)) {
				$fileName = self::getLocalUrlPath($fileName);
				return md5_file($fileName);
			}

			// Get md5 from external file
			$contents = t3lib_div::getURL($fileName);
			if (!empty($contents)) {
				return md5($contents);
			}

			return '';
		}


		/**
		 * Get last modification time of a file or directory
		 *
		 * @param string $fileName Path to the file
		 * @return integer Timestamp of the modification time
		 */
		static public function getModificationTime($fileName) {
			// clearstatcache();
			return (int) @filemtime($fileName);
		}


		/**
		 * Transfers a file to client browser
		 *
		 * This function must be called before any HTTP headers have been sent
		 *
		 * @param string $fileName Path to the file
		 * @param string $visibleFileName Override real file name with this one for download
		 * @return boolean FALSE if file not exists
		 */
		static public function transferFile($fileName, $visibleFileName = '') {
			if (self::isLocalUrl($fileName)) {
				$fileName = self::getLocalUrlPath($fileName);
			}

			// Check if file exists
			if (!self::fileExists($fileName)) {
				return FALSE;
			}

			// Get file name for download
			if (empty($visibleFileName)) {
				$visibleFileName = basename($fileName);
			}

			// Set headers
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Sat, 10 Jan 1970 00:00:00 GMT');
			header('Content-Disposition: attachment; filename=' . (string) $visibleFileName);
			header('Content-type: x-application/octet-stream');
			header('Content-Transfer-Encoding: binary');

			// Send file contents
			readfile($fileName);
			ob_flush();
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
		static public function getFiles($directory, $fileType = '', $timestamp = 0, $recursive = FALSE) {
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
					$fileName = $file->getPathname();

					// Check file type
					if ($fileType) {
						if (substr($fileName, strrpos($fileName, '.') + 1) != $fileType) {
							continue;
						}
					}

					// Check timestamp
					if ($timestamp) {
						$modificationTime = self::getModificationTime($fileName);
						if ($modificationTime < $timestamp) {
							continue;
						}
					}

					$result[] = $fileName;
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
		static public function copyFile($fromFileName, $toFileName, $overwrite = FALSE) {
			if (self::isLocalUrl($fromFileName)) {
				$fromFileName = self::getLocalUrlPath($fromFileName);
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
		 * Check if a URL is located to current server
		 *
		 * @param string $urlToFile URL of the file
		 * @return boolean TRUE if given file is local
		 */
		static public function isLocalUrl($urlToFile) {
			return t3lib_div::isOnCurrentHost($urlToFile);
		}


		/**
		 * Returns local file name from URL if located to current server
		 *
		 * Required to get absolute path on filesystem if php has no rights
		 * to fetch a file via URL from current server (TYPO3_REQUEST_HOST).
		 *
		 * @param string $urlToFile URL of the file
		 * @return string Absolute path to file
		 */
		static public function getLocalUrlPath($urlToFile) {
			$hostUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/';
			return PATH_site . str_ireplace($hostUrl, '', $urlToFile);
		}


		/**
		 * Compiles the ext_emconf.php file
		 *
		 * @param string $extKey Extension key
		 * @param array $emConfArray Content of the file
		 * @return string PHP file content, ready to write to ext_emconf.php file
		 * @see tx_em_Extensions_Details::construct_ext_emconf_file()
		 */
		static public function createExtEmconfFile($extKey, array $emConfArray) {
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