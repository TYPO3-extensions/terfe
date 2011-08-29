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
	 * Utilities to manage zip and t3x files
	 */
	class Tx_TerFe2_Utility_Archive {

		/**
		 * Create a zip archive
		 *
		 * @param string $filename File name of the archive
		 * @param array $files All files to insert
		 * @param string $overwrite Overwrite file if exists
		 * @return boolean TRUE if success
		 */
		public static function createZipArchive($filename, array $files, $overwrite = FALSE) {
			if (!class_exists('ZipArchive')) {
				throw new Exception('Please make sure that php zip extension is installed');
			}

				// Check if file already exists
			if (!$overwrite && Tx_TerFe2_Utility_File::fileExists($filename)) {
				return TRUE;
			}

				// Create zip archive
			$createMode = ($overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE);
			$zipArchive = new ZipArchive();
			if (empty($zipArchive) || !$zipArchive->open($filename, $createMode)) {
				throw new Exception('Could not open ZIP file to write');
			}

				// Add files
			foreach ($files as $path => $content) {
				if (empty($path)) {
					continue;
				}
				if (!$zipArchive->addFromString($path, (string) $content)) {
					throw new Exception('Could not write file "' . $path . '" into ZIP file');
				}
			}

				// Save and close
			if (!$zipArchive->close()) {
				throw new Exception('Could not close ZIP file');
			}

			return TRUE;
		}


		/**
		 * Returns the content of a zip archive
		 *
		 * @param string $filename File name
		 * @param string $path Path to extract into
		 * @param mixed $files Single filename or array of filenames to extract
		 * @return boolean TRUE if success
		 */
		public static function extractZipArchive($filename, $path, $files = NULL) {
			if (!class_exists('ZipArchive')) {
				throw new Exception('Please make sure that php zip extension is installed');
			}

				// Check if file exists
			if (!Tx_TerFe2_Utility_File::fileExists($filename)) {
				throw new Exception('File "' . $filename . '" not found to extract');
			}

				// Check if path is writable
			$path = Tx_TerFe2_Utility_File::getAbsoluteDirectory($path);
			if (!is_writable($path)) {
				throw new Exception('Path "' . $path . '" is not writeable');
			}

				// Load zip archive
			$zipArchive = new ZipArchive();
			if (empty($zipArchive) || !$zipArchive->open($filename)) {
				throw new Exception('Could not open zip file to read');
			}

				// Extract
			if (!$zipArchive->extractTo($path, $files)) {
				throw new Exception('Could not extract file "' . $filename . '" to path "' . $path . '"');
			}

				// Save and close
			if (!$zipArchive->close()) {
				throw new Exception('Could not close ZIP file');
			}

			return TRUE;
		}


		/**
		 * Creates a zip file from given extension T3X file
		 *
		 * @param string $t3xFile Path to the t3x file
		 * @param string $zipFile Path to the zip file
		 * @return boolean TRUE if success
		 */
		public static function convertT3xToZip($t3xFile, $zipFile) {
			if (empty($t3xFile)) {
				throw new Exception('No valid t3x file given to convert to zip file');
			}
			if (empty($zipFile)) {
				throw new Exception('No valid zip file given to convert t3x file into');
			}

				// Check if file was cached
			if (Tx_TerFe2_Utility_File::fileExists($zipFile)) {
				return TRUE;
			}

				// Unpack extension files
			$files = array();
			$content = self::extractT3xArchive($t3xFile);
			if (!empty($content['FILES']) && is_array($content['FILES'])) {
				foreach ($content['FILES'] as $fileInfo) {
					$files[$fileInfo['name']] = $fileInfo['content'];
				}
			}

				// Create ext_emconf.php
			if (!empty($content['extKey']) && !empty($content['EM_CONF']) && is_array($content['EM_CONF'])) {
				$files['ext_emconf.php'] = Tx_TerFe2_Utility_File::createExtEmconfFile(
					$content['extKey'],
					$content['EM_CONF']
				);
			}

				// Create ZIP archive
			self::createArchive($zipFile, $files);

			return TRUE;
		}


		/**
		 * Extract files from an extension t3x file
		 *
		 * @param string $filename Path to t3x file
		 * @return array Unpacked extension files
		 */
		public static function extractT3xArchive($filename) {
			if (empty($filename)) {
				return array();
			}

				// Get local file name if on same server
			if (Tx_TerFe2_Utility_File::isLocalUrl($filename)) {
				$filename = Tx_TerFe2_Utility_File::getAbsolutePathFromUrl($filename);
			}

				// Get file content
			$content = t3lib_div::getURL($filename);
			if (empty($content)) {
				return array();
			}

			return self::decompressT3xStream($content);
		}


		/**
		 * Decompress t3x file content stream
		 *
		 * @param string $content File content
		 * @return array Files array
		 */
		public static function decompressT3xStream($content) {
			if (empty($content)) {
				return array();
			}

				// Get content parts
			list($hash, $compression, $data) = explode(':', $content, 3);
			unset($content);

				// Get extension files
			$files = gzuncompress($data);
			if (empty($files) || $hash != md5($files)) {
				return array();
			}

				// Unserialize files array
			return unserialize($files);
		}

	}
?>