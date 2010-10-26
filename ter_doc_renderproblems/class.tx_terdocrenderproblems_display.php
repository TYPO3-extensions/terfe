<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2006 Robert Lemke (robert@typo3.org)
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
 * Displays detailed information about why a document could not be rendered
 *
 * $Id: class.tx_terdocrenderproblems_display.php 4351 2006-12-15 17:27:29Z robert_typo3 $
 *
 * @author	Robert Lemke <robert@typo3.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */

require_once (t3lib_extMgm::extPath('ter_doc').'class.tx_terdoc_api.php');
require_once (t3lib_extMgm::extPath('ter_doc').'class.tx_terdoc_documentformat.php');

class tx_terdocrenderproblems_display extends tx_terdoc_documentformat_display {

	/**
	 * Dummy function - nothing to render for this output type
	 * 
	 * @param	string		$documentDir: Absolute directory for the document currently being processed.
	 * @return	void
	 */
	public function renderCache ($documentDir) {
	}

	/**
	 * Renders the list of problems which occurred during the render process of a 
	 * document.
	 *
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @param	object		$pObj: Reference to the calling object (must be a pi_base child). Used for creating links etc.
	 * @return	string
	 * @access	public
	 */
	public function renderDisplay ($extensionKey, $version, &$pObj) {
		global $TSFE, $TYPO3_DB;

		$errorList = array();
		$renderTime = 'some unknown date';
		$res = $TYPO3_DB->exec_SELECTquery(
			'tstamp,errorcode',
			'tx_terdoc_renderproblems',
			'extensionkey=' . $TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terdoc_renderproblems') . ' AND ' . 
				'version=' . $TYPO3_DB->fullQuoteStr($version, 'tx_terdoc_renderproblems')
		);
		
		while ($renderProblemRow = $TYPO3_DB->sql_fetch_assoc($res)) {
			$errorList[] = '
				<li>
					<p><strong>' . $pObj->pi_getLL('api_error_renderproblems_' . $renderProblemRow['errorcode'], 'Unknown error.' , 1) . '</strong></p>
					<p>' . $pObj->pi_getLL('api_error_renderproblemsolutions_' . $renderProblemRow['errorcode'], '' , 1) . '</p>
				</li>
			';
			$renderTime = strftime('%d.%m.%Y %R %Z', $renderProblemRow['tstamp']);
		}
		$mailingListLink = '<a href="http://lists.netfielders.de/cgi-bin/mailman/listinfo/typo3-project-documentation">Documentation Team mailing list</a>';
		
		$output = '
			<h2>'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_doc_renderproblems/locallang.xml:display_title')).'</h2>
			<p>'.htmlspecialchars(sprintf($TSFE->sL('LLL:EXT:ter_doc_renderproblems/locallang.xml:display_introduction'), $extensionKey, $version)).'</p>
			' . (count($errorList) ? '<ol>' . implode('', $errorList) . '</ol>' : 'No problems found.') . '
			<p>'.sprintf(htmlspecialchars($TSFE->sL('LLL:EXT:ter_doc_renderproblems/locallang.xml:display_footer')), $renderTime, $mailingListLink).'</p>
		';

		return $output;
	}
	
	/**
	 * Returns TRUE if error reports are available for the document, otherwise FALSE
	 * 
	 * @param	string		$extensionKey: Extension key of the document
	 * @param	string		$version: Version number of the document
	 * @return	boolean		TRUE if an error report is available, otherwise FALSE
	 */
	public function isAvailable ($extensionKey, $version) {
		global $TYPO3_DB;
		
		$res = $TYPO3_DB->exec_SELECTquery(
			'uid',
			'tx_terdoc_renderproblems',
			'extensionkey=' . $TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terdoc_renderproblems') . ' AND ' . 
				'version=' . $TYPO3_DB->fullQuoteStr($version, 'tx_terdoc_renderproblems')
		);
		$problemCount = $TYPO3_DB->sql_num_rows($res);	
		return ($problemCount == 0) ? FALSE : TRUE;
	}
}
?>