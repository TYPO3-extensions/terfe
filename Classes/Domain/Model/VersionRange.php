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
	 * Version range of a software relation
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Domain_Model_VersionRange extends Tx_Extbase_DomainObject_AbstractValueObject {

		/**
		 * The minimum value
		 * @var integer
		 */
		protected $minimumValue;

		/**
		 * The maximum value
		 * @var integer
		 */
		protected $maximumValue;


		/**
		 * Set minimum and maximum value while creating an instance
		 * 
		 * @param integer $minimumValue Minimum value of the range
		 * @param integer $maximumValue Maximum value of the range
		 * @return void
		 */
		public function __construct($minimumValue = 0, $maximumValue = 0) {
			$this->setMinimumValue($minimumValue);
			$this->setMaximumValue($maximumValue);
		}


		/**
		 * Setter for minimumValue
		 *
		 * @param integer $minimumValue Minimum value of the range
		 * @return void
		 */
		public function setMinimumValue($minimumValue) {
			$this->minimumValue = $minimumValue;
		}


		/**
		 * Getter for minimumValue
		 *
		 * @return integer Minimum value of the range
		 */
		public function getMinimumValue() {
			return $this->minimumValue;
		}


		/**
		 * Setter for maximumValue
		 *
		 * @param integer $maximumValue Maximum value of the range
		 * @return void
		 */
		public function setMaximumValue($maximumValue) {
			$this->maximumValue = $maximumValue;
		}


		/**
		 * Getter for maximumValue
		 *
		 * @return integer Maximum value of the range
		 */
		public function getMaximumValue() {
			return $this->maximumValue;
		}
	}
?>