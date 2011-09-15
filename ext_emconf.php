<?php

########################################################################
# Extension Manager/Repository config file for ext "ter_fe2".
#
# Auto generated 01-09-2011 10:49
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TER Frontend Index',
	'description' => 'New TER Frontend based on Extbase and Fluid',
	'category' => 'plugin',
	'author' => 'Kai Vogel',
	'author_email' => 'kai.vogel@speedprogs.de',
	'author_company' => 'Speedprogs.de',
	'shy' => '',
	'dependencies' => 'cms,extbase,fluid',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => 'typo3temp/tx_terfe2/images/,typo3temp/tx_terfe2/files/',
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
	'_md5_values_when_last_written' => 'a:223:{s:9:"ChangeLog";s:4:"01b0";s:16:"ext_autoload.php";s:4:"3898";s:12:"ext_icon.gif";s:4:"37b8";s:17:"ext_localconf.php";s:4:"54f5";s:14:"ext_tables.php";s:4:"46e3";s:14:"ext_tables.sql";s:4:"e0c1";s:31:"Classes/Domain/Model/Author.php";s:4:"f152";s:33:"Classes/Domain/Model/Category.php";s:4:"8f31";s:35:"Classes/Domain/Model/Experience.php";s:4:"c10b";s:34:"Classes/Domain/Model/Extension.php";s:4:"d59e";s:51:"Classes/Domain/Model/ExtensionManagerCacheEntry.php";s:4:"97b4";s:30:"Classes/Domain/Model/Media.php";s:4:"ce48";s:33:"Classes/Domain/Model/Relation.php";s:4:"b2b0";s:28:"Classes/Domain/Model/Tag.php";s:4:"bafd";s:32:"Classes/Domain/Model/Version.php";s:4:"3a17";s:48:"Classes/Domain/Repository/AbstractRepository.php";s:4:"1a13";s:46:"Classes/Domain/Repository/AuthorRepository.php";s:4:"8260";s:48:"Classes/Domain/Repository/CategoryRepository.php";s:4:"70b7";s:66:"Classes/Domain/Repository/ExtensionManagerCacheEntryRepository.php";s:4:"6252";s:49:"Classes/Domain/Repository/ExtensionRepository.php";s:4:"6ecb";s:43:"Classes/Domain/Repository/TagRepository.php";s:4:"8925";s:46:"Classes/ExtensionProvider/AbstractProvider.php";s:4:"54d7";s:54:"Classes/ExtensionProvider/ExtensionManagerProvider.php";s:4:"60d3";s:42:"Classes/ExtensionProvider/FileProvider.php";s:4:"078f";s:47:"Classes/ExtensionProvider/ProviderInterface.php";s:4:"8cab";s:45:"Classes/ExtensionProvider/ProviderManager.php";s:4:"287e";s:42:"Classes/ExtensionProvider/SoapProvider.php";s:4:"c6c7";s:32:"Classes/Object/ObjectBuilder.php";s:4:"d705";s:43:"Classes/Persistence/AbstractPersistence.php";s:4:"bb42";s:44:"Classes/Persistence/PersistenceInterface.php";s:4:"1f8d";s:32:"Classes/Persistence/Registry.php";s:4:"9d56";s:31:"Classes/Persistence/Session.php";s:4:"d1ac";s:26:"Classes/Service/Mirror.php";s:4:"6dfd";s:24:"Classes/Service/Soap.php";s:4:"9bb3";s:40:"Classes/Task/UpdateExtensionListTask.php";s:4:"4d7d";s:63:"Classes/Task/UpdateExtensionListTaskAdditionalFieldProvider.php";s:4:"8c3d";s:27:"Classes/Utility/Archive.php";s:4:"82b7";s:24:"Classes/Utility/File.php";s:4:"e85e";s:30:"Classes/Utility/TypoScript.php";s:4:"9ef3";s:41:"Configuration/FlexForms/flexform_list.xml";s:4:"c22b";s:28:"Configuration/TCA/Author.php";s:4:"811f";s:30:"Configuration/TCA/Category.php";s:4:"91f6";s:32:"Configuration/TCA/Experience.php";s:4:"1905";s:31:"Configuration/TCA/Extension.php";s:4:"06e5";s:27:"Configuration/TCA/Media.php";s:4:"34da";s:30:"Configuration/TCA/Relation.php";s:4:"700a";s:25:"Configuration/TCA/Tag.php";s:4:"b806";s:29:"Configuration/TCA/Version.php";s:4:"a467";s:46:"Configuration/TypoScript/Default/constants.txt";s:4:"0ad1";s:42:"Configuration/TypoScript/Default/setup.txt";s:4:"70a5";s:43:"Configuration/TypoScript/Json/constants.txt";s:4:"bbe2";s:39:"Configuration/TypoScript/Json/setup.txt";s:4:"abe9";s:42:"Configuration/TypoScript/Rss/constants.txt";s:4:"0b06";s:38:"Configuration/TypoScript/Rss/setup.txt";s:4:"dea8";s:40:"Resources/Private/Language/locallang.xml";s:4:"e12c";s:74:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_author.xml";s:4:"2d47";s:76:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_category.xml";s:4:"7391";s:78:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_experience.xml";s:4:"d541";s:77:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_extension.xml";s:4:"cf81";s:73:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_media.xml";s:4:"f19f";s:76:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_relation.xml";s:4:"4ddd";s:71:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_tag.xml";s:4:"725e";s:75:"Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_version.xml";s:4:"e748";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"ae23";s:38:"Resources/Private/Layouts/Default.html";s:4:"7325";s:37:"Resources/Private/Layouts/Default.rss";s:4:"163a";s:36:"Resources/Private/Layouts/Index.html";s:4:"c488";s:42:"Resources/Private/Partials/AuthorList.html";s:4:"d677";s:42:"Resources/Private/Partials/AuthorMenu.html";s:4:"da7e";s:44:"Resources/Private/Partials/CategoryList.html";s:4:"4b46";s:44:"Resources/Private/Partials/CategoryMenu.html";s:4:"634c";s:45:"Resources/Private/Partials/ExtensionList.html";s:4:"9802";s:44:"Resources/Private/Partials/ExtensionList.rss";s:4:"5e8b";s:42:"Resources/Private/Partials/FormErrors.html";s:4:"f5bc";s:41:"Resources/Private/Partials/QuickMenu.html";s:4:"a816";s:40:"Resources/Private/Partials/TagCloud.html";s:4:"bb80";s:39:"Resources/Private/Partials/TagList.html";s:4:"3dfa";s:44:"Resources/Private/Templates/Author/Edit.html";s:4:"b9ee";s:45:"Resources/Private/Templates/Author/Index.html";s:4:"da1e";s:43:"Resources/Private/Templates/Author/New.html";s:4:"87a8";s:44:"Resources/Private/Templates/Author/Show.html";s:4:"2f54";s:46:"Resources/Private/Templates/Category/Edit.html";s:4:"956a";s:47:"Resources/Private/Templates/Category/Index.html";s:4:"da35";s:45:"Resources/Private/Templates/Category/New.html";s:4:"b90e";s:47:"Resources/Private/Templates/Extension/Edit.html";s:4:"29f7";s:48:"Resources/Private/Templates/Extension/Index.html";s:4:"efb8";s:47:"Resources/Private/Templates/Extension/List.html";s:4:"f2d2";s:46:"Resources/Private/Templates/Extension/List.rss";s:4:"712d";s:57:"Resources/Private/Templates/Extension/ListByCategory.html";s:4:"35ec";s:52:"Resources/Private/Templates/Extension/ListByTag.html";s:4:"ecff";s:53:"Resources/Private/Templates/Extension/ListLatest.html";s:4:"8001";s:52:"Resources/Private/Templates/Extension/ListLatest.rss";s:4:"712d";s:46:"Resources/Private/Templates/Extension/New.html";s:4:"b981";s:47:"Resources/Private/Templates/Extension/Show.html";s:4:"7121";s:41:"Resources/Private/Templates/Tag/Edit.html";s:4:"51aa";s:43:"Resources/Private/Templates/Tag/Filter.html";s:4:"13c1";s:42:"Resources/Private/Templates/Tag/Index.html";s:4:"0f2e";s:40:"Resources/Private/Templates/Tag/New.html";s:4:"cc40";s:42:"Resources/Public/CSS/tx_terfe2_default.css";s:4:"8deb";s:56:"Resources/Public/Icons/tx_terfe2_domain_model_author.gif";s:4:"1103";s:58:"Resources/Public/Icons/tx_terfe2_domain_model_category.gif";s:4:"4e5b";s:60:"Resources/Public/Icons/tx_terfe2_domain_model_experience.gif";s:4:"4e5b";s:59:"Resources/Public/Icons/tx_terfe2_domain_model_extension.gif";s:4:"905a";s:55:"Resources/Public/Icons/tx_terfe2_domain_model_media.gif";s:4:"1103";s:58:"Resources/Public/Icons/tx_terfe2_domain_model_relation.gif";s:4:"4e5b";s:53:"Resources/Public/Icons/tx_terfe2_domain_model_tag.gif";s:4:"4e5b";s:57:"Resources/Public/Icons/tx_terfe2_domain_model_version.gif";s:4:"1103";s:29:"_old/before_rewrite/ChangeLog";s:4:"01b0";s:36:"_old/before_rewrite/ext_autoload.php";s:4:"7f57";s:34:"_old/before_rewrite/ext_emconf.php";s:4:"9cf9";s:32:"_old/before_rewrite/ext_icon.gif";s:4:"37b8";s:37:"_old/before_rewrite/ext_localconf.php";s:4:"86ed";s:34:"_old/before_rewrite/ext_tables.php";s:4:"46e3";s:34:"_old/before_rewrite/ext_tables.sql";s:4:"332f";s:61:"_old/before_rewrite/Classes/Controller/AbstractController.php";s:4:"fcf2";s:59:"_old/before_rewrite/Classes/Controller/AuthorController.php";s:4:"9b1d";s:61:"_old/before_rewrite/Classes/Controller/CategoryController.php";s:4:"e1dd";s:62:"_old/before_rewrite/Classes/Controller/ExtensionController.php";s:4:"5482";s:56:"_old/before_rewrite/Classes/Controller/TagController.php";s:4:"115c";s:51:"_old/before_rewrite/Classes/Domain/Model/Author.php";s:4:"1b50";s:53:"_old/before_rewrite/Classes/Domain/Model/Category.php";s:4:"8f31";s:55:"_old/before_rewrite/Classes/Domain/Model/Experience.php";s:4:"c10b";s:54:"_old/before_rewrite/Classes/Domain/Model/Extension.php";s:4:"cf56";s:71:"_old/before_rewrite/Classes/Domain/Model/ExtensionManagerCacheEntry.php";s:4:"fc2d";s:50:"_old/before_rewrite/Classes/Domain/Model/Media.php";s:4:"ce48";s:53:"_old/before_rewrite/Classes/Domain/Model/Relation.php";s:4:"b2b0";s:48:"_old/before_rewrite/Classes/Domain/Model/Tag.php";s:4:"bafd";s:52:"_old/before_rewrite/Classes/Domain/Model/Version.php";s:4:"cd39";s:68:"_old/before_rewrite/Classes/Domain/Repository/AbstractRepository.php";s:4:"c769";s:66:"_old/before_rewrite/Classes/Domain/Repository/AuthorRepository.php";s:4:"8260";s:68:"_old/before_rewrite/Classes/Domain/Repository/CategoryRepository.php";s:4:"70b7";s:69:"_old/before_rewrite/Classes/Domain/Repository/ExtensionRepository.php";s:4:"8650";s:63:"_old/before_rewrite/Classes/Domain/Repository/TagRepository.php";s:4:"8925";s:75:"_old/before_rewrite/Classes/ExtensionProvider/AbstractExtensionProvider.php";s:4:"b2a6";s:74:"_old/before_rewrite/Classes/ExtensionProvider/ExtensionManagerProvider.php";s:4:"4df5";s:67:"_old/before_rewrite/Classes/ExtensionProvider/ExtensionProvider.php";s:4:"cc9c";s:76:"_old/before_rewrite/Classes/ExtensionProvider/ExtensionProviderInterface.php";s:4:"54ac";s:62:"_old/before_rewrite/Classes/ExtensionProvider/FileProvider.php";s:4:"c83b";s:65:"_old/before_rewrite/Classes/ExtensionProvider/ProviderManager.php";s:4:"34a6";s:62:"_old/before_rewrite/Classes/ExtensionProvider/SoapProvider.php";s:4:"3c6b";s:60:"_old/before_rewrite/Classes/Task/UpdateExtensionListTask.php";s:4:"a9be";s:49:"_old/before_rewrite/Classes/Utility/Extension.php";s:4:"5923";s:45:"_old/before_rewrite/Classes/Utility/Files.php";s:4:"a7c2";s:47:"_old/before_rewrite/Classes/Utility/Session.php";s:4:"8bc4";s:44:"_old/before_rewrite/Classes/Utility/Soap.php";s:4:"3d41";s:50:"_old/before_rewrite/Classes/Utility/TypoScript.php";s:4:"584a";s:43:"_old/before_rewrite/Classes/Utility/Zip.php";s:4:"0840";s:55:"_old/before_rewrite/Classes/View/Extension/ListJson.php";s:4:"80e9";s:61:"_old/before_rewrite/Classes/View/Extension/ListLatestJson.php";s:4:"6cd2";s:59:"_old/before_rewrite/Classes/ViewHelpers/CdataViewHelper.php";s:4:"4979";s:62:"_old/before_rewrite/Classes/ViewHelpers/DateTimeViewHelper.php";s:4:"cd76";s:67:"_old/before_rewrite/Classes/ViewHelpers/ExtensionIconViewHelper.php";s:4:"68db";s:57:"_old/before_rewrite/Classes/ViewHelpers/UrlViewHelper.php";s:4:"d5e6";s:61:"_old/before_rewrite/Configuration/FlexForms/flexform_list.xml";s:4:"c22b";s:48:"_old/before_rewrite/Configuration/TCA/Author.php";s:4:"6e79";s:50:"_old/before_rewrite/Configuration/TCA/Category.php";s:4:"91f6";s:52:"_old/before_rewrite/Configuration/TCA/Experience.php";s:4:"1905";s:51:"_old/before_rewrite/Configuration/TCA/Extension.php";s:4:"06e5";s:47:"_old/before_rewrite/Configuration/TCA/Media.php";s:4:"34da";s:50:"_old/before_rewrite/Configuration/TCA/Relation.php";s:4:"700a";s:45:"_old/before_rewrite/Configuration/TCA/Tag.php";s:4:"b806";s:49:"_old/before_rewrite/Configuration/TCA/Version.php";s:4:"cd87";s:66:"_old/before_rewrite/Configuration/TypoScript/Default/constants.txt";s:4:"0ad1";s:62:"_old/before_rewrite/Configuration/TypoScript/Default/setup.txt";s:4:"c6dc";s:63:"_old/before_rewrite/Configuration/TypoScript/Json/constants.txt";s:4:"bbe2";s:59:"_old/before_rewrite/Configuration/TypoScript/Json/setup.txt";s:4:"abe9";s:62:"_old/before_rewrite/Configuration/TypoScript/Rss/constants.txt";s:4:"0b06";s:58:"_old/before_rewrite/Configuration/TypoScript/Rss/setup.txt";s:4:"dea8";s:60:"_old/before_rewrite/Resources/Private/Language/locallang.xml";s:4:"4db0";s:94:"_old/before_rewrite/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_author.xml";s:4:"2b6d";s:96:"_old/before_rewrite/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_category.xml";s:4:"7391";s:98:"_old/before_rewrite/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_experience.xml";s:4:"d541";s:97:"_old/before_rewrite/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_extension.xml";s:4:"cf81";s:93:"_old/before_rewrite/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_media.xml";s:4:"f19f";s:96:"_old/before_rewrite/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_relation.xml";s:4:"4ddd";s:91:"_old/before_rewrite/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_tag.xml";s:4:"725e";s:95:"_old/before_rewrite/Resources/Private/Language/locallang_csh_tx_terfe2_domain_model_version.xml";s:4:"a826";s:63:"_old/before_rewrite/Resources/Private/Language/locallang_db.xml";s:4:"61b6";s:58:"_old/before_rewrite/Resources/Private/Layouts/Default.html";s:4:"7325";s:57:"_old/before_rewrite/Resources/Private/Layouts/Default.rss";s:4:"163a";s:56:"_old/before_rewrite/Resources/Private/Layouts/Index.html";s:4:"c488";s:62:"_old/before_rewrite/Resources/Private/Partials/AuthorList.html";s:4:"d677";s:62:"_old/before_rewrite/Resources/Private/Partials/AuthorMenu.html";s:4:"da7e";s:64:"_old/before_rewrite/Resources/Private/Partials/CategoryList.html";s:4:"4b46";s:64:"_old/before_rewrite/Resources/Private/Partials/CategoryMenu.html";s:4:"634c";s:65:"_old/before_rewrite/Resources/Private/Partials/ExtensionList.html";s:4:"9802";s:64:"_old/before_rewrite/Resources/Private/Partials/ExtensionList.rss";s:4:"5e8b";s:62:"_old/before_rewrite/Resources/Private/Partials/FormErrors.html";s:4:"f5bc";s:61:"_old/before_rewrite/Resources/Private/Partials/QuickMenu.html";s:4:"a816";s:60:"_old/before_rewrite/Resources/Private/Partials/TagCloud.html";s:4:"bb80";s:59:"_old/before_rewrite/Resources/Private/Partials/TagList.html";s:4:"3dfa";s:64:"_old/before_rewrite/Resources/Private/Templates/Author/Edit.html";s:4:"b9ee";s:65:"_old/before_rewrite/Resources/Private/Templates/Author/Index.html";s:4:"da1e";s:63:"_old/before_rewrite/Resources/Private/Templates/Author/New.html";s:4:"87a8";s:64:"_old/before_rewrite/Resources/Private/Templates/Author/Show.html";s:4:"2f54";s:66:"_old/before_rewrite/Resources/Private/Templates/Category/Edit.html";s:4:"956a";s:67:"_old/before_rewrite/Resources/Private/Templates/Category/Index.html";s:4:"da35";s:65:"_old/before_rewrite/Resources/Private/Templates/Category/New.html";s:4:"b90e";s:67:"_old/before_rewrite/Resources/Private/Templates/Extension/Edit.html";s:4:"29f7";s:68:"_old/before_rewrite/Resources/Private/Templates/Extension/Index.html";s:4:"efb8";s:67:"_old/before_rewrite/Resources/Private/Templates/Extension/List.html";s:4:"f2d2";s:66:"_old/before_rewrite/Resources/Private/Templates/Extension/List.rss";s:4:"712d";s:77:"_old/before_rewrite/Resources/Private/Templates/Extension/ListByCategory.html";s:4:"35ec";s:72:"_old/before_rewrite/Resources/Private/Templates/Extension/ListByTag.html";s:4:"ecff";s:73:"_old/before_rewrite/Resources/Private/Templates/Extension/ListLatest.html";s:4:"8001";s:72:"_old/before_rewrite/Resources/Private/Templates/Extension/ListLatest.rss";s:4:"712d";s:66:"_old/before_rewrite/Resources/Private/Templates/Extension/New.html";s:4:"b981";s:67:"_old/before_rewrite/Resources/Private/Templates/Extension/Show.html";s:4:"7121";s:61:"_old/before_rewrite/Resources/Private/Templates/Tag/Edit.html";s:4:"51aa";s:63:"_old/before_rewrite/Resources/Private/Templates/Tag/Filter.html";s:4:"13c1";s:62:"_old/before_rewrite/Resources/Private/Templates/Tag/Index.html";s:4:"0f2e";s:60:"_old/before_rewrite/Resources/Private/Templates/Tag/New.html";s:4:"cc40";s:62:"_old/before_rewrite/Resources/Public/CSS/tx_terfe2_default.css";s:4:"8deb";s:76:"_old/before_rewrite/Resources/Public/Icons/tx_terfe2_domain_model_author.gif";s:4:"1103";s:78:"_old/before_rewrite/Resources/Public/Icons/tx_terfe2_domain_model_category.gif";s:4:"4e5b";s:80:"_old/before_rewrite/Resources/Public/Icons/tx_terfe2_domain_model_experience.gif";s:4:"4e5b";s:79:"_old/before_rewrite/Resources/Public/Icons/tx_terfe2_domain_model_extension.gif";s:4:"905a";s:75:"_old/before_rewrite/Resources/Public/Icons/tx_terfe2_domain_model_media.gif";s:4:"1103";s:78:"_old/before_rewrite/Resources/Public/Icons/tx_terfe2_domain_model_relation.gif";s:4:"4e5b";s:73:"_old/before_rewrite/Resources/Public/Icons/tx_terfe2_domain_model_tag.gif";s:4:"4e5b";s:77:"_old/before_rewrite/Resources/Public/Icons/tx_terfe2_domain_model_version.gif";s:4:"1103";s:34:"_old/before_rewrite/doc/manual.sxw";s:4:"fe2d";s:14:"doc/manual.sxw";s:4:"fe2d";}',
);

?>