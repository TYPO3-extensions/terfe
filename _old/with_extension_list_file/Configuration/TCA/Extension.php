<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$TCA['tx_terfe2_domain_model_extension'] = array(
		'ctrl'      => $TCA['tx_terfe2_domain_model_extension']['ctrl'],
		'interface' => array(
			'showRecordFieldList' => 'ext_key,forge_link,hudson_link,last_update,categories,tags,versions,last_version, frontend_user',
		),
		'types' => array(
			'1' => array('showitem' => 'ext_key,forge_link,hudson_link,last_update,categories,tags,versions,last_version, frontend_user'),
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
					'foreign_table'       => 'tx_terfe2_domain_model_extension',
					'foreign_table_where' => 'AND tx_terfe2_domain_model_extension.uid=###REC_FIELD_l18n_parent### AND tx_terfe2_domain_model_extension.sys_language_uid IN (-1,0)',
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
			'ext_key' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.ext_key',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim,required',
				),
			),
			'forge_link' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.forge_link',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim',
				),
			),
			'hudson_link' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.hudson_link',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim',
				),
			),
			'last_upload' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.last_upload',
				'config'  => array(
					'type'     => 'input',
					'size'     => 12,
					'max'      => 20,
					'eval'     => 'datetime',
					'default'  => '0',
				),
			),
			'last_maintained' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.last_maintained',
				'config'  => array(
					'type'     => 'input',
					'size'     => 12,
					'max'      => 20,
					'eval'     => 'datetime',
					'default'  => '0',
				),
			),
			'categories' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.categories',
				'config'  => array(
					'type'              => 'select',
					'size'              => 3,
					'minitems'          => 0,
					'maxitems'          => 9999,
					'autoSizeMax'       => 10,
					'multiple'          => 0,
					'foreign_table'     => 'tx_terfe2_domain_model_category',
					'MM'                => 'tx_terfe2_extension_category_mm',
					'MM_opposite_field' => 'extensions',
				),
			),
			'tags' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.tags',
				'config'  => array(
					'type'              => 'select',
					'size'              => 3,
					'minitems'          => 0,
					'maxitems'          => 9999,
					'autoSizeMax'       => 10,
					'multiple'          => 0,
					'foreign_table'     => 'tx_terfe2_domain_model_tag',
					'MM'                => 'tx_terfe2_extension_tag_mm',
					'MM_opposite_field' => 'extensions',
				),
			),
			'versions' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.versions',
				'config'  => array(
					'type'          => 'inline',
					'foreign_table' => 'tx_terfe2_domain_model_version',
					'foreign_field' => 'extension',
					'maxitems'      => 9999,
					'appearance'    => array(
						'collapse'              => 0,
						'newRecordLinkPosition' => 'bottom',
					),
				),
			),
			'last_version' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.last_version',
				'config'  => array(
					'type'          => 'inline',
					'foreign_table' => 'tx_terfe2_domain_model_version',
					'maxitems'      => 1,
				),
			),
			'frontend_user' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_extension.frontend_user',
				'config'  => array(
					'type'          => 'inline',
					'foreign_table' => 'fe_users',
					'maxitems'      => 1,
				),
			),
		),
	);
?>