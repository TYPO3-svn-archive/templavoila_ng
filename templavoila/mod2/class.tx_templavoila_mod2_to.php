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


	/**
	 * Initializes the to object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila control-center module.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	void
	 * @access public
	 */
	function init(&$pObj) {
		// Make local reference to some important variables:
		$this->pObj = &$pObj;
		$this->doc = &$this->pObj->doc;
		$this->extKey = &$this->pObj->extKey;
		$this->modTSconfig = &$this->pObj->modTSconfig;
		$this->MOD_SETTINGS = &$this->pObj->MOD_SETTINGS;
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
		global $BE_USER;

		// Put together the records icon including content sensitive menu link wrapped around it:
		$recordIcon = t3lib_iconWorks::getIconImage('tx_templavoila_tmplobj', $toRow, $this->doc->backPath, 'class="absmiddle"');
		$recordIcon = $this->doc->wrapClickMenuOnIcon($recordIcon, 'tx_templavoila_tmplobj', $toRow['uid'], 1, '&callingScriptId=' . rawurlencode($this->doc->scriptID));

		// Preview icon:
		if ($toRow['previewicon']) {
			$icon = '<img src="' . $this->doc->backPath . '../uploads/tx_templavoila/' . $toRow['previewicon'] . '" alt="" />';
		} else {
			$icon = '[' . $GLOBALS['LANG']->getLL('noicon') . ']';
		}

		// Mapping status / link:
		$linkUrl = $this->pObj->cm1Script . 'id=' . $this->pObj->id . '&table=tx_templavoila_tmplobj&uid=' . $toRow['uid'] . '&_reload_from=1&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));

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

		$mappingStatus = $mappingStatus_index = '';
		if ($fileMtime && $toRow['fileref_mtime']) {
			if ($toRow['fileref_md5'] != '') {
				$modified = (@md5_file($fileReference) != $toRow['fileref_md5']);
			} else {
				$modified = ($toRow['fileref_mtime'] != $fileMtime);
			}

			if (!$modified)	{
				$mappingStatus  = $mappingStatus_index = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_ok2.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" />';
				$mappingStatus .= $GLOBALS['LANG']->getLL('center_mapping_good') . '<br/>';
			} else {
				$mappingStatus  = $mappingStatus_index = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_warning2.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" />';
				$mappingStatus .= sprintf($GLOBALS['LANG']->getLL('center_mapping_changed'), t3lib_BEfunc::datetime($toRow['tstamp'])) . '<br/>';

				$this->pObj->setErrorLog($scope, 'warning', $mappingStatus . ' (TO: "' . $toRow['title'] . '")');
			}

			// Module may be allowed, but modify may not
			if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
				$mappingStatus .= '<a href="' . htmlspecialchars($linkUrl) . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to') . ' ]</a> ';
			}
		} elseif (!$fileMtime) {
			$mappingStatus  = $mappingStatus_index = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_fatalerror.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" />';
			$mappingStatus .= $GLOBALS['LANG']->getLL('center_mapping_unmapped') . '<br/>';

			$this->pObj->setErrorLog($scope, 'fatal', $mappingStatus . ' (TO: "' . $toRow['title'] . '")');

			$mappingStatus .= '<em style="font-size: 0.8em;>' . $GLOBALS['LANG']->getLL('center_mapping_note') . '<br/></em>';

			if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
				$mappingStatus .= '<a href="' . htmlspecialchars($linkUrl) . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to_map') . ' ]</a> ';
			}
		} else {
			$mappingStatus = '';

			if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
				$mappingStatus .= '<a href="' . htmlspecialchars($linkUrl) . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to_remap') . ' ]</a> ';
				$mappingStatus .= '<a href="' . htmlspecialchars($linkUrl . '&SET[page]=preview') . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to_verify') . ' ]</a>';
			}
		}

		if (!$BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
			$mappingStatus .= '<a href="' . htmlspecialchars($linkUrl . '&SET[page]=preview') . '">[ ' . $GLOBALS['LANG']->getLL('center_view_to_preview') . ' ]</a>';
		}

		if ($this->MOD_SETTINGS['set_details'])	{
			$XMLinfo = $this->pObj->xmlObj->DSdetails($toRow['localprocessing']);
		}

		// Links:
		if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
			$lpXML = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_tmplobj][' . $toRow['uid'] . ']=edit&columnsOnly=localprocessing', $this->doc->backPath)) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit') . '" alt="" class="absmiddle" />' .
					'</a>';
			$editLink = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_tmplobj][' . $toRow['uid'] . ']=delete', $this->doc->backPath)) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/garbage.gif', 'width="11" height="12"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit') . '" alt="" class="absmiddle" style="float: right;" />' .
					'</a>';
			$editLink .= '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_tmplobj][' . $toRow['uid'] . ']=edit', $this->doc->backPath)) . '">' .
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

		// Compile info table:
		$tableAttribs = ' border="0" cellpadding="1" cellspacing="1" width="98%" style="margin-top: 3px;" class="lrPadding"';

		$fRWTOUres = array();

		if (!$children)	{
			if ($this->MOD_SETTINGS['set_details'])	{
				$fRWTOUres = $this->findRecordsWhereTOUsed($toRow, $scope);
			}

			$content .= '
			<table' . $tableAttribs . '>
				<tr class="bgColor4-20">
					<td colspan="3">' .
						$recordIcon .
						$toTitle .
						$editLink .
					'</td>
				</tr>
				<tr class="bgColor4">
					<td rowspan="' . ($this->MOD_SETTINGS['set_details'] ? 7 : 4) . '" style="width: 100px; text-align: center;">' . $icon . '</td>
					<td>' . $GLOBALS['LANG']->getLL('fileref') . ':</td>
					<td>' . $fileRef . $fileMsg . '</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('description') . ':</td>
					<td>' . htmlspecialchars($toRow['description']) . '</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('center_list_mapstatus') . ':</td>
					<td>' . $mappingStatus . '</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('center_view_localproc') . ' <strong>XML</strong>:</td>
					<td>' . $lpXML . ($this->MOD_SETTINGS['set_details'] ? '<hr />' . $XMLinfo['HTML'] : '') . '</td>
				</tr>' . ($this->MOD_SETTINGS['set_details'] ? '
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('used') . ':</td>
					<td>' . $fRWTOUres['HTML'] . '</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('created') . ':</td>
					<td>' . t3lib_BEfunc::datetime($toRow['crdate']) . ' ' . $GLOBALS['LANG']->getLL('by') . ' [' . $toRow['cruser_id'] . ']</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('updated') . ':</td>
					<td>' . t3lib_BEfunc::datetime($toRow['tstamp']) . '</td>
				</tr>' : '') . '
			</table>
			';
		} else {
			$content .= '
			<table' . $tableAttribs . '>
				<tr class="bgColor4-20">
					<td colspan="3">' .
						$recordIcon .
						$toTitle .
						$editLink .
						'</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('fileref') . ':</td>
					<td>' . $fileRef . $fileMsg . '</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('center_list_mapstatus') . ':</td>
					<td>' . $mappingStatus.'</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('rendertype') . ':</td>
					<td>' . t3lib_BEfunc::getProcessedValue('tx_templavoila_tmplobj', 'rendertype', $toRow['rendertype']) . '</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('language') . ':</td>
					<td>' . t3lib_BEfunc::getProcessedValue('tx_templavoila_tmplobj', 'sys_language_uid', $toRow['sys_language_uid']) . '</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('center_view_localproc') . ' <strong>XML</strong>:</td>
					<td>' . $lpXML . ($this->MOD_SETTINGS['set_details'] ? '<hr />' . $XMLinfo['HTML'] : '') . '</td>
				</tr>' . ($this->MOD_SETTINGS['set_details'] ? '
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('created') . ':</td>
					<td>' . t3lib_BEfunc::datetime($toRow['crdate']) . ' ' . $GLOBALS['LANG']->getLL('by') . ' [' . $toRow['cruser_id'] . ']</td>
				</tr>
				<tr class="bgColor4">
					<td>' . $GLOBALS['LANG']->getLL('updated') . ':</td>
					<td>' . t3lib_BEfunc::datetime($toRow['tstamp']) . '</td>
				</tr>' : '') . '
			</table>
			';
		}

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
			'HTML' => $content,
			'mappingStatus' => $mappingStatus_index,
			'usage' => $fRWTOUres['usage']
		);
	}

	/**
	 * Creates a list of all template files used in TOs
	 *
	 * @return	string		HTML table
	 */
	function completeTemplateFileList() {
		$output = '';
		if (is_array($this->tFileList))	{
			$output='';

			// USED FILES:
			$tRows = array();
			$tRows[] = '
				<tr class="c-headLineTable" style="font-weight: bold; color: #FFFFFF;">
					<td>File</td>
					<td align="center">Usage count</td>
					<td>New DS/TO</td>
				</tr>';

			$i = 0;
			foreach ($this->tFileList as $tFile => $count) {
				$tRows[] = '
					<tr class="' . ($i++ % 2 == 0 ? 'bgColor4' : 'bgColor6') . '">
						<td>' .
							'<a href="' . htmlspecialchars($this->doc->backPath . '../' . substr($tFile, strlen(PATH_site))) . '" target="_blank">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/zoom.gif','width="11" height="12"').' alt="" class="absmiddle" /> ' . htmlspecialchars(substr($tFile,strlen(PATH_site))) .
							'</a></td>
						<td align="center">' . $count . '</td>
						<td>' .
							'<a href="'.htmlspecialchars($this->pObj->cm1Script . 'id=' . $this->pObj->id . '&file=' . rawurlencode($tFile)) . '&mapElPath=%5BROOT%5D">'.
							'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_el.gif','width="11" height="12"').' alt="" class="absmiddle" /> ' . htmlspecialchars('Create...') .
							'</a></td>
					</tr>';
			}

			if (count($tRows) > 1) {
				$output .= '
				<h3>Used files:</h3>
				<table border="0" cellpadding="1" cellspacing="1" class="typo3-dblist">
					' . implode('', $tRows) . '
				</table>
				';
			}

			// TEMPLATE ARCHIVE:
			if ($this->modTSconfig['properties']['templatePath']) {
				$path = t3lib_div::getFileAbsFileName($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] . $this->modTSconfig['properties']['templatePath']);
				if (@is_dir($path) && is_array($GLOBALS['FILEMOUNTS']))	{
					foreach ($GLOBALS['FILEMOUNTS'] as $mountCfg) {
						if (t3lib_div::isFirstPartOfStr($path,$mountCfg['path'])) {
							$files = t3lib_div::getFilesInDir($path, 'html,htm,tmpl', 1);

							// USED FILES:
							$tRows = array();
							$tRows[] = '
								<tr class="c-headLineTable" style="font-weight: bold; color: #FFFFFF;">
									<td>File</td>
									<td align="center">Usage count</td>
									<td>New DS/TO</td>
								</tr>';
                            
                            $i = 0;
							foreach($files as $tFile) {
								$tRows[] = '
									<tr class="' . ($i++ % 2 == 0 ? 'bgColor4' : 'bgColor6') . '">
										<td>'.
											'<a href="' . htmlspecialchars($this->doc->backPath . '../' . substr($tFile, strlen(PATH_site))) . '" target="_blank">' .
											'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/zoom.gif','width="11" height="12"').' alt="" class="absmiddle" /> ' . htmlspecialchars(substr($tFile, strlen(PATH_site))) .
											'</a></td>
										<td align="center">' . ($this->tFileList[$tFile] ? $this->tFileList[$tFile] : '-') . '</td>
										<td>'.
											'<a href="' . htmlspecialchars($this->pObj->cm1Script . 'id=' . $this->pObj->id . '&file=' . rawurlencode($tFile)) . '&mapElPath=%5BROOT%5D">' .
											'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_el.gif','width="11" height="12"').' alt="" class="absmiddle" /> ' . htmlspecialchars('Create...') .
											'</a></td>
									</tr>';
							}

							if (count($tRows) > 1)	{
								$output .= '
								<h3>Template Archive:</h3>
								<table border="0" cellpadding="1" cellspacing="1" class="typo3-dblist">
									' . implode('', $tRows) . '
								</table>
								';
							}
						}
					}
				}
			}
		}

		return $output;
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
				$output[]='
							<tr class="bgColor5 tableheader">
								<td>PID:</td>
								<td>Title:</td>
								<td>Path:</td>
								<td>Workspace:</td>
							</tr>';

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
					if (($path = $this->pObj->findRecordsWhereUsed_pid($pRow['uid']))) {
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
								<td>-</td>
								<td>-</td>
							</tr>';
					}
				}

				$GLOBALS['TYPO3_DB']->sql_free_result($res);
				break;
			// FCE
			case TVDS_SCOPE_FCE:
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

				// Header:
				$output[]='
							<tr class="bgColor5 tableheader">
								<td>UID:</td>
								<td>Header:</td>
								<td>Path:</td>
								<td>Workspace:</td>
							</tr>';

				// Elements:
				while (false !== ($pRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
					if (($path = $this->pObj->findRecordsWhereUsed_pid($pRow['pid']))) {
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
								<td>-</td>
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
				$outputString = 'Used in ' . (count($output) - 1) . ' Elements:<table border="0" cellspacing="1" cellpadding="1" class="lrPadding">' . implode('', $output) . '</table>';
			} else {
				$outputString = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_warning2.gif', 'width="18" height="16"') . ' alt="" class="absmiddle" />No usage!';
				$this->pObj->setErrorLog($scope, 'warning', $outputString . ' (TO: "' . $toRow['title'] . '")');
			}
		}

		return array(
			'HTML' => $outputString,
			'usage' => count($output) - 1
		);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_to.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_to.php']);
}

?>