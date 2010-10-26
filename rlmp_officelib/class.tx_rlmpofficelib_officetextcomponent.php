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

require_once(t3lib_extMgm::extPath('rlmp_officelib').'class.tx_rlmpofficelib_officenulliterator.php');

/**
 * Text component class. This is the "template" for all other content based objects. In general, there
 * are two types, components which have no children or composites which usually have no content but
 * some children.
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_textcomponent {

	var $parentDocObj = null;		// Reference to the parent document object
	var $conditionTags = array();	// Set from outside to create a certain collection of objects (ie. render condition). See officedocument class for more details.
	var	$content = null;			// This object's content. In many cases, this is some CDATA
	var $type = '';					// Type of this component, depends on the context. Fx. for a paragraph it would be 'complete' or 'cdata'
	var $level = null;				// Used by some elements like headers
	var $parentObj = null;			// The parent object.
	var $styleName = null;			// Name of the style (if any) to be applied for this object

	/**
	 * Default constructor. Please specify the document object which holds this officetextcomponent.
	 * Additionally you may specify some content while creating this object.
	 *
	 * @param	object	&$parentDocObj: The father document object
	 * @param	mixed	$content: Some content, which will be stored inside this object
	 * @return	void
	 */
	function rlmp_officelib_textcomponent (&$parentDocObj, $content='') {
		$this->parentDocObj =& $parentDocObj;
		$this->content = $content;
	}

	/**
	 * Returns this object's parent (if any). This information is set by the add() function of the
	 * parent object, which means it is essential to use add() rather than adding an object to the
	 * children array yourself!
	 *
	 * @return	object		Reference to the parent object (if any).
	 */
	function getParent () {
		return $this->parentObj;
	}

	/**
	 * Returns true if a parent object exists
	 *
	 * @return	boolean		true if a parent object exists
	 */
	function hasParent () {
		return is_object ($this->parentObj);
	}

	/**
	 * Returns true if this object has children (for components is always false)
	 *
	 * @return	boolean		true if this object has child objects
	 */
	function hasChildren () {
		return false;
	}

	/**
	 * Returns true if this object should be rendered with the current condition tags / values set. Just
	 * set the document's conditionTags array accordingly.
	 *
	 * @return	boolean		true if this object should be rendered
	 */
	function shouldBeRendered () {
		$renderMe = true;
		if (is_array ($this->parentDocObj->conditionTags)) {
			foreach ($this->parentDocObj->conditionTags as $tag => $value) {
				if ($this->conditionTags[$tag] != $value) $renderMe = false;
			}
		}
		return $renderMe;
	}

	/**
	 * Method for rendering the current officetextcomponent
	 *
	 * @param	string
	 * @return	void
	 */
	function render () {
		if ($this->shouldBeRendered() && is_callable(array ($this->parentDocObj, 'callRenderFunction')))	{
			return $this->parentDocObj->callRenderFunction ($this);
		}
	}
}





/**
 * Text composite class. In addition to its mother class textcomponent, objects of this class may also
 * have children, which is actually the reason why this class exists.
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 * @see class rlmp_officelib_textcomponent
 */
class rlmp_officelib_textcomposite extends rlmp_officelib_textcomponent {

	var $children = array ();

	/**
	 * Adds a child object to this textcomposite object
	 *
	 * @param	object	$child: Object to be added
	 * @return	void
	 */
	function add (&$child) {
		if (is_object($child)) {
			$child->parentDocObj =& $this->parentDocObj;
			$child->parentObj =& $this;
			$this->children[] = $child;
		}
	}

	/**
	 * Removes a certain child
	 *
	 * TODO! HOW TO DETERMINE WHICH CHILD? UID? CURSOR?
	 *
	 * @param	string
	 * @return	void
	 */
	function remove (&$child) {

	}

