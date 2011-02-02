<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	// Make plugin available in Frontend
	Tx_Extbase_Utility_Extension::configurePlugin(
		$_EXTKEY,
		'Pi1',
		array(
			'Extension' => 'index, list, listLatest, listByCategory, listByTag, show, new, create, edit, update, delete, createVersion',
		),
		array(
			'Extension' => 'create, update, delete, createVersion',
		)
	);

	// Register extension list update task
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx_TerFe2_Task_UpdateExtensionListTask'] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_terfe2_task_updateextensionlisttask.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_terfe2_task_updateextensionlisttask.description',
		'additionalFields' => '',
	);
?>