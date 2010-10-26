<?php

########################################################################
# Extension Manager/Repository config file for ext: "rlmp_officelib"
#
# Auto generated 21-05-2008 19:03
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Documents Suite code library',
	'description' => 'This extension provides an API for dealing with office documents in general. It offers an object model with several methods acting as templates for your own implementations. OOwriter documents are supported by default.',
	'category' => 'misc',
	'shy' => 0,
	'version' => '0.2.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Robert Lemke',
	'author_email' => 'robert@typo3.org',
	'author_company' => 'robert lemke medienprojekte',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '3.5.0-0.0.0',
			'php' => '3.0.0-0.0.0',
			'cms' => '',
			'libunzipped' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:15:{s:9:"Changelog";s:4:"0d09";s:30:"class.tx_rlmpofficelib_div.php";s:4:"5195";s:41:"class.tx_rlmpofficelib_officedocument.php";s:4:"4a9b";s:40:"class.tx_rlmpofficelib_officefactory.php";s:4:"22be";s:37:"class.tx_rlmpofficelib_officemeta.php";s:4:"cbae";s:45:"class.tx_rlmpofficelib_officenulliterator.php";s:4:"3009";s:50:"class.tx_rlmpofficelib_officepagebreakiterator.php";s:4:"ff40";s:46:"class.tx_rlmpofficelib_officetextcomponent.php";s:4:"d3e6";s:45:"class.tx_rlmpofficelib_officetextiterator.php";s:4:"bb1d";s:37:"class.tx_rlmpofficelib_renderhtml.php";s:4:"fab0";s:8:"debug.js";s:4:"25f0";s:12:"ext_icon.gif";s:4:"662c";s:12:"doc/TODO.txt";s:4:"2b90";s:52:"oowriter/class.tx_rlmpofficelib_oowriterdocument.php";s:4:"538e";s:56:"oowriter/class.tx_rlmpofficelib_oowritertextiterator.php";s:4:"f388";}',
);

?>