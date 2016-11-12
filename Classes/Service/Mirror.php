<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ******************************************************************/

/**
 * Service to handle mirrors
 */
class Tx_TerFe2_Service_Mirror implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var integer
     */
    protected $maxAttempts = 2;

    /**
     * @var string
     */
    protected $protocol = 'http';

    /**
     * @var integer
     */
    protected $repositoryId = 1;

    /**
     * @var string
     */
    protected $mirrorUrl;


    /**
     * Setter for maxAttempts
     *
     * @param integer $maxAttempts Maximal count of attemts to connect
     * @return void
     */
    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = (int)$maxAttempts;
    }


    /**
     * Getter for maxAttempts
     *
     * @return integer Maximal count of attemts to connect
     */
    public function getMaxAttempts()
    {
        return (int)$this->maxAttempts;
    }


    /**
     * Setter for protocol
     *
     * @param string $protocol Url protocol
     * @return void
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }


    /**
     * Getter for protocol
     *
     * @return string Url protocol
     */
    public function getProtocol()
    {
        return $this->protocol;
    }


    /**
     * Setter for repositoryId
     *
     * @param integer $repositoryId Repository id
     * @return void
     */
    public function setRepositoryId($repositoryId)
    {
        $this->repositoryId = (int)$repositoryId;
    }


    /**
     * Getter for repositoryId
     *
     * @return integer Repository id
     */
    public function getRepositoryId()
    {
        return (int)$this->repositoryId;
    }


    /**
     * Returns mirror url from local extension manager
     *
     * @param boolean $refresh Generate new mirror url
     * @return string Mirror url
     */
    public function getMirror($refresh = FALSE)
    {
        if (!$refresh && !empty($this->mirrorUrl)) {
            return $this->mirrorUrl;
        }

        // Get extension manager settings
        $emSettings = array(
            'rep_url' => '',
            'extMirrors' => '',
            'selectedRepository' => $this->getRepositoryId(),
            'selectedMirror' => 0,
        );
        if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['em'])) {
            $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['em']);
            $emSettings = array_merge($emSettings, $extConf);
        }

        if (!empty($emSettings['rep_url'])) {
            // Force manually added url
            $mirrorUrl = $emSettings['rep_url'];
        } else {
            // Set selected repository to "1" if no mirrors found
            $mirrors = unserialize($emSettings['extMirrors']);
            if (!is_array($mirrors)) {
                if ($emSettings['selectedRepository'] < 1) {
                    $emSettings['selectedRepository'] = 1;
                }
            }

            // Get mirrors from repository object
            $repository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_em_Repository', $emSettings['selectedRepository']);
            if ($repository->getMirrorListUrl()) {
                $repositoryUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_em_Repository_Utility', $repository);
                $mirrors = $repositoryUtility->getMirrors(TRUE)->getMirrors();
                unset($repositoryUtility);
                if (!is_array($mirrors)) {
                    throw new Exception('No mirrors found');
                }
            }

            // Build url
            $protocol = $this->getProtocol();
            $selectedMirror = (!empty($emSettings['selectedMirror']) ? $emSettings['selectedMirror'] : array_rand($mirrors));
            $mirrorUrl = rtrim($protocol, ':/') . '://' . $mirrors[$selectedMirror]['host'] . $mirrors[$selectedMirror]['path'];
        }

        return $this->mirrorUrl = rtrim($mirrorUrl, '/ ') . '/';
    }


    /**
     * Generate the url to a file on mirror server
     *
     * @param string $filename File name to check
     * @return string Url to file on mirror server
     */
    public function getUrlToFile($filename)
    {
        if (empty($filename)) {
            throw new Exception('No filename given to generate url');
        }

        // Get first mirror url
        $mirrorUrl = $this->getMirror();

        // Check mirrors if file exits
        $attempts = 1;
        $maxAttempts = $this->getMaxAttempts();
        while (!Tx_TerFe2_Utility_File::fileExists($mirrorUrl . $filename)) {
            $attempts++;
            if ($attempts > $maxAttempts) {
                // throw new Exception('File "' . $filename . '" could not be found on ' . $maxAttempts . ' mirrors, break');
                // break;
                return '';
            }
            $mirrorUrl = $this->getMirror(TRUE);
        }

        return $mirrorUrl . $filename;
    }


    /**
     * Fetch a file from mirror server
     *
     * @param string $filename File name to fetch
     * @return string File content
     */
    public function getFile($filename)
    {
        $url = $this->getUrlToFile($filename);

        if (Tx_TerFe2_Utility_File::isLocalUrl($url)) {
            $url = Tx_TerFe2_Utility_File::getAbsolutePathFromUrl($url);
            $content = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($url);
        } else {
            $content = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($url, 0, array(TYPO3_user_agent));
        }

        if (empty($content)) {
            throw new Exception('Could not fetch file "' . $filename . '" from mirror server');
        }

        return (string)$content;
    }

}

?>