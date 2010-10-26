<?php

########################################################################
# Extension Manager/Repository config file for ext: "ter_doc_renderproblems"
#
# Auto generated 22-05-2008 02:38
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TER Docs render problems',
	'description' => 'Displays detailed information about why a document could not be rendered',
	'category' => 'misc',
	'author' => 'Robert Lemke',
	'author_email' => 'robert@typo3.org',
	'shy' => '',
	'dependencies' => 'cms,ter_doc',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '1.0.0',
	'_md5_values_when_last_written' => 'a:5:{s:9:"ChangeLog";s:4:"bfe6";s:41:"class.tx_terdocrenderproblems_display.php";s:4:"e89b";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"d0a5";s:13:"locallang.xml";s:4:"7f78";}',
	'constraints' => array(
		'requires' => array(
			'typo3' => '4.0.0-',
			'php' => '5.0.0-',
			'ter_doc' => '2.0.1-',
		),
		'depends' => array(
			'cms' => '',
			'ter_doc' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>