<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	// Add plugin to list
	Tx_Extbase_Utility_Extension::registerPlugin(
		$_EXTKEY,
		'Pi1',
		'TER Frontend Index'
	);

	// Add static TypoScript files
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'TER Frontend Index');

	// Add flexform to field list of the Backend form
	$extIdent = strtolower(t3lib_div::underscoredToUpperCamelCase($_EXTKEY)) . '_pi1';
	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$extIdent] = 'layout,select_key,recursive';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$extIdent] = 'pi_flexform';
	t3lib_extMgm::addPiFlexFormValue($extIdent, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_list.xml');

	// Domain models and their label field
	$models = array(
		'extension'  => 'ext_key',
		'category'   => 'title',
		'tag'        => 'title',
		'version'    => 'title',
		'media'      => 'title',
		'experience' => 'date_time',
		'relation'   => 'relation_type',
	);

	// Add entities and value objects
	foreach ($models as $modelName => $labelField) {

		// Add help text to the Backend form
		t3lib_extMgm::addLLrefForTCAdescr(
			'tx_terfe2_domain_model_' . $modelName,
			'EXT:ter_fe2/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_' . $modelName . '.xml'
		);

		// Allow datasets on standard pages
		t3lib_extMgm::allowTableOnStandardPages('tx_terfe2_domain_model_' . $modelName);

		// Add table configuration
		$TCA['tx_terfe2_domain_model_' . $modelName] = array (
			'ctrl' => array (
				'title'                    => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_' . $modelName,
				'label'                    => $labelField,
				'tstamp'                   => 'tstamp',
				'crdate'                   => 'crdate',
				'versioningWS'             => 2,
				'versioning_followPages'   => TRUE,
				'origUid'                  => 't3_origuid',
				'languageField'            => 'sys_language_uid',
				'transOrigPointerField'    => 'l18n_parent',
				'transOrigDiffSourceField' => 'l18n_diffsource',
				'delete'                   => 'deleted',
				'enablecolumns'            => array(
					'disabled'                => 'hidden'
				),
				'dynamicConfigFile'        => t3lib_extMgm::extPath($_EXTKEY)    . 'Configuration/TCA/' . ucfirst($modelName) . '.php',
				'iconfile'                 => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_terfe2_domain_model_' . $modelName . '.gif'
			)
		);

	}
?>