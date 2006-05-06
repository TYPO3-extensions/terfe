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
 * Code library for the frontend plugins of the TER FE extension
 *
 * $Id$
 *
 * @author	Robert Lemke <robert@typo3.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   83: class tx_terfe_common
 *   98:     public function __construct($pObj)
 *  109:     public function init()
 *
 *              SECTION: DATABASE RELATED FUNCTIONS
 *  136:     public function db_getExtensionRecord($extensionKey, $version)
 *  161:     public function db_prepareExtensionRecordForOutput($extensionRecord)
 *  216:     public function db_getExtensionDetails ($extensionKey, $version)
 *  252:     public function db_getExtensionKeysByOwner($owner)
 *  281:     public function db_getLatestVersionNumberOfExtension ($extensionKey, $ignoreReviewState=FALSE)
 *  313:     protected function db_getAndUpdateExtensionDetails ($extensionKey, $version)
 *  375:     public function db_getFullNameByUsername ($username)
 *
 *              SECTION: RENDER FUNCTIONS
 *  409:     public function getTopMenu($menuItems)
 *  459:     public function getRenderedDependencies($dependenciesArr)
 *  535:     public function getRenderedReverseDependencies ($extensionKey, $version)
 *  583:     public function getRenderedListOfFiles($extensionDetailsArr)
 *  648:     public function getRenderedFilePreview ($pathAndFileName)
 *  675:     public function getIcon_extension($extensionKey, $version)
 *  692:     public function getIcon_state ($state)
 *
 *              SECTION: FILE-RELATED FUNCTIONS
 *  719:     public function getExtensionVersionPathAndBaseName ($extensionKey, $version)
 *  738:     protected function getUnpackedT3XFile ($extensionKey, $version)
 *  761:     protected function transferFile ($fullPath, $visibleFilename=NULL)
 *
 *              SECTION: EXTENSION INDEX RELATED FUNCTIONS
 *  793:     protected function extensionIndex_updateDB()
 *  876:     protected function extensionIndex_wasModified ()
 *
 *              SECTION: VARIOUS HELPER FUNCTIONS
 *  902:     public function getLL($key, $alternativeLabel='', $passThroughHtmlspecialchars=FALSE)
 *  916:     public function csConvHSC ($string)
 *
 * TOTAL FUNCTIONS: 23
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Shared code library for the TER frontend plugins
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_terfe
 */
class tx_terfe_common {

	public		$baseDirT3XContentCache = '';										// Full path to T3X content cache. Automatically set by this class.
	public		$repositoryDir = '';												// Full path to the extension repository files. Must be set from outside before calling init()!

	protected	$pObj;																// Reference to the parent object - must be a child of pi_base.
	protected	$validStates = 'alpha,beta,stable,experimental,test,obsolete';		// List of valid development states

	/**
	 * Class constructor.
	 *
	 * @param	object		$pObj: Reference to the parent object - must be a child of pi_base
	 * @return	void
	 * @access	public
	 */
	public function __construct($pObj) {
		$this->pObj = $pObj;
	}

	/**
	 * Initializes this class after basic settings have been made. Call this function always
	 * before using an instance of this class the first time.
	 *
	 * @return	void
	 * @access	public
	 */
	public function init() {
		$this->baseDirT3XContentCache = PATH_site.'typo3temp/tx_terfe/t3xcontentcache/';
		if ($this->extensionIndex_wasModified ()) {
			$this->extensionIndex_updateDB ();
		}
	}





	/*********************************************************
	 *
	 * DATABASE RELATED FUNCTIONS
	 *
	 *********************************************************/

