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
	 * Create zip archives from t3x files
	 */
	class Tx_TerFe2_Task_CreateZipArchivesTask extends Tx_TerFe2_Task_AbstractTask {

		/**
		 * @var string
		 */
		public $zipFilePath = 'fileadmin/extensionFiles/';

		/**
		 * @var Tx_TerFe2_Domain_Repository_VersionRepository
		 */
		protected $versionRepository;

		/**
		 * @var Tx_TerFe2_Provider_ProviderManager
		 */
		protected $providerManager;

		/**
		 * @var Tx_Extbase_Persistence_Manager
		 */
		protected $persistenceManager;


		/**
		 * Initialize task
		 *
		 * @return void
		 */
		public function initializeTask() {
			$this->versionRepository  = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
			$this->providerManager    = $this->objectManager->get('Tx_TerFe2_Provider_ProviderManager');
			$this->persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
			$this->zipFilePath = Tx_TerFe2_Utility_File::getAbsoluteDirectory($this->zipFilePath);
		}


		/**
		 * Execute the task
		 *
		 * @param integer $lastRun Timestamp of the last run
		 * @param integer $offset Starting point
		 * @param integer $count Element count to process at once
		 * @return boolean TRUE on success
		 */
		protected function executeTask($lastRun, $offset, $count) {
				// TODO: Remove testing values
			$offset = 0;

				// Get all versions without zip file
			$versions = $this->versionRepository->findWithoutZipFile($offset, $count);
			if (empty($versions)) {
				return FALSE;
			}

				// Build zip files
			foreach ($versions as $version) {
				$provider = $this->providerManager->getProvider($version->getExtensionProvider());
				$t3xFile = $provider->getFileUrl($version, 't3x');
				$zipFile = $this->zipFilePath . basename($provider->getFileName($version, 'zip'));

					// Check file hash
				$fileHash = Tx_TerFe2_Utility_File::getFileHash($t3xFile);
				if ($fileHash != $version->getFileHash()) {
					throw new Exception('File was changed and is therefore corrupt');
				}

					// Convert...
				$result = TRUE;
				if (!Tx_TerFe2_Utility_File::fileExists($zipFile)) {
					$result = Tx_TerFe2_Utility_Archive::convertT3xToZip($t3xFile, $zipFile);
				}

					// Save relative path into version model
				if (!empty($result)) {
					$zipFile = Tx_TerFe2_Utility_File::getRelativeDirectory($zipFile);
					$version->setZipFile($zipFile);
					$this->persistenceManager->persistAll();
				}
			}

			return TRUE;
		}

	}
?>