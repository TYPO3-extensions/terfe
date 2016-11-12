<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

	// Add plugin to list
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pi1',
	'TER Frontend Index'
);

	// Add static TypoScript files
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Default/', 'TER Frontend - Default Configuration');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Rss/',     'TER Frontend - RSS Output');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Json/',    'TER Frontend - JSON Output');

	// Add flexform to field list of the Backend form
$extIdent = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY)) . '_pi1';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$extIdent] = 'layout,select_key,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$extIdent] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($extIdent, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_list.xml');

	// Domain models and their label / search fields
$models = array(
	'extension'  => array('ext_key', 'ext_key'),
	'category'   => array('title', 'title,description'),
	'tag'        => array('title', 'title'),
	'version'    => array('title', 'title,description,state,em_category'),
	'media'      => array('title', 'title,type,language,source,description'),
	'experience' => array('date_time', 'comment'),
	'relation'   => array('relation_key', 'relation_type,relation_key'),
	'author'     => array('name', 'name,email,username'),
);

	// Add entities and value objects
foreach ($models as $modelName => $modelConfiguration) {
		// Add help text to the Backend form
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
		'tx_terfe2_domain_model_' . $modelName,
		'EXT:ter_fe2/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_' . $modelName . '.xml'
	);

		// Allow datasets on standard pages
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_terfe2_domain_model_' . $modelName);

		// Add table configuration
	$GLOBALS['TCA']['tx_terfe2_domain_model_' . $modelName] = array (
		'ctrl' => array (
			'title'                    => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_' . $modelName,
			'label'                    => $modelConfiguration[0],
			'searchFields'             => $modelConfiguration[1],
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
				'disabled'                 => 'hidden'
			),
			'dynamicConfigFile'        => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/' . ucfirst($modelName) . '.php',
			'iconfile'                 => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/' . $modelName . '.gif',
		),
	);
}

	// Add table configuration for the search index table
$GLOBALS['TCA']['tx_terfe2_domain_model_search'] = array (
	'ctrl' => array (
		'hideTable'         => TRUE,
		'tstamp'            => 'tstamp',
		'crdate'            => 'crdate',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Search.php',
	),
);

	// Add plugin to new content element wizard
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig("
	mod.wizards.newContentElement.wizardItems.special {\n
		elements." . $extIdent . " {\n
			icon        = " . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . "Resources/Public/Images/Wizard.gif\n
			title       = LLL:EXT:" . $_EXTKEY . "/Resources/Private/Language/locallang_db.xml:newContentElement.wizardItem.title\n
			description = LLL:EXT:" . $_EXTKEY . "/Resources/Private/Language/locallang_db.xml:newContentElement.wizardItem.description\n\n
			tt_content_defValues {\n
				CType = list\n
				list_type = " . $extIdent . "\n
			}\n
		}\n\n
		show := addToList(" . $extIdent . ")\n
	}
");
?>