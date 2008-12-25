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
 * Submodule 'ds' for the templavoila control-center module
 *
 * @author   Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor Niels Fr�hling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */

/**
 * Submodule 'ds' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fr�hling <niels@frohling.biz>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod2_ds {


	// References to the control-center module object
	var $pObj;	// A pointer to the parent object, that is the templavoila control-center module script. Set by calling the method init() of this class.
	var $doc;	// A reference to the doc object of the parent object.


	/**
	 * Initializes the ds object. The calling class must make sure that the right locallang files are already loaded.
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
	 * DS information retrieval and rendering
	 *
	 *****************************/


	/**
	 * Rendering a single data structures information
	 *
	 * @param	array		Data Structure information
	 * @param	array		Array with TO found for this ds
	 * @param	integer		Scope.
	 * @return	string		HTML content
	 */
	function renderDSDisplay($dsRow, $toIdArray, $scope) {
		global $BE_USER;

		$tableAttribs = ' border="0" cellpadding="1" cellspacing="1" width="98%" class="lrPadding"';

		$XMLinfo = array();
		$dsID = $dsRow['_STATIC'] ? $dsRow['path'] : $dsRow['uid'];

		// If ds was a true record:
		if (!$dsRow['_STATIC']) {
			// Record icon:
			// Put together the records icon including content sensitive menu link wrapped around it:
			$recordIcon = t3lib_iconWorks::getIconImage('tx_templavoila_datastructure', $dsRow, $this->doc->backPath, 'class="absmiddle"');
			$recordIcon = $this->doc->wrapClickMenuOnIcon($recordIcon, 'tx_templavoila_datastructure', $dsRow['uid'], 1, '&callingScriptId='.rawurlencode($this->doc->scriptID));

			// Preview icon:
			if ($dsRow['previewicon']) {
				$icon = '<img src="' . $this->doc->backPath . '../uploads/tx_templavoila/' . $dsRow['previewicon'] . '" alt="" />';
			} else {
				$icon = '[' . $GLOBALS['LANG']->getLL('noicon') . ']';
			}

			// Template status / link:
			$linkUrl = $this->pObj->cm1Script . 'id=' . $this->pObj->id.'&table=tx_templavoila_datastructure&uid='.$dsRow['uid'].'&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
			$templateStatus  = $this->findDSUsageWithImproperTOs($dsID, $toIdArray, $scope);
			$templateStatus .= '<br/><a href="'.htmlspecialchars($linkUrl).'">[ '.$GLOBALS['LANG']->getLL('center_view_ds').' ]</a>';

			// Links:
			if ($BE_USER->check('tables_modify', 'tx_templavoila_datastructure')) {
				$lpXML = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_datastructure][' . $dsRow['uid'].']=edit&columnsOnly=dataprot', $this->doc->backPath)) . '">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit') . '" alt="" class="absmiddle" />' .
						'</a>';
				$editLink = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_datastructure][' . $dsRow['uid'] . ']=delete', $this->doc->backPath)).'">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/garbage.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit') . '" alt="" class="absmiddle" style="float: right;" />' .
						'</a>';
				$editLink .= '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_datastructure][' . $dsRow['uid'].']=edit', $this->doc->backPath)).'">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit') . '" alt="" class="absmiddle" style="float: right;" />' .
						'</a>';
				$dsTitle = '<a href="' . htmlspecialchars($linkUrl) . '" title="' . $GLOBALS['LANG']->getLL('center_view_ds') . '">' . htmlspecialchars($dsRow['title']) . '</a>';
			} else {
				$lpXML = '';
				$editLink = '';
				$dsTitle = htmlspecialchars($dsRow['title']);
			}

			// Format XML if requested (renders VERY VERY slow)
			if ($this->MOD_SETTINGS['set_details'])	{
				$lpXML .= '';

				if ($dsRow['dataprot'] && $this->MOD_SETTINGS['set_showDSxml']) {
					require_once(PATH_t3lib.'class.t3lib_syntaxhl.php');
					$hlObj = t3lib_div::makeInstance('t3lib_syntaxhl');
					$lpXML .= '<pre>'.str_replace(chr(9),'&nbsp;&nbsp;&nbsp;',$hlObj->highLight_DS($dsRow['dataprot'])).'</pre>';
				}
			}

			$lpXML .= $dsRow['dataprot']
				? t3lib_div::formatSize(strlen($dsRow['dataprot'])).'bytes'
				: '&mdash;';

			// Details:
			if ($this->MOD_SETTINGS['set_details'])	{
				$XMLinfo = $this->pObj->xmlObj->DSdetails($dsRow['dataprot']);
			}

			// Compile info table:
			$content .= '
			<table' . $tableAttribs . '>
				<tr class="bgColor5">
					<td colspan="3" style="border-top: 1px solid black;">'.
						$recordIcon.
						$dsTitle.
						$editLink.
						'</td>
				</tr>
				<tr class="bgColor4">
					<td rowspan="'.($this->MOD_SETTINGS['set_details'] ? 4 : 2).'" style="width: 100px; text-align: center;">'.$icon.'</td>
					<td>'.$GLOBALS['LANG']->getLL('center_view_tmplstatus').':</td>
					<td>'.$templateStatus.'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$GLOBALS['LANG']->getLL('center_view_globalproc').'&nbsp;<strong>XML</strong>:</td>
					<td>' . $lpXML . ($this->MOD_SETTINGS['set_details'] ? '<hr />' . $XMLinfo['HTML'] : '') . '</td>
				</tr>'.($this->MOD_SETTINGS['set_details'] ? '
				<tr class="bgColor4">
					<td>'.$GLOBALS['LANG']->getLL('created').':</td>
					<td>'.t3lib_BEfunc::datetime($dsRow['crdate']).' '.$GLOBALS['LANG']->getLL('by').' ['.$dsRow['cruser_id'].']</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$GLOBALS['LANG']->getLL('updated').':</td>
					<td>'.t3lib_BEfunc::datetime($dsRow['tstamp']).'</td>
				</tr>' : '').'
			</table>';
		}
		else {	// DS was a file:

			// XML file icon:
			$recordIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/fileicons/xml.gif','width="18" height="16"').' alt="" class="absmiddle" />';

			// Preview icon:
			if ($dsRow['icon'] && $iconPath = t3lib_div::getFileAbsFileName($dsRow['icon']))	{
				$icon = '<img src="'.$this->doc->backPath.'../'.substr($iconPath,strlen(PATH_site)).'" alt="" />';
			} else {
				$icon = '['.$GLOBALS['LANG']->getLL('noicon').']';
			}

			$fileReference = t3lib_div::getFileAbsFileName($dsRow['path']);
			if (@is_file($fileReference))	{
				$fileRef = '<a href="'.htmlspecialchars($this->doc->backPath.'../'.substr($fileReference,strlen(PATH_site))).'" target="_blank">'.
							htmlspecialchars($dsRow['path']).
							'</a>';

				if ($this->MOD_SETTINGS['set_details'])	{
					$XMLinfo = $this->pObj->xmlObj->DSdetails(t3lib_div::getUrl($fileReference));
				}
			} else {
				$fileRef = htmlspecialchars($dsRow['path']).' ['.$GLOBALS['LANG']->getLL('filenotfound').'!]';
			}

			$dsRecTitle = (substr($dsRow['title'], 0, 4) == 'LLL:' ? $GLOBALS['LANG']->sL($dsRow['title']) : $dsRow['title']);

			// Compile table:
			$content.='
			<table'.$tableAttribs.'>
				<tr class="bgColor2">
					<td colspan="3" style="border-top: 1px solid black;">'.
						$recordIcon.
						htmlspecialchars($dsRecTitle).
						'</td>
				</tr>
				<tr class="bgColor4">
					<td rowspan="'.($this->MOD_SETTINGS['set_details'] ? 2 : 1).'" style="width: 100px; text-align: center;">'.$icon.'</td>
					<td>XML '.$GLOBALS['LANG']->getLL('file').':</td>
					<td>'.$fileRef.
						($this->MOD_SETTINGS['set_details'] ? '<hr/>'.$XMLinfo['HTML'] : '').'</td>
				</tr>'.($this->MOD_SETTINGS['set_details'] ? '
				<tr class="bgColor4">
					<td>'.$GLOBALS['LANG']->getLL('center_view_tmplstatus').':</td>
					<td>'.$this->findDSUsageWithImproperTOs($dsID, $toIdArray, $scope).'</td>
				</tr>' : '').'
			</table>';
		}

		if ($this->MOD_SETTINGS['set_details'])	{
			if ($XMLinfo['referenceFields']) {
				$containerMode = $GLOBALS['LANG']->getLL('yes');
				if ($XMLinfo['languageMode'] === 'Separate') {
					$containerMode .= ' ' . $this->doc->icons(3) . $GLOBALS['LANG']->getLL('center_refs_sep');
				} elseif ($XMLinfo['languageMode'] === 'Inheritance') {
					$containerMode .= ' ' . $this->doc->icons(2);
					if ($XMLinfo['inputFields']) {
						$containerMode .= $GLOBALS['LANG']->getLL('center_refs_inp');
					} else {
						$containerMode .= htmlspecialchars($GLOBALS['LANG']->getLL('center_refs_no'));
					}
				}
			} else {
				$containerMode = $GLOBALS['LANG']->getLL('no');
			}

			$containerMode.=' (ARI='.$XMLinfo['rootelements'].'/'.$XMLinfo['referenceFields'].'/'.$XMLinfo['inputFields'].')';
		}

		// Return content
		return array(
			'HTML' => $content,
			'languageMode' => $XMLinfo['languageMode'],
			'container' => $containerMode
		);
	}

	/**
	 * Creates listings of pages / content elements where NO or WRONG template objects are used.
	 *
	 * @param	array		Data Structure ID
	 * @param	array		Array with numerical toIDs. Must be integers and never be empty. You can always put in "-1" as dummy element.
	 * @param	integer		Scope value. 1) page,  2) content elements
	 * @return	string		HTML table listing usages.
	 */
	function findDSUsageWithImproperTOs($dsID, $toIdArray, $scope)	{
		$output = array();

		switch (intval($scope))	{
			// Pages
			case TVDS_SCOPE_PAGE:
				// Header:
				$output[]='
							<tr class="bgColor5 tableheader">
								<td>Title:</td>
								<td>Path:</td>
							</tr>';

				// Main templates:
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid,title,pid',
					'pages',
					'(
						(tx_templavoila_to NOT IN ('.implode(',',$toIdArray).') AND tx_templavoila_ds='.$GLOBALS['TYPO3_DB']->fullQuoteStr($dsID,'pages').') OR
						(tx_templavoila_next_to NOT IN ('.implode(',',$toIdArray).') AND tx_templavoila_next_ds='.$GLOBALS['TYPO3_DB']->fullQuoteStr($dsID,'pages').')
					)'.
						t3lib_BEfunc::deleteClause('pages')
				);

				while (false !== ($pRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
					$path = $this->pObj->findRecordsWhereUsed_pid($pRow['uid']);
					if ($path) {
						$output[] = '
							<tr class="bgColor4-20">
								<td nowrap="nowrap">'.
									'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[pages]['.$pRow['uid'].']=edit',$this->doc->backPath)).'">'.
									htmlspecialchars($pRow['title']).
									'</a></td>
								<td nowrap="nowrap">'.
									'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($pRow['uid'],$this->doc->backPath).'return false;').'">'.
									htmlspecialchars($path).
									'</a></td>
							</tr>';
					} else {
						$output[] = '
							<tr class="bgColor4-20">
								<td><em>No access</em></td>
								<td>-</td>
							</tr>';
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($res);
				break;

			case TVDS_SCOPE_FCE:
				// Select Flexible Content Elements:
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid,header,pid',
					'tt_content',
					'CType='.$GLOBALS['TYPO3_DB']->fullQuoteStr('templavoila_pi1','tt_content').
						' AND tx_templavoila_to NOT IN ('.implode(',',$toIdArray).')'.
						' AND tx_templavoila_ds='.$GLOBALS['TYPO3_DB']->fullQuoteStr($dsID,'tt_content').
						t3lib_BEfunc::deleteClause('tt_content'),
					'',
					'pid'
				);

				// Header:
				$output[] = '
							<tr class="bgColor5 tableheader">
								<td>Header:</td>
								<td>Path:</td>
							</tr>';

				// Elements:
				while (false !== ($pRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
					$path = $this->pObj->findRecordsWhereUsed_pid($pRow['pid']);
					if ($path)	{
						$output[]='
							<tr class="bgColor4-20">
								<td nowrap="nowrap">'.
									'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tt_content]['.$pRow['uid'].']=edit',$this->doc->backPath)).'" title="Edit">'.
									htmlspecialchars($pRow['header']).
									'</a></td>
								<td nowrap="nowrap">'.
									'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($pRow['pid'],$this->doc->backPath).'return false;').'" title="View page">'.
									htmlspecialchars($path).
									'</a></td>
							</tr>';
					} else {
						$output[]='
							<tr class="bgColor4-20">
								<td><em>No access</em></td>
								<td>-</td>
							</tr>';
					}
				}

				$GLOBALS['TYPO3_DB']->sql_free_result($res);
				break;
		}

		// Create final output table:
		if (count($output)) {
			if (count($output) > 1) {
				$outputString = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" />'.
					'Invalid template objects (TOs) on ' . (count($output) - 1) . ' ' .
						($scope == TVDS_SCOPE_OTHER ? 'other elements' :
						($scope == TVDS_SCOPE_PAGE  ? 'pages' :
						($scope == TVDS_SCOPE_FCE   ? 'content elements' :
						                              'plugin elements'))) .
					':';
				$this->pObj->setErrorLog($scope,'fatal',$outputString);

				$outputString.='<table border="0" cellspacing="1" cellpadding="1" class="lrPadding">' . implode('', $output) . '</table>';
			} else {
				$outputString = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('noerrors');
			}
		}

		return $outputString;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_ds.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_ds.php']);
}

?>