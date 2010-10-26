<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Robert Lemke (robert@typo3.org)
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
 * Documentation renderer for the docondemand extension. Called from the CLI
 * script as well as from the docondemand_* extensions for registering
 * output formats. 
 *
 * $Id: class.tx_docondemand_renderdocuments.php,v 1.3 2006/03/06 00:15:20 robert_typo3 Exp $
 *
 * @author	Robert Lemke <robert@typo3.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */

class tx_docondemand_renderdocuments {

	protected $repositoryDir = '';									// Full path to the local extension repository. Configured in the Extension Manager
	protected $verbose = TRUE;										// If TRUE, some debugging output will be sent to STDOUT. Configured in the Extension Manager
	protected $fullPath = FALSE;									// If set to a path and file name, logging will be redirected to that file
	protected $unzipCommand = '';									// Commandline for unzipping files
	protected $debug = FALSE;										// Makes it easer to debug this class. Set to FALSE in productional use!
	
	protected $outputFormats = array();								// Objects and method names of the render classes. Add new class by calling registerRenderClass()
	protected $languageGuesserServiceObj = array();					// Holds an instance of the service "textLang" 

	private static $instance = FALSE;								// Holds an instance of this class

	/**
	 * This constructor is private because you may only instantiate this class by calling
	 * the function getInstance() which returns a unique instance of this class (Singleton).
	 * 
	 * @return		void
	 * @access		private
	 */
	private function __construct() {
	}

	/**
	 * Returns a unique instance of this class. Call this function instead of creating a new
	 * instance manually!
	 * 
	 * @return		object		Unique instance of tx_docondemand_renderdocuments
	 * @access		public
	 */
	public function getInstance() {
		if (self::$instance === FALSE) {
			self::$instance = new tx_docondemand_renderdocuments;	
		}
		return self::$instance;	
	} 

	/**
	 * Initializes this class and checks if another process is running already.
	 * 
	 * @return	void
	 * @access	protected
	 */
	protected function init() {

			// Fetch static configuration from Extension Manager:
		$staticConfArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['docondemand']);
		if (is_array ($staticConfArr)) {
			
			$this->repositoryDir = $staticConfArr['repositoryDir'];
			if (substr($this->repositoryDir, -1, 1) != '/'){
				$this->repositoryDir .= '/';
			}
			$this->unzipCommand = $staticConfArr['unzipCommand'];
			$this->logFullPath = strlen ($staticConfArr['logFullPath']) ? $staticConfArr['logFullPath'] : FALSE;
		}

			// Check if another process currently renders the documents:
		if (@file_exists (PATH_site.'typo3temp/tx_docondemand/tx_docondemand_render.lock')) {
			$this->log ('Found .lock file ...');			
				// If the lock is not older than X minutes, skip index creation:
			if (filemtime (PATH_site.'typo3temp/tx_docondemand/tx_docondemand_render.lock') > (time() - (10*60))) {
				$this->log('... aborting - another process seems to render documents right now!'.chr(10));
				if (!$this->debug) die();
			} else {
				$this->log('... lock file was older than 10 minutes, so start rendering anyway'.chr(10));
			}
		}		
		
