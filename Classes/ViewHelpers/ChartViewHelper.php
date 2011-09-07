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
	 *
	 * For documentation and examples visit http://www.jqplot.com
	 */
	class Tx_TerFe2_ViewHelpers_ChartViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

		/**
		 * Disable the escaping interceptor
		 */
		protected $escapingInterceptorEnabled = FALSE;

		/**
		 * @var string
		 */
		protected $chart = '
			<div id="%1$s" style="height:%2$s;width:%3$s;"></div>
			<script type="text/javascript">
				$(document).ready(function(){
					$.jqplot(\'%1$s\', [[%4$s]], {%5$s});
				});
			</script>
		';


		/**
		 * Renders a jqPlot chart
		 *
		 * @param object $object The object to get chart from
		 * @param string $type The type of information to render
		 * @param integer $height Height of the chart
		 * @param integer $width Width of the chart
		 * @param string $color Color of the line
		 * @return string Chart
		 */
		public function render($object = NULL, $type = 'downloads', $height = 300, $width = 400, $color = '#FFA500') {
			if ($object === NULL) {
				$object = $this->renderChildren();
			}

			$points = array();
			$type = trim(strtolower($type));
			if ($object instanceof Tx_TerFe2_Domain_Model_Extension) {
				$points = $this->getExtensionPoints($object, $type);
			}

			$id = uniqid('chart_');
			$height = (int) $height . 'px';
			$width = (int) $width . 'px';
			$points = implode(',', $points);
			$options = '
				series:[{color:\'' . $color . '\'}]
			';

			return sprintf($this->chart, $id, $height, $width, $points, $options);
		}


		/**
		 * Returns the points by type for an extension model
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension Extension object
		 * @param string $type Type of the information to get
		 * @return array Points to render in chart
		 */
		protected function getExtensionPoints(Tx_TerFe2_Domain_Model_Extension $extension, $type) {
			$result = array();

			if ($type === 'downloads') {
				$versions = $extension->getVersions();
				foreach ($versions as $version) {
					$result[] = "['" . $version->getVersionNumber() . "'," . (int) $version->getDownloadCounter() . "]";
				}
			}

			return $result;
		}

	}
?>