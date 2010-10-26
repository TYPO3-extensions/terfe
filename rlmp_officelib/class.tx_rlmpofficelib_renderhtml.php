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

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   46: class rlmp_officelib_renderhtml
 *   64:     function render (&$reference, $preRendered='')
 *  270:     function renderStyleAttributes ($styleArr)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Implementation of the office XHTML rendering class for Office documents
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_rlmpofficelib
 */
class rlmp_officelib_renderhtml {

	var $conf = array ();
	var $numberingArr = array (								// This is used until proper numebering is implemented
		'headers' => array (0,0,0,0,0,0,0,0,0,0),
	);
	var $backPath = '';										// May be set, eg. when using in backend mode. image paths will be prefixed with this backpath

	/**
	 * The render function. Only call if this is an instantiate object.
	 *
	 * @param	object		Object to be rendered
	 * @param	[type]		$preRendered: ...
	 * @return	string		HTML output
	 * @access private
	 */
	function render (&$reference, $preRendered='') {
		$styleName =& $reference->styleName;
		$stylesArr = $reference->parentDocObj->getMergedStyles ();
		$renderedAttributes = $this->renderStyleAttributes($stylesArr[$styleName]);
		$wrap = $debugSpan = array ('','');
		$debug =& $reference->parentDocObj->debug;

		if (is_object ($reference)) {
			if ($debug) {
				$debugHTML  = 'Style name:    '.$styleName.'\n';
				$debugHTML .= 'Class:         '.get_class($reference).'\n';
				$debugHTML .= 'Type:          '.$reference->type.'\n';
				$debugHTML .= 'Conditions:\n';
				foreach ($reference->conditionTags as $key => $value) {
					$debugHTML .= '           '.$key.' = '.$value.'\n';
				}
				$debugHTML .= 'Properties:\n';
				if (is_array ($stylesArr[$styleName]['properties'])) {
					foreach ($stylesArr[$styleName]['properties'] as $key => $value) {
						$debugHTML .= '           '.$key.' = '.$value.'\n';
					}
				} else {
					$debugHTML .= 'none\n';
				}

				$debugOMO = ' onMouseOver="tx_rlmpofficelib_officedocument_showDebug(\''.$debugHTML.'\');"';
				$debugOMO .= ' onMouseOut="tx_rlmpofficelib_officedocument_hideDebug();"';
				$debugSpan = array ('<span '.$debugOMO.'>','</span>');

#				if (!is_array ($stylesArr[$styleName])) { $debugOMO.= ' style="background-color: #EEEEEE"'; }
			}

				// Now render the different text composite / component objects:

			if (is_a ($reference, 'rlmp_officelib_tcparagraph')) {
				$attributes = ($renderedAttributes ? ' style="'.$renderedAttributes.'"' : '').' '.$this->conf['tagAttributes.']['tcparagraph'];
				if ($reference->type == 'complete') {
					$wrap = array ('<p'.$attributes.'>','</p>');
					if (!strlen ($preRendered)) { $preRendered = '&nbsp;'; }
				}
				return $wrap[0].$debugSpan[0].htmlspecialchars($reference->content).$debugSpan[1].$preRendered.$wrap[1];

			} elseif (is_a ($reference, 'rlmp_officelib_tcheader')) {
				$attributes = $renderedAttributes ? ' style="'.$renderedAttributes.'"' : '';
				$numbering = $reference->numbering ? $reference->numbering.' ' : '';
				if (strlen ($reference->name)) {
					$attributes.= ' name="'.htmlspecialchars($reference->name).'"';
					$wrap[0].= ' <a id="'.substr ($reference->hyperlinkKey,1).'"></a>';
				}
				return $wrap[0].'<h'.$reference->level.$attributes.$debugOMO.'>'.htmlspecialchars($numbering.$reference->content).'</h'.$reference->level.'>'.$wrap[1];

			} elseif (is_a ($reference, 'rlmp_officelib_tclinebreak')) {
				return '<br />';

			} elseif (is_a ($reference, 'rlmp_officelib_tcspace')) {
				return str_repeat($debug ? '<span style="color:grey;">·</span>':'&nbsp;', intval ($reference->content));

			} elseif (is_a ($reference, 'rlmp_officelib_tctab')) {
				return $debug ? '<span style="color:grey;">&curren;&middot;&middot;&middot;</span>': str_repeat('&nbsp;', 4);

			} elseif (is_a ($reference, 'rlmp_officelib_tcimage')) {
				$maxW = intval ($this->conf['maxImageWidth']) ? intval ($this->conf['maxImageWidth']) : 400;
				$ratio = $reference->width / $reference->height;
				$width = intval($reference->displayWidth && !$this->conf['dontRenderImageDisplaySize']) ? $reference->displayWidth : $reference->width;
				$height = ceil ($width / $ratio);

				if (strlen ($reference->name)) {
					$attributes.= ' name="'.htmlspecialchars($reference->name).'"';
					$wrap[0].= ' <a id="'.htmlspecialchars(substr ($reference->hyperlinkKey,1)).'"></a>';
				}
				if ($reference->width > $maxW)	{
					return $wrap[0].'<a href="#" onclick="'.htmlspecialchars('vHWin=window.open(\''.$this->backPath.$reference->content.'\',\'_NEW_IMG_WINDOW\',\'width='.($width+40).',height='.($height+40).',status=0,menubar=0,scrollbars=1,resizable=1\');vHWin.focus();return false;').'"><img src="'.$this->backPath.$reference->content.'" width="'.$maxW.'" height="'.floor($reference->height/($reference->width/$maxW)).'" style="border: 1px solid black; text-decoration:none;" border="0" title="" alt="" /></a><br />';
				} else {
					return $wrap[0].'<img src="'.$this->backPath.$reference->content.'" width="'.$width.'" height="'.$height.'"'.($width>50 && $height>50 ? ' style="border: 1px solid black; decoration:none; "':'').$debugOMO.' /><br />';
				}

			} elseif (is_a ($reference, 'rlmp_officelib_tclink')) {
				$label = $reference->content ? $reference->content : $preRendered;
				$href = $reference->href;
				if ($href{0} == '#') {
					$tmpArr = explode ('|',$href);
					if ($tmpArr[1] == 'outline') {	// We don't support links to real outline (numbered) headers etc. That depends on implementation of real numbering features
						$href = $tmpArr[0];
					}
					$href = '#'.trim(substr (preg_replace('/([0-9]\.)*([0-9])*/', '', $href), 1));
				}
				if (isset ($reference->parentDocObj->hyperlinkObjects[$href])) {
					$link = '<a href="'.$this->conf['internalLinkTemplate'].$href.'">'.$label.'</a>';
					if (is_array ($reference->parentDocObj->hyperlinkObjects[$href]->conditionTags)) {
						foreach ($reference->parentDocObj->hyperlinkObjects[$href]->conditionTags as $key => $value) {
							$link = str_replace ('###'.strtoupper($key).'###', $value, $link);
						}
					}
					return $link;
				} else {
					$target = $this->conf['externalLinkTarget'];
					return '<a href="'.$href.'">'.$label.'</a>';
				}

			} elseif (is_a ($reference, 'rlmp_officelib_tcspan')) {
				$attributes = $renderedAttributes ? ' style="'.$renderedAttributes.'"' : '';
				$inner = (is_callable(array ($reference->childObj, 'render'))) ? $reference->childObj->render() : '';
				return '<span'.$attributes.$debugOMO.'>'.htmlspecialchars($reference->content).$inner.'</span>';

			} elseif (is_a ($reference, 'rlmp_officelib_tctable')) {
				switch ($reference->type) {
					case 'table' :
						$attributes = $renderedAttributes ? ' style="'.$renderedAttributes.'"' : '';
						if (!$this->conf['renderTableWidths']) { $attributes = preg_replace('/width:.*;/', '', $attributes); }
						if ($stylesArr[$styleName]['properties']['TABLE:ALIGN']) {
							$wrap = array ('<div style="text-align: '.$stylesArr[$styleName]['properties']['TABLE:ALIGN'].'">','</div>');
						}
						if (strlen ($reference->name)) {
							$attributes.= ' name="'.htmlspecialchars($reference->name).'"';
							$wrap[0].= ' <a id="'.substr ($reference->hyperlinkKey,1).'"></a>';
						}
						return $wrap[0].'<table cellspacing="0" '.$attributes.'>'.$preRendered.'</table>'.$wrap[1];

					case 'header-row' :
					case 'row' :
						$attributes = $renderedAttributes ? ' style="'.$renderedAttributes.'"' : '';
						if ($reference->numberRowsSpanned > 1) {
							$attributes.=' colspan="'.$reference->numberColumnsSpanned.'"';
						}
						return '<tr'.$attributes.'>'.$preRendered.'</tr>';
					case 'cell' :
						if (!preg_match('/vertical-align:.*;/', $renderedAttributes)) { $renderedAttributes.=' vertical-align:top;'; }
						$attributes = $renderedAttributes ? ' style="'.$renderedAttributes.'"' : '';
						if ($reference->numberColumnsSpanned > 1) {	$attributes.=' colspan="'.$reference->numberColumnsSpanned.'"';	}
						return '<td'.$attributes.'>'.$preRendered.'</td>';
					default:
						return $reference->parentDocObj->debug ? 'ERROR (rlmp_officelib_oowritertctable): Unknown type '.$reference->type : '';
				}

			} elseif (is_a ($reference, 'rlmp_officelib_tclist')) {
				$attributes = $renderedAttributes ? ' style="'.$renderedAttributes.'"' : '';
				switch ($reference->type) {
					case 'ordered-list':
						return '<ol'.$attributes.'>'.$preRendered.'</ol>';
					case 'unordered-list':
						return '<ul'.$attributes.'>'.$preRendered.'</ul>';
					case 'list-item':
					case 'list-header':
						return '<li'.$attributes.'>'.$preRendered.'</li>';
					default:
						return $reference->parentDocObj->debug ? 'ERROR (rlmp_officelib_oowriterlistcomposite): Unknown type '.$reference->type : '';
				}
				break;

			} elseif (is_a ($reference, 'rlmp_officelib_tcindex')) {
				switch ($reference->type) {
					case 'toc':
						if (!$this->conf['renderNativeTOC']) return;
						break;
				}
				$attributes = $renderedAttributes ? ' style="'.$renderedAttributes.'"' : '';
				return $preRendered;

			} elseif (is_a ($reference, 'rlmp_officelib_tcfield')) {
				switch ($reference->type) {
					case 'date':
						$content = $reference->fixed ? $this->renderValue (array ('value'=>$reference->content, 'type'=>'date')) : strftime($this->conf['dateFormat']);
						break;
					case 'time':
						$content = $reference->fixed ? $this->renderValue (array ('value'=>$reference->content, 'type'=>'time')) : strftime($this->conf['timeFormat']);
						break;
					case 'user':
						$content = htmlspecialchars($reference->content);
						break;
					default:
						$content = 'UNKNOWN FIELD TYPE ('.$reference->type.')';
				}
				return $debugSpan[0].htmlspecialchars($content).$debugSpan[1];

			} elseif (is_a ($reference, 'rlmp_officelib_tcmeta')) {
				$content = $this->renderValue ($reference->parentDocObj->metaObj->getProperty($reference->content));
				return $debugSpan[0].$content.$debugSpan[1];

			} elseif (is_a ($reference, 'rlmp_officelib_tcvariable')) {
				$content = $reference->parentDocObj->variablesArr[$reference->content];
				return $debugSpan[0].htmlspecialchars($content).$debugSpan[1];

			} elseif (is_a ($reference, 'rlmp_officelib_tcbookmark')) {
				return '<a id="'.htmlspecialchars($reference->name).'"></a>';

			} elseif (is_a ($reference, 'rlmp_officelib_tcerror')) {
				return $reference->parentDocObj->debug ? '<p style="background-color: yellow; color:red;">'.$reference->content.'</p>' : '';

			} elseif (is_a ($reference, 'rlmp_officelib_textcomposite')) {
				$attributes = $renderedAttributes ? ' style="'.$renderedAttributes.'"' : '';
				return $preRendered;

			} else {
				return 'ERROR (rlmp_officelib_oowriterrenderhtml): Unknown text class: '.get_class($reference);
			}
		} else  { return 'ERROR (rlmp_officelib_oowriterrenderhtml): Object expected'; }
	}

