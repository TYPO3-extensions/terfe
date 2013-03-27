<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2013 Thomas Löffler <thomas.loeffler@typo3.org>
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


	class Tx_TerFe2_Task_ImportExtensionsFromQueueTask extends tx_scheduler_Task {

		/**
		 * PID for all extension related records
		 *
		 * @var int $pid
		 */
		protected $pid = 459;

		/**
		 * executes the importer
		 *
		 * @return bool
		 */
		public function execute() {
			$extensionsFromQueue = $this->getExtensionsFromQueue();

			// finish task if no extensions in queue
			if (empty($extensionsFromQueue)) {
				t3lib_div::sysLog('No new extensions in queue table', 'ter_fe2', 1);
				return TRUE;
			}

			foreach ($extensionsFromQueue as $ext) {
				$extensionData = $this->getExtensionData($ext['extensionuid']);
				if (!$this->versionExists($extensionData)) {
					$extUid = $this->extensionExists($extensionData);
					$this->saveExtension($extUid, $extensionData, $ext['crdate']);
					t3lib_div::sysLog('Extension "' . $extensionData['extensionkey'] . '", version ' . $extensionData['version'] . ' saved in ter_fe2', 'ter_fe2', 1);

					// update the EXT:solr Index Queue
					if (t3lib_extMgm::isLoaded('solr')) {
						$indexQueue = t3lib_div::makeInstance('tx_solr_indexqueue_Queue');
						$indexQueue->updateItem('tx_terfe2_domain_model_extension', $extUid);
					}
				}
				t3lib_div::sysLog('Extension "' . $extensionData['extensionkey'] . '" still exists with version ' . $extensionData['version'] . ' in ter_fe2', 'ter_fe2', 1);
				$this->removeExtensionFromQueue($ext['extensionuid']);
			}

			return TRUE;
		}

		public function removeExtensionFromQueue($extUid) {
			$updateQueue = array(
				'tstamp' => time(),
				'imported_to_fe' => 1
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_ter_extensionqueue', 'extensionuid = ' . $extUid, $updateQueue);
		}

		/**
		 * Gets extensions from queue which
		 * are not imported yet in ter_fe2
		 *
		 * @return array $extensions Extensions in queue table
		 */
		public function getExtensionsFromQueue() {
			$extensions = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'extensionuid,crdate',
				'tx_ter_extensionqueue',
				'NOT deleted AND NOT imported_to_fe',
				FALSE,
				'crdate'
			);

			return $extensions;
		}

		/**
		 * Gets the extension data out of ter tables
		 *
		 * @param int $extensionUid
		 *
		 * @return array $extData
		 */
		public function getExtensionData($extensionUid) {
			$extData = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
				'tx_ter_extensions.*, tx_ter_extensiondetails.*',
				'tx_ter_extensions
				LEFT JOIN tx_ter_extensiondetails ON tx_ter_extensions.uid = tx_ter_extensiondetails.extensionuid',
				'tx_ter_extensions.uid = ' . (int) $extensionUid
			);
			return $extData;
		}

		/**
		 * checks if a version with a specific version
		 * string exists
		 *
		 * @param array $extData
		 *
		 * @return boolean
		 */
		public function versionExists($extData) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'tx_terfe2_domain_model_version.uid',
				'tx_terfe2_domain_model_version
				LEFT JOIN tx_terfe2_domain_model_extension ON tx_terfe2_domain_model_extension.uid = tx_terfe2_domain_model_version.extension',
				'NOT tx_terfe2_domain_model_version.deleted
				AND tx_terfe2_domain_model_version.version_string = "' . mysql_real_escape_string($extData['version']) .'"
				AND tx_terfe2_domain_model_extension.ext_key = "' . mysql_real_escape_string($extData['extensionkey']) . '"'
			);
			return (boolean) $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		}

		/**
		 * @param array $extData
		 *
		 * @return int $uid extension uid
		 */
		public function extensionExists($extData) {
			$extRec = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
				'uid',
				'tx_terfe2_domain_model_extension',
				'NOT deleted AND ext_key = "' . mysql_real_escape_string($extData['extensionkey']) .'"'
			);
			if ($extRec) {
				return $extRec['uid'];
			}
			return $this->createExtension($extData);
		}

		/**
		 * @param array $extData
		 *
		 * @return int $uid extension uid of new record
		 */
		public function createExtension($extData) {
			$insertExtension = array(
				'pid' => $this->pid,
				'ext_key' => $extData['extensionkey'],
				'last_upload' => time(),
				'last_maintained' => time(),
				'versions' => 0,
				'last_version' => 0,
				'frontend_user' => $extData['lastuploadbyusername'],
				'crdate' => time(),
				'tstamp' => time()
			);

			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_terfe2_domain_model_extension', $insertExtension);
			return $GLOBALS['TYPO3_DB']->sql_insert_id();
		}

		/**
		 * @param int $extUid
		 * @param array $extData
		 * @param int $crdate
		 */
		public function saveExtension($extUid, $extData, $crdate) {
			$versionUid = $this->createVersion($extUid, $extData, $crdate);
			if ($versionUid) {
				$this->addRelations($versionUid, $extData['dependencies']);
				$this->updateExtension($versionUid, $extUid);
			}
		}

		/**
		 * @param int $extUid
		 * @param array $extData
		 * @param int $crdate
		 *
		 * @return int $versionUid
		 */
		public function createVersion($extUid, $extData, $crdate) {
			$states = tx_em_Tools::getDefaultState(NULL);
			$categories = tx_em_Tools::getDefaultCategory(NULL);

			$insertVersion = array(
				'pid' => $this->pid,
				'extension' => $extUid,
				'title' => $extData['title'],
				'description' => $extData['description'],
				'author' => $this->createAuthor($extData),
				'version_number' => t3lib_div::int_from_ver($extData['version']),
				'version_string' => $extData['version'],
				'upload_date' => $crdate,
				'upload_comment' => $extData['uploadcomment'],
				'file_hash' => $extData['t3xfilemd5'],
				'download_counter' => 0,
				'frontend_download_counter' => 0,
				'state' => !empty($states[(string) $extData['state']]) ? (string) $extData['state'] : 'n/a',
				'em_category' => !empty($categories[(string) $extData['category']]) ? (string) $extData['category'] : '',
				'load_order' => $extData['loadorder'],
				'priority' => $extData['priority'],
				'shy' => (boolean) $extData['shy'],
				'uploadfolder' => $extData['uploadfolder'] ? $extData['uploadfolder'] : '',
				'create_dirs' => $extData['createdirs'] ? $extData['createdirs'] : '',
				'modify_tables' => $extData['modifytables'] ? $extData['modifytables'] : '',
				'lock_type' => $extData['locktype'] ? $extData['locktype'] : '',
				'clear_cache_on_load' => (boolean) $extData['clearcacheonload'],
				'cgl_compliance' => $extData['codingguidelinescompliance'] ? $extData['codingguidelinescompliance'] : '',
				'cgl_compliance_note' => $extData['codingguidelinescompliancenote'] ? $extData['codingguidelinescompliancenote'] : '',
				'review_state' => 0,
				'manual' => '',
				'has_manual' => (boolean) $extData['ismanualincluded'],
				'media' => 0,
				'experiences' => 0,
				'software_relations' => 0,
				'extension_provider' => 'file',
				'has_zip_file' => 0,
				'has_images' => 0,
				't3x_file_size' => @filesize(PATH_site . 'fileadmin/ter/' . $extData['extensionkey'] . '_' . $extData['version_string'] . '.t3x'),
				'zip_file_size' => 0
			);

			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_terfe2_domain_model_version', $insertVersion);
			return $GLOBALS['TYPO3_DB']->sql_insert_id();
		}

		/**
		 * @param int $versionUid
		 * @param int $extUid
		 */
		public function updateExtension($versionUid, $extUid) {
			$updateExtension = array(
				'tstamp' => time(),
				'versions' => $this->getNumberOfVersions($extUid),
				'last_version' => $versionUid
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_extension', 'uid = ' . $extUid, $updateExtension);
		}

		/**
		 * @param int $extUid
		 *
		 * @return int $numberOfVersions
		 */
		public function getNumberOfVersions($extUid) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid',
				'tx_terfe2_domain_model_version',
				'extension = ' . $extUid . ' AND NOT deleted'
			);
			return $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		}

		/**
		 * @param int $versionUid
		 * @param string $dependencies
		 */
		public function addRelations($versionUid, $dependencies) {
			$countRelations = 0;
			$dependencies = unserialize($dependencies);
			foreach ($dependencies as $relation) {
				$relationType = $relation['kind'];
				$relationKey = $relation['extensionKey'];
				$version = $this->getVersionByRange($relation['versionRange']);
				if ($relationKey) {
					$insertRelation = array(
						'relation_type'   => $relationType,
						'relation_key'    => $relationKey,
						'minimum_version' => $version[0],
						'maximum_version' => $version[1],
						'version'         => $versionUid,
						'related_extension' => $this->getUidOfRelatedExtension($relationKey)
					);
					$countRelations++;
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_terfe2_domain_model_relation', $insertRelation);
				}
			}

			$updateVersion = array(
				'software_relations' => $countRelations
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_version', 'uid = ' . $versionUid, $updateVersion);
		}

		/**
		 * @param string $extKey
		 *
		 * @return int $uidOfRelatedExtension
		 */
		public function getUidOfRelatedExtension($extKey) {
			$extRec = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
				'uid',
				'tx_terfe2_domain_model_extension',
				'ext_key = "' . mysql_real_escape_string($extKey) .'" AND NOT deleted'
			);
			if ($extRec['uid']) {
				return $extRec['uid'];
			}
			return 0;
		}

		/**
		 * @param array $extData
		 *
		 * @return int $authorUid
		 */
		public function getAuthor($extData) {
			return $this->authorExists($extData);
		}

		/**
		 * @param array $extData
		 *
		 * @return int $authorUid
		 */
		public function authorExists($extData) {
			$authorRec = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
				'uid',
				'tx_terfe2_domain_model_author',
				'NOT deleted
				AND username = "' . mysql_real_escape_string($extData['lastuploadbyusername']) . '"
				AND name = "' . mysql_real_escape_string($extData['authorname']) . '"
				AND email = "' . mysql_real_escape_string($extData['authoremail']) . '"
				AND company = "' . mysql_real_escape_string($extData['authorcompany']) . '"'
			);
			if ($authorRec['uid']) {
				return $authorRec['uid'];
			}

			return $this->createAuthor($extData);
		}

		/**
		 * @param array $extData
		 *
		 * @return int $authorUid
		 */
		public function createAuthor($extData) {
			$insertAuthor = array(
				'name' => $extData['authorname'],
				'email' => $extData['authoremail'],
				'company' => $extData['authorcompany'],
				'username' => $extData['lastuploadbyusername'],
				'frontend_user' => $this->getFeUserUidFromUsername($extData['lastuploadbyusername'])
			);

			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_terfe2_domain_model_author', $insertAuthor);
			return $GLOBALS['TYPO3_DB']->sql_insert_id();
		}

		/**
		 * @param string $username
		 *
		 * @return int $feUserUid
		 */
		public function getFeUserUidFromUsername($username) {
			$userRec = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
				'uid',
				'fe_users',
				'username = "' . mysql_real_escape_string($username) . '"'
			);
			if ($userRec['uid']) {
				return $userRec['uid'];
			}
			return FALSE;
		}

		/**
		 * Returns an array with minimum and maximum version number from range
		 *
		 * @param string $version Range of versions
		 * @return array Minumum and maximum version number
		 */
		protected function getVersionByRange($version) {
			$version = Tx_Extbase_Utility_Arrays::trimExplode('-', $version);
			$minimum = (!empty($version[0]) ? t3lib_div::int_from_ver($version[0]) : 0);
			$maximum = (!empty($version[1]) ? t3lib_div::int_from_ver($version[1]) : 0);

			return array($minimum, $maximum);
		}

	}


?>