<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	require_once (t3lib_extMgm::extPath ('ter_doc').'class.tx_terdoc_renderdocuments.php');
	require_once (t3lib_extMgm::extPath ('ter_doc_docbook').'class.tx_terdocdocbook.php');

	$renderDocsObj = tx_terdoc_renderdocuments::getInstance();
	$renderDocsObj->registerOutputFormat ('ter_doc_docbook', 'LLL:EXT:ter_doc_docbook/locallang.xml:format_docbook', 'download', new tx_terdocdocbook);

?>