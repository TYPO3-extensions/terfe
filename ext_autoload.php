<?php
	$extensionPath = t3lib_extMgm::extPath('ter_fe2');
	$extensionClassesPath = $extensionPath . 'Classes/';

	return array(
		'tx_terfe2_controller_categorycontroller' => $extensionClassesPath . 'Controller/CategoryController.php',
		'tx_terfe2_controller_extensioncontroller' => $extensionClassesPath . 'Controller/ExtensionController.php',
		'tx_terfe2_controller_tagcontroller' => $extensionClassesPath . 'Controller/TagController.php',
		'tx_terfe2_domain_model_category' => $extensionClassesPath . 'Domain/Model/Category.php',
		'tx_terfe2_domain_model_experience' => $extensionClassesPath . 'Domain/Model/Experience.php',
		'tx_terfe2_domain_model_extension' => $extensionClassesPath . 'Domain/Model/Extension.php',
		'tx_terfe2_domain_model_media' => $extensionClassesPath . 'Domain/Model/Media.php',
		'tx_terfe2_domain_model_relation' => $extensionClassesPath . 'Domain/Model/Relation.php',
		'tx_terfe2_domain_model_tag' => $extensionClassesPath . 'Domain/Model/Tag.php',
		'tx_terfe2_domain_model_version' => $extensionClassesPath . 'Domain/Model/Version.php',
		'tx_terfe2_domain_model_versionrange' => $extensionClassesPath . 'Domain/Model/VersionRange.php',
		'tx_terfe2_domain_repository_categoryrepository' => $extensionClassesPath . 'Domain/Repository/CategoryRepository.php',
		'tx_terfe2_domain_repository_extensionrepository' => $extensionClassesPath . 'Domain/Repository/ExtensionRepository.php',
		'tx_terfe2_domain_repository_tagrepository' => $extensionClassesPath . 'Domain/Repository/TagRepository.php',
		'tx_terfe2_service_filehandlerservice' => $extensionClassesPath . 'Service/FileHandlerService.php',
		'tx_terfe2_service_typoscriptparserservice' => $extensionClassesPath . 'Service/TypoScriptParserService.php',
		'tx_terfe2_task_updateextensionlisttask' => $extensionClassesPath . 'Task/UpdateExtensionListTask.php',
	);
?>