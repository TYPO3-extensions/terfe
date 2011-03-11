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
	class Tx_TerFe2_Utility_Files implements t3lib_Singleton {

		/**
		 * Check if a file or directory exists
		 *
		 * @param string $filename Path to the file
		 * @return boolean TRUE if file exists
		 */
		static public function fileExists($filename) {
			if (empty($filename)) {
				return FALSE;
			}

			if (is_dir($filename)) {
				return (bool) file_exists($filename);
			}

			return (bool) is_readable($filename);
		}


		/**
		 * Returns relative path to a file via extension key and version
		 *
		 * @param string $extKey Extension Key
		 * @param string $version Version of the extension
		 * @param string $fileType File type of the returning path
		 * @return string Path and filename
		 */
		static public function getT3xRelPath($extKey, $version, $fileType = 't3x') {
			if (empty($extKey) || empty($version)) {
				return '';
			}

			$extKey  = strtolower($extKey);
			$version = Tx_Extbase_Utility_Arrays::integerExplode('.', $version);
			$path    = '%s/%s/%s_%d.%d.%d.' . strtolower(trim($fileType, '. '));

			return sprintf($path, $extKey[0], $extKey[1], $extKey, $version[0], $version[1], $version[2]);
		}


		/**
		 * Returns relative path to cached extension icon
		 *
		 * @param string $extKey Extension Key
		 * @param string $version Version of the extension
		 * @param string $fileType File type of the returning path
		 * @return string Path and filename
		 */
		static public function getIconRelCachePath($extKey, $version, $fileType = 'gif') {
			if (empty($extKey) || empty($version)) {
				return '';
			}

			// Get temporary directory
			$tmpPath = 'typo3temp/pics/';
			if (!self::fileExists(PATH_site . $tmpPath)) {
				t3lib_div::mkdir_deep(PATH_site . $tmpPath);
			}

			$extKey  = strtolower($extKey);
			$version = Tx_Extbase_Utility_Arrays::integerExplode('.', $version);
			$path    = $tmpPath . '%s_%d.%d.%d.' . strtolower(trim($fileType, '. '));

			return sprintf($path, $extKey, $version[0], $version[1], $version[2]);
		}


		/**
		 * Unpack an extension from T3X file
		 *
		 * @param string $filename Path to T3X file
		 * @return array Unpacked extension files
		 */
		static public function unpackT3xFile($filename) {
			if (empty($filename)) {
				return array();
			}

			// Get file content
			$contents = @file_get_contents($filename);
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
		 * @param string $filename Path to the file
		 * @return string Generated hash or an empty string if file not found
		 */
		static public function getFileHash($filename) {
			$result = @md5_file($filename);
			if ($result !== FALSE) {
				return $result;
			}

			return '';
		}


		/**
		 * Get last modification time of a file or directory
		 *
		 * @param string $filename Path to the file
		 * @reutrn integer Timestamp of the modification time
		 */
		static public function getModificationTime($filename) {
			// clearstatcache();
			return (int) @filemtime($filename);
		}


		/**
		 * Transfers a file to the client browser
		 *
		 * This function must be called before any HTTP headers have been sent
		 *
		 * @param string $filename Path to the file
		 * @param string $visibleFilename Override real filename with this one for download
		 * @return boolean FALSE if file not exists
		 */
		static public function transferFile($filename, $visibleFilename = '') {
			// Get filename for download
			if (empty($visibleFilename)) {
				$visibleFilename = basename($filename);
			}

			// Set headers
			header('Content-Disposition: attachment; filename=' . (string) $visibleFilename);
			header('Content-type: x-application/octet-stream');
			header('Content-Transfer-Encoding: binary');

			// Send file contents
			readfile($filename);
			ob_flush();
			exit;
		}


		/**
		 * Get a list of all files in a directory
		 *
		 * @param string $dirname Path to the directory
		 * @param string $fileType Type of the files to find
		 * @param integer $timestamp Timestamp of the last file change
		 * @param boolean $recursive Get subfolder content too
		 * @return array All contained files
		 */
		static public function getFiles($dirname, $fileType = '', $timestamp = 0, $recursive = FALSE) {
			$dirname = t3lib_div::getFileAbsFileName($dirname);
			if (!self::fileExists($dirname)) {
				return array();
			}

			$fileType  = ltrim($fileType, '.');
			$timestamp = (int) $timestamp;
			$result    = array();

			if ($recursive) {
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirname));
			} else {
				$files = new DirectoryIterator($dirname);
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
			$fromFile = file_get_contents($fromFileName);

			// Check files
			if ($fromFile === FALSE || ($toFileExists && !$overwrite)) {
				return FALSE;
			}

			// Remove existing
			if (self::fileExists($toFileName) && $overwrite) {
				unlink($toFileName);
			}

			// Copy file to new name
			$result = file_put_contents($toFileName, $fromFile);
			return ($result !== FALSE);
		}

	}
?>