	/**
	 * Translates the internal style attributes to CSS styles if available
	 *
	 * @param	array		$styleArr: The styles array. Only the properties section will be used.
	 * @return	string		A line of CSS definitions, ready to use in an HTML style attribute.
	 */
	function renderStyleAttributes ($styleArr) {
#debug ($styleArr,'renderStyleAttributes styleArr',__LINE__,__FILE__);

		if (intval ($this->conf['dontRenderAnyStyle'])) return '';

		$attributes = array ();
		if (is_array ($styleArr['properties'])) {
			foreach ($styleArr['properties'] as $key => $value) {
#debug (array ($key, $value), 'K=>V');
				switch ($key) {
					case 'FO:FONT-NAME':
							//  TODO! Should return the rendered attributes from the font declarations array.
						break;

						// Following a bunch of properties which have an exact pendant in CSS
					case 'FO:LETTER-SPACING':
					case 'FO:TEXT-INDENT':			// Cave: OOo relates percentage values to the parent style's indenting, not to the the surrounding elements
					case 'FO:FONT-STYLE':
					case 'FO:FONT-WEIGHT':
					case 'FO:FONT-VARIANT':
					case 'FO:TEXT-TRANSFORM':
					case 'FO:TEXT-ALIGN':
					case 'FO:LINE-HEIGHT':
					case 'FO:BORDER':
					case 'FO:BORDER-TOP':
					case 'FO:BORDER-BOTTOM':
					case 'FO:BORDER-LEFT':
					case 'FO:BORDER-RIGHT':
					case 'STYLE:TEXT-COMBINE':		// NOTE: This is CSS 3.0 !
					case 'STYLE:VERTICAL-ALIGN':
					case 'STYLE:WIDTH':
					case 'STYLE:HEIGHT':
						$tmp = explode (':',$key);
						$attributes[] = strtolower($tmp[1]).': '.$value.';';
						break;

					case 'FO:COLOR':
					case 'FO:BACKGROUND-COLOR':
						if (!intval ($this->conf['dontRenderColors'])) {
							$tmp = explode (':',$key);
							$attributes[] = strtolower($tmp[1]).': '.$value.';';
						}
						break;

					case 'FO:MARGIN-TOP':			// Cave: OOo relates percentage values to the parent style's margin, not to the the surrounding elements
					case 'FO:MARGIN-BOTTOM':		// Cave: OOo relates percentage values to the parent style's margin, not to the the surrounding elements
					case 'FO:MARGIN-LEFT':			// Cave: OOo relates percentage values to the parent style's margin, not to the the surrounding elements
					case 'FO:MARGIN-RIGHT':			// Cave: OOo relates percentage values to the parent style's margin, not to the the surrounding elements
						if (!intval ($this->conf['dontRenderMargins'])) {
							$tmp = explode (':',$key);
							$attributes[] = strtolower($tmp[1]).': '.$value.';';
						}
						break;

					case 'FO:PADDING':
					case 'FO:PADDING-TOP':
					case 'FO:PADDING-BOTTOM':
					case 'FO:PADDING-LEFT':
					case 'FO:PADDING-RIGHT':
						if (!intval ($this->conf['dontRenderPaddings'])) {
							$tmp = explode (':',$key);
							$attributes[] = strtolower($tmp[1]).': '.$value.';';
						}
						break;

					case 'FO:FONT-FAMILY':
							// TODO: Mapping (configuration via TS) of fonts for the web
						$attributes[] = 'font-family: '.$value.';';
						break;
					case 'FO:FONT-SIZE':
							// TODO: Mapping (configuration via TS) of fonts sizes to classes or different sizes
							// Cave: OOo relates percentage values to the parent style, not to the the surrounding elements
						if (!intval ($this->conf['dontRenderFontSizes'])) {
							$attributes[] = 'font-size: '.$value.';';
						}
						break;
					case 'STYLE:TEXT-SCALE':
					case 'FO:FONT-SIZE-REL':
						if (!intval ($this->conf['dontRenderFontSizes'])) {
							// TODO: Relative font-size relating to the parent style!
						}
						break;
					case 'STYLE:TEXT-BACKGROUND-COLOR':
						$attributes[] = 'background-color: '.$value.';';
						break;
					case 'STYLE:BACKGROUND-IMAGE':
							// TODO!
						break;
					case 'STYLE:TEXT-POSITION':
						if (stristr($value, 'sub') !== false) {
							$attributes[] = 'vertical-align: sub;';
							$attributes[] = 'font-size: 50%';
						} elseif (stristr($value, 'super') !== false) {
							$attributes[] = 'vertical-align: super;';
							$attributes[] = 'font-size: 50%';
						}
						break;
					case 'TEXT:UNDERLINE':
						if ($value == 'none') {
							$attributes[] = 'text-decoration: none;';
						} else {
							$attributes[] = 'text-decoration: underline;';
						}
						break;
					case 'STYLE:TEXT-CROSSING-OUT':
						if ($value == 'none') {
							$attributes[] = 'text-decoration: none;';
						} else {
							$attributes[] = 'text-decoration: line-through;';
						}
						break;
					case 'STYLE:TEXT-BLINKING':
						if ($value == 'none') {
							$attributes[] = 'text-decoration: none;';
						} else {
							$attributes[] = 'text-decoration: blink;';
						}
						break;
					case 'FO:BREAK-BEFORE':
					case 'FO:BREAK-AFTER':
					case 'STYLE:KEEP-WITH-NEXT':
							// TODO: Determine page breaks
						break;

					case 'STYLE:USE-WINDOW-FONT-COLOR':		// boolean
					case 'STYLE:TEXT-UNDERLINE-COLOR':
					case 'STYLE:TEXT-OUTLINE':				// boolean
					case 'STYLE:TEXT-EMPHASIZE':			// emphasize text in asian documents
					case 'STYLE:TEXT-COMBINE-START-CHAR':
					case 'STYLE:TEXT-COMBINE-END-CHAR':
					case 'STYLE:TEXT-AUTOSPACE':			// Automatically put space between asian and western and complex characters?
					case 'STYLE:LETTER-KERNING':
					case 'STYLE:LINE-BREAK':
					case 'STYLE:BREAK-INSIDE':
					case 'STYLE:LINE-HEIGHT-AT-LEAST':
					case 'STYLE:FONT-RELIEF':
					case 'STYLE:SHADOW':
					case 'STYLE:FONT-STYLE-NAME':
					case 'STYLE:FONT-PITCH':
					case 'STYLE:FONT-CHARSET':
					case 'STYLE:JUSTIFY-SINGLE-WORD':
					case 'STYLE:AUTO-TEXT-INDENT':

					case 'STYLE:TAB-STOPS':
					case 'STYLE:TAB-STOP':
					case 'STYLE:POSITION':
					case 'STYLE:TYPE':
					case 'STYLE:CHAR':
					case 'STYLE:LEADER-CHAR':

					case 'STYLE:DROP-CAP':					// Bigger capital at beginning of a line

					case 'FO:HYPHENATE':
					case 'FO:HYPHENATION-KEEP':
					case 'FO:HYPHENATION-REMAIN-CHAR-COUNT':
					case 'FO:HYPHENATION-PUSH-CHAR-COUNT':
					case 'FO:HYPHENATION-LADDER-COUNT':

					case 'FO:WIDOWS':						// Minimum number of lines at top of a page to prevent paragraph widows
					case 'FO:ORPHANS':						// Minimum number of lines at end of a page to prevent paragraph orphans
					case 'FO:TEXT-ALIGN-LAST':
					case 'FO:TEXT-SHADOW':
					case 'FO:SCORE-SPACES':					// If spaces should be underlined or crossed out
					case 'FO:FONT-FAMILY-GENERIC':
					case 'FO:LANGUAGE':
					case 'FO:COUNTRY':
						// These properties will not be rendered
						break;
				}
			}
		}
		return trim (implode (' ', $attributes));
	}

	/**
	 * Renders a value (fx. from the meta object) and takes it's type into account (string, date ...)
	 *
	 * @param	array		$valueType: The value and the type (string|integer|date|time)
	 * @return	string		HTML output
	 * @access	public
	 */
	function renderValue ($valueType) {
		switch ($valueType['type']) {
			case 'string':
				return htmlspecialchars ($valueType['value']);
			break;
			case 'integer':
				return intval ($valueType['value']);
			break;
			case 'date':
				return strftime($this->conf['dateFormat'], $valueType['value']);
			break;
			case 'time':
				return strftime($this->conf['timeFormat'], $valueType['value']);
			break;
			default:
				return htmlspecialchars ($valueType['value']);
		}
	}
}

?>