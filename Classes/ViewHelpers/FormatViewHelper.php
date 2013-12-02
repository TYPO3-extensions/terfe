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
	 * Format view helper
	 */
	class Tx_TerFe2_ViewHelpers_FormatViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

		/**
		 * Disable the escaping interceptor
		 */
		protected $escapingInterceptorEnabled = FALSE;


		/**
		 * Formats content with given function
		 *
		 * @param string $function Function to format the content
		 * @param mixed $content Content
		 * @return string Formated content
		 */
		public function render($function, $content = NULL) {
			if ($content === NULL) {
				$content = $this->renderChildren();
			}

			if (empty($function) || !function_exists($function)) {
				throw new Exception('Function "' . $function . '" not found to modify content');
			}

			if (!is_string($content) && !is_array($content)) {
				throw new Exception('"' . ucfirst(gettype($content)) . '" is not an allowed type in format view helper');
			}

			if (is_array($content)) {
				return call_user_func_array($function, $content);
			}

			return call_user_func($function, $content);
		}

	}
?>