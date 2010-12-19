<?php

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_terdoc_categories'] = Array (
	'ctrl' => $TCA['tx_terdoc_categories']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'title,description,isdefault,viewpid'
	),
	'columns' => Array (
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:ter_doc/locallang_db.xml:tx_terdoc_categories.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'description' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:ter_doc/locallang_db.xml:tx_terdoc_categories.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'isdefault' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:ter_doc/locallang_db.xml:tx_terdoc_categories.isdefault',
			'config' => Array (
				'type' => 'check',
			)
		),
		'viewpid' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:ter_doc/locallang_db.xml:tx_terdoc_categories.viewpid',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title,description,isdefault,viewpid')
	),
);
?>