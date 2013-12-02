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
	 * Crop content view helper
	 */
	class Tx_TerFe2_ViewHelpers_CropViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

		/**
		 * Disable the escaping interceptor
		 */
		protected $escapingInterceptorEnabled = FALSE;


		/**
		 * Returns cropped content
		 *
		 * Notice: Use Tx_Fluid_ViewHelpers_Format_CropViewHelper for 4.6 and above
		 * 
		 * @param string $content Content to return
		 * @param integer $length Text length
		 * @param boolean $keepWords Crop complete words
		 * @param string $ending Append string to cropped text
		 * @return string Cropped content
		 */
		public function render($content = NULL, $length = 100, $keepWords = TRUE, $ending = '...') {
			if ($content === NULL) {
				$content = $this->renderChildren();
			}

			$length = (int) $length;
			if (empty($content) || strlen($content) <= abs($length)) {
				return $content;
			}

			if (isset($GLOBALS['LANG'])) {
				$languageObject = $GLOBALS['LANG'];
			} else {
				$languageObject = t3lib_div::makeInstance('language');
				$languageObject->init('en');
			}

			if ($length < 0) {
				$content = $languageObject->csConvObj->substr($languageObject->charSet, $content, $length);
				$trunc = strpos($content, ' ');
				$content = ($trunc && $keepWords ? $ending . substr($content, $trunc) : $ending . $content);
			} else {
				$content = $languageObject->csConvObj->substr($languageObject->charSet, $content, 0, $length);
				$trunc = strrpos($content, ' ');
				$content = ($trunc && $keepWords) ? substr($content, 0, $trunc) . $ending : $content . $ending;
			}

			return $content;
		}

	}
?>