<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Susanne Moog <s.moog@neusta.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
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
 * ************************************************************* */

/**
 * The address controller for the Address package
 *
 * @version $Id: $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Tx_TerDoc_Controller_CliController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_TerDoc_Domain_Repository_ExtensionRepository
	 */
	protected $extensionRepository;
	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * Initializes the current action
	 *
	 * @return void
	 */
	public function initializeAction() {


		// Define controller property here
		$this->settings = Tx_TerDoc_Utility_Cli::getSettings();
		$this->settings['repositoryDir'] = Tx_TerDoc_Utility_Cli::sanitizeDirectoryPath($this->settings['repositoryDir']);
		$this->unzipCommand = $this->settings['unzipCommand'];
		$this->verbose = $this->settings['cliVerbose'] ? TRUE : FALSE;
		$this->logFullPath = strlen($this->settings['logFullPath']) ? $this->settings['logFullPath'] : FALSE;

		// Extends settings
		$this->settings['homeDir'] = Tx_TerDoc_Utility_Cli::sanitizeDirectoryPath(PATH_site . $this->settings['homeDir']);
		$this->settings['documentsCache'] = $this->settings['homeDir'] . 'documentscache/';
		$this->settings['lockFile'] = $this->settings['homeDir'] . 'tx_terdoc_render.lock';
		$this->settings['md5File'] = $this->settings['homeDir'] . 'tx_terdoc_extensionsmd5.txt';
		$this->settings['extensionFile'] = $this->settings['repositoryDir'] . 'extensions.xml.gz';

		// Initialize objects
		$this->languageGuesserServiceObj = t3lib_div::makeInstanceService('textLang'); // Initialize language guessing service:
		$this->extensionRepository = t3lib_div::makeInstance('Tx_TerDoc_Domain_Repository_ExtensionRepository', $this->settings, $this->arguments); // Initialize repository
		// Makes sure the envionment is good and throw an error if that is not the case
		$this->validateEnvironement();
	}

	/**
	 * Index action for this controller. Displays a list of addresses.
	 *
	 * @param  array $arguments list of possible arguments
	 * @return void
	 */
	public function renderAction($arguments) {

		// Options  coming from the CLI
		$this->arguments = $arguments;

		$this->initializeAction();

		if (!$this->isLocked() || $this->arguments['force']) {
			// create a lock
			touch($this->settings['lockFile']);

			Tx_TerDoc_Utility_Cli::log(strftime('%d.%m.%y %R') . ' ter_doc renderer starting ...');

			if ($this->extensionRepository->wasModified() || $this->arguments['force']) {
				Tx_TerDoc_Utility_Cli::log('* extensions.xml was modified since last run');

				if ($this->extensionRepository->updateDB()) {

					$this->extensionRepository->deleteOutdatedDocuments();
					$modifiedExtensionVersionsArr = $this->extensionRepository->getModifiedExtensionVersions();

					foreach ($modifiedExtensionVersionsArr as $extensionAndVersionArr) {
						$transformationErrorCodes = array();
						$extensionKey = $extensionAndVersionArr['extensionkey'];
						$version = $extensionAndVersionArr['version'];
						$documentDir = Tx_TerDoc_Utility_Cli::getDocumentDirOfExtensionVersion($extensionKey, $version);

						Tx_TerDoc_Utility_Cli::log('* Rendering documents for extension "' . $extensionKey . '" (' . $version . ')');
						$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_terdoc_renderproblems', 'extensionkey="' . $extensionKey . '" AND version="' . $version . '"');
						Tx_TerDoc_Utility_Cli::log('temporary end');

						exit();
						if ($this->documentCache_transformManualToDocBook($extensionKey, $version, $transformationErrorCodes)) {
							foreach ($this->outputFormats as $label => $formatInfoArr) {
								Tx_TerDoc_Utility_Cli::log('   * Rendering ' . $label);
								$formatInfoArr['object']->renderCache($documentDir);
							}
						} else {
							$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_terdoc_manuals', 'extensionkey="' . $extensionKey . '" AND version="' . $version . '"');
							Tx_TerDoc_Utility_Cli::log('	* No manual found or problem while extracting manual');
						}
						$this->pageCache_clearForExtension($extensionKey);
						t3lib_div::writeFile($documentDir . 't3xfilemd5.txt', $extensionAndVersionArr['t3xfilemd5']);

						foreach ($transformationErrorCodes as $errorCode) {
							$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_terdoc_renderproblems', array('extensionkey' => $extensionKey, 'version' => $version, 'tstamp' => time(), 'errorcode' => $errorCode));
						}
						Tx_TerDoc_Utility_Cli::log('   * Error code(s): ' . implode(',', $transformationErrorCodes));
					}
					$this->pageCache_clearForAll();
				}
				Tx_TerDoc_Utility_Cli::log(strftime('%d.%m.%y %R') . ' done.');
			} else
				Tx_TerDoc_Utility_Cli::log('Extensions.xml was not modified since last run, so nothing to do - done.');

			unlink($this->settings['lockFile']);
		}
		else {
			Tx_TerDoc_Utility_Cli::log('... aborting - another process seems to render documents right now!');
		}
	}

	/**
	 * Validate the running environment. Check whether the path are correct and if some directories exist
	 *
	 * @return void
	 */
	protected function validateEnvironement() {
		// if home directory is not defined, create this one now.
		if (!is_dir($this->settings['documentsCache'])) {
			// @todo: check whether a set up action would be necessary
			//throw new Exception('Exception thrown #1294746784: temp directory does not exist "' . $this->settings['documentsCache'] . '". Run command setUp', 1294746784);
			try {
				mkdir($this->settings['documentsCache'], 0777, TRUE);
			} catch (Exception $e) {
				Tx_TerDoc_Utility_Cli::log($e->getMessage());
			}
		}

		// Check if configuration is valid ...and throw error if that is not the case
		if (!is_dir($this->settings['repositoryDir'])) {
			throw new Exception('Exception thrown #1294657643: directory does not exist "' . $this->settings['repositoryDir'] . '". Make sure key "repositoryDir" is properly defined in file ' . $configurationArray['typoscriptFile'], 1294657643);
		}
	}

	/**
	 * Index action for this controller. Displays a list of addresses.
	 *
	 * @return void
	 */
	protected function isLocked() {
		$result = FALSE;
		// Check if another process currently renders the documents:
		if (file_exists($this->settings['lockFile'])) {
			Tx_TerDoc_Utility_Cli::log('Found .lock file ...');

			// If the lock is not older than X minutes, skip index creation:
			if (filemtime($this->settings['lockFile']) > (time() - (6 * 60 * 60))) {
				if (!$this->debug) {
					$result = TRUE;
				}
			} else {
				Tx_TerDoc_Utility_Cli::log('... lock file was older than 6 hours, so start rendering anyway');
			}
		}
		return $result;
	}

	/**
	 * display some help on the console
	 *
	 * @return void
	 */
	public function helpAction() {

		$message = <<< EOF
handles TER documentation

usage:
    /var/www/typo3/cli_dispatch.phpsh ter_doc <options> <commands>

options:
    -h, --help            - print this message
    -f, --force           - force de command to be executed
    -l=10, --limit=10     - force de command to be executed

commands:
    render                - render documentation cache
EOF;

		Tx_TerDoc_Utility_Cli::log($message);
	}

}

?>