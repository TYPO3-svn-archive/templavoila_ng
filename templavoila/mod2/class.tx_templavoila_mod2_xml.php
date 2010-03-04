<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2006 Kasper Sk?rh?j <kasper@typo3.com>
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
 * Submodule 'xml' for the templavoila control-center module
 *
 * @author   Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */

/**
 * Submodule 'xml' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod2_xml {


	// References to the control-center module object
	var $pObj;	// A pointer to the parent object, that is the templavoila control-center module script. Set by calling the method init() of this class.
	var $doc;	// A reference to the doc object of the parent object.


	/**
	 * Initializes the xml object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila control-center module.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	void
	 * @access	public
	 */
	function init(&$pObj) {
		// Make local reference to some important variables:
		$this->pObj = &$pObj;
		$this->doc = &$this->pObj->doc;
		$this->extKey = &$this->pObj->extKey;
		$this->MOD_SETTINGS = &$this->pObj->MOD_SETTINGS;
	}


	/******************************
	 *
	 * XML analysis and synthesis
	 * may be used for DS-xml and TO-xml alike
	 *
	 *****************************/


	/**
	 * Shows a graphical summary of a array-tree, which suppose was a XML
	 * (but don't need to). This function works recursively.
	 *
	 * @param	[type]		$XMLtree: an array holding the T3DataStructure
	 * @return	[type]		HTML showing an overview of the T3DataStructure
	 */
	function renderXMLdetails($XMLtree) {
		$HTML = '';

		if (is_array($XMLtree) && (count($XMLtree) > 0)) {
			$HTML .= '<dl class="DS-details">';

			foreach ($XMLtree as $elm => $def) {
				$HTML .= '<dt>';
				$HTML .= ($elm == "meta"
					? $GLOBALS['LANG']->getLL('center_details_conf')
					: $def['tx_templavoila']['title'] . ' (' . $elm . ')');
				$HTML .= '</dt>';
				$HTML .= '<dd>';

				/* this is the configuration-entry ------------------------------ */
				if ($elm == "meta") {
					/* The basic XML-structure of an meta-entry is:
					 *
					 * <meta>
					 * 	<langDisable>		-> no localization
					 * 	<langChildren>		-> no localization for children
					 * 	<sheetSelector>		-> a php-function for selecting "sDef"
					 * </meta>
					 */

					/* it would also be possible to use the 'list-style-image'-property
					 * for the flags, which would be more sensible to IE-bugs though
					 */
					$conf = '';
					if (isset($def['langDisable'])) $conf .= '<li>' .
						(($def['langDisable'] == 1)
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_loc0')
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif',        'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_loc1')
					) . '</li>';
					if (isset($def['langChildren'])) $conf .= '<li>' .
						(($def['langChildren'] == 1)
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif',        'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_locc1')
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_locc0')
					) . '</li>';
					if (isset($def['disableDataPreview'])) $conf .= '<li>' .
						(($def['disableDataPreview'] != '')
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif',        'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_preview1')
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_preview0')
					) . '</li>';
					if (isset($def['sheetSelector'])) $conf .= '<li>' .
						(($def['sheetSelector'] != '')
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif',        'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_sheet1') . ' [<em>' . $def['sheetSelector'] . '</em>]'
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_sheet0')
					) . '</li>';
					if (isset($def['noEditOnCreation'])) $conf .= '<li>' .
						(($def['noEditOnCreation'] != '')
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif',        'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_edit1') . ' [<em>' . $def['noEditOnCreation'] . '</em>]'
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_edit0')
					) . '</li>';

					if ($conf != '')
						$HTML .= '<ul class="DS-config">' . $conf . '</ul>';
				}
				/* this a container for repetitive elements --------------------- */
				else if (isset($def['section']) && ($def['section'] == 1)) {
					$HTML .= '<p>[..., ..., ...]</p>';
				}
				/* this a container for cellections of elements ----------------- */
				else if (isset($def['type']) && ($def['type'] == "array")) {
					$HTML .= '<p>[...]</p>';
				}
				/* this a regular entry ----------------------------------------- */
				else {
					/* The basic XML-structure of an entry is:
					 *
					 * <element>
					 * 	<tx_templavoila>	-> entries with informational character belonging to this entry
					 * 	<TCEforms>		-> entries being used for TCE-construction
					 * 	<type + el + section>	-> subsequent hierarchical construction
					 *	<langOverlayMode>	-> ??? (is it the language-key?)
					 * </element>
					 */
					if (($tv = $def['tx_templavoila'])) {
						/* The basic XML-structure of an tx_templavoila-entry is:
						 *
						 * <tx_templavoila>
						 * 	<title>			-> Human readable title of the element
						 * 	<description>		-> A description explaining the elements function
						 * 	<sample_data>		-> Some sample-data (can't contain HTML)
						 * 	<eType>			-> The preset-type of the element, used to switch use/content of TCEforms/TypoScriptObjPath
						 * 	<oldStyleColumnNumber>	-> for distributing the fields across the tt_content column-positions
						 * 	<proc>			-> define post-processes for this element's value
						 *		<int>		-> this element's value will be cast to an integer (if exist)
						 *		<HSC>		-> this element's value will convert special chars to HTML-entities (if exist)
						 *		<stdWrap>	-> an implicit stdWrap for this element, "stdWrap { ...inside... }"
						 * 	</proc>
						 *	<TypoScript_constants>	-> an array of constants that will be substituted in the <TypoScript>-element
						 * 	<TypoScript>		->
						 * 	<TypoScriptObjPath>	->
						 * </tx_templavoila>
						 */

						if (isset($tv['description']) && ($tv['description'] != ''))
							$HTML .= '<p>"' . $tv['description'] . '"</p>';

						/* it would also be possible to use the 'list-style-image'-property
						 * for the flags, which would be more sensible to IE-bugs though
						 */
						$proc = '';
						if (isset($tv['proc']) && isset($tv['proc']['int'])) $proc .= '<li>' .
							(($tv['proc']['int'] == 1)
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif',        'width="18" height="16"').' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_integer0')
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"').' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_integer1')
						) . '</li>';
						if (isset($tv['proc']) && isset($tv['proc']['HSC'])) $proc .= '<li>' .
							(($tv['proc']['HSC'] == 1)
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif',        'width="18" height="16"').' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_hsc0') . ' [' . $GLOBALS['LANG']->getLL('center_details_hsc_on' ) . ']'
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"').' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_hsc1') . ' [' . $GLOBALS['LANG']->getLL('center_details_hsc_off') . ']'
						) . '</li>';
						if (isset($tv['proc']) && isset($tv['proc']['stdWrap'])) $proc .= '<li>' .
							(($tv['proc']['stdWrap'] != '')
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif',        'width="18" height="16"').' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_wrap0')
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"').' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_details_wrap1')
						) . '</li>';

						if ($proc != '')
							$HTML .= '<ul class="DS-proc">' . $proc . '</ul>';

						switch ($tv['eType']) {
							case "input":
							case "input_h":
							case "input_g":
							case "text":
							case "rte":
							case "link":
							case "int":
							case "image":
							case "imagefixed":
							case "imagelist":
							case "select":
								$preset = sprintf($GLOBALS['LANG']->getLL('center_details_preset_' + $tv['eType']), $tv['eType']);
								$tco = false;
								break;
							case "ce":
							case "TypoScriptObject":

							case "none":
							default:
								$preset = sprintf($GLOBALS['LANG']->getLL('center_details_preset_' + $tv['eType']), $tv['eType']);
								$tco = true;
								break;
						}

						$column = sprintf($GLOBALS['LANG']->getLL('center_details_column_' + $tv['oldStyleColumnNumber']), $tv['oldStyleColumnNumber']);

						$notes = '';
						if (($tv['eType'] == "TypoScriptObject") && isset($tv['TypoScript']))
							$notes .= '<li>' . $GLOBALS['LANG']->getLL('center_details_ts_redundant') . '</li>';
						if ((($tv['eType'] == "TypoScriptObject") || !isset($tv['TypoScript'])) && isset($tv['TypoScript_constants']))
							$notes .= '<li>' . $GLOBALS['LANG']->getLL('center_details_ts_redundantconst') . '</li>';
						if (($tv['eType'] != "TypoScriptObject") && isset($tv['TypoScriptObjPath']))
							$notes .= '<li>' . $GLOBALS['LANG']->getLL('center_details_ts_redundantpath') . '</li>';
						if (isset($tv['TypoScriptObjPath']) && preg_match('/[^a-zA-Z0-9\.\:_]/', $tv['TypoScriptObjPath']))
							$notes .= '<li><strong>' . $GLOBALS['LANG']->getLL('center_details_ts_illegalpath') . '</strong></li>';
						if (isset($tv['proc']) && isset($tv['proc']['int']) && ($tv['proc']['int'] == 1) && isset($tv['proc']['HSC']))
							$notes .= '<li>' . $GLOBALS['LANG']->getLL('center_details_hcs_redundant') . '</li>';

						$tsstats = '';
						if (isset($tv['TypoScript']))
							$tsstats .= '<li>' . sprintf($GLOBALS['LANG']->getLL('center_details_ts_stats'), (1 + strlen($tv['TypoScript']) - strlen(str_replace("\n", "", $tv['TypoScript'])))) . '</li>';
						if (isset($tv['TypoScript_constants']))
							$tsstats .= '<li>' . sprintf($GLOBALS['LANG']->getLL('center_details_ts_statsconst'), count($tv['TypoScript_constants'])) . '</li>';
						if (isset($tv['TypoScriptObjPath']))
							$tsstats .= '<li>' . sprintf($GLOBALS['LANG']->getLL('center_details_ts_statspath'), '<em>' . $tv['TypoScriptObjPath'] . '</em>') . '</li>';

						$HTML .= '<dl class="DS-infos">';
						$HTML .= '<dt>' . $GLOBALS['LANG']->getLL('center_details_preset') . ':</dt>';
						$HTML .= '<dd>' . $preset . '</dd>';
						$HTML .= '<dt>' . $GLOBALS['LANG']->getLL('center_details_column') . ':</dt>';
						$HTML .= '<dd>' . $column . '</dd>';
						if ($tsstats != '') {
							$HTML .= '<dt>' . $GLOBALS['LANG']->getLL('center_details_ts') . ':</dt>';
							$HTML .= '<dd><ul class="DS-stats">' . $tsstats . '</ul></dd>';
						}
						if ($notes != '') {
							$HTML .= '<dt>' . $GLOBALS['LANG']->getLL('center_details_notes') . ':</dt>';
							$HTML .= '<dd><ul class="DS-notes">' . $notes . '</ul></dd>';
						}
						$HTML .= '</dl>';
					}
					else {
						$HTML .= '<p>' . $GLOBALS['LANG']->getLL('center_details_notv') . '</p>';
					}

					if (($tf = $def['TCEforms'])) {
						/* The basic XML-structure of an TCEforms-entry is:
						 *
						 * <TCEforms>
						 * 	<label>			-> TCE-label for the BE
						 * 	<config>		-> TCE-configuration array
						 * </TCEforms>
						 */
					}
					else if (!$tco) {
						$HTML .= '<p>' . $GLOBALS['LANG']->getLL('center_details_notce') . '</p>';
					}
				}

				/* there are some childs to process ----------------------------- */
				if (isset($def['type']) && ($def['type'] == "array")) {
					if (isset($def['section']))
						;
					if (isset($def['el']))
						$HTML .= $this->renderXMLdetails($def['el']);
				}

				$HTML .= '</dd>';
			}

			$HTML .= '</dl>';
		}
		else
			$HTML .= '<p>
					<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_warning2.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" />
					' . $GLOBALS['LANG']->getLL('center_details_nochild') . '
				</p>';

		return $HTML;
	}

	/**
	 * Show meta data part of Data Structure
	 *
	 * @param	[type]		$XMLstring: ...
	 * @return	[type]		...
	 */
	function getXMLdetails($XMLstring) {
		$XMLcontent = t3lib_div::xml2array($XMLstring);

		$inputFields     = 0;
		$referenceFields = 0;
		$rootElements    = 0;

		if (is_array ($XMLcontent) && is_array($XMLcontent['ROOT']['el'])) {
			foreach($XMLcontent['ROOT']['el'] as $elKey => $elCfg) {

				if (isset($elCfg['TCEforms']))	{
					// Assuming that a reference field for content elements is recognized like this, increment counter. Otherwise assume input field of some sort.
					if ($elCfg['TCEforms']['config']['type'] === 'group' && $elCfg['TCEforms']['config']['allowed'] === 'tt_content')	{
						$referenceFields++;
					} else {
						$inputFields++;
					}
				}

				if (isset($elCfg['el']))
					$elCfg['el'] = '...';

				unset($elCfg['tx_templavoila']['sample_data']);
				unset($elCfg['tx_templavoila']['tags']);
				unset($elCfg['tx_templavoila']['eType']);

				$rootElementsHTML .= '<b>' . $elCfg['tx_templavoila']['title'] . '</b>' . t3lib_div::view_array($elCfg);
				$rootElements++;
			}
		}

	/*	$XMLcontent = array('meta' => $XMLcontent['meta']);	*/

		$languageMode = '';
		$layoutMode = $GLOBALS['LANG']->getLL('center_layout_auto');
		$sheetMode = $GLOBALS['LANG']->getLL('center_sheet_auto');

		if (is_array($XMLcontent['meta'])) {
			if ($XMLcontent['meta']['langDisable']) {
				$languageMode = $GLOBALS['LANG']->getLL('disabled');
			} elseif ($XMLcontent['meta']['langChildren']) {
				$languageMode = $GLOBALS['LANG']->getLL('inherited');
			} else {
				$languageMode = $GLOBALS['LANG']->getLL('separated');
			}

			if (trim($XMLcontent['meta']['sheetSelector'])) {
				$sheetMode = sprintf($GLOBALS['LANG']->getLL('center_sheet_sel'), $XMLcontent['meta']['sheetSelector']);
			}

			if (trim($XMLcontent['meta']['beLayout'])) {
				$layoutMode = $GLOBALS['LANG']->getLL('center_layout_becode');
			}
			else if (trim($XMLcontent['meta']['beLayoutFile'])) {
				$layoutMode = sprintf($GLOBALS['LANG']->getLL('center_layout_befile'), renderFile_link($XMLcontent['meta']['beLayoutFile']));
			}
		}

		if ($referenceFields) {
			$containerMode = $GLOBALS['LANG']->getLL('yes');

			if ($languageMode === $GLOBALS['LANG']->getLL('separated')) {
				$containerMode .= ' ' . $this->doc->icons(3) . $GLOBALS['LANG']->getLL('center_refs_sep');
			} else if ($languageMode === $GLOBALS['LANG']->getLL('inherited')) {
				$containerMode .= ' ' . $this->doc->icons(2);
				if ($inputFields) {
					$containerMode .= $GLOBALS['LANG']->getLL('center_refs_inp');
				} else {
					$containerMode .= htmlspecialchars($GLOBALS['LANG']->getLL('center_refs_no'));
				}
			}
		} else {
			$containerMode = $GLOBALS['LANG']->getLL('no');
		}

		return array(/*t3lib_div::view_array($XMLcontent).'Language Mode => "'.$languageMode.'"<hr/>
						Root Elements = ' . $rootElements . ', hereof ref/input fields = '.($referenceFields.'/'.$inputFields).'<hr/>
						'.$rootElementsHTML*/
			'HTML'   => $this->renderXMLdetails($XMLcontent),
			'status' => $containerMode,
			'stats'  => array(
				'rootElements'    => $rootElements,
				'referenceFields' => $referenceFields,
				'inputFields'     => $inputFields,
				'languageMode'    => $languageMode,
				'layoutMode'      => $layoutMode,
				'sheetMode'       => $sheetMode
			)
		);
	}

	/**
	 * Renders the name and a link of a given template-file
	 *
	 * @param	string		The file-path
	 * @return	the dl-fragment with the informations and icons
	 */
	function renderFile_link($theFile) {
		// Find the file:
		$theFile = t3lib_div::getFileAbsFileName($theFile, 1);
		if ($theFile && @is_file($theFile)) {
			preg_match_all('/(.*)\\.([^\\.]*$)/',  $theFile, $reg);
			$alttext = '' . $reg[2][0];

			$icon = t3lib_BEfunc::getFileIcon($alttext);
			$icon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/fileicons/' . $icon, 'width="18" height="16"') . ' align="top" title="' . htmlspecialchars($alttext) . '" alt="" />';

			$relFilePath = substr($theFile,strlen(PATH_site));
			$onCl = 'return top.openUrlInWindow(\'' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $relFilePath.'\', \'FileView\');';

			return $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($icon, $theFile) . ' <a href="#" onclick="' . htmlspecialchars($onCl) . '">' . htmlspecialchars($relFilePath) . '</a>';
		}

		return '';
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_xml.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_xml.php']);
}

?>