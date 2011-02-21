<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
	 *           Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Domain_Model_Relation extends Tx_Extbase_DomainObject_AbstractValueObject {

		/**
		 * Dependancy, conflict or suggest
		 * @var string
		 * @validate NotEmpty
		 */
		protected $relationType;

		/**
		 * Core, extension or system
		 * @var string
		 * @validate NotEmpty
		 */
		protected $softwareType;

		/**
		 * extension key, php, mysql or something else
		 * @var string
		 * @validate NotEmpty
		 */
		protected $relationKey;

		/**
		 * Version range, something like 3.8.1-4.5.1
		 * @var Tx_TerFe2_Domain_Model_VersionRange
		 * @validate NotEmpty
		 */
		protected $versionRange;


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
		 * Setter for softwareType
		 *
		 * @param string $softwareType Core, extension or system
		 * @return void
		 */
		public function setSoftwareType($softwareType) {
			$this->softwareType = $softwareType;
		}


		/**
		 * Getter for softwareType
		 *
		 * @return string Core, extension or system
		 */
		public function getSoftwareType() {
			return $this->softwareType;
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
			return $this->relationKey;
		}


		/**
		 * Setter for versionRange
		 *
		 * @param Tx_TerFe2_Domain_Model_VersionRange $versionRange Version range of the relation
		 * @return void
		 */
		public function setVersionRange(Tx_TerFe2_Domain_Model_VersionRange $versionRange) {
			$this->versionRange = $versionRange;
		}


		/**
		 * Getter for versionRange
		 *
		 * @return Tx_TerFe2_Domain_Model_VersionRange Version range of the relation
		 */
		public function getVersionRange() {
			return $this->versionRange;
		}

	}
?>