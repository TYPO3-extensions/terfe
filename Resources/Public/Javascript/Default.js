jQuery(document).ready(function($) {

	/**
	 * Default chart options
	 */
	var defaultChartOptions = {
		axes: {
			xaxis: {
				renderer: $.jqplot.CategoryAxisRenderer
			}
		},
		grid: {
			drawGridLines: true,
			gridLineColor: '#cccccc',
			background:    '#fffdf6',
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


	/**
	 * Render chart
	 *
	 * @param string containerId ID of the chart container
	 * @param boolean renderShy Render shy charts
	 * @return void
	 */
	function renderChart(containerId, renderShy) {
		if (!containerId || typeof(charts) === 'undefined' || typeof(charts[containerId]) === 'undefined') {
			return;
		}

		var chart = charts[containerId];
		if ((!renderShy && chart.isShy) || chart.isRendered) {
			return;
		}

		var options = defaultChartOptions;
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
	function toggleExtensionDetails(element) {
		var $element = $(element);
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
			$toggleElement.show();

				// Render chart
			var $chart = $toggleElement.find('div.chart-container');
			if (typeof($chart) !== 'undefined') {
				renderChart($chart.attr('id'), true);
			}
		} else {
			$toggleElement.hide();
		}
	}


	/**
	 * Process all charts
	 */
	$('div.chart-container').each(function(index, element) {
		renderChart($(element).attr('id'), false);
	});


	/**
	 * Add click event handler to all toggle elements
	 */
	$('.extension-row-toggle').click(function(event) {
		event.preventDefault();
		toggleExtensionDetails($(this));
	});

});