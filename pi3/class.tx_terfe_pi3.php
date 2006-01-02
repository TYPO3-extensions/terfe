<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Robert Lemke (robert@typo3.org)
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
 * Plugin "Review Frontent" for the 'ter_fe' extension.
 *
 * $Id$
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @author	Michael Scharkow <michael@underused.org>
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

if (t3lib_extMgm::isLoaded ('ter_doc')) {
	require_once (t3lib_extMgm::extPath('ter_doc').'class.tx_terdoc_api.php');			
}

require_once (t3lib_extMgm::extPath('ter_fe').'pi1/class.tx_terfe_pi1.php'); 

class tx_terfe_pi3 extends tx_terfe_pi1 {

	public		$prefixId = 'tx_terfe_pi3';											// Same as class name
	public		$scriptRelPath = 'pi3/class.tx_terfe_pi3.php';						// Path to this script relative to the extension dir.
	public		$extKey = 'ter_fe';													// The extension key.
	public		$pi_checkCHash = TRUE;												// Handle empty CHashes correctly
	
	protected	$repositoryDir = '';												// Full path to the extension repository files
	protected	$baseDirT3XContentCache = '';										// Full path to T3X content cache
	protected	$viewMode = '';														// View mode, one of the following: LATEST, CATEGORIES, FULLLIST
	protected	$WSDLURI;
	protected	$SOAPServiceURI;
	
	protected	$validStates = 'alpha,beta,stable,experimental,test,obsolete';		// List of valid development states
	protected	$validReviewStates = array(-1,1);									// List of valid review states
	protected	$validObjections = array('Cross-site scripting','SQL-injection','Not CGL-compliant','Changed MD5', 'other security flaws');
	protected	$minReviews = 2;
	protected	$maxReviews = 2;
	

	/**
	 * Initializes the plugin, only called from main()
	 * 
	 * @param	array	$conf: The plugin configuration array
	 * @return	void
	 * @access	protected
	 */
	protected function init($conf) {
		global $TSFE;
		
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();			// Set default piVars from TS
		$this->pi_initPIflexForm();				// Init FlexForm configuration for plugin
		$this->pi_loadLL();
		
		$this->repositoryDir = $this->cObj->stdWrap ($this->conf['repositoryDirectory'], ($this->conf['repositoryDirectory.']));
		if (substr ($this->repositoryDir, -1, 1) != '/') $this->repositoryDir .= '/';

		$staticConfArr = unserialize ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_fe']);
		if (is_array ($staticConfArr)) {
			$this->WSDLURI = $staticConfArr['WSDLURI'];
			$this->SOAPServiceURI = $staticConfArr['SOAPServiceURI'];
		}
		
		$this->baseDirT3XContentCache = PATH_site.'typo3temp/tx_terfe/t3xcontentcache/';
	}
	
