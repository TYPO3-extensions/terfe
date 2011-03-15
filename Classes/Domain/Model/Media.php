<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
	 *       and Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
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
	 * Any type of media associated to an extension
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Domain_Model_Media extends Tx_Extbase_DomainObject_AbstractEntity {

		/**
		 * Title of the media
		 * @var string
		 * @validate NotEmpty
		 */
		protected $title;

		/**
		 * Media type (link, image, video, pdf)
		 * @var string
		 * @validate NotEmpty
		 */
		protected $type;

		/**
		 * Short language parameter (en, de, dk, etc.)
		 * @var string
		 * @validate NotEmpty
		 */
		protected $language;

		/**
		 * Filename or url
		 * @var string
		 * @validate NotEmpty
		 */
		protected $source;

		/**
		 * Short description of this media attachment
		 * @var string
		 */
		protected $description;


		/**
		 * Setter for title
		 *
		 * @param string $title Title of the media
		 * @return void
		 */
		public function setTitle($title) {
			$this->title = $title;
		}


		/**
		 * Getter for title
		 *
		 * @return string Title of the media
		 */
		public function getTitle() {
			return $this->title;
		}


		/**
		 * Setter for type
		 *
		 * @param string $type Media type (link, image, video, pdf)
		 * @return void
		 */
		public function setType($type) {
			$this->type = $type;
		}


		/**
		 * Getter for type
		 *
		 * @return string Media type (link, image, video, pdf)
		 */
		public function getType() {
			return $this->type;
		}


		/**
		 * Setter for language
		 *
		 * @param string $language Short language parameter (en, de, dk, etc.)
		 * @return void
		 */
		public function setLanguage($language) {
			$this->language = $language;
		}


		/**
		 * Getter for language
		 *
		 * @return string Short language parameter (en, de, dk, etc.)
		 */
		public function getLanguage() {
			return $this->language;
		}


		/**
		 * Setter for source
		 *
		 * @param string $source Filename or url
		 * @return void
		 */
		public function setSource($source) {
			$this->source = $source;
		}


		/**
		 * Getter for source
		 *
		 * @return string Filename or url
		 */
		public function getSource() {
			return $this->source;
		}


		/**
		 * Setter for description
		 *
		 * @param string $description Short description of this media attachment
		 * @return void
		 */
		public function setDescription($description) {
			$this->description = $description;
		}


		/**
		 * Getter for description
		 *
		 * @return string Short description of this media attachment
		 */
		public function getDescription() {
			return $this->description;
		}

	}
?>