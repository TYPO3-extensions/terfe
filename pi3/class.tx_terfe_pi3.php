<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Robert Lemke (robert@typo3.org)
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
 * Plugin "Review Frontend" for the 'ter_fe' extension.
 *
 * $Id$
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @author	Michael Scharkow <michael@underused.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  123: class tx_terfe_pi3 extends tx_terfe_pi1
 *  145:     protected function init($conf)
 *  178:     public function main($content,$conf)
 *
 *              SECTION: LIST VIEWS
 *  229:     protected function renderListView($mode)
 *  307:     protected function renderListView_shortExtensionRecord($extensionRecord)
 *
 *              SECTION: SINGLE VIEWS
 *  350:     protected function renderSingleView()
 *  392:     protected function renderSingleView_selectExtensionVersion()
 *  416:     protected function renderSingleView_extensionInfo($extensionRecord)
 *  493:     protected function renderSingleView_reviewInfo($extensionKey, $version)
 *  579:     protected function renderSingleView_reviewNotes($extensionKey, $version)
 *  628:     protected function renderSingleView_otherVersionsInfo($extensionKey, $currentVersion)
 *  678:     protected function renderSingleView_otherExtensionsInfo($owner, $currentExtensionKey)
 *  736:     protected function renderSingleView_files($extensionKey, $version)
 *
 *              SECTION: SUB RENDER FUNCTIONS
 *  775:     protected function renderSub_mainTopMenu()
 *  797:     protected function renderSub_subTopMenu()
 *  818:     protected function renderSub_button($fieldsArr, $llKey)
 *  842:     protected function renderSub_reviewRatingInfo($reviewRecord)
 *  883:     protected function renderSub_reviewRatingForm($reviewRecord)
 *  922:     protected function renderSub_errorWrap($message)
 *
 *              SECTION: COMMAND HANDLER FUNCTIONS
 *  948:     protected function cmd_handle()
 *  968:     protected function cmd_startReview()
 *  979:     protected function cmd_leaveReviewTeam()
 * 1002:     protected function cmd_joinReviewTeam()
 * 1021:     protected function cmd_setReviewRating()
 * 1038:     protected function cmd_removeReviewRating()
 * 1053:     protected function cmd_addReviewNote()
 *
 *              SECTION: DATABASE RELATED FUNCTIONS
 * 1079:     protected function db_getAllVersionNumbersOfExtension ($extensionKey)
 * 1105:     protected function db_getReviewRecord ($extensionKey, $version)
 * 1133:     protected function db_createReviewRecord ($extensionKey, $version, $reviewer)
 * 1165:     protected function db_deleteReviewRecord($extensionKey, $version)
 * 1208:     protected function db_updateReviewRecord($extensionKey, $version, $fieldsArr)
 * 1241:     protected function db_getReviewRatingRecords ($extensionKey, $version)
 * 1275:     protected function db_createReviewRatingRecord($extensionKey, $version, $rating, $reviewer)
 * 1321:     protected function db_deleteReviewRatingRecords($extensionKey, $version, $reviewer=NULL)
 * 1359:     protected function db_updateReviewRecord_t3xMD5($extensionKey, $version)
 * 1394:     protected function db_addReviewNote ($extensionKey, $version, $note, $reviewer=NULL)
 * 1416:     protected function db_getReviewNotes($extensionKey, $version)
 *
 *              SECTION: SOAP FUNCTIONS
 * 1459:     protected function soap_setReviewState($extensionKey, $version, $reviewState)
 * 1492:     protected function getReviewState($extensionKey, $version)
 *
 *              SECTION: ICON FUNCTIONS
 * 1525:     protected function getIcon_reviewState($reviewState)
 * 1543:     protected function getIcon_reviewStateBar($reviewState)
 *
 * TOTAL FUNCTIONS: 40
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

define (TX_TERFE_REVIEWSTATE_UNREVIEWED, FALSE);
define (TX_TERFE_REVIEWSTATE_INSECURE, -1);
define (TX_TERFE_REVIEWSTATE_PENDING, 0);
define (TX_TERFE_REVIEWSTATE_PASSED, 1);
define (TX_TERFE_COLOR_UNREVIEWED, '#b6b6b6');
define (TX_TERFE_COLOR_INSECURE, '#d12438');
define (TX_TERFE_COLOR_PENDING, '#e9e718');
define (TX_TERFE_COLOR_PASSED, '#3bb65c');
define (TX_TERFE_MINVOTES, 2);														// Minimum number of votes neccessary to let an extension pass the security check


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('ter_fe').'class.tx_terfe_common.php');
if (t3lib_extMgm::isLoaded ('ter_doc')) {
	require_once (t3lib_extMgm::extPath('ter_doc').'class.tx_terdoc_api.php');
}

/**
 * Plugin TER extension reviews
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @author		Michael Scharkow <michael@underused.org>
 * @package 	TYPO3
 * @subpackage	tx_terfe
 */
class tx_terfe_pi3 extends tx_terfe_pi1 {

	public		$prefixId = 'tx_terfe_pi3';											// Same as class name
	public		$scriptRelPath = 'pi3/class.tx_terfe_pi3.php';						// Path to this script relative to the extension dir.
	public		$extKey = 'ter_fe';													// The extension key.
	public		$pi_checkCHash = TRUE;												// Handle empty CHashes correctly

	protected	$viewMode = '';														// View mode, one of the following: LATEST, CATEGORIES, FULLLIST
	protected	$WSDLURI;
	protected	$SOAPServiceURI;

	protected	$reviewer = array();												// User name and password of the currently logged in reviewer

	protected	$notificationEmail_recipient = 'robert@typo3.org,hirdes@elios.de,rg@rupertgermann.de'';
	protected	$notificationEmail_sender = 'noreply@typo3.org';
	protected	$notificationEmail_replyTo = 'typo3-project-security@lists.netfielders.de';

	/**
	 * Initializes the plugin, only called from main()
	 *
	 * @param	array		$conf: The plugin configuration array
	 * @return	void
	 * @access	protected
	 * @see		main()
	 */
	protected function init($conf) {
		global $TSFE;

		$this->conf=$conf;
		$this->pi_setPiVarDefaults();			// Set default piVars from TS
		$this->pi_initPIflexForm();				// Init FlexForm configuration for plugin
		$this->pi_loadLL();

		$this->commonObj = new tx_terfe_common($this);
		$this->commonObj->repositoryDir = $this->conf['repositoryDirectory'];
		if (substr ($this->commonObj->repositoryDir, -1, 1) != '/') $this->commonObj->repositoryDir .= '/';
		$this->commonObj->init();

		$staticConfArr = unserialize ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_fe']);
		if (is_array ($staticConfArr)) {
			$this->WSDLURI = $staticConfArr['WSDLURI'];
			$this->SOAPServiceURI = $staticConfArr['SOAPServiceURI'];
		}

		$this->reviewer = array (
			'username' => $TSFE->fe_user->user['username'],
			'password' => $TSFE->fe_user->user['password']
		);
	}

	/**
	 * The plugin's main function
	 *
	 * @param	string		$content: Content rendered so far (not used)
	 * @param	array		$conf: The plugin configuration array
	 * @return	string		The plugin's HTML output
	 * @access	public
	 */
	public function main($content,$conf)	{
		global $TSFE;

		$this->init($conf);
		if (!@is_dir ($this->commonObj->repositoryDir)) return 'TER_FE Error: Repository directory ('.$this->repositoryDir.') does not exist!';
		$subContent = '';

		$subContent .= $this->cmd_handle();

		if (isset($this->piVars['extensionkey'])) {
			$subContent .= $this->renderSingleView();
		} else {
			if (!strlen($this->piVars['view'])) $this->piVars['view'] = 'review';
			switch ($this->piVars['view']) {
				case 'unreviewed':	$subContent .= $this->renderListView('unreviewed'); break;
				case 'passed':		$subContent .= $this->renderListView('passed'); break;
				case 'insecure':	$subContent .= $this->renderListView('insecure'); break;
				case 'pending':		$subContent .= $this->renderListView('pending'); break;
				default:			$subContent .= $this->renderSingleView_selectExtensionVersion(); break;
			}
		}

			// Put everything together:
		$content = '
			<h2>'.$this->pi_getLL('general_extensionreview', '', 1).'</h2>'.
			$this->renderSub_mainTopMenu().
			($this->errorMessage ? '<div class="error" style="padding: 1em;border: 2px solid red; text-align: center; margin: 1em;">'.$this->errorMessage.'</div>' : '').
			$subContent.'
			<br />
		';

		return $this->pi_wrapInBaseClass($content);
	}





