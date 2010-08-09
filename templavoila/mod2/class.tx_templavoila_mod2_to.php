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
 * Submodule 'to' for the templavoila control-center module
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
 * Submodule 'to' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod2_to {


	// References to the control-center module object
	var $pObj;	// A pointer to the parent object, that is the templavoila control-center module script. Set by calling the method init() of this class.
	var $doc;	// A reference to the doc object of the parent object.
	var $modifiable;


	/**
	 * Initializes the to object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila control-center module.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	void
	 * @access public
	 */
	function init(&$pObj) {
		global $BE_USER;

		// Make local reference to some important variables:
		$this->pObj = &$pObj;
		$this->doc = &$this->pObj->doc;
		$this->extKey = &$this->pObj->extKey;
		$this->modTSconfig = &$this->pObj->modTSconfig;
		$this->MOD_SETTINGS = &$this->pObj->MOD_SETTINGS;

		// Module may be allowed, but modify may not
		$this->modifiable = $BE_USER->check('tables_modify', 'tx_templavoila_tmplobj');
	}


	/******************************
	 *
	 * TO helpers
	 *
	 *****************************/


	function isModifiable() {
		return $this->modifiable;
	}


	/**
	 * Collects all TO-records in a given SysFolder
	 *
	 * @param	integer		SysFolder
	 * @return	array		DS-rows
	 */
	function findRecords($pid) {

		$toRecords = array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'cruser_id, crdate, tstamp, uid, title, parent, fileref, sys_language_uid, datastructure, rendertype, localprocessing, previewicon, description, fileref_mtime, fileref_md5',
			'tx_templavoila_tmplobj',
			'pid = ' . intval($pid) . t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj'),
			'',
			'title'
		);

		while($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			t3lib_BEfunc::workspaceOL('tx_templavoila_tmplobj',$row);
			$toRecords[$row['parent']][] = $row;
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $toRecords;
	}


	/******************************
	 *
	 * TO information retrieval and rendering
	 *
	 *****************************/

	var $tFileList = array();


	/**
	 * Render display of a Template Object
	 *
	 * @param	array		Template Object record to render
	 * @param	array		Array of all Template Objects (passed by reference. From here records are unset)
	 * @param	integer		Scope of DS
	 * @param	boolean		If set, the function is asked to render children to template objects (and should not call it self recursively again).
	 * @return	string		HTML content
	 */
	function renderTODisplay($toRow, &$toRecords, $scope, $children = 0) {

		$collapseIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/ol/minusonly.gif', 'width="11" height="12"') . ' alt="" class="absmiddle" />';

		// Put together the records icon including content sensitive menu link wrapped around it:
		$recordIcon = t3lib_iconWorks::getIconImage('tx_templavoila_tmplobj', $toRow, $this->doc->backPath, 'class="absmiddle"');
		$recordIcon = $this->doc->wrapClickMenuOnIcon($recordIcon, 'tx_templavoila_tmplobj', $toRow['uid'], 1, '&callingScriptId=' . rawurlencode($this->doc->scriptID));

		// Preview icon:
		if ($toRow['previewicon']) {
			if (isset($this->modTSconfig['properties']['toPreviewIconThumb']) && $this->modTSconfig['properties']['toPreviewIconThumb'] != '0') {
				$icon = t3lib_BEfunc::getThumbNail($this->doc->backPath . 'thumbs.php', PATH_site . 'uploads/tx_templavoila/' . $toRow['previewicon'],
					'hspace="5" vspace="5" border="1"',
					strpos($this->modTSconfig['properties']['toPreviewIconThumb'], 'x') ? $this->modTSconfig['properties']['toPreviewIconThumb'] : '');
			} else {
				$icon = '<img src="' . $this->doc->backPath . '../uploads/tx_templavoila/' . $toRow['previewicon'] . '" alt="" />';
			}
		} else {
			$icon = '[' . $GLOBALS['LANG']->getLL('noicon') . ']';
		}

		// Mapping status / link:
		$linkUrl = $this->pObj->cm1Script . 'id=' . $this->pObj->id . '&table=tx_templavoila_tmplobj&uid=' . $toRow['uid'] . '&_reload_from=1&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));

		/* ------------------------------------------------------------------------------ */
		$fileReference = t3lib_div::getFileAbsFileName($toRow['fileref']);
		if (@is_file($fileReference)) {
			$this->tFileList[$fileReference]++;

			$fileRef = '<a href="' . htmlspecialchars($this->doc->backPath . '../' . substr($fileReference, strlen(PATH_site))) . '" target="_blank">' . htmlspecialchars($toRow['fileref']) . '</a>';
			$fileMsg = '';
			$fileMtime = filemtime($fileReference);
		} else {
			$fileRef = htmlspecialchars($toRow['fileref']);
			$fileMsg = '<div class="typo3-red">' . $GLOBALS['LANG']->getLL('filenotfound') . '</div>';
			$fileMtime = 0;
		}

		/* ------------------------------------------------------------------------------ */
		$mappingStatusError   = 0;
		$mappingStatusIcon    = '';
		$mappingStatusTitle   = '';
		$mappingStatusMessage = '';
		$mappingStatusActions = '';

		if ($fileMtime && $toRow['fileref_mtime']) {
			if ($toRow['fileref_md5'] != '') {
				$modified = (@md5_file($fileReference) != $toRow['fileref_md5']);
			} else {
				$modified = ($toRow['fileref_mtime'] != $fileMtime);
			}

			if (($mappingStatusError = $modified ? 1 : 0)) {
				$mappingStatusIcon  .= t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_warning2.gif', 'width="18" height="16"');
				$mappingStatusTitle .= sprintf($GLOBALS['LANG']->getLL('center_mapping_changed'), t3lib_BEfunc::datetime($toRow['tstamp']));
			} else {
				$mappingStatusIcon  .= t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif', 'width="18" height="16"');
				$mappingStatusTitle .= $GLOBALS['LANG']->getLL('center_mapping_good');
			}

			// Module may be allowed, but modify may not
			if ($this->modifiable) {
				$mappingStatusActions .= '<a href="' . htmlspecialchars($linkUrl) . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to') . ' ]</a> ';
			}
		} else if (($mappingStatusError = !$fileMtime ? 2 : 0)) {
			$mappingStatusIcon    .= t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"');
			$mappingStatusTitle   .= $GLOBALS['LANG']->getLL('center_mapping_unmapped');
			$mappingStatusMessage .= '<em style="font-size: 0.8em;>' . $GLOBALS['LANG']->getLL('center_mapping_note') . '<br /></em>';

			if ($this->modifiable) {
				$mappingStatusActions .= '<a href="' . htmlspecialchars($linkUrl) . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to_map') . ' ]</a> ';
			}
		} else {
			if ($this->modifiable) {
				$mappingStatusActions .= '<a href="' . htmlspecialchars($linkUrl) . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to_remap') . ' ]</a> ';
				$mappingStatusActions .= '<a href="' . htmlspecialchars($linkUrl . '&SET[page]=preview') . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to_verify') . ' ]</a>';
			}
		}

		if (!$this->modifiable) {
			$mappingStatusActions .= '<a href="' . htmlspecialchars($linkUrl . '&SET[page]=preview') . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to_preview') . ' ]</a>';
		}

		$mappingStatusShort = '<img' . $mappingStatusIcon . ' title="' . $mappingStatusTitle . '" class="absmiddle" />';
		$mappingStatusLine  = '<img' . $mappingStatusIcon . ' alt="" class="absmiddle" /> ' . $mappingStatusTitle . '<br />';
		$mappingStatusLong  = $mappingStatusLine . $mappingStatusMessage;

		if ($mappingStatusError >= 2) {
			$this->pObj->setErrorLog($scope, 'fatal', $mappingStatusLine . ' (TO: "' . $toRow['title'] . '")');
		} else if ($mappingStatusError >= 1) {
			$this->pObj->setErrorLog($scope, 'warning', $mappingStatusLine . ' (TO: "' . $toRow['title'] . '")');
		}

		/* ------------------------------------------------------------------------------ */
		if ($this->MOD_SETTINGS['set_details'])	{
			$XMLinfo = $this->pObj->xmlObj->getXMLdetails($toRow['localprocessing']);
		}

		/* ------------------------------------------------------------------------------ */
		// Links:
		if ($this->modifiable) {
			$lpXML = '<a href="#" onclick="' .
					htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_tmplobj][' . $toRow['uid'] . ']=edit&columnsOnly=localprocessing', $this->doc->backPath)) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit') . '" alt="" class="absmiddle" />' .
					'</a>';
			$editLink = '<a href="' .
					$this->doc->issueCommand('&cmd[tx_templavoila_tmplobj][' . $toRow['uid'] . '][delete]=1') . '" onclick="return confirm(' . $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('deleteTOMsg')) . ');">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/garbage.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:delete') . '" alt="" class="absmiddle" style="float: right;" />' .
					'</a>';
			$editLink .= '<a href="#" onclick="' .
					htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_tmplobj][' . $toRow['uid'] . ']=edit', $this->doc->backPath)) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit') . '" alt="" class="absmiddle" style="float: right;" />' .
					'</a>';
			$toTitle = '<a href="' . htmlspecialchars($linkUrl) . '" title="' . $GLOBALS['LANG']->getLL('center_view_to') . '">' . htmlspecialchars($toRow['title']) . '</a>';
		} else {
			$lpXML = '';
			$editLink = '';
			$toTitle = htmlspecialchars($toRow['title']);
		}

		// Format XML if requested
		if ($this->MOD_SETTINGS['set_details'])	{
			$lpXML .= '';

			if ($toRow['localprocessing'])	{
				require_once(PATH_t3lib . 'class.t3lib_syntaxhl.php');

				$hlObj = t3lib_div::makeInstance('t3lib_syntaxhl');
				$lpXML .= '<pre>' . str_replace(chr(9), '&nbsp;&nbsp;&nbsp;', $hlObj->highLight_DS($toRow['localprocessing'])) . '</pre>';
			}
		}

		$lpXML .= $toRow['localprocessing']
			? t3lib_div::formatSize(strlen($toRow['localprocessing'])) . 'bytes'
			: '&mdash;';

		/* ------------------------------------------------------------------------------ */
		// Compile info table:
		$tableAttribs = ' border="0" cellpadding="1" cellspacing="1" width="98%" style="margin-top: -3px;" class="lrPadding"';

		if (!$children)	{
			if ($this->MOD_SETTINGS['set_details'])	{
				/* ------------------------------------------------------------------------------ */
				list($templateUsageError,
				     $templateUsageIcon,
				     $templateUsageTitle,
				     $templateUsageMessage,
				     $templateUsageCount) = $this->findRecordsWhereTOUsed($toRow, $scope);

				$templateUsageShort = '<img' . $templateUsageIcon . ' title="' . $templateUsageTitle . '" class="absmiddle" />';
				$templateUsageLine  = '<img' . $templateUsageIcon . ' alt="" class="absmiddle" /> ' . $templateUsageTitle . '<br />';
				$templateUsageLong  = $templateUsageLine . $templateMessage;

				if ($templateUsageError >= 2) {
					$this->pObj->setErrorLog($scope, 'fatal', $templateUsageLine . ' (TO: "' . $toRow['title'] . '")');
				} else if ($templateUsageError >= 1) {
					$this->pObj->setErrorLog($scope, 'warning', $templateUsageLine . ' (TO: "' . $toRow['title'] . '")');
				}
			}

			$content .= '
			<table' . $tableAttribs . '>
				<tr class="bgColor4-20">
					<td colspan="3">' .
						$collapseIcon .
						$recordIcon .
						$toTitle . ' <em>[Template Object, DB Record]</em>' .
						$editLink .
					'</td>
				</tr>
				<tr class="bgColor4">
					<td style="width: 100px; padding: 1em; vertical-align: top; text-align: center;">' . $icon . '</td>
					<td>
					<dl class="TO-listing">
						<dt>' . $GLOBALS['LANG']->getLL('description') . ':</dt>
						<dd>' . ($toRow['description'] ? htmlspecialchars($toRow['description']) : '&mdash;') . '</dd>

						<dt>' . $GLOBALS['LANG']->getLL('fileref') . ':</dt>
						<dd>' . $fileRef . $fileMsg . '</dd>

						<dt>' . $GLOBALS['LANG']->getLL('center_view_localproc') . ' <strong>XML</strong>:</dt>
						<dd>' . $lpXML . ($this->MOD_SETTINGS['set_details'] ? '<hr />' . $XMLinfo['HTML'] : '') . '</dd>

					' . ($this->MOD_SETTINGS['set_details'] ? '
						<dt>' . $GLOBALS['LANG']->getLL('created') . ':</dt>
						<dd>' . t3lib_BEfunc::datetime($toRow['crdate']) . ' ' . $GLOBALS['LANG']->getLL('by') . ' [' . $toRow['cruser_id'] . ']</dd>

						<dt>' . $GLOBALS['LANG']->getLL('updated') . ':</dt>
						<dd>' . t3lib_BEfunc::datetime($toRow['tstamp']) . '</dd>

						<dt>' . $GLOBALS['LANG']->getLL('used') . ':</dt>
						<dd>' . $templateUsageLong . '</dd>
					' : '') . '

						<dt>' . $GLOBALS['LANG']->getLL('center_list_mapstatus') . ':</dt>
						<dd>' . $mappingStatusLong . '</dd>
					</dl>
					<div class="actions">' .
						$mappingStatusActions . '
					</div>
					</td>
				</tr>
			</table>
			';
		} else {
			$content .= '
			<table' . $tableAttribs . '>
				<tr class="bgColor4-20">
					<td colspan="3">' .
						$collapseIcon .
						$recordIcon .
						$toTitle . ' <em>[Template Object, DB Record]</em>' .
						$editLink .
						'</td>
				</tr>
				<tr class="bgColor4">
					<td>
					<dl class="TO-listing">
						<dt>' . $GLOBALS['LANG']->getLL('fileref') . ':</dt>
						<dd>' . $fileRef . $fileMsg . '</dd>

						<dt>' . $GLOBALS['LANG']->getLL('rendertype') . ':</dt>
						<dd>' . t3lib_BEfunc::getProcessedValue('tx_templavoila_tmplobj', 'rendertype', $toRow['rendertype']) . '</dd>

						<dt>' . $GLOBALS['LANG']->getLL('language') . ':</dt>
						<dd>' . t3lib_BEfunc::getProcessedValue('tx_templavoila_tmplobj', 'sys_language_uid', $toRow['sys_language_uid']) . '</dd>

						<dt>' . $GLOBALS['LANG']->getLL('center_view_localproc') . ' <strong>XML</strong>:</dt>
						<dd>' . $lpXML . ($this->MOD_SETTINGS['set_details'] ? '<hr />' . $XMLinfo['HTML'] : '') . '</dd>

					' . ($this->MOD_SETTINGS['set_details'] ? '
						<dt>' . $GLOBALS['LANG']->getLL('created') . ':</dt>
						<dd>' . t3lib_BEfunc::datetime($toRow['crdate']) . ' ' . $GLOBALS['LANG']->getLL('by') . ' [' . $toRow['cruser_id'] . ']</dd>

						<dt>' . $GLOBALS['LANG']->getLL('updated') . ':</dt>
						<dd>' . t3lib_BEfunc::datetime($toRow['tstamp']) . '</dd>
					' : '') . '

						<dt>' . $GLOBALS['LANG']->getLL('center_list_mapstatus') . ':</dt>
						<dd>' . $mappingStatusLong . '</dd>
					</dl>
					' . $mappingStatusActions . '
					</td>
				</tr>
			</table>
			';
		}

		/* ------------------------------------------------------------------------------ */
		// Traverse template objects which are not children of anything:
		if (!$children && is_array($toRecords[$toRow['uid']])) {
			$TOchildrenContent = '';

			foreach ($toRecords[$toRow['uid']] as $toIndex => $childToObj) {
				$rTODres = $this->renderTODisplay($childToObj, $toRecords, $scope, 1);
				$TOchildrenContent .= $rTODres['HTML'];

				// Unset it so we can eventually see what is left:
				unset($toRecords[$toRow['uid']][$toIndex]);
			}

			$content .= '<div style="margin-left: 102px;">' . $TOchildrenContent . '</div>';
		}

		// Return content
		return array(
			'HTML'   => $content,
			'icon'   => $recordIcon,
			'link'   => $linkUrl,
			'action' => $editLink,
			'status' => $mappingStatusShort,
			'stats'  => $templateUsageCount
		);
	}

	/**
	 * Creates an array of all used templatefiles with counters
	 *
	 * @return	array		templatefile usages.
	 */
	function findFilesWhereTOUsed() {
		return $this->tFileList;
	}

	/**
	 * Creates listings of pages / content elements where template objects are used.
	 *
	 * @param	array		Template Object record
	 * @param	integer		Scope value. 1) page,  2) content elements
	 * @return	string		HTML table listing usages.
	 */
	function findRecordsWhereTOUsed($toRow, $scope) {
		$output = array();

		switch (intval($scope))	{
			// PAGES:
			case TVDS_SCOPE_PAGE:
				// Header:
				$output[] = '
					<tr class="bgColor5 tableheader">
						<td>PID:</td>
						<td>Title:</td>
						<td>Path:</td>
						<td>Workspace:</td>
					</tr>
				';

				// Main templates:
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid,title,pid,t3ver_wsid,t3ver_id',
					'pages',
					'(
						(tx_templavoila_to='      . intval($toRow['uid']) . ' AND tx_templavoila_ds=' .      $GLOBALS['TYPO3_DB']->fullQuoteStr($toRow['datastructure'], 'pages') . ') OR
						(tx_templavoila_next_to=' . intval($toRow['uid']) . ' AND tx_templavoila_next_ds=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($toRow['datastructure'], 'pages') . ')
					)' .
					t3lib_BEfunc::deleteClause('pages')
				);

				while (false !== ($pRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
					if (($path = $this->pObj->getPIDPath($pRow['uid']))) {
						$output[] = '
							<tr class="bgColor4-20">
								<td nowrap="nowrap">'.
									'<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[pages][' . $pRow['uid'] . ']=edit', $this->doc->backPath)).'" title="Edit">'.
									htmlspecialchars($pRow['uid']) .
									'</a></td>
								<td nowrap="nowrap">' .
									htmlspecialchars($pRow['title']) .
									'</td>
								<td nowrap="nowrap">' .
									'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($pRow['uid'],$this->doc->backPath).'return false;').'" title="View">'.
									htmlspecialchars($path) .
									'</a></td>
								<td nowrap="nowrap">' .
									htmlspecialchars($pRow['pid'] == -1 ? 'Offline version 1.' . $pRow['t3ver_id'] . ', WS: ' . $pRow['t3ver_wsid'] : 'LIVE!').
									'</td>
							</tr>';
					} else {
						$output[] = '
							<tr class="bgColor4-20">
								<td nowrap="nowrap">'.
									htmlspecialchars($pRow['uid']) .
									'</td>
								<td><em>No access</em></td>
								<td>&mdash;</td>
								<td>&mdash;</td>
							</tr>';
					}
				}

				$GLOBALS['TYPO3_DB']->sql_free_result($res);
				break;

			// FCE
			case TVDS_SCOPE_FCE:
				// Header:
				$output[] = '
					<tr class="bgColor5 tableheader">
						<td>UID:</td>
						<td>Header:</td>
						<td>Path:</td>
						<td>Workspace:</td>
					</tr>
				';

				// Select Flexible Content Elements:
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid,header,pid,t3ver_wsid,t3ver_id',
					'tt_content',
					'CType='.$GLOBALS['TYPO3_DB']->fullQuoteStr('templavoila_pi1','tt_content') .
						' AND tx_templavoila_to=' . intval($toRow['uid']) .
						' AND tx_templavoila_ds=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($toRow['datastructure'], 'tt_content') .
						t3lib_BEfunc::deleteClause('tt_content'),
					'',
					'pid'
				);

				// Elements:
				while (false !== ($pRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
					if (($path = $this->pObj->getPIDPath($pRow['pid']))) {
						$output[] = '
							<tr class="bgColor4-20">
								<td nowrap="nowrap">'.
									'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tt_content]['.$pRow['uid'].']=edit',$this->doc->backPath)).'" title="Edit">'.
									htmlspecialchars($pRow['uid']).
									'</a></td>
								<td nowrap="nowrap">'.
									htmlspecialchars($pRow['header']).
									'</td>
								<td nowrap="nowrap">'.
									'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($pRow['pid'],$this->doc->backPath).'return false;').'" title="View page">'.
									htmlspecialchars($path).
									'</a></td>
								<td nowrap="nowrap">'.
									htmlspecialchars($pRow['pid']==-1 ? 'Offline version 1.'.$pRow['t3ver_id'].', WS: '.$pRow['t3ver_wsid'] : 'LIVE!').
									'</td>
							</tr>';
					} else {
						$output[] = '
							<tr class="bgColor4-20">
								<td nowrap="nowrap">'.
									htmlspecialchars($pRow['uid']).
									'</td>
								<td><em>No access</em></td>
								<td>&mdash;</td>
								<td>&mdash;</td>
							</tr>';
					}
				}

				$GLOBALS['TYPO3_DB']->sql_free_result($res);
				break;
		}

		// Create final output table:
		if (count($output) > 1) {
			return array(
				0,
				t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif', 'width="18" height="16"'),
				'Used in ' . (count($output) - 1) . ' Elements',
				':
				<table border="0" cellspacing="1" cellpadding="1" class="lrPadding">
					' . implode('', $output) . '
				</table>',
				count($output) - 1
			);
		} else {
			return array(
				1,
				t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_warning2.gif', 'width="18" height="16"'),
				'No usage!',
				'',
				0
			);
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_to.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_to.php']);
}

?>