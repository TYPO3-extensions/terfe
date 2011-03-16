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
	 * Date Time View Helper
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_ViewHelpers_DateTimeViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

		/**
		 * Renders an extension icon of given extension and version
		 *
		 * @param mixed $dateTime Time to format
		 * @param string $format Format of the resulting time
		 * @return string Formated time
		 */
		public function render($dateTime = NULL, $format = 'Y-m-d h-i-s') {
			if ($dateTime === NULL) {
				$dateTime = $this->renderChildren();
			}

			if (empty($dateTime)) {
				$dateTime = new DateTime;
			}

			if (is_int($dateTime) || is_string($dateTime)) {
				return date($format, (int) $dateTime);
			}

			if ($dateTime instanceof DateTime) {
				return $dateTime->format($format);
			}

			return '';
		}

	}
?>