	/*********************************************************
	 *
	 * LIST VIEWS
	 *
	 *********************************************************/

	/**
	 * Renders a list of extensions with a certain review status
	 *
	 * @param	string		$mode: "unreviewed", "passed", "insecure" or "pending"
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderListView($mode) {
		global $TYPO3_DB, $TSFE;

		$tableRows = array ();

		switch ($mode) {
			case 'unreviewed' : 
				$res = $TYPO3_DB->exec_SELECTquery (
					'ext.extensionkey,ext.title,ext.version,ext.authorname,ext.authoremail,ext.ownerusername,ext.state',
					'tx_terfe_extensions as ext LEFT JOIN tx_terfe_reviews AS rev ON (
						ext.extensionkey = rev.extensionkey AND
						ext.version = rev.version
					) ',
					'ext.reviewstate = 0 AND 
						(ext.state = "stable" OR ext.state = "beta") AND
						rev.uid IS NULL
					',
					'',
					'ext.extensiondownloadcounter DESC',
					''
				);
			break;
			case 'pending':
				$res = $TYPO3_DB->exec_SELECTquery (
					'ext.extensionkey,ext.title,ext.version,ext.authorname,ext.authoremail,ext.ownerusername,ext.state,rev.reviewers',
					'tx_terfe_extensions as ext JOIN tx_terfe_reviews AS rev ON (
						ext.extensionkey = rev.extensionkey AND
					ext.version = rev.version)',
					'ext.reviewstate = 0',
					'',
					'tstamp ASC',
					''
				);
			break;
			case 'passed' :
				$res = $TYPO3_DB->exec_SELECTquery (
					'ext.extensionkey,title,ext.version,authorname,authoremail,ownerusername,state',
					'tx_terfe_extensions as ext JOIN tx_terfe_reviews AS rev ON (
						ext.extensionkey = rev.extensionkey AND
					ext.version = rev.version)',
					'ext.reviewstate >= 1',
					'',
					'lastmodified DESC',
					''
				);
			break;
			case 'insecure' :	
				$res = $TYPO3_DB->exec_SELECTquery (
					'ext.extensionkey,title,ext.version,authorname,authoremail,ownerusername,state',
					'tx_terfe_extensions as ext JOIN tx_terfe_reviews AS rev ON (
						ext.extensionkey = rev.extensionkey AND
					ext.version = rev.version)',
					'ext.reviewstate < 0',
					'',
					'lastmodified DESC',
					''
				);
			break;			
		}

		$extensionRecordsToRender = array();

		if ($res) {
			if ($TYPO3_DB->sql_num_rows($res) == 0) return $this->pi_getLL('listview_noextensionsfound','',1);
			while ($extensionRecord = $TYPO3_DB->sql_fetch_assoc ($res)) {
				if (version_compare($extensionRecord['version'], $extensionRecordsToRender[$extensionRecord['extensionkey']]['version'], '>')) {
					$extensionRecordsToRender[$extensionRecord['extensionkey']] = $extensionRecord;
				}
			}
		}

		foreach ($extensionRecordsToRender as $extensionRecord) {
			if ($mode == 'unreviewed') {
				$latestVersion = $this->commonObj->db_getLatestVersionNumberOfExtension ($extensionRecord['extensionkey'], TRUE);
				if ($extensionRecord['version'] == $latestVersion) {
					$tableRows[] = $this->renderListView_shortExtensionRecord ($extensionRecord);
				}
			} else {
				$disabled = t3lib_div::inList($extensionRecord['reviewers'], $this->reviewer['username']);
				$tableRows[] = $this->renderListView_shortExtensionRecord ($extensionRecord, $disabled);
			}
		}

		$content.= '
			<p>'.$this->pi_getLL('listview_'.$mode.'_introduction','',1).'</p>
			<table style="margin-top:10px;">
				<th class="th-main">&nbsp;</th>
				<th class="th-main">'.$this->commonObj->getLL('extension_title','',1).'</th>
				<th class="th-main">'.$this->commonObj->getLL('extension_extensionkey','',1).'</th>
				<th class="th-main">'.$this->commonObj->getLL('extension_version','',1).'</th>
				<th class="th-main">'.$this->commonObj->getLL('extension_authorname','',1).'</th>
			'.implode('', $tableRows).'
			</table>
		';
		return $content;
	}

	/**
	 * Render a short extension info row for the review-specific listing of extensions
	 *
	 * @param	array		$extensionRecord: Database record from tx_terfe_extensions
	 * @param	boolean	$disabled: If set to TRUE, the row will be rendered in a disabled style
	 * @return	string		Two HTML table rows wrapped in <tr>
	 * @access	protected
	 */
	protected function renderListView_shortExtensionRecord($extensionRecord, $disabled=FALSE)	{
		global $TSFE;

		if (t3lib_extMgm::isLoaded ('ter_doc')) {
			$terDocAPIObj = tx_terdoc_api::getInstance();
			$documentationLink = $terDocAPIObj->getDocumentationLink ($extensionRecord['extensionkey'], $extensionRecord['version']);
		} else {
			$documentationLink = '';
		}

		$extensionRecord = $this->commonObj->db_prepareextensionRecordForOutput ($extensionRecord);
		$cssClass = $disabled ? 'td-sub-disabled' : 'td-sub';

		$tableRow = '
			<tr>
				<td class="'.$cssClass.'">'.$this->commonObj->getIcon_extension ($extensionRecord['extensionkey'], $extensionRecord['version']).'</td>
				<td class="'.$cssClass.'">'.$this->pi_linkTP_keepPIvars($extensionRecord['title'], array('view' => 'review', 'extensionkey' => $extensionRecord['extensionkey'], 'version' => $extensionRecord['version']),1).'</td>
				<td class="'.$cssClass.'">'.$extensionRecord['extensionkey'].'</td>
				<td class="'.$cssClass.'">'.$extensionRecord['version'].'</td>
				<td class="'.$cssClass.'">'.$extensionRecord['ownerusernameandname'].'</td>
			</tr>
		';

		return $tableRow;
	}





	/*********************************************************
	 *
	 * SINGLE VIEWS
	 *
	 *********************************************************/

