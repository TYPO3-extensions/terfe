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

			$result = @fopen($filename, 'r');
			return ($result !== FALSE);
		}


		/**
		 * Generates a file name via extension key, version and type
		 *
		 * @param string $extKey Extension Key
		 * @param string $version Version of the extension
		 * @param string $fileType File type of the returning path
		 * @return string Path and filename
		 */
		static public function generateFileName($extKey, $version, $fileType = 't3x') {
			if (empty($extKey) || empty($version) || empty($fileType)) {
				return '';
			}

			$extKey   = strtolower($extKey);
			$version = Tx_Extbase_Utility_Arrays::integerExplode('.', $version);
			$fileName = '%s_%d.%d.%d.' . strtolower(trim($fileType, '. '));

			return sprintf($fileName, $extKey, $version[0], $version[1], $version[2]);
		}


		/**
		 * Returns absolute path to given directory
		 *
		 * @return string Absolute path
		 */
		static public function getAbsDirectory($path) {
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
		 * @param string $filename Path to T3X file
		 * @return array Unpacked extension files
		 */
		static public function unpackT3xFile($filename) {
			if (empty($filename)) {
				return array();
			}

			// Get file content
			$contents = t3lib_div::getURL($filename);
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
			// Get md5 from local file
			if (self::isLocalUrl($filename)) {
				$filename = self::getLocalUrlPath($filename);
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
			if (self::isLocalUrl($filename)) {
				$filename = self::getLocalUrlPath($filename);
			}

			// Check if file exists
			if (!self::fileExists($filename)) {
				return FALSE;
			}

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
		 * Returns local filename from URL if located to current server
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
		 * Creates a ZIP file from given extension T3X file
		 * 
		 * TODO: Create ext_emconf.php
		 * 
		 * @param string $filename Path to the T3X file
		 * @param string $overwrite Overwrite file if exists
		 * @return string File name of the ZIP file
		 */
		static public function createT3xZipArchive($filename, $overwrite = FALSE) {
			if (!class_exists('ZipArchive')) {
				throw new Exception('Please make sure that php zip extension is installed');
			}
			if (empty($filename)) {
				return '';
			}

			// Get file names
			$archiveName = substr(basename($filename), 0, strrpos(basename($filename), '.')) . '.zip';
			$tempFile = self::getAbsDirectory('typo3temp/') . $archiveName;

			// Check if file was cached
			if ($overwrite || !self::fileExists($tempFile)) {
				// Unpack extension files
				$content = self::unpackT3xFile($filename);
				$createMode = ($overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE);
				if (empty($content['FILES']) || !is_array($content['FILES'])) {
					return '';
				}

				// Create ZIP archive
				$zipFile = new ZipArchive();
				$zipFile->open($tempFile, $createMode);
				foreach ($content['FILES'] as $fileInfo) {
					if (!empty($fileInfo['name']) && isset($fileInfo['content'])) {
						$zipFile->addFromString($fileInfo['name'], $fileInfo['content']);
					}
				}
				$zipFile->close();
			}

			return $tempFile;
		}

	}
?>