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
	 * Any type of relation of an extension
	 */
	class Tx_TerFe2_Domain_Model_Relation extends Tx_TerFe2_Domain_Model_AbstractValueObject {

		/**
		 * Dependancy, conflict or suggest
		 * @var string
		 * @validate NotEmpty
		 */
		protected $relationType;

		/**
		 * Extension key, php, mysql or something else
		 * @var string
		 * @validate NotEmpty
		 */
		protected $relationKey;

		/**
		 * Minimum required version for this Relation
		 * @var integer
		 */
		protected $minimumVersion;

		/**
		 * Maximum allowed version for this Relation
		 * @var integer
		 */
		protected $maximumVersion;


		/**
		 * Setter for relationType
		 *
		 * @param string $relationType Dependancy, conflict or suggest
		 * @return void
		 */
		public function setRelationType($relationType) {
			$this->relationType = $relationType;
		}


		/**
		 * Getter for relationType
		 *
		 * @return string Dependancy, conflict or suggest
		 */
		public function getRelationType() {
			return $this->relationType;
		}


		/**
		 * Setter for relationKey
		 *
		 * @param string $relationKey extension key, php, mysql or something else
		 * @return void
		 */
		public function setRelationKey($relationKey) {
			$this->relationKey = $relationKey;
		}


		/**
		 * Getter for relationKey
		 *
		 * @return string extension key, php, mysql or something else
		 */
		public function getRelationKey() {
			if (empty($this->relationKey)) {
				return '';
			}
			return strtolower(trim($this->relationKey));
		}


		/**
		 * Setter for minimumVersion
		 *
		 * @param integer $minimumVersion Minimum required version
		 * @return void
		 */
		public function setMinimumVersion($minimumVersion) {
			$this->minimumVersion = $minimumVersion;
		}


		/**
		 * Getter for minimumVersion
		 *
		 * @return integer Minimum required version
		 */
		public function getMinimumVersion() {
			return (int) $this->minimumVersion;
		}


		/**
		 * Setter for maximumVersion
		 *
		 * @param integer $maximumVersion Maximum allowed version
		 * @return void
		 */
		public function setMaximumVersion($maximumVersion) {
			$this->maximumVersion = $maximumVersion;
		}


		/**
		 * Getter for maximumVersion
		 *
		 * @return integer Maximum allowed version
		 */
		public function getMaximumVersion() {
			return (int) $this->maximumVersion;
		}


		/**
		 * Returns minumum and maximum version as string
		 *
		 * @return string Version
		 */
		public function getVersionString() {
			$version = array();

			if (!empty($this->minimumVersion)) {
				$version[] = Tx_TerFe2_Utility_Version::versionFromInteger($this->minimumVersion);
			}

			if (!empty($this->maximumVersion)) {
				$version[] = Tx_TerFe2_Utility_Version::versionFromInteger($this->maximumVersion);
			}

			return (!empty($version) ? implode(' - ', $version) : '');
		}


		/**
		 * Get software type
		 *
		 * @return string core, system or extension
		 */
		public function getType() {
			$key = $this->getRelationKey();

			if (empty($key)) {
				return '';
			}

			if ($key === 'cms' || $key === 'typo3') {
				return 'core';
			}

			if ($key === 'php') {
				return 'system';
			}

			return 'extension';
		}


		/**
		 * Is core relation
		 *
		 * @return boolean TRUE if related to core
		 */
		public function getIsCore() {
			return ($this->getType() == 'core');
		}


		/**
		 * Is system relation
		 *
		 * @return boolean TRUE if related to system
		 */
		public function getIsSystem() {
			return ($this->getType() == 'system');
		}


		/**
		 * Is extension relation
		 *
		 * @return boolean TRUE if related to an extension
		 */
		public function getIsExtension() {
			return ($this->getType() == 'extension');
		}

	}
?>