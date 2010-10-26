<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2003 Kasper Skårhøj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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
 * Class for unzipping ZIP files and store them into database.
 *
 * @author	 Kasper Skårhøj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   57: class tx_libunzipped 
 *   91:     function init($file,$extIdStr='')	
 *  127:     function setExternalID($string)	
 *  136:     function clearCachedContent()	
 *  149:     function extractZippedDocumentsAndCacheIt()	
 *  179:     function storeFilesInDB($path)	
 *  222:     function DBcompileInsert($fields_values)	
 *  238:     function removeDir($tempDir)	
 *  273:     function getAllFilesAndFoldersInPath($fileArr,$extPath)	
 *  296:     function getFileFromXML($filepath)	
 *  310:     function getFileListFromDB()	
 *
 * TOTAL FUNCTIONS: 10
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */ 


/**
 * Class for unzipping ZIP files and store them into database.
 * 
 * @author	 Kasper Skårhøj <kasper@typo3.com>
 */
class tx_libunzipped {

		// EXTERNAL static values:	
		
		// unzipAppCmd contains the commandline for the unzipping tool. Will be overridden if a different commandline
		//	was provided when this extension was installed

	var $unzipAppCmd ='unzip -qq ###ARCHIVENAME### -d ###DIRECTORY###';	// Unzip Application (don't set to blank!) ** MODIFIED RL, 15.08.03

		// Example for WinRAR:
		//	var $unzipAppCmd ='c:\Programme\WinRAR\winrar.exe x -afzip -ibck -inul -o+ ###ARCHIVENAME### ###DIRECTORY###';
		
	var $compressedStorage=0;	// Boolean. If set, gzcompress will be used to compress the files before insertion in the database!
	
		// Internal, dynamic:
	var $file='';		// Reference to file, absolute path.
	var $fileHash='';	// Is set to a md5-hash string based on the filename + mtime.
	var $mtime=0;		// Is set to the mtime integer of the file.
	var $ext_ID='';		// Is set to an id string identifying this file storage. Default is to set it to an integer hash of the filename.
	





