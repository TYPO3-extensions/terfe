<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class Tx_TerFe2_Controller_Eid_ExtensionController
 */
class Tx_TerFe2_Controller_Eid_ExtensionController
{

    /**
     * @var t3lib_DB
     */
    protected $databaseConnection;

    /**
     * @var array
     */
    protected $jsonArray;

    public function __construct()
    {
        tslib_eidtools::connectDB();
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];

        $this->jsonArray = array(
            'meta' => NULL,
            'data' => NULL
        );
    }

    /**
     * @param $action
     */
    public function dispatch($action)
    {
        if (NULL === $action) {
            return;
        }

        if (is_callable(array($this, $action))) {
            $this->{$action}();
        }
    }

    /**
     * @return void
     */
    protected function findAllWithRepositoryUrlAsPackageSource()
    {
        $extensions = $this->databaseConnection->exec_SELECTgetRows(
            '*',
            'tx_terfe2_domain_model_extension',
            'hidden = 0 and deleted = 0 and repository_clone_url <> ""'
        );

        foreach ($extensions as $extension) {
            $this->jsonArray['data'][$extension['ext_key']] = array(
                'repository_clone_url' => $extension['repository_clone_url'],
            );
        }

        $json = json_encode($this->jsonArray, TRUE);

        if (JSON_ERROR_NONE !== ($jsonErrorCode = json_last_error())) {
            $this->jsonArray['meta'] = array(
                'error' => array(
                    'type' => 'json encoding error',
                    'code' => $jsonErrorCode
                )
            );
            $this->jsonArray['data'] = NULL;

            $json = json_encode($this->jsonArray);
        }

        header('Content-Type: application/json');
        echo $json;
    }
}

$controller = new Tx_TerFe2_Controller_Eid_ExtensionController();
$controller->dispatch(t3lib_div::_GET('action'));
