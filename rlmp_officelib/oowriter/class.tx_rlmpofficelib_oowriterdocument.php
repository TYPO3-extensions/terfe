<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 Robert Lemke (robert@typo3.org)
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

require_once(t3lib_extMgm::extPath('rlmp_officelib').'class.tx_rlmpofficelib_officefactory.php');
require_once(t3lib_extMgm::extPath('rlmp_officelib').'class.tx_rlmpofficelib_officedocument.php');
require_once(t3lib_extMgm::extPath('rlmp_officelib').'class.tx_rlmpofficelib_officemeta.php');
require_once(t3lib_extMgm::extPath('rlmp_officelib').'class.tx_rlmpofficelib_officetextcomponent.php');
require_once(t3lib_extMgm::extPath('rlmp_officelib').'class.tx_rlmpofficelib_div.php');
require_once(t3lib_extMgm::extPath('rlmp_officelib').'oowriter/class.tx_rlmpofficelib_oowritertextiterator.php');
require_once(t3lib_extMgm::extPath('libunzipped').'class.tx_libunzipped.php');

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   73: class rlmp_officelib_oowriterdocument extends rlmp_officelib_officedocument
 *   85:     function rlmp_officelib_oowriterdocument ()
 *
 *              SECTION: GENERAL DOCUMENT FUNCTIONS
 *  111:     function init ()
 *  153:     function load ($filename)
 *  220:     function save ($filename)
 *
 *              SECTION: PARSING FUNCTIONS
 *  242:     function parseMetaIntoObject ($metaArr, &$objMeta)
 *  292:     function parseContentBodyIntoObject ($bodyArr)
 *  313:     function parseParagraphIntoObject($v)
 *  628:     function parseTableRows ($subTags, $rowType)
 *  656:     function parseIndex ($v)
 *  727:     function getCurrentNumbering ($elementType, $level)
 *  750:     function prepareStyles ($stylesArr)
 *  808:     function parseStyleProperties ($attributesArr, &$properties)
 *
 *              SECTION: DEBUG / HELPER FUNCTIONS
 *  846:     function noProcessing ($v)
 *  858:     function debugDocumentObj (&$documentObj)
 *
 * TOTAL FUNCTIONS: 14
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Open Office Writer implementation of the officedocument class. This OOWriter
 * parser also works with PHP4 and does not depend on PHP extensions like DOM,
 * DOMXML and such. The drawback is a relatively high memory consumption.
 *
 * WARNING: This parser does NOT support everything which is possible with XML.
 * Especially namespaces are not supported and may turn out being a problem with
 * future versions of the Open Office format if prefixes like DC: (for Dublin Core)
 * are changed. However, it works pretty well with OOWriter files up to version
 * 1.1 (the current version at the time of this writing).
 *
 * Please only instantiate through the factory method in class officefactory.
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_oowriterdocument extends rlmp_officelib_officedocument {

	var $zipObj;
	var $obj_officelib_div;
	var $autoGenerateNumbering = false;

	/**
	 * Constructor
	 *
	 * @return	void
	 * @access public
	 */
	function rlmp_officelib_oowriterdocument () {
		$this->obj_officelib_div =& rlmp_officelib_div::getInstance ();

		$parentClass = get_parent_class($this);
		if (is_callable(array ($parentClass, $parentClass))) {
			parent::$parentClass ($parentDocObj, $content);
		}
	}





 	/***********************************************
	 *
	 * GENERAL DOCUMENT FUNCTIONS
	 *
  	 ***********************************************/

	/**
	 * Initialises a new document
	 *
	 * @param	string
	 * @return	void
	 * @access public
	 */
	function init () {
		unset ($this->textComposites['body']);
		$this->textComposites['body'] = new rlmp_officelib_textcomposite($this);
		$this->textComposites['body']->parentObj = &$this;

		unset ($this->textComposites['header']);
		$this->textComposites['header'] = new rlmp_officelib_textcomposite($this);
		$this->textComposites['header']->parentObj = &$this;

		unset ($this->textComposites['footer']);
		$this->textComposites['footer'] = new rlmp_officelib_textcomposite($this);
		$this->textComposites['footer']->parentObj = &$this;

		unset ($this->metaObj);
		$this->metaObj = new rlmp_officelib_officemeta($this);

			// Create some default configuration when to perform a page break and what objects to use for the TOC
	    $this->pageBreakConf = array (
			'classes' => 'rlmp_officelib_tcheader',
			'body' => array (
				'eval' => array (
					'(get_class ($obj) == "rlmp_officelib_tcheader") ? ($obj->level < 3) : true',
					'(get_class ($this->previousObj) != "rlmp_officelib_tcheader")',
				)
			)
		);

		$this->tocConf = array (
			'classes' => 'rlmp_officelib_tcheader',
			'eval' => array (
				'(get_class ($obj) == "rlmp_officelib_tcheader") ? ($obj->level < 4) : true',
			)
		);
	}

	/**
	 * Loads an open office writer document
	 *
	 * @param	string		$filename: The filename including including its absolute path
	 * @return	void
	 * @access public
	 */
	function load ($filename) {
		$this->init();

		if (@file_exists($filename)) {
				// Unzipping SXW file, getting filelist:
			$this->zipObj = t3lib_div::makeInstance('tx_libunzipped');
			$files = $this->zipObj->init($filename);

			if (is_array ($this->zipObj->errorMessages)) {
				$this->errorMessages = array_merge($this->errorMessages, $this->zipObj->errorMessages);
			}
			if ($files === false) {
				return false;
			} elseif (count($files))	{
					// Extract and parse the meta information of the document
				$fileInfo = $this->zipObj->getFileFromXML('meta.xml');
				$XMLcontent = $fileInfo['content'];
				$metaArr = t3lib_div::xml2tree ($XMLcontent);
				$this->parseMetaIntoObject ($metaArr['office:document-meta'][0]['ch']['office:meta'][0]['ch'], $this->metaObj);

					// Extract the style part of the document
				$fileInfo = $this->zipObj->getFileFromXML('styles.xml');
				$XMLcontent = $fileInfo['content'];
				$parser = xml_parser_create();
				xml_parse_into_struct ($parser,$XMLcontent,$vals,$index);
				xml_parser_free($parser);

					// Parse the "common" and "master" styles into the styles array
				$styleTags = array ('OFFICE:STYLES', 'STYLE:MASTER-STYLES', 'TEXT:LIST-STYLE');
				foreach ($styleTags as $tag) {
					$tmpStylesArr = $this->obj_officelib_div->indentSubTagsRec(array_slice($vals,$index[$tag][0]+1,$index[$tag][1]-$index[$tag][0]-1),2);
					$this->prepareStyles($tmpStylesArr);
					unset ($tmpStylesArr);
				}

					// Parse header and footer
				$this->textComposites['header'] =& $this->parseContentBodyIntoObject ($this->obj_officelib_div->indentSubTagsRec(array_slice($vals, $index['STYLE:HEADER'][0]+1, $index['STYLE:HEADER'][1]-$index['STYLE:HEADER'][0]-1),1));
				$this->textComposites['footer'] =& $this->parseContentBodyIntoObject ($this->obj_officelib_div->indentSubTagsRec(array_slice($vals, $index['STYLE:FOOTER'][0]+1, $index['STYLE:FOOTER'][1]-$index['STYLE:FOOTER'][0]-1),1));

					// Extract the content part of the document
				$fileInfo = $this->zipObj->getFileFromXML('content.xml');
				$XMLcontent = $fileInfo['content'];

				$parser = xml_parser_create();
				xml_parse_into_struct ($parser,$XMLcontent,$vals,$index);
				xml_parser_free($parser);

					// Parse the "automatic" styles into the styles array
				$officeStylesArr = $this->obj_officelib_div->indentSubTagsRec(array_slice($vals,$index['OFFICE:AUTOMATIC-STYLES'][0]+1,$index['OFFICE:AUTOMATIC-STYLES'][1]-$index['OFFICE:AUTOMATIC-STYLES'][0]-1),2);
				$this->prepareStyles ($officeStylesArr);
				unset ($officeStylesArr);

					// Parse the content part of the document
				$officeBodyArr = $this->obj_officelib_div->indentSubTagsRec(array_slice($vals, $index['OFFICE:BODY'][0]+1, $index['OFFICE:BODY'][1]-$index['OFFICE:BODY'][0]-1),1);
				$this->textComposites['body'] =& $this->parseContentBodyIntoObject ($officeBodyArr);
#echo t3lib_div::view_array($this->debugDocumentObj($this->textComposites['body']));
				return true;
			}
		}
		return false;
	}

	/**
	 * Saves an open office writer document
	 *
	 * @param	string		$filename: The filename
	 * @return	void
	 * @access public
	 * @todo	Not implemented yet.
	 */
	function save ($filename) {

	}






 	/***********************************************
	 *
	 * PARSING FUNCTIONS
	 *
  	 ***********************************************/
	/**
	 * Parses array from the "meta" XML namespace into the meta object
	 *
	 * @param	string		$metaArr: The XML content of the meta namespace (from file meta.xml) as an array
	 * @param	object		$objMeta: Reference to the meta object
	 * @return	void
	 * @access private
	 */
	function parseMetaIntoObject ($metaArr, &$objMeta) {
		if (is_array($metaArr)) {
			foreach ($metaArr as $element => $value) {

				switch ($element) {
					case 'meta:generator' :	$objMeta->setProperty ('generator', $value[0]['values'][0]); break;
					case 'meta:creation-date' :
						list ($date, $time) = explode ('T',$value[0]['values'][0]);
						$objMeta->setProperty ('creation-date', strtotime($date),'date');
					break;
					case 'meta:initial-creator' : $objMeta->setProperty ('initial-creator', $value[0]['values'][0]); break;
					case 'dc:date' :
						list ($date, $time) = explode ('T',$value[0]['values'][0]);
						$objMeta->setProperty ('modification-date', strtotime ($date),'date');
					break;
					case 'dc:creator' : $objMeta->setProperty ('creator', $value[0]['values'][0]); break;
					case 'meta:user-defined' :
						if (is_array ($value)) {
							foreach ($value as $userFields) {
								$objMeta->setProperty ('user_'.$userFields['attrs']['meta:name'], $userFields['values'][0]);
							}
						}
						break;
					case 'meta:document-statistic' :
						if (is_array ($value[0]['attrs'])) {
							foreach ($value[0]['attrs'] as $k => $v) {
								unset ($label);
								if ($k == 'meta:table-count') $label = 'tables';
								if ($k == 'meta:image-count') $label = 'images';
								if ($k == 'meta:object-count') $label = 'objects';
								if ($k == 'meta:page-count') $label = 'pages';
								if ($k == 'meta:paragraph-count') $label = 'paragraphs';
								if ($k == 'meta:word-count') $label = 'words';
								if ($k == 'meta:character-count') $label = 'characters';
								if ($label) {
									$objMeta->setProperty ('statistics_'.$label, $v, 'integer');
								}
							}
						}
						break;
					case 'dc:title' :
						$objMeta->setProperty ('title', $value[0]['values'][0]);
						$this->documentTitle = $value[0]['values'][0];
					break;
				}
			}
		}
	}

	/**
	 * Parses XML from the content part of an open office file
	 *
	 * @param	string		$XMLcontent: The XML content of the meta namespace (from file content.xml)
	 * @return	object		textComponent or textComposite object
	 * @access private
	 */
	function parseContentBodyIntoObject ($bodyArr) {
#debug ($bodyArr,'contentArr',__LINE__,__FILE__,15);
		$returnObj = new rlmp_officelib_textcomposite($this);

		if (is_array ($bodyArr)) {
			reset ($bodyArr);
			while(list(,$v) = each($bodyArr)) {
				$tmpRef =& $this->parseParagraphIntoObject ($v);
				$returnObj->add ($tmpRef);
			}
		}
		return $returnObj;
	}

	/**
	 * This processes the content inside a paragraph or header. The result will be added to the
	 * given textComposite object
	 *
	 * @param	array		$v: Arrayed XML sub-structure of the paragraph or header
	 * @return	object		textComponent or textComposite object
	 * @access private
	 */
	function parseParagraphIntoObject($v) {
		if (is_array ($v)) {
			$v['_method'] = 'parseParagraphIntoObject';
			switch($v['tag'])	{
				case 'TEXT:SECTION':
						// Not implemented: If TEXT:SECTION-SOURCE or OFFICE:DDE-SOURCE is defined (subTags), then the
						// content of this section resides in a different file or datasource
					if (!$v['attributes']['TEXT:DISPLAY'] == 'none') {
						$this->currentSection = $v['attributes']['TEXT:NAME'];
						$this->currentSection = '_hidden';
						$returnObj = new rlmp_officelib_textcomposite($this);
					} else {
						// Not implemented: display conditions
					}
				break;
				case 'TEXT:P':
				case 'TEXT:INDEX-TITLE':
					if (t3lib_div::inList('complete,cdata,open,close',$v['type']))	{
						$returnObj = new rlmp_officelib_tcparagraph($this, $v['value']);
						$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
						$returnObj->type = $v['type'];
					} else {
						$returnObj = $this->noProcessing($v);
					}
				break;
				case 'TEXT:H':
					if (t3lib_div::inList('complete,cdata',$v['type']))	{
						$returnObj = new rlmp_officelib_tcheader($this, $v['value']);
						$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
						$returnObj->name = $v['value'];
						$returnObj->level = $v['attributes']['TEXT:LEVEL'];
						$returnObj->hyperlinkKey = '#'.$returnObj->name;
						$returnObj->numbering = $this->getCurrentNumbering ('header', $returnObj->level);	// THIS MUST BE REMOVED WHEN NUMBERING IS IMPLEMENTED PROPERLY!
					} else $returnObj = $this->noProcessing($v);
				break;
				case 'TEXT:S':
					$returnObj = new rlmp_officelib_tcspace($this, t3lib_div::intInRange($v['attributes']['TEXT:C'],1,30));
					$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
				break;
				case 'TEXT:TAB-STOP':
					$returnObj = new rlmp_officelib_tctab($this);
				break;
				case 'TEXT:LINE-BREAK':
					$returnObj = new rlmp_officelib_tclinebreak($this);
				break;
				case 'TEXT:SPAN':
					if (t3lib_div::inList('complete,cdata',$v['type']))	{
						$returnObj = new rlmp_officelib_tcspan($this, $v['value']);
						$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
					} else $returnObj = $this->noProcessing($v);
				break;
				case 'TEXT:A':
					if (t3lib_div::inList('complete,cdata',$v['type']))	{
						$returnObj = new rlmp_officelib_tclink($this, $v['value']);
						$returnObj->href = str_replace ('_',' ',urldecode ($v['attributes']['XLINK:HREF']));
						$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
					} else $returnObj = $this->noProcessing($v);
				break;
				case 'TABLE:TABLE':
					if (is_array($v['subTags']))	{
						$returnObj = new rlmp_officelib_tctable($this, '', 'table');
						$returnObj->name = $v['attributes']['TABLE:NAME'];
						$returnObj->styleName = $v['attributes']['TABLE:STYLE-NAME'];

						$tableElements = $this->obj_officelib_div->indentSubTagsRec($v['subTags'],1);
						reset($tableElements);

						while(list($k,$v) = each($tableElements)) {
							if ($v['tag'] == 'TABLE:TABLE-COLUMN') $returnObj->numCols ++;
							if ($v['tag']=='TABLE:TABLE-HEADER-ROWS')	{
								$headerRows = $this->obj_officelib_div->indentSubTagsRec($v['subTags'],1);
								reset($headerRows);
								while(list(,$vv)=each($headerRows))	{
									$tmpRef = $this->parseTableRows ($vv['subTags'], 'header-row');
									$returnObj->add ($tmpRef);
								}
							}
							if ($v['tag']=='TABLE:TABLE-ROW')	{
								$tmpRef = $this->parseTableRows ($v['subTags'], 'row');
								$returnObj->add ($tmpRef);
							}
						}
						$returnObj->hyperlinkKey = '#'.$returnObj->name.'|table';
					} else {
						$returnObj = new rlmp_officelib_tcerror($this, 'Table has no child objects! '.$v['tag']);
					}
				break;
				case 'TEXT:ORDERED-LIST':
				case 'TEXT:UNORDERED-LIST':
					if (is_array($v['subTags'])) {
						$returnObj = new rlmp_officelib_tclist ($this,'',($v['tag'] == 'TEXT:ORDERED-LIST') ? 'ordered-list' : 'unordered-list');
						$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
						$returnObj->continueNumbering = $v['attributes']['TEXT:CONTINUE-NUMBERING'];
					} else $returnObj = new rlmp_officelib_tcerror($this, 'List has no child objects! '.$v['tag']);
				break;

				case 'TEXT:LIST-ITEM':
					$returnObj = new rlmp_officelib_tclist ($this,'','list-item');
					$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
					$returnObj->restartNumbering = $v['attributes']['TEXT:RESTART-NUMBERING'];
					$returnObj->startValue = $v['attributes']['TEXT:START-VALUE'];
				break;
				case 'TEXT:LIST-HEADER':
					$returnObj = new rlmp_officelib_tclist ($this,'','list-header');
					$tmpRef = $this->parseContentBodyIntoObject($this->obj_officelib_div->indentSubTags($v['subTags']));
					$returnObj->add ($tmpRef);
					$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
					$returnObj->restartNumbering = $v['attributes']['TEXT:RESTART-NUMBERING'];
					$returnObj->startValue = $v['attributes']['TEXT:START-VALUE'];
				break;

				case 'OFFICE:STYLES':
				case 'OFFICE:AUTOMATIC-STYLES':
				case 'OFFICE:MASTER-STYLES':
				case 'OFFICE:FONT-DECLS':
						// Just ignore that.
					$returnObj = null;
				break;

				case 'TEXT:VARIABLE-DECLS':
				case 'TEXT:USER-FIELD-DECLS':
				case 'TEXT:SEQUENCE-DECLS':
					if (is_array ($v['subTags'])) {
						switch ($v['tag']) {
							case 'TEXT:VARIABLE-DECLS':		$key = 'variable'; break;
							case 'TEXT:USER-FIELD-DECLS':	$key = 'user'; break;
							case 'TEXT:SEQUENCE-DECLS':		$key = 'sequence'; break;
							default: $key = 'ERROR';
						}
						foreach ($v['subTags'] as $vv) {
							$this->variablesArr[$key][$vv['attributes']['TEXT:NAME']] = array (
								'declared' => true,
								'level' => $vv['attributes']['TEXT:DISPLAY-OUTLINE-LEVEL'],
							);
							if (intval ($vv['attributes']['TEXT:DISPLAY-OUTLINE-LEVEL'])) {
								$this->variablesArr[$key][$vv['attributes']['TEXT:NAME']]['separation-character'] = $vv['attributes']['TEXT:SEPARATION-CHARACTER'];
							}
						}
					}
					$returnObj = null;
				break;
				case 'TEXT:SEQUENCE':
					/*
					$returnObj = new rlmp_officelib_tcvariable(&$this);
					$returnObj->type = 'sequence';
					$returnObj->name = $v['attributes']['TEXT:NAME'];
					$returnObj->formula = $v['attributes']['TEXT:FORMULA'];
						# STYLE:NUM-FORMAT			-> 1|i|I|a|A    -> numeric, alphabetic or romanic numbering
						# STYLE:NUM-LETTER-SYNC     -> true | false -> synchronized: aa bb cc -> not synchronized: aa ab ac
						# TEXT:REF-NAME				-> used for references
					*/
#debug ($v, $v['tag'],__LINE__,__FILE__);
					$returnObj = null;

				break;
				case 'TEXT:VARIABLE-SET':
				case 'TEXT:VARIABLE-GET':
				case 'TEXT:VARIABLE-INPUT':
				case 'TEXT:USER-FIELD-INPUT':
				case 'TEXT:EXPRESSION':
				case 'TEXT:INPUT':
				case 'TEXT:TEXT-INPUT':
						// TODO
					$returnObj =& $this->noProcessing($v);
				break;

				case 'TEXT:USER-FIELD-GET':
					$returnObj =& new rlmp_officelib_tcvariable ($v['attributes']['TEXT:NAME']);
				break;
				case 'TEXT:TABLE-OF-CONTENT':
				case 'TEXT:TABLE-INDEX':
				case 'TEXT:OBJECT-INDEX':
				case 'TEXT:ILLUSTRATION-INDEX':
				case 'TEXT:USER-INDEX':
					$returnObj =& $this->parseIndex($v);
				break;
				case 'TEXT:INDEX-BODY':
				case 'TEXT:TABLE-OF-CONTENT-SOURCE':
				case 'TEXT:TABLE-INDEX-SOURCE':
				case 'TEXT:OBJECT-INDEX-SOURCE':
				case 'TEXT:ILLUSTRATION-INDEX-SOURCE':
				case 'TEXT:USER-INDEX-SOURCE':
						// We don't support "real" indexes yet, only cached versions
					$returnObj = null;
				break;
				case 'DRAW:IMAGE':
					if ($v['attributes']['XLINK:HREF'])	{
						$fI = pathinfo($v['attributes']['XLINK:HREF']);
						if (t3lib_div::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],strtolower($fI['extension'])))	{
							$imgData = $this->zipObj->getFileFromXML(substr($v['attributes']['XLINK:HREF'],1));
							if (is_array($imgData))	{
								$imgInfo = unserialize($imgData['info']);
								if (is_array($imgInfo))	{
									$writefile='typo3temp/tx_rlmpofficelib_'.t3lib_div::shortmd5($imgData['filepath']).'.'.$imgData['filetype'];
									t3lib_div::writeFile(PATH_site.$writefile,$imgData['content']);
									$returnObj = new rlmp_officelib_tcimage($this, $writefile);
									$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
									$returnObj->height = $imgInfo[1];
									$returnObj->width = $imgInfo[0];
									$returnObj->displayHeight = $this->obj_officelib_div->convertToPixels($v['attributes']['SVG:HEIGHT']);
									$returnObj->displayWidth = $this->obj_officelib_div->convertToPixels($v['attributes']['SVG:WIDTH']);
									$returnObj->name = $v['attributes']['DRAW:NAME'];
									$returnObj->hyperlinkKey = '#'.$returnObj->name.'|graphic';
								}
							}
						} else $returnObj = $this->noProcessing($v);
					}
				break;
				case 'DRAW:TEXT-BOX':
					if (is_array ($v['subTags'])) {
						$returnObj = new rlmp_officelib_textcomposite($this);
					} else $returnObj = $this->noProcessing($v);
				break;
				case 'DRAW:OBJECT-OLE':
				case 'DRAW:OBJECT':
				case 'DRAW:PLUGIN':
						// We ignore plugins and objects for now
					$returnObj = null;
				break;
				case 'TEXT:BOOKMARK':
				case 'TEXT:BOOKMARK-START':
					$returnObj = new rlmp_officelib_tcbookmark($this,'');
					$returnObj->name = $v['attributes']['TEXT:NAME'];
				break;
				case 'TEXT:BOOKMARK-END':
						// Bookmark ranges are not supported (yet).
					$returnObj = null;
				break;
				case 'TEXT:TRACKED-CHANGES':
				case 'TEXT:CHANGE':
				case 'TEXT:CHANGE-START':
				case 'TEXT:CHANGE-END':
						// We don't support change tracking yet
					$returnObj = null;
				break;
				case 'TEXT:TITLE':
					$returnObj = new rlmp_officelib_tcmeta($this, 'title');
				break;
				case 'TEXT:PAGE-NUMBER':
					$returnObj = new rlmp_officelib_tcmeta($this, '_page-number');
				break;
				case 'TEXT:PAGE-COUNT':
					$returnObj = new rlmp_officelib_tcmeta($this, '_number-of-pages');
				break;
				case 'TEXT:CREATION-DATE':
					$returnObj = new rlmp_officelib_tcmeta($this, 'creation-date');
				break;
				case 'TEXT:MODIFICATION-DATE':
					$returnObj = new rlmp_officelib_tcmeta($this, 'modification-date');
				break;
				case 'TEXT:CREATOR':
					$returnObj = new rlmp_officelib_tcmeta($this, 'creator');
				break;
				case 'TEXT:INITIAL-CREATOR':
					$returnObj = new rlmp_officelib_tcmeta($this, 'initial-creator');
				break;
				case 'TEXT:PARAGRAPH-COUNT':
					$returnObj = new rlmp_officelib_tcmeta($this, 'statistics_paragraphs');
				break;
				case 'TEXT:WORD-COUNT':
					$returnObj = new rlmp_officelib_tcmeta($this, 'statistics_words');
				break;
				case 'TEXT:CHARACTER-COUNT':
					$returnObj = new rlmp_officelib_tcmeta($this, 'statistics_characters');
				break;
				case 'TEXT:IMAGE-COUNT':
					$returnObj = new rlmp_officelib_tcmeta($this, 'statistics_images');
				break;
				case 'TEXT:TABLE-COUNT':
					$returnObj = new rlmp_officelib_tcmeta($this, 'statistics_tables');
				break;
				case 'TEXT:OBJECT-COUNT':
					$returnObj = new rlmp_officelib_tcmeta($this, 'statistics_objects');
				break;

				case 'TEXT:USER-DEFINED':
					$returnObj = new rlmp_officelib_tcfield($this, $v['value']);
					$returnObj->type = 'user';
				break;
				case 'TEXT:DATE':
					list ($date, $time) = explode ('T',$v['attributes']['TEXT:DATE-VALUE']);
					$returnObj = new rlmp_officelib_tcfield($this, strtotime ($date));
					$returnObj->type = 'date';
					$returnObj->fixed = (isset ($v['attributes']['TEXT:FIXED'])) ? $v['attributes']['TEXT:FIXED'] : false;
				break;
				case 'TEXT:TIME':
					list ($date, $time) = explode ('T',$v['attributes']['TEXT:TIME-VALUE']);
					$returnObj = new rlmp_officelib_tcfield($this, strtotime ($time));
					$returnObj->type = 'time';
					$returnObj->fixed = (isset ($v['attributes']['TEXT:FIXED'])) ? $v['attributes']['TEXT:FIXED'] : false;
				break;

				case 'TEXT:FILE-NAME':
					$returnObj = null;
				break;

				default:
					$returnObj = $this->noProcessing($v);
				break;
			}

			if (is_array ($v['subTags'])) {
				$v['subTags'] = $this->obj_officelib_div->indentSubTags($v['subTags']);
				reset($v['subTags']);
				while(list(,$subV)=each($v['subTags']))	{
					if (is_object ($returnObj) && is_callable(array ($returnObj,'add'))) {
						$tmpRef =& $this->parseParagraphIntoObject($subV);
						$returnObj->add ($tmpRef);
					}
				}
			}

			return $returnObj;
		}
	}

	/**
	 * Processing of table rows, used by the body rendering function
	 *
	 * @param	array		$subTags: Arrayed XML structure of the table row's content. Will be parsed by renderOOBody()
	 * @param	string		$rowType: 'header-row' or 'row'
	 * @return	object		Table row object
	 * @access private
	 */
	function parseTableRows ($subTags, $rowType) {
		$objTableRow = new rlmp_officelib_tctable($this, '', $rowType);
		$objTableRow->styleName = $v['attributes']['TABLE:STYLE-NAME'];
		$objTableRow->numberRowsSpanned = $v['attributes']['TABLE:NUMBER-ROWS-SPANNED'];

		$cellsArr = $this->obj_officelib_div->indentSubTagsRec($subTags,2);
		reset($cellsArr);
		while(list($k,$v) = each($cellsArr)) {
			if ($v['tag'] == 'TABLE:TABLE-CELL') {
				$objTableCell = new rlmp_officelib_tctable($this, '','cell');
				$objTableCell->styleName = $v['attributes']['TABLE:STYLE-NAME'];
				$objTableCell->numberColumnsSpanned = $v['attributes']['TABLE:NUMBER-COLUMNS-SPANNED'];
				$objTableCell->valueType = $v['attributes']['TABLE:VALUE-TYPE'];

				$tmpRef = $this->parseContentBodyIntoObject($v['subTags']);
				$objTableCell->add ($tmpRef);
				$objTableRow->add ($objTableCell);
			}
		}
		return $objTableRow;
	}

	/**
	 * Processing of an index like a table of content, table index etc.
	 *
	 * @param	array		$v:	The arrayed XML sub-structure only containing the subpart of the index
	 * @return	object		Index object
	 * @access private
	 */
	function parseIndex ($v) {
		$returnObj = new rlmp_officelib_tcindex ($this);
		switch ($v['tag']) {
			case 'TEXT:ILLUSTRATION-INDEX':
				$returnObj->type = 'illustration';
				$confUseCaption = isset ($v['attributes']['TEXT:USE-CAPTION']) ? $v['attributes']['TEXT:USE-CAPTION'] : true;
				if ($confUseCaption) {
					$confCaptionSequenceName = $v['attributes']['TEXT:CAPTION-SEQUENCE-NAME'];
					$confSequenceFormat = $v['attributes']['TEXT:SEQUENCE-FORMAT'];
				}
			break;
			case 'TEXT:TABLE-INDEX':
				$returnObj->type = 'table';
				$confUseCaption = isset ($v['attributes']['TEXT:USE-CAPTION']) ? $v['attributes']['TEXT:USE-CAPTION'] : true;
				if ($confUseCaption) {
					$confCaptionSequenceName = $v['attributes']['TEXT:CAPTION-SEQUENCE-NAME'];
					$confSequenceFormat = $v['attributes']['TEXT:SEQUENCE-FORMAT'];
				}
			break;
			case 'TEXT:TABLE-OF-CONTENT':
				$returnObj->type = 'toc';
			break;
			case 'TEXT:OBJECT-INDEX':
				$returnObj->type = 'object';
			break;
			case 'TEXT:USER-INDEX':
				$returnObj->type = 'user';
			break;
			default:
				$returnObj->type = 'unknown';
		}

		$returnObj->name = $v['attributes']['TEXT:NAME'];
		$returnObj->styleName = $v['attributes']['TEXT:STYLE-NAME'];
		# $returnObj->xxxxx = $v['attributes']['TEXT:INDEX-SCOPE'];

		if (is_array($v['subTags'])) {
			$subTagsArr = $this->obj_officelib_div->indentSubTagsRec($v['subTags'],2);
			foreach ($subTagsArr as $vv) {
				switch ($vv['tag']) {
					case 'TEXT:INDEX-BODY':
							// This is the cached version of the index which contains the original page numbers etc., but we save that anyways
						if (is_array ($vv['subTags'])) {
							$tmpRef = $this->parseContentBodyIntoObject($vv['subTags']);
							$returnObj->add ($tmpRef);
						}
						break;
					case 'TEXT:ILLUSTRATION-INDEX-SOURCE':
					case 'TEXT:TABLE-OF-CONTENT-SOURCE':
					case 'TEXT:TABLE-INDEX-SOURCE':
					case 'TEXT:OBJECT-INDEX-SOURCE':
					case 'TEXT:USER-INDEX-SOURCE':
							// This is used to generate templates for the index entries. We don't use that yet, but should do that in the future
							// Subtags: INDEX-TITLE-TEMPLATE, *-ENTRY-TEMPLATE
						break;
					default:
				}
			}
		}
		return $returnObj;
	}

	/**
	 * Returns a numbering like 1.4.2. etc. for a certain element and level.
	 * THIS FUNCTION IS STUPID, it's only a substitute for the real numbering of OOdocs which is not implemented yet
	 *
	 * @param	string		$elementType
	 * @param	integer		$level
	 * @return	string
	 * @access private
	 * @todo	Replace this function by the real thing
	 */
	function getCurrentNumbering ($elementType, $level) {
		$level;
		$out = '';
		if (intval ($level) && $this->autoGenerateNumbering) {
			$this->numberingArr[$elementType][intval($level)]++;
			for ($j=($level+1); $j<=10; $j++) {
				$this->numberingArr[$elementType][$j] = 0;
			}
			for ($i=1; $i<=$level-$this->numberingOffset; $i++) {
				$out .= $this->numberingArr[$elementType][$i+$this->numberingOffset] . '.';
			}
		}
		return $out;
	}

	/**
	 * This processes the styles of the current document. The result will be stored in this document's
	 * styles array.
	 *
	 * @param	array		$stylesArr: Arrayed XML sub-structure of the automatic styles part
	 * @return	void
	 * @access private
	 */
	function prepareStyles ($stylesArr) {
		if (is_array ($stylesArr)) {
			reset($stylesArr);
			while(list($k,$v)=each($stylesArr))	{
#debug (array ($k,$v),'styles',__LINE__,__FILE__,16);
				if (t3lib_div::inList ('STYLE:STYLE,TEXT:LIST-STYLE', $v['tag'])) {
					$name = $v['attributes']['STYLE:NAME'];
					$properties = array ();
					$subStyles = array ();
					$mode = '';

					if (is_array ($v['subTags'])) {

						foreach ($v['subTags'] as $subTag) {
							switch ($subTag['tag']) {
								case 'STYLE:PROPERTIES':
									$mode = 'style';
									$this->parseStyleProperties ($subTag['attributes'], $properties);
									break;

								case 'TEXT:LIST-LEVEL-STYLE-BULLET':
								case 'TEXT:LIST-LEVEL-STYLE-NUMBER':
									$mode = strtolower (substr($subTag['tag'], strpos ($subTag['tag'], ':')));
									if (is_array ($subTag['subTags'])) {
											// Render the 10 different level-styles for bullet-lists
										$this->prepareStyles($subTag['subTags']);

											// Assign level-styles to this list style
										$subStyles[$subTag['attributes']['TEXT:LEVEL']] = $subTag['attributes']['TEXT:STYLE-NAME'];
									}
									break;
							}
						}
					}
						// Insert style into document's style table
					$this->stylesArr[$name] = array (
						'family' => ((t3lib_div::inList ($this->validStyleFamilies,$v['STYLE:FAMILY'])) ? $v['STYLE:FAMILY'] : $this->defaultStyleFamily),
						'parentStyleName' => $v['attributes']['STYLE:PARENT-STYLE-NAME'],
						'masterStyleName' => $v['attributes']['STYLE:MASTER-PAGE-NAME'],
						'mode' => $mode,
						'subStyles' => $subStyles,
						'properties' => $properties,
					);
				}
			}
		}
	}

	/**
	 * Parses an array of style properties into the officelib common style properties format. The variable $properties
	 * which is passed by reference, will contain the result.
	 *
	 * @param	array		$attributesArr: The attributes. Example: 'TABLE-ALIGN' => 'left', ...
	 * @param	array		$properties: Parsed properties will be merged into this array
	 * @return	void
	 * @access private
	 * @see prepareStyles()
	 */
	function parseStyleProperties ($attributesArr, &$properties) {
		if (is_array ($attributesArr)) {
			foreach ($attributesArr as $key => $value) {
				switch ($key) {
					case 'STYLE:WIDTH':
					case 'STYLE:HEIGHT':
					case 'FO:MARGIN-LEFT':
					case 'FO:MARGIN-RIGHT':
					case 'FO:MARGIN-TOP':
					case 'FO:MARGIN-BOTTOM':
					case 'FO:TEXT-INDENT':
						$properties[$key] = t3lib_div::intInRange( $this->obj_officelib_div->convertToPixels ($value),0,2000).'px';
					break;
					default:
						$properties[$key] = $value;
				}
			}
		}
	}





 	/***********************************************
	 *
	 * DEBUG / HELPER FUNCTIONS
	 *
  	 ***********************************************/

	/**
	 * Adds an "error" type textcomponent to the specified textComposite object. This method
	 * is called whenever a certain tag could not be processed.
	 *
	 * @param	array		$v: Arrayed XML sub-structure of the paragraph or header
	 * @return	object		$textComposite: An instantiated textcomposite object
	 * @access private
	 */
	function noProcessing ($v)	{
		return new rlmp_officelib_tcerror($this, 'Did not know how to render this tag: '.$v['tag'].' ('.$v['_method'].') - type: '.$v['type']);
	}


	/**
	 * Creates an array for debugging the document object
	 *
	 * @param	object		$documentObj
	 * @return	array		Nested table object structure
	 * @access private
	 */
	function debugDocumentObj (&$documentObj) {
		$returnArr = array ();

		if (is_a ($documentObj,'rlmp_officelib_tctable')) {
			$returnArr[get_class($documentObj)] = array (
				'type' => $documentObj->type,
				'numberRowsSpanned' => $documentObj->numberRowsSpanned,
				'numberColumnsSpanned' => $documentObj->numberColumnsSpanned,
				'numCols' => $documentObj->numCols,
				'valueType' => $documentObj->valueType,
			);
		} else {
			$returnArr[get_class($documentObj)] = array (
				'content' => $documentObj->content,
				'numberChildren' => count ($documentObj->children),
				'numberSisters' => count ($documentObj->sisters),
			);
		}

		if (is_callable (array ($documentObj, 'getChildren'))) {
			$childrenArr = $documentObj->getChildren();
			if (is_array ($childrenArr)) {
				foreach ($childrenArr as $child) {
					$returnArr[get_class($documentObj)]['children'][] = $this->debugdocumentObj($child);
				}
			}
		}
		return $returnArr;
	}
}

?>