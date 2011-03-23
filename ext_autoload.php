<?php
	$extensionPath = t3lib_extMgm::extPath('ter_fe2');
	$extensionClassesPath = $extensionPath . 'Classes/';

	return array(
		'tx_terfe2_controller_categorycontroller' => $extensionClassesPath . 'Controller/CategoryController.php',
		'tx_terfe2_controller_extensioncontroller' => $extensionClassesPath . 'Controller/ExtensionController.php',
		'tx_terfe2_controller_tagcontroller' => $extensionClassesPath . 'Controller/TagController.php',
		'tx_terfe2_domain_model_author' => $extensionClassesPath . 'Domain/Model/Author.php',
		'tx_terfe2_domain_model_category' => $extensionClassesPath . 'Domain/Model/Category.php',
		'tx_terfe2_domain_model_experience' => $extensionClassesPath . 'Domain/Model/Experience.php',
		'tx_terfe2_domain_model_extension' => $extensionClassesPath . 'Domain/Model/Extension.php',
		'tx_terfe2_domain_model_media' => $extensionClassesPath . 'Domain/Model/Media.php',
		'tx_terfe2_domain_model_relation' => $extensionClassesPath . 'Domain/Model/Relation.php',
		'tx_terfe2_domain_model_tag' => $extensionClassesPath . 'Domain/Model/Tag.php',
		'tx_terfe2_domain_model_version' => $extensionClassesPath . 'Domain/Model/Version.php',
		'tx_terfe2_domain_repository_categoryrepository' => $extensionClassesPath . 'Domain/Repository/CategoryRepository.php',
		'tx_terfe2_domain_repository_extensionrepository' => $extensionClassesPath . 'Domain/Repository/ExtensionRepository.php',
		'tx_terfe2_domain_repository_tagrepository' => $extensionClassesPath . 'Domain/Repository/TagRepository.php',
		'tx_terfe2_extensionprovider_abstractextensionprovider' => $extensionClassesPath . 'ExtensionProvider/AbstractExtensionProvider.php',
		'tx_terfe2_extensionprovider_extensionprovider' => $extensionClassesPath . 'ExtensionProvider/ExtensionProvider.php',
		'tx_terfe2_extensionprovider_extensionproviderinterface' => $extensionClassesPath . 'ExtensionProvider/ExtensionProviderInterface.php',
		'tx_terfe2_extensionprovider_fileprovider' => $extensionClassesPath . 'ExtensionProvider/FileProvider.php',
		'tx_terfe2_extensionprovider_soapprovider' => $extensionClassesPath . 'ExtensionProvider/SoapProvider.php',
		'tx_terfe2_task_updateextensionlisttask' => $extensionClassesPath . 'Task/UpdateExtensionListTask.php',
		'tx_terfe2_utility_files' => $extensionClassesPath . 'Utility/Files.php',
		'tx_terfe2_utility_session' => $extensionClassesPath . 'Utility/Session.php',
		'tx_terfe2_utility_soap' => $extensionClassesPath . 'Utility/Soap.php',
		'tx_terfe2_utility_typoscript' => $extensionClassesPath . 'Utility/TypoScript.php',
		'tx_terfe2_utility_zip' => $extensionClassesPath . 'Utility/Zip.php',
		'tx_terfe2_view_extension_listjson' => $extensionClassesPath . 'View/Extension/ListJson.php',
		'tx_terfe2_view_extension_listlatestjson' => $extensionClassesPath . 'View/Extension/ListLatestJson.php',
		'tx_terfe2_viewhelpers_datetimeviewhelper' => $extensionClassesPath . 'ViewHelpers/DateTimeViewHelper.php',
		'tx_terfe2_viewhelpers_exticonviewhelper' => $extensionClassesPath . 'ViewHelpers/ExtIconViewHelper.php',
		'tx_terfe2_viewhelpers_urlviewhelper' => $extensionClassesPath . 'ViewHelpers/UrlViewHelper.php',
	);
?>