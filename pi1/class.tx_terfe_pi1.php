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
 * Plugin 'TER Frontend' for the 'ter_fe' extension.
 *
 * $Id$
 *
 * @author	Robert Lemke <robert@typo3.org>
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

if (t3lib_extMgm::isLoaded ('ter_doc')) {
	require_once (t3lib_extMgm::extPath('ter_doc').'class.tx_terdoc_api.php');			
}

class tx_terfe_pi1 extends tslib_pibase {

	public		$prefixId = 'tx_terfe_pi1';											// Same as class name
	public		$scriptRelPath = 'pi1/class.tx_terfe_pi1.php';						// Path to this script relative to the extension dir.
	public		$extKey = 'ter_fe';													// The extension key.
	public		$pi_checkCHash = TRUE;												// Handle empty CHashes correctly
	
	protected	$repositoryDir = '';												// Full path to the extension repository files
	protected	$baseDirT3XContentCache = '';										// Full path to T3X content cache
	protected	$viewMode = '';														// View mode, one of the following: LATEST, CATEGORIES, FULLLIST
	
	protected	$validStates = 'alpha,beta,stable,experimental,test,obsolete';		// List of valid development states
	protected	$feedbackMailsCCAddress = 'robert@typo3.org';						// Email address(es) which also receive the feedback emails	

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
		$this->pi_setPiVarDefaults(); 			// Set default piVars from TS
		$this->pi_initPIflexForm();				// Init FlexForm configuration for plugin
		$this->pi_loadLL();
		
		$this->repositoryDir = $this->conf['repositoryDirectory'];
		if (substr ($this->repositoryDir, -1, 1) != '/') $this->repositoryDir .= '/';
		
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
		$this->init($conf);

		if (!@is_dir ($this->repositoryDir)) return 'TER_FE Error: Repository directory ('.$this->repositoryDir.') does not exist!';			
		if ($this->extensionIndex_wasModified ()) {
			$this->extensionIndex_updateDB ();	
		}

			// Prepare the top menu items:
		if (!$this->piVars['view']) $this->piVars['view'] = 'new';
		$menuItems = array ('new',  'popular', 'fulllist', 'search', 'unsupported'); # FIXME: disabled: categories

			// Render the top menu		
		$topMenu = '';
		foreach ($menuItems as $itemKey) {
			$itemActive = ($this->piVars['view'] == $itemKey);
			$link = $this->pi_linkTP($this->pi_getLL('views_'.$itemKey,'',1), array('tx_terfe_pi1[view]' => $itemKey), 1);
			$topMenu .='<span '.($itemActive ? 'class="submenu-button-active"' :'class="submenu-button"').'>'.$link.'</span>';
		}
		
		if ($this->piVars['showExt']) {
			$subContent = $this->renderSingleView_extension ($this->piVars['showExt'], $this->piVars['version']);			
		} else {
			switch ($this->piVars['view']) {
				case 'new':		$subContent = $this->renderListView_new(); break;
				case 'categories': 		$subContent = $this->renderListView_categories(); break;
				case 'popular': 		$subContent = $this->renderListView_popular(); break;
				case 'fulllist':		$subContent = $this->renderListView_fullList(); break;
				case 'search':			$subContent = $this->renderListView_search(); break;
				case 'unsupported':		$subContent = $this->renderListView_unsupported(); break;
			}
		}
		
			// Put everything together:
		$content = '
			<h2>'.$this->pi_getLL('general_extensionrepository', '', 1).'</h2>
			<br />			
			'.$topMenu.'<br />
			<br />
			'.$subContent.'
		';

