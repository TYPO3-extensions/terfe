<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005, 2006 Robert Lemke (robert@typo3.org)
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
 * Plugin 'TER Extension Key Management' for the 'ter_fe' extension.
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
 *   88: class tx_terfe_pi2 extends tslib_pibase
 *  109:     protected function init($conf)
 *  132:     public function main($content,$conf)
 *  195:     protected function renderView_introduction ()
 *  213:     protected function renderView_registerKeys ()
 *  368:     protected function renderView_manageKeys ()
 *  523:     protected function renderTopNavigation ()
 *  542:     protected function csConvHSC ($string)
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

defined('TX_TER_ERROR_GENERAL_EXTREPDIRDOESNTEXIST')
	|| define ('TX_TER_ERROR_GENERAL_EXTREPDIRDOESNTEXIST', '100');
defined('TX_TER_ERROR_GENERAL_NOUSERORPASSWORD')
	|| define ('TX_TER_ERROR_GENERAL_NOUSERORPASSWORD', '101');
defined('TX_TER_ERROR_GENERAL_USERNOTFOUND')
	|| define ('TX_TER_ERROR_GENERAL_USERNOTFOUND', '102');
defined('TX_TER_ERROR_GENERAL_WRONGPASSWORD')
	|| define ('TX_TER_ERROR_GENERAL_WRONGPASSWORD', '103');
defined('TX_TER_ERROR_GENERAL_DATABASEERROR')
	|| define ('TX_TER_ERROR_GENERAL_DATABASEERROR', '104');

defined('TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONDOESNTEXIST')
	|| define ('TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONDOESNTEXIST', '202');
defined('TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONCONTAINSNOFILES')
	|| define ('TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONCONTAINSNOFILES', '203');
defined('TX_TER_ERROR_UPLOADEXTENSION_WRITEERRORWHILEWRITINGFILES')
	|| define ('TX_TER_ERROR_UPLOADEXTENSION_WRITEERRORWHILEWRITINGFILES', '204');
defined('TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONTOOBIG')
	|| define ('TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONTOOBIG', '205');
defined('TX_TER_ERROR_UPLOADEXTENSION_EXISTINGEXTENSIONRECORDNOTFOUND')
	|| define ('TX_TER_ERROR_UPLOADEXTENSION_EXISTINGEXTENSIONRECORDNOTFOUND', '206');
defined('TX_TER_ERROR_UPLOADEXTENSION_FILEMD5DOESNOTMATCH')
	|| define ('TX_TER_ERROR_UPLOADEXTENSION_FILEMD5DOESNOTMATCH', '207');
defined('TX_TER_ERROR_UPLOADEXTENSION_ACCESSDENIED')
	|| define ('TX_TER_ERROR_UPLOADEXTENSION_ACCESSDENIED', '208');

defined('TX_TER_ERROR_REGISTEREXTENSIONKEY_DBERRORWHILEINSERTINGKEY')
	|| define ('TX_TER_ERROR_REGISTEREXTENSIONKEY_DBERRORWHILEINSERTINGKEY', '300');

defined('TX_TER_ERROR_DELETEEXTENSIONKEY_ACCESSDENIED')
	|| define ('TX_TER_ERROR_DELETEEXTENSIONKEY_ACCESSDENIED', '500');
defined('TX_TER_ERROR_DELETEEXTENSIONKEY_KEYDOESNOTEXIST')
	|| define ('TX_TER_ERROR_DELETEEXTENSIONKEY_KEYDOESNOTEXIST', '501');
defined('TX_TER_ERROR_DELETEEXTENSIONKEY_CANTDELETEBECAUSEVERSIONSEXIST')
	|| define ('TX_TER_ERROR_DELETEEXTENSIONKEY_CANTDELETEBECAUSEVERSIONSEXIST', '502');

defined('TX_TER_ERROR_MODIFYEXTENSIONKEY_ACCESSDENIED')
	|| define ('TX_TER_ERROR_MODIFYEXTENSIONKEY_ACCESSDENIED', '600');
defined('TX_TER_ERROR_MODIFYEXTENSIONKEY_SETTINGTOTHISOWNERISNOTPOSSIBLE')
	|| define ('TX_TER_ERROR_MODIFYEXTENSIONKEY_SETTINGTOTHISOWNERISNOTPOSSIBLE', '601');
defined('TX_TER_ERROR_MODIFYEXTENSIONKEY_KEYDOESNOTEXIST')
	|| define ('TX_TER_ERROR_MODIFYEXTENSIONKEY_KEYDOESNOTEXIST', '602');

defined('TX_TER_ERROR_SETREVIEWSTATE_NOUSERGROUPDEFINED')
	|| define ('TX_TER_ERROR_SETREVIEWSTATE_NOUSERGROUPDEFINED', '700');
