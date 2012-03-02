<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$GLOBALS['TCA']['tx_terfe2_domain_model_author'] = array(
		'ctrl'      => $GLOBALS['TCA']['tx_terfe2_domain_model_author']['ctrl'],
		'interface' => array(
			'showRecordFieldList' => 'name,email,company,forge_link,username,versions',
		),
		'types' => array(
			'1' => array('showitem' => 'name,email,company,forge_link,username,versions'),
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
					'foreign_table'       => 'tx_terfe2_domain_model_author',
					'foreign_table_where' => 'AND tx_terfe2_domain_model_author.uid=###REC_FIELD_l18n_parent### AND tx_terfe2_domain_model_author.sys_language_uid IN (-1,0)',
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
			'name' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_author.name',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim,required',
				),
			),
			'email' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_author.email',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim',
				),
			),
			'company' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_author.company',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim',
				),
			),
			'forge_link' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_author.forge_link',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim',
				),
			),
			'username' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_author.username',
				'config'  => array(
					'type' => 'input',
					'size' => 30,
					'eval' => 'trim',
				),
			),
			'versions' => array(
				'exclude' => 1,
				'label'   => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang_db.xml:tx_terfe2_domain_model_author.versions',
				'config'  => array(
					'type'          => 'inline',
					'foreign_table' => 'tx_terfe2_domain_model_version',
					'foreign_field' => 'author',
					'maxitems'      => 9999,
					'appearance'    => array(
						'collapse'              => 0,
						'newRecordLinkPosition' => 'bottom',
					),
				),
			),
		),
	);
?>