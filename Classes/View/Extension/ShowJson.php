<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2012 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	 * Json output view for the show action of extension controller
	 */
	class Tx_TerFe2_View_Extension_ShowJson extends Tx_Extbase_MVC_View_AbstractView {

		/**
		 * @var array
		 */
		protected $internalKeys = array(
			'frontendUser',
			'flattrUsername',
			'crdate',
			'reverseVersionsWithPositiveReviewsByVersionNumber',
			'reverseVersionsByVersionNumber',
			'extensionProvider',
			'uid',
			'pid',
			'value',
		);

		/**
		 * Render method, returns details about an extension
		 *
		 * @return string JSON content
		 */
		public function render() {
			$jsonArray = array();

			if (!empty($this->variables['extension']) && $this->variables['extension'] instanceof Tx_TerFe2_Domain_Model_Extension) {
				$extension = $this->variables['extension']->toArray();
				$lastVersion = $this->variables['extension']->getLastVersion();
				$version = array();

				if (!empty($lastVersion)) {
					$version = $lastVersion->toArray();
				}

				$jsonArray = array_merge($extension, $version);
				$jsonArray = $this->cleanupInternalKeys($jsonArray);
			}

			exit(json_encode($jsonArray));
		}

		/**
		 * Remove internal fields recursive
		 * 
		 * @param array $values Reference to the values array
		 * @return array Cleaned array
		 */
		protected function cleanupInternalKeys(array $values) {
			foreach ($values as $key => $value) {
				if (in_array($key, $this->internalKeys) || $value === '__lazy__') {
					unset($values[$key]);
				} else if (!empty($value) && is_array($value)) {
					$values[$key] = $this->cleanupInternalKeys($value);
				}
			}
			return $values;
		}

	}
?>