	/**
	 * Returns the extension record (row) for of the given extension version
	 * or FALSE if an error ocurred.
	 *
	 * @param	string		$extensionKey: Extension key to fetch the record for
	 * @param	string		$version: The extension version number
	 * @return	mixed		The extension record from table tx_terfe_extensions or FALSE if an error occurred
	 * @access	public
	 * @see		db_prepareExtensionRecordForOutput()
	 */
	public function db_getExtensionRecord($extensionKey, $version) {
		global $TYPO3_DB;

		$res = $TYPO3_DB->exec_SELECTquery (
			'*',
			'tx_terfe_extensions',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensions').' AND '.
				'version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_extensions')
		);
		if ($res) {
			$ExtensionRecord = $TYPO3_DB->sql_fetch_assoc ($res);
			if (is_array($ExtensionRecord)) return $ExtensionRecord;
		}
		return FALSE;
	}

	/**
	 * Converts charsets and htmlspecialchars certain field of the given
	 * record from table tx_terfe_extensions so it can be displayed directly
	 * at the frontend.
	 *
	 * @param	array		$extensionRecord: One record from table tx_terfe_extensions
	 * @return	array		The modified record
	 * @access	public
	 */
	public function db_prepareExtensionRecordForOutput($extensionRecord) {
		if (is_array ($extensionRecord)) {
			foreach ($extensionRecord as $key => $value) {
				switch ($key) {
					case 'extensionkey':
					case 'title':
					case 'description':
					case 'authorname':
					case 'authoremail':
					case 'authorcompany':
					case 'uploadcomment':
						$extensionRecord[$key] = $this->csConvHSC ($value);
					break;
					case 'ownerusername':
						$extensionRecord[$key] = $this->csConvHSC ($value);
						$extensionRecord['ownerusernameandname'] = $this->csConvHSC ($value .' ('.$this->db_getFullNameByUsername($extensionRecord['ownerusername']).')');
					break;
					case 'state':
						$extensionRecord['state_raw'] = $value;
						$extensionRecord[$key] = $this->getLL('extension_state_'.$extensionRecord[$key],'', 1);
					break;
					case 'reviewstate':
						$extensionRecord['reviewstate_raw'] = $value;
						$extensionRecord[$key] = $this->getLL('extension_reviewstate_'.$extensionRecord[$key],'', 1);
					break;
					case 'dependencies':
						$extensionRecord[$key] = unserialize ($value);
					break;
					case 'lastuploaddate':
						$extensionRecord['lastuploaddate_raw'] = $value;
						$extensionRecord[$key] = strftime($this->getLL('general_dateandtimeformat'), $value);
					break;
					case 'versiondownloadcounter':
						$extensionRecord[$key] = intval($extensionRecord['extensiondownloadcounter']).' / '.intval($value);
                                        case 'rating':
						$extensionRecord[$key] = round($extensionRecord[$key],2);
					break;
					break;

				}
			}
		}
		return $extensionRecord;
	}

	/**
	 * Returns a record of extension details for the given extension version.
	 *
	 * This information is fetched from the DB table tx_terfe_extensiondetails.
	 * If it doesn't exist yet or the information in that table does not match
	 * with the actual .t3x file of the extension, the DB record will be updated
	 * automatically.
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @return	array		Extension details record
	 * @access	public
	 * @see		db_getAndUpdateExtensionDetails()
	 */
	public function db_getExtensionDetails ($extensionKey, $version) {
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
	 * Returns an array of extension keys which are owned by the given author.
	 *
	 * Note: This information is built from cached data and might differ from the data in the
	 *       main repository. If you need 100% valid information, call the SOAP method instead.
	 *
	 * @param	string		$owner: User name of the extension author
	 * @return	mixed		Array of extension keys or FALSE if an error occurred
	 * @access	public
	 */
	public function db_getExtensionKeysByOwner($owner) {
		global $TYPO3_DB;

		$res = $TYPO3_DB->exec_SELECTquery (
			'extensionkey',
			'tx_terfe_extensions',
			'ownerusername='.$TYPO3_DB->fullQuoteStr($owner, 'tx_terfe_extensions')
		);
		if ($res) {
			$extensionKeysArr = array();
			while ($row =  $TYPO3_DB->sql_fetch_assoc ($res)) {
				$extensionKeysArr[$row['extensionkey']] = $row['extensionkey'];
			}
			return $extensionKeysArr;
		}
		return FALSE;
	}

	/**
	 * Searches the repository for the highest version number of an upload of the
	 * extension specified by $extensionKey. If no upload was found at all, FALSE
	 * will be returned. If at least one upload was found, the highest version number
	 * following the format major.minor.dev (eg. 4.2.1) will be returned.
	 *
	 * @param	string		$extKey: Extension key
	 * @param	boolean		$ignoreReviewState: If set to TRUE, even unreviewed extension versions will be taken into account
	 * @return	mixed		The version number as a string or FALSE
	 * @access	public
	 */
	public function db_getLatestVersionNumberOfExtension ($extensionKey, $ignoreReviewState=FALSE) {
		global $TYPO3_DB;

		$res = $TYPO3_DB->exec_SELECTquery (
			'version',
			'tx_terfe_extensions',
			'extensionkey="'.$TYPO3_DB->quoteStr($extensionKey, 'tx_terfe_extensions').'"' . ($ignoreReviewState ? '' : ' AND reviewstate > 0')
		);
		$latestVersion = '0';
		while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
			if (version_compare($row['version'], $latestVersion, '>')) {
				$latestVersion = $row['version'];
			}
		}
		return $latestVersion == '0' ? FALSE : $latestVersion;
	}

	/**
	 * Fetches the meta data and other details of an extension from the T3X file,
	 * updates the cached data in the database and returns the created record as
	 * an array.
	 *
	 * The files of the given extension are extracted and stored in a subdirectory
	 * of typo3temp so they can be accessed later on without extracting the T3X file again.
	 *
	 * @param	string		$extensionKey: Extension key of the extension
	 * @param	string		$version: Version number of the extension
	 * @return	mixed		Extension details record or FALSE if operation was not successful
	 * @access	protected
	 * @see		db_getExtensionDetails()
	 * @todo	Create clean up mechanism for temporary files of deleted extensions
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
	 * Returns the full name of a person defined by the given user name
	 *
	 * @param	string		$username: User name of a person in fe_users
	 * @return	string		Full name of the person
	 * @access	public
	 */
	public function db_getFullNameByUsername ($username) {
		global $TYPO3_DB, $TSFE;

		$res = $TYPO3_DB->exec_SELECTquery (
			'name',
			'fe_users',
			'username='.$TYPO3_DB->fullQuoteStr($username, 'fe_users') . $this->pObj->cObj->enableFields('fe_users')
		);
		if ($res) {
			$row = $TYPO3_DB->sql_fetch_assoc ($res);
			$fromCharset = $TSFE->csConvObj->parse_charset($TSFE->TYPO3_CONF_VARS['BE']['forceCharset'] ? $TSFE->TYPO3_CONF_VARS['BE']['forceCharset'] : $TSFE->defaultCharSet);
			return $TSFE->csConvObj->utf8_encode($row['name'], $fromCharset);
		} else {
			return '';
		}
	}





	/*********************************************************
	 *
	 * RENDER FUNCTIONS
	 *
	 *********************************************************/

	/**
	 * Renders the top tab menu which allows for selection of the different views.
	 *
	 * @param	array		$menuItems: Array of key values for the menu items
	 * @return	string		HTML output, enclosed in a DIV
	 * @access	public
	 */
	public function getTopMenu($menuItems) {

			// Render the top menu
		$counter = 0;
		foreach ($menuItems as $itemKey) {
			$activeItemsArr[$counter] = $this->pObj->piVars['view'] == $itemKey;
			$counter ++;
		}

		$counter = 0;
		$topMenuItems = '';
		foreach ($menuItems as $itemKey) {
			$this->pObj->pi_linkTP('', array($this->pObj->prefixId.'[view]' => $itemKey), 1);
			$link = '<a href="'.$this->pObj->cObj->lastTypoLinkUrl.'" '.($activeItemsArr[$counter] ? 'class="active"' : '').'>'.$this->pObj->pi_getLL('views_'.$itemKey,'',1).'</a>';

			if ($activeItemsArr[$counter]) {
				if ($counter > 0) {
					$topMenuItems .= '<div><img src="fileadmin/templates/images/terfe-tabnav-act-left.gif" alt="" /></div>';
				}
				$topMenuItems .= $link.'
					<div><img src="fileadmin/templates/images/terfe-tabnav-act-right.gif" alt="" /></div>
				';
			} else {
				if ($counter > 0 && !$activeItemsArr[$counter-1]) {
					$topMenuItems .= '<div><img src="fileadmin/templates/images/terfe-tabnav-right.gif" alt="" /></div>';
				}
				$topMenuItems .= $link;
			}

			$counter ++;
		}

		$topMenu = '
			<div class="terfe-tabnav">
				<div><img src="fileadmin/templates/images/terfe-tabnav-'.($activeItemsArr[0] ? 'act-' : '').'start.gif" alt="" /></div>
				'.$topMenuItems.'
				<div><img src="fileadmin/templates/images/terfe-tabnav-end.gif" alt="" /></div>
			</div>
		';
		return $topMenu;
	}

	/**
	 * Renders dependency information for frontend output from the given
	 * depencies array
	 *
	 * @param	array		$dependenciesArr: The dependencies
	 * @return	string		HTML output
	 * @access	public
	 */
	public function getRenderedDependencies($dependenciesArr) {
		global $TYPO3_DB, $TSFE;

		$output = '';
		if (is_array ($dependenciesArr)) {
			$alwaysAvailableExtensions = 'php,typo3,cms,lang';
			$someExtensionsAreNotAvailable = FALSE;
			$listRows = array ();
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
					$listRows[] = '
						<li>'.$this->getLL('extension_dependencies_kind_'.$dependencyArr['kind'],'',1).' '.$this->csConvHSC ($dependencyArr['extensionKey']).' '
						.$dependencyArr['versionRange'].'</li>
					';
				}
			}

			if ($someExtensionsAreNotAvailable) {
				$listRows[] = '
						<li style="color:red">'.$this->getLL('extension_dependencies_someextensionsarenotavailable','',1).'</li>
				';
			}

			$output = '
				<ul>
					'.implode ('', $listRows).'
				</ul>
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
	 * @access	public
	 */
	public function getRenderedReverseDependencies ($extensionKey, $version) {
		global $TYPO3_DB, $TSFE;

		$output = '';

		$res = $TYPO3_DB->exec_SELECTquery (
			'extensionkey, dependingextensions',
			'tx_terfe_extensiondependencies',
			'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensiondependencies')
		);
		if ($res) {
			$dependingExtensionKeysArr = array();
			$listRows = array();

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
				$listRows[] = '<li>'.$this->csConvHSC($key).' '.implode (', ', $versionsArr).'</li>';
			}

			if (count ($listRows)) {
				$output =
					'<p>'.$this->getLL('extension_reversedependencies_intro','',1).'</p>
					<ul>
						'.implode ('', $listRows).'
					</ul>
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
	 * @access	public
	 */
	public function getRenderedListOfFiles($extensionDetailsArr) {
		$output = '&nbsp;';
		$filesArr = $extensionDetailsArr['files'];

		$firstLetter = strtolower (substr ($extensionDetailsArr['extensionkey'], 0, 1));
		$secondLetter = strtolower (substr ($extensionDetailsArr['extensionkey'], 1, 1));
		$tempDir = substr ($this->baseDirT3XContentCache, strlen(PATH_site)).$firstLetter.'/'.$secondLetter.'/';

		if (is_array ($filesArr)) {
			$tableRows = array ();
			foreach ($filesArr as $fileName => $fileArr) {

				$downloadLink = $this->pObj->pi_linkTP_keepPIvars ($this->getLL('general_download','',1), array('downloadFile' => urlencode($fileName)), 1);
				if (t3lib_div::inList ('php,txt,tmpl,htm,xml,sql,asc,log,jpg,gif,png,css', strtolower (substr ($fileName, -3, 3)))) {
					$viewLink = $this->pObj->pi_linkTP_keepPIvars ($this->getLL('general_view','',1), array('viewFile' => urlencode($fileName)), 1);
				} else {
					$viewLink = '';
				}
				$tableRows[] = '
					<tr>
						<td class="filename">'.$this->csConvHSC ($fileName).'</td>
						<td>'.t3lib_div::formatSize($fileArr['size']).'</td>
						<td>'.$viewLink.'</td>
						<td>'.strftime($this->getLL('general_dateandtimeformat'), $fileArr['mtime']).'</td>
						<td>'.$downloadLink.'</td>
					</tr>
				';
			}

			$t3xDownloadURL = substr ($this->getExtensionVersionPathAndBaseName($extensionDetailsArr['extensionkey'], $extensionDetailsArr['version']).'.t3x', strlen(PATH_site));

			$filePreview = '';
			if (isset($this->pObj->piVars['downloadFile']) && is_array ($filesArr[urldecode($this->pObj->piVars['downloadFile'])])) {
				$filename = basename(urldecode($this->pObj->piVars['downloadFile']));
				$this->transferFile ($tempDir.basename($filesArr[urldecode($this->pObj->piVars['downloadFile'])]['tempfilename']), $filename);
				unset ($this->pObj->piVars['downloadFile']);
				return '';
			}

			if (isset($this->pObj->piVars['viewFile']) && is_array ($filesArr[urldecode($this->pObj->piVars['viewFile'])])) {
				$filePreview = $this->getRenderedFilePreview ($tempDir.basename($filesArr[urldecode($this->pObj->piVars['viewFile'])]['tempfilename']));
			}

			$output ='
				<table class="filelist">
				<tr><th>'.$this->getLL('extension_files_filename','',1).'
				</th><th>'.$this->getLL('extension_files_filesize','',1).'
				</th><th>'.$this->getLL('extension_files_preview','',1).'
				</th><th>'.$this->getLL('extension_files_date','',1).'
				</th><th>'.$this->getLL('extension_files_download','',1).'</th></tr>
					'.implode ('', $tableRows).'
				</table>'.$filePreview;

		}
		return $output;
	}

	/**
	 * Renders a file preview, for example a syntax highlighted PHP file
	 * or an image
	 *
	 * @param	string		$pathAndFileName: The full path and file name
	 * @return	string		HTML output
	 * @access	public
	 */
	public function getRenderedFilePreview ($pathAndFileName) {
		$output = '<strong>'.htmlspecialchars(sprintf ($this->getLL('extension_filepreview',''), basename($pathAndFileName))).':</strong><br />';

		if (t3lib_div::inList ('php,txt,xml,sql,log,css,tmpl,htm,asc', strtolower (substr ($pathAndFileName, -3, 3)))) {
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
	 * @return	string		Returns the icon image tag, if any
	 * @access	public
	 */
	public function getIcon_extension($extensionKey, $version)	{
		$iconFileName = $this->getExtensionVersionPathAndBaseName($extensionKey, $version).'.gif';
		if (@is_file($iconFileName)) {
			$iconTag = '<img src="'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').substr($iconFileName, strlen(PATH_site)).'" alt="'.htmlspecialchars($extensionKey).'" />';
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
	 * @access	public
	 */
	public function getIcon_state ($state)	{
		if (t3lib_div::inList ($this->validStates, $state)) {
			return '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/state_'.$state.'.gif" width="109" height="21" alt="'.$this->getLL('extension_state_'.$state,'',1).'" title="'.$this->getLL('extension_state_'.$state,'',1).'" />';
		} else {
			return '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/state_na.gif" width="109" height="21" alt="" title="" />';
		}
	}






	/*********************************************************
	 *
	 * FILE-RELATED FUNCTIONS
	 *
	 *********************************************************/

	/**
	 * Returns the full path including file name but excluding file extension of
	 * the specified extension version in the file repository.
	 *
	 * @param	string		$extensionKey: Extension key of the extension version
	 * @param	string		$version: Version number of the extension version
	 * @return	string		Full path name including file name (excluding file extension) of the specified extension version
	 */
	public function getExtensionVersionPathAndBaseName ($extensionKey, $version) {
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
	 * Transfers a file to the client browser.
	 * NOTE: This function must be called *before* any HTTP headers have been sent!
	 *
	 * @param	string		$fullPath: Full absolute path including filename which leads to the file to be transfered
	 * @param	string		$visibleFilename: File name which is visible for the user while downloading. If not set, the real file name will be used
	 * @return	boolean		TRUE if successful, FALSE if file did not exist.	 *
	 * @access	protected
	 */
	protected function transferFile ($fullPath, $visibleFilename=NULL) {

		if (!@file_exists($fullPath)) return FALSE;

		$filename = basename($fullPath);
		if (!isset($visibleFilename)) $visibleFilename = $filename;

		header('Content-Disposition: attachment; filename='.$visibleFilename.'');
		header('Content-type: x-application/octet-stream');
		header('Content-Transfer-Encoding: binary');
		header('Content-length:'.filesize($fullPath).'');
		readfile($fullPath);
		return TRUE;
	}





	/*********************************************************
	 *
	 * EXTENSION INDEX RELATED FUNCTIONS
	 *
	 *********************************************************/

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
		if ($extensions === FALSE) {
			$debugArr = @gzfile($this->repositoryDir.'extensions.xml.gz');
			@unlink (PATH_site.'typo3temp/tx_terfe/tx_terfe_updatedbextensionindex.lock');
			return;
		}

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
								$dbExtensionDependenciesArr[$dependencyArr['extensionKey']] = (strlen($dependingExtensions) ? $dependingExtensions.',' : '') . $extension['extensionkey'].'('.$value['version'].')';
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
		$currentMD5Hash = md5_file($this->repositoryDir.'extensions.xml.gz');
		return ($oldMD5Hash != $currentMD5Hash);
	}





	/*********************************************************
	 *
	 * VARIOUS HELPER FUNCTIONS
	 *
	 *********************************************************/

	/**
	 * getLL function specific to this class, always returning labels from the locallang
	 * file belonging to this code library.
	 *
	 * @param	string		$key: Locallang key for the label
	 * @param	string		$alternativeLabel: Return this string if no label could be found
	 * @param	boolean		$passThroughHtmlspecialchars: If the label should be processed by htmlspecialchars()
	 * @return	string		The locallang label (if exists)
	 * @access	public
	 */
	public function getLL($key, $alternativeLabel='', $passThroughHtmlspecialchars=FALSE) {
		$label = $GLOBALS['TSFE']->sL('LLL:EXT:ter_fe/locallang_common.xml:'.$key);
		if (!strlen($label)) $label = $alternativeLabel;
		return ($passThroughHtmlspecialchars ? htmlspecialchars($label) : $label);
	}

	/**
	 * Converts the given string from utf-8 to the charset of the current frontend
	 * page and processes the result with htmlspecialchars()
	 *
	 * @param	string		$string: The utf-8 string to convert
	 * @return	string		The converted string
	 * @access	public
	 */
	public function csConvHSC ($string) {
		return htmlspecialchars($GLOBALS['TSFE']->csConv($string, 'utf-8'));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/class.tx_terfe_common.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/class.tx_terfe_common.php']);
}

?>