			// Initialize language guessing service:
		$this->languageGuesserServiceObj = t3lib_div::makeInstanceService('textLang');
	}





	/******************************************************
	 *
	 * Main API functions (public)
	 *
	 ******************************************************/

	/**
	 * Main function - starts the document cache rendering process
	 * 
	 * @return	void
	 * @access	public
	 */
	public function render($file) {		
		$this->init();

		touch(PATH_site.'typo3temp/tx_docondemand/tx_docondemand_render.lock');

		$this->log(chr(10).strftime('%d.%m.%y %R').' docondemand renderer starting ...');
		
		$documentDir = $this->repositoryDir . $file . '/';
		
		if($this->transformManualToDocBook($file)) {
			foreach ($this->outputFormats as $label => $formatInfoArr) {
				$this->log ('   * Rendering '.$label);
				$formatInfoArr['object']->render($this->repositoryDir . str_replace('.sxw', '', $file) . '/');
				@unlink(PATH_site.'typo3temp/tx_docondemand/tx_docondemand_render.lock');
			}
		} else {
								
			$this->log ('	* No manual found or problem while extracting manual');	
			@unlink(PATH_site.'typo3temp/tx_docondemand/tx_docondemand_render.lock');
		}			
		$this->log(chr(10).strftime('%d.%m.%y %R').' done.'.chr(10));	
	}

	/**
	 * Registers a new output format.
	 * 
	 * @param	string		$label: Unique name (label) of the output format. Can be a locallang string (eg. "LLL:EXT:docondemand_html/locallang.php:label")
	 * @param	string		$type: Possible values: "download" and "readonline" 
	 * @param	object		$objectReference: Instance of the render class. If $type is "readonline", a method "renderOnline" must exist. A method "renderCache" is mandatory.
	 * @return	void
	 * @access	public
	 */
	public function registerOutputFormat($key, $label, $type, &$objectReference) {
		$this->outputFormats[$key] = array (
			'label' => $label,
			'type' => $type,
			'object' => $objectReference,
		);
	}
	
	/**
	 * Returns an array of output formats which were previously
	 * rendered with registerOutputFormat()
	 * 
	 * @return	array		Array of output formats and instantiated rendering objects
	 * @access	public
	 */
	public function getOutputFormats () {
		return $this->outputFormats;	
	}


	/**
	 * Transforms the manual of the specified extension version to docbook and saves
	 * the result in a file called "manual.xml" in the document cache directory of the
	 * extension.
	 * 
	 * @param	string		$extensionKey: The extension key
	 * @param	string		$version: The extension's version string
	 * @return	boolean		returns FALSE if operation was not successful, otherwise TRUE
	 * @access	protected
	 */
	protected function transformManualToDocBook($file) {
		$documentDir = $this->repositoryDir . str_replace('.sxw', '', $file) . '/';
			// Prepare output directory:
		if (@is_dir($documentDir)) $this->removeDirRecursively($documentDir);
		if (@is_dir($documentDir . 'sxw')) $this->removeDirRecursively($documentDir . 'sxw');
		if (@is_dir($documentDir . 'docbook')) $this->removeDirRecursively($documentDir . 'docbook');
		@mkdir($documentDir);
		@mkdir($documentDir . 'sxw');
		@mkdir($documentDir . 'docbook');

			// Unzip the Open Office Writer file:
		$unzipCommand = $this->unzipCommand;
		$unzipCommand = str_replace('###ARCHIVENAME###', $this->repositoryDir . $file, $unzipCommand);
		$unzipCommand = str_replace('###DIRECTORY###', $documentDir . 'sxw/', $unzipCommand);

		$unzipResultArr = array();
		exec($unzipCommand, $unzipResultArr);
				
		if (@is_dir($documentDir.'sxw/Pictures')) {
			rename($documentDir.'sxw/Pictures', $documentDir.'docbook/pictures');
		}  

			// Transform the manual's content.xml to DocBook:
		$this->log ('   * Rendering DocBook');
		$xsl = new DomDocument();
		$xsl->load(t3lib_extMgm::extPath('docondemand').'res/oomanual2docbook.xsl');

		if (!@file_exists($documentDir . 'sxw/content.xml')) {
			$this->log ('	* documentCache_transformManualToDocBook: ' . $documentDir . 'sxw/content.xml does not exist.');	
			return FALSE;
		}
		
		$manualDom = new DomDocument();
		$manualDom->load($documentDir . 'sxw/content.xml');
				
		$xsltProc = new XSLTProcessor();
		$xsl = $xsltProc->importStylesheet($xsl);
		
		$docBookDom = $xsltProc->transformToDoc($manualDom);
		$docBookDom->formatOutput = FALSE;
		$docBookDom->save($documentDir . 'docbook/manual.xml');

			// Create Table Of Content:
		$tocArr = array ();
		$chapterCount = 1;
		$sectionCount = 1;			
		$subSectionCount = 1;			
		$simpleDocBook = simplexml_import_dom($docBookDom);
		if ($simpleDocBook === FALSE) {
			$this->log ('	* documentCache_transformManualToDocBook: SimpleXML error while transforming XML to DocBook');	
			return FALSE;
		}
		
		$abstract = '';
		$textExcerpt = '';

		foreach ($simpleDocBook->chapter as $chapter) {
			$tocArr[$chapterCount]['title'] = (string)$chapter->title;
			foreach ($chapter->section as $section) {
				$tocArr[$chapterCount]['sections'][$sectionCount]['title'] = (string)$section->title;

						// Try to extract an abstract out of the first paragraph of a section usually called "What does it do?":
				if ($chapterCount <= 1) {
					foreach ($section->section as $subSection) {
						if (strlen($abstract) == 0) {
							$abstract = (string)$subSection->para;
						}
					}						
				}				
				if (strlen($textExcerpt) < 2000) {
					$textExcerpt .= (string)$section->para;
					foreach ($section->section as $subSection) {
						if (strlen($textExcerpt) < 2000) {
							$textExcerpt .= (string)$subSection->para;	
							$textExcerpt .= (string)$subSection->itemizedlist->listitem->para;	
						}
					}						
				}

				foreach ($section->section as $subSection) {
					$tocArr[$chapterCount]['sections'][$sectionCount]['subsections'][$subSectionCount]['title'] = (string)$subSection->title;
					$subSectionCount ++;
				}
				$sectionCount++;
				$subSectionCount = 1;
			}
			$chapterCount++;
			$sectionCount = 1;
		}
		t3lib_div::writeFile($documentDir.'toc.dat', serialize($tocArr));

			// Identify the language of the document:
		if (strlen ($textExcerpt)) {
			if (is_object($this->languageGuesserServiceObj)) {
				$this->languageGuesserServiceObj->process($textExcerpt, '', array('encoding' => 'utf-8'));
			    $documentLanguage = strtolower($this->languageGuesserServiceObj->getOutput());
			} else {
				$this->log ('   * Warning: Could not guess language because textLang service was not available');
				$metaXML = simplexml_load_file($documentDir.'sxw/meta.xml');
				$DCLanguageArr = $metaXML->xpath('//dc:language');
				$documentLanguage = is_array($DCLanguageArr) ? strtolower(substr ($DCLanguageArr[0], 0, 2)) : '';	
			}
		} else {
			$this->log('   * Warning: Could not guess language because the text excerpt was empty!');		
		}		

		t3lib_div::writeFile($documentDir.'abstract.txt', $abstract);
		t3lib_div::writeFile($documentDir.'text-excerpt.txt', $textExcerpt);
		t3lib_div::writeFile($documentDir.'language.txt', $documentLanguage);
		
		$this->removeDirRecursively ($documentDir . 'sxw');

		return TRUE;
	}


	/**
	 * Removes directory with all files from the given path recursively! 
	 * Path must somewhere below typo3temp/
	 * 
	 * @param	string		$removePath: Absolute path to directory to remove
	 * @return	void		
	 * @access	protected
	 */
	protected function removeDirRecursively($removePath)	{
			// Go through dirs:
		$dirs = t3lib_div::get_dirs($removePath);
		if (is_array($dirs))	{
			foreach($dirs as $subdirs)	{
				if ($subdirs)	{
					$this->removeDirRecursively($removePath.'/'.$subdirs.'/');
				}
			}
		}

			// Then files in this dir:
		$fileArr = t3lib_div::getFilesInDir($removePath,'',1);
		if (is_array($fileArr))	{
			foreach($fileArr as $file)	{
				unlink($file);
			}
		}
			// Remove this dir:
		rmdir($removePath);
	}





	/******************************************************
	 *
	 * Other helper functions (protected)
	 *
	 ******************************************************/

	/**
	 * Writes a message to STDOUT if in verbose mode
	 * 
	 * @param	string		$msg: The message to output
	 * @return	void
	 * @access	protected
	 */
	protected function log($msg) {
		if ($this->verbose) {
			if ($this->logFullPath) {
				$fh = fopen($this->logFullPath, 'a');
				if ($fh) {
					fwrite ($fh, $msg.chr(10));
					fclose ($fh);
				}
			} else {
				echo ($msg.chr(10));
			}
		}		
	}
	
}

?>