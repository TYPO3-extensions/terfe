<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:ter_fe/flexform_ds_pluginmode_pi1.xml');
t3lib_extMgm::addPlugin(Array('LLL:EXT:ter_fe/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages,recursive';
t3lib_extMgm::addPlugin(Array('LLL:EXT:ter_fe/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key,pages,recursive';
t3lib_extMgm::addPlugin(Array('LLL:EXT:ter_fe/locallang_db.xml:tt_content.list_type_pi3', $_EXTKEY.'_pi3'),'list_type');

if (TYPO3_MODE=='BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_terfe_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_terfe_pi1_wizicon.php';
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_terfe_pi2_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_terfe_pi2_wizicon.php';
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_terfe_pi3_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi3/class.tx_terfe_pi3_wizicon.php';
}

?>