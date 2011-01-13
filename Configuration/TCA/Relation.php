<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$TCA['tx_terfe2_domain_model_relation'] = array(
		'ctrl'      => $TCA['tx_terfe2_domain_model_relation']['ctrl'],
		'interface' => array(
			'showRecordFieldList' => 'relation_type,software_type,tx_terfe2_key,version',
		),
		'types' => array(
			'1' => array('showitem' => 'relation_type,software_type,tx_terfe2_key,version'),
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
						array('LLL:EXT:lang/locallang_general.php:LGL.default_value', 0)
					),
				),
			),
			'l18n_parent' => array(
				'displayCond' => 'FIELD:sys_language_uid:>:0',
				'exclude'     => 1,
				'label'       => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
				'config' => array(
					'type'                => 'select',
					'foreign_table'       => 'tx_terfe2_domain_model_relation',
					'foreign_table_where' => 'AND tx_terfe2_domain_model_relation.uid=###REC_FIELD_l18n_parent### AND tx_terfe2_domain_model_relation.sys_language_uid IN (-1,0)',
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
				)
			),
			'hidden' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
				'config'  => array(
					'type' => 'check',
				)
			),
			'relation_type' => array(
				'exclude'   => 1,
				'label'     => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_relation.relation_type',
				'config'    => array(
					'type'     => 'select',
					'size'     => 1,
					'maxitems' => 1,
					'eval'     => 'required',
					'items'    => array (
						array('-- Label --', 0),
					),
				)
			),
			'software_type' => array(
				'exclude'    => 1,
				'label'      => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_relation.software_type',
				'config'     => array(
					'type'     => 'select',
					'size'     => 1,
					'maxitems' => 1,
					'eval'     => 'required',
					'items'    => array (
						array('-- Label --', 0),
					),
				)
			),
			'tx_terfe2_key' => array(
				'exclude'    => 1,
				'label'      => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_relation.tx_terfe2_key',
				'config'     => array(
					'type'    => 'input',
					'size'    => 30,
					'eval'    => 'trim,required',
				)
			),
			'version' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_relation.version',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim',
				)
			),
			'version' => array(
				'config' => array(
					'type' => 'passthrough',
				)
			),
		),
	);
?>