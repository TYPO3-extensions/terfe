<?php
	if (!defined ('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$GLOBALS['TCA']['tx_terfe2_domain_model_search'] = array(
		'ctrl'      => $GLOBALS['TCA']['tx_terfe2_domain_model_search']['ctrl'],
		'interface' => array(
			'showRecordFieldList' => 'extension_key,title,description,author_list,upload_comment,version_string,state,em_category,software_relation_list,category_list,tag_list,version_uid,extension_uid',
		),
		'types' => array(
			'1' => array('showitem' => 'extension_key,title,description,author_list,upload_comment,version_string,state,em_category,software_relation_list,category_list,tag_list,version_uid,extension_uid'),
		),
		'palettes' => array(
			'1' => array('showitem' => ''),
		),
		'columns' => array(
			'extension_key' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'title' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'description' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'author_list' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'upload_comment' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'version_string' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'state' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'em_category' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'software_relation_list' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'category_list' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'tag_list' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'version_uid' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
			'extension_uid' => array(
				'config' => array(
					'type' => 'passthrough',
				),
			),
		),
	);
?>