defined('TX_TER_ERROR_SETREVIEWSTATE_ACCESSDENIED')
	|| define ('TX_TER_ERROR_SETREVIEWSTATE_ACCESSDENIED', '701');
defined('TX_TER_ERROR_SETREVIEWSTATE_EXTENSIONVERSIONDOESNOTEXIST')
	|| define ('TX_TER_ERROR_SETREVIEWSTATE_EXTENSIONVERSIONDOESNOTEXIST', '702');

defined('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_NOUSERGROUPDEFINED')
	|| define ('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_NOUSERGROUPDEFINED', '800');
defined('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_ACCESSDENIED')
	|| define ('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_ACCESSDENIED', '801');
defined('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_EXTENSIONVERSIONDOESNOTEXIST')
	|| define ('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_EXTENSIONVERSIONDOESNOTEXIST', '802');
defined('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_INCREMENTORNOTPOSITIVEINTEGER')
	|| define ('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_INCREMENTORNOTPOSITIVEINTEGER', '803');
defined('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_EXTENSIONKEYDOESNOTEXIST')
	|| define ('TX_TER_ERROR_INCREASEEXTENSIONDOWNLOADCOUNTER_EXTENSIONKEYDOESNOTEXIST', '804');

defined('TX_TER_ERROR_DELETEEXTENSION_ACCESS_DENIED')
	|| define ('TX_TER_ERROR_DELETEEXTENSION_ACCESS_DENIED', '900');
defined('TX_TER_ERROR_DELETEEXTENSION_EXTENSIONDOESNTEXIST')
	|| define ('TX_TER_ERROR_DELETEEXTENSION_EXTENSIONDOESNTEXIST', '901');

	// Result codes:
defined('TX_TER_RESULT_GENERAL_OK')
	|| define ('TX_TER_RESULT_GENERAL_OK', '10000');

defined('TX_TER_RESULT_EXTENSIONKEYALREADYEXISTS')
	|| define ('TX_TER_RESULT_EXTENSIONKEYALREADYEXISTS', '10500');
defined('TX_TER_RESULT_EXTENSIONKEYDOESNOTEXIST')
	|| define ('TX_TER_RESULT_EXTENSIONKEYDOESNOTEXIST', '10501');
defined('TX_TER_RESULT_EXTENSIONKEYNOTVALID')
	|| define ('TX_TER_RESULT_EXTENSIONKEYNOTVALID', '10502');
defined('TX_TER_RESULT_EXTENSIONKEYSUCCESSFULLYREGISTERED')
	|| define ('TX_TER_RESULT_EXTENSIONKEYSUCCESSFULLYREGISTERED', '10503');
defined('TX_TER_RESULT_EXTENSIONSUCCESSFULLYUPLOADED')
	|| define ('TX_TER_RESULT_EXTENSIONSUCCESSFULLYUPLOADED', '10504');
defined('TX_TER_RESULT_EXTENSIONSUCCESSFULLYDELETED')
	|| define ('TX_TER_RESULT_EXTENSIONSUCCESSFULLYDELETED', '10505');



require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('ter_fe').'class.tx_terfe_common.php');

/**
 * Plugin Extension key management
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_terfe
 */
class tx_terfe_pi2 extends tslib_pibase {

	public		$prefixId = 'tx_terfe_pi2';											// Same as class name
	public		$scriptRelPath = 'pi2/class.tx_terfe_pi2.php';						// Path to this script relative to the extension dir.
	public		$extKey = 'ter_fe';													// The extension key.
	public		$pi_checkCHash = TRUE;												// Handle empty CHashes correctly

	protected 	$WSDLURI;
	protected	$SOAPServiceURI;





	/**
	 * Initializes the plugin, only called from main()
	 *
	 * @param	array		$conf: The plugin configuration array
	 * @return	void
	 * @access	protected
	 */
	protected function init($conf) {
		global $TSFE;

		$this->conf=$conf;
		$this->pi_setPiVarDefaults(); 			// Set default piVars from TS
		$this->pi_initPIflexForm();				// Init FlexForm configuration for plugin
		$this->pi_loadLL();

		$staticConfArr = unserialize ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_fe']);
		if (is_array ($staticConfArr)) {
			$this->WSDLURI = $staticConfArr['WSDLURI'];
			$this->SOAPServiceURI = $staticConfArr['SOAPServiceURI'];
		}

		$this->commonObj = new tx_terfe_common($this);
		$this->commonObj->repositoryDir = $this->conf['repositoryDirectory'];
		if (substr ($this->commonObj->repositoryDir, -1, 1) != '/') $this->commonObj->repositoryDir .= '/';
		$this->commonObj->init();
	}

