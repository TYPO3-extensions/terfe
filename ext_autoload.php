<?php
	$extensionClassesPath = t3lib_extMgm::extPath('ter_fe2', 'Classes/');

	return array(
		'tx_terfe2_controller_abstractcontroller'                          => $extensionClassesPath . 'Controller/AbstractController.php',
		'tx_terfe2_controller_authorcontroller'                            => $extensionClassesPath . 'Controller/AuthorController.php',
		'tx_terfe2_controller_categorycontroller'                          => $extensionClassesPath . 'Controller/CategoryController.php',
		'tx_terfe2_controller_extensioncontroller'                         => $extensionClassesPath . 'Controller/ExtensionController.php',
		'tx_terfe2_controller_tagcontroller'                               => $extensionClassesPath . 'Controller/TagController.php',
		'tx_terfe2_domain_model_abstractentity'                            => $extensionClassesPath . 'Domain/Model/AbstractEntity.php',
		'tx_terfe2_domain_model_abstractvalueobject'                       => $extensionClassesPath . 'Domain/Model/AbstractValueObject.php',
		'tx_terfe2_domain_model_author'                                    => $extensionClassesPath . 'Domain/Model/Author.php',
		'tx_terfe2_domain_model_category'                                  => $extensionClassesPath . 'Domain/Model/Category.php',
		'tx_terfe2_domain_model_experience'                                => $extensionClassesPath . 'Domain/Model/Experience.php',
		'tx_terfe2_domain_model_extension'                                 => $extensionClassesPath . 'Domain/Model/Extension.php',
		'tx_terfe2_domain_model_extensionmanagercacheentry'                => $extensionClassesPath . 'Domain/Model/ExtensionManagerCacheEntry.php',
		'tx_terfe2_domain_model_media'                                     => $extensionClassesPath . 'Domain/Model/Media.php',
		'tx_terfe2_domain_model_relation'                                  => $extensionClassesPath . 'Domain/Model/Relation.php',
		'tx_terfe2_domain_model_tag'                                       => $extensionClassesPath . 'Domain/Model/Tag.php',
		'tx_terfe2_domain_model_version'                                   => $extensionClassesPath . 'Domain/Model/Version.php',
		'tx_terfe2_domain_repository_abstractrepository'                   => $extensionClassesPath . 'Domain/Repository/AbstractRepository.php',
		'tx_terfe2_domain_repository_authorrepository'                     => $extensionClassesPath . 'Domain/Repository/AuthorRepository.php',
		'tx_terfe2_domain_repository_categoryrepository'                   => $extensionClassesPath . 'Domain/Repository/CategoryRepository.php',
		'tx_terfe2_domain_repository_extensionmanagercacheentryrepository' => $extensionClassesPath . 'Domain/Repository/ExtensionManagerCacheEntryRepository.php',
		'tx_terfe2_domain_repository_extensionrepository'                  => $extensionClassesPath . 'Domain/Repository/ExtensionRepository.php',
		'tx_terfe2_domain_repository_tagrepository'                        => $extensionClassesPath . 'Domain/Repository/TagRepository.php',
		'tx_terfe2_domain_repository_versionrepository'                    => $extensionClassesPath . 'Domain/Repository/VersionRepository.php',
		'tx_terfe2_provider_abstractprovider'                              => $extensionClassesPath . 'Provider/AbstractProvider.php',
		'tx_terfe2_provider_extensionmanagerprovider'                      => $extensionClassesPath . 'Provider/ExtensionManagerProvider.php',
		'tx_terfe2_provider_fileprovider'                                  => $extensionClassesPath . 'Provider/FileProvider.php',
		'tx_terfe2_provider_providerinterface'                             => $extensionClassesPath . 'Provider/ProviderInterface.php',
		'tx_terfe2_provider_providermanager'                               => $extensionClassesPath . 'Provider/ProviderManager.php',
		'tx_terfe2_provider_soapprovider'                                  => $extensionClassesPath . 'Provider/SoapProvider.php',
		'tx_terfe2_object_objectbuilder'                                   => $extensionClassesPath . 'Object/ObjectBuilder.php',
		'tx_terfe2_persistence_abstractpersistence'                        => $extensionClassesPath . 'Persistence/AbstractPersistence.php',
		'tx_terfe2_persistence_persistenceinterface'                       => $extensionClassesPath . 'Persistence/PersistenceInterface.php',
		'tx_terfe2_persistence_registry'                                   => $extensionClassesPath . 'Persistence/Registry.php',
		'tx_terfe2_persistence_session'                                    => $extensionClassesPath . 'Persistence/Session.php',
		'tx_terfe2_service_documentation'                                  => $extensionClassesPath . 'Service/Documentation.php',
		'tx_terfe2_service_mirror'                                         => $extensionClassesPath . 'Service/Mirror.php',
		'tx_terfe2_service_soap'                                           => $extensionClassesPath . 'Service/Soap.php',
		'tx_terfe2_task_abstracttask'                                      => $extensionClassesPath . 'Task/AbstractTask.php',
		'tx_terfe2_task_abstractadditionalfieldprovider'                   => $extensionClassesPath . 'Task/AbstractAdditionalFieldProvider.php',
		'tx_terfe2_task_createziparchivestask'                             => $extensionClassesPath . 'Task/CreateZipArchivesTask.php',
		'tx_terfe2_task_createziparchivestaskadditionalfieldprovider'      => $extensionClassesPath . 'Task/CreateZipArchivesTaskAdditionalFieldProvider.php',
		'tx_terfe2_task_updateextensionlisttask'                           => $extensionClassesPath . 'Task/UpdateExtensionListTask.php',
		'tx_terfe2_task_updateextensionlisttaskadditionalfieldprovider'    => $extensionClassesPath . 'Task/UpdateExtensionListTaskAdditionalFieldProvider.php',
		'tx_terfe2_utility_archive'                                        => $extensionClassesPath . 'Utility/Archive.php',
		'tx_terfe2_utility_array'                                          => $extensionClassesPath . 'Utility/Array.php',
		'tx_terfe2_utility_datetime'                                       => $extensionClassesPath . 'Utility/Datetime.php',
		'tx_terfe2_utility_file'                                           => $extensionClassesPath . 'Utility/File.php',
		'tx_terfe2_utility_typoscript'                                     => $extensionClassesPath . 'Utility/TypoScript.php',
		'Tx_TerFe2_view_extension_listjson'                                => $extensionClassesPath . 'View/Extension/ListJson.php',
		'Tx_TerFe2_view_extension_listlatestjson'                          => $extensionClassesPath . 'View/Extension/ListLatestJson.php',
		'tx_terfe2_viewhelpers_cdataviewhelper'                            => $extensionClassesPath . 'ViewHelpers/CdataViewHelper.php',
		'tx_terfe2_viewhelpers_chartviewhelper'                            => $extensionClassesPath . 'ViewHelpers/ChartViewHelper.php',
		'tx_terfe2_viewhelpers_cropviewhelper'                             => $extensionClassesPath . 'ViewHelpers/CropViewHelper.php',
		'tx_terfe2_viewhelpers_datetimeviewhelper'                         => $extensionClassesPath . 'ViewHelpers/DateTimeViewHelper.php',
		'tx_terfe2_viewhelpers_documentationlinkviewhelper'                => $extensionClassesPath . 'ViewHelpers/DocumentationLinkViewHelper.php',
		'tx_terfe2_viewhelpers_extensioniconviewhelper'                    => $extensionClassesPath . 'ViewHelpers/ExtensionIconViewHelper.php',
		'tx_terfe2_viewhelpers_formatviewhelper'                           => $extensionClassesPath . 'ViewHelpers/FormatViewHelper.php',
		'tx_terfe2_viewhelpers_rawviewhelper'                              => $extensionClassesPath . 'ViewHelpers/RawViewHelper.php',
	);
?>