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
	 * Utilities to manage session content
	 */
	class Tx_TerFe2_Utility_Session {

		/**
		 * @var string
		 */
		static protected $sessionName = 'TerFe2';

		/**
		 * @var array
		 */
		static protected $sessionContent;


		/**
		 * Load session content
		 *
		 * @return void
		 */
		static public function load() {
			if (empty($GLOBALS['TSFE']->fe_user)) {
				throw new Exception('Could not load session without frontend user');
			}

			if (empty(self::$sessionContent)) {
				self::$sessionContent = $GLOBALS['TSFE']->fe_user->getKey('ses', self::$sessionName);
			}
		}


		/**
		 * Save session content
		 *
		 * @return void
		 */
		static public function save() {
			if (empty($GLOBALS['TSFE']->fe_user)) {
				throw new Exception('Could not save session without frontend user');
			}

			$GLOBALS['TSFE']->fe_user->setKey('ses', self::$sessionName, self::$sessionContent);
			$GLOBALS['TSFE']->storeSessionData();
		}


		/**
		 * Add a value to session
		 *
		 * @param string $key Name of the value
		 * @param mixed $value Value content
		 * @return void
		 */
		static public function addValue($key, $value) {
			if (empty($key)) {
				throw new Exception('Empty keys are not allowed for a session value');
			}

			self::$sessionContent[$key] = $value;
		}


		/**
		 * Add multiple values to session
		 *
		 * @param array $value Key <-> value pairs
		 * @return void
		 */
		static public function addValues(array $values) {
			foreach ($values as $key => $value) {
				self::addValue($key, $value);
			}
		}


		/**
		 * Check if session contains given value key
		 *
		 * @param string $key Name of the value
		 * @return boolean TRUE if exists
		 */
		static public function hasValue($key) {
			return isset(self::$sessionContent[$key]);
		}


		/**
		 * Get value from session
		 *
		 * @param string $key Name of the value
		 * @return mixed Value content
		 */
		static public function getValue($key) {
			if (self::hasValue($key)) {
				return self::$sessionContent[$key];
			}

			return NULL;
		}


		/**
		 * Add an extension download to session
		 *
		 * @param string $extensionKey Downloaded extension key
		 * @return void
		 */
		static public function addDownload($extensionKey) {
			$downloadedExtensions = self::getDownloads();
			$downloadedExtensions[$extensionKey] = $GLOBALS['SIM_EXEC_TIME'];
			self::setDownloads($downloadedExtensions);
		}


		/**
		 * Check if given extension was downloaded in period
		 *
		 * @param string $extensionKey Extension key to check
		 * @param integer $period Time period in hours
		 * @return boolean TRUE if extension was downloaded in given period
		 */
		static public function hasDownloaded($extensionKey, $period = 24) {
			$downloadedExtensions = self::getDownloads();
			if (!is_array($downloadedExtensions)) {
				return FALSE;
			}
			if (!array_key_exists($extensionKey, $downloadedExtensions)) {
				return FALSE;
			}

				// Check timestamp
			$period     = 3600 * (int) $period; // Seconds
			$timestamp  = $downloadedExtensions[$extensionKey];
			$difference = ((int) $GLOBALS['SIM_EXEC_TIME'] - (int) $timestamp);
			if ($difference > $period) {
				return FALSE;
			}

			return TRUE;
		}


		/**
		 * Get extension download timestamp from session
		 *
		 * @param string $extensionKey Downloaded extension key
		 * @return integer Timestamp
		 */
		static public function getDownloadTime($extensionKey) {
			$downloadedExtensions = self::getDownloads();
			if (!is_array($downloadedExtensions)) {
				return 0;
			}
			if (!array_key_exists($extensionKey, $downloadedExtensions)) {
				return 0;
			}

			return (int) $downloadedExtensions[$extensionKey];
		}


		/**
		 * Returns an array of all downloaded extensions
		 * 
		 * @return array Downloaded extensions
		 */
		static public function getDownloads() {
			$downloadedExtensions = self::getValue('downloadedExtensions');
			if (is_array($downloadedExtensions)) {
				return $downloadedExtensions;
			}

			return array();
		}


		/**
		 * Sets an array of downloaded extensions
		 * 
		 * @param array $downloadedExtensions Downloaded extensions
		 * @return void
		 */
		static public function setDownloads(array $downloadedExtensions) {
			self::addValue('downloadedExtensions', $downloadedExtensions);
		}

	}
?>