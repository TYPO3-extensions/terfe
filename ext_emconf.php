<?php

########################################################################
# Extension Manager/Repository config file for ext "ter_fe2".
#
# Auto generated 13-01-2011 20:33
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TER Frontend Index',
	'description' => 'New TER Frontend based on Extbase and Fluid',
	'category' => 'plugin',
	'author' => 'Kai Vogel,Thomas Loeffler',
	'author_email' => 'kai.vogel@speedprogs.de,loeffler@spooner-web.de',
	'author_company' => 'Speedprogs.de,Spooner Web',
	'shy' => '',
	'dependencies' => 'cms,extbase,fluid',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'extbase' => '',
			'fluid' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'em' => '',
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:50:{s:21:"ext_conf_template.txt";s:4:"f21e";s:12:"ext_icon.gif";s:4:"e922";s:17:"ext_localconf.php";s:4:"73e2";s:14:"ext_tables.php";s:4:"0c93";s:14:"ext_tables.sql";s:4:"c6da";s:16:"kickstarter.json";s:4:"0478";s:42:"Classes/Controller/ExtensionController.php";s:4:"a349";s:33:"Classes/Domain/Model/Category.php";s:4:"41d0";s:35:"Classes/Domain/Model/Experience.php";s:4:"c639";s:34:"Classes/Domain/Model/Extension.php";s:4:"813f";s:30:"Classes/Domain/Model/Media.php";s:4:"3231";s:33:"Classes/Domain/Model/Relation.php";s:4:"2eec";s:28:"Classes/Domain/Model/Tag.php";s:4:"5487";s:32:"Classes/Domain/Model/Version.php";s:4:"5a55";s:49:"Classes/Domain/Repository/ExtensionRepository.php";s:4:"a079";s:41:"Configuration/FlexForms/flexform_list.xml";s:4:"6bb0";s:30:"Configuration/TCA/Category.php";s:4:"91f6";s:32:"Configuration/TCA/Experience.php";s:4:"3e9a";s:31:"Configuration/TCA/Extension.php";s:4:"1b00";s:27:"Configuration/TCA/Media.php";s:4:"34da";s:30:"Configuration/TCA/Relation.php";s:4:"bc1b";s:25:"Configuration/TCA/Tag.php";s:4:"44e0";s:29:"Configuration/TCA/Version.php";s:4:"6e88";s:38:"Configuration/TypoScript/constants.txt";s:4:"4db4";s:34:"Configuration/TypoScript/setup.txt";s:4:"284c";s:40:"Resources/Private/Language/locallang.xml";s:4:"8d09";s:76:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_category.xml";s:4:"aff6";s:78:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_experience.xml";s:4:"45be";s:77:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_extension.xml";s:4:"b703";s:73:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_media.xml";s:4:"62c2";s:76:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_relation.xml";s:4:"41b8";s:71:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_tag.xml";s:4:"bab5";s:75:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_version.xml";s:4:"4450";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"3eb5";s:38:"Resources/Private/Layouts/default.html";s:4:"1eeb";s:42:"Resources/Private/Partials/formErrors.html";s:4:"f5bc";s:49:"Resources/Private/Templates/Extension/create.html";s:4:"d41d";s:47:"Resources/Private/Templates/Extension/edit.html";s:4:"7dc0";s:48:"Resources/Private/Templates/Extension/index.html";s:4:"062f";s:47:"Resources/Private/Templates/Extension/list.html";s:4:"d41d";s:46:"Resources/Private/Templates/Extension/new.html";s:4:"de7d";s:47:"Resources/Private/Templates/Extension/show.html";s:4:"4579";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:58:"Resources/Public/Icons/tx_terfe2_domain_model_category.gif";s:4:"4e5b";s:60:"Resources/Public/Icons/tx_terfe2_domain_model_experience.gif";s:4:"4e5b";s:59:"Resources/Public/Icons/tx_terfe2_domain_model_extension.gif";s:4:"905a";s:55:"Resources/Public/Icons/tx_terfe2_domain_model_media.gif";s:4:"1103";s:58:"Resources/Public/Icons/tx_terfe2_domain_model_relation.gif";s:4:"4e5b";s:53:"Resources/Public/Icons/tx_terfe2_domain_model_tag.gif";s:4:"4e5b";s:57:"Resources/Public/Icons/tx_terfe2_domain_model_version.gif";s:4:"1103";}',
);

?>