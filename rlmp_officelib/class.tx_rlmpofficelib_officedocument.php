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

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(t3lib_extMgm::extPath('rlmp_officelib').'class.tx_rlmpofficelib_officepagebreakiterator.php');

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   62: class rlmp_officelib_officedocument
 *  119:     function rlmp_officelib_officedocument ()
 *  128:     function init ()
 *  137:     function load ()
 *  147:     function save ()
 *  158:     function render ($compositeName='body', $conditions='')
 *  182:     function callRenderFunction ($objToBeRendered, $preRendered='')
 *  197:     function setRenderEngine (&$renderEngingeObj)
 *  209:     function getTextComposite ($compositeName)
 *  221:     function getTOC ($compositeName='body')
 *  234:     function performPageBreaks ($compositeName='body')
 *  254:     function getMergedStyles ()
 *  277:     function mergeStyles ($styleName, $styleArr)
 *
 * TOTAL FUNCTIONS: 12
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Template class (factory product) for the implementation of office document
 * objects. Don't instantiate this class, rather create a sub class of this
 * class and instantiate it using the factory method "createDocument" located
 * in rlmp_officelib_officefactory.
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_officedocument {

	var $textComposites = array ();			// An array holding different text composites. Most common objects are 'body' and headers and footers for the different styles
	var $debug = false;						// In debug mode the render class and other parts might create some helpful output
	var $documentTitle = 'Untitled';		// Title of the document
	var $relPathAndFilename = '';			// path and filename relative to the site's PATH, leading to the original file (if any)

	/*
		$stylesArr contains all styles of this document in multi-dimensional associative array. The styles are indexed by their
		unique name where each of the styles should at least contain the following fields:

			'parentStyleName' => string,		// Name of the parent style (if any)
			'mode' => string,					// Style mode, e.g. 'style' or 'list-level-style-bullet'
			'subStyles' => array (),			// Array of sub styles for certain modes. Example: In 'list-level-style-*' modes, this contains 10 styles for the different list-levels, indexed by its level
			'family' => string,					// Family name of this style, eg. "paragraph" or "heading".
			'level' => integer,					// Level, used in certain style types like headings
			'properties' => array ();			// The styles properties. Example: 'FO:COLOR' => 'red', 'BORDER-LINE-WIDTH' => '12px 5px 5px' ...

	*/
	var $stylesArr = array ();
	var $mergedStylesArr = array ();			// Evaluated styles array, don't use this directly but call function getMergedStyles () instead
	var $mergedStylesMD5 = -1;					// Used internally.

	var $validStyleFamilies = 'text,paragraph,section,table,table-column,table-row,table-cell,table-page,default';
	var $defaultStyleFamily = 'paragraph';

	var $variablesArr = array (					// These variables are declared in an office document and set / get from there
		'sequence' => array (),
		'simple' => array (),
		'user' => array (),
	);

	var $pageBreakConf = array (				// Dummy configuration for the page break iterator: When to perform a pagebreak.
		'classes' => 'rlmp_officelib_officetextcomponent'
	);
	var $tocConf = array (					// Dummy configuration for the page break iterator: When to add a TOC entry
		'classes' => 'rlmp_officelib_officetextcomponent'
	);

	var $conditionTags = null;					// Condition tags / values are used internally while rendering the composite objects. Set by render(). Don't set from outside!
	var $numberOfPages = 0;						// Set by performPageBreaks()
	var $toc = array ();						// Set by the officepagebreakiterator: Contains page titles, levels etc. indexed by page number

	var $hyperlinkObjects = array ();			// This array contains a list of special HREFs (key) and a reference to their target object. In order to render a correct href (with the right
												// page number etc.) the values of this array must be evaluated by the render  engine, which actually knows about the right URIs.
												// All illustrations, tables, thus linkeable objects will be in here, even if they have no referring link!

	var $metaObj = null;						// An object containing meta information about the document, like author, creation date etc.

	var $objRenderEngine = null;				// Instantiated class which is used to render this document. May be set from outside through "setRenderEngine()" for rendering this document in different ways.
	var $errorMessages = array ();				// If an error occured, this array contains the error messages

	/**
	 * The (still empty) constructor
	 *
	 * @return	void
	 */
	function rlmp_officelib_officedocument () {
	}

	/**
	 * Default method for initialising the document. Usually used for creating default text composites etc. in the
	 * concrete implementation
	 *
	 * @return	void
	 */
	function init () {
		$this->documentTitle = 'Untitled';
	}

	/**
	 * Dummy method for loading a document
	 *
	 * @return	void
	 */
	function load () {

	}

	/**
	 * Dummy method for saving a document
	 *
	 * @param	string
	 * @return	void
	 */
	function save () {

	}

	/**
	 * The default render function: Tells a certain textComposite object to render itself. Default is the 'body' text composite
	 *
	 * @param	string		$compositeName: The text composite's name. Usually something like 'body'
	 * @param	array		$condition: Pair of 'tag' => 'value'. The objects to be rendered may be "tagged" by some function defining a certain collection. If you set a tag (fx. 'page') and a value (fx. '2') only those objects with this combinition of tag / value will be rendered.
	 * @return	mixed		some rendered output
	 */
	function render ($compositeName='body', $conditions='') {
		$debugCode = '';
		if ($this->debug) {
			$debugCode = '<div id="tx_rlmpofficelib_officedocument_debuglayer" style="padding: 2px; width:auto; height: auto; font-size:10px; background-color:yellow; border: 1px solid black; text-color:black; position: absolute; visibility: hidden; "><pre style="height:auto; width:auto;" id="tx_rlmpofficelib_officedocument_debuglayerpre">&nbsp;</pre></div>';
			$GLOBALS['TSFE']->additionalHeaderData['tx_rlmpofficelib_officedocument_debug'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('rlmp_officelib').'debug.js'.'"></script>';
		}

		$this->conditionTags = (is_array ($conditions)) ? $conditions : '';

		if (is_callable(array ($this->textComposites[$compositeName],'render'))) {
			return $debugCode.$this->textComposites[$compositeName]->render();
		} else {
			return null;
		}
	}

	/**
	 * This function selects the right render method for the given object and calls it.
	 *
	 * @param	object		$objToBeRendered: The object to be rendered. The correct render function will be determined by the object's classname
	 * @param	mixed		Some pre-rendered content, fx. used by the table composite object to pass the pre-rendered content from its children (rows, cells)
	 * @return	mixed		The render function's output
	 * @access public
	 */
	function callRenderFunction ($objToBeRendered, $preRendered='')  {
		if (is_callable(array ($this->objRenderEngine,'render'))) {
			return call_user_func(array ($this->objRenderEngine,'render'), $objToBeRendered, $preRendered);
		}
		return null;
	}

	/**
	 * Sets the render engine, i.e. the class which will be used for rendering this document object.
	 * You'll have to pass an instantiated object
	 *
	 * @param	string/object		$renderClass: Name of the render enginge class or an instantiated object
	 * @return	void
	 * @access public
	 */
	function setRenderEngine (&$renderEngingeObj)  {
		if (is_object ($renderEngingeObj)) {
			$this->objRenderEngine =& $renderEngingeObj;
		}
	}

	/**
	 * Returns a certain text composite. Default is the 'body' text composite.
	 *
	 * @param	string		Name of the text composite
	 * @return	object		Reference to the text composite object if it exists.
	 */
	function getTextComposite ($compositeName) {
		return $this->textComposites[$compositeName];
	}

	/**
	 * Returns the table of content array. This array must be filled by calling performPageBreaks() first and will
	 * contain arrays with "sectionHeader"s and "level"s indexed by pagenumbers.
	 *
	 * @param	string		$compositeName: The text composite's name. Usually something like 'body'
	 * @return	array		The table of content
	 * @access public
	 */
	function getTOC ($compositeName='body') {
		return $this->toc[$compositeName];
	}

	/**
	 * Traverses the text composite objects in order to perform page breaks by using certain criterias defined in
	 * the pageBreakConf array. The result will be some "condition tags" in each object, defining to which page it belongs.
	 * Additionally the table of content will be created and saved into $this->toc
	 *
	 * @param	string		$compositeName: The text composite's name. Usually something like 'body'
	 * @return	void
	 * @access public
	 */
	function performPageBreaks ($compositeName='body') {
		if (is_object ($this->textComposites[$compositeName])) {
			$iterator =& $this->textComposites[$compositeName]->createIterator('rlmp_officelib_officepagebreakiterator');
			$iterator->setConf (array ('pageBreaks' => $this->pageBreakConf[$compositeName], 'tocEntries' => $this->tocConf[$compositeName]));
			$iterator->doProcessing();
			$this->numberOfPages = $iterator->pageNumber;
			if (!is_array ($this->toc[$compositeName])) { $this->toc[$compositeName] = array (); }
			if (is_array ($iterator->toc)) {
				$this->toc[$compositeName] =t3lib_div::array_merge_recursive_overrule ($this->toc[$compositeName],  $iterator->toc);
			}
		}
	}

	/**
	 * Returns an evaluated styles array with solved inheritance of parent styles.
	 * The result of this function will be cached internally.
	 *
	 * @return	array		The merged styles array
	 * @access public
	 */
	function getMergedStyles () {		
		if ($this->mergedStylesMD5 != md5 (serialize($this->mergedStylesArr))) {
			if (is_array ($this->stylesArr)) {
				$this->mergedStylesArr = $this->stylesArr;
				foreach ($this->stylesArr as $styleName => $styleArr) {
					$this->mergeStyles ($styleName, $styleArr);
				}
			}
			$this->mergedStylesMD5 = md5 (serialize($this->mergedStylesArr));
		}
		return $this->mergedStylesArr;
	}


	/**
	 * Recalculates style inheritance. The merged styles will be saved into
	 * $this->mergedStylesArr. Always use getMergedStyles to access this array.
	 *
	 * @param	array		$styleName: ...
	 * @param	array		$styleArr: ...
	 * @return	array
	 * @access private
	 */
	function mergeStyles ($styleName, $styleArr) {
		if (is_array ($this->mergedStylesArr[$styleArr['parentStyleName']])) {
			$parentStyle = $this->mergeStyles($styleArr['parentStyleName'], $this->mergedStylesArr[$styleArr['parentStyleName']]);
			$styleArr['properties'] = t3lib_div::array_merge_recursive_overrule ($styleArr['properties'], $parentStyle['properties']);
			$this->mergedStylesArr[$styleName] = $styleArr;
			return $styleArr;
		} else {
			return $styleArr;
		}
	}

}

?>