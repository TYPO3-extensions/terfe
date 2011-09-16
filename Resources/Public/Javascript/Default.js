/**
 * Returns default chart options
 * 
 * @return object Chart options
 */
function getDefaultChartOptions() {
	return {
		axes: {
			xaxis: {
				renderer: $.jqplot.CategoryAxisRenderer
			}
		},
		grid: {
			drawGridLines: true,
			gridLineColor: '#cccccc',
			background:    '#ffffff',
			borderColor:   '#4D4D4D',
			borderWidth:   1.0,
			shadow:        false
		},
		highlighter: {
			show: true,
			sizeAdjust: 5,
			tooltipLocation: 'ne',
			tooltipOffset: 6,
			tooltipAxes: 'y'
		},
		cursor: {
			show: false
		}
	};
}


(function($) {

	/**
	 * Render chart
	 *
	 * @param boolean renderShy Render shy charts
	 * @return void
	 */
	$.fn.renderChart = function(renderShy) {
		var containerId = $(this).attr('id');
		if (!containerId || typeof(charts) === 'undefined' || typeof(charts[containerId]) === 'undefined') {
			return;
		}

		var chart = charts[containerId];
		if ((!renderShy && chart.isShy) || chart.isRendered) {
			return;
		}

		var options = getDefaultChartOptions();
		options.title = chart.options.title;
		options.series = chart.options.series;

		$.jqplot(containerId, chart.lines, options);
		charts[containerId].isRendered = true;
	}


	/**
	 * Toggle extension row details
	 *
	 * @param mixed element Object or selector
	 * @return void
	 */
	$.fn.toggleExtensionDetails = function() {
		var $element = $(this);
		var counter = 0;

			// Find extension row container
		while (!$element.hasClass('extension-row') && counter < 5) {
			$element = $element.parent();
			counter++;
		}

			// Stop here without correct element
		$toggleElement = $element.find('div.extension-additional');
		if (typeof($element) === 'undefined') {
			return;
		}

			// Toggle visibility
		if ($toggleElement.css('display') === 'none') {
			$toggleElement.fadeIn('fast');

				// Render chart
			var $chart = $toggleElement.find('div.chart-container');
			if (typeof($chart) !== 'undefined') {
				$chart.renderChart(true);
			}
		} else {
			$toggleElement.fadeOut('fast');
		}
	}

})(jQuery);


jQuery(document).ready(function($) {

	/**
	 * Process all charts
	 */
	$('div.chart-container').renderChart(false);


	/**
	 * Add click event handler to all toggle elements
	 */
	$('.extension-row-toggle').click(function(event) {
		event.preventDefault();
		$(this).toggleExtensionDetails();
	});

});