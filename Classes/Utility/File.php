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
		 * @param string $path Path to the file / directory
		 * @param boolean $create Create if not exists
		 * @return string Absolute path
		 */
		public static function getAbsoluteDirectory($path, $create = TRUE) {
			if (empty($path)) {
				return PATH_site;
			}

			if ($create && !self::fileExists(PATH_site . $path)) {
				t3lib_div::mkdir_deep(PATH_site, $path);
			}

			return PATH_site . rtrim($path, '/') . '/';
		}


		/**
		 * Returns absolute path to given directory
		 *
		 * @param string $path Path to the file / directory
		 * @return string Relative path
		 */
		public static function getRelativeDirectory($path) {
			if (empty($path)) {
				return '';
			}

			$path = str_replace(PATH_site, '', $path);
			if (is_dir($path)) {
				$path = rtrim($path, '/') . '/';
			}

			return $path;
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

				// Send file
			header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Expires: Sat, 10 Jan 1970 00:00:00 GMT');
			header('Pragma: public');
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename=' . (string) $visibleFileName);
			header('Content-type: x-application/octet-stream');
			header('Content-Transfer-Encoding: binary');
			if (self::isAbsolutePath($filename)) {
				header('Content-Length: ' . filesize($filename));
			}
			ob_clean();
			flush();
			readfile($filename);
			exit;
		}


		/**
		 * Transfers file content to client browser
		 *
		 * This function must be called before any HTTP headers have been sent
		 *
		 * @param string $content File content
		 * @param string $visibleFileName File name for downloaded file
		 * @return boolean FALSE if something wents wrong
		 */
		public static function transferFileContent($content, $fileName) {
			if (empty($content) || empty($fileName)) {
				return FALSE;
			}

				// Send file content
			header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Expires: Sat, 10 Jan 1970 00:00:00 GMT');
			header('Pragma: public');
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename=' . (string) $fileName);
			header('Content-type: x-application/octet-stream');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . strlen($content));
			ob_clean();
			flush();
			echo $content;
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
			if (empty($fromFileName) || empty($toFileName)) {
				return FALSE;
			}

				// Check if file already exists
			$toFileExists = self::fileExists($toFileName);
			if ($toFileExists && !$overwrite) {
				return FALSE;
			}

				// Check if target directory exists
			if (!self::fileExists(dirname($toFileName))) {
				return FALSE;
			}

				// Get local url
			if (self::isLocalUrl($fromFileName)) {
				$fromFileName = self::getAbsolutePathFromUrl($fromFileName);
			}

				// Get file content
			$fromFile = t3lib_div::getURL($fromFileName);
			if ($fromFile === FALSE) {
				return FALSE;
			}

				// Remove existing when successfully fetched new file
			if ($toFileExists) {
				self::removeFile($toFileName);
			}

				// Copy file to new name
			$result = t3lib_div::writeFile($toFileName, $fromFile);
			
			return ($result !== FALSE);
		}


		/**
		 * Copy a directory
		 *
		 * @param string $fromDirectory Existing directory
		 * @param string $toParentDirectory Name of the new parent directory
		 * @param string $newDirectoryName Name of the new directory
		 * @param boolean $overwrite A directory with new name will be overwritten if set
		 * @return boolean TRUE if success
		 */
		public static function copyDirectory($fromDirectory, $toParentDirectory, $newDirectoryName = '', $overwrite = FALSE) {
			if (!self::fileExists($toParentDirectory)) {
				return FALSE;
			}

			$rollbackFiles = array();

			$newDirectory = rtrim($toParentDirectory, '/') . '/' . rtrim($newDirectoryName, '/') . '/';
			$newDirectory = self::getAbsoluteDirectory($newDirectory);

			$files = self::getFiles($fromDirectory, '', 0, TRUE);
			foreach ($files as $filename) {
				$newFilename = str_replace($fromDirectory, '', $filename);
				if (!self::copyFile($filename, $newDirectory . $newFilename, $overwrite)) {
					foreach ($rollbackFiles as $rollbackFile) {
						unlink($rollbackFile);
					}
					return FALSE;
				}
				$rollbackFiles[] = $newDirectory . $newFilename;
			}

			return TRUE;
		}


		/**
		 * Move a file
		 *
		 * @param string $fromFileName Existing file
		 * @param string $toFileName File name of the new file
		 * @param boolean $overwrite A file with new name will be overwritten if set
		 * @return boolean TRUE if success
		 */
		public static function moveFile($fromFileName, $toFileName, $overwrite = FALSE) {
			$result = self::copyFile($fromFileName, $toFileName, $overwrite);
			if ($result && self::isAbsolutePath($fromFileName)) {
				self::removeFile($fromFileName);
			}
			return $result;
		}


		/**
		 * Move a directory
		 *
		 * @param string $fromDirectory Existing directory
		 * @param string $toParentDirectory Name of the new parent directory
		 * @param string $newDirectoryName Name of the new directory
		 * @param boolean $overwrite A directory with new name will be overwritten if set
		 * @return boolean TRUE if success
		 */
		public static function moveDirectory($fromDirectory, $toParentDirectory, $newDirectoryName = '', $overwrite = FALSE) {
			if (!self::copyDirectory($fromDirectory, $toParentDirectory, $newDirectoryName, $overwrite)) {
				return FALSE;
			}
			return self::removeDirectory($fromDirectory, TRUE);
		}


		/**
		 * Remove a file
		 * 
		 * @param string $filename Path to the file
		 * @return boolean TRUE if success
		 */
		public static function removeFile($filename) {
			if (self::fileExists($filename)) {
				return unlink($filename);
			}
			return TRUE;
		}


		/**
		 * Remove a directory and all contents
		 * 
		 * @param string $directory Directory path
		 * @param boolean $removeNonEmpty Remove non empty directories
		 * @return TRUE if success
		 */
		public static function removeDirectory($directory, $removeNonEmpty = TRUE) {
			return t3lib_div::rmdir($directory, (bool) $removeNonEmpty);
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
			return $hostUrl . str_replace(PATH_site, '', $path);
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