	/**
	 * The plugin's main function
	 * 
	 * @param	string	$content: Content rendered so far (not used)
	 * @param	array	$conf: The plugin configuration array
	 * @return	string	The plugin's HTML output
	 * @access	public
	 */
	public function main($content,$conf)	{
		global $TSFE;
		
		$this->init($conf);

		if (!@is_dir ($this->repositoryDir)) return 'TER_FE Error: Repository directory ('.$this->repositoryDir.') does not exist!';			
		if ($this->extensionIndex_wasModified ()) {
			$this->extensionIndex_updateDB ();	
		}

			// Prepare the top menu items:
		$menuItems = array ('unreviewed','passed','insecure','pending');

			// Render the top menu		
		$topMenu = '';
		foreach ($menuItems as $itemKey) {
			$itemActive = ($this->piVars['view'] == $itemKey);
			$link = $this->pi_linkTP($this->pi_getLL('views_'.$itemKey,'',1), array('tx_terfe_pi3[view]' => $itemKey), 1);
			$topMenu .='<span '.($itemActive ? 'class="submenu-button-active"' :'class="submenu-button"').'>'.$link.'</span>';
		}


		// if we are in single view mode
		if ($this->piVars['extensionkey']) {
			$this->extensionKey = $this->piVars['extensionkey'];
			$this->piVars['version'] = trim($this->piVars['version']) ? trim($this->piVars['version']) : $this->db_getLatestVersionNumberOfExtension ($this->version);
			$this->version = $this->piVars['version'];
			$this->reviewer = $TSFE->fe_user->user;
			$this->reviewable = $this->is_reviewable($this->extensionKey,$this->version);
			
			if ($this->reviewable == 1 && $this->piVars['cmd'] == 'startreview'){
				if ($this->db_createReviewRecord($this->extensionKey, $this->version, $this->reviewer['username'])){
					$this->reviewable = 2;
				} else {
					$this->errorMessage = "Could not create initial review";
				}
			}
			
			if ($this->reviewable == 2 && $this->piVars['cmd'] == 'savereview'){ 
					$this->reviewable = ($this->db_saveReviewRecord($this->piVars) ? False : 2);
			}
			 
			$this->extensionInfo = $this->db_getExtensionRecord($this->extensionKey, $this->version);
			$this->extensionReviews = $this->db_getReviewRecords ($this->extensionKey, $this->version);
			$this->otherExtensionReviews = $this->db_getOtherReviewRecords ($this->extensionKey, $this->version);
			
			if (is_array($this->extensionInfo)){
				$subContent = '<table>'.$this->renderSingleView_review_extensionInfo($this->db_prepareExtensionRowForOutput($this->extensionInfo)).'</table>';
			}

			if ($this->extensionReviews){
				$subContent .= '<h3>'.$this->pi_getLL('singleview_review_header').'</h3>';
				$subContent .= $this->renderSingleView_review_reviewInfo($this->extensionReviews);
			}
			
			if ($this->reviewable == 2){
				$subContent .= '<h3>'.$this->pi_getLL('singleview_review_startreview').'</h3>';
				$subContent .= $this->renderReviewForm();
				
			}  
			
			if ($this->reviewable == 1){
				$subContent .= '<h3>'.$this->pi_getLL('singleview_review_startreview').'</h3>';
				$subContent .= $this->renderStartReviewButton();
			}
			
			if ($this->otherExtensionReviews){
				$subContent .= '<h3>'.$this->pi_getLL('singleview_review_history').'</h3>';
				$subContent .= $this->renderSingleView_review_reviewInfo($this->otherExtensionReviews);
			}
			
	
			// else we are in list mode	
	
		} else {
			switch ($this->piVars['view']) {
				case 'unreviewed':	$subContent = $this->renderListView('unreviewed'); break;
				case 'passed':		$subContent = $this->renderListView('passed'); break;
				case 'insecure':	$subContent = $this->renderListView('insecure'); break;
				case 'pending':		$subContent = $this->renderListView_reviewList($this->db_getUsersReviewRecords($TSFE->fe_user->user['username'],1)); break;
				default:		$subContent = $this->renderSingleView_selectExtensionVersion(); break;
				//default: $subContent = $this->renderListView('unreviewed'); break;

			}
		}
		
			// Put everything together:
		$content = '
			<h2>'.$this->pi_getLL('general_extensionreview', '', 1).'</h2>
			<div id="topmenu">'.$topMenu.'</div>';
		if ($this->errorMessage){
			$content .= '<div class="error" style="padding: 1em;border: 2px solid red; text-align: center; margin: 1em;">'.$this->errorMessage.'</div>';
		}
			$content .= $subContent;

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Checks whether an extension version can be reviewed
	 * 
	 * @param	string		$extensionkey: Extension key
	 * @param	string		$version: Extension version
	 * @return	id			$reviewStage: 1 if review not yet started, 2 if already started
	 * @access	protected
	 */
	protected function is_reviewable($extensionkey, $version){

		$extensionInfo = $this->db_getExtensionRecord($extensionkey, $version);
		// check for existing extension and review state
		
		if (!$extensionInfo){
			$this->errorMessage = $this->pi_getLL('error_extnotfound');
			return False;
		}

		// this must be changed for cascaded reviews
		if ($extensionInfo['reviewstate'] != 0){
	 		  $this->errorMessage = $this->pi_getLL('error_alreadyreviewed');
			return False;
		}

		$reviewRow = $this->db_getReviewRecords($extensionkey, $version);
		if (is_array($reviewRow)){
		/*	$this->numReviews = sizeof($reviewRow);
			if ($this->numReviews >= $this->maxReviews ){
				return False;
			}
		 */
			foreach ($reviewRow as $review){
				if ($review['reviewer'] == $this->reviewer['username'] && $review['reviewstate'] == 0){
					return	2;
				}
			}
		} 
		return 1;
	}



	/**
	 * Renders a list of extensions with a certain review status 
	 * 
	 * @param	string		$mode: "unreviewed", "passed" or "insecure"
	 * @return	string		HTML output
	 * @access	protected
	 */

	protected function renderListView($mode) {
		global $TYPO3_DB, $TSFE;

		$tableRows = array ();	

		switch ($mode) {
			case 'unreviewed' : $reviewStateClause = 'reviewstate = 0'; break;
			case 'passed' :		$reviewStateClause = 'reviewstate >= 1'; break;
			case 'insecure' :	$reviewStateClause = 'reviewstate < 0'; break;
		}

		$res = $TYPO3_DB->exec_SELECTquery (
			'extensionkey,title,version,authorname,authoremail,ownerusername',
			'tx_terfe_extensions',
			'state <> "obsolete" AND '.$reviewStateClause,
			'',
			'lastuploaddate DESC, version DESC',
			''
		);
		$alreadyRenderedExtensionKeys = array();

		if ($res) {

					// Set the magic "reg1" so we can clear the cache for this overview if a new extension is uploaded:		
			if (t3lib_extMgm::isLoaded ('ter_doc')) {
				$terDocAPIObj = tx_terdoc_api::getInstance();
				$TSFE->page_cache_reg1 = $terDocAPIObj->createAndGetCacheUidForExtensionVersion ('_all','');
			}

			while ($extensionRow = $TYPO3_DB->sql_fetch_assoc ($res)) {
				if (!t3lib_div::inArray ($alreadyRenderedExtensionKeys, $extensionRow['extensionkey'])) {
					$tableRows[] = $this->renderListView_shortExtensionRow ($extensionRow);
					$alreadyRenderedExtensionKeys[] = $extensionRow['extensionkey'];
				}
			}
		}

		$content.= '
			<p>'.$this->pi_getLL('listview_'.$mode.'_introduction','',1).'</p>
			<table style="margin-top:10px;">
				<th class="th-main">&nbsp;</th>
				<th class="th-main">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_title')).'</th>
				<th class="th-main">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_extensionkey')).'</th>
				<th class="th-main">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_version')).'</th>
				<th class="th-main">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_documentation')).'</th>
				<th class="th-main">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_authorname')).'</th>
			'.implode('', $tableRows).'
			</table>
		';
		return $content;
	}

	/**
	 * Render a short extension info row for the full listing of extensions
	 *
	 * @param	array		$extRow: Database record from tx_terfe_extensions
	 * @return	string		Two HTML table rows wrapped in <tr>
	 */
	protected function renderListView_shortExtensionRow($extRow)	{
		global $TSFE;

		if (t3lib_extMgm::isLoaded ('ter_doc')) {			
			$terDocAPIObj = tx_terdoc_api::getInstance();
			$documentationLink = $terDocAPIObj->getDocumentationLink ($extRow['extensionkey'], $extRow['version']);
		} else {		
			$documentationLink = '';
		} 

		$extRow = $this->db_prepareExtensionRowForOutput ($extRow);
				
		$tableRows = '
			<tr>
				<td class="td-sub">'.$this->getIcon_extension ($extRow['extensionkey'], $extRow['version']).'</td>
				<td class="td-sub" nowrap="nowrap">'.$this->pi_linkTP_keepPIvars($extRow['title'], array('view' => 'review', 'extensionkey' => $extRow['extensionkey'], 'version' => $extRow['version']),1).'</td>
				<td class="td-sub">'.$extRow['extensionkey'].'</td>
				<td class="td-sub">'.$extRow['version'].'</td>
				<td class="td-sub" nowrap="nowrap">'.$documentationLink.'</td>
				<td class="td-sub">'.$extRow['ownerusernameandname'].'</td>
			</tr>
		';

		return $tableRows;
	}

	/**
	 * Renders a link list of review records.
	 * 
	 * @param	array	$reviewRows: Array of review records
	 * @return	string		HTML output
	 * @access	protected
	 */

	protected function renderListView_ReviewList($reviewRows){
		if (is_array($reviewRows)){
			$content = '<div class="reviewlist"><ul>';
			foreach ($reviewRows as $extRow){
				$content .= '<li>'.$this->pi_linkTP_keepPIvars($extRow['extensionkey'].' ('.$extRow['version'].')', 
														array('view' => 'review', 'extensionkey' => $extRow['extensionkey'], 'version' => $extRow['version']),
														1).' </li>';
					
			}
			$content .= '</ul></div>';
			return $content;
		}
	}


	/**
	 * Renders a form for selecting the extension key and version number for a review.
	 * 
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderSingleView_selectExtensionVersion() {
		global $TSFE;
		
		$output = '
			<p>'.$this->pi_getLL('singleview_selectextensionversion_pleaseselect','',1).'</p>
			<br />
			<form action="'.$this->pi_getPageLink($TSFE->id).'" method="get">
				<input type="hidden" name="tx_terfe_pi3[view]" value="review" />
				<input type="text" name="tx_terfe_pi3[extensionkey]" size="20" value="extensionkey" onfocus="fieldextensionkey.value=\'\'" id="fieldextensionkey" />
				<input type="text" name="tx_terfe_pi3[version]" size="12" value="1.0.0" onfocus="fieldversion.value=\'\'" id="fieldversion" />
				<input type="submit" value="'.$this->pi_getLL('singleview_selectextensionversion_submit','',1).'" />
			</form>
		';
		
		return $output;		
	}

	/**
	 * Renders the main view for a review of an extension
	 * 
	 * @return	array	Extension Information Row
	 * @access	protected
	 */
	protected function db_getExtensionRecord($extensionKey,$version) {
		global $TYPO3_DB;
			// Fetch the extension record:
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			'tx_terfe_extensions',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensions').' AND '.
			'version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_extensions')
		);
		if (!$res) return False;
		
