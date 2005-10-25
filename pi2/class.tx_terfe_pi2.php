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
 * Plugin 'TER Extension Key Management' for the 'ter_fe' extension.
 *
 * $Id$
 *
 * @author	Robert Lemke <robert@typo3.org>
 */

	// Error codes: 

define (TX_TER_ERROR_GENERAL_EXTREPDIRDOESNTEXIST, '100');
define (TX_TER_ERROR_GENERAL_NOUSERORPASSWORD, '101');
define (TX_TER_ERROR_GENERAL_USERNOTFOUND, '102');
define (TX_TER_ERROR_GENERAL_WRONGPASSWORD, '103');

define (TX_TER_ERROR_UPLOADEXTENSION_NOUPLOADPASSWORD, '200');
define (TX_TER_ERROR_UPLOADEXTENSION_WRONGUPLOADPASSWORD, '201');
define (TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONDOESNTEXIST, '202');
define (TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONCONTAINSNOFILES, '203');
define (TX_TER_ERROR_UPLOADEXTENSION_WRITEERRORWHILEWRITINGFILES, '204');
define (TX_TER_ERROR_UPLOADEXTENSION_EXTENSIONTOOBIG, '205');
define (TX_TER_ERROR_UPLOADEXTENSION_EXISTINGEXTENSIONRECORDNOTFOUND, '206');

define (TX_TER_ERROR_REGISTEREXTENSIONKEY_DBERRORWHILEINSERTINGKEY, '300');

define (TX_TER_ERROR_GETEXTENSIONKEYS_DBERRORWHILEFETCHINGKEYS, '400');


	// Result codes: 
define (TX_TER_RESULT_GENERAL_OK, '10000');
	
define (TX_TER_RESULT_EXTENSIONKEYALREADYEXISTS, '10500');
define (TX_TER_RESULT_EXTENSIONKEYDOESNOTEXIST, '10501');
define (TX_TER_RESULT_EXTENSIONKEYNOTVALID, '10502');
define (TX_TER_RESULT_EXTENSIONKEYSUCCESSFULLYREGISTERED, '10503');
define (TX_TER_RESULT_EXTENSIONSUCCESSFULLYUPLOADED, '10504');

