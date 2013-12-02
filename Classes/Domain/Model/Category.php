<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
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
	 * Frontend category of the extension
	 */
	class Tx_TerFe2_Domain_Model_Category extends Tx_TerFe2_Domain_Model_AbstractValueObject {

		/**
		 * Title of the category
		 * @var string
		 * @validate NotEmpty
		 */
		protected $title;

		/**
		 * Description of the category
		 * @var string
		 */
		protected $description;


		/**
		 * Setter for title
		 *
		 * @param string $title Title of the category
		 * @return void
		 */
		public function setTitle($title) {
			$this->title = $title;
		}


		/**
		 * Getter for title
		 *
		 * @return string Title of the category
		 */
		public function getTitle() {
			return $this->title;
		}


		/**
		 * Setter for description
		 *
		 * @param string $description Description of the category
		 * @return void
		 */
		public function setDescription($description) {
			$this->description = $description;
		}


		/**
		 * Getter for description
		 *
		 * @return string Description of the category
		 */
		public function getDescription() {
			return $this->description;
		}

	}
?>