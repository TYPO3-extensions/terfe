<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

		// Make plugin available in frontend
	Tx_Extbase_Utility_Extension::configurePlugin(
		$_EXTKEY,
		'Pi1',
		array(
			'Extension' => 'index, show, new, create, edit, update, delete, download, list, listLatest',
			'Category'  => 'list, new, create, edit, update, delete, show',
			'Tag'       => 'list, new, create, edit, update, delete, show',
			'Author'    => 'list, edit, update, show',
			'Media'     => 'list, new, create, edit, update, delete, show',
		),
		array(
			'Extension' => 'create, update, delete',
			'Category'  => 'create, update, delete',
			'Tag'       => 'create, delete',
			'Author'    => 'update',
			'Media'     => 'create, delete',
		)
	);

		// Register extension providers
	if (!isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$_EXTKEY]['extensionProviders'])) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$_EXTKEY]['extensionProviders'] = array();
	}
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$_EXTKEY]['extensionProviders']['extensionmanager'] = array(
		'class' => 'Tx_TerFe2_Provider_ExtensionManagerProvider',
		'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_terfe2_provider_extensionmanagerprovider.name',
		'configuration' => array(
			'repositoryId'      => 1,
			'fileCachePath'     => 'typo3temp/tx_terfe2/files/',
		),
	);
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$_EXTKEY]['extensionProviders']['file'] = array(
		'class' => 'Tx_TerFe2_Provider_FileProvider',
		'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_terfe2_provider_fileprovider.name',
		'configuration' => array(
			'extensionRootPath' => 'fileadmin/ter/',
			'extensionListFile' => 'fileadmin/ter/extensions.xml.gz',
		),
	);
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$_EXTKEY]['extensionProviders']['soap'] = array(
		'class' => 'Tx_TerFe2_Provider_SoapProvider',
		'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_terfe2_provider_soapprovider.name',
		'configuration' => array(
			'wsdlUrl'           => '',
			'username'          => '',
			'password'          => '',
			'getExtensionsFunc' => 'getExtensions',
			'getFileUrlFunc'    => 'getFileUrl',
			'getFileNameFunc'   => 'getFileName',
		),
	);

		// Register create zip archives task
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx_TerFe2_Task_CreateExtensionFilesTask'] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_terfe2_task_createextensionfilestask.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_terfe2_task_createextensionfilestask.description',
		'additionalFields' => 'tx_terfe2_task_createextensionfilestaskadditionalfieldprovider',
	);

		// Register extension list update task
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Tx_TerFe2_Task_UpdateExtensionListTask'] = array(
		'extension'        => $_EXTKEY,
		'title'            => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_terfe2_task_updateextensionlisttask.name',
		'description'      => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml:tx_terfe2_task_updateextensionlisttask.description',
		'additionalFields' => 'tx_terfe2_task_updateextensionlisttaskadditionalfieldprovider',
	);

?>