	/**
	 * Renders the single view of an extension for the review. Sub functions render
	 * the situation-specific parts.
	 *
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderSingleView() {

		$extensionKey = $this->piVars['extensionkey'];
		$version = (strlen(trim($this->piVars['version'])) ? $this->piVars['version'] : $this->commonObj->db_getLatestVersionNumberOfExtension($extensionKey,TRUE));
		if (!strlen($extensionKey) || !strlen($version)) return $this->renderSingleView_selectExtensionVersion();

		$extensionRecord = $this->commonObj->db_getExtensionRecord($extensionKey, $version);
		if ($extensionRecord === FALSE) return $this->renderSingleView_selectExtensionVersion();

		if (!strlen($this->piVars['subview'])) $this->piVars['subview'] = 'overview';

		$output = '<h3>'.htmlspecialchars(sprintf($this->pi_getLL('singleview_heading'), $this->piVars['extensionkey'])).'</h3><br />'.$this->renderSub_subTopMenu();

		switch ($this->piVars['subview']) {
			case 'files' :
				$output .= '
					<table>
						'.$this->renderSingleView_files($extensionKey, $version).'
					</table>
				';
			break;
			default:
				$output .= '
					<table>
						'.$this->renderSingleView_extensionInfo($extensionRecord).'
						'.$this->renderSingleView_reviewInfo($extensionKey, $version).'
						'.$this->renderSingleView_reviewNotes($extensionKey, $version).'
						'.$this->renderSingleView_otherVersionsInfo($extensionKey, $version).'
						'.$this->renderSingleView_otherExtensionsInfo($extensionRecord['ownerusername'], $extensionKey).'
					</table>
				';
		}

		return $output;
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
	 * Renders a section with extension details for the review view.
	 *
	 * @param	array		$extensionRecord: (raw) row of the extension record
	 * @return	string		HTML output: Table rows, ready for insertion into a table
	 * @access	protected
	 */
	protected function renderSingleView_extensionInfo($extensionRecord) {
		global $TSFE;

		if (t3lib_extMgm::isLoaded ('ter_doc')) {
			if (t3lib_extMgm::isLoaded ('ter_doc_html')) {
				$terDocAPIObj = tx_terdoc_api::getInstance();
				$documentationLink = $terDocAPIObj->getDocumentationLink ($extensionRecord['extensionkey'], $extensionRecord['version']);
			} else {
				$documentationLink = $terDocAPIObj->getDocumentationLink ($extensionRecord['extensionkey'], $extensionRecord['version']);
			}
		}

			// Prepare options for version selectorbox:
		$optionValuesArr = $this->db_getAllVersionNumbersOfExtension ($extensionRecord['extensionkey']);
		$versionOptions = '';
		foreach ($optionValuesArr as $optionValue) {
			$versionOptions .= '<option value="'.$optionValue.'" '.($optionValue == $extensionRecord['version'] ? 'selected="selected"' : '').'>'.$optionValue.'</option>'.chr(10);
		}


			// Build the output
		$extensionRecord = $this->commonObj->db_prepareextensionRecordForOutput($extensionRecord);
		$content ='
				<tr>
					<th class="th-sub" colspan="3">'.sprintf ($this->pi_getLL('singleview_review_extensioninfo_sectionheading','',1), $extensionRecord['title']).'</th>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->commonObj->getLL('extension_extensionkey','',1).':</th>
					<td class="td-sub" style="width:90%;"><em>'.$extensionRecord['extensionkey'].'</em></td>
					<td class="td-sub" rowspan="4">
						<table>
							<tr><th nowrap="nowrap" class="th-sub">'.$this->commonObj->getLL('extension_state','',1).':</th><td>'.$this->commonObj->getIcon_state($extensionRecord['state_raw']).'</td></tr>
							<tr>
								<th nowrap="nowrap" class="th-sub">'.$this->commonObj->getLL('extension_version','',1).':</th>
								<td>
									<form name="versionselector" action="'.t3lib_div::getIndpEnv('REQUEST_URI').'" method="POST">
										<select name="tx_terfe_pi3[version]" onChange="versionselector.submit()">'.$versionOptions.'</select>
									</form>
								</td>
							</tr>
							<tr><th nowrap="nowrap" class="th-sub">'.$this->commonObj->getLL('extension_category','',1).':</th><td class="td-sub">'.$this->commonObj->getLL('extension_category_'.$extensionRecord['category'],'',1).'</td></tr>
							<tr><th nowrap="nowrap" class="th-sub">'.$this->commonObj->getLL('extension_lastuploaddate','',1).':</th><td class="td-sub">'.$extensionRecord['lastuploaddate'].'</td></tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->commonObj->getLL('extension_description','',1).':</th>
					<td class="td-sub">'.$extensionRecord['description'].'</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->commonObj->getLL('extension_ownerusername','',1).':</th>
					<td class="td-sub">'.$extensionRecord['ownerusernameandname'].'</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->commonObj->getLL('extension_documentation','',1).':</th>
					<td class="td-sub" valign="top">'.$documentationLink.'</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->commonObj->getLL('extension_dependencies','',1).':</th>
					<td class="td-sub" valign="top">'.$this->commonObj->getRenderedDependencies ($extensionRecord['dependencies']).'</td>
					<td class="td-sub" valign="top">'.$this->commonObj->getRenderedReverseDependencies ($extensionRecord['extensionkey'], $extensionRecord['version']).'</td>
				</tr>
		';

		return $content;

	}

	/**
	 * Renders a section with information about the review of the specified
	 * extension version and buttons for solving review related tasks.
	 *
	 * @param	string		$extensionKey: The extension key of the extension to show review information for
	 * @param	string		$version: Version number of the extension
	 * @return	string		HTML output - Table rows, ready for insertion into a table.
	 * @access	protected
	 */
	protected function renderSingleView_reviewInfo($extensionKey, $version) {
		global $TSFE;

		$reviewRecord = $this->db_getReviewRecord($extensionKey, $version);
		$reviewState = is_array($reviewRecord) ? $this->getReviewState($extensionKey, $version) : FALSE;

		$joinLeaveButton = '';
		$reviewTeam = '';
		$reviewersArr = t3lib_div::trimExplode (',', $reviewRecord['reviewers']);
		if (is_array($reviewersArr)) {
			foreach ($reviewersArr as $username) {
				if (strlen($username)) {
					$reviewTeam .= (strlen($reviewTeam) ? ', ' : '') . $this->commonObj->csConvHSC ($username .' ('.$this->commonObj->db_getFullnameByUsername($username).')');
				}
			}
		}

			// Switch based on review state:
		switch (TRUE) {
			case ($reviewState === TX_TERFE_REVIEWSTATE_UNREVIEWED) :
				$buttonFieldsArr  = array (
					'cmd' => 'startreview',
					'extensionkey' => $extensionKey,
					'version' => $version
				);
				$reviewStateOutput = '
					<td class="td-sub">'.$this->pi_getLL('singleview_review_reviewinfo_reviewstate_'.($reviewState === FALSE ? 'FALSE' : $reviewState),'',1).'</td>
					<td class="td-sub" style="text-align:center;">'.$this->renderSub_button($buttonFieldsArr, 'singleview_review_startreview').'</td>
				';
			break;
			case ($reviewState === TX_TERFE_REVIEWSTATE_PENDING) :
			case ($reviewState === TX_TERFE_REVIEWSTATE_INSECURE) :
			case ($reviewState === TX_TERFE_REVIEWSTATE_PASSED) :

				$userAlreadyReviewsThisExtension = t3lib_div::inList($reviewRecord['reviewers'], $this->reviewer['username']);
				$reviewRatingRecords = $this->db_getReviewRatingRecords($extensionKey, $version);

				if ($reviewRatingRecords === FALSE || !isset($reviewRatingRecords[$this->reviewer['username']])) {
					$buttonFieldsArr  = array (
						'cmd' => $userAlreadyReviewsThisExtension ? 'leavereviewteam' : 'joinreviewteam',
						'extensionkey' => $extensionKey,
						'version' => $version
					);
					$joinLeaveButton = $this->renderSub_button($buttonFieldsArr, 'singleview_review_'.$buttonFieldsArr['cmd']);
				}

				$reviewStateOutput = '
					<td class="td-sub">
						'.$this->pi_getLL('singleview_review_reviewinfo_reviewstate_'.($reviewState === FALSE ? 'FALSE' : $reviewState),'',1).'
						'.$this->pi_getLL('singleview_review_reviewinfo_'.($userAlreadyReviewsThisExtension ? 'youarereviewing' : 'youarenotreviewing'),'',1).'
'.$this->debugOutput.'
					</td>
					<td class="td-sub">&nbsp;</td>
				';
			break;
		}

			// Put everything together:
		$output = '
			<tr>
				<th class="th-sub" colspan="3">'.$this->getIcon_reviewState($reviewState).$this->pi_getLL('singleview_review_reviewinfo_sectionheading','',1).'</th>
			</tr>
			<tr>
				<th class="th-sub">'.$this->commonObj->getLL('extension_reviewstate','',1).':</th>
				'.$reviewStateOutput.'
			</tr>
			<tr>
				<th class="th-sub">'.$this->pi_getLL('review_reviewers','',1).':</th>
				<td class="td-sub">'.$reviewTeam.'&nbsp;</td>
				<td class="td-sub" style="text-align:center;">'.$joinLeaveButton.'&nbsp;</td>
			</tr>
			<tr>
				<th class="th-sub">'.$this->pi_getLL('review_rating','',1).':</th>
				'.$this->renderSub_reviewRatingInfo($reviewRecord).'
			</tr>
		';
		return $output;
	}