	/**
	 * The plugin's main function
	 *
	 * @param	string		$content: Content rendered so far (not used)
	 * @param	array		$conf: The plugin configuration array
	 * @return	string		The plugin's HTML output
	 * @access	public
	 * @todo	Known problem: Currently I check if user is admin by look comparing the user with a usergroup defined for ter_fe. It would be better to ask the TER via SOAP if the current user is admin, because then we don't have to configure that on two sides.
	 */
	public function main($content,$conf)	{
		global $TSFE;

		$this->init($conf);
		if (!@is_dir ($this->commonObj->repositoryDir)) return 'TER_FE Error: Repository directory ('.$this->commonObj->repositoryDir.') does not exist!';
		$userLoggedIn = is_array ($TSFE->fe_user->user);
		$userIsAdmin = (intval($conf['adminFEUserGroup']) && t3lib_div::inList($TSFE->fe_user->user['usergroup'], $conf['adminFEUserGroup']));

			// Prepare the top menu items:
		if (!$this->piVars['view']) $this->piVars['view'] = 'introduction';
		$menuItems = array ('introduction');
		if ($userLoggedIn) {
			$menuItems[] = 'register';
			$menuItems[] = 'manage';
		}
		if ($userIsAdmin) {
			$menuItems[] = 'admin';
		}

			// Render the top menu
		$topMenu = '';
		foreach ($menuItems as $itemKey) {
			$itemActive = ($this->piVars['view'] == $itemKey);
			$link = $this->pi_linkTP($this->pi_getLL('views_'.$itemKey,'',1), array('tx_terfe_pi2[view]' => $itemKey), 0);
			$topMenu .='<span '.($itemActive ? 'class="submenu-button-active"' :'class="submenu-button"').'>'.$link.'</span>';
		}

		switch ($this->piVars['view']) {
			case 'register':
				$subContent = $userLoggedIn ? $this->renderView_registerKeys() : $this->pi_getLL('registerkeys_needlogin', '',1);
				break;
			case 'manage':
				$subContent = $userLoggedIn ? $this->renderView_manageKeys() : $this->pi_getLL('managekeys_needlogin', '',1);
				break;
			case 'admin':
				$subContent = ($userLoggedIn && $userIsAdmin) ? $this->renderView_administration() : '';
				break;
			case 'introduction':
			default:
				$subContent = $this->renderView_introduction();
		}

			// Put everything together:
		$content = '
			<h2>'.$this->pi_getLL('general_extensionkeys', 1).'</h2>
			<br />
			'.$this->commonObj->getTopMenu($menuItems).'<br />
			<br />
			'.$subContent.'
		';
		return $this->pi_wrapInBaseClass($content);
	}





