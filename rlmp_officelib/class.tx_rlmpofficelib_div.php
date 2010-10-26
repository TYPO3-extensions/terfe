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
 * Various helper functions for usage in the different document parsers
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_div {

	var $dpi = 96;

	/**
	 * This function returns a globally unique instance of this class (-> Singleton). Always use this
	 * function in order to create / get an instance of this class, don't instantiate it yourself.
	 *
	 * @return	object	An instance of this class
	 * @access	public
	 */
	function &getInstance()  {
		static $instance;
		if (!isset ($instance)) {
			$instance = t3lib_div::makeInstance ('rlmp_officelib_div');
		}
		return $instance;
	}

	/**
	 * Processes the XML structure for open tags and 'indents' them in the array
	 *
	 * @param	array	$xmlArrBody:	The "body" section of the document's arrayed XML structure
	 * @return	array	The modified XML structure
	 */
	function indentSubTags($xmlArrBody)	{
		$newStruct=array();
		$subStruct=array();
		$currentTag='';
		$currentLevel=0;
		reset($xmlArrBody);
		while(list($k,$v)=each($xmlArrBody))	{
			if ($currentTag)	{
				if (!strcmp($v['tag'],$currentTag))	{	// match...
					if ($v['type']=='close')	$currentLevel--;
					if ($v['type']=='open')		$currentLevel++;
				}
				if ($currentLevel<=0)	{	// should never be LESS than 0, but for safety...
					$currentTag='';
					$subStruct['type']='complete';
					$newStruct[]=$subStruct;
				} else {
					$subStruct['subTags'][]=$v;
				}
			} else {	// On root level:
				if (t3lib_div::inList('complete,cdata',$v['type']))	{
					$newStruct[]=$v;
				}
				if ($v['type']=='open')	{
					$currentLevel=1;
					$currentTag = $v['tag'];

					$subStruct=$v;
					$subStruct['subTags']=array();
				}
			}
		}
		return $newStruct;
	}

	/**
	 * Indents open tags and does so recursively to a certain number of levels
	 *
	 * @param	array		$xmlArrBody:	The "body" section of the document's arrayed XML structure
	 * @param	integer		$depth: recursive depth
	 * @return	array		indented XML array
	 */
	function indentSubTagsRec($xmlArrBody, $depth=1)	{
		if ($depth<1)	return $xmlArrBody;
		$xmlArrBody = $this->indentSubTags($xmlArrBody);
		if ($depth>1)	{
			reset($xmlArrBody);
			while(list($k,$v)=each($xmlArrBody))	{
				if (is_array($xmlArrBody[$k]['subTags']))	{
					$xmlArrBody[$k]['subTags'] = $this->indentSubTagsRec($xmlArrBody[$k]['subTags'],$depth-1);
				}
			}
		}
		return $xmlArrBody;
	}

	/**
	 * Converts centimeters to pixels.
	 * 1in = 2.54cm
	 *
	 * @param	string		$value: The original value. Examples: "2.345cm", "3.4in"
	 * @return	string		converted value with trailing "px"
	 */
	function convertToPixels ($str) {
		if (strpos($str, 'cm')) {
			return ceil(intval ($str) / 2.54 * $this->dpi);
		} elseif (strpos($str, 'in')) {
			return ceil(intval ($str) * $this->dpi);
		}
		return 0;
	}
}
?>