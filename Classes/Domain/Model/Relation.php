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
		protected $key;

		/**
		 * Version of the field "key", e.g. key "php" and version "5.2"
		 * @var string
		 */
		protected $version;


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
		 * Setter for key
		 *
		 * @param string $key extension key, php, mysql or something else
		 * @return void
		 */
		public function setKey($key) {
			$this->key = $key;
		}


		/**
		 * Getter for key
		 *
		 * @return string extension key, php, mysql or something else
		 */
		public function getKey() {
			return $this->key;
		}


		/**
		 * Setter for version
		 *
		 * @param string $version Version of the field "key", e.g. key "php" and version "5.2"
		 * @return void
		 */
		public function setVersion($version) {
			$this->version = $version;
		}


		/**
		 * Getter for version
		 *
		 * @return string Version of the field "key", e.g. key "php" and version "5.2"
		 */
		public function getVersion() {
			return $this->version;
		}

	}
?>