	/**
	 * Renders a section with review notes for the review of the given extension
	 *
	 * @param	string		$extensionKey: The extension key of the extension to show review notes for
	 * @param	string		$version: Version number of the extension
	 * @return	string		HTML output - Table rows, ready for insertion into a table.
	 * @access	protected
	 */
	protected function renderSingleView_reviewNotes($extensionKey, $version) {
		global $TSFE;

		$output = '';
		$reviewRow = $this->db_getReviewRecord ($extensionKey, $version);
		if (is_array($reviewRow)) {
			$reviewNotes = $this->db_getReviewNotes($extensionKey, $version);

			if (is_array ($reviewNotes)) {
				$output ='
					<tr>
						<th class="th-sub" colspan="3">'.$this->pi_getLL('singleview_review_reviewnotes_notesandhistory','',1).'</th>
					</tr>
					<tr>
						<th class="th-sub">&nbsp;</th>
						<td class="td-sub" colspan="2">
							<form action="'.t3lib_div::getIndpEnv('REQUEST_URI').'" method="POST" style="display:inline;">
								<textarea name="tx_terfe_pi3[reviewnote]" style="margin:4px;" cols="76" rows="10" wrap="virtual"></textarea>
								<input type="submit" value="'.$this->pi_getLL('singleview_review_reviewnotes_addnote','',1).'"/>
								<input type="hidden" name="tx_terfe_pi3[cmd]" value="addreviewnote" />
								<input type="hidden" name="tx_terfe_pi3[extensionkey]" value="'.htmlspecialchars($extensionKey).'" />
								<input type="hidden" name="tx_terfe_pi3[version]" value="'.htmlspecialchars($version).'" />
							</form>
						</td>
					</tr>
				';
				foreach ($reviewNotes as $reviewNote) {
					$output .= '
						<tr>
							<th class="th-sub">&nbsp;</th>
							<td class="td-sub" colspan="2">
								<strong>'.$reviewNote['reviewer'].' <em>'.strftime ($this->commonObj->getLL('general_dateandtimeformat'), $reviewNote['tstamp']).'</em>:</strong><br />
								'.nl2br(htmlspecialchars(str_replace("\t", '&nbsp;&nbsp;&nbsp;', $reviewNote['note']))).'</td>
						</tr>
					';
				}
			}
		}
		return $output;
	}

	/**
	 * Renders a section with information about other versions of the specified extension
	 *
	 * @param	string		$extensionKey: The extension key of the extension to show review information for
	 * @param	string		$currentVersion: Version number of the extension version not to include
	 * @return	string		HTML output - Table rows, ready for insertion into a table.
	 * @access	protected
	 */
	protected function renderSingleView_otherVersionsInfo($extensionKey, $currentVersion) {
		global $TSFE;

		$versionsArr = $this->db_getAllVersionNumbersOfExtension($extensionKey);
		if ($versionsArr === FALSE || count($versionsArr) < 2) return '';

		$otherVersionsOutput = '';

		foreach ($versionsArr as $version) {
			$reviewRecord = $this->db_getReviewRecord($extensionKey, $version);
			$reviewState = is_array($reviewRecord) ? $this->getReviewState($extensionKey, $version) : FALSE;

			if ($version != $currentVersion) {
				$otherVersionsOutput .= '
					<tr>
						<td class="td-sub">
							'.$this->getIcon_reviewStateBar($reviewState).'
							'.$this->pi_linkTP_keepPIvars($version, array('view' => 'review', 'extensionkey' => $extensionKey, 'version' => $version), 0, 1).'
						</td>
						<td class="td-sub">'.$this->pi_getLL('singleview_review_reviewinfo_reviewstate_'.($reviewState === FALSE ? 'FALSE' : $reviewState),'',1).'</td>
					</tr>
				';
			}
		}

			// Put everything together:
		$output = '
			<tr>
				<th class="th-sub" colspan="3">'.$this->pi_getLL('singleview_review_otherversionsinfo_sectionheading','',1).'</th>
			</tr>
			<tr>
				<th class="th-sub">&nbsp;</th>
				<td class="td-sub" colspan="2">
					<table>
						'.$otherVersionsOutput.'
					</table>
				</td>
			</tr>
		';
		return $output;
	}

	/**
	 * Renders a section with information about other extensions by the same owner
	 *
	 * @param	string		$owner: User name of the extension author
	 * @param	string		$currentExtensionKey: The extension key of the extension not to include
	 * @return	string		HTML output - Table rows, ready for insertion into a table.
	 * @access	protected
	 */
	protected function renderSingleView_otherExtensionsInfo($owner, $currentExtensionKey) {
		global $TSFE;

		$extensionKeysArr = $this->commonObj->db_getExtensionKeysByOwner($owner);
		if ($extensionKeysArr === FALSE || count($extensionKeysArr) < 2) return '';

		$otherExtensionsOutput = '';

		foreach ($extensionKeysArr as $extensionKey) {

			if ($extensionKey != $currentExtensionKey) {
				$versionsArr = $this->db_getAllVersionNumbersOfExtension($extensionKey);
				$otherExtensionsOutput .= '
					<tr>
						<td class="td-sub">
				';
				if (is_array($versionsArr)) {
					foreach ($versionsArr as $version) {
						$reviewRecord = $this->db_getReviewRecord($extensionKey, $version);
						$reviewState = is_array($reviewRecord) ? $this->getReviewState($extensionKey, $version) : FALSE;
						$otherExtensionsOutput .= $this->getIcon_reviewStateBar($reviewState).' ';
					}
				}
				$otherExtensionsOutput .= '
						</td>
						<td class="td-sub">
							'.$this->pi_linkTP_keePPIvars($extensionKey, array('view' => 'review', 'extensionkey' => $extensionKey, 'version' => $version), 0, 1).'
						</td>
					</tr>
				';
			}
		}

			// Put everything together:
		$output = '
			<tr>
				<th class="th-sub" colspan="3">'.$this->pi_getLL('singleview_review_otherextensionsinfo_sectionheading','',1).'</th>
			</tr>
			<tr>
				<th class="th-sub">&nbsp;</th>
				<td class="td-sub" colspan="2">
					<table>
						'.$otherExtensionsOutput.'
					</table>
				</td>
			</tr>
		';
		return $output;
	}

	/**
	 * Renders a section with the list of files of the given extension
	 *
	 * @param	string		$extensionKey: The extension key of the extension to list the files of
	 * @param	string		$version: Version number of the extension
	 * @return	string		HTML output - Table rows, ready for insertion into a table.
	 * @access	protected
	 */
	protected function renderSingleView_files($extensionKey, $version) {
		global $TSFE;

		$extDetailsRow = $this->commonObj->db_getExtensionDetails ($extensionKey, $version);

			// Build the output
		$content ='
				<tr>
					<th class="th-sub" colspan="3">'.sprintf ($this->pi_getLL('singleview_files_sectionheading','',1), $extensionRecord['title']).'</th>
				</tr>
				<tr>
					<td class="td-sub" colspan="3">
						'.$this->commonObj->getRenderedListOfFiles ($extDetailsRow).'
					</td>
				</tr>
		';

		return $content;

	}





	/*********************************************************
	 *
	 * SUB RENDER FUNCTIONS
	 *
	 *********************************************************/

	/**
	 * Renders the main top menu which allows the user to select from one of
	 * the list views.
	 *
	 * @return	string		HTML output (the top menu)
	 * @access	protected
	 * @see		main()
	 */
	protected function renderSub_mainTopMenu() {
		$menuItemsAndStates = array ('review' => NULL, 'unreviewed' => TX_TERFE_REVIEWSTATE_UNREVIEWED, 'pending' => TX_TERFE_REVIEWSTATE_PENDING, 'passed' => TX_TERFE_REVIEWSTATE_PASSED, 'insecure' => TX_TERFE_REVIEWSTATE_INSECURE);

		$topMenu = '<br />';
		foreach ($menuItemsAndStates as $itemKey => $state) {
			$itemActive = ($this->piVars['view'] == $itemKey);
			$link = $this->pi_linkTP($this->pi_getLL('views_'.$itemKey,'',1), array('tx_terfe_pi3[view]' => $itemKey), 1);
			$topMenu .='<span class="submenu-button'.($itemActive ? '-active' :'').'">'.($state !== NULL ? $this->getIcon_reviewStateBar($state) : '').' '.$link.'</span>';
		}
		$topMenu .= '<br /><br />';

		return $topMenu;
	}

