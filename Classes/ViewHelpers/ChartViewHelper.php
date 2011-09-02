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
	 * Chart view helper
	 */
	class Tx_TerFe2_ViewHelpers_ChartViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

		/**
		 * Disable the escaping interceptor
		 */
		protected $escapingInterceptorEnabled = FALSE;

		/**
		 * @var string
		 */
		protected $scriptTag = '<script type="text/javascript">|</script>';


		/**
		 * Renders a jqPlot chart
		 *
		 * @param array $points Array of points on chart
		 * @return string Chart
		 */
		public function render($points = NULL) {
			if ($points === NULL) {
				$points = $this->renderChildren();
			}

			$script = '$.jqplot("chartdiv",  [[[1,2],[3,5.12],[5,13.1],[7,33.6],[9,85.9],[11,219.9]]]);';
			return str_replace('|', $script, $this->scriptTag);
		}

	}
?>