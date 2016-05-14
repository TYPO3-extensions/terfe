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
	 * @var Tx_Extbase_Persistence_IdentityMap
	 */
	protected $identityMap;

	/**
	 * @var Tx_Extbase_Persistence_Session
	 */
	protected $session;

	/**
	 * @var array
	 */
	protected $supportedCoreVersions = array();

	/**
	 * @var int
	 */
	protected $releaseDateOfOldestSupportedTypo3Version;

	/**
	 * @var tx_solr_indexqueue_Queue
	 */
	protected $solrIndexQueue;

	/**
	 * Initialize Task
	 *
	 * @return void
	 */
	public function initializeTask() {
		$this->objectManager        = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->persistenceManager   = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		$this->identityMap          = $this->objectManager->get('Tx_Extbase_Persistence_IdentityMap');
		$this->session              = $this->objectManager->get('Tx_Extbase_Persistence_Session');
		$this->versionRepository  = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
		$this->coreVersions         = json_decode(t3lib_div::getUrl(PATH_site . $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] . 'currentcoredata.json'), TRUE);
		$this->solrIndexQueue       = $this->objectManager->get('tx_solr_indexqueue_Queue');
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

		$this->releaseDateOfOldestSupportedTypo3Version = $this->getReleaseDateOfOldestSupportedTypo3Version();

		// Foreach extension
		foreach ($versions as $version) {
			$this->checkVersion($version);
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
			'NOT deleted AND NOT hidden AND review_state >= 0',
			'',
			'crdate DESC',
			'3000'
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
			if (preg_match('/^\d+\.\d+$/', $version) || preg_match('/^\d+$/', $version)) {
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
				$version = (string)$version;
				// gets core version x.x.0
				$supportedMinimumVersion = t3lib_utility_VersionNumber::convertVersionNumberToInteger($version . '.0');
				$extensionMinimumVersionAsString = Tx_TerFe2_Utility_Version::versionFromInteger($extensionMinimumVersion);

				/*
				 * checks if extension dependency lies within the first release of the main release version
				 * or extension minimum version begins with main release version
				 */
				if (($supportedMinimumVersion >= $extensionMinimumVersion || strpos($extensionMinimumVersionAsString, $version) === 0)
						&& $supportedMinimumVersion <= $extensionMaximumVersion) {
					$result = TRUE;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * check if the given version is outdated and mark it in database
	 *
	 * @param integer $version
	 */
	protected function checkVersion($version) {
		/** @var Tx_TerFe2_Domain_Model_Version $version */
		$version = $this->versionRepository->findByUid($version['uid']);

		if(!$version instanceof Tx_TerFe2_Domain_Model_Version) {
			return;
		}

		$isOutdated = FALSE;

		if ($version->getUploadDate() === NULL) {
			$isOutdated = TRUE;
			// Check if date is set
		} elseif ($version->getUploadDate() < $this->releaseDateOfOldestSupportedTypo3Version) {
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

			if ($version->getExtension() && $version->getExtension()->getUid()) {
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					'tx_terfe2_domain_model_extension',
					'uid = ' . $version->getExtension()->getUid(),
					array(
						'tstamp' => time()
					)
				);
				$this->solrIndexQueue->updateItem('tx_terfe2_domain_model_extension', $version->getExtension()->getUid());
			}
		}

		$this->cleanupMemory($version);
	}

	/**
	 * free some memory after checking a version
	 *
	 * prevent memory leaks on the long running scheduler task
	 *
	 * @param Tx_TerFe2_Domain_Model_Version $version
	 */
	public function cleanupMemory($version) {
		if ($this->identityMap->hasObject($version)) {
			$this->identityMap->unregisterObject($version);
		}
		$this->session->unregisterReconstitutedObject($version);
		foreach ($version->getSoftwareRelations() as $relation) {
			/** @var $relation Tx_TerFe2_Domain_Model_Relation */
			if ($this->identityMap->hasObject($relation)) {
				$this->identityMap->unregisterObject($relation);
			}
			$this->session->unregisterReconstitutedObject($relation);
		}
		if ($this->identityMap->hasObject($version->getExtension())) {
			$this->identityMap->unregisterObject($version->getExtension());
			$this->session->unregisterReconstitutedObject($version->getExtension());
		}
	}
}