require_once(PATH_tslib.'class.tslib_pibase.php');

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

		$staticConfArr = unserialize ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_fe']);
		if (is_array ($staticConfArr)) {
			$this->WSDLURI = $staticConfArr['WSDLURI'];
			$this->SOAPServiceURI = $staticConfArr['SOAPServiceURI'];
		}
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
		$userLoggedIn = is_array ($TSFE->fe_user->user);
		$userIsAdmin = FALSE;	// TODO: Admin mode not implemented yet 

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
				$subContent = $userLoggedIn ? $this->renderView_administrateKeys() : $this->pi_getLL('adminkeys_needlogin', '',1);
			break;			
			case 'introduction':
			default:
				$subContent = $this->renderView_introduction();
		}
				
			// Put everything together:
		$content = '
			<h2>'.$this->pi_getLL('general_extensionkeys', 1).'</h2>
			<br />			
			'.$topMenu.'<br />
			<br />
			'.$subContent.'
		';
				
		return $this->pi_wrapInBaseClass($content);
	}





	/**
	 * Renders the introduction view
	 * 
	 * @return	string	HTML output
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
	 * @return	string	HTML output
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
			
			case 'registersubmit':
					$accountDataArr = array('username' => $TSFE->fe_user->user['username'], 'password' => $TSFE->fe_user->user['password']);
					$extensionKey = $TSFE->csConv(t3lib_div::GPVar('tx_terfe_pi2_extensionkey'), 'utf-8');

					if (!strlen (t3lib_div::GPVar('tx_terfe_pi2_extensiontitle'))) {
						$output .=  '
							<h4>'.$this->pi_getLL('general_error','',1).'</h4>
							<p>'.$this->pi_getLL('registerkeys_titlemissing','',1).'</p>
						';
					} else {
						$soapClientObj = new SoapClient ($this->WSDLURI, array ('exceptions' => TRUE));
						try {
							$result = $soapClientObj->checkExtensionKey($accountDataArr, $extensionKey);
							if ((integer)$result['resultCode'] == TX_TER_RESULT_EXTENSIONKEYDOESNOTEXIST) {
								$extensionKeyDataArr = array(
									'extensionKey' => $extensionKey,
									'title' => $TSFE->csConv(t3lib_div::GPVar('tx_terfe_pi2_extensionkey'), 'utf-8'),
									'description' => $TSFE->csConv(t3lib_div::GPVar('tx_terfe_pi2_extensiondescription'), 'utf-8'),
									'uploadPassword' => $TSFE->csConv(t3lib_div::GPVar('tx_terfe_pi2_extensionuploadpassword'), 'utf-8')
								);
								$result = $soapClientObj->registerExtensionKey($accountDataArr, $extensionKeyDataArr);

								$output .= ' 
									<h4>'.$this->pi_getLL('registerkeys_success','',1).'</h4>
									<p>'.$this->pi_getLL('registerkeys_success_explanation','',1).'</p>
								';
								break;
								
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

			case 'registercheck':
					$accountDataArr = array('username' => $TSFE->fe_user->user['username'], 'password' => $TSFE->fe_user->user['password']);
					$extensionKey = $TSFE->csConv(t3lib_div::GPVar('tx_terfe_pi2_extensionkey'), 'utf-8');
			
					$soapClientObj = new SoapClient ($this->WSDLURI, array ('trace' => 1, 'exceptions' => 1));
					try {
						$result = $soapClientObj->checkExtensionKey($accountDataArr, $extensionKey);

						switch ((integer)$result['resultCode']) {												
							case TX_TER_RESULT_EXTENSIONKEYDOESNOTEXIST :
								$output .= ' 
									<h4>'.$this->pi_getLL('registerkeys_title','',1).'</h4>
									<p>'.sprintf ($this->pi_getLL('registerkeys_keyisvalid','',1), '<em>'.$extensionKey.'</em>').'</p>
									<br />
									<form action="" method="post" name="tx_terfe_pi2_register">
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
												<th class="th-sub" nowrap="nowrap">'.$this->pi_getLL('registerkeys_extensionuploadpassword', '', 1).':</th>
												<td class="td-sub"><input type="password" name="tx_terfe_pi2_extensionuploadpassword" size="30" /></td>
												<td><em>'.$this->pi_getLL('registerkeys_extensionuploadpassword_hint', '', 1).'</em></td>
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
		}
		
		return $output;		
	}

	/**
	 * Renders the view for managing extension keys
	 * 
	 * @return	string	HTML output
	 * @access	protected
	 */
	protected function renderView_manageKeys ()	{
		global $TSFE, $TYPO3_DB;

		$output = '';
		$actionMessages = '';

		$iconInfo = '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/info.gif" width="16" height="16" alt="" title="" style="vertical-align:middle;" />';
		$iconError = '<img src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/error.gif" width="16" height="16" alt="" title="" style="vertical-align:middle;" />';

		try {
			$soapClientObj = new SoapClient ($this->WSDLURI, array ('trace' => 1, 'exceptions' => 1));
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
				case 'changepassword':
					$uploadPassword = t3lib_div::GPvar('tx_terfe_pi2_uploadpassword');
					$extensionKey = t3lib_div::GPvar('tx_terfe_pi2_extensionkey');

					$resultArr = $soapClientObj->modifyExtensionKey($accountDataArr, array('extensionKey' => $extensionKey, 'uploadPassword' => $uploadPassword));

					if (is_array ($resultArr)) {
						switch ($resultArr['resultCode']) {
							case TX_TER_RESULT_GENERAL_OK : $actionMessages = '<p>'.$iconInfo.' <strong>'.sprintf ($this->pi_getLL('managekeys_action_changeuploadpassword_success','',1), $extensionKey).'</strong></p><br />'; break;
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
									<td class="td-sub">'.$this->csConvHSC($extensionKeyArr['extensionkey']).'</td>
									<td class="td-sub">'.$this->csConvHSC($extensionKeyArr['title']).'</td>
									<td class="td-sub">
										'.($numberOfVersions > 0 ? $numberOfVersions : '<em>'.$this->pi_getLL('general_none','',1).'</em>').'
									</td>
									<td class="td-sub" nowrap="nowrap">
										<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2_register">
											<input name="tx_terfe_pi2_targetusername" type="text" size="15" />
											<input type="image" src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/transferkey.gif" alt="'.$this->pi_getLL('managekeys_action_transferkey','',1).'" title="'.$this->pi_getLL('managekeys_action_transferkey','',1).'" onFocus="blur()" />
											<input name="tx_terfe_pi2_extensionkey" type="hidden" value="'.$extensionKeyArr['extensionkey'].'" />
											<input name="tx_terfe_pi2_cmd" type="hidden" value="transferkey" />
										</form>
									</td>
									<td class="td-sub" nowrap="nowrap">
										<form action="'.$this->pi_linkTP_keepPIvars_url(array(),1).'" method="post" name="tx_terfe_pi2_register">
											<input name="tx_terfe_pi2_newpassword" type="password" size="15" value="'.$this->pi_getLL('managekeys_newpassword','',1).'" />
											<input type="image" src="'.t3lib_extMgm::siteRelPath('ter_fe').'res/changepassword.gif" alt="'.$this->pi_getLL('managekeys_action_changeuploadpassword','',1).'" title="'.$this->pi_getLL('managekeys_action_changeuploadpassword','',1).'" onFocus="blur()"/>
											<input name="tx_terfe_pi2_extensionkey" type="hidden" value="'.$extensionKeyArr['extensionkey'].'" />
											<input name="tx_terfe_pi2_cmd" type="hidden" value="changepassword" />
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
							<th class="th-sub">'.$this->pi_getLL('registerkeys_extensiontitle','',1).'</th>
							<th class="th-sub">'.$this->pi_getLL('managekeys_uploads','',1).'</th>
							<th class="th-sub">'.$this->pi_getLL('managekeys_transfer','',1).'</th>
							<th class="th-sub">'.$this->pi_getLL('managekeys_changepassword','',1).'</th>
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
	 * Renders the view for registering extension keys
	 * 
	 * @return	string	HTML output
	 * @access	protected
	 */
	protected function renderTopNavigation ()	{
		
		$output = 'Register';
		
		return $output;		
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi2/class.tx_terfe_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fe/pi2/class.tx_terfe_pi2.php']);
}

?>