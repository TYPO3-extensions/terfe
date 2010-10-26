<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Susanne Moog <typo3@susannemoog.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
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
 ***************************************************************/
/**
 * Plugin 'TER Documentation' for the 'docondemand' extension.
 *
 * $Id: $
 *
 * @author	Susanne Moog <typo3@susannemoog.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_docondemand_upload extends tslib_pibase {
	var $prefixId = "tx_docondemand_upload";        // Same as class name
	var $scriptRelPath = "upload/class.tx_docondemand_upload.php";    // Path to this script relative to the extension dir.
	var $extKey = "docondemand";    // The extension key.
	var $status = array();



	function main($content,$conf)    {
		$this->conf=$conf;
		$this->maxsize = $this->conf['maxsize'] ? $this->conf['maxsize'] : 100000;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		$staticConfArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['docondemand']);
		$this->path = $staticConfArr['repositoryDir'];
		if (substr($this->path, -1, 1) != '/') {
			$this->path .= '/';
		}
		require_once t3lib_extMgm::extPath('docondemand').'class.tx_docondemand_renderdocuments.php';
		require_once (t3lib_extMgm::extPath ('docondemand').'class.tx_docondemand_readonline.php');
		$renderDocsObj = tx_docondemand_renderdocuments::getInstance();
		$renderDocsObj->registerOutputFormat('docondemand_onlinehtml', 'HTMLDISPLAY', 'display', new tx_docondemand_readonline);
		$htmlOutputObjFormat = $renderDocsObj->getOutputFormats();	
		
		if($this->piVars['do_upload'] && !$this->piVars['document']){
			$this->handleUpload();
			if($this->fileName) {
				$renderDocsObj->render($this->fileName);
				$content = $htmlOutputObjFormat['docondemand_onlinehtml']['object']->renderDisplay($this->path . str_replace('.sxw', '', $this->fileName) . '/', $this);
			}
		} else if($this->piVars['document']) {
			$content = $htmlOutputObjFormat['docondemand_onlinehtml']['object']->renderDisplay($this->path . $this->piVars['document'], $this);
			$content = str_replace('{TX_docondemand_PICTURESDIR}', str_replace(t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT'), '', $this->path)  . $this->piVars['document'] . 'docbook/pictures/', $content);
		} else {
			$content .= $this->displayUploadForm();
		}
		
		return $this->pi_wrapInBaseClass($content);
	}

	function displayUploadForm(){
		$content = $this->cObj->cObjGetSingle($this->conf['form'], $this->conf['form.']);
		$content = str_replace("###STATUS###", implode('', $this->status), $content);
		return $content;
	}

	function handleUpload(){
		$content='';
		$path = $this->path;
		if(!$this->path){
			echo 'path not set';
		}
		$uploaddir = is_dir($path) ? $path : $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'];
		$this->fileName = time() . '-' . $_FILES[$this->prefixId]['name'];
		$uploadfile = $uploaddir . $this->fileName;

		if(is_file($uploadfile) && $this->conf['noOverwrite']){ //file already exists?
			$this->setStatus(5);
		}

		if($this->file_too_big($_FILES[$this->prefixId]['size'])){
			$this->setStatus(2);
		}

		if(!$this->mime_allowed($_FILES[$this->prefixId]['type'])){ //mimetype allowed?
			$this->setStatus(6);
		}

		if(!$this->ext_allowed($_FILES[$this->prefixId]['name'])){ //extension allowed?
			$this->setStatus(7);
		}

		if(empty($this->status)){ //no errors so far
			if(move_uploaded_file($_FILES[$this->prefixId]['tmp_name'], $uploadfile)) {//success!
				$filemode = octdec($this->conf['fileMode']);
				@chmod($uploadfile, $filemode);
				$this->setStatus(8);
			} else {
				$this->setStatus($_FILES[$this->prefixId]['error']);
			}
		}
	}



	function setStatus($statusCode){

		switch($statusCode){
			case 0:
				break;
			case 1:
			case 2:
				$status = 'toobig';
				break;
			case 3:
				$status = 'partial';
				break;
			case 4:
				$status = 'nofile';
				break;
			case 5:
				$status = 'exist';
				break;
			case 6:
				$status = 'mimenotallowed';
				break;
			case 7:
				$status = 'extensionnotallowed';
				break;
			case 8:
				$status = 'uploadsuccessfull';
				break;
			default:
				$status = 'unknown';
				break;
		}		 
		$this->status[] = $this->cObj->cObjGetSingle($this->conf['message.'][$status], $this->conf['message.'][$status . '.']);
	}

	function mime_allowed($mime){
		if(!($this->conf['checkMime'])) return TRUE;         //all mimetypes allowed
		$includelist = explode(",", $this->conf['mimeInclude']);
		$excludelist = explode(",", $this->conf['mimeExclude']);        //overrides includelist
		return (   (in_array($mime,$includelist) || in_array('*',$includelist))   &&   (!in_array($mime,$excludelist))  );
	}

	function ext_allowed($filename){
		$extension = '';
		if($extension = strstr($filename, '.')) {
			$extension = substr($extension, 1);
			return (bool)($extension == 'sxw');
		} else {
			return FALSE;
		}
	}

	function file_too_big($filesize){
		return $filesize > $this->maxsize;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/docondemand/pi1/class.tx_docondemand_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/docondemand/pi1/class.tx_docondemand_pi1.php']);
}

?>