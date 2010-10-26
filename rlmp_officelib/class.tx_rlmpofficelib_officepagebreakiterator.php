<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Robert Lemke (robert@typo3.org)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
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
***************************************************************/

require_once(t3lib_extMgm::extPath('rlmp_officelib').'class.tx_rlmpofficelib_officetextiterator.php');

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */

/**
 * Office general purpose *internal* iterator class. Provides methods for traversing
 * the text composites / components objects tree structure and do some processing
 * if a certain criteria is met.
 *
 * Use this class as a template for your own specialized iterator.
 *
 * The iterator design pattern is also known as "cursor"
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_officepagebreakiterator extends rlmp_officelib_officetextiterator {

	var $pageNumber = 1;
	var $charactersSinceLastPageBreak = 0;
	var $toc = array ();

	function processObject (&$obj) {
			// Perform a page break if conditions are right
		if ($this->evaluateCriteria($obj, $this->conf['pageBreaks'])) {
			$this->pageNumber ++;
			$this->charactersSinceLastPageBreak = 0;
			$this->toc['pageTitles'][$this->pageNumber] = (isset($obj->numbering) ? $obj->numbering.' ':'') . $obj->content;
		}

			// Create a new entry in the table of content if conditions apply
		if ($this->evaluateCriteria($obj, $this->conf['tocEntries'])) {
			$this->toc['items'][] = array (
				'sectionHeader' => $obj->content,
				'level' => $obj->level,
				'page' => $this->pageNumber,
				'numbering' => $obj->numbering,
			);

				// Set title of the first page if still emtpy
			if ($this->pageNumber == 1 && isset ($obj->content) && !isset ($this->toc['pageTitles'][1])) {
				$this->toc['pageTitles'][1] = (isset($obj->numbering) ? $obj->numbering.' ':'') . $obj->content;
			}
		}

		$this->charactersSinceLastPageBreak += strlen($obj->content);
		$obj->conditionTags['page'] = $this->pageNumber;

		if (isset ($obj->hyperlinkKey)) {
			if (!isset ($obj->parentDocObj->hyperlinkObjects[$obj->hyperlinkKey])) {
					// It seems like in Open Office Writer this is implemented the same: An internal link jumps to the
					// FIRST occurrence of a named link, therefore links to two different headers having the same content
					// is not possible.
				$obj->parentDocObj->hyperlinkObjects[$obj->hyperlinkKey] =& $obj;
			}
		}
	}
}

?>