<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	require_once (t3lib_extMgm::extPath ('ter_doc').'class.tx_terdoc_renderdocuments.php');
	require_once (t3lib_extMgm::extPath ('ter_doc_renderproblems').'class.tx_terdocrenderproblems_display.php');

	$renderDocsObj = tx_terdoc_renderdocuments::getInstance();
	$renderDocsObj->registerOutputFormat ('ter_doc_renderproblems', 'LLL:EXT:ter_doc_renderproblems/locallang.xml:format_display', 'display', new tx_terdocrenderproblems_display);

?>