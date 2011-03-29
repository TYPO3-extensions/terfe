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
	 * Utilities to manage ZIP files
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Utility_Zip {

		/**
		 * Create a ZIP archive
		 *
		 * @param string $fileName File name of the archive
		 * @param array $filesArray All files to insert
		 * @param string $overwrite Overwrite file if exists
		 * @return boolean TRUE if success
		 */
		static public function createArchive($fileName, array $filesArray, $overwrite = FALSE) {
			if (!class_exists('ZipArchive')) {
				throw new Exception('Please make sure that php zip extension is installed');
			}

			// Check if file already exists
			if (!$overwrite && Tx_TerFe2_Utility_Files::fileExists($zipFile)) {
				return TRUE;
			}

			// Create ZIP archive
			$createMode = ($overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE);
			$zipArchive = new ZipArchive();
			if (empty($zipArchive) || !$zipArchive->open($fileName, $createMode)) {
				throw new Exception('Could not open ZIP file to write');
			}

			// Add files
			foreach ($filesArray as $path => $content) {
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
		 * Creates a ZIP file from given extension T3X file
		 *
		 * @param string $fileName Path to the T3X file
		 * @return string File name of the ZIP file
		 */
		static public function convertT3xToZip($fileName) {
			if (empty($fileName)) {
				throw new Exception('No valid T3X file given to convert to ZIP file');
			}

			// Get archive name
			$archiveName = substr(basename($fileName), 0, strrpos(basename($fileName), '.')) . '.zip';
			$zipFile     = Tx_TerFe2_Utility_Files::getAbsoluteDirectory('typo3temp/') . $archiveName;

			// Check if file was cached
			if (Tx_TerFe2_Utility_Files::fileExists($zipFile)) {
				return $zipFile;
			}

			// Unpack extension files
			$filesArray = array();
			$content    = Tx_TerFe2_Utility_Files::unpackT3xFile($fileName);
			if (!empty($content['FILES']) && is_array($content['FILES'])) {
				foreach ($content['FILES'] as $fileInfo) {
					$filesArray[$fileInfo['name']] = $fileInfo['content'];
				}
			}

			// Create ext_emconf.php
			if (!empty($content['extKey']) && !empty($content['EM_CONF']) && is_array($content['EM_CONF'])) {
				$filesArray['ext_emconf.php'] = Tx_TerFe2_Utility_Files::createExtEmconfFile(
					$content['extKey'],
					$content['EM_CONF']
				);
			}

			// Create ZIP archive
			self::createArchive($zipFile, $filesArray);

			return $zipFile;
		}

	}
?>