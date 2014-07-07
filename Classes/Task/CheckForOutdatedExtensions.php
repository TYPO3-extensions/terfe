<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Tomas Norre <tomas.norre@gmail.com>
 *  (c) 2014 Thorsten Schneider <mail@thorsten-schneider.org>
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
 * Class Tx_TerFe2_Task_CheckForOutdatedExtensions
 */
class Tx_TerFe2_Task_CheckForOutdatedExtensions extends tx_scheduler_Task {

	/**
	 * @var Tx_Extbase_Persistence_Manager
	 */
	protected $persistenceManager;

	/**
	 * @var Tx_TerFe2_Domain_Repository_VersionRepository
	 */
	protected $versionRepository;

	/**
	 * @var array
	 */
	protected $coreVersions;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var array
	 */
	protected $supportedCoreVersions = array();

	/**
	 * Initialize Task
	 *
	 * @return void
	 */
	public function initializeTask() {
		$this->objectManager        = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->persistenceManager   = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		$this->versionRepository  = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
		$this->coreVersions         = json_decode(t3lib_div::getUrl(PATH_site . $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] . 'currentcoredata.json'), TRUE);
	}

	/**
	 * Execute Task
	 *
	 * @return bool
	 */
	public function execute() {

		$this->initializeTask();

		// Find all extension versions which are not outdated.
		$versions = $this->getNotOutdatedAndSecureVersions();

		$this->getLatestAndOldestSupportedTypo3Versions();

		$releaseDateOfOldestSupportedTypo3Version = $this->getReleaseDateOfOldestSupportedTypo3Version();

		// Foreach extension
		foreach ($versions as $version) {
			/** @var Tx_TerFe2_Domain_Model_Version $version */
			$version = $this->versionRepository->findByUid($version['uid']);

			if(!$version instanceof Tx_TerFe2_Domain_Model_Version) {
				continue;
			}

			$isOutdated = FALSE;

			if ($version->getUploadDate() === NULL) {
				$isOutdated = TRUE;
				// Check if date is set
			} elseif ($version->getUploadDate() < $releaseDateOfOldestSupportedTypo3Version) {
				$isOutdated = TRUE;
				// Check upload date against oldestActiveTYPO3Version first release date.
			} elseif (!$this->isVersionDependingOnAnActiveSupportedTypo3Version($version->getTypo3Dependency())) {
				$isOutdated = TRUE;
				// Check against dependency against TYPO3 not actively supported
			}


			if ($isOutdated) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					'tx_terfe2_domain_model_version',
					'uid = ' . $version->getUid(),
					array(
						'review_state' => -2
					)
				);
			}

		}

		return TRUE;
	}

	/**
	 * Get not outdated extensions
	 *
	 * @return mixed
	 */
	public function getNotOutdatedAndSecureVersions() {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid',
			'tx_terfe2_domain_model_version',
			'NOT deleted AND NOT hidden AND review_state >= 0'
		);

		return $rows;
	}

	/**
	 * Get the release date of the oldest supported TYPO3 Version.
	 *
	 * @return int
	 */
	public function getReleaseDateOfOldestSupportedTypo3Version() {

		$oldestMinorVersion = explode('.', $this->supportedCoreVersions['oldest']);
		$oldestMinorVersion = $oldestMinorVersion[0] . '.' . $oldestMinorVersion[1];

		$releaseDate = $this->coreVersions[$oldestMinorVersion]['releases'][$this->supportedCoreVersions['oldest']]['date'];

		return strtotime($releaseDate);
	}


	/**
	 * Get the latest and oldest supported TYPO3 Versions.
	 *
	 * @throws RuntimeException
	 * @return void
	 */
	public function getLatestAndOldestSupportedTypo3Versions() {
		if ($this->coreVersions === NULL) {
			throw new RuntimeException('typo3.org JSON not accessible!', 1399140291);
		}
		// Collect currently supported core versions
		$oldestSupportedCoreVersion = '99.99.99';
		$latestSupportedCoreVersion = '0.0.0';
		$allSupportedCoreVersions = array();

		foreach ($this->coreVersions as $version => $coreInfo) {
			// Only use keys that represent a branch number
			if (preg_match('/^\d+\.\d+$/', $version)) {
				if ($coreInfo['active'] == TRUE) {

					$allSupportedCoreVersions[] = $version;

					// Checks the latest version
					$latestBranchVersion = $coreInfo['latest'];
					if (!preg_match('/dev|alpha/', $latestBranchVersion)) {
						if (version_compare($latestSupportedCoreVersion, $latestBranchVersion, '<')) {
							$latestSupportedCoreVersion = $latestBranchVersion;
						}
					}

					// Check the oldest active version
					if (version_compare($version . '.0', $oldestSupportedCoreVersion, '<')) {
						$oldestSupportedCoreVersion = $version;
					}
				}
			}
		}

		// get first beta of oldest active version
		$oldestSupportedCoreVersionReleases = array_reverse($this->coreVersions[$oldestSupportedCoreVersion]['releases']);
		foreach ($oldestSupportedCoreVersionReleases as $subVersion => $subVersionInfo) {
			if (!preg_match('/dev|alpha/', $subVersion)) {
				$oldestSupportedCoreVersion = $subVersion;
				break;
			}
		}

		$this->supportedCoreVersions = array(
			'latest' => $latestSupportedCoreVersion,
			'oldest' => $oldestSupportedCoreVersion,
			'all' => $allSupportedCoreVersions,
		);
	}

	/**
	 * @param Tx_TerFe2_Domain_Model_Relation $dependency
	 *
	 * @return boolean
	 */
	public function isVersionDependingOnAnActiveSupportedTypo3Version($dependency) {

		$result = FALSE;

		if ($dependency instanceof Tx_TerFe2_Domain_Model_Relation) {
			$extensionMinimumVersion = $dependency->getMinimumVersion();
			$extensionMaximumVersion = $dependency->getMaximumVersion();

			foreach ($this->supportedCoreVersions['all'] as $version) {
				// gets core version x.x.0
				$supportedMinimumVersion = t3lib_utility_VersionNumber::convertVersionNumberToInteger($version . '.0');

				if ($supportedMinimumVersion >= $extensionMinimumVersion && $supportedMinimumVersion <= $extensionMaximumVersion) {
					$result = TRUE;
					break;
				}
			}
		}

		return $result;
	}
}