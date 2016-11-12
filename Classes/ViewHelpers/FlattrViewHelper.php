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
class Tx_TerFe2_ViewHelpers_FlattrViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @var string
     */
    protected $buttonHtml = "<script type='text/javascript'>
							/* <![CDATA[ */
							(function() {
								var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
								s.type = 'text/javascript';
								s.async = true;
								s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
								t.parentNode.insertBefore(s, t);
							})();
							/* ]]> */</script>
								<a class='FlattrButton' style='display:none;'
									title='%s'
									data-flattr-uid='%s'
									data-flattr-button='compact'
									href='%s'>
									Flattr Button
								</a>";


    /**
     * Renders a flattr button
     *
     * @param $flattrData null|object result object of flattr thing
     * @return string HTML of flattr button
     */
    public function render($flattrData = NULL)
    {
        if ($flattrData == NULL) {
            return '';
        }

        $button = sprintf($this->buttonHtml, $flattrData->title, $flattrData->owner->username, $flattrData->url);

        return $button;
    }

}

?>