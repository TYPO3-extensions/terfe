<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	require_once (t3lib_extMgm::extPath ('ter_doc').'class.tx_terdoc_renderdocuments.php');
	require_once (t3lib_extMgm::extPath ('ter_doc_sxw').'class.tx_terdocsxw.php');

	$renderDocsObj = tx_terdoc_renderdocuments::getInstance();
	$renderDocsObj->registerOutputFormat ('ter_doc_sxw', 'LLL:EXT:ter_doc_sxw/locallang.xml:format_sxw', 'download', new tx_terdocsxw);

?>