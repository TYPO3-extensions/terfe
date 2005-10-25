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

class tx_terfe_pi1 extends tslib_pibase {

	public		$prefixId = 'tx_terfe_pi1';											// Same as class name
	public		$scriptRelPath = 'pi1/class.tx_terfe_pi1.php';						// Path to this script relative to the extension dir.
	public		$extKey = 'ter_fe';													// The extension key.
	public		$pi_checkCHash = TRUE;												// Handle empty CHashes correctly
	
	protected	$repositoryDir = '';												// Full path to the extension repository files
	protected	$baseDirT3XContentCache = '';										// Full path to T3X content cache
	protected	$viewMode = '';														// View mode, one of the following: LATEST, CATEGORIES, FULLLIST
	
	protected	$validStates = 'alpha,beta,stable,experimental,test,obsolete';		// List of valid development states	

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
			
		if ($this->extensionIndex_wasModified ()) {
			$this->extensionIndex_updateDB ();	
		}

			// Prepare the top menu items:
		if (!$this->piVars['view']) $this->piVars['view'] = 'latest';
		$menuItems = array ('latest', 'categories', 'popular', 'search');

			// Render the top menu		
		$topMenu = '';
		foreach ($menuItems as $itemKey) {
			$itemActive = ($this->piVars['view'] == $itemKey);
			$link = $this->pi_linkTP($this->pi_getLL('views_'.$itemKey,'',1), array('tx_terfe_pi1[view]' => $itemKey), 1);
			$topMenu .='<span '.($itemActive ? 'class="submenu-button-active"' :'class="submenu-button"').'>'.$link.'</span>';
		}
		
		if ($this->piVars['showExt']) {
			$subContent = $this->renderSingleView_extension ($this->piVars['showExt']);			
		} else {
			switch ($this->piVars['view']) {
				case 'latest':		$subContent = $this->renderListView_latest(); break;
				case 'categories': 	break;
				case 'popular': 	break;
				case 'search':		$subContent = $this->renderListView_search(); break;
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

	protected function renderListView_latest() {
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
					$tableRows[] = $this->renderListView_extensionRow ($extensionRow);
					$alreadyRenderedExtensionKeys[] = $extensionRow['extensionkey'];
				}
			}
		}
		
		$content.= '
			<p>'.htmlspecialchars(sprintf($this->pi_getLL('renderview_latest_introduction',''), $numberOfDays)).'</p>
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
				<input type="text" name="tx_terfe_pi1[sword]" size="20" />
				<input type="submit" value="'.$this->pi_getLL('listview_search_searchbutton','',1).'" />
				<input type="hidden" name="tx_terfe_pi1[view]" value="search" />
			</form>
		';
		