		if ($resRow = $TYPO3_DB->sql_fetch_assoc ($res)){
			return $resRow;
		} else {
			return False;
		}
	}

	/**
	 * Renders a section with extension details for the review view.
	 * 
	 * @param	array		$extRow: Row of the extension record
	 * @return	string		HTML output
	 */
	protected function renderSingleView_review_extensionInfo($extRow) {
		global $TSFE;

		if (t3lib_extMgm::isLoaded ('ter_doc')) {			
			if (t3lib_extMgm::isLoaded ('ter_doc_html')) {
				$terDocAPIObj = tx_terdoc_api::getInstance();
				$documentationLink = $terDocAPIObj->getDocumentationLink ($extRow['extensionkey'], $extRow['version']);

					// Set the magic "reg1" so we can clear the cache for this manual if a new one is uploaded:		
				$terDocAPIObj = tx_terdoc_api::getInstance();
				$TSFE->page_cache_reg1 = $terDocAPIObj->createAndGetCacheUidForExtensionVersion ($extRow['extensionkey'], $extRow['version']);
				
			} else {
				$documentationLink = $terDocAPIObj->getDocumentationLink ($extRow['extensionkey'], $extRow['version']);
			}
		} 

			// Prepare options for version selectorbox:
		$optionValuesArr = $this->db_getAllVersionNumbersOfExtension ($extRow['extensionkey']);
		$versionOptions = '';
		foreach ($optionValuesArr as $optionValue) {	
			$versionOptions .= '<option value="'.$optionValue.'" '.($optionValue == $extRow['version'] ? 'selected="selected"' : '').'>'.$optionValue.'</option>'.chr(10);
		}

			// Build the output
		$content ='
				<tr>
					<th class="th-sub" colspan="3">'.sprintf ($this->pi_getLL('singleview_review_extensioninfo_sectionheading','',1), $extRow['title']).'</th>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_extensionkey')).':</th>
					<td class="td-sub" style="width:90%;"><em>'.$extRow['extensionkey'].'</em></td>
					<td class="td-sub" rowspan="4">
						<table>
							<tr><th nowrap="nowrap" class="th-sub">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_state')).':</th><td>'.$this->getIcon_state($extRow['state_raw']).'</td></tr>
							<tr>
								<th nowrap="nowrap" class="th-sub">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_version')).':</th>
								<td>
									<form name="versionselector" action="'.t3lib_div::getIndpEnv('REQUEST_URI').'" method="POST">
										<select name="tx_terfe_pi3[version]" onChange="versionselector.submit()">'.$versionOptions.'</select>
									</form>									
								</td>
							</tr>
							<tr><th nowrap="nowrap" class="th-sub">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_category')).':</th><td>'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_category_'.$extRow['category'])).'</td></tr>
							<tr><th nowrap="nowrap" class="th-sub">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_lastuploaddate')).':</th><td>'.$extRow['lastuploaddate'].'</td></tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_description')).':</th>
					<td class="td-sub">'.$extRow['description'].'</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_ownerusername')).':</th>
					<td class="td-sub">'.$extRow['ownerusernameandname'].'</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_documentation')).':</th>
					<td class="td-sub" valign="top">'.$documentationLink.'</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_dependencies')).':</th>
					<td class="td-sub" valign="top">'.$this->getRenderedDependencies ($extRow['dependencies']).'</td>
					<td class="td-sub" valign="top">'.$this->getRenderedReverseDependencies ($extRow['extensionkey'], $extRow['version']).'</td>
				</tr>
		';
		
		return $content;
	
	}

	
	/**
	 * Renders a section with information about reviews for the given extension
	 * 
	 * @param	array		$reviewRows: Review data records
	 * @return	string		HTML output
	 */
	protected function renderSingleView_review_reviewInfo($reviewRows) {
		
		if (is_array($reviewRows)) {
			$content = '<table><tr><th>Status</th><th>Version</th><th>Complaints</th><th>Notes</th><th>Reviewer</th></tr>';
			$odd_or_even = 1;
			foreach ($reviewRows as $review){
				$odd_or_even = 1 - $odd_or_even;
				$content .= $this->renderReviewRow($review,$odd_or_even);
			}
			$content .= '</table>';
		} else {
			$content = 'No reviews yet';
		}
		return $content;
	}
	
	/**
	 * Renders one table row for a review record
	 * 
	 * @param	array		$reviewRow: Review data record
	 * @param	boolean		$even: True if we are in an even row (hack for zebra tables)
	 * @return	string		HTML output
	 */
	function renderReviewRow($reviewRow,$even){
		if (intval($reviewRow['reviewstate']) >= 1){
			$reviewRow['status'] = 'PASSED';
		} else if (intval($reviewRow['reviewstate']) == -1){
			$reviewRow['status'] = 'REJECTED';
		} else {
			$reviewRow['status'] = "PENDING";
		}	

		
		$template = '<tr '.($even ? 'class="even"' : '') .'><td>###STATUS###</td><td>###VERSION###</td><td>###OBJECTIONS###</td><td>###NOTES###</td><td>###REVIEWER###</td></tr>';
		$markerArray = $reviewRow;
		$output = $this->cObj->substituteMarkerArray($template,$markerArray,'###|###',1);
		return $output;
	}


	protected function renderStartReviewButton(){
		$content .= '	<form action="'.t3lib_div::getIndpEnv('REQUEST_URI').'" method="POST">
						<input type="hidden" value="startreview" name="tx_terfe_pi3[cmd]"/>
						<input type="hidden" value="'.$this->extensionKey.'" name="tx_terfe_pi3[extensionkey]"/>
						<input type="hidden" value="'.$this->version.'" name="tx_terfe_pi3[version]"/>
						<input type="submit" value="'.$this->pi_getLL('singleview_review_startreview').'"/>';

		return $content;
	}
		
	
	/**
	 * Renders a section with a review form
	 * 
	 * @return	string		HTML output
	 */

	protected function renderReviewForm() {
			
			$content .= '	<form action="'.t3lib_div::getIndpEnv('REQUEST_URI').'" method="POST">
							<fieldset><legend>Security Review</legend>
							<p>
							<label for="reviewstate">Status</label>
							<input type="radio" value="1" id="reviewstate" name="tx_terfe_pi3[reviewstate]"/>Accepted
							<input type="radio" value="-1" id="reviewstate" name="tx_terfe_pi3[reviewstate]"/>Rejected
							</p></p>
							<label for="objections">Objections</label>';
			
			foreach ($this->validObjections as $complaint){
				$content .= '<input type="checkbox" value="'.$complaint.'"';
				if ($this->piVars['objections'] && in_array($complaint,$this->piVars['objections'])){
					$content .= 'checked="checked" ';
				}
				$content .= 'name="tx_terfe_pi3[objections][]" id="objections"/>'.$complaint;
			}
			
			$content .='	</p><p>
							<label for="notes">'.$this->pi_getLL('singleview_review_notes').'
							<textarea cols="60" rows="3" id="notes" name="tx_terfe_pi3[notes]">'.
							htmlspecialchars(trim($this->piVars['notes'])).
							'</textarea></p><p>
							<label for="forcestate">Immediately set review state in TER</label>
							<input type="checkbox" id="forcestate" name="tx_terfe_pi3[forcestate]" value="1"'.
							( $this->piVars['forcestate'] ? 'checked="checked"' : '').'
							/>
							<input type="hidden" value="savereview" name="tx_terfe_pi3[cmd]"/>
							<input type="hidden" value="'.$this->extensionKey.'" name="tx_terfe_pi3[extensionkey]"/>
							<input type="hidden" value="'.$this->version.'" name="tx_terfe_pi3[version]"/>
							<input type="submit" value="'.$this->pi_getLL('singleview_review_submitreview').'"/>
							</fieldset>';


		
		return $content;
	
	}

	/**
	 * Renders a section with review notes for the review of the given extension
	 *
	 * NOT USED AT THE MOMENT
	 * 
	 * @param	array		$extRow: Row of the extension record
	 * @return	string		HTML output
	 */
	protected function renderSingleView_review_reviewNotes($extRow) {
		global $TSFE;

		$output = '';
		$reviewRow = $this->db_getReviewRecord ($extRow['extensionkey'], $extRow['version']);
		if (is_array($reviewRow)) {			
			$reviewNotes = $this->db_getReviewNotes($reviewRow['uid']);
			$reviewRow = $this->db_prepareReviewRowForOutput($reviewRow);

			if (is_array ($reviewNotes)) {
				
					// Build the output:
				$output ='
					<tr>
						<th class="th-sub" colspan="3">'.$this->pi_getLL('singleview_review_reviewnotes_notesandhistory','',1).'</th>
					</tr>
				';
				foreach ($reviewNotes as $reviewNote) {
					$output .= '
						<tr>
							<td class="td-sub" colspan="3"><strong>'.$reviewNote['reviewer'].' '.$reviewNote['tstamp'].'</strong>: '.$reviewNote['note'].'</td>
						</tr>
					';
				}
			}
		}
		
		return $output;
	
	}






	/**
	 * Converts charsets and htmlspecialchars certain field of the given
	 * record from table tx_terfe_extensions so it can be displayed directly
	 * at the frontend.
	 *
	 * NOT USED AT THE MOMENT, BUT WILL BE SOON
	 * 
	 * @param	array		$extensionRow: One record from table tx_terfe_extensions
	 * @return	array		The modified record
	 * @access protected
	 */
	protected function db_prepareExtensionRowForOutput ($extensionRow) {
		if (is_array ($extensionRow)) {
			foreach ($extensionRow as $key => $value) {
				switch ($key) {
					case 'reviewstate':
						$extensionRow['reviewstate_raw'] = $value;
						$extensionRow[$key] = $this->pi_getLL('extension_reviewstate_'.$extensionRow[$key],'',1);
					break;
				}					
			}
			$extensionRow = parent::db_prepareExtensionRowForOutput ($extensionRow);												
		}
		return $extensionRow;
	}

	/**
	 * Converts charsets and htmlspecialchars certain field of the given
	 * record from table tx_terfe_reviews so it can be displayed directly
	 * at the frontend.
	 *
	 * NOT USED AT THE MOMENT
	 * 
	 * @param	array		$reviewRow: One record from table tx_terfe_reviews
	 * @return	array		The modified record
	 * @access protected
	 */
	protected function db_prepareReviewRowForOutput ($reviewRow) {
		if (is_array ($reviewRow)) {
			foreach ($reviewRow as $key => $value) {
				switch ($key) {
					case 'reviewer':
						$reviewersArr = t3lib_div::trimExplode (',', $value);
						if (is_array ($reviewersArr)) {
							$reviewRow[$key] = '';
							$reviewRow['reviewerswithname'] = '';
							foreach ($reviewersArr as $reviewerUsername) {
								$reviewRow[$key] .= $this->csConvHSC ($reviewerUsername).' ';
								$reviewRow['reviewerswithname'] .= $this->csConvHSC ($reviewerUsername .' ('.$this->db_getFullNameByUsername($reviewerUsername).') ');
							}
						}
					break;
				}					
			}
		}
		return $reviewRow;
	}
	
	/**
	 * Delivers all version numbers found for the given extension key.
	 * If no upload was found at all, FALSE will be returned.
	 *
	 * @param	string		$extKey: Extension key
	 * @return	mixed		The version numbers as an array or FALSE
	 * @access	public 
	 */
	protected function db_getAllVersionNumbersOfExtension ($extensionKey) {
		global $TYPO3_DB;
		
		$res = $TYPO3_DB->exec_SELECTquery (
			'version',
			'tx_terfe_extensions',
			'extensionkey="'.$TYPO3_DB->quoteStr($extensionKey, 'tx_terfe_extensions').'"'
		);
		$versionNumbers = FALSE;
		while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
			$versionNumbers[] = $row['version'];	
		}
		
		return $versionNumbers;	
	}
	
	/**
	 * Returns the all review records for the given extension except the given version. An additional
	 * field "_currentt3xfilemd5" will contain the current MD5 hash of the actual
	 * t3x file.
	 * 
	 * @param		string	$extensionKey: Extension key of the extension
	 * @param		string	$version: Version number of the extension
	 * @return		mixed	Review record or FALSE
	 * @access		protected
	 */

	protected function db_getOtherReviewRecords ($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;
		
		$table = 'tx_terfe_reviews';
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			$table,
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, $table).' AND version != '.$TYPO3_DB->fullQuoteStr($version, $table),
			'',
			'version DESC'
		);
		
		if ($res) {
			while ($row =  $TYPO3_DB->sql_fetch_assoc ($res)) {
				$review[] =  $row;
			}
			return $review;
		}
		return FALSE;
	}

	/**
	 * Returns the all pending review records for current FE user. An additional
	 * field "_currentt3xfilemd5" will contain the current MD5 hash of the actual
	 * t3x file.
	 * 
	 * @param		string	$username: Username of reviewer
	 * 
	 * @return		mixed	Review record or FALSE
	 * @access		protected
	 */

	protected function db_getUsersReviewRecords ($username,$pending = 0) {
		global $TYPO3_DB, $TSFE;
		
		$table = 'tx_terfe_reviews';
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			$table,
			'reviewer = '.$TYPO3_DB->fullQuoteStr($username,$table).($pending ? ' AND reviewstate = 0': ''),
			'',
			'tstamp ASC'
		);
		
		if ($res) {
			while ($row =  $TYPO3_DB->sql_fetch_assoc ($res)) {
				$review[] =  $row;
			}
			return $review;
		}
		return False;
	}

	
	/**
	 * Returns the review records for the given extension version. An additional
	 * field "_currentt3xfilemd5" will contain the current MD5 hash of the actual
	 * t3x file.
	 * 
	 * @param		string	$extensionKey: Extension key of the extension
	 * @param		string	$version: Version number of the extension
	 * @return		mixed	Review record or FALSE
	 * @access		protected
	 */

	protected function db_getReviewRecords ($extensionKey, $version,$reviewstate=0) {
		global $TYPO3_DB, $TSFE;
		
		$table = 'tx_terfe_reviews';
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			$table,
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, $table) .
			' AND version='.$TYPO3_DB->fullQuoteStr($version, $table).
			($reviewstate ? ' AND reviewstate='.intval($reviewstate) : '')
		);
		
		if ($res) {
			while ($row =  $TYPO3_DB->sql_fetch_assoc ($res)) {
				$review[] =  $row;
			}
			return $review;
		}
		return FALSE;
	}

	/**
	 * Creates a new review record for the given extension version. If the extension
	 * version does not exist or a review record already exists, FALSE is returned
	 * 
	 * NOT USED AT THE MOMENT
	 * 
	 * @param		string	$extensionKey: Extension key of the extension
	 * @param		string	$version: Version number of the extension
	 * @param		string	$reviewer: User name of the initial reviewer
	 * @return		boolean	TRUE or FALSE
	 * @access		protected
	 */

	protected function db_createReviewRecord ($extensionKey, $version, $reviewer) {
		global $TYPO3_DB, $TSFE;

		$t3xPathAndFileName = $this->getExtensionVersionPathAndBaseName($extensionKey, $version).'.t3x';
		$t3xFileMD5 = @md5_file ($t3xPathAndFileName);
		$reviewRow = array (
			'extensionkey' => $extensionKey,
			'version' => $version,
			'reviewer' => $reviewer,
			'tstamp' => time(),
			't3xfilemd5' => $t3xFileMD5
		);
		
		$res = $TYPO3_DB->exec_INSERTquery ('tx_terfe_reviews', $reviewRow);
		return $res ? True : False;
	}

	/**
	 * Saves a new review record for the current extension version. If the extension
	 * version does not exist or saving fails, FALSE is returned
	 * 
	 * @param		array	$data:	The Review data array
	 * @return		boolean	TRUE or FALSE
	 * @access		protected
	 */
	
	protected function db_saveReviewRecord($data){
		global $TYPO3_DB;
		
		// check for valid FE user
		if (!$this->reviewer){
			$this->errorMessage = 'Not logged in as valid reviewer';
			return False;
		}


		// check for valid input
		if (!$data['reviewstate'] || !in_array($data['reviewstate'],$this->validReviewStates)) {
			$this->errorMessage = 'No valid status submitted!';
			return False;
		}

		if ($data['reviewstate'] == -1 && !$data['objections']){
			$this->errorMessage = $this->pi_getLL('error_rejectwithoutcomplaints');
			return False;
		}
		
		if ($data['reviewstate'] >= 1 && $data['objections']){
			$this->errorMessage = $this->pi_getLL('error_passwithcomplaints');
			return False;
		}
		

		if ($data['forcestate'] || $data['reviewstate'] == -1){			
			if (!$this->soap_setReviewState($this->extensionKey, $this->version, intval($data['reviewstate']))){
					$this->errorMessage = 'Could not save review state!';
					return False;
			} else { $updated_soap = True;
			}
		}
	 
		
		$objections = array();
		if (is_array($data['objections'])){
			foreach ($data['objections'] as $objection){
				if (in_array($objection,$this->validObjections)){
					$objections[] = $objection;
				}
			}
		}

		$t3xPathAndFileName = $this->getExtensionVersionPathAndBaseName($this->extensionKey, $this->version).'.t3x';
		$t3xFileMD5 = @md5_file ($t3xPathAndFileName);
		$reviewRow = array (
//			'extensionkey' => $this->extensionKey,
//			'version' => $this->version,
//			'reviewer' => $this->reviewer['username'],
			'reviewstate' => intval($data['reviewstate']),
			'notes' => strip_tags($data['notes']),
			'objections' => implode(', ',$objections),
			'tstamp' => time(),
			't3xfilemd5' => $t3xFileMD5
		);

		$res = $TYPO3_DB->exec_UPDATEquery (
			'tx_terfe_reviews',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($this->extensionKey, 'tx_terfe_reviews') .
				' AND version='.$TYPO3_DB->fullQuoteStr($this->version, 'tx_terfe_reviews'),
			$reviewRow
		);		

		if (!$res){
			$this->errorMessage = "Could not save review!";
			return False;
		} 

		if ($data['forcestate'] || $data['reviewstate'] == -1 || sizeof($this->db_getReviewRecords($this->extensionKey, $this->version,1)) >= $this->minReviews){
			$rstate = array('reviewstate' => intval($data['reviewstate']));

			//set state via SOAP
			if (!$updated_soap && !$this->soap_setReviewState($this->extensionKey, $this->version, $rstate)){
				$this->errorMessage = "Could not save review via SOAP!";
				return False;
			}
			
			//update the database (so we get instant results), to be overriden with next TER sync
			$res = $TYPO3_DB->exec_UPDATEquery(
				'tx_terfe_extensions', 
				'extensionkey='.$TYPO3_DB->fullQuoteStr($this->extensionKey, 'tx_terfe_extensions') .
				' AND version='.$TYPO3_DB->fullQuoteStr($this->version, 'tx_terfe_extensions'),
				$rstate
				);
			if (!$res){	
				$this->errorMessage = "Could not save review state to database, this is not fatal!";
			}
		}
		
		return True;		
		
	} 

		

	/**
	 * Updates the MD5 of a .T3X file stored in a review so it reflects the current MD5
	 * of the actual file.
	 * 
	 * @param		string	$extensionKey: Extension key of the extension
	 * @param		string	$version: Version number of the extension
	 * @return		boolean	TRUE or FALSE
	 * @access		protected
	 */

	protected function db_updateReviewRecord_t3xMD5 ($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;
	
		$currentReviewRow = $this->db_getReviewRecords($extensionKey, $version);
		if ($currentReviewRow === FALSE) return FALSE;

		$t3xPathAndFileName = $this->getExtensionVersionPathAndBaseName($extensionKey, $version).'.t3x';
		$t3xFileMD5 = @md5_file ($t3xPathAndFileName);
		$reviewRow = array (
			't3xfilemd5' => $t3xFileMD5
		);
		
		$res = $TYPO3_DB->exec_UPDATEquery (
			'tx_terfe_reviews',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_reviews') .
				' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_reviews'),
			$reviewRow
		);

		$this->db_addReviewNote ($currentReviewRow['uid'], 'Updated .T3X file MD5 (new MD5: '.$t3xFileMD5.')');
				
		return $res ? TRUE : FALSE;
	}

	/**
	 * Stores the new review state in the review record and sets it in the TER by issuing
	 * a command via SOAP
	 * 
	 * @param		string	$extensionKey: Extension key of the extension
	 * @param		string	$version: Version number of the extension
	 * @param		integer	$reviewState: The new review state
	 * @return		boolean	TRUE or FALSE
	 * @access		protected
	 */
	protected function soap_setReviewState ($extensionKey, $version, $reviewState) {
		if ($this->conf['noSoap']){return True;}

		global $TYPO3_DB;
	
		$soapClientObj = new SoapClient ($this->WSDLURI, array ('exceptions' => TRUE));
		
		try {
			$accountDataArr = array (
				'username' => $this->reviewer['username'],
				'password' => $this->reviewer['password']
			);
			$reviewStateDataArr = array (
				'extensionKey' => $extensionKey,
				'version' => $version,
				'reviewState' => $reviewState,			
			);
			$soapClientObj->setReviewState($accountDataArr, $reviewStateDataArr);
		} catch (SoapFault $exception) {
			return FALSE;
		}

		return True;
		
		/*
		 * $reviewRow = array (
			'reviewstate' => intval($reviewState)
		);
		
		$res = $TYPO3_DB->exec_UPDATEquery (
			'tx_terfe_reviews',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_reviews') .
				' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_reviews'),
			$reviewRow
		);		
		return $res ? TRUE : FALSE;
		 */
	}

	/**
	 * Adds a note to a review. If the reviewer's username is not specified,
	 * the username of the currently logged in FE user will be taken instead.
	 * 
	 * @param		integer	$reviewUid: UID of the review record
	 * @param		string	$note: The note (tags will be stripped)
	 * @param		string	$reviewer: (optional) User name of the reviewer
	 * @return		boolean	TRUE or FALSE
	 * @access		protected
	 */
	protected function db_addReviewNote ($reviewUid, $note, $reviewer=NULL) {
		global $TYPO3_DB, $TSFE;

		$noteRow = array (
			'reviewuid' => intval($reviewUid),
			'tstamp' => time(),
			'note' => strip_tags ($note),
			'reviewer' => ($reviewer === NULL ? $TSFE->fe_user->user['username'] : $reviewer)
		);
		$res = $TYPO3_DB->exec_INSERTquery ('tx_terfe_reviewnotes', $noteRow);		
		return $res ? TRUE : FALSE;
	}

	/**
	 * Returns all notes of a review.
	 * 
	 * @param		integer	$reviewUid: UID of the review record
	 * @return		mixed: Array of review notes or FALSE if an error occurred
	 * @access		protected
	 */
	protected function db_getReviewNotes ($reviewUid) {
		global $TYPO3_DB, $TSFE;

		$table = 'tx_terfe_reviewnotes';
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			$table,
			'reviewuid='.$TYPO3_DB->fullQuoteStr($reviewUid, $table),
			'',
			'tstamp DESC'
		);
		
		if ($res) {
			$reviewNotesArr = array();	
			while ($row = $TYPO3_DB->sql_fetch_assoc ($res)) {
				$reviewNotesArr[$row['tstamp']] = $row;
			}
			return $reviewNotesArr;			
		}
		return FALSE;
	}


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi3/class.tx_terfe_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi3/class.tx_terfe_pi3.php']);
}

?>