	/**
	 * Renders the sub top menu which allows the user to select from one certain
	 * sub views in the single view of an extension.
	 *
	 * @return	string		HTML output (the top menu)
	 * @access	protected
	 * @see		renderSingleView()
	 */
	protected function renderSub_subTopMenu() {
		$menuItems = array ('overview', 'files');

		$subTopMenu = '';
		foreach ($menuItems as $itemKey) {
			$itemActive = ($this->piVars['subview'] == $itemKey);
			$link = $this->pi_linkTP_keepPIvars($this->pi_getLL('subviews_'.$itemKey,'',1), array('view' => 'review', 'subview' => $itemKey, 'extensionkey' => $this->piVars['extensionkey'], 'version' => $this->piVars['version']), 0, 1);
			$subTopMenu .='<span class="submenu-button'.($itemActive ? '-active' :'').'">'.$link.'</span> ';
		}
		$subTopMenu .= '<br /><br />';
		return $subTopMenu;
	}

	/**
	 * Renders an HTML form for a button
	 *
	 * @param	array		$fieldsArr: Associative array with field keys => values. Will be rendered as hidden input fields.
	 * @param	string		$llKey: Locallang key which is used for the button label
	 * @return	string		HTML output (form)
	 * @access	protected
	 */
	protected function renderSub_button($fieldsArr, $llKey){
		$output = '
			<form action="'.t3lib_div::getIndpEnv('REQUEST_URI').'" method="POST" style="display:inline;">
		';
		foreach ($fieldsArr as $key => $value) {
			$output .= '
				<input type="hidden" name="tx_terfe_pi3['.htmlspecialchars($key).']" value="'.htmlspecialchars($value).'" />
			';
		}
		$output .= '
				<input type="submit" value="'.$this->pi_getLL($llKey).'"/>
			</form>
		';
		return $output;
	}

	/**
	 * Renders information about the ratings which have or have not been submitted during the
	 * review specified by the review record.
	 *
	 * @param	array		$reviewRecord: The review record of the review we render the information for
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderSub_reviewRatingInfo($reviewRecord) {

		$listOfRatings = '';
		$reviewRatingRecords = $this->db_getReviewRatingRecords ($reviewRecord['extensionkey'], $reviewRecord['version']);
		switch (TRUE) {
			case count ($reviewRatingRecords) == 0 : $votesCountKey = 'none'; break;
			case count ($reviewRatingRecords) == 1 : $votesCountKey = '1'; break;
			case count ($reviewRatingRecords) > 1 : $votesCountKey = 'n'; break;
		}

		if (is_array ($reviewRatingRecords)) {
			$listOfRatings = '<table>';
			foreach ($reviewRatingRecords as $username => $reviewRatingRecord) {
				$rating = $reviewRatingRecord['rating'];
				$listOfRatings .= '
					<tr>
						<td class="td-sub">'.htmlspecialchars($username).'</td>
						<td class="td-sub"><span style="background-color:'.($rating == -1 ? TX_TERFE_COLOR_INSECURE : TX_TERFE_COLOR_PASSED).';">&nbsp;&nbsp;</span> '.$this->pi_getLL('review_rating_'.($rating == -1 ? 'insecure' : 'passed').'','',1).'</td>
					</tr>
				';
			}
			$listOfRatings .= '</table>';
		}

		$output = '
			<td class="td-sub">
				'.$this->pi_getLL('singleview_review_reviewinfo_rating_'.$votesCountKey,'',1).'
				'.$listOfRatings.'
			</td>
			<td class="td-sub" style="text-align:center;">'.$this->renderSub_reviewRatingForm($reviewRecord).'</td>
		';
		return $output;
	}

	/**
	 * Renders an HTML form which sets or removes a review rating
	 *
	 * @param	array		$reviewRecord: The review record of the review to render the form for
	 * @return	string		HTML output (form)
	 * @access	protected
	 */
	protected function renderSub_reviewRatingForm($reviewRecord) {
		if (!t3lib_div::inList($reviewRecord['reviewers'], $this->reviewer['username'])) return '&nbsp';
		$reviewRatingRecords = $this->db_getReviewRatingRecords($reviewRecord['extensionkey'], $reviewRecord['version']);
		$reviewerSubmittedRating = ($reviewRatingRecords === FALSE || isset($reviewRatingRecords[$this->reviewer['username']]));

		if ($reviewerSubmittedRating) {
			$command = 'removereviewrating';
			$rating = $reviewRatingRecords[$this->reviewer['username']]['rating'];
			$ratingOptions = '
				'.$this->pi_getLL('singleview_review_yourated','',1).'
				<span style="background-color:'.($rating == -1 ? TX_TERFE_COLOR_INSECURE : TX_TERFE_COLOR_PASSED).';">&nbsp;&nbsp;</span> '.$this->pi_getLL('review_rating_'.($rating == -1 ? 'insecure' : 'passed').'','',1).'
			';
		} else {
			$command = 'setreviewrating';
			$ratingOptions = '
				<input type="radio" name="tx_terfe_pi3[reviewrating]" value="-1" /><span style="background-color:'.TX_TERFE_COLOR_INSECURE.';">&nbsp;&nbsp;</span> '.$this->pi_getLL('review_rating_insecure','',1).'
				<input type="radio" name="tx_terfe_pi3[reviewrating]" value="1" /><span style="background-color:'.TX_TERFE_COLOR_PASSED.';">&nbsp;&nbsp;</span> '.$this->pi_getLL('review_rating_passed','',1).'<br />
			';
		}

		$output = '
			<form action="'.t3lib_div::getIndpEnv('REQUEST_URI').'" method="POST" style="display:inline;">
				'.$ratingOptions.'<br />
				<input type="submit" value="'.$this->pi_getLL('singleview_review_'.$command).'"/>
				<input type="hidden" name="tx_terfe_pi3[cmd]" value="'.$command.'" />
				<input type="hidden" name="tx_terfe_pi3[extensionkey]" value="'.htmlspecialchars($reviewRecord['extensionkey']).'" />
				<input type="hidden" name="tx_terfe_pi3[version]" value="'.htmlspecialchars($reviewRecord['version']).'" />
			</form>
		';
		return $output;
	}

	/**
	 * Wraps the given string with HTML code used for displaying error messages.
	 *
	 * @param	string		$message: The message to display
	 * @return	string		The message wrapped by additional HTML code
	 * @access	protected
	 */
	protected function renderSub_errorWrap($message) {
		$output = '
			<p>
				<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/warning.gif" width="16" height="16" alt="" title="" style="vertical-align: middle; padding-right:6px;" />
				<strong><span style="color:red;">'.$message.'</span></strong>
			</p>
		';
		return $output;
	}





	/*********************************************************
	 *
	 * COMMAND HANDLER FUNCTIONS
	 *
	 *********************************************************/

	/**
	 * Checks piVars for a command and handles it if neccessary.
	 *
	 * @return	string		An empty string or a message, depending on the command.
	 * @access	protected
	 */
	protected function cmd_handle() {
		switch ($this->piVars['cmd']) {
			case 'startreview':			$output = $this->cmd_startReview(); break;
			case 'leavereviewteam':		$output = $this->cmd_leaveReviewTeam(); break;
			case 'joinreviewteam':		$output = $this->cmd_joinReviewTeam(); break;
			case 'setreviewrating':		$output = $this->cmd_setReviewRating(); break;
			case 'removereviewrating':	$output = $this->cmd_removeReviewRating(); break;
			case 'addreviewnote':		$output = $this->cmd_addReviewNote(); break;
			default:
				$output = '';
		}
		return $output;
	}