	/**
	 * Renders the introduction view
	 *
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderView_introduction ()	{
		global $TSFE;

		$output = '';
		if (!is_array ($TSFE->fe_user->user)) {
			$output .= '<strong>'.$this->pi_getLL('introduction_needlogin','',1) .'</strong><br /><br />';
		}
		$output .= $this->pi_getLL('introduction_explanation');

		return $output;
	}

	/**
	 * Renders the view for registering extension keys
	 *
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderView_registerKeys ()	{
		global $TSFE;

		$output = '';
		$rulesMessage ='
			<h5>'.$this->pi_getLL('registerkeys_rules_heading','',1).'</h5>
			'.$this->pi_getLL('registerkeys_rules_explanation','',1).'<br />
			<br />
			<ul>
				<li>'.$this->pi_getLL('registerkeys_rules_allowedcharacters','',1).'</li>
				<li>'.$this->pi_getLL('registerkeys_rules_prefixes','',1).'</li>
				<li>'.$this->pi_getLL('registerkeys_rules_startandend','',1).'</li>
				<li>'.$this->pi_getLL('registerkeys_rules_length','',1).'</li>
			</ul>
		';


		switch (t3lib_div::GPVar ('tx_terfe_pi2_cmd')) {

			case 'registersubmit':
					$accountDataArr = array('username' => $TSFE->fe_user->user['username'], 'password' => $TSFE->fe_user->user['password']);
					$extensionKey = $TSFE->csConv(t3lib_div::GPVar('tx_terfe_pi2_extensionkey'), 'utf-8');

					if (!strlen (t3lib_div::GPVar('tx_terfe_pi2_extensiontitle'))) {
						$output .=  '
							<h4>'.$this->pi_getLL('general_error','',1).'</h4>
							<p>'.$this->pi_getLL('registerkeys_titlemissing','',1).'</p>
						';
					} else {
						$soapClientObj = $this->getSoapClient();
						try {
							$result = $soapClientObj->checkExtensionKey($accountDataArr, $extensionKey);
								// remove cookies
							$soapClientObj->__setCookie('SaneID');
							$soapClientObj->__setCookie('fe_typo_user');

							if (strcmp(TX_TER_RESULT_EXTENSIONKEYDOESNOTEXIST, $result['resultCode']) == 0) {
								$extensionKeyDataArr = array(
									'extensionKey' => $extensionKey,
									'title' => $TSFE->csConv(t3lib_div::GPVar('tx_terfe_pi2_extensiontitle'), 'utf-8'),
									'description' => $TSFE->csConv(t3lib_div::GPVar('tx_terfe_pi2_extensiondescription'), 'utf-8'),
								);
								$soapClientObj = $this->getSoapClient();
								$result = $soapClientObj->registerExtensionKey($accountDataArr, $extensionKeyDataArr);

								$output .= '
									<h4>'.$this->pi_getLL('registerkeys_success','',1).'</h4>
									<p>'.$this->pi_getLL('registerkeys_success_explanation','',1).'</p>
								';
							} else {
								$output .= '
									<h4>'.$this->pi_getLL('general_error','',1).'</h4>
									<p>'.sprintf($this->pi_getLL('registerkeys_result_unknown','',1), $result['resultCode']).'</p>
								';
							}
						} catch (SoapFault $exception) {
							$output .=  '
								<h4>'.$this->pi_getLL('general_error','',1).'</h4>
								<p>SoapFault Exception (#'.$exception->faultcode.'): '.$exception->faultstring.'</p>
							';
						}
					}
					break;

			case 'registercheck':
					$accountDataArr = array('username' => $TSFE->fe_user->user['username'], 'password' => $TSFE->fe_user->user['password']);
					$extensionKey = $TSFE->csConv(t3lib_div::GPVar('tx_terfe_pi2_extensionkey'), 'utf-8');

					$soapClientObj = $this->getSoapClient();
					try {
						$result = $soapClientObj->checkExtensionKey($accountDataArr, $extensionKey);

						switch ($result['resultCode']) {
							case TX_TER_RESULT_EXTENSIONKEYDOESNOTEXIST :
								$output .= '
									<h4>'.$this->pi_getLL('registerkeys_title','',1).'</h4>
									<p>'.sprintf ($this->pi_getLL('registerkeys_keyisvalid','',1), '<em>'.$extensionKey.'</em>').'</p>
									<br />
									<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2_register">
										<table style="border: 0;">
											<tr>
												<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('registerkeys_extensionkey', '', 1).':</th>
												<td class="td-sub"><em>'.$extensionKey.'</em></td>
												<td>&nbsp;</td>
											</tr>
											<tr>
												<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('registerkeys_extensiontitle', '', 1).':</th>
												<td class="td-sub"><input type="text" name="tx_terfe_pi2_extensiontitle" size="30" /></td>
												<td><em>'.$this->pi_getLL('registerkeys_extensiontitle_hint', '', 1).'</em></td>
											</tr>
											<tr>
												<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('registerkeys_extensiondescription', '', 1).':</th>
												<td class="td-sub"><textarea name="tx_terfe_pi2_extensiondescription" rows="10" style="width:100%;"></textarea></td>
												<td><em>'.$this->pi_getLL('registerkeys_extensiondescription_hint', '', 1).'</em></td>
											</tr>
											<tr>
												<td>&nbsp;</td>
												<td style="text-align:right;"><input type="submit" value="'.$this->pi_getLL('registerkeys_doregister','',1).'" /></td>
												<td>&nbsp;</td>
											</tr>
										</table>
										<input name="tx_terfe_pi2_cmd" type="hidden" value="registersubmit" />
										<input name="tx_terfe_pi2_extensionkey" type="hidden" value="'.$extensionKey.'" />
									</form>
								';
								break;

							default:
								$output .= '
									<h4>'.$this->pi_getLL('registerkeys_extensionkeynotvalid','',1).'</h4>
									<p>'.$this->pi_getLL('registerkeys_result_'.$result['resultCode'],'Unknown error '.$result['resultCode'],1).'</p>
									<br />
									<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2_register">
										<label>'.$this->pi_getLL('registerkeys_extensionkey', '', 1).':</label>
										<input name="tx_terfe_pi2_extensionkey" type="text" size="20" />
										<input type="submit" value="'.$this->pi_getLL('registerkeys_checkvalidity','',1).'" />
										<input name="tx_terfe_pi2_cmd" type="hidden" value="registercheck" />
									</form>
								'.$rulesMessage;
								break;
						}
					} catch (SoapFault $exception) {
						$output .=  '
							<h4>'.$this->pi_getLL('general_error','',1).'</h4>
							<p>SoapFault Exception (#'.$exception->faultcode.'): '.$exception->faultstring.'</p>
						';
					}
					break;

			default:
					$output = '
						<h4>'.$this->pi_getLL('registerkeys_title','',1).'</h4>
						<p>'.$this->pi_getLL('registerkeys_introduction','',1).'</p>
						<br />
						<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2_register">
							<label>'.$this->pi_getLL('registerkeys_extensionkey', '', 1).':</label>
							<input name="tx_terfe_pi2_extensionkey" type="text" size="20" />
							<input type="submit" value="'.$this->pi_getLL('registerkeys_checkvalidity','',1).'" />
							<input name="tx_terfe_pi2_cmd" type="hidden" value="registercheck" />
						</form>
						'.$rulesMessage.'
					';
					break;
		}

		return $output;
	}

	/**
	 * Renders the view for managing extension keys
	 *
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderView_manageKeys ()	{
		global $TSFE, $TYPO3_DB;

		$output = '';
		$actionMessages = '';

		$iconInfo = '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/info.gif" width="16" height="16" alt="" title="" style="vertical-align:middle;" />';
		$iconError = '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/error.gif" width="16" height="16" alt="" title="" style="vertical-align:middle;" />';

		try {
			$soapClientObj = $this->getSoapClient();
			$accountDataArr = array('username' => $TSFE->fe_user->user['username'], 'password' => $TSFE->fe_user->user['password']);


				// Handle submitted actions:
			switch (t3lib_div::GPVar ('tx_terfe_pi2_cmd')) {

				case 'transferkey':
					$targetUsername = t3lib_div::GPvar('tx_terfe_pi2_targetusername');
					$extensionKey = t3lib_div::GPvar('tx_terfe_pi2_extensionkey');

					$resultArr = $soapClientObj->modifyExtensionKey($accountDataArr, array('extensionKey' => $extensionKey, 'ownerUsername' => $targetUsername));

					if (is_array ($resultArr)) {
						switch ($resultArr['resultCode']) {
							case TX_TER_RESULT_GENERAL_OK : $actionMessages = '<p>'.$iconInfo.' <strong>'.sprintf ($this->pi_getLL('managekeys_action_transferkey_success','',1), $extensionKey, $targetUsername).'</strong></p><br />'; break;
							case TX_TER_ERROR_GENERAL_USERNOTFOUND: $actionMessages = '<p>'.$iconError.' <strong>'.sprintf ($this->pi_getLL('managekeys_action_transferkey_usernotfound','',1), $extensionKey, $targetUsername).'</strong></p><br />'; break;
							default: $actionMessages = '<p>'.$iconError.' <strong>'.sprintf ($this->pi_getLL('general_errorcode','',1), $resultArr['resultCode']).'</strong></p><br />'; break;
						}
					}
					break;

				case 'deletekey':
					$extensionKey = t3lib_div::GPvar('tx_terfe_pi2_extensionkey');

					$resultArr = $soapClientObj->deleteExtensionKey($accountDataArr, $extensionKey);
					if (is_array ($resultArr)) {
						switch ($resultArr['resultCode']) {
							case TX_TER_RESULT_GENERAL_OK : $actionMessages = '<p>'.$iconInfo.' <strong>'.sprintf ($this->pi_getLL('managekeys_action_deletekey_success','',1), $extensionKey).'</strong></p><br />'; break;
							default: $actionMessages = '<p>'.$iconError.' <strong>'.sprintf ($this->pi_getLL('general_errorcode','',1), $resultArr['resultCode']).'</strong></p><br />'; break;
						}
					}
					break;
			}

				// Create list of extension keys:
			$filterOptionsArr = array ('username' => $TSFE->fe_user->user['username']);
			$soapClientObj = $this->getSoapClient();
			$resultArr = $soapClientObj->getExtensionKeys($accountDataArr, $filterOptionsArr);
			if (is_array ($resultArr) && $resultArr['simpleResult']['resultCode'] == TX_TER_RESULT_GENERAL_OK) {

				$tableRows = array();
				if (is_array ($resultArr['extensionKeyData'])) {
					foreach ($resultArr['extensionKeyData'] as $extensionKeyArr) {
						$res = $TYPO3_DB->exec_SELECTquery (
							'version',
							'tx_terfe_extensions',
							'extensionkey="'.$TYPO3_DB->quoteStr($extensionKeyArr['extensionkey'],'tx_terfe_extensions').'"'
						);
						if ($res) {
							$numberOfVersions = $TYPO3_DB->sql_num_rows ($res);
							$tableRows[] = '
								<tr>
									<td class="td-sub"><span title="'.$this->csConvHSC($extensionKeyArr['title']).'">'.$this->csConvHSC($extensionKeyArr['extensionkey']).'</span></td>
									<td class="td-sub">
										'.($numberOfVersions > 0 ? $numberOfVersions : '<em>'.$this->pi_getLL('general_none','',1).'</em>').'
									</td>
									<td class="td-sub" nowrap="nowrap">
										<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2_register">
											<input name="tx_terfe_pi2_targetusername" type="text" size="10" />
											<input type="image" src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/transferkey.gif" alt="'.$this->pi_getLL('managekeys_action_transferkey','',1).'" title="'.$this->pi_getLL('managekeys_action_transferkey','',1).'" onFocus="blur()" />
											<input name="tx_terfe_pi2_extensionkey" type="hidden" value="'.$extensionKeyArr['extensionkey'].'" />
											<input name="tx_terfe_pi2_cmd" type="hidden" value="transferkey" />
										</form>
									</td>
									<td class="td-sub" nowrap="nowrap">
										'. ($numberOfVersions == 0 ?
												'<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2_register" onSubmit="return confirm(\''.sprintf($this->pi_getLL('managekeys_deleteareyousure','',1), $extensionKeyArr['extensionkey']).'\');">
													<input type="image" src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/delete.gif" alt="'.$this->pi_getLL('managekeys_action_deletekey','',1).'" title="'.$this->pi_getLL('managekeys_action_deletekey','',1).'" onFocus="blur()" />
													<input name="tx_terfe_pi2_extensionkey" type="hidden" value="'.$extensionKeyArr['extensionkey'].'" />
													<input name="tx_terfe_pi2_cmd" type="hidden" value="deletekey" />
												</form>'
											:
												'&nbsp;'
											).'
									</td>
								</tr>
							';
						}
					}
				}

				$output = '
					<h4>'.$this->pi_getLL('managekeys_title','',1).'</h4>
					<p>'.$this->pi_getLL('managekeys_introduction','',1).'</p>
					<br />
					'.$actionMessages.'
					<table>
						<tr>
							<th class="th-sub">'.$this->pi_getLL('registerkeys_extensionkey','',1).'</th>
							<th class="th-sub">'.$this->pi_getLL('managekeys_uploads','',1).'</th>
							<th class="th-sub">'.$this->pi_getLL('managekeys_transfer','',1).'</th>
							<th class="th-sub">'.$this->pi_getLL('managekeys_delete','',1).'</th>
						</tr>
						'.implode (chr(10),$tableRows).'
					</table>
				';
			} else {
				$output .=  '
					<h4>'.$this->pi_getLL('general_error','',1).'</h4>
					<p>'.sprintf($this->pi_getLL('general_errorcode','',1), $resultArr['simpleResult']['resultCode']).'</p>
				';
			}
		} catch (SoapFault $exception) {
			$output .=  '
				<h4>'.$this->pi_getLL('general_error','',1).'</h4>
				<p>SoapFault Exception (#'.$exception->faultcode.'): '.$exception->faultstring.'</p>
			';
		}

		return $output;
	}

	/**
	 * Renders the view for administrators
	 *
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderView_administration ()	{
		global $TSFE, $TYPO3_DB;

		$output = '';
		$actionMessages = '';

		$iconInfo = '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/info.gif" width="16" height="16" alt="" title="" style="vertical-align:middle;" />';
		$iconError = '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/error.gif" width="16" height="16" alt="" title="" style="vertical-align:middle;" />';

		try {
			$soapClientObj = $this->getSoapClient();
			$accountDataArr = array('username' => $TSFE->fe_user->user['username'], 'password' => $TSFE->fe_user->user['password']);

				// Handle submitted actions:
			switch (t3lib_div::GPVar ('tx_terfe_pi2_cmd')) {

				case 'deleteextensionversion':
					$extensionKey = t3lib_div::GPvar('tx_terfe_pi2_extensionkey');
					$version = t3lib_div::GPvar('tx_terfe_pi2_version');

					$resultArr = $soapClientObj->deleteExtension($accountDataArr, $extensionKey, $version);
					if (is_array ($resultArr)) {
						switch ($resultArr['resultCode']) {
							case TX_TER_RESULT_EXTENSIONSUCCESSFULLYDELETED :
								$actionMessages = '<p>'.$iconInfo.' <strong>'.sprintf ($this->pi_getLL('admin_action_deleteextension_success','',1), $extensionKey, $version).'</strong></p><br />';
								$res = $TYPO3_DB->exec_DELETEquery (
									'tx_terfe_extensions',
									'extensionkey='.$TYPO3_DB->fullQuoteStr($extensionKey, 'tx_terfe_extensions').' AND version='.$TYPO3_DB->fullQuoteStr($version, 'tx_terfe_extensions')
								);
							break;
							default: $actionMessages = '<p>'.$iconError.' <strong>'.sprintf ($this->pi_getLL('general_errorcode','',1), $resultArr['resultCode']).'</strong></p><br />'; break;
						}
					}
					break;

				case 'transferkey':
					$targetUsername = t3lib_div::GPvar('tx_terfe_pi2_targetusername');
					$extensionKey = t3lib_div::GPvar('tx_terfe_pi2_extensionkey');

					$resultArr = $soapClientObj->modifyExtensionKey($accountDataArr, array('extensionKey' => $extensionKey, 'ownerUsername' => $targetUsername));

					if (is_array ($resultArr)) {
						switch ($resultArr['resultCode']) {
							case TX_TER_RESULT_GENERAL_OK : $actionMessages = '<p>'.$iconInfo.' <strong>'.sprintf ($this->pi_getLL('managekeys_action_transferkey_success','',1), $extensionKey, $targetUsername).'</strong></p><br />'; break;
							case TX_TER_ERROR_GENERAL_USERNOTFOUND: $actionMessages = '<p>'.$iconError.' <strong>'.sprintf ($this->pi_getLL('managekeys_action_transferkey_usernotfound','',1), $extensionKey, $targetUsername).'</strong></p><br />'; break;
							default: $actionMessages = '<p>'.$iconError.' <strong>'.sprintf ($this->pi_getLL('general_errorcode','',1), $resultArr['resultCode']).'</strong></p><br />'; break;
						}
					}
					break;

				case 'deletekey':
					$extensionKey = t3lib_div::GPvar('tx_terfe_pi2_extensionkey');

					$resultArr = $soapClientObj->deleteExtensionKey($accountDataArr, $extensionKey);
					if (is_array ($resultArr)) {
						switch ($resultArr['resultCode']) {
							case TX_TER_RESULT_GENERAL_OK : $actionMessages = '<p>'.$iconInfo.' <strong>'.sprintf ($this->pi_getLL('managekeys_action_deletekey_success','',1), $extensionKey).'</strong></p><br />'; break;
							default: $actionMessages = '<p>'.$iconError.' <strong>'.sprintf ($this->pi_getLL('general_errorcode','',1), $resultArr['resultCode']).'</strong></p><br />'; break;
						}
					}
				break;
			}

				// Render search form:
			$searchForm = '
				<form action="'.$this->pi_getPageLink($TSFE->id).'" method="get">
					<input type="hidden" name="tx_terfe_pi2_cmd" value="search" />
					<input type="hidden" name="no_cache" value="1" />
					<input type="hidden" name="tx_terfe_pi2[view]" value="admin" />
					<input type="text" name="tx_terfe_pi2_extensionkey" size="20" />
					<input type="submit" value="'.$this->pi_getLL('admin_search_searchbutton','',1).'" />
				</form>
				<br />
			';

				// Create list of extensions:
			$extensionKey = t3lib_div::GPvar('tx_terfe_pi2_extensionkey');
			if (strlen($extensionKey)) {
				$filterOptionsArr = array ('extensionKey' => $extensionKey);
				$soapClientObj = $this->getSoapClient();
				$resultArr = $soapClientObj->getExtensionKeys($accountDataArr, $filterOptionsArr);
				if (is_array ($resultArr) && $resultArr['simpleResult']['resultCode'] == TX_TER_RESULT_GENERAL_OK) {

					$tableRows = array();
					if (is_array ($resultArr['extensionKeyData'])) {
						foreach ($resultArr['extensionKeyData'] as $extensionKeyArr) {
							$res = $TYPO3_DB->exec_SELECTquery (
								'version',
								'tx_terfe_extensions',
								'extensionkey="'.$TYPO3_DB->quoteStr($extensionKeyArr['extensionkey'],'tx_terfe_extensions').'"'
							);
							if ($res) {
								$numberOfVersions = $TYPO3_DB->sql_num_rows ($res);
								$tableRows[] = '
									<tr>
										<td class="td-sub"><span title="'.$this->csConvHSC($extensionKeyArr['title']).'">'.$this->csConvHSC($extensionKeyArr['extensionkey']).'</span></td>
										<td class="td-sub">'.$this->csConvHSC($extensionKeyArr['ownerusername']).'</td>
										<td class="td-sub" nowrap="nowrap">
											<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2">
												<input name="tx_terfe_pi2_targetusername" type="text" size="10" />
												<input type="image" src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/transferkey.gif" alt="'.$this->pi_getLL('managekeys_action_transferkey','',1).'" title="'.$this->pi_getLL('managekeys_action_transferkey','',1).'" onFocus="blur()" />
												<input name="tx_terfe_pi2_extensionkey" type="hidden" value="'.$extensionKeyArr['extensionkey'].'" />
												<input name="tx_terfe_pi2_version" type="hidden" value="'.$extensionKeyArr['version'].'" />
												<input name="tx_terfe_pi2_cmd" type="hidden" value="transferkey" />
											</form>
										</td>
										<td class="td-sub" nowrap="nowrap">
											'. ($numberOfVersions == 0 ?
													'<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2" onSubmit="return confirm(\''.sprintf($this->pi_getLL('managekeys_deleteareyousure','',1), $extensionKeyArr['extensionkey']).'\');">
														<input type="image" src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/delete.gif" alt="'.$this->pi_getLL('managekeys_action_deletekey','',1).'" title="'.$this->pi_getLL('managekeys_action_deletekey','',1).'" onFocus="blur()" />
														<input name="tx_terfe_pi2_extensionkey" type="hidden" value="'.$extensionKeyArr['extensionkey'].'" />
														<input name="tx_terfe_pi2_cmd" type="hidden" value="deletekey" />
													</form>'
												:
													'&nbsp;'
												).'
										</td>
									</tr>
								';
								if ($numberOfVersions>0) {
									while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
										$tableRows[] = '
											<tr>
												<td class="td-sub">&nbsp;</td>
												<td class="td-sub">'.htmlspecialchars($row['version']).'</td>
												<td class="td-sub" nowrap="nowrap">&nbsp;</td>
												<td class="td-sub" nowrap="nowrap">
													<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2" onSubmit="return confirm(\''.sprintf($this->pi_getLL('admin_deleteextensionareyousure','',1), $extensionKeyArr['extensionkey'], $row['version']).'\');">
														<input type="image" src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/delete.gif" alt="'.$this->pi_getLL('admin_action_deleteextension','',1).'" title="'.$this->pi_getLL('admin_action_deleteextension','',1).'" onFocus="blur()" />
														<input name="tx_terfe_pi2_extensionkey" type="hidden" value="'.$extensionKeyArr['extensionkey'].'" />
														<input name="tx_terfe_pi2_version" type="hidden" value="'.$row['version'].'" />
														<input name="tx_terfe_pi2_cmd" type="hidden" value="deleteextensionversion" />
													</form>
												</td>
											</tr>
										';
									}
								}
							}
						}
					}

				} else {
					$output .=  '
						<h4>'.$this->pi_getLL('general_error','',1).'</h4>
						<p>'.sprintf($this->pi_getLL('general_errorcode','',1), $resultArr['simpleResult']['resultCode']).'</p>
					';
				}
			}
			$output .= '
				<h4>'.$this->pi_getLL('admin_title','',1).'</h4>
				<p>'.$this->pi_getLL('admin_introduction','',1).'</p>
				<br />
				'.$actionMessages.'
				'.$searchForm.'
			';
			if (count($tableRows)) {
				$output .= '
					<table>
						<tr>
							<th class="th-sub">'.$this->pi_getLL('registerkeys_extensionkey','',1).'</th>
							<th class="th-sub">'.$this->pi_getLL('admin_version','',1).' / '.$this->pi_getLL('admin_owner','',1).'</th>
							<th class="th-sub">'.$this->pi_getLL('managekeys_transfer','',1).'</th>
							<th class="th-sub">'.$this->pi_getLL('managekeys_delete','',1).'</th>
						</tr>
						'.implode (chr(10),$tableRows).'
					</table>
				';
			}
		} catch (SoapFault $exception) {
			$output .=  '
				<h4>'.$this->pi_getLL('general_error','',1).'</h4>
				<p>SoapFault Exception (#'.$exception->faultcode.'): '.$exception->faultstring.'</p>
			';
		}

		return $output;
	}





	/**
	 * Renders the view for registering extension keys
	 *
	 * @return	string		HTML output
	 * @access	protected
	 */
	protected function renderTopNavigation ()	{

		$output = 'Register';

		return $output;
	}

	/**
	 * Instantiate a new SoapClient
	 *
	 * @return SoapClient
	 */
	protected function getSoapClient() {
		static $client;

		if ($client instanceof SoapClient) {
			unset($client);
		}
		$client = new SoapClient(
			$this->WSDLURI,
			array(
				'trace' => 1,
				'exceptions' => 1
			)
		);
		return $client;
	}



	/**
	 * Converts the given string from utf-8 to the charset of the current frontend
	 * page and processes the result with htmlspecialchars()
	 *
	 * @param	string		$string: The utf-8 string to convert
	 * @return	string		The converted string
	 * @access	protected
	 */
	protected function csConvHSC ($string) {
		return htmlspecialchars($GLOBALS['TSFE']->csConv($string, 'utf-8'));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi2/class.tx_terfe_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi2/class.tx_terfe_pi2.php']);
}

?>