		return $this->pi_wrapInBaseClass($content);
	}



	/**
	 * Renders a list of extensions which have been recently uploaded to
	 * the repository
	 * 
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderListView_new() {
		global $TYPO3_DB, $TSFE;

		$numberOfDays = 50;
		$tableRows = array ();	

				// Set the magic "reg1" so we can clear the cache for this manual if a new one is uploaded:		
		if (t3lib_extMgm::isLoaded ('ter_doc')) {
			$terDocAPIObj = tx_terdoc_api::getInstance();
			$TSFE->page_cache_reg1 = $terDocAPIObj->createAndGetCacheUidForExtensionVersion ('_all','');
		}

		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			'tx_terfe_extensions',
			'lastuploaddate > '.(time()-($numberOfDays*24*3600)).' AND reviewstate > 0',
			'',
			'lastuploaddate DESC',
			''
		);
		$alreadyRenderedExtensionKeys = array();
		if ($res) {
			while ($extensionRow = $TYPO3_DB->sql_fetch_assoc ($res)) {
				if (!t3lib_div::inArray ($alreadyRenderedExtensionKeys, $extensionRow['extensionkey'])) {
					$tableRows[] = $this->renderListView_detailledExtensionRow ($extensionRow);
					$alreadyRenderedExtensionKeys[] = $extensionRow['extensionkey'];
				}
			}
		}

		$searchForm = '
			<form action="'.$this->pi_getPageLink($TSFE->id).'" method="get">
				<input type="hidden" name="tx_terfe_pi1[view]" value="search" />
				<input type="hidden" name="no_cache" value="1" />
				<input type="text" name="tx_terfe_pi1[sword]" size="20" />
				<input type="submit" value="'.$this->pi_getLL('listview_search_searchbutton','',1).'" />
			</form>
		';
		
		$content.= '
			'.$searchForm.'
			<p>'.htmlspecialchars(sprintf($this->pi_getLL('listview_new_introduction',''), $numberOfDays)).'</p>
			<table cellspacing="0" style="margin-top:10px;">
			'.implode('', $tableRows).'
			</table>
		';
		return $content;
	}

	/**
	 * Renders a list of extensions grouped by categories
	 * 
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderListView_categories() {
		global $TYPO3_DB;

		$numberOfDays = 50;
		$tableRows = array ();	

		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			'tx_terfe_extensions',
			'lastuploaddate > '.(time()-($numberOfDays*24*3600)),
			'',
			'lastuploaddate DESC',
			''
		);
		$alreadyRenderedExtensionKeys = array();
		if ($res) {
			while ($extensionRow = $TYPO3_DB->sql_fetch_assoc ($res)) {
				if (!t3lib_div::inArray ($alreadyRenderedExtensionKeys, $extensionRow['extensionkey'])) {
					$tableRows[] = $this->renderListView_detailledExtensionRow ($extensionRow);
					$alreadyRenderedExtensionKeys[] = $extensionRow['extensionkey'];
				}
			}
		}

		$content.= '
			<p>'.htmlspecialchars(sprintf($this->pi_getLL('renderview_new_introduction',''), $numberOfDays)).'</p>
			<table cellspacing="0" style="margin-top:10px;">
			'.implode('', $tableRows).'
			</table>
		';
		return $content;
	}

	/**
	 * Renders a list of extensions sorted by popularity
	 * 
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderListView_popular() {
		global $TYPO3_DB;

		$tableRows = array ();	

				// Set the magic "reg1" so we can clear the cache for this manual if a new one is uploaded:		
		if (t3lib_extMgm::isLoaded ('ter_doc')) {
			$terDocAPIObj = tx_terdoc_api::getInstance();
			$TSFE->page_cache_reg1 = $terDocAPIObj->createAndGetCacheUidForExtensionVersion ('_all','');
		}

		$res = $TYPO3_DB->exec_SELECTquery (
			'DISTINCT extensionkey',
			'tx_terfe_extensions',
			'reviewstate > 0',
			'',
			'extensiondownloadcounter DESC',
			'0,20'
		);
		if ($res) {
			while ($extensionKeyRow = $TYPO3_DB->sql_fetch_assoc ($res)) {
				$version = $this->db_getLatestVersionNumberOfExtension ($extensionKeyRow['extensionkey']);

				$res2 = $TYPO3_DB->exec_SELECTquery (
					'*',
					'tx_terfe_extensions',
					'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKeyRow['extensionkey'], 'tx_terfe_extensions').' AND '.
					'version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_extensions')
				);
				if (!$res2) return 'Extension '.htmlspecialchars($extensionKeyRow['extensionkey']).' not found!';	
				$extensionRow = $TYPO3_DB->sql_fetch_assoc ($res2);
				$tableRows[] = $this->renderListView_detailledExtensionRow ($extensionRow);
			}
		}

		$content.= '
			<p>'.$this->pi_getLL('listview_popular_introduction','',1).'</p>
			<table cellspacing="0" style="margin-top:10px;">
			'.implode('', $tableRows).'
			</table>
		';
		return $content;
	}

	/**
	 * Renders a list of extensions based on a search
	 * 
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderListView_search() {
		global $TYPO3_DB, $TSFE;

		$searchForm = '
			<form action="'.$this->pi_getPageLink($TSFE->id).'" method="get">
				<input type="hidden" name="tx_terfe_pi1[view]" value="search" />
				<input type="hidden" name="no_cache" value="1" />
				<input type="text" name="tx_terfe_pi1[sword]" size="20" />
				<input type="submit" value="'.$this->pi_getLL('listview_search_searchbutton','',1).'" />
			</form>
		';

		$searchResult = strlen(trim($this->piVars['sword'])) > 2 ? $this->renderListView_searchResult() : '';
		
		$content.= '
			'.$searchForm.'
			'.$searchResult.'
		';
		return $content;
	}

	/**
	 * Renders the actual search result
	 * 
	 * @return	string		HTML table with search result
	 * @access	protected
	 * @see renderListView_search
	 */
	protected function renderListView_searchResult() {
		global $TYPO3_DB;

		$tableRows = array ();	

		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			'tx_terfe_extensions',
			$TYPO3_DB->searchQuery (explode (' ', $this->piVars['sword']), array('extensionkey','title','description'), 'tx_terfe_extensions').' AND reviewstate > 0',
			'',
			'lastuploaddate DESC',
			'0,30'
		);

		if ($res) {
			$alreadyRenderedExtensionKeys = array();
			if ($TYPO3_DB->sql_num_rows($res)) {
				while ($extensionRow = $TYPO3_DB->sql_fetch_assoc ($res)) {
					if (!t3lib_div::inArray ($alreadyRenderedExtensionKeys, $extensionRow['extensionkey'])) {
						$tableRows[] = $this->renderListView_detailledExtensionRow ($extensionRow);
						$alreadyRenderedExtensionKeys[] = $extensionRow['extensionkey'];
					}
				}
				$output = '
					<table cellspacing="0" style="margin-top:10px;">
					'.implode('', $tableRows).'
					</table>
				';		
			} else {
				$output = $this->pi_getLL('listview_search_noresult','',1);			
			}
		}

		return $output;
	}

	/**
	 * Renders a full list of all available extensions
	 * 
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderListView_fullList() {
		global $TYPO3_DB, $TSFE;

		$tableRows = array ();	

		$res = $TYPO3_DB->exec_SELECTquery (
			'extensionkey,title,version',
			'tx_terfe_extensions',
			'state <> "obsolete" AND reviewstate > 0',
			'',
			'title ASC',
			''
		);
		$alreadyRenderedExtensionKeys = array();

		if ($res) {

					// Set the magic "reg1" so we can clear the cache for this manual if a new one is uploaded:		
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
			<p>'.$this->pi_getLL('listview_fulllist_introduction','',1).'</p>
			<table style="margin-top:10px;">
				<th class="th-main">&nbsp;</th>
				<th class="th-main">'.$this->pi_getLL('extension_title','',1).'</th>
				<th class="th-main">'.$this->pi_getLL('extension_extensionkey','',1).'</th>
				<th class="th-main">'.$this->pi_getLL('extension_version','',1).'</th>
				<th class="th-main">'.$this->pi_getLL('extension_documentation','',1).'</th>
			'.implode('', $tableRows).'
			</table>
		';
		return $content;
	}

	/**
	 * Renders a list of all unsupported (ie. not reviewed) extensions
	 * 
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderListView_unsupported() {
		global $TYPO3_DB, $TSFE;

		$tableRows = array ();	

		$res = $TYPO3_DB->exec_SELECTquery (
			'extensionkey,title,version',
			'tx_terfe_extensions',
			'state <> "obsolete" AND reviewstate = 0',
			'',
			'title ASC',
			''
		);
		$extensionVersionsToRender = array();

		if ($res) {

					// Set the magic "reg1" so we can clear the cache for this manual if a new one is uploaded:		
			if (t3lib_extMgm::isLoaded ('ter_doc')) {
				$terDocAPIObj = tx_terdoc_api::getInstance();
				$TSFE->page_cache_reg1 = $terDocAPIObj->createAndGetCacheUidForExtensionVersion ('_all','');
			}

			while ($extensionRow = $TYPO3_DB->sql_fetch_assoc ($res)) {
				if (version_compare($extensionRow['version'], $extensionVersionsToRender[$extensionRow['extensionkey']]['version'], '>') ) {
					$extensionVersionsToRender[$extensionRow['extensionkey']] = $extensionRow;
				}
			}

			foreach ($extensionVersionsToRender as $extensionRow) {
				$tableRows[] = $this->renderListView_shortExtensionRow ($extensionRow);
			}
		}

		$content.= '
			<p>'.$this->pi_getLL('listview_unsupported_introduction','',1).'</p><br />
			<p style="color:red; font-weight:bold;">'.$this->pi_getLL('listview_unsupported_introduction_warning','',1).'</p>
			<table style="margin-top:10px;">
				<th class="th-main">&nbsp;</th>
				<th class="th-main">'.$this->pi_getLL('extension_title','',1).'</th>
				<th class="th-main">'.$this->pi_getLL('extension_extensionkey','',1).'</th>
				<th class="th-main">'.$this->pi_getLL('extension_version','',1).'</th>
				<th class="th-main">'.$this->pi_getLL('extension_documentation','',1).'</th>
			'.implode('', $tableRows).'
			</table>
		';
		return $content;
	}





	/**
	 * Renders the single view for (the latest version of) an extension including several sub views.
	 * 
	 * @param	string		$extensionKey: The extension key of the extension to render
	 * @param	string		$version: Version number of the extension or an empty string for displaying the most recent reviewed version
	 * @return	string		HTML output
	 */
	protected function renderSingleView_extension ($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;

		if (!strlen($version)) $version = $this->db_getLatestVersionNumberOfExtension ($extensionKey);

			// Fetch the extension record:
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			'tx_terfe_extensions',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensions').' AND '.
			'version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_extensions')
		);
		if (!$res) return 'Extension '.htmlspecialchars($extensionKey).' not found!';	
		$extRow = $this->db_prepareExtensionRowForOutput ($TYPO3_DB->sql_fetch_assoc ($res));

				// Set the magic "reg1" so we can clear the cache for this manual if a new one is uploaded:		
		if (t3lib_extMgm::isLoaded ('ter_doc')) {
			$terDocAPIObj = tx_terdoc_api::getInstance();
			$TSFE->page_cache_reg1 = $terDocAPIObj->createAndGetCacheUidForExtensionVersion ($extRow['extensionkey'], $extRow['version']);
		}

			// Prepare the top menu items:
		if (!$this->piVars['extView']) $this->piVars['extView'] = 'info';
		$menuItems = array ('info', 'details', 'feedback','rating');

			// Render the top menu		
		$topMenu = '';
		foreach ($menuItems as $itemKey) {
			$itemActive = ($this->piVars['extView'] == $itemKey);
			$link = $this->pi_linkTP_keepPIvars($this->pi_getLL('extensioninfo_views_'.$itemKey,'',1), array('showExt' => $extensionKey, 'extView' => $itemKey, 'viewFile' => ''), 1);
			$topMenu .='<span '.($itemActive ? 'class="submenu-button-active"' :'class="submenu-button"').'>'.$link.'</span>';
		}

			// Render content of the currently selected view:
		switch ($this->piVars['extView']) {
			case 'details' :
				$subContent = $this->renderSingleView_extensionDetails ($extRow);
			break;
			case 'feedback' :
				$subContent = $this->renderSingleView_feedbackForm ($extRow);
				break;

				// ADDED BY MICHAEL SCHARKOW, just using another class for all the rating stuff!
			case 'rating':
				require_once('class.tx_terfe_ratings.php');
				$rating = new tx_terfe_ratings($extRow,$this);
				$subContent = $rating->renderSingleView_rating();
					
			break;	
			case 'info':
			default:
				$subContent = $this->renderSingleView_extensionInfo ($extRow);
		}

			// Put everything together:
		$content ='
			<h3>'.$extRow['title'].'</h3><br />			
			<p>'.$topMenu.'</p><br />
			'.$subContent.'<br />
			<p class="text-button">'.$this->pi_linkTP_keepPIvars($this->pi_getLL('general_back','',1), array('showExt'=>'','extview'=>''),1).'</p>
		';
		
		return $content;
	}

	/**
	 * Renders the sub view "Info" of an extension single view
	 * 
	 * @param	string		$extensionKey: The extension key of the extension to render
	 * @return	string		HTML output
	 */
	protected function renderSingleView_extensionInfo($extRow) {
		global $TSFE;

		if (t3lib_extMgm::isLoaded ('ter_doc')) {			
			if (t3lib_extMgm::isLoaded ('ter_doc_html')) {
				$terDocAPIObj = tx_terdoc_api::getInstance();
				$documentationIndex = $terDocAPIObj->getRenderedTOC ($extRow['extensionkey'], $extRow['version']);

					// Set the magic "reg1" so we can clear the cache for this manual if a new one is uploaded:		
				$terDocAPIObj = tx_terdoc_api::getInstance();
				$TSFE->page_cache_reg1 = $terDocAPIObj->createAndGetCacheUidForExtensionVersion ($extRow['extensionkey'], $extRow['version']);
				
			} else {
				$documentationIndex = '<strong style="color:red;">'.$this->pi_getLL('general_terdochtmlnotinstalled','',1).'</strong>';
			}
		} else {
			$documentationIndex = '<strong style="color:red;">'.$this->pi_getLL('general_terdocnotinstalled','',1).'</strong>';
		} 

		$content ='
			<table>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_extensionkey','',1).':</th>
					<td class="td-sub" style="width:90%;"><em>'.$extRow['extensionkey'].'</em></td>
					<td class="td-sub" rowspan="4">
						<table>
							<tr><th nowrap="nowrap" class="th-sub">'.$this->pi_getLL('extension_state','',1).':</th><td>'.$this->getIcon_state($extRow['state_raw']).'</td></tr>
							<tr><th nowrap="nowrap" class="th-sub">'.$this->pi_getLL('extension_version','',1).':</th><td>'.$extRow['version'].'</td></tr>
							<tr><th nowrap="nowrap" class="th-sub">'.$this->pi_getLL('extension_category','',1).':</th><td>'.$this->pi_getLL('extension_category_'.$extRow['category']).'</td></tr>
						</table>
					</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_description','',1).':</th>
					<td class="td-sub">'.$extRow['description'].'</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_ownerusername','',1).':</th>
					<td class="td-sub">'.$extRow['ownerusernameandname'].'</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extensioninfo_documentation','',1).':</th>
					<td class="td-sub" valign="top">'.$documentationIndex.'</td>
				</tr>
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_uploadcomment').':</th>
					<td class="td-sub" colspan=2>'.$extRow['uploadcomment'].'</td>
				</tr>
			</table>
		';
		
		return $content;
	}

	/**
	 * Renders the details sub view of an extension single view
	 *
	 * @param	array		$extRow: The extension record
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderSingleView_extensionDetails ($extRow) {
		global $TSFE;

		$extDetailsRow = $this->db_getExtensionDetails ($extRow['extensionkey'], $extRow['version']);

				// Set the magic "reg1" so we can clear the cache for this manual if a new one is uploaded:		
		if (t3lib_extMgm::isLoaded ('ter_doc')) {
			$terDocAPIObj = tx_terdoc_api::getInstance();
			$TSFE->page_cache_reg1 = $terDocAPIObj->createAndGetCacheUidForExtensionVersion ($extRow['extensionkey'], $extRow['version']);
		}

			// Compile detail rows information:
		$detailRows = '';
		$detailsArr = array (
			'extension_extensionkey' => $extRow['extensionkey'],		
			'extension_version' => $extRow['version'],		
			'extension_category' => $extRow['category'],		
			'extension_state' => $extRow['state'],		
			'extension_reviewstate' => $extRow['reviewstate'],		
			'extension_dependencies' => $this->getRenderedDependencies ($extRow['dependencies']),
			'extension_reversedependencies' => $this->getRenderedReverseDependencies ($extRow['extensionkey'], $extRow['version']),		
			'extension_lastuploaddate' => $extRow['lastuploaddate'],		
			'extension_uploadcomment' => $extRow['uploadcomment'],		
			'extension_files' => $this->getRenderedListOfFiles ($extDetailsRow),		
		);

		foreach ($detailsArr as $llKey => $value) {
			$detailRows .= '
				<tr>
					<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL($llKey,'',1).':</th>
					<td class="td-sub">'.$value.'</td>
				</tr>
			';
		}

			// Put everything together
		$content = '
			<table>
				'.$detailRows.'
			</table>
		';

		return $content;
	}

	/**
	 * Renders the feedback sub view of an extension single view
	 *
	 * @param	array		$extRow: The extension record
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderSingleView_feedbackForm ($extRow) {
		global $TSFE;

		$TSFE->no_cache = 1;

		$extDetailsRow = $this->db_getExtensionDetails ($extRow['extensionkey'], $extRow['version']);
		$authorName = htmlspecialchars($extRow['authorname']); 
		$defaultMessage = 'Hi '.$extRow['authorname'].','.chr(10).chr(10).'...'.chr(10).chr(10).'Best regards'.chr(10).$TSFE->fe_user->user['name'].' ('.$TSFE->fe_user->user['username'].')';

		if (is_array($this->piVars['DATA']) && trim($this->piVars['DATA']['comment']) && trim($this->piVars['DATA']['sender_email']) && strcmp(trim(ereg_replace('[[:space:]]','',$defaultMessage)),trim(ereg_replace('[[:space:]]','',$this->piVars['DATA']['comment']))))	{
			
				session_start();
				$captchaString = $_SESSION['tx_captcha_string'];
				$_SESSION['tx_captcha_string']='';
						
				if (t3lib_div::validEmail($this->piVars['DATA']['sender_email'])) {
					if ($captchaString == $this->piVars['DATA']['captcha']) {
						$message = 'TER feedback - '.$extRow['extension_key'].chr(10).trim($this->piVars['DATA']['comment']);
						$this->cObj->sendNotifyEmail($message, $extRow['authoremail'], $this->feedbackMailsCCAddress, $this->piVars['DATA']['sender_email'], $this->piVars['DATA']['sender_name']);
	
						$content ='
							<h3>'.$this->pi_getLL('extensioninfo_feedback_emailsent','',1).'</h3>
							<p>'.htmlspecialchars(sprintf($this->pi_getLL('extensioninfo_feedback_emailsent_details'), $extRow['authoremail'])).'</p>
						';
					} else $content = '<p>'.$this->pi_getLL('extensioninfo_feedback_invalidcaptcha','',1).'</p>';
				} else $content = '<p>'.htmlspecialchars(sprintf($this->pi_getLL('extensioninfo_feedback_invalidemailaddress'), $this->piVars['DATA']['sender_email'])).'</p>';
			} else {				
				$content.='
					<h3>'.$this->pi_getLL('extensioninfo_feedback_feedbacktotheauthor','', 1).'</h3>
					<p>'.htmlspecialchars(sprintf ($this->pi_getLL('extensioninfo_feedback_introduction'), $authorName)).'</p>
					<p>'.$this->pi_getLL('extensioninfo_feedback_moreintroduction','',1).'</p>
				
					<form action="'.t3lib_div::getIndpEnv('REQUEST_URI').'" method="POST" style="margin: 0px 0px 0px 0px;">
						<br />
						<p><strong>'.$this->pi_getLL('extensioninfo_feedback_yourname','',1).':</strong></p>
						'.($TSFE->loginUser ?
							'<input type="hidden" name="'.$this->prefixId.'[DATA][sender_name]" value="'.htmlspecialchars($TSFE->fe_user->user['name'].' ('.$TSFE->fe_user->user['username']).')" />
							 <p>'.htmlspecialchars($TSFE->fe_user->user['name'].' ('.$GLOBALS['TSFE']->fe_user->user['username'].')').'</p>' :
							'<input type="text" name="'.$this->prefixId.'[DATA][sender_name]" style="width: 400px;" /><br />').
						'<br />
			
						<p><strong>'.$this->pi_getLL('extensioninfo_feedback_youremailaddress','',1).':</strong></p>
						'.($TSFE->loginUser ?
							'<input type="hidden" name="'.$this->prefixId.'[DATA][sender_email]" value="'.htmlspecialchars($TSFE->fe_user->user['email']).'" />
							 <p>'.htmlspecialchars($TSFE->fe_user->user['email']).'</p>' :
							'<input type="text" name="'.$this->prefixId.'[DATA][sender_email]" style="width: 400px;"><br />').
						'<br />
			
						<p><strong>'.$this->pi_getLL('extensioninfo_feedback_yourcomment','',1).':</strong></p>
						<textarea rows="5" name="'.$this->prefixId.'[DATA][comment]" style="width: 400px;">'.$defaultMessage.'</textarea><br /><br />
						<p>'.$this->pi_getLL('extensioninfo_feedback_captchainstruction','',1).':<br />
							<img src="'.t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php" alt="" style="vertical-align:middle;" />
							<input type="text" name="'.$this->prefixId.'[DATA][captcha]" size="10" />
							<input type="submit" value="'.$this->pi_getLL('extensioninfo_feedback_sendfeedback','',1).'">
						</p>
					</form>
				';
			}

		return $content;
	}

	/**
	 * Render the detailled extension info row for listing of categories, news etc.
	 *
	 * @param	array		$extRow: Database record from tx_terfe_extensions
	 * @return	string		Two HTML table rows wrapped in <tr>
	 */
	protected function renderListView_detailledExtensionRow($extRow)	{
		global $TSFE;

		if (t3lib_extMgm::isLoaded ('ter_doc')) {			
			$terDocAPIObj = tx_terdoc_api::getInstance();
			$documentationLink = $terDocAPIObj->getDocumentationLink ($extRow['extensionkey'], $extRow['version']);
		} else {		
			$documentationLink = '<span style="color:red;">'.$this->pi_getLL('general_terdocnotinstalled','',1).'</style>';
		} 

		$extRow = $this->db_prepareExtensionRowForOutput ($extRow);
		$tableRows = '
			<tr>
				<th class="th-main">'.$this->getIcon_extension ($extRow['extensionkey'], $extRow['version']).'</th>
				<th class="th-main" colspan ="2">'.$this->pi_linkTP($extRow['title'], array('tx_terfe_pi1[view]' => 'search', 'tx_terfe_pi1[showExt]' => $extRow['extensionkey'], 'tx_terfe_pi1[version]' => $extRow['version']),1).' - <em>'.$extRow['extensionkey'].'</em></th>
				<th class="th-main" style="text-align:right;">'.$this->getIcon_state($extRow['state_raw']).'</th>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td style="width:55%;">
					<table style="width: 100%">
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_authorname','',1).':</th><td class="td-sub" nowrap="nowrap">'.$extRow['authorname'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_category','',1).':</th><td class="td-sub" nowrap="nowrap">'.$extRow['category'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_version','',1).':</th><td class="td-sub" nowrap="nowrap">'.$extRow['version'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_downloads','',1).':</th><td class="td-sub" nowrap="nowrap">'.$extRow['versiondownloadcounter'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_lastuploaddate','',1).':</th><td class="td-sub" nowrap="nowrap">'.$extRow['lastuploaddate'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_uploadcomment','',1).':</th><td style="width:60%;" class="td-sub">'.$extRow['uploadcomment'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_documentation','',1).':</th><td style="width:60%;" class="td-sub">'.$documentationLink.'</td></tr>
					</table>
				</td>
				<td colspan="2">
					<p>'.$extRow['description'].'</p>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td colspan="3">'.'<br /><br /></td>
			</tr>
		';

		return $tableRows;
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
				<td class="td-sub" nowrap="nowrap">'.$this->pi_linkTP_keepPIvars($extRow['title'], array('showExt' => $extRow['extensionkey'], 'version' => $extRow['version']),1).'</td>
				<td class="td-sub">'.$extRow['extensionkey'].'</td>
				<td class="td-sub">'.$extRow['version'].'</td>
				<td class="td-sub" nowrap="nowrap">'.$documentationLink.'</td>
			</tr>
		';

		return $tableRows;
	}







	/**
	 * Renders dependency information for frontend output from the given 
	 * depencies array
	 * 
	 * @param	array		$dependenciesArr: The dependencies
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function getRenderedDependencies ($dependenciesArr) {
		global $TYPO3_DB, $TSFE;
				
		$output = '';
		if (is_array ($dependenciesArr)) {			
			$alwaysAvailableExtensions = 'php,typo3,cms,lang';
			$someExtensionsAreNotAvailable = FALSE;
			$tableRows = array ();
			foreach ($dependenciesArr as $dependencyArr) {				
				
				if (strlen ($dependencyArr['extensionKey'])) {
						// Check if an extension within the version range exists in the official repository:
					if (t3lib_div::inList ($alwaysAvailableExtensions, $dependencyArr['extensionKey'])) {
						$extensionIsAvailable = TRUE;
					} else { 
						$extensionIsAvailable = FALSE;			
						$res = $TYPO3_DB->exec_SELECTquery (
							'extensionkey, version',
							'tx_terfe_extensions',
							'extensionkey='.$TYPO3_DB->fullQuoteStr($dependencyArr['extensionkey'], 'tx_terfe_extensions')
						);
						if ($res) {
							if ($TYPO3_DB->sql_num_rows($res) && strlen($dependencyArr['versionRange'] == 0)) {
								$extensionIsAvailable = TRUE;	
							} else {
								if (strstr ($dependencyArr['versionRange'], '-') !== FALSE) {
									list ($lowerRange, $upperRange) = explode ('-',$dependencyArr['versionRange']);  	
								} elseif (strlen($dependencyArr['versionRange'])){
									$lowerRange = $upperRange = $dependencyArr['versionRange']; 	
								} else {
									$extensionIsAvailable = TRUE;	
								}
								while ($row = $TYPO3_DB->sql_fetch_assoc ($res)) {
									if (version_compare($row['version'], $lowerRange, '>=') && version_compare($row['version'], $upperRange, '<=')) {
										$extensionIsAvailable = TRUE;
									} 
								}
							}
						}
					}
					
						// Render the depencies information:
					$colorStyle = $extensionIsAvailable ? '' : 'color:red;';
					if (!$extensionIsAvailable) $someExtensionsAreNotAvailable = TRUE;
					$tableRows[] = '
						<tr>
							<td class="td-sub" style="'.$colorStyle.'">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_dependencies_kind_'.$dependencyArr['kind'])).'</td>
							<td class="td-sub" style="'.$colorStyle.'">'.$this->csConvHSC ($dependencyArr['extensionKey']).'</td>
							<td class="td-sub" style="'.$colorStyle.'">'.$dependencyArr['versionRange'].'</td>
						</tr>
					';
				}
			}
			
			if ($someExtensionsAreNotAvailable) {
				$tableRows[] = '
					<tr>
						<td class="td-sub" style="color:red" colspan="3">'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_dependencies_someextensionsarenotavailable')).'</td>
					</tr>
				';
			}
			
			$output = '
				<table>
					'.implode ('', $tableRows).'
				</table>
			';
		}
		return $output;
	}

	/**
	 * Renders reverse dependency information for frontend output for the given
	 * extension version
	 * 
	 * @param	string		$extensionKey: The extension key other extensions depend on
	 * @param	string		$version: The version number other extensions depend on (not used yet, but do specify!)
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function getRenderedReverseDependencies ($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;
				
		$output = '';
		
		$res = $TYPO3_DB->exec_SELECTquery (
			'extensionkey, dependingextensions',
			'tx_terfe_extensiondependencies',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensiondependencies')
		);
		if ($res) {	
			$dependingExtensionKeysArr = array();
			$tableRows = array();
				
			$row = $TYPO3_DB->sql_fetch_assoc ($res);	
			$extensionsArr = explode (',', $row['dependingextensions']);
			if (is_array ($extensionsArr)) {
				foreach ($extensionsArr as $keyAndVersion) {
					list ($key, $version) = explode ('(', $keyAndVersion);
					$version = substr ($version ,0,-1);
					$dependingExtensionKeysArr [$key][] = $version;
				}	
			}
			
			foreach ($dependingExtensionKeysArr as $key => $versionsArr) {
				$tableRows[] = '<tr><td class="td-sub">'.$this->csConvHSC($key).'</td><td class="td-sub">'.implode (', ', $versionsArr).'</td></tr>';
			}
			
			if (count ($tableRows)) {
				$output = '
					'.htmlspecialchars($TSFE->sL('LLL:EXT:ter_fe/pi1/locallang.php:extension_reversedependencies_intro')).'
					<table>
						'.implode ('', $tableRows).'
					</table>
				';
			}
		}
		return $output;
	}

	/**
	 * Renders a list of files of an extension with view and download links, all wrapped
	 * into an HTML table.
	 * 
	 * @param	array		$extensionDetailsArr: Record of table tx_terfe_extensiondetails with unserialized files array
	 * @return	string		HTML output
	 * @access	protectec 
	 */
	protected function getRenderedListOfFiles ($extensionDetailsArr) {
		$output = '&nbsp;';
		$filesArr = $extensionDetailsArr['files'];
		
		$firstLetter = strtolower (substr ($extensionDetailsArr['extensionkey'], 0, 1));
		$secondLetter = strtolower (substr ($extensionDetailsArr['extensionkey'], 1, 1));
		$tempDir = substr ($this->baseDirT3XContentCache, strlen(PATH_site)).$firstLetter.'/'.$secondLetter.'/'; 

		if (is_array ($filesArr)) {
			$tableRows = array ();
			foreach ($filesArr as $fileName => $fileArr) {
				
				if (t3lib_div::inList ('php,txt,tml,htm,xml,sql,asc,log,jpg,gif,png,css', strtolower (substr ($fileName, -3, 3)))) {
					$viewLink = $this->pi_linkTP_keepPIvars ($this->pi_getLL('general_view','',1), array('viewFile' => urlencode($fileName)), 1);
				} else {
					$viewLink = '';	
				}
				$tableRows[] = '
					<tr>
						<td nowrap="nowrap">'.$this->csConvHSC ($fileName).'</td>
						<td nowrap="nowrap">'.t3lib_div::formatSize($fileArr['size']).'</td>
						<td nowrap="nowrap">'.$viewLink.'</td>
						<td nowrap="nowrap">'.date($this->pi_getLL('general_dateandtimeformat'), $fileArr['mtime']).'</td>
						<td nowrap="nowrap"><a href="'.$tempDir.$fileArr['tempfilename'].'">'.$this->pi_getLL('general_download','',1).'</a></td>
					</tr>
				';
			}
	
			$t3xDownloadURL = substr ($this->getExtensionVersionPathAndBaseName($extensionDetailsArr['extensionkey'], $extensionDetailsArr['version']).'.t3x', strlen(PATH_site));

			$filePreview = '';
			if (isset($this->piVars['viewFile']) && is_array ($filesArr[urldecode($this->piVars['viewFile'])])) {
				$filePreview = $this->getRenderedFilePreview ($tempDir.basename($filesArr[urldecode($this->piVars['viewFile'])]['tempfilename']));
			}			
				
			$output ='
				<table>
					'.implode ('', $tableRows).'
				</table>
				<br /><br />
				<p><a href="'.$t3xDownloadURL.'">'.$this->pi_getLL('extensionfiles_downloadcompressedt3x','',1).'</a></p>
				<br />
				'.$filePreview.'
			';
			
		}
		return $output;
	}

	/**
	 * Renders a file preview, for example a syntax highlighted PHP file
	 * or an image 
	 * 
	 * @param	string		$pathAndFileName: The full path and file name
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function getRenderedFilePreview ($pathAndFileName) {
		$output = '<strong>'.htmlspecialchars(sprintf ($this->pi_getLL('extension_filepreview',''), basename($pathAndFileName))).':</strong><br />';

		if (t3lib_div::inList ('php,txt,xml,sql,log,css,tml,htm,asc', strtolower (substr ($pathAndFileName, -3, 3)))) {
			ob_start();
			highlight_file(PATH_site.$pathAndFileName);
			$output .= ob_get_contents();
			ob_end_clean();
	
			$output = str_replace('<code>','<pre>', $output);
			$output = str_replace('</code>','</pre>', $output);
			
		} elseif (t3lib_div::inList ('jpg,gif,png', strtolower (substr ($pathAndFileName, -3, 3)))) {
			$output .= '<img src="'.$pathAndFileName.'" />';
		}
		
		return $output;
	}

	/**
	 * Returns the image tag for an icon of an extension.
	 *
	 * @param	string		Extension key
	 * @param	string		Version
	 * @return	string 		Returns the icon image tag, if any
	 */
	protected function getIcon_extension($extensionKey, $version)	{
		$iconFileName = $this->getExtensionVersionPathAndBaseName($extensionKey, $version).'.gif';
		if (@is_file($iconFileName)) {
			$iconTag = '<img src="'.t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR').substr($iconFileName, strlen(PATH_site)).'" alt="'.htmlspecialchars($extensionKey).'" />';
		} else {
			$iconTag = '';	
		}

		return $iconTag;
	}

	/**
	 * Returns the proper state image for the development state given
	 *
	 * @param	string		$state: The state (alpha, beta, ...)
	 * @return	string		HTML image tag
	 * @access	protected
	 */
	protected function getIcon_state ($state)	{
		if (t3lib_div::inList ($this->validStates, $state)) {
			return '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/state_'.$state.'.gif" width="109" height="17" alt="'.$this->pi_getLL('extension_state_'.$state,'',1).'" title="'.$this->pi_getLL('extension_state_'.$state,'',1).'" />';
		} else {
			return '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/state_na.gif" width="109" height="17" alt="" title="" />';
		}
	}

	/**
	 * Returns the full path including file name but excluding file extension of
	 * the specified extension version in the file repository.
	 */
	protected function getExtensionVersionPathAndBaseName ($extensionKey, $version) {
		$firstLetter = strtolower (substr ($extensionKey, 0, 1));
		$secondLetter = strtolower (substr ($extensionKey, 1, 1));
		$fullPath = $this->repositoryDir.$firstLetter.'/'.$secondLetter.'/';

		list ($majorVersion, $minorVersion, $devVersion) = t3lib_div::intExplode ('.', $version);
		
		return $fullPath . strtolower ($extensionKey).'_'.$majorVersion.'.'.$minorVersion.'.'.$devVersion;		
	}
	
	/**
	 * Returns the unpacked array from a T3X file of the extension specified by
	 * $extensionKey and $version
	 * 
	 * @param	string		$extensionKey: Extension key
	 * @param	string		$version: Version number
	 * @return	mixed		T3X Array or FALSE if operation was not successful
	 * @access	protected
	 */
	protected function getUnpackedT3XFile ($extensionKey, $version) {
		$t3xFileRaw = @file_get_contents ($this->getExtensionVersionPathAndBaseName($extensionKey, $version).'.t3x'); 
		if ($t3xFileRaw === FALSE) return FALSE;

		list ($md5Hash, $compressionFlag, $dataRaw) = split (':', $t3xFileRaw, 3);
		unset ($t3xFileRaw);
		
		$dataUncompressed = gzuncompress ($dataRaw);
		if ($md5Hash != md5 ($dataUncompressed)) return FALSE;
		unset ($dataRaw);		
		
		return unserialize ($dataUncompressed);		
	}





	/**
	 * Reads the extension index file (extensions.xml.gz) and updates
	 * the different database tables accordingly.
	 * 
	 * @return	void
	 * @access	protected
	 */
	protected function extensionIndex_updateDB() {
		global $TYPO3_DB;
		
			// Check if another process tries to update the index right now:
		if (@file_exists (PATH_site.'typo3temp/tx_terfe/tx_terfe_updatedbextensionindex.lock')) {
				// If the lock is not older than 10 minutes, skip index creation:
			if (filemtime (PATH_site.'typo3temp/tx_terfe/tx_terfe_updatedbextensionindex.lock') > (time() - 600)) {
				return;
			}
		}
			
		touch (PATH_site.'typo3temp/tx_terfe/tx_terfe_updatedbextensionindex.lock');

			// Transfer data from extensions.xml.gz to database:		
		$extensions = simplexml_load_string (@implode ('', @gzfile($this->repositoryDir.'extensions.xml.gz')));

		$TYPO3_DB->exec_DELETEquery ('tx_terfe_extensions', '1');
		$TYPO3_DB->exec_DELETEquery ('tx_terfe_extensiondependencies', '1');		
		$dbExtensionDependenciesArr = array();
		
		foreach ($extensions as $extension) {
			foreach ($extension as $tag => $value) {
				if ($tag == 'version') {
					$extensionsRow = array (
						  'extensionkey' => $extension['extensionkey'],
						  'version' => $value['version'],
						  'title' => $value->title,
						  'description' => $value->description,
						  'state' => $value->state,
						  'reviewstate' => $value->reviewstate,
						  'category' => $value->category,
						  'extensiondownloadcounter' => $extension->downloadcounter,
						  'versiondownloadcounter' => $value->downloadcounter,
						  'lastuploaddate' => $value->lastuploaddate,
						  'uploadcomment' => $value->uploadcomment,
						  'dependencies' => $value->dependencies,
						  'authorname' => $value->authorname,
						  'authoremail' => $value->authoremail,
						  'authorcompany' => $value->authorcompany,
						  'ownerusername' => $value->ownerusername,
						  't3xfilemd5' => $value->t3xfilemd5
					);
					$TYPO3_DB->exec_INSERTquery ('tx_terfe_extensions', $extensionsRow);
						
						// Cache dependency information:
					$dependenciesArr = unserialize ((string)$value->dependencies);
					if (is_array ($dependenciesArr) && $value->reviewstate > 0) {
						foreach ($dependenciesArr as $dependencyArr) {
							if (strlen($dependencyArr['extensionKey'])) {
								$dependingExtensions = $dbExtensionDependenciesArr[$dependencyArr['extensionKey']];		
								$dbExtensionDependenciesArr[$dependencyArr['extensionKey']] = (strlen($dependingExtensions) ? $dependingExtensions.',' : '') . $extension['extensionKey'].'('.$value['version'].')';
							}
						}
					}
				}
			}
		}

		foreach ($dbExtensionDependenciesArr as $extensionKey => $dependingExtensions) {
			$dependenciesRow = array (
				'extensionkey' => $extensionKey,
				'dependingextensions' => $dependingExtensions
			);
			$TYPO3_DB->exec_INSERTquery ('tx_terfe_extensiondependencies', $dependenciesRow);
		}

			// Create new MD5 hash and remove lock:
		t3lib_div::writeFile (PATH_site.'typo3temp/tx_terfe/tx_terfe_extensionsmd5.txt', md5_file ($this->repositoryDir.'extensions.xml.gz'));		
		@unlink (PATH_site.'typo3temp/tx_terfe/tx_terfe_updatedbextensionindex.lock');
	}

	/**
	 * Checks if the extension index file (extensions.xml.gz) was modified
	 * since the last built of the extension index in the database.
	 * 
	 * @return	boolean		TRUE if the index has changed
	 * @access	protected
	 */
	protected function extensionIndex_wasModified () {
		$oldMD5Hash = @file_get_contents (PATH_site.'typo3temp/tx_terfe/tx_terfe_extensionsmd5.txt');
		$currentMD5Hash = @md5_file ($this->repositoryDir.'extensions.xml.gz');
		return ($oldMD5Hash != $currentMD5Hash); 	
	}



	/**
	 * Converts charsets and htmlspecialchars certain field of the given
	 * record from table tx_terfe_extensions so it can be displayed directly
	 * at the frontend.
	 *
	 * @param	array		$extensionRow: One record from table tx_terfe_extensions
	 * @return	array		The modified record
	 * @access protected
	 */
	protected function db_prepareExtensionRowForOutput ($extensionRow) {
		if (is_array ($extensionRow)) {
			foreach ($extensionRow as $key => $value) {
				switch ($key) {
					case 'extensionkey':
					case 'title':
					case 'description':
					case 'authorname':
					case 'authoremail':
					case 'authorcompany':
					case 'uploadcomment':
						$extensionRow[$key] = $this->csConvHSC ($value);
					break;
					case 'ownerusername':
						$extensionRow[$key] = $this->csConvHSC ($value);
						$extensionRow['ownerusernameandname'] = $this->csConvHSC ($value .' ('.$this->db_getFullNameByUsername($extensionRow['ownerusername']).')');
					break;
					case 'state':
						$extensionRow['state_raw'] = $value;
						$extensionRow[$key] = $this->pi_getLL('extension_state_'.$extensionRow[$key],'',1);
					break;										
					case 'reviewstate':
						$extensionRow['reviewstate_raw'] = $value;
						$extensionRow[$key] = $this->pi_getLL('extension_reviewstate_'.$extensionRow[$key],'',1);
					break;										
					case 'dependencies':
						$extensionRow[$key] = unserialize ($value);
					break;										
					case 'lastuploaddate':
						$extensionRow[$key] = date ($this->pi_getLL('general_dateandtimeformat'), $value);
					break;					
					case 'versiondownloadcounter':
						$extensionRow[$key] = intval($extensionRow['extensiondownloadcounter']).' / '.intval($value);
					break;					
				}					
			}
		}
		return $extensionRow;
	}

	/**
	 * Returns the full name of a person defined by the given user name
	 *
	 * @param	string		$username: User name of a person in fe_users
	 * @return	string		Full name of the person
	 * @access protected
	 */
	protected function db_getFullNameByUsername ($username) {
		global $TYPO3_DB, $TSFE;
		
		$res = $TYPO3_DB->exec_SELECTquery (
			'name',
			'fe_users',
			'username='.$TYPO3_DB->fullQuoteStr($username, 'fe_users') . $this->cObj->enableFields('fe_users')
		);
		if ($res) {	
			$row = $TYPO3_DB->sql_fetch_assoc ($res);
			$fromCharset = $TSFE->csConvObj->parse_charset($TSFE->TYPO3_CONF_VARS['BE']['forceCharset'] ? $TSFE->TYPO3_CONF_VARS['BE']['forceCharset'] : $TSFE->defaultCharSet);			
			return $TSFE->csConvObj->utf8_encode($row['name'], $fromCharset);
		} else {
			return '';
		}
	}

	/**
	 * Returns a record of extension details for the given extension version.
	 * 
	 * This information is fetched from the DB table tx_terfe_extensiondetails.
	 * If it doesn't exist yet or the information in that table does not match
	 * with the actual .t3x file of the extension, the DB record will be updated
	 * automatically.
	 * 
	 * @param		string	$extensionKey: Extension key of the extension
	 * @param		string	$version: Version number of the extension
	 * @return		array	Extension details record
	 * @access		protected
	 */
	protected function db_getExtensionDetails ($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;
		
		$table = 'tx_terfe_extensiondetails';
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			$table,
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, $table) .
			' AND version='.$TYPO3_DB->fullQuoteStr($version, $table)
		);
		
		if ($res) {	
			$row = $TYPO3_DB->sql_fetch_assoc ($res);
	
			$t3xPathAndFileName = $this->getExtensionVersionPathAndBaseName($extensionKey, $version).'.t3x';
			$t3xMD5Hash = @md5_file ($t3xPathAndFileName);
		
			if (is_array ($row) && $t3xMD5Hash == $row['t3xmd5hash']) {
				$row['files'] = unserialize ($row['files']);
				return $row;
			} else {
				return $this->db_getAndUpdateExtensionDetails ($extensionKey, $version);
			}							
		}
	}

	/**
	 * Fetches the meta data and other details of an extension from the T3X file,
	 * updates the cached data in the database and returns the created record as 
	 * an array.
	 * 
	 * The files of the given extension are extracted and stored in a subdirectory
	 * of typo3temp so they can be accessed later on without extracting the T3X file again. 
	 * 
	 * @param		string	$extensionKey: Extension key of the extension
	 * @param		string	$version: Version number of the extension
	 * @return		mixed	Extension details record or FALSE if operation was not successful
	 * @access		protected
	 * @todo		Create clean up mechanism for temporary files of deleted extensions 
	 */
	protected function db_getAndUpdateExtensionDetails ($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;

		$t3xArr = $this->getUnpackedT3XFile ($extensionKey, $version);
		if (!is_array ($t3xArr)) return FALSE;
		
		$filesArr = array ();
		if (is_array ($t3xArr['FILES'])) {
			$baseDir = $this->baseDirT3XContentCache;
			$firstLetter = strtolower (substr ($extensionKey, 0, 1));
			$secondLetter = strtolower (substr ($extensionKey, 1, 1));

				// Create directories if neccessary and delete possible old data from this extension version:
			@mkdir ($baseDir.$firstLetter);
			@mkdir ($baseDir.$firstLetter.'/'.$secondLetter);
			
			foreach (glob($baseDir.$firstLetter.'/'.$secondLetter.'/'.$extensionKey.'-'.$version.'*') as $fileName) {
		   		@unlink ($fileName);
			}

				// Now write the files to the temporary directory:
			foreach ($t3xArr['FILES'] as $fileName => $fileInfoArr) {
				$cleanFileName = $extensionKey.'-'.$version.'-'.preg_replace ('/[^\w]/', '__', $fileName);
				$tempFileName = $baseDir.$firstLetter.'/'.$secondLetter.'/'.$cleanFileName;

				t3lib_div::writeFile ($tempFileName, $fileInfoArr['content']);

				$filesArr[$fileName] = array (
					'size' => $fileInfoArr['size'],
					'mtime' => $fileInfoArr['mtime'],
					'tempfilename' => $cleanFileName
				);
			}
		}
		
		$detailsRow = array (
			'extensionkey' => $extensionKey,
			'version' => $version,
			'files' => serialize ($filesArr),
			't3xfilemd5' => @md5_file ($this->getExtensionVersionPathAndBaseName($extensionKey, $version).'.t3x')
		);

			// Update db record:		
		$table = 'tx_terfe_extensiondetails';
		$res = $TYPO3_DB->exec_DELETEquery (
			$table, 
			'extensionkey='.$TYPO3_DB->fullQuoteStr ($extensionKey, $table).' AND version='.$TYPO3_DB->fullQuoteStr ($version, $table)
		);
		$res = $TYPO3_DB->exec_INSERTquery ('tx_terfe_extensiondetails', $detailsRow);
		
		$detailsRow['uid'] = $TYPO3_DB->sql_insert_id();
		$detailsRow['files'] = $filesArr;
		return $detailsRow;
	}
	
	/**
	 * Searches the repository for the highest version number of an upload of the
	 * extension specified by $extensionKey. If no upload was found at all, FALSE
	 * will be returned. If at least one upload was found, the highest version number
	 * following the format major.minor.dev (eg. 4.2.1) will be returned.
	 *
	 * @param	string		$extKey: Extension key
	 * @return	mixed		The version number as a string or FALSE
	 * @access	public 
	 */
	protected function db_getLatestVersionNumberOfExtension ($extensionKey) {
		global $TYPO3_DB;
		
		$res = $TYPO3_DB->exec_SELECTquery (
			'version',
			'tx_terfe_extensions',
			'extensionkey="'.$TYPO3_DB->quoteStr($extensionKey, 'tx_terfe_extensions').'" AND reviewstate > 0'
		);
		$latestVersion = FALSE;
		while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
			if (version_compare($row['version'], $latestVersion, '>')) {
				$latestVersion = $row['version'];	
			}
		}
		
		return $latestVersion;	
	}
	
	/**
	 * Converts the given string from utf-8 to the charset of the current frontend
	 * page and processes the result with htmlspecialchars() 
	 * 
	 * @param	string	$string: The utf-8 string to convert
	 * @return	string	The converted string
	 * @access	protected
	 */
	protected function csConvHSC ($string) {
		return htmlspecialchars($GLOBALS['TSFE']->csConv($string, 'utf-8'));		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi1/class.tx_terfe_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi1/class.tx_terfe_pi1.php']);
}

?>