	/**
	 * Starts a review
	 *
	 * @return	string		An empty string or a message, depending on the result of the command
	 * @access	protected
	 */
	protected function cmd_startReview() {
		$result = $this->db_createReviewRecord($this->piVars['extensionkey'], $this->piVars['version'], $this->reviewer['username']);
		$subjectAndMessage = '[TER2][review] '.$this->piVars['extensionkey'].' ('.$this->piVars['version'].') : new review started
			'.$this->reviewer['username'].' has started a new security review for extension '.$this->piVars['extensionkey'].' ('.$this->piVars['version'].')
		';
		$this->cObj->sendNotifyEmail($subjectAndMessage, $this->notificationEmail_recipient, $this->notificationEmail_sender, $this->notificationEmail_replyTo, 'TER2 Security review framework');

		return $result === FALSE ? $this->renderSub_errorWrap($this->pi_getLL('error_startreview_general','',1)) : '';
	}

	/**
	 * Removes a user from the review team for a certain extension version
	 *
	 * @return	string		An empty string or a message, depending on the result of the command
	 * @access	protected
	 */
	protected function cmd_leaveReviewTeam() {
		$reviewRecord = $this->db_getReviewRecord($this->piVars['extensionkey'], $this->piVars['version']);
		if ($reviewRecord === FALSE) return $this->renderSub_errorWrap($this->pi_getLL('error_reviewrecordnotfound','',1));

		if (!t3lib_div::inList($reviewRecord['reviewers'], $this->reviewer['username'])) return $this->renderSub_errorWrap($this->pi_getLL('error_cantleavenomember','',1));

		$reviewRecord['reviewers'] = t3lib_div::rmFromList($this->reviewer['username'], $reviewRecord['reviewers']);
		$this->db_addReviewNote($this->piVars['extensionkey'], $this->piVars['version'], $this->reviewer['username'].' leaves the review team.');
		if (strlen($reviewRecord['reviewers'])) {
			$this->db_updateReviewRecord($this->piVars['extensionkey'], $this->piVars['version'], array('reviewers' => $reviewRecord['reviewers']));
		} else {
			$this->db_deleteReviewRecord($this->piVars['extensionkey'], $this->piVars['version']);
		}

		return '';
	}

	/**
	 * Adds a user to the review team for a certain extension version
	 *
	 * @return	string		An empty string or a message, depending on the result of the command
	 * @access	protected
	 */
	protected function cmd_joinReviewTeam() {
		$reviewRecord = $this->db_getReviewRecord($this->piVars['extensionkey'], $this->piVars['version']);
		if ($reviewRecord === FALSE) return $this->renderSub_errorWrap($this->pi_getLL('error_reviewrecordnotfound','',1));

		if (t3lib_div::inList($reviewRecord['reviewers'], $this->reviewer['username'])) return $this->renderSub_errorWrap($this->pi_getLL('error_cantjoinalreadymember','',1));

		$reviewRecord['reviewers'] .= ','.$this->reviewer['username'];
		$this->db_updateReviewRecord($this->piVars['extensionkey'], $this->piVars['version'], array('reviewers' => $reviewRecord['reviewers']));
		$this->db_addReviewNote($this->piVars['extensionkey'], $this->piVars['version'], $this->reviewer['username'].' joins the review team.');

		return '';
	}

	/**
	 * Sets the rating of the current reviewer for the review of an extension version
	 *
	 * @return	string		An empty string or a message, depending on the result of the command
	 * @access	protected
	 */
	protected function cmd_setReviewRating() {
		$reviewRecord = $this->db_getReviewRecord($this->piVars['extensionkey'], $this->piVars['version']);
		if ($reviewRecord === FALSE) return $this->renderSub_errorWrap($this->pi_getLL('error_reviewrecordnotfound','',1));

		if (!t3lib_div::inList($reviewRecord['reviewers'], $this->reviewer['username'])) return $this->renderSub_errorWrap($this->pi_getLL('error_cantsetreviewratingnomember','',1));
		if (!strlen($this->piVars['reviewrating'])) return $this->renderSub_errorWrap($this->pi_getLL('error_cantsetreviewratingnorating','',1));

		$this->db_createReviewRatingRecord($this->piVars['extensionkey'], $this->piVars['version'], $this->piVars['reviewrating'], $this->reviewer['username']);
		$this->db_addReviewNote($this->piVars['extensionkey'], $this->piVars['version'], $this->reviewer['username'].' sets his rating to '.$this->piVars['reviewrating'].'.');
	}

	/**
	 * Removes the rating of the current reviewer for the review of an extension version
	 *
	 * @return	string		An empty string or a message, depending on the result of the command
	 * @access	protected
	 */
	protected function cmd_removeReviewRating() {
		$reviewRecord = $this->db_getReviewRecord($this->piVars['extensionkey'], $this->piVars['version']);
		if ($reviewRecord === FALSE) return $this->renderSub_errorWrap($this->pi_getLL('error_reviewrecordnotfound','',1));

		if (!t3lib_div::inList($reviewRecord['reviewers'], $this->reviewer['username'])) return $this->renderSub_errorWrap($this->pi_getLL('error_cantsetreviewratingnomember','',1));
		$this->db_deleteReviewRatingRecords($this->piVars['extensionkey'], $this->piVars['version'], $this->reviewer['username']);
		$this->db_addReviewNote($this->piVars['extensionkey'], $this->piVars['version'], $this->reviewer['username'].' revokes his rating.');
	}

	/**
	 * Adds a note to the review of an extension version
	 *
	 * @return	string		An empty string or a message, depending on the result of the command
	 * @access	protected
	 */
	protected function cmd_addReviewNote() {
		$reviewRecord = $this->db_getReviewRecord($this->piVars['extensionkey'], $this->piVars['version']);
		if ($reviewRecord === FALSE) return $this->renderSub_errorWrap($this->pi_getLL('error_reviewrecordnotfound','',1));

		if (!t3lib_div::inList($reviewRecord['reviewers'], $this->reviewer['username'])) return $this->renderSub_errorWrap($this->pi_getLL('error_cantaddnotenomember','',1));
		$this->db_addReviewNote($this->piVars['extensionkey'], $this->piVars['version'], 'Note: '.$this->piVars['reviewnote']);
	}





