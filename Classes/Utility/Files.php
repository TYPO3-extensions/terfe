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
		 * @return string Path and basename to file
		 */
		static public function getPathAndBasename($extKey, $version) {
			if (empty($extKey) || empty($version)) {
				return '';
			}

			$extKey  = strtolower($extKey);
			$version = Tx_Extbase_Utility_Arrays::integerExplode('.', $version);
			$path    = '%s/%s/%s_%d.%d.%d';

			return sprintf($path, $extKey[0], $extKey[1], $extKey, $version[0], $version[1], $version[2]);
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
			$filename = t3lib_div::getFileAbsFileName($filename);
			if (!self::fileExists($filename)) {
				return FALSE;
			}

			// Get filename for download
			if (empty($visibleFilename)) {
				$visibleFilename = basename($filename);
			}

			// Get file size
			$size = filesize($filename);
			if (empty($size)) {
				return FALSE;
			}

			// Set headers
			header('Content-Disposition: attachment; filename=' . (string) $visibleFilename);
			header('Content-type: x-application/octet-stream');
			header('Content-Transfer-Encoding: binary');
			header('Content-length:' . (string) $size);

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
		 * @param integer $lastChange Timestamp of the last file change
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

	}
?>