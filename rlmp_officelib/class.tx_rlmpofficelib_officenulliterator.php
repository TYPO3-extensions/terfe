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


/**
 * Null iterator class. This is used as a template for other iterators
 * and provides default methods for component leaf classes (like for example officetextparagraph)
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_officenulliterator {

	/**
	 * Theoretically jumps to the first element
	 *
	 * @return	void
	 */
	function first () {
	}

	/**
	 * Theoretically jumps to the next element
	 *
	 * @return	void
	 */
	function next () {

	}

	/**
	 * Theoretically jumps to the previous element
	 *
	 * @return	void
	 */
	function previous () {

	}

	/**
	 * Returns true if the end of elements is reached
	 *
	 * @return	boolean
	 */
	function isDone () {
		return true;
	}

	/**
	 * Theoretically returns the current item
	 *
	 * @return	object	The current Item
	 */
	function getCurrentItem () {

	}

	/**
	 * Theoretically applies a certain filter for traversing the object
	 *
	 * @param	mixed	Something
	 * @return	void
	 */
	function setFilter ($someFilter) {

	}
}

?>