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
	 * Json output view for the list action of extension controller
	 */
	class Tx_TerFe2_View_Extension_ListJson extends Tx_Extbase_MVC_View_AbstractView {

		/**
		 * Render method, returns latest Extensions
		 *
		 * @return string JSON content
		 */
		public function render() {
			$jsonArray  = array();
			$extensions = array();

				// Get extensions from view data
			if (!empty($this->variables['extensions']) && $this->variables['extensions'] instanceof Tx_Extbase_Persistence_QueryResult) {
				$extensions = $this->variables['extensions']->toArray();
			}
			if (!empty($extensions)) {
				foreach ($extensions as $extension) {
					$jsonArray[] = $extension->toArray();
				}
			}

			return json_encode($jsonArray);
		}

	}
?>