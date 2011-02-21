<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$TCA['tx_terfe2_domain_model_versionrange'] = array(
		'ctrl'      => $TCA['tx_terfe2_domain_model_versionrange']['ctrl'],
		'interface' => array(
			'showRecordFieldList' => 'minimum_value,maximum_value',
		),
		'types' => array(
			'1' => array('showitem' => 'minimum_value,maximum_value'),
		),
		'palettes' => array(
			'1' => array('showitem' => ''),
		),
		'columns' => array(
			'sys_language_uid' => array(
				'exclude'      => 1,
				'label'        => 'LLL:EXT:lang/locallang_general.php:LGL.language',
				'config'       => array(
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
					'type'     => 'select',
					'items'    => array(
						array('', 0),
					),
					'foreign_table'       => 'tx_terfe2_domain_model_versionrange',
					'foreign_table_where' => 'AND tx_terfe2_domain_model_versionrange.uid=###REC_FIELD_l18n_parent### AND tx_terfe2_domain_model_versionrange.sys_language_uid IN (-1,0)',
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
			'minimum_value' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_versionrange.minimum_value',
				'config'  => array(
					'type' => 'input',
					'size' => 12,
					'eval' => 'int',
				),
			),
			'maximum_value' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_versionrange.maximum_value',
				'config'  => array(
					'type' => 'input',
					'size' => 12,
					'eval' => 'int',
				),
			),
		),
	);
?>