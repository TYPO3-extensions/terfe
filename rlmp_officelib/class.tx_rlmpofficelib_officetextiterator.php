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
 * Office general purpose *internal* iterator class. Provides methods for traversing
 * the text composites / components objects tree structure and do some processing
 * if a certain criteria is met.
 *
 * Use this class as a template for your own specialized iterator.
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_officetextiterator extends rlmp_officelib_officenulliterator {

	var $parentObj = null;
	var $resultObj = null;
	var $previousObj = null;
	var $conf = array ();

	function rlmp_officelib_officetextiterator (&$parentObj) {
		$this->parentObj =& $parentObj;
	}

	function setConf ($conf) {
		$this->conf = $conf;
	}

	function doProcessing () {
		$parentObjClass = get_class($this->parentObj);
		$this->resultObj = new $parentObjClass ($this->parentObj->parentDocObj);
		$this->processTree ($this->parentObj);

	}

	function getResult () {
		return $this->resultObj;
	}

	function processTree (&$obj) {
		$this->processObject($obj);
		$this->previousObj =& $obj;
		if ($obj->hasChildren()) {
			foreach (array_keys ($obj->children) as $key) {
				$this->processTree ($obj->children[$key]);
				$this->previousObj =& $obj->children[$key];
			}
		}
	}

	function processObject (&$obj) {
		if ($this->evaluateCriteria($obj)) {
			$this->resultObj->add ($obj);
		}
	}

	function evaluateCriteria (&$obj, $confArr='') {
		$ok = true;
		if (!is_array ($confArr)) {
			$confArr = $this->conf;
		}
		if (is_array ($confArr['eval'])) {
			foreach ($confArr['eval'] as $condition) {
				$ok = $ok && (eval('return ('.$condition.');'));
			}
		}
		return $ok;
	}
}

?>