	/*********************************************************
	 *
	 * DATABASE RELATED FUNCTIONS
	 *
	 *********************************************************/

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
	 * Returns the review record for the given extension version. An additional
	 * field "_currentt3xfilemd5" will contain the current MD5 hash of the actual
	 * t3x file.
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @return	mixed		Review record or FALSE
	 * @access	protected
	 */
	protected function db_getReviewRecord ($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;

		$table = 'tx_terfe_reviews';
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			$table,
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, $table) .
			' AND version='.$TYPO3_DB->fullQuoteStr($version, $table)
		);

		if ($res) {
			$row =  $TYPO3_DB->sql_fetch_assoc ($res);
			if (is_array ($row)) return $row;
		}
		return FALSE;
	}

	/**
	 * Creates a new review record for the given extension version. If the extension
	 * version does not exist or a review record already exists, FALSE is returned
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @param	string		$reviewer: User name of the initial reviewer
	 * @return	boolean		TRUE or FALSE
	 * @access	protected
	 */
	protected function db_createReviewRecord ($extensionKey, $version, $reviewer) {
		global $TYPO3_DB;

		$res = $this->db_getReviewRecord ($extensionKey, $version);
		if ($res !== FALSE) return FALSE;

		$t3xPathAndFileName = $this->commonObj->getExtensionVersionPathAndBaseName($extensionKey, $version).'.t3x';
		$t3xFileMD5 = @md5_file ($t3xPathAndFileName);
		$reviewRow = array (
			'extensionkey' => $extensionKey,
			'version' => $version,
			'reviewers' => $reviewer,
			'tstamp' => time(),
			't3xfilemd5' => $t3xFileMD5
		);

		$res = $TYPO3_DB->exec_INSERTquery ('tx_terfe_reviews', $reviewRow);

		$this->db_addReviewNote($extensionKey, $version, 'Started a new review.');

		return $res ? TRUE : FALSE;
	}

	/**
	 * Deletes a review record for a given extension version. Also makes sure that the
	 * review state in the main repository is set correctly.
	 *
	 * @param	string		$extensionKey: Extension key the review is related to
	 * @param	string		$version: Version number of the extension
	 * @return	boolean		TRUE or FALSE if operation was not successful.
	 * @access	protected
	 */
	protected function db_deleteReviewRecord($extensionKey, $version){
		global $TYPO3_DB;

		$this->db_deleteReviewRatingRecords($extensionKey, $version);

		$res = $this->soap_setReviewState($extensionKey, $version, 0);
		if ($res !== TRUE) return FALSE;

		$res = $TYPO3_DB->exec_UPDATEquery(
			'tx_terfe_extensions',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensions') .
				' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_extensions'),
			array('reviewstate' => 0)
		);
		if (!$res) return FALSE;

		$res = $TYPO3_DB->exec_DELETEquery(
			'tx_terfe_reviews',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_reviews') .
				' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_reviews')
		);
		if (!$res) return FALSE;

		$res = $TYPO3_DB->exec_DELETEquery(
			'tx_terfe_reviewnotes',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_reviews') .
				' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_reviews')
		);
		if (!$res) return FALSE;

		return TRUE;
	}

	/**
	 * Updates a review record for a given extension version. Also makes sure that the
	 * review state in the main repository is set correctly.
	 *
	 * @param	string		$extensionKey: Extension key the review is related to
	 * @param	string		$version: Version number of the extension
	 * @param	array		$fieldsArr: An associative array of keys and values for the fields to be updated. Currently only "reviewers" is allowed!
	 * @return	boolean		TRUE or FALSE if operation was not successful.
	 * @access	protected
	 */
	protected function db_updateReviewRecord($extensionKey, $version, $fieldsArr){
		global $TYPO3_DB;

		$oldReviewRecord = $this->db_getReviewRecord ($extensionKey, $version);
		if ($oldReviewRecord === FALSE) return FALSE;

		$newReviewRow = array (
			'reviewers' => $fieldsArr['reviewers'],
			'tstamp' => time(),
		);
		$res = $TYPO3_DB->exec_UPDATEquery (
			'tx_terfe_reviews',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensions') .
				' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_extensions'),
			$newReviewRow
		);
		if ($res) return FALSE;

		if (!strlen($newReviewRecord['reviewers'])) {
			if (!$this->db_deleteReviewRecord($extensionKey, $version)) return FALSE;
		}

		return TRUE;
	}

	/**
	 * Returns all review rating records for the given extension version.
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @return	mixed		Review rating records or FALSE
	 * @access	protected
	 */
	protected function db_getReviewRatingRecords ($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;

		$table = 'tx_terfe_reviewratings';
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			$table,
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, $table) .
			' AND version='.$TYPO3_DB->fullQuoteStr($version, $table)
		);

		if ($res) {
			$reviewRatingRecords = array();
			while ($row = $TYPO3_DB->sql_fetch_assoc ($res)) {
				$row['rating'] = strlen($row['rating']) ? (integer)$row['rating'] : FALSE;
				$reviewRatingRecords[$row['reviewer']] = $row;
			}
			return $reviewRatingRecords;
		}
		return FALSE;
	}

	/**
	 * Creates a new review rating record for the review of the given extension version.
	 * If a review rating record for that extension version by the current reviewer already
	 * exists or another error occurred FALSE is returned
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @param	integer		$rating: Rating (reviewstate) to set. Must be either TX_TERFE_REVIEWSTATE_INSECURE or TX_TERFE_REVIEWSTATE_PASSED
	 * @param	string		$reviewer: User name of the reviewer
	 * @param	boolean		$recursive: If set, older versions will also be set to insecure if the current version is
	 * @return	boolean		TRUE or FALSE
	 * @access	protected
	 */
	protected function db_createReviewRatingRecord($extensionKey, $version, $rating, $reviewer,$recursive=TRUE) {
		global $TYPO3_DB;

		$res = $this->db_getReviewRecord ($extensionKey, $version);
		if ($res === FALSE) return FALSE;

		$reviewRatingRecords = $this->db_getReviewRatingRecords ($extensionKey, $version);
		if ($reviewRatingRecords === FALSE || isset($reviewRatingRecords[$reviewer])) return FALSE;
		if ($rating != TX_TERFE_REVIEWSTATE_INSECURE && $rating != TX_TERFE_REVIEWSTATE_PASSED) return FALSE;

		$reviewRatingRow = array (
			'extensionkey' => $extensionKey,
			'version' => $version,
			'rating' => $rating,
			'reviewer' => $reviewer,
			'tstamp' => time(),
		);
		$res = $TYPO3_DB->exec_INSERTquery ('tx_terfe_reviewratings', $reviewRatingRow);
		if ($res === FALSE) return FALSE;

		if (count($reviewRatingRecords) + 1 >= TX_TERFE_MINVOTES || $rating == TX_TERFE_REVIEWSTATE_INSECURE) {
			$res = $TYPO3_DB->exec_UPDATEquery(
				'tx_terfe_extensions',
				'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensions') .
					' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_extensions'),
				array('reviewstate' => $rating)
			);
			if (!$res) return FALSE;

			$res = $this->soap_setReviewState($extensionKey, $version, $rating);
			if ($res !== TRUE) return FALSE;
			// Reject older versions as well
			// 
			if ($rating == TX_TERFE_REVIEWSTATE_INSECURE && $recursive){
				$versionsArr = $this->db_getAllVersionNumbersOfExtension($extensionKey);
				if (is_array($versionsArr)) {
					foreach ($versionsArr as $otherversion) {
						if ($version > $otherversion) {
							$extensionRecord = $this->commonObj->db_getExtensionRecord($extensionKey, $otherversion);
							if (!$extensionRecord['reviewstate']){
								$this->db_createReviewRecord ($extensionKey, $otherversion, $this->reviewer['username']); 
								$this->db_addReviewNote ($extensionKey, $otherversion, 'Status set by inheritance from version '.$version);
								$this->db_createReviewRatingRecord($extensionKey, $otherversion, TX_TERFE_REVIEWSTATE_INSECURE,$this->reviewer['username'],0);
							}
						}
					}
				}
			}

		}
		return TRUE;
	}

	/**
	 * Deletes all review rating records for a given extension version or only those submitted by
	 * the specified reviewer. Also makes sure that the review state in the main repository is set
	 * correctly.
	 *
	 * @param	string		$extensionKey: Extension key the review is related to
	 * @param	string		$version: Version number of the extension
	 * @param	string		$reviewer: If set, only ratings submitted by this reviewer will be deleted
	 * @return	boolean		TRUE or FALSE if operation was not successful.
	 * @access	protected
	 */
	protected function db_deleteReviewRatingRecords($extensionKey, $version, $reviewer=NULL) {
		global $TYPO3_DB;

		$reviewRatingRecords = $this->db_getReviewRatingRecords ($extensionKey, $version);
		if ($reviewRatingRecords === FALSE) return FALSE;

		$res = $this->soap_setReviewState($extensionKey, $version, 0);
		if ($res !== TRUE) return FALSE;

		$res = $TYPO3_DB->exec_UPDATEquery(
			'tx_terfe_extensions',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensions') .
				' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_extensions'),
			array('reviewstate' => 0)
		);
		if (!$res) return FALSE;

		$addWhere = isset ($reviewer) ? ' AND reviewer='.$TYPO3_DB->fullQuoteStr($reviewer, 'tx_terfe_reviewratings') : '';

		$res = $TYPO3_DB->exec_DELETEquery(
			'tx_terfe_reviewratings',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_reviews') .
				' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_reviews') . $addWhere
		);
		if (!$res) return FALSE;

		return TRUE;
	}

	/**
	 * Updates the MD5 of a .T3X file stored in a review so it reflects the current MD5
	 * of the actual file.
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @return	boolean		TRUE or FALSE
	 * @access	protected
	 */
	protected function db_updateReviewRecord_t3xMD5($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;

		$reviewRecord = $this->db_getReviewRecord($extensionKey, $version);
		if ($reviewRecord === FALSE) return FALSE;

		$t3xPathAndFileName = $this->commonObj->getExtensionVersionPathAndBaseName($extensionKey, $version).'.t3x';
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

		$this->db_addReviewNote ($extensionKey, $version, 'Updated .T3X file MD5 (new MD5: '.$t3xFileMD5.')');

		return $res ? TRUE : FALSE;
	}

	/**
	 * Adds a note to a review. If the reviewer's username is not specified,
	 * the username of the currently logged in FE user will be taken instead.
	 *
	 * @param	integer		$reviewUid: UID of the review record
	 * @param	string		$note: The note
	 * @param	string		$reviewer: (optional) User name of the reviewer
	 * @param	[type]		$reviewer: ...
	 * @return	boolean		TRUE or FALSE
	 * @access	protected
	 */
	protected function db_addReviewNote ($extensionKey, $version, $note, $reviewer=NULL) {
		global $TYPO3_DB, $TSFE;

		$noteRow = array (
			'extensionkey' => $extensionKey,
			'version' => $version,
			'tstamp' => time(),
			'note' => $note,
			'reviewer' => ($reviewer === NULL ? $this->reviewer['username'] : $reviewer)
		);
		$res = $TYPO3_DB->exec_INSERTquery ('tx_terfe_reviewnotes', $noteRow);
		return $res ? TRUE : FALSE;
	}

	/**
	 * Returns all notes of a review.
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @return	mixed:		Array of review notes or FALSE if an error occurred
	 * @access	protected
	 */
	protected function db_getReviewNotes($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;

		$table = 'tx_terfe_reviewnotes';
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			$table,
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_reviewnotes') .
				' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_reviewnotes'),
			'',
			'tstamp DESC'
		);

		if ($res) {
			$reviewNotesArr = array();
			while ($row = $TYPO3_DB->sql_fetch_assoc ($res)) {
				$reviewNotesArr[] = $row;
			}
			return $reviewNotesArr;
		}
		return FALSE;
	}





	/*********************************************************
	 *
	 * SOAP FUNCTIONS
	 *
	 *********************************************************/

	/**
	 * Stores the new review state in the review record and sets it in the TER by issuing
	 * a command via SOAP
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @param	integer		$reviewState: The new review state
	 * @return	boolean		TRUE or FALSE
	 * @access	protected
	 */
	protected function soap_setReviewState($extensionKey, $version, $reviewState) {
		global $TYPO3_DB;

		if ($this->conf['noSoap']) return TRUE;

		$soapClientObj = new SoapClient ($this->WSDLURI, array ('exceptions' => TRUE));
		try {
			$accountDataArr = array (
				'username' => $this->reviewer['username'],
				'password' => $this->reviewer['password']
			);
			$reviewStateDataArr = array (
				'extensionKey' => (string)$extensionKey,
				'version' => (string)$version,
				'reviewState' => (integer)$reviewState,
			);
			$soapClientObj->setReviewState($accountDataArr, $reviewStateDataArr);
		} catch (SoapFault $exception) {
			$this->db_addReviewNote($extensionKey, $version, 'Setting the review state via SOAP FAILED! ('.$exception->faultstring.')');
			return FALSE;
		}
		$this->db_addReviewNote($extensionKey, $version, 'Successfully updated review state via SOAP (reviewstate = '.$reviewState.').');

			// Send a notification email:
		$subjectAndMessage = '[TER2][review] '.$extensionKey.' ('.$version.') review state changed to '.$reviewState.'.
The review state for the extension '.$extensionKey.' ('.$version.') has been set to '.$reviewState.'. Reviewer: '.$this->reviewer['username'].'
';
		$this->cObj->sendNotifyEmail($subjectAndMessage, $this->notificationEmail_recipient, $this->notificationEmail_sender, $this->notificationEmail_replyTo, 'TER2 Security review framework');


		return TRUE;
	}

	/**
	 * Calculates and returns the current review state of the specified extension version
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @return	mixed		The review state (one of TX_TERFE_REVIEWSTATE*).
	 * @access	protected
	 */
	protected function getReviewState($extensionKey, $version){

			// Check if the t3x md5 has changed in the meantime!
		$reviewRecord = $this->db_getReviewRecord($extensionKey, $version);
		$t3xPathAndFileName = $this->commonObj->getExtensionVersionPathAndBaseName($extensionKey, $version).'.t3x';
		$t3xFileMD5 = @md5_file ($t3xPathAndFileName);

		if ($t3xFileMD5 != $reviewRecord['t3xfilemd5']) {
#			$this->db_addReviewNote($extensionKey, $version, 'MD5 sum of this extension version has changed! Resetting review ratings ...');
			$this->db_addReviewNote($extensionKey, $version, 'MD5 sum of this extension version has changed! Because that might have happened during the launch of TER2, I just set update the MD5 in the database.');
#			$this->db_deleteReviewRatingRecords($extensionKey, $version);
			$this->db_updateReviewRecord_t3xMD5($extensionKey, $version);
		}

		$reviewRatingRecords = $this->db_getReviewRatingRecords($extensionKey, $version);
		if ($reviewRatingRecords === FALSE ) return TX_TERFE_REVIEWSTATE_UNREVIEWED;

		$positiveRatingsCount = 0;
		foreach ($reviewRatingRecords as $reviewRatingRecord) {
			if ($reviewRatingRecord['rating'] === TX_TERFE_REVIEWSTATE_INSECURE) return TX_TERFE_REVIEWSTATE_INSECURE;
			$positiveRatingsCount ++;
		}

		if ($positiveRatingsCount >= TX_TERFE_MINVOTES) return TX_TERFE_REVIEWSTATE_PASSED;

		return 	TX_TERFE_REVIEWSTATE_PENDING;
	}





	/*********************************************************
	 *
	 * ICON FUNCTIONS
	 *
	 *********************************************************/

	/**
	 * Returns the proper review state image for the state given
	 *
	 * @param	integer		$reviewState: The state (one of TX_TERFE_REVIEWSTATE_*)
	 * @return	string		HTML image tag
	 * @access	protected
	 */
	protected function getIcon_reviewState($reviewState) {
		switch (TRUE) {
			case ($reviewState === TX_TERFE_REVIEWSTATE_INSECURE) :		$file = 'redled.gif'; break;
			case ($reviewState === TX_TERFE_REVIEWSTATE_PENDING) :		$file = 'yellowled.gif'; break;
			case ($reviewState === TX_TERFE_REVIEWSTATE_PASSED) :		$file = 'greenled.gif'; break;
			case ($reviewState === TX_TERFE_REVIEWSTATE_UNREVIEWED) :	$file = 'greyled.gif'; break;
			default : $file = 'greyled.gif';
		}
		return '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/'.$file.'" width="16" height="16" alt="" title="" style="vertical-align: middle; padding-right:6px;" />';
	}

	/**
	 * Returns a vertical bar of the color matching the given review state
	 *
	 * @param	integer		$reviewState: The state (one of TX_TERFE_REVIEWSTATE_*)
	 * @return	string		HTML code
	 * @access	protected
	 */
	protected function getIcon_reviewStateBar($reviewState) {
		switch (TRUE) {
			case ($reviewState === TX_TERFE_REVIEWSTATE_INSECURE) :		$color = TX_TERFE_COLOR_INSECURE; break;
			case ($reviewState === TX_TERFE_REVIEWSTATE_PENDING) :		$color = TX_TERFE_COLOR_PENDING; break;
			case ($reviewState === TX_TERFE_REVIEWSTATE_PASSED) :			$color = TX_TERFE_COLOR_PASSED; break;
			case ($reviewState === TX_TERFE_REVIEWSTATE_UNREVIEWED) :	$color = TX_TERFE_COLOR_UNREVIEWED; break;
			default : $file = 'greyled.gif';
		}
		return '<span style="background-color:'.$color.';">&nbsp;&nbsp;</span>';
	}





	/*********************************************************
	 *
	 * MISCELLANEOUS
	 *
	 *********************************************************/

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi3/class.tx_terfe_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi3/class.tx_terfe_pi3.php']);
}

?>
