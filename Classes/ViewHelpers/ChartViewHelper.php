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
class Tx_TerFe2_ViewHelpers_ChartViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper
{

    /**
     * Disable the escaping interceptor
     */
    protected $escapingInterceptorEnabled = FALSE;

    /**
     * @var string
     */
    protected $chart = '
			<div id="%1$s" style="height:%2$s;width:%3$s;" class="chart-container"></div>
			<script type="text/javascript">
				if (typeof(charts) == \'undefined\') {
					var charts = [];
				}
				charts[\'%1$s\'] = {
					lines: %4$s,
					options: {%5$s},
					isShy: %6$s,
					isRendered: false
				};
			</script>
		';


    /**
     * Renders a jqPlot chart
     *
     * @param object $object The object to get chart from
     * @param string $method The render method
     * @param string $title Title of the chart
     * @param integer $height Height of the chart
     * @param integer $width Width of the chart
     * @param string $color Color of the line
     * @param integer $pointCount Count of points to render in one line
     * @param boolean $renderOnLoad Render chart when DOM is ready
     * @return string Chart
     */
    public function render($object = NULL, $method = 'downloadsByVersion', $title = '', $height = 300, $width = 400, $color = '#4D4D4D', $pointCount = 10, $renderOnLoad = TRUE)
    {
        if ($object === NULL) {
            $object = $this->renderChildren();
        }

        // Check object type
        if (!$object instanceof Tx_Extbase_DomainObject_DomainObjectInterface) {
            throw new Exception('Charts can only be rendered for domain objects yet');
        }

        // Check given method name
        if (empty($method)) {
            throw new Exception('Can not render a chart without render method');
        }

        // Get method name
        $method = 'get' . ucfirst(trim($method));
        if (!method_exists($this, $method)) {
            throw new Exception('No method with name "' . $method . '" defined in chart view helper');
        }

        // Get chart options
        $id = uniqid('chart-');
        $height = (int)$height . 'px';
        $width = (int)$width . 'px';
        $lines = json_encode($this->$method($object, (int)$pointCount));
        $options = '
				title: \'' . $title . '\',
				series: [{color:\'' . $color . '\'}]
			';
        $isShy = (empty($renderOnLoad) ? 'true' : 'false');

        return sprintf($this->chart, $id, $height, $width, $lines, $options, $isShy);
    }


    /**
     * Returns downloads by version
     *
     * @param Tx_TerFe2_Domain_Model_Extension Extension object
     * @param integer $pointCount Count of points to render in one line
     * @return array Lines to render in chart
     */
    protected function getDownloadsByVersion(Tx_TerFe2_Domain_Model_Extension $extension, $pointCount = 10)
    {
        $points = array();
        $versions = $extension->getReverseVersionsByVersionNumber();
        $counter = 0;

        foreach ($versions as $version) {
            if ($counter === $pointCount) {
                break;
            }
            $counter++;
            $points[] = array((string)$version->getVersionString(), (int)$version->getAllDownloads());
        }

        krsort($points);

        return array(array_values($points));
    }


}

?>