	/**
	 * Method for rendering this object. Because it's a composite, it's told to render
	 * all its children.
	 *
	 * @return	void
	 */
	function render() {
		$childrenOut = '';
		if (is_array ($this->children)) {
			foreach ($this->children as $child) {
				$childrenOut .= $child->render ();
			}
		}
		$out = $childrenOut;	// Set to children's output to show error messages if there are any
		if ($this->shouldBeRendered () && is_callable(array ($this->parentDocObj, 'callRenderFunction')))	{
			$out = $this->parentDocObj->callRenderFunction ($this, $childrenOut);
		}
		return $out;
	}

	/**
	 * Returns true if this object has children
	 *
	 * @return	boolean		true if this object has child objects
	 */
	function hasChildren () {
		return (count ($this->children) > 0);
	}

	/**
	 * Returns an array with references to all child objects to this object
	 *
	 * @return	array	This object's child objects
	 */
	function getChildren () {
		return $this->children;
	}

	/**
	 * Creates a new iterator for traversing this object.
	 *
	 * @param	string	Iterator classname
	 * @return	object	Iterator object
	 */
	function createIterator ($className = 'rlmp_officelib_officetextiterator') {
		return new $className ($this);
	}
}





	/**
	 *
	 * Specialized child classes follow:
	 *
	 */

class rlmp_officelib_tcparagraph extends rlmp_officelib_textcomposite {}
class rlmp_officelib_tcheader extends rlmp_officelib_textcomposite {}
class rlmp_officelib_tcspan extends rlmp_officelib_textcomposite {}
class rlmp_officelib_tclinebreak extends rlmp_officelib_textcomponent {}
class rlmp_officelib_tcspace extends rlmp_officelib_textcomponent {}
class rlmp_officelib_tctab extends rlmp_officelib_textcomponent {}
class rlmp_officelib_tcerror extends rlmp_officelib_textcomponent {}
class rlmp_officelib_tcmeta extends rlmp_officelib_textcomponent {}
class rlmp_officelib_tcvariable extends rlmp_officelib_textcomponent {}
class rlmp_officelib_tcbookmark extends rlmp_officelib_textcomponent {}

class rlmp_officelib_tcfield extends rlmp_officelib_textcomponent {
	var $fixed = false;
}

class rlmp_officelib_tclink extends rlmp_officelib_textcomposite {
	var $href = '';
}

class rlmp_officelib_tcimage extends rlmp_officelib_textcomponent {
	var $height=0;
	var $width=0;
	var $displayHeight=0;
	var $displayWidth=0;
}

class rlmp_officelib_tcindex extends rlmp_officelib_textcomposite {
	var $indexTitleObj = null;
}

class rlmp_officelib_tctable extends rlmp_officelib_textcomposite {
	var $name = '';
	var $type = '';
	var $numberRowsSpanned = null;
	var $numberColumnsSpanned = null;
	var $numCols = 0;
	var $valueType = '';

	/**
	 * Constructor. Please specify the document object which holds this officetextcomponent.
	 * Additionally you may specify some content and the type of this table object.
	 *
	 * @param	object	&$parentDocObj: The father document object
	 * @param	mixed	$content: Some content, which will be stored inside this object
	 * @param	string	$type: The type of this table composite: 'table', 'row', 'header-row' or 'cell'
	 * @return	void
	 */
	function rlmp_officelib_tctable (&$parentDocObj, $content='', $type='') {
		$this->type = $type;
		$this->parentDocObj =& $parentDocObj;
		$this->content = $content;
	}

}

class rlmp_officelib_tclist extends rlmp_officelib_textcomposite {

	var $continueNumbering = false;


	/**
	 * Constructor. Please specify the document object which holds this officetextcomponent.
	 * Additionally you may specify some content and the type of this table object.
	 *
	 * @param	object	&$parentDocObj: The father document object
	 * @param	mixed	$content: Some content, which will be stored inside this object
	 * @param	string	$type: The type of this list composite: 'unordered-list', 'ordered-list' or 'list-item'
	 * @return	void
	 */
	function rlmp_officelib_tclist (&$parentDocObj, $content='', $type='') {
		$this->type = $type;
		$this->parentDocObj =& $parentDocObj;
		$this->content = $content;
	}

}

?>