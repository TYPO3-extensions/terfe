<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "realurl".
 *
 * Auto generated 26-07-2014 14:37
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'RealURL: speaking paths for TYPO3',
    'description' => 'Creates nice looking URLs for TYPO3 pages: converts http://example.com/index.phpid=12345&L=2 to http://example.com/path/to/your/page/. Please, ask for free support in TYPO3 mailing lists or contact the maintainer for paid support.',
    'category' => 'fe',
    'shy' => 0,
    'version' => '1.12.8',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'TYPO3_version' => '4.5.0-6.2.999',
    'PHP_version' => '5.3.2-5.999.999',
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => 'pages,sys_domain,pages_language_overlay,sys_template',
    'clearcacheonload' => 1,
    'lockType' => '',
    'author' => 'Dmitry Dulepov',
    'author_email' => 'dmitry.dulepov@gmail.com',
    'author_company' => '',
    'CGLcompliance' => NULL,
    'CGLcompliance_note' => NULL,
);

?>