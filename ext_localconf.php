<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	// Make plugin available in Frontend
	Tx_Extbase_Utility_Extension::configurePlugin(
		$_EXTKEY,
		'Pi1',
		array(
			'Extension' => 'index, list, show, new, create, edit, update, delete, createVersion',
		),
		array(
			'Extension' => 'create, update, delete, createVersion',
		)
	);
?>