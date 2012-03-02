<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$GLOBALS['TCA']['tx_terfe2_domain_model_media'] = array(
		'ctrl'      => $GLOBALS['TCA']['tx_terfe2_domain_model_media']['ctrl'],
		'interface' => array(
			'showRecordFieldList' => 'title,type,language,source,description',
		),
		'types' => array(
			'1' => array('showitem' => 'title,type,language,source,description'),
		),
		'palettes' => array(
			'1' => array('showitem' => ''),
		),
		'columns' => array(
			'sys_language_uid' => array(
				'exclude'       => 1,
				'label'         => 'LLL:EXT:lang/locallang_general.php:LGL.language',
				'config'        => array(
					'type'                => 'select',
					'foreign_table'       => 'sys_language',
					'foreign_table_where' => 'ORDER BY sys_language.title',
					'items'               => array(
						array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages', -1),
						array('LLL:EXT:lang/locallang_general.php:LGL.default_value', 0),
					),
				),
			),
			'l18n_parent' => array(
				'displayCond' => 'FIELD:sys_language_uid:>:0',
				'exclude'     => 1,
				'label'       => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
				'config'      => array(
					'type'                => 'select',
					'foreign_table'       => 'tx_terfe2_domain_model_media',
					'foreign_table_where' => 'AND tx_terfe2_domain_model_media.uid=###REC_FIELD_l18n_parent### AND tx_terfe2_domain_model_media.sys_language_uid IN (-1,0)',
					'items'               => array(
						array('', 0),
					),
				),
			),
			'l18n_diffsource' => array(
				'config'       => array(
					'type'      => 'passthrough',
				),
			),
			't3ver_label' => array(
				'displayCond' => 'FIELD:t3ver_label:REQ:true',
				'label'       => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
				'config'      => array(
					'type'     => 'none',
					'cols'     => 27,
				),
			),
			'hidden' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
				'config'  => array(
					'type' => 'check',
				),
			),
			'title' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_media.title',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim,required',
				),
			),
			'type' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_media.type',
				'config'  => array(
					'type'     => 'select',
					'size'     => 1,
					'maxitems' => 1,
					'eval'     => 'required',
					'items'    => array (
						array('LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_media.type.0', 0),
						array('LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_media.type.1', 1),
						array('LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_media.type.2', 2),
					),
				),
			),
			'language' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_media.language',
				'config' => array(
				'type' => 'select',
				'items' => array(
					array('',0),
				),
				'itemsProcFunc' => 'tx_staticinfotables_div->selectItemsTCA',
				'itemsProcFunc_config' => array(
					'table' => 'static_countries',
					'indexField' => 'cn_iso_3',
				),
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'default' => ''
			)
			),
			'source' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_media.source',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim,required',
				),
			),
			'description' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_media.description',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim',
				),
			),
			'version' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
		),
	);
?>