		$searchResult = strlen(trim($this->piVars['sword']) > 2) ? $this->renderListView_searchResult() : '';
		
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
			$TYPO3_DB->searchQuery (explode (' ', $this->piVars['sword']), array('extensionkey','title','description'), 'tx_terfe_extensions'),
			'',
			'lastuploaddate DESC',
			'0,30'
		);

		if ($res) {
			$alreadyRenderedExtensionKeys = array();
			if ($TYPO3_DB->sql_num_rows($res)) {
				while ($extensionRow = $TYPO3_DB->sql_fetch_assoc ($res)) {
					if (!t3lib_div::inArray ($alreadyRenderedExtensionKeys, $extensionRow['extensionkey'])) {
						$tableRows[] = $this->renderListView_extensionRow ($extensionRow);
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
	 * Renders the single view for an extension including several sub views.
	 * 
	 * @param	string		$extensionKey: The extension key of the extension to render
	 * @return	string		HTML output
	 */
	protected function renderSingleView_extension ($extensionKey) {
		global $TYPO3_DB;

			// Fetch the extension record:
		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			'tx_terfe_extensions',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensions'),
			'',
			'version DESC'
		);
		if (!$res) return 'Extension '.htmlspecialchars($extensionKey).' not found!';	
		$extRow = $this->db_prepareExtensionRowForOutput ($TYPO3_DB->sql_fetch_assoc ($res));

			// Prepare the top menu items:
		if (!$this->piVars['extView']) $this->piVars['extView'] = 'info';
		$menuItems = array ('info', 'details', 'feedback');

			// Render the top menu		
		$topMenu = '';
		foreach ($menuItems as $itemKey) {
			$itemActive = ($this->piVars['extView'] == $itemKey);
			$link = $this->pi_linkTP($this->pi_getLL('extensioninfo_views_'.$itemKey,'',1), array('tx_terfe_pi1[showExt]' => $extensionKey, 'tx_terfe_pi1[extView]' => $itemKey), 1);
			$topMenu .='<span '.($itemActive ? 'class="submenu-button-active"' :'class="submenu-button"').'>'.$link.'</span>';
		}

			// Render content of the currently selected view:
		switch ($this->piVars['extView']) {
			case 'details' :
				$subContent = $this->renderSingleView_extensionDetails ($extRow);
			break;
			case 'feedback' :
				$subContent = 'TODO: Feedback';
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
		$documentationIndex = '<span style="color:red;">FIXME: Documentation index</style>'; # FIXME: Documentation index must be rendered when ter_doc is finished 

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

		$extDetailsRow = $this->db_getExtensionDetails ($extRow['extensionkey'], $extRow['version']);

			// Compile detail rows information:
		$detailRows = '';
		$detailsArr = array (
			'extension_extensionkey' => $extRow['extensionkey'],		
			'extension_version' => $extRow['version'],		
			'extension_category' => $extRow['category'],		
			'extension_state' => $extRow['state'],		
			'extension_dependencies' => $this->getRenderedDependencies ($extRow['dependencies']),		
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
	 * Render the extension info row for listing of categories, news etc.
	 *
	 * @param	array		$extRow: Database record from tx_terfe_extensions
	 * @return	string		Two HTML table rows wrapped in <tr>
	 */
	protected function renderListView_extensionRow($extRow)	{
		global $TSFE;

		$extRow = $this->db_prepareExtensionRowForOutput ($extRow);
				
		$tableRows = '
			<tr>
				<th class="th-main">'.$this->getIcon_tag ($extRow['extensionkey'], $extRow['version']).'</th>
				<th class="th-main" colspan ="2">'.$this->pi_linkTP_keepPIvars($extRow['title'], array('showExt' => $extRow['extensionkey']),1).' - <em>'.$extRow['extensionkey'].'</em></th>
				<th class="th-main" style="text-align:right;">'.$this->getIcon_state($extRow['state_raw']).'</th>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td style="width:55%;">
					<table style="width: 100%">
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_authorname','',1).':</th><td class="td-sub" nowrap="nowrap">'.$extRow['authorname'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_category','',1).':</th><td class="td-sub" nowrap="nowrap">'.$extRow['category'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_version','',1).':</th><td class="td-sub" nowrap="nowrap">'.$extRow['version'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_lastuploaddate','',1).':</th><td class="td-sub" nowrap="nowrap">'.$extRow['lastuploaddate'].'</td></tr>
						<tr><th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('extension_uploadcomment','',1).':</th><td style="width:60%;" class="td-sub" nowrap="nowrap">'.$extRow['uploadcomment'].'</td></tr>
					</table>
				</td>
				<td colspan="2">
					<p>'.$extRow['description'].'</p>
				</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
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
		$output = '';
		if (is_array ($dependenciesArr)) {
			$tableRows = array ();
			foreach ($dependenciesArr as $dependencyArr) {
				$tableRows[] = '
					<tr>
						<td class="td-sub">'.$this->pi_getLL('extension_dependencies_kind_'.$dependencyArr['kind'],'',1).'</td>
						<td class="td-sub">'.$this->csConvHSC ($dependencyArr['extensionKey']).'</td>
						<td class="td-sub">'.$dependencyArr['versionRange'].'</td>
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
	 * Renders a list of files of an extension with view and download links, all wrapped
	 * into an HTML table.
	 * 
	 * @param	array		$extensionDetailsArr: Record of table tx_terfe_extensiondetails with unserialized files array
	 * @return	string		HTML output
	 * @access	protectec 
	 * 
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
			if (is_array ($filesArr[urldecode($this->piVars['viewFile'])])) {
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
	protected function getIcon_tag($extensionKey, $version)	{
		$iconFileName = $this->getExtensionVersionPathAndBaseName($extensionKey, $version).'.gif';
		$iconTag = '<img src="'.t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR').substr($iconFileName, strlen(PATH_site)).'" alt="'.htmlspecialchars($extensionKey).'" />';

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

		$TYPO3_DB->exec_DELETEquery ('tx_terfe_extensions', '1');		

			// Transfer data from extensions.xml.gz to database:		
		$extensions = simplexml_load_string (@implode ('', @gzfile($this->repositoryDir.'extensions.xml.gz')));
		if (!is_object($extensions)) return;
		
		foreach ($extensions as $extension) {
			foreach ($extension as $version) {
				$extensionsRow = array (
					  'extensionkey' => $extension['extensionKey'],
					  'version' => $version['version'],
					  'title' => $version->title,
					  'description' => $version->description,
					  'state' => $version->state,
					  'category' => $version->category,
					  'lastuploaddate' => $version->lastuploaddate,
					  'uploadcomment' => $version->uploadcomment,
					  'dependencies' => $version->dependencies,
					  'authorname' => $version->authorname,
					  'authoremail' => $version->authoremail,
					  'authorcompany' => $version->authorcompany,
					  'ownerusername' => $version->ownerusername,
					  't3xfilemd5' => $version->t3xfilemd5
				);
				$TYPO3_DB->exec_INSERTquery ('tx_terfe_extensions', $extensionsRow);
			}	
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
					case 'dependencies':
						$extensionRow[$key] = unserialize ($value);
					break;										
					case 'lastuploaddate':
						$extensionRow[$key] = date ($this->pi_getLL('general_dateandtimeformat'), $value);
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
			return $row['name'];		
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