	/**
	 * Init object with abs path to ZIP file. 
	 * Will make sure that the ZIP file is read and stored in database (if that is not the case already)
	 * Returns '' on success, otherwise an error string.
	 * 
	 * @param	string		Absolute path to the file which is to be libunzipped/stored in DB (remember, no file inside can be larger than what can be stored in a BLOB)
	 * @param	string		ID string to use (alternative to the internally calculated)
	 * @return	array		Returns the filelist in ZIP file.
	 */
	function init($file,$extIdStr='')	{
		$staticConf = unserialize ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['libunzipped']);
		if ($staticConf['unzipAppCmd']) {
			$this->unzipAppCmd = $staticConf['unzipAppCmd'];
		}
		if (is_file($file))	{
			if (t3lib_div::isAbsPath($file))	{
				$this->file=$file;
				$this->setExternalID($extIdStr?$extIdStr:$this->file);	// Default value...
				
						// Make hash string:
				$this->mtime = filemtime($this->file);
				$this->fileHash = md5($this->file.'|'.$this->mtime);
				
					// Get file list from DB:
				if (!count($this->getFileListFromDB()))	{
					$this->clearCachedContent();
					$cc = $this->extractZippedDocumentsAndCacheIt();
					if (!$cc || !t3lib_div::testInt($cc))	{
						return 'No files found in ZIP file - or some other error: '.$cc;
					}
				}
				
				return $this->getFileListFromDB();
			} else return 'Not absolute file reference';
		} else return 'Not a file.';
	}
	
	
	/**
	 * Setting external id ($this->ext_ID)
	 * This is usefull if some plugin wants to identify a document not by its filename (which may have been changed) but by its relationship to the plugin.
	 * 
	 * @param	string		String to be hashed. Eg. filename
	 * @return	void		
	 */
	function setExternalID($string)	{
		$this->ext_ID = hexdec(substr(md5($string),0,7));	
	}
	
	/**
	 * Clearing cached content for $this->ext_ID
	 * 
	 * @return	void		
	 */
	function clearCachedContent()	{
		$query='DELETE FROM tx_libunzipped_filestorage WHERE rel_id='.intval($this->ext_ID);
		mysql(TYPO3_db,$query);
		echo mysql_error();
	}
	
	/**
	 * This takes the ZIP file, unzips it, reads all documents, store them in database for next retrieval.
	 * The file is libunzipped in PATH_site.'typo3temp/' + a randomly named folder.
	 * 
	 * @return	void		
	 * @access private
	 */
	function extractZippedDocumentsAndCacheIt()	{
		if (is_file($this->file))	{
			$tempDir = PATH_site.'typo3temp/'.md5(microtime()).'/';
			mkdir($tempDir, 0777);
			if (is_dir($tempDir))	{
					// This is if I want to check the content:
			#	$cmd = $this->unzipAppPath.' -t '.$this->file;
			#	exec($cmd,$dat);
			#	debug($dat);
			
					// Unzip the files inside: **MODIFIED RL, 15.08.03
				$cmd = $this->unzipAppCmd;
				$cmd = str_replace ('###ARCHIVENAME###', $this->file, $cmd);
				$cmd = str_replace ('###DIRECTORY###', $tempDir, $cmd);
				exec($cmd);

				$cc = $this->storeFilesInDB($tempDir);
				$this->removeDir($tempDir);
				return $cc;
			} else return 'No dir: '.$tempDir;
		} else return 'No file: '.$this->file;
	}

	/**
	 * Traverses the directory $path and stores all files in the database hash table (one file per record)
	 * 
	 * @param	string		The path to the temporary folder created in typo3temp/
	 * @return	void		
	 * @access private
	 */
	function storeFilesInDB($path)	{
		$allFiles=array();
		$cc=0;

		$fileArr = $this->getAllFilesAndFoldersInPath(array(),$path);
		reset($fileArr);
		while(list(,$filePath)=each($fileArr))	{
			if (is_file($filePath))	{
				$fI=pathinfo($filePath);
				$info = @getimagesize($filePath);
				$fArray=array(
					'filemtime'=>filemtime($filePath),
					'filesize'=>filesize($filePath),
					'filetype'=>strtolower($fI['extension']),
					'filename'=>$fI['basename'],
					'filepath'=>substr($filePath,strlen($path)),
					'info' => serialize($info),
					'compressed' => ($this->compressedStorage ? 1 : 0)
				);
				$allFiles[]=$fArray;
				
				$fArray['content'] = t3lib_div::getUrl($filePath);
				if ($this->compressedStorage)	$fArray['content']=gzcompress($fArray['content']);

				$fArray['rel_id'] = $this->ext_ID;
				$fArray['hash'] = $this->fileHash;
				
				$query = $this->DBcompileInsert($fArray);
				$res = mysql(TYPO3_db,$query);
				if (mysql_error())	debug(array(mysql_error(),$filePath));
				$cc++;
			}
		}
		return $cc;
	}

	/**
	 * Creates an INSERT SQL-statement for "tx_libunzipped_filestorage" from the array with field/value pairs $fields_values.
	 * 
	 * @param	array		Array with field=>value pairs to insert.
	 * @return	string		The query!
	 * @access private
	 */
	function DBcompileInsert($fields_values)	{
		if (is_array($fields_values))	{
			$fields_values = t3lib_div::slashArray($fields_values, 'add');
			$query = 'INSERT INTO tx_libunzipped_filestorage ('.implode(',',array_keys($fields_values)).') VALUES ("'.implode('","',$fields_values).'")';
			return $query;
		}
	}
	
	/**
	 * Removes directory with all files from the path $tempDir. 
	 * $tempDir must be a subfolder to typo3temp/
	 * 
	 * @param	[type]		$tempDir: ...
	 * @return	[type]		...
	 * @access private
	 */
	function removeDir($tempDir)	{
		$testDir=PATH_site.'typo3temp/';
		if (!t3lib_div::isFirstPartOfStr($tempDir,$testDir))	die($tempDir.' was not within '.$testDir);

			// Go through dirs:
		$dirs = t3lib_div::get_dirs($tempDir);
		if (is_array($dirs))	{
			reset($dirs);
			while(list(,$subdirs)=each($dirs))	{
				if ($subdirs)	{
					$this->removeDir($tempDir.$subdirs.'/');
				}
			}
		}
			// Then files in this dir:
		$fileArr=t3lib_div::getFilesInDir($tempDir,'',1);
		if (is_array($fileArr))	{
			reset($fileArr);
			while(list(,$file)=each($fileArr))	{
				if (!t3lib_div::isFirstPartOfStr($file,$testDir))	die($file.' was not within '.$testDir);	// PARAnoid...
				unlink($file);
			}
		}
			// Remove this dir:
		rmdir($tempDir);
	}

	/**
	 * Returns an array with all files and folders in $extPath
	 * 
	 * @param	[type]		$fileArr: ...
	 * @param	[type]		$extPath: ...
	 * @return	[type]		...
	 * @access private
	 */
	function getAllFilesAndFoldersInPath($fileArr,$extPath)	{
		$extList='';
		$fileArr[]=$extPath;
		$fileArr=array_merge($fileArr,t3lib_div::getFilesInDir($extPath,$extList,1,1));
		
		$dirs = t3lib_div::get_dirs($extPath);
		if (is_array($dirs))	{
			reset($dirs);
			while(list(,$subdirs)=each($dirs))	{
				if ($subdirs)	{
					$fileArr = $this->getAllFilesAndFoldersInPath($fileArr,$extPath.$subdirs.'/');
				}
			}
		}
		return $fileArr;
	}
	
	/**
	 * Returns a file with the relative (to the XML-file) path $filepath from the currently cached XML-file (read from database)
	 * 
	 * @param	[type]		$filepath: ...
	 * @return	[type]		...
	 */
	function getFileFromXML($filepath)	{
		$query = 'SELECT * FROM tx_libunzipped_filestorage WHERE rel_id='.intval($this->ext_ID).' AND hash="'.addslashes($this->fileHash).'" AND filepath="'.addslashes($filepath).'"';
		$res = mysql(TYPO3_db,$query);
		if ($row = mysql_fetch_assoc($res))	{
			if ($row['compressed'])	$row['content']=gzuncompress($row['content']);
			return $row;
		}
	}

	/**
	 * [Describe function...]
	 * 
	 * @return	[type]		...
	 */
	function getFileListFromDB()	{
		$query = 'SELECT uid,rel_id,hash,filemtime,filesize,filetype,filename,filepath,compressed,info 
					FROM tx_libunzipped_filestorage 
					WHERE rel_id='.intval($this->ext_ID).' 
						AND hash="'.addslashes($this->fileHash).'"';
		$res = mysql(TYPO3_db,$query);
		$output=array();
		while ($row = mysql_fetch_assoc($res))	{
			$output[]=$row;
		}
		return $output;
	}
}
 
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/libunzipped/class.tx_libunzipped.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/libunzipped/class.tx_libunzipped.php']);
}
?>