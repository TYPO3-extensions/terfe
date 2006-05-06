<?php

########################################################################
# Extension Manager/Repository config file for ext: "ter_fe"
# 
# Auto generated 06-05-2006 19:17
# 
# Manual updates:
# Only the data in the array - anything else is removed by next write
########################################################################

$EM_CONF[$_EXTKEY] = Array (
	'title' => 'TER Frontend',
	'description' => 'Frontend for the TYPO3 Extension Repository',
	'category' => 'fe',
	'author' => 'Robert Lemke',
	'author_email' => 'robert@typo3.org',
	'shy' => '',
	'dependencies' => 'cms,lang,captcha',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => 'typo3temp/tx_terfe/t3xcontentcache/',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'The TYPO3 Association',
	'version' => '0.0.0',	// Don't modify this! Managed automatically during upload to repository.
	'_md5_values_when_last_written' => 'a:93:{s:31:".#class.tx_terfe_common.php.1.2";s:4:"0014";s:25:".locallang_common.xml.swp";s:4:"00cd";s:8:".project";s:4:"e45a";s:9:"ChangeLog";s:4:"3a0d";s:25:"class.tx_terfe_common.php";s:4:"a458";s:21:"ext_conf_template.txt";s:4:"0902";s:12:"ext_icon.gif";s:4:"1cd6";s:17:"ext_localconf.php";s:4:"1725";s:14:"ext_tables.php";s:4:"4414";s:14:"ext_tables.sql";s:4:"53c8";s:24:"ext_typoscript_setup.txt";s:4:"35fc";s:30:"flexform_ds_pluginmode_pi1.xml";s:4:"ec1e";s:30:"flexform_ds_pluginmode_pi2.xml";s:4:"79a4";s:13:"locallang.xml";s:4:"01d3";s:20:"locallang_common.xml";s:4:"06dc";s:16:"locallang_db.xml";s:4:"1438";s:11:"CVS/Entries";s:4:"496a";s:14:"CVS/Repository";s:4:"02e7";s:8:"CVS/Root";s:4:"78ff";s:33:"pi1/.#class.tx_terfe_pi1.php.1.16";s:4:"afc5";s:32:"pi1/.#class.tx_terfe_pi1.php.1.7";s:4:"d9f6";s:23:"pi1/.#locallang.xml.1.4";s:4:"e2e3";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:26:"pi1/class.tx_terfe_pi1.php";s:4:"072a";s:30:"pi1/class.tx_terfe_pi1.php.bak";s:4:"d9f6";s:30:"pi1/class.tx_terfe_pi1.php.old";s:4:"fc72";s:34:"pi1/class.tx_terfe_pi1_wizicon.php";s:4:"458c";s:30:"pi1/class.tx_terfe_ratings.php";s:4:"75be";s:25:"pi1/clean_with_index.diff";s:4:"247a";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"a8d7";s:25:"pi1/pi1_cleanup_only.diff";s:4:"f7e8";s:15:"pi1/CVS/Entries";s:4:"df0e";s:18:"pi1/CVS/Repository";s:4:"2ab0";s:12:"pi1/CVS/Root";s:4:"78ff";s:14:"pi2/ce_wiz.gif";s:4:"02b6";s:26:"pi2/class.tx_terfe_pi2.php";s:4:"359c";s:34:"pi2/class.tx_terfe_pi2_wizicon.php";s:4:"b97e";s:13:"pi2/clear.gif";s:4:"cc11";s:17:"pi2/locallang.xml";s:4:"1f65";s:15:"pi2/CVS/Entries";s:4:"e6f1";s:18:"pi2/CVS/Repository";s:4:"f18b";s:12:"pi2/CVS/Root";s:4:"78ff";s:19:"res/.ter_fe.css.swp";s:4:"4baf";s:22:"res/changepassword.gif";s:4:"8be0";s:20:"res/comparefiles.gif";s:4:"4e40";s:14:"res/delete.gif";s:4:"46fc";s:13:"res/error.gif";s:4:"2611";s:16:"res/greenled.gif";s:4:"48a7";s:15:"res/greyled.gif";s:4:"3bd8";s:12:"res/info.gif";s:4:"d67e";s:14:"res/redled.gif";s:4:"c9cc";s:19:"res/state_alpha.gif";s:4:"f971";s:18:"res/state_beta.gif";s:4:"81f5";s:26:"res/state_experimental.gif";s:4:"1698";s:16:"res/state_na.gif";s:4:"fb15";s:22:"res/state_obsolete.gif";s:4:"6cd7";s:20:"res/state_stable.gif";s:4:"59f7";s:18:"res/state_test.gif";s:4:"f92e";s:14:"res/ter_fe.css";s:4:"c972";s:19:"res/transferkey.gif";s:4:"ba35";s:15:"res/warning.gif";s:4:"3330";s:17:"res/yellowled.gif";s:4:"d927";s:15:"res/CVS/Entries";s:4:"555a";s:18:"res/CVS/Repository";s:4:"8e57";s:12:"res/CVS/Root";s:4:"78ff";s:25:"res/icons/state_alpha.gif";s:4:"f971";s:24:"res/icons/state_beta.gif";s:4:"81f5";s:33:"res/icons/state_beta_reviewed.gif";s:4:"1ead";s:32:"res/icons/state_experimental.gif";s:4:"1698";s:26:"res/icons/state_stable.gif";s:4:"59f7";s:35:"res/icons/state_stable_reviewed.gif";s:4:"8fea";s:21:"res/icons/CVS/Entries";s:4:"2975";s:24:"res/icons/CVS/Repository";s:4:"c367";s:18:"res/icons/CVS/Root";s:4:"78ff";s:25:"res/icons/CVS/CVS/Entries";s:4:"57b8";s:28:"res/icons/CVS/CVS/Repository";s:4:"6f8e";s:22:"res/icons/CVS/CVS/Root";s:4:"78ff";s:14:"doc/manual.sxw";s:4:"fe2d";s:15:"doc/CVS/Entries";s:4:"57b8";s:18:"doc/CVS/Repository";s:4:"ebcc";s:12:"doc/CVS/Root";s:4:"78ff";s:14:"pi3/ce_wiz.gif";s:4:"02b6";s:26:"pi3/class.tx_terfe_pi3.php";s:4:"acc3";s:34:"pi3/class.tx_terfe_pi3_wizicon.php";s:4:"bf4f";s:13:"pi3/clear.gif";s:4:"cc11";s:17:"pi3/locallang.xml";s:4:"9a33";s:15:"pi3/CVS/Entries";s:4:"c056";s:18:"pi3/CVS/Repository";s:4:"81c9";s:12:"pi3/CVS/Root";s:4:"78ff";s:19:"pi3/CVS/CVS/Entries";s:4:"57b8";s:22:"pi3/CVS/CVS/Repository";s:4:"4c80";s:16:"pi3/CVS/CVS/Root";s:4:"78ff";}',
	'constraints' => 'Array',
);

?>