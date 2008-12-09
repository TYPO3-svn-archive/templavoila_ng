<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2006 Kasper Sk�rh�j <kasper@typo3.com>
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
 * Module 'TemplaVoila' for the 'templavoila' extension.
 *
 * $Id: index.php 9141 2008-05-12 15:52:41Z dmitry $
 *
 * @author   Kasper Sk�rh�j <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  101: class tx_templavoila_module2 extends t3lib_SCbase
 *  125:     function menuConfig()
 *  144:     function main()
 *  203:     function printContent()
 *
 *              SECTION: Rendering module content:
 *  227:     function renderModuleContent()
 *  264:     function renderModuleContent_searchForTODS()
 *  326:     function renderModuleContent_mainView()
 *  460:     function renderDSlisting($dsScopeArray, &$toRecords,$scope)
 *  563:     function renderDataStructureDisplay($dsR, $toIdArray, $scope)
 *  718:     function renderTODisplay($toObj, &$toRecords, $scope, $children=0)
 *  902:     function findRecordsWhereTOUsed($toObj,$scope)
 * 1041:     function findDSUsageWithImproperTOs($dsID, $toIdArray, $scope)
 * 1158:     function findRecordsWhereUsed_pid($pid)
 * 1174:     function completeTemplateFileList()
 * 1271:     function setErrorLog($scope,$type,$HTML)
 * 1282:     function getErrorLog($scope)
 * 1308:     function DSdetails($DSstring)
 *
 *              SECTION: Wizard for new site
 * 1381:     function renderNewSiteWizard_overview()
 * 1442:     function renderNewSiteWizard_run()
 * 1491:     function wizard_checkMissingExtensions()
 * 1527:     function wizard_checkConfiguration()
 * 1537:     function wizard_checkDirectory()
 * 1557:     function wizard_step1()
 * 1620:     function wizard_step2()
 * 1669:     function wizard_step3()
 * 1778:     function wizard_step4()
 * 1800:     function wizard_step5($menuField)
 * 2039:     function wizard_step6()
 * 2060:     function getImportObj()
 * 2078:     function syntaxHLTypoScript($v)
 * 2094:     function makeWrap($cfg)
 * 2110:     function getMenuDefaultCode($field)
 * 2122:     function saveMenuCode()
 * 2160:     function getBackgroundColor($filePath)
 *
 * TOTAL FUNCTIONS: 33
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once('conf.php');

require_once (PATH_t3lib.'class.t3lib_scbase.php');
require_once (PATH_t3lib.'class.t3lib_parsehtml.php');

$LANG->includeLLFile('EXT:templavoila/mod2/locallang.xml');
$BE_USER->modAccess($MCONF,1);

/**
 * Module 'TemplaVoila' for the 'templavoila' extension.
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_module2 extends t3lib_SCbase {

		// External static:
	var $importPageUid = 0;	// Import as first page in root!

	var $wizardData = array();	// Session data during wizard

	var $pageinfo;
	var $modTSconfig;
	var $extKey = 'templavoila';			// Extension key of this module
	var $baseScript = 'index.php?';
	var $mod2Script = '../mod2/index.php?';
	var $cm1Script = '../cm1/index.php?';

	var $tFileList=array();
	var $errorsWarnings=array();


	function init() {
		parent::init();

		if (preg_match('/mod.php$/', PATH_thisScript)) {
			$this->baseScript = 'mod.php?M=web_txtemplavoilaM2&';
			$this->mod2Script = 'mod.php?M=web_txtemplavoilaM2&';
			$this->cm1Script = 'mod.php?M=xMOD_txtemplavoilaCM1&';
		}
	}

	/**
	 * Preparing menu content
	 *
	 * @return	void
	 */
	function menuConfig()	{
		$this->MOD_MENU = array(
#			'set_showDSxml' => '',
			'set_details' => '',
			'wiz_step' => ''
		);

			// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.'.$this->MCONF['name']);

			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::GPvar('SET'), $this->MCONF['name']);
	}

	/**
	 * Main function of the module.
	 *
	 * @return	void		Nothing.
	 */
	function main()    {
		global $BE_USER, $LANG, $BACK_PATH;

			// Access check!
			// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if ($access)    {

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('noDoc');
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->divClass = '';
			$this->doc->form='<form action="'.htmlspecialchars($this->baseScript . 'id='.$this->id) . '" method="post" autocomplete="off">';

				// Add custom styles
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey) . "mod2/styles.css";

				// Adding classic jumpToUrl function, needed for the function menu.
				// Also, the id in the parent frameset is configured.
			$this->doc->JScode=$this->doc->wrapScriptTags('
				function jumpToUrl(URL)	{ //
					document.location = URL;
					return false;
				}
				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
			');

				// Setting up support for context menus (when clicking the items icon)
			$CMparts = $this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode .= $CMparts[0];
			$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->postCode.= $CMparts[2];

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);

				// Rendering module content
			$this->renderModuleContent(false);

			if ($BE_USER->mayMakeShortcut()) {
				$this->content.='<br /><br />'.$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']);
			}
		} else {	// No access or no current uid:

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('noDoc');
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->divClass = '';
			$this->doc->form='<form action="'.htmlspecialchars($this->baseScript . 'id='.$this->id).'" method="post" autocomplete="off">';
			$this->content.=$this->doc->startPage($LANG->getLL('title'));
		}

		$this->content .= $this->doc->endPage();
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()    {
		echo $this->content;
	}










	/******************************
	 *
	 * Rendering module content:
	 *
	 *******************************/

	/**
	 * Renders module content:
	 *
	 * @return	void
	 */
	function renderModuleContent($singleView=false)	{

		if ($this->MOD_SETTINGS['wiz_step'])	{	// Run wizard instead of showing overview.
			$this->renderNewSiteWizard_run();
			return false;
		}
		else {
				// Select all Data Structures in the PID and put into an array:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'count(*)',
						'tx_templavoila_datastructure',
						'pid='.intval($this->id).t3lib_BEfunc::deleteClause('tx_templavoila_datastructure')
					);
			list($countDS) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

				// Select all Template Records in PID:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'count(*)',
						'tx_templavoila_tmplobj',
						'pid='.intval($this->id).t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj')
					);
			list($countTO) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

				// If there are TO/DS, render the module as usual, otherwise do something else...:
			if ($countTO || $countDS)	{
				$this->renderModuleContent_mainView($singleView);
				return true;
			}
			else {
				$this->renderModuleContent_searchForTODS();
				$this->renderNewSiteWizard_overview();
				return false;
			}
		}
	}

	/**
	 * Renders module content, overview of pages with DS/TO on.
	 *
	 * @return	void
	 */
	function renderModuleContent_searchForTODS()	{
		global $LANG;

			// Select all Data Structures in the PID and put into an array:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'pid,count(*)',
					'tx_templavoila_datastructure',
					'pid>=0'.t3lib_BEfunc::deleteClause('tx_templavoila_datastructure'),
					'pid'
				);
		while($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
			$list[$row['pid']]['DS'] = $row['count(*)'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

			// Select all Template Records in PID:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'pid,count(*)',
					'tx_templavoila_tmplobj',
					'pid>=0'.t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj'),
					'pid'
				);
		while($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
			$list[$row['pid']]['TO'] = $row['count(*)'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

			// Traverse the pages found and list in a table:
		$tRows = array();
		$tRows[] = '
			<tr class="bgColor5 tableheader">
				<td>' . $LANG->getLL('list_storage') . '</td>
				<td>' . $LANG->getLL('list_dss') . ':</td>
				<td>' . $LANG->getLL('list_tos') . ':</td>
			</tr>';

		if (is_array($list))	{
			foreach($list as $pid => $stat)	{
				$path = $this->findRecordsWhereUsed_pid($pid);
				if ($path)	{
					$tRows[] = '
						<tr class="bgColor4">
							<td><a href="' . $this->baseScript. 'id=' . $pid . '">' . htmlspecialchars($path) . '</a></td>
							<td>'.htmlspecialchars($stat['DS']).'</td>
							<td>'.htmlspecialchars($stat['TO']).'</td>
						</tr>';
				}
			}

				// Create overview
			$outputString  = '<p>' . $LANG->getLL('list_intro') . ':</p>';
			$outputString .= '<table border="0" cellpadding="1" cellspacing="1" class="lrPadding">'.implode('',$tRows).'</table>';

				// Add output:
			$this->content.= $outputString;
		}
	}

	/**
	 * Renders module content main view:
	 *
	 * @return	void
	 */
	function renderModuleContent_mainView($singleView)	{
		global $LANG;

			// Select all Data Structures in the PID and put into an array:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'*',
					'tx_templavoila_datastructure',
					'pid='.intval($this->id).t3lib_BEfunc::deleteClause('tx_templavoila_datastructure'),
					'',
					'title'
				);
		$dsRecords = array();
		while($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
			t3lib_BEfunc::workspaceOL('tx_templavoila_datastructure',$row);
			$dsRecords[$row['scope']][] = $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

			// Select all static Data Structures and add to array:
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures']))	{
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $staticDS)	{
				$staticDS['_STATIC'] = 1;
				$dsRecords[$staticDS['scope']][] = $staticDS;
			}
		}

			// Select all Template Records in PID:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'cruser_id,crdate,tstamp,uid,title, parent, fileref, sys_language_uid, datastructure, rendertype,localprocessing, previewicon,description,fileref_mtime,fileref_md5',
					'tx_templavoila_tmplobj',
					'pid='.intval($this->id).t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj'),
					'',
					'title'
				);
		$toRecords = array();
		while($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
			t3lib_BEfunc::workspaceOL('tx_templavoila_tmplobj',$row);
			$toRecords[$row['parent']][] = $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

			// -----------------------------------------------------
			// Traverse scopes of data structures display template records belonging to them:
			// Each scope is places in its own tab in the tab menu:
		$dsScopes = array_unique(array_merge(array(1,2,0),array_keys($dsRecords)));
		$parts = array();
		foreach($dsScopes as $scopePointer)	{

				// Create listing for a DS:
			list($content,$dsCount,$toCount) = $this->renderDSlisting($dsRecords[$scopePointer],$toRecords,$scopePointer);
			$scopeIcon = '';

				// Label for the tab:
			switch((string)$scopePointer)	{
				case '1':
					$func = 'pagetmpl';
					$label = $LANG->getLL('center_tab_pagetmpl');
					$scopeIcon = t3lib_iconWorks::getIconImage('pages',array(),$this->doc->backPath,'class="absmiddle"');
					break;
				case '2':
					$func = 'fcetmpl';
					$label = $LANG->getLL('center_tab_fcetmpl');
					$scopeIcon = t3lib_iconWorks::getIconImage('tt_content',array(),$this->doc->backPath,'class="absmiddle"');
					break;
				case '0':
					$func = 'other';
					$label = $LANG->getLL('center_tab_other');
					break;
				default:
					$func = 'unknown';
					$label = $LANG->getLL('center_tab_unknown').' "'.$scopePointer.'"';
					break;
			}

				// Error/Warning log:
			$errStat = $this->getErrorLog($scopePointer);

				// Add parts for Tab menu:
			$parts[$func][] = array(
				'label' => $label,
				'icon' => $scopeIcon,
				'content' => $content,
				'linkTitle' => 'DS/TO = '.$dsCount.'/'.$toCount,
				'stateIcon' => $errStat['iconCode']
			);

			if (false !== ($errStat)) {
				if ($singleView)
					$this->MOD_MENU['page'][$func] = '<span style="color: brown;">' .
					$this->MOD_MENU['page'][$func] . ' ('.$errStat['iconCode'].')</span>';
			}
		}

			// -----------------------------------------------------
			// Find lost Template Objects and add them to a TAB if any are found:
		$lostTOs = '';
		$lostTOCount = 0;
		foreach($toRecords as $TOcategories)	{
			foreach($TOcategories as $toObj)	{
				$rTODres = $this->renderTODisplay($toObj, $toRecords, 1);
				$lostTOs.= $rTODres['HTML'];
				$lostTOCount++;
			}
		}

			// Add parts for Tab menu:
		if ($lostTOs) {
			$parts['lost'][] = array(
				'label' => $LANG->getLL('center_tab_lost').' ['.$lostTOCount.']',
				'content' => $lostTOs
			);

			if ($singleView)
				$this->MOD_MENU['page']['lost'] = '<span style="color: brown;">' .
				$this->MOD_MENU['page']['lost'] . ' ('.$lostTOCount.')</span>';
		}
		else if ($singleView) {
			$parts['lost'][] = array(
				'label' => $LANG->getLL('center_tab_lost')
			);
		}

			// -----------------------------------------------------
			// Complete Template File List
		$parts['tmplfiles'][] = array(
			'label' => $LANG->getLL('center_tab_tmplfiles'),
			'content' => $this->completeTemplateFileList()
		);

			// -----------------------------------------------------
			// Errors:
		if (false !== ($errStat = $this->getErrorLog('_ALL'))) {
			$parts['errors'][] = array(
				'label' => $LANG->getLL('center_tab_errors') . ' (' . $errStat['count'] . ')',
				'content' => $errStat['content'],
				'stateIcon' => $errStat['iconCode']
			);

			if ($singleView)
				$this->MOD_MENU['page']['errors'] = '<span style="color: red;">' .
				$this->MOD_MENU['page']['errors'] . ' ('.$errStat['count'].')</span>';
		}
		else if ($singleView) {
			$parts['errors'][] = array(
				'label' => $LANG->getLL('center_tab_errors')
			);
		}

			// -----------------------------------------------------
		if ($singleView) {
			foreach ($this->MOD_MENU['page'] as $label) {
				if (!$parts[$label])
					unset($this->MOD_MENU['page'][$label]);
				//	$this->MOD_MENU['page'][$label] = '<span style="text-decoration: line-through;">' .
				//	$this->MOD_MENU['page'][$label] . '</span>';
			}

			foreach ($parts as $label => $list)
			foreach ($list as $cnf) {
				if (!$cnf['content'])
					unset($this->MOD_MENU['page'][$label]);
				//	$this->MOD_MENU['page'][$label] = '<span style="text-decoration: line-through;">' .
				//	$this->MOD_MENU['page'][$label] . '</span>';
			}

				// show only selected parts
			$list = $parts[$this->MOD_SETTINGS['page']];
			if (!count($list)) $list[] = array();
			foreach ($list as $cnf) {
				$this->content .= $this->doc->section(
					$cnf['label'] ? $cnf['label'] : $this->MOD_MENU['page'][$this->MOD_SETTINGS['page']],
					$cnf['content'] ? $cnf['content'] : $LANG->getLL('none'),
					FALSE,
					TRUE);
			}
		}
		else {
				// put all existing into tabs (no index!)
			$tabs = array();
			foreach ($parts as &$list)
			foreach ($list as &$cnf)
				$tabs[] = $cnf;

				// Create setting handlers:
			$settings = '<p>'.
					t3lib_BEfunc::getFuncCheck('','SET[set_details]',$this->MOD_SETTINGS['set_details'],'',t3lib_div::implodeArrayForUrl('',$_GET,'',1,1)).
					' '.$LANG->getLL('center_details').' &nbsp;&nbsp;&nbsp;'.
				'</p><hr />';

				// Add output:
			$this->content .=
				$settings .
				$this->doc->getDynTabMenu($tabs,'TEMPLAVOILA:templateModule:'.$this->id, 0,0,300);
		}
	}

	/**
	 * Renders Data Structures from $dsScopeArray
	 *
	 * @param	array		Data Structures in a numeric array
	 * @param	array		Array of template objects (passed by reference).
	 * @param	[type]		$scope: ...
	 * @return	array		Returns array with three elements: 0: content, 1: number of DS shown, 2: number of root-level template objects shown.
	 */
	function renderDSlisting($dsScopeArray, &$toRecords,$scope)	{
		global $LANG, $BE_USER;

		$dsCount=0;
		$toCount=0;
		$content='';
		$index='';

			// Traverse data structures to list:
		if (is_array($dsScopeArray))	{
			foreach($dsScopeArray as $dsR)	{

					// Set relation ID of data structure used by template objects:
				$dsID = $dsR['_STATIC'] ? $dsR['path'] : $dsR['uid'];

					// Traverse template objects which are not children of anything:
				$TOcontent = '';
				$indexTO = '';
				$toIdArray = array(-1);
				if (is_array($toRecords[0]))	{
					$newPid = $dsR['pid'];
					$newFileRef = '';
					$newTitle = ($dsR['_STATIC'] && substr($dsR['title'], 0, 4) == 'LLL:' ? $GLOBALS['LANG']->sL($dsR['title']) : $dsR['title']) . ' [TEMPLATE]';

					foreach($toRecords[0] as $toIndex => $toObj)	{
						if (!strcmp($toObj['datastructure'], $dsID))	{	// If the relation ID matches, render the template object:
							$rTODres = $this->renderTODisplay($toObj, $toRecords, $scope);
							$TOcontent.= '<a name="to-'.$toObj['uid'].'"></a>'.$rTODres['HTML'];
							$indexTO.='
								<tr class="bgColor4">
									<td>&nbsp;&nbsp;&nbsp;</td>
									<td><a href="#to-'.$toObj['uid'].'">'.htmlspecialchars($toObj['title']).'</a></td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td align="center">'.$rTODres['mappingStatus'].'</td>
									<td align="center">'.$rTODres['usage'].'</td>
								</tr>';
							$toCount++;
								// Unset it so we can eventually see what is left:
							unset($toRecords[0][$toIndex]);

							$newPid=-$toObj['uid'];
							$newFileRef = $toObj['fileref'];
							$newTitle = $toObj['title'].' [ALT]';
							$toIdArray[] = $toObj['uid'];
						}
					}

						// For static DS we use the current page id as the PID:
					if (is_null($newPid)) {
						$newPid = t3lib_div::_GP('id');
					}

					if ($newFileRef == '' && $dsR['_STATIC'] && isset($dsR['fileref'])) {
						$newFileRef = $dsR['fileref'];
					}

						// Module may be allowed, but modify may not
					if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
							// New-TO link:
						$TOcontent.= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick(
									'&edit[tx_templavoila_tmplobj]['.$newPid.']=new'.
									'&defVals[tx_templavoila_tmplobj][datastructure]='.rawurlencode($dsID).
									'&defVals[tx_templavoila_tmplobj][title]='.rawurlencode($newTitle).
									'&defVals[tx_templavoila_tmplobj][fileref]='.rawurlencode($newFileRef)
									,$this->doc->backPath)).'">' .
								'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_el.gif','width="11" height="12"').' alt="" class="absmiddle" /> '.$LANG->getLL('center_view_to_new').
								'</a>';
					}
				}

					// Render data structure display
				$rDSDres = $this->renderDataStructureDisplay($dsR, $toIdArray, $scope);
				$content.= '<a name="ds-'.md5($dsID).'"></a>'.$rDSDres['HTML'];
				$index.='
					<tr class="bgColor4-20">
						<td colspan="2"><a href="#ds-'.md5($dsID).'">'.htmlspecialchars($dsR['title']?$dsR['title']:$dsR['path']).'</a></td>
						<td align="center">'.$rDSDres['languageMode'].'</td>
						<td align="center">'.$rDSDres['container'].'</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>';
				if ($indexTO)	{
					$index.=$indexTO;
				}
				$dsCount++;

					// Wrap TO elements in a div-tag and add to content:
				if ($TOcontent)	{
					$content.='<div style="margin-left: 102px;">'.$TOcontent.'</div>';
				}

				$content.='<br />';
			}
		}

		if ($index)	{
			$content = '
				<h4>'.$LANG->getLL('center_list_overview').':</h4>
				<table border="0" cellpadding="0" cellspacing="1">
					<tr class="bgColor5 tableheader">
						<td colspan="2">'.$LANG->getLL('center_list_title').':</td>
						<td>'.$LANG->getLL('center_list_loc').':</td>
						<td>'.$LANG->getLL('center_list_costatus').':</td>
						<td>'.$LANG->getLL('center_list_mapstatus').':</td>
						<td>'.$LANG->getLL('center_list_usage').':</td>
					</tr>
				'.$index.'
				</table>
				<h4>'.$LANG->getLL('center_list_listing').':</h4>'.
				$content;
		}

		return array($content,$dsCount,$toCount);
	}

	/**
	 * Rendering a single data structures information
	 *
	 * @param	array		Data Structure information
	 * @param	array		Array with TO found for this ds
	 * @param	integer		Scope.
	 * @return	string		HTML content
	 */
	function renderDataStructureDisplay($dsR, $toIdArray, $scope)	{
		global $LANG, $BE_USER;

		$tableAttribs = ' border="0" cellpadding="1" cellspacing="1" width="98%" class="lrPadding"';

		$XMLinfo = array();
		$dsID = $dsR['_STATIC'] ? $dsR['path'] : $dsR['uid'];

			// If ds was a true record:
		if (!$dsR['_STATIC'])	{
				// Record icon:
				// Put together the records icon including content sensitive menu link wrapped around it:
			$recordIcon = t3lib_iconWorks::getIconImage('tx_templavoila_datastructure',$dsR,$this->doc->backPath,'class="absmiddle"');
			$recordIcon = $this->doc->wrapClickMenuOnIcon($recordIcon, 'tx_templavoila_datastructure', $dsR['uid'], 1, '&callingScriptId='.rawurlencode($this->doc->scriptID));

				// Preview icon:
			if ($dsR['previewicon'])	{
				$icon = '<img src="'.$this->doc->backPath.'../uploads/tx_templavoila/'.$dsR['previewicon'].'" alt="" />';
			} else {
				$icon = '['.$LANG->getLL('noicon').']';
			}

				// Template status / link:
			$linkUrl = $this->cm1Script.'id='.$this->id.'&table=tx_templavoila_datastructure&uid='.$dsR['uid'].'&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
			$templateStatus  = $this->findDSUsageWithImproperTOs($dsID, $toIdArray, $scope);
			$templateStatus .= '<br/><a href="'.htmlspecialchars($linkUrl).'">[ '.$LANG->getLL('center_view_ds').' ]</a>';

				// Links:
			if ($BE_USER->check('tables_modify', 'tx_templavoila_datastructure')) {
				$lpXML = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_datastructure]['.$dsR['uid'].']=edit&columnsOnly=dataprot',$this->doc->backPath)).'">' .
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit').'" alt="" class="absmiddle" />' .
						'</a>';
				$editLink = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_datastructure]['.$dsR['uid'].']=edit',$this->doc->backPath)).'">' .
						'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit').'" alt="" class="absmiddle" />' .
						'</a>';
				$dsTitle = '<a href="'.htmlspecialchars($linkUrl).'" title="'.$LANG->getLL('center_view_ds').'">'.htmlspecialchars($dsR['title']).'</a>';
			}
			else {
				$lpXML = '';
				$editLink = '';
				$dsTitle = htmlspecialchars($dsR['title']);
			}

				// Format XML if requested (renders VERY VERY slow)
			if ($this->MOD_SETTINGS['set_details'])	{
				$lpXML .= '';

				if ($dsR['dataprot'] && $this->MOD_SETTINGS['set_showDSxml'])	{
					require_once(PATH_t3lib.'class.t3lib_syntaxhl.php');
					$hlObj = t3lib_div::makeInstance('t3lib_syntaxhl');
					$lpXML .= '<pre>'.str_replace(chr(9),'&nbsp;&nbsp;&nbsp;',$hlObj->highLight_DS($dsR['dataprot'])).'</pre>';
				}
			}

			$lpXML .= $dsR['dataprot']
				? t3lib_div::formatSize(strlen($dsR['dataprot'])).'bytes'
				: '&mdash;';

				// Details:
			if ($this->MOD_SETTINGS['set_details'])	{
				$XMLinfo = $this->DSdetails($dsR['dataprot']);
			}

				// Compile info table:
			$content.='
			<table'.$tableAttribs.'>
				<tr class="bgColor5">
					<td colspan="3" style="border-top: 1px solid black;">'.
						$recordIcon.
						$dsTitle.
						$editLink.
						'</td>
				</tr>
				<tr class="bgColor4">
					<td rowspan="'.($this->MOD_SETTINGS['set_details'] ? 4 : 2).'" style="width: 100px; text-align: center;">'.$icon.'</td>
					<td>'.$LANG->getLL('center_view_tmplstatus').':</td>
					<td>'.$templateStatus.'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('center_view_globalproc').'&nbsp;<strong>XML</strong>:</td>
					<td>' . $lpXML . ($this->MOD_SETTINGS['set_details'] ? '<hr />' . $XMLinfo['HTML'] : '') . '</td>
				</tr>'.($this->MOD_SETTINGS['set_details'] ? '
				<tr class="bgColor4">
					<td>'.$LANG->getLL('created').':</td>
					<td>'.t3lib_BEfunc::datetime($dsR['crdate']).' '.$LANG->getLL('by').' ['.$dsR['cruser_id'].']</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('updated').':</td>
					<td>'.t3lib_BEfunc::datetime($dsR['tstamp']).'</td>
				</tr>' : '').'
			</table>';
		}
		else {	// DS was a file:

				// XML file icon:
			$recordIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/fileicons/xml.gif','width="18" height="16"').' alt="" class="absmiddle" />';

				// Preview icon:
			if ($dsR['icon'] && $iconPath = t3lib_div::getFileAbsFileName($dsR['icon']))	{
				$icon = '<img src="'.$this->doc->backPath.'../'.substr($iconPath,strlen(PATH_site)).'" alt="" />';
			}
			else {
				$icon = '['.$LANG->getLL('noicon').']';
			}

			$fileReference = t3lib_div::getFileAbsFileName($dsR['path']);
			if (@is_file($fileReference))	{
				$fileRef = '<a href="'.htmlspecialchars($this->doc->backPath.'../'.substr($fileReference,strlen(PATH_site))).'" target="_blank">'.
							htmlspecialchars($dsR['path']).
							'</a>';

				if ($this->MOD_SETTINGS['set_details'])	{
					$XMLinfo = $this->DSdetails(t3lib_div::getUrl($fileReference));
				}
			}
			else {
				$fileRef = htmlspecialchars($dsR['path']).' ['.$LANG->getLL('filenotfound').'!]';
			}

			$dsRecTitle = (substr($dsR['title'], 0, 4) == 'LLL:' ? $GLOBALS['LANG']->sL($dsR['title']) : $dsR['title']);

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
					<td>XML '.$LANG->getLL('file').':</td>
					<td>'.$fileRef.
						($this->MOD_SETTINGS['set_details'] ? '<hr/>'.$XMLinfo['HTML'] : '').'</td>
				</tr>'.($this->MOD_SETTINGS['set_details'] ? '
				<tr class="bgColor4">
					<td>'.$LANG->getLL('center_view_tmplstatus').':</td>
					<td>'.$this->findDSUsageWithImproperTOs($dsID, $toIdArray, $scope).'</td>
				</tr>' : '').'
			</table>';
		}

		if ($this->MOD_SETTINGS['set_details'])	{
			if ($XMLinfo['referenceFields']) {
				$containerMode = $LANG->getLL('yes');
				if ($XMLinfo['languageMode']==='Separate') {
					$containerMode .= ' ' . $this->doc->icons(3) . $LANG->getLL('center_refs_sep');
				}
				elseif ($XMLinfo['languageMode']==='Inheritance') {
					$containerMode .= ' ' . $this->doc->icons(2);
					if ($XMLinfo['inputFields']) {
						$containerMode .= $LANG->getLL('center_refs_inp');
					}
					else {
						$containerMode .= htmlspecialchars($LANG->getLL('center_refs_no'));
					}
				}
			}
			else {
				$containerMode = $LANG->getLL('no');
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
	 * Render display of a Template Object
	 *
	 * @param	array		Template Object record to render
	 * @param	array		Array of all Template Objects (passed by reference. From here records are unset)
	 * @param	integer		Scope of DS
	 * @param	boolean		If set, the function is asked to render children to template objects (and should not call it self recursively again).
	 * @return	string		HTML content
	 */
	function renderTODisplay($toObj, &$toRecords, $scope, $children=0)	{
		global $LANG, $BE_USER;

			// Put together the records icon including content sensitive menu link wrapped around it:
		$recordIcon = t3lib_iconWorks::getIconImage('tx_templavoila_tmplobj',$toObj,$this->doc->backPath,'class="absmiddle"');
		$recordIcon = $this->doc->wrapClickMenuOnIcon($recordIcon, 'tx_templavoila_tmplobj', $toObj['uid'], 1, '&callingScriptId='.rawurlencode($this->doc->scriptID));

			// Preview icon:
		if ($toObj['previewicon'])	{
			$icon = '<img src="'.$this->doc->backPath.'../uploads/tx_templavoila/'.$toObj['previewicon'].'" alt="" />';
		}
		else {
			$icon = '['.$LANG->getLL('noicon').']';
		}

			// Mapping status / link:
		$linkUrl = $this->cm1Script.'id='.$this->id.'&table=tx_templavoila_tmplobj&uid='.$toObj['uid'].'&_reload_from=1&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));

		$fileReference = t3lib_div::getFileAbsFileName($toObj['fileref']);
		if (@is_file($fileReference))	{
			$this->tFileList[$fileReference]++;

			$fileRef = '<a href="' . htmlspecialchars($this->doc->backPath . '../' . substr($fileReference, strlen(PATH_site))) . '" target="_blank">' . htmlspecialchars($toObj['fileref']) . '</a>';
			$fileMsg = '';
			$fileMtime = filemtime($fileReference);
		}
		else {
			$fileRef = htmlspecialchars($toObj['fileref']);
			$fileMsg = '<div class="typo3-red">'.$LANG->getLL('filenotfound').'</div>';
			$fileMtime = 0;
		}

		$mappingStatus = $mappingStatus_index = '';
		if ($fileMtime && $toObj['fileref_mtime'])	{
			if ($toObj['fileref_md5'] != '') {
				$modified = (@md5_file($fileReference) != $toObj['fileref_md5']);
			}
			else {
				$modified = ($toObj['fileref_mtime'] != $fileMtime);
			}

			if (!$modified)	{
				$mappingStatus  = $mappingStatus_index = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_ok2.gif','width="18" height="16"').' alt="" class="absmiddle" />';
				$mappingStatus .= $LANG->getLL('center_mapping_good') . '<br/>';
			}
			else {
				$mappingStatus  = $mappingStatus_index = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_warning2.gif','width="18" height="16"').' alt="" class="absmiddle" />';
				$mappingStatus .= sprintf($LANG->getLL('center_mapping_changed'), t3lib_BEfunc::datetime($toObj['tstamp'])) . '<br/>';

				$this->setErrorLog($scope,'warning',$mappingStatus.' (TO: "'.$toObj['title'].'")');
			}

				// Module may be allowed, but modify may not
			if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
				$mappingStatus .= '<a href="'.htmlspecialchars($linkUrl).'">[ '.$LANG->getLL('center_view_to').' ]</a> ';
			}
		}
		elseif (!$fileMtime) {
			$mappingStatus  = $mappingStatus_index = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_fatalerror.gif','width="18" height="16"').' alt="" class="absmiddle" />';
			$mappingStatus .= $LANG->getLL('center_mapping_unmapped') . '<br/>';

			$this->setErrorLog($scope,'fatal',$mappingStatus.' (TO: "'.$toObj['title'].'")');

			$mappingStatus .= '<em style="font-size: 0.8em;>'.$LANG->getLL('center_mapping_note').'<br/></em>';

			if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
				$mappingStatus .= '<a href="'.htmlspecialchars($linkUrl).'">[ '.$LANG->getLL('center_view_to_map').' ]</a> ';
			}
		}
		else {
			$mappingStatus = '';

			if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
				$mappingStatus .= '<a href="'.htmlspecialchars($linkUrl).'">[ '.$LANG->getLL('center_view_to_remap').' ]</a> ';
				$mappingStatus .= '<a href="'.htmlspecialchars($linkUrl.'&SET[page]=preview').'">[ '.$LANG->getLL('center_view_to_verify').' ]</a>';
			}
		}

		if (!$BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
			$mappingStatus.='<a href="'.htmlspecialchars($linkUrl.'&SET[page]=preview').'">[ '.$LANG->getLL('center_view_to_preview').' ]</a>';
		}

		if ($this->MOD_SETTINGS['set_details'])	{
			$XMLinfo = $this->DSdetails($toObj['localprocessing']);
		}

			// Links:
		if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
			$lpXML = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_tmplobj]['.$toObj['uid'].']=edit&columnsOnly=localprocessing',$this->doc->backPath)).'">' .
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit').'" alt="" class="absmiddle" />' .
					'</a>';
			$editLink = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_tmplobj]['.$toObj['uid'].']=edit',$this->doc->backPath)).'">' .
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit').'" alt="" class="absmiddle" />' .
					'</a>';
			$toTitle = '<a href="'.htmlspecialchars($linkUrl).'" title="'.$LANG->getLL('center_view_to').'">'.htmlspecialchars($toObj['title']).'</a>';
		}
		else {
			$lpXML = '';
			$editLink = '';
			$toTitle = htmlspecialchars($toObj['title']);
		}

			// Format XML if requested
		if ($this->MOD_SETTINGS['set_details'])	{
			$lpXML .= '';

			if ($toObj['localprocessing'])	{
				require_once(PATH_t3lib.'class.t3lib_syntaxhl.php');
				$hlObj = t3lib_div::makeInstance('t3lib_syntaxhl');
				$lpXML .= '<pre>'.str_replace(chr(9),'&nbsp;&nbsp;&nbsp;',$hlObj->highLight_DS($toObj['localprocessing'])).'</pre>';
			}
		}

		$lpXML .= $toObj['localprocessing']
			? t3lib_div::formatSize(strlen($toObj['localprocessing'])).'bytes'
			: '&mdash;';

			// Compile info table:
		$tableAttribs = ' border="0" cellpadding="1" cellspacing="1" width="98%" style="margin-top: 3px;" class="lrPadding"';

		$fRWTOUres = array();

		if (!$children)	{
			if ($this->MOD_SETTINGS['set_details'])	{
				$fRWTOUres = $this->findRecordsWhereTOUsed($toObj,$scope);
			}

			$content.='
			<table'.$tableAttribs.'>
				<tr class="bgColor4-20">
					<td colspan="3">'.
						$recordIcon.
						$toTitle.
						$editLink.
						'</td>
				</tr>
				<tr class="bgColor4">
					<td rowspan="'.($this->MOD_SETTINGS['set_details'] ? 7 : 4).'" style="width: 100px; text-align: center;">'.$icon.'</td>
					<td>'.$LANG->getLL('fileref').':</td>
					<td>'.$fileRef.$fileMsg.'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('description').':</td>
					<td>'.htmlspecialchars($toObj['description']).'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('center_list_mapstatus').':</td>
					<td>'.$mappingStatus.'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('center_view_localproc').' <strong>XML</strong>:</td>
					<td>' . $lpXML . ($this->MOD_SETTINGS['set_details'] ? '<hr />' . $XMLinfo['HTML'] : '') . '</td>
				</tr>'.($this->MOD_SETTINGS['set_details'] ? '
				<tr class="bgColor4">
					<td>'.$LANG->getLL('used').':</td>
					<td>'.$fRWTOUres['HTML'].'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('created').':</td>
					<td>'.t3lib_BEfunc::datetime($toObj['crdate']).' '.$LANG->getLL('by').' ['.$toObj['cruser_id'].']</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('updated').':</td>
					<td>'.t3lib_BEfunc::datetime($toObj['tstamp']).'</td>
				</tr>' : '').'
			</table>
			';
		} else {
			$content.='
			<table'.$tableAttribs.'>
				<tr class="bgColor4-20">
					<td colspan="3">'.
						$recordIcon.
						$toTitle.
						$editLink.
						'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('fileref').':</td>
					<td>'.$fileRef.$fileMsg.'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('center_list_mapstatus').':</td>
					<td>'.$mappingStatus.'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('rendertype').':</td>
					<td>'.t3lib_BEfunc::getProcessedValue('tx_templavoila_tmplobj', 'rendertype', $toObj['rendertype']).'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('language').':</td>
					<td>'.t3lib_BEfunc::getProcessedValue('tx_templavoila_tmplobj', 'sys_language_uid', $toObj['sys_language_uid']).'</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('center_view_localproc').' <strong>XML</strong>:</td>
					<td>' . $lpXML . ($this->MOD_SETTINGS['set_details'] ? '<hr />' . $XMLinfo['HTML'] : '') . '</td>
				</tr>'.($this->MOD_SETTINGS['set_details'] ? '
				<tr class="bgColor4">
					<td>'.$LANG->getLL('created').':</td>
					<td>'.t3lib_BEfunc::datetime($toObj['crdate']).' '.$LANG->getLL('by').' ['.$toObj['cruser_id'].']</td>
				</tr>
				<tr class="bgColor4">
					<td>'.$LANG->getLL('updated').':</td>
					<td>'.t3lib_BEfunc::datetime($toObj['tstamp']).'</td>
				</tr>' : '').'
			</table>
			';
		}

			// Traverse template objects which are not children of anything:
		if (!$children && is_array($toRecords[$toObj['uid']]))	{
			$TOchildrenContent = '';
			foreach($toRecords[$toObj['uid']] as $toIndex => $childToObj)	{
				$rTODres = $this->renderTODisplay($childToObj, $toRecords, $scope, 1);
				$TOchildrenContent .= $rTODres['HTML'];

					// Unset it so we can eventually see what is left:
				unset($toRecords[$toObj['uid']][$toIndex]);
			}
			$content.='<div style="margin-left: 102px;">'.$TOchildrenContent.'</div>';
		}

			// Return content
		return array(
			'HTML' => $content,
			'mappingStatus' => $mappingStatus_index,
			'usage' => $fRWTOUres['usage']
		);
	}

	/**
	 * Creates listings of pages / content elements where template objects are used.
	 *
	 * @param	array		Template Object record
	 * @param	integer		Scope value. 1) page,  2) content elements
	 * @return	string		HTML table listing usages.
	 */
	function findRecordsWhereTOUsed($toObj,$scope)	{

		$output = array();

		switch ($scope)	{
			case 1:	// PAGES:
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
						(tx_templavoila_to='.intval($toObj['uid']).' AND tx_templavoila_ds='.$GLOBALS['TYPO3_DB']->fullQuoteStr($toObj['datastructure'],'pages').') OR
						(tx_templavoila_next_to='.intval($toObj['uid']).' AND tx_templavoila_next_ds='.$GLOBALS['TYPO3_DB']->fullQuoteStr($toObj['datastructure'],'pages').')
					)'.
						t3lib_BEfunc::deleteClause('pages')
				);

				while(false !== ($pRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
					$path = $this->findRecordsWhereUsed_pid($pRow['uid']);
					if ($path)	{
						$output[]='
							<tr class="bgColor4-20">
								<td nowrap="nowrap">'.
									'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[pages]['.$pRow['uid'].']=edit',$this->doc->backPath)).'" title="Edit">'.
									htmlspecialchars($pRow['uid']).
									'</a></td>
								<td nowrap="nowrap">'.
									htmlspecialchars($pRow['title']).
									'</td>
								<td nowrap="nowrap">'.
									'<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($pRow['uid'],$this->doc->backPath).'return false;').'" title="View">'.
									htmlspecialchars($path).
									'</a></td>
								<td nowrap="nowrap">'.
									htmlspecialchars($pRow['pid']==-1 ? 'Offline version 1.'.$pRow['t3ver_id'].', WS: '.$pRow['t3ver_wsid'] : 'LIVE!').
									'</td>
							</tr>';
					} else {
						$output[]='
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
			case 2:

					// Select Flexible Content Elements:
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'uid,header,pid,t3ver_wsid,t3ver_id',
					'tt_content',
					'CType='.$GLOBALS['TYPO3_DB']->fullQuoteStr('templavoila_pi1','tt_content').
						' AND tx_templavoila_to='.intval($toObj['uid']).
						' AND tx_templavoila_ds='.$GLOBALS['TYPO3_DB']->fullQuoteStr($toObj['datastructure'],'tt_content').
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
				while(false !== ($pRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
					$path = $this->findRecordsWhereUsed_pid($pRow['pid']);
					if ($path)	{
						$output[]='
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
						$output[]='
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
		if (count($output))	{
			if (count($output)>1)	{
				$outputString = 'Used in '.(count($output)-1).' Elements:<table border="0" cellspacing="1" cellpadding="1" class="lrPadding">'.implode('',$output).'</table>';
			} else {
				$outputString = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_warning2.gif','width="18" height="16"').' alt="" class="absmiddle" />No usage!';
				$this->setErrorLog($scope,'warning',$outputString.' (TO: "'.$toObj['title'].'")');
			}
		}

		return array('HTML' => $outputString, 'usage'=>count($output)-1);
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
		global $LANG;

		$output = array();

		switch ($scope)	{
			case 1:	//
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

				while(false !== ($pRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
					$path = $this->findRecordsWhereUsed_pid($pRow['uid']);
					if ($path)	{
						$output[]='
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
						$output[]='
							<tr class="bgColor4-20">
								<td><em>No access</em></td>
								<td>-</td>
							</tr>';
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($res);
			break;
			case 2:

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
				$output[]='
							<tr class="bgColor5 tableheader">
								<td>Header:</td>
								<td>Path:</td>
							</tr>';

					// Elements:
				while(false !== ($pRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
					$path = $this->findRecordsWhereUsed_pid($pRow['pid']);
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
		if (count($output))	{
			if (count($output)>1)	{
				$outputString = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_fatalerror.gif','width="18" height="16"').' alt="" class="absmiddle" />'.
					'Invalid template objects (TOs) on '.(count($output)-1).' '.
						($scope == 1 ? 'pages' :
						($scope == 2 ? 'content elements' :
						               'plugin elements')) .
					':';
				$this->setErrorLog($scope,'fatal',$outputString);

				$outputString.='<table border="0" cellspacing="1" cellpadding="1" class="lrPadding">'.implode('',$output).'</table>';
			} else {
				$outputString = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_ok2.gif','width="18" height="16"').' alt="" class="absmiddle" /> ' . $LANG->getLL('noerrors');
			}
		}

		return $outputString;
	}

	/**
	 * Checks if a PID value is accessible and if so returns the path for the page.
	 * Processing is cached so many calls to the function are OK.
	 *
	 * @param	integer		Page id for check
	 * @return	string		Page path of PID if accessible. otherwise zero.
	 */
	function findRecordsWhereUsed_pid($pid)	{
		if (!isset($this->pidCache[$pid]))	{
			$this->pidCache[$pid] = array();

			$pageinfo = t3lib_BEfunc::readPageAccess($pid,$this->perms_clause);
			$this->pidCache[$pid]['path'] = $pageinfo['_thePath'];
		}

		return $this->pidCache[$pid]['path'];
	}

	/**
	 * Creates a list of all template files used in TOs
	 *
	 * @return	string		HTML table
	 */
	function completeTemplateFileList()	{
		$output = '';
		if (is_array($this->tFileList))	{

			$output='';

				// USED FILES:
			$tRows = array();
			$tRows[] = '
				<tr class="bgColor5 tableheader">
					<td>File</td>
					<td>Usage count:</td>
					<td>New DS/TO?</td>
				</tr>';
			foreach($this->tFileList as $tFile => $count)	{

				$tRows[] = '
					<tr class="bgColor4">
						<td>'.
							'<a href="'.htmlspecialchars($this->doc->backPath.'../'.substr($tFile,strlen(PATH_site))).'" target="_blank">'.
							htmlspecialchars(substr($tFile,strlen(PATH_site))).
							'</a></td>
						<td align="center">'.$count.'</td>
						<td>'.
							'<a href="'.htmlspecialchars($this->cm1Script.'id='.$this->id.'&file='.rawurlencode($tFile)).'&mapElPath=%5BROOT%5D">'.
							htmlspecialchars('Create...').
							'</a></td>
					</tr>';
			}

			if (count($tRows)>1)	{
				$output.= '
				<h3>Used files:</h3>
				<table border="0" cellpadding="1" cellspacing="1" class="lrPadding">
					'.implode('',$tRows).'
				</table>
				';
			}

				// TEMPLATE ARCHIVE:
			if ($this->modTSconfig['properties']['templatePath'])	{
				$path = t3lib_div::getFileAbsFileName($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'].$this->modTSconfig['properties']['templatePath']);
				if (@is_dir($path) && is_array($GLOBALS['FILEMOUNTS']))	{
					foreach($GLOBALS['FILEMOUNTS'] as $mountCfg)	{
						if (t3lib_div::isFirstPartOfStr($path,$mountCfg['path']))	{

							$files = t3lib_div::getFilesInDir($path,'html,htm,tmpl',1);

								// USED FILES:
							$tRows = array();
							$tRows[] = '
								<tr class="bgColor5 tableheader">
									<td>File</td>
									<td>Usage count:</td>
									<td>New DS/TO?</td>
								</tr>';
							foreach($files as $tFile)	{

								$tRows[] = '
									<tr class="bgColor4">
										<td>'.
											'<a href="'.htmlspecialchars($this->doc->backPath.'../'.substr($tFile,strlen(PATH_site))).'" target="_blank">'.
											htmlspecialchars(substr($tFile,strlen(PATH_site))).
											'</a></td>
										<td align="center">'.($this->tFileList[$tFile]?$this->tFileList[$tFile]:'-').'</td>
										<td>'.
											'<a href="'.htmlspecialchars($this->cm1Script.'id='.$this->id.'&file='.rawurlencode($tFile)).'&mapElPath=%5BROOT%5D">'.
											htmlspecialchars('Create...').
											'</a></td>
									</tr>';
							}

							if (count($tRows)>1)	{
								$output.= '
								<h3>Template Archive:</h3>
								<table border="0" cellpadding="1" cellspacing="1" class="lrPadding">
									'.implode('',$tRows).'
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
	 * Stores errors/warnings inside the class.
	 *
	 * @param	string		Scope string, 1=page, 2=ce, _ALL= all errors
	 * @param	string		"fatal" or "warning"
	 * @param	string		HTML content for the error.
	 * @return	void
	 * @see getErrorLog()
	 */
	function setErrorLog($scope,$type,$HTML)	{
		$this->errorsWarnings['_ALL'][$type][] = $this->errorsWarnings[$scope][$type][] = $HTML;
	}

	/**
	 * Returns status for a single scope
	 *
	 * @param	string		Scope string
	 * @return	array		Array with content
	 * @see setErrorLog()
	 */
	function getErrorLog($scope)	{
		$errStat = false;

		if (is_array($this->errorsWarnings[$scope]))	{
			$errStat = array();

			if (is_array($this->errorsWarnings[$scope]['warning']))	{
				$errStat['count'] = count($this->errorsWarnings[$scope]['warning']);
				$errStat['content'] = '<h3>Warnings</h3>'.implode('<hr/>',$this->errorsWarnings[$scope]['warning']);
				$errStat['iconCode'] = 2;
			}

			if (is_array($this->errorsWarnings[$scope]['fatal']))	{
				$errStat['count'] = count($this->errorsWarnings[$scope]['fatal']).($errStat['count'] ? '/'.$errStat['count']:'');
				$errStat['content'].= '<h3>Fatal errors</h3>'.implode('<hr/>',$this->errorsWarnings[$scope]['fatal']);
				$errStat['iconCode'] = 3;
			}
		}

		return $errStat;
	}

	/**
	 * Shows a graphical summary of a array-tree, which suppose was a XML
	 * (but don't need to). This function works recursively.
	 *
	 * @param	[type]		$DStree: an array holding the DSs defined structure
	 * @return	[type]		HTML showing an overview of the DS-structure
	 */
	function renderDSdetails($DStree) {
		global $LANG;

		$HTML = '';

		if (is_array($DStree) && (count($DStree) > 0)) {
			$HTML .= '<dl class="DS-details">';

			foreach ($DStree as $elm => $def) {
				$HTML .= '<dt>';
				$HTML .= ($elm == "meta" ? $LANG->getLL('center_details_conf') : $def['tx_templavoila']['title']);
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
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_fatalerror.gif','width="18" height="16"').' alt="" class="absmiddle" />'
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_ok2.gif','width="18" height="16"').' alt="" class="absmiddle" />'
						) . ' ' . $LANG->getLL('center_details_loc') . '</li>';
					if (isset($def['langChildren'])) $conf .= '<li>' .
						(($def['langChildren'] == 1)
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_ok2.gif','width="18" height="16"').' alt="" class="absmiddle" />'
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_fatalerror.gif','width="18" height="16"').' alt="" class="absmiddle" />'
						) . ' ' . $LANG->getLL('center_details_locc') . '</li>';
					if (isset($def['sheetSelector'])) $conf .= '<li>' .
						(($def['sheetSelector'] != '')
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_ok2.gif','width="18" height="16"').' alt="" class="absmiddle" />'
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_fatalerror.gif','width="18" height="16"').' alt="" class="absmiddle" />'
						) . ' ' . $LANG->getLL('center_details_sheet') .
						(($def['sheetSelector'] != '')
? ' [<em>' . $def['sheetSelector'] . '</em>]'
: ''
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
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_ok2.gif','width="18" height="16"').' alt="" class="absmiddle" />'
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_fatalerror.gif','width="18" height="16"').' alt="" class="absmiddle" />'
						) . ' ' . $LANG->getLL('center_details_integer') . '</li>';
						if (isset($tv['proc']) && isset($tv['proc']['HSC'])) $proc .= '<li>' .
						(($tv['proc']['HSC'] == 1)
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_ok2.gif','width="18" height="16"').' alt="" class="absmiddle" />'
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_fatalerror.gif','width="18" height="16"').' alt="" class="absmiddle" />'
						) . ' ' . $LANG->getLL('center_details_hsc') .
						(($tv['proc']['HSC'] == 1)
? ' [' . $LANG->getLL('center_details_hsc_on') . ']'
: ' [' . $LANG->getLL('center_details_hsc_off') . ']'
						) . '</li>';
						if (isset($tv['proc']) && isset($tv['proc']['stdWrap'])) $proc .= '<li>' .
						(($tv['proc']['stdWrap'] != '')
? '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_ok2.gif','width="18" height="16"').' alt="" class="absmiddle" />'
: '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_fatalerror.gif','width="18" height="16"').' alt="" class="absmiddle" />'
						) . ' ' . $LANG->getLL('center_details_wrap') . '</li>';

						if ($proc != '')
							$HTML .= '<ul class="DS-proc">' . $proc . '</ul>';

						switch ($tv['eType']) {
							case "input":            $preset = 'Plain input field';             $tco = false; break;
							case "input_h":          $preset = 'Header field';                  $tco = false; break;
							case "input_g":          $preset = 'Header field, Graphical';       $tco = false; break;
							case "text":             $preset = 'Text area for bodytext';        $tco = false; break;
							case "rte":              $preset = 'Rich text editor for bodytext'; $tco = false; break;
							case "link":             $preset = 'Link field';                    $tco = false; break;
							case "int":              $preset = 'Integer value';                 $tco = false; break;
							case "image":            $preset = 'Image field';                   $tco = false; break;
							case "imagefixed":       $preset = 'Image field, fixed W+H';        $tco = false; break;
							case "select":           $preset = 'Selector box';                  $tco = false; break;
							case "ce":               $preset = 'Content Elements';              $tco = true;  break;
							case "TypoScriptObject": $preset = 'TypoScript Object Path';        $tco = true;  break;

							case "none":             $preset = 'None';                          $tco = true;  break;
							default:                 $preset = 'Custom [' . $tv['eType'] . ']'; $tco = true;  break;
						}

						switch ($tv['oldStyleColumnNumber']) {
							case 0:  $column = 'Normal [0]';                                   break;
							case 1:  $column = 'Left [1]';                                     break;
							case 2:  $column = 'Right [2]';                                    break;
							case 3:  $column = 'Border [3]';                                   break;
							default: $column = 'Custom [' . $tv['oldStyleColumnNumber'] . ']'; break;
						}

						$notes = '';
						if (($tv['eType'] != "TypoScriptObject") && isset($tv['TypoScriptObjPath']))
							$notes .= '<li>redundant &lt;TypoScriptObjPath&gt;-entry</li>';
						if (($tv['eType'] == "TypoScriptObject") && isset($tv['TypoScript']))
							$notes .= '<li>redundant &lt;TypoScript&gt;-entry</li>';
						if ((($tv['eType'] == "TypoScriptObject") || !isset($tv['TypoScript'])) && isset($tv['TypoScript_constants']))
							$notes .= '<li>redundant &lt;TypoScript_constants&gt;-entry</li>';
						if (isset($tv['proc']) && isset($tv['proc']['int']) && ($tv['proc']['int'] == 1) && isset($tv['proc']['HSC']))
							$notes .= '<li>redundant &lt;proc&gt;&lt;HSC&gt;-entry</li>';
						if (isset($tv['TypoScriptObjPath']) && preg_match('/[^a-zA-Z0-9\.\:_]/', $tv['TypoScriptObjPath']))
							$notes .= '<li><strong>&lt;TypoScriptObjPath&gt;-entry contains illegal characters and/or has multiple lines</strong></li>';

						$tsstats = '';
						if (isset($tv['TypoScript_constants']))
							$tsstats .= '<li>' . count($tv['TypoScript_constants']) . ' TS-constants defined for use in the &lt;TypoScript&gt;-entry</li>';
						if (isset($tv['TypoScript']))
							$tsstats .= '<li>' . (1 + strlen($tv['TypoScript']) - strlen(str_replace("\n", "", $tv['TypoScript']))) . ' lines of TS-code inside the &lt;TypoScript&gt;-entry</li>';
						if (isset($tv['TypoScriptObjPath']))
							$tsstats .= '<li>will utilize the TS-structure <em>' . $tv['TypoScriptObjPath'] . '</em> defined inside the &lt;TypoScriptObjPath&gt;-entry</li>';

						$HTML .= '<dl class="DS-infos">';
						$HTML .= '<dt>Preset used for the element:</dt>';
						$HTML .= '<dd>' . $preset . '</dd>';
						$HTML .= '<dt>Column-positioning:</dt>';
						$HTML .= '<dd>' . $column . '</dd>';
						if ($tsstats != '') {
							$HTML .= '<dt>Typo-Script:</dt>';
							$HTML .= '<dd><ul class="DS-stats">' . $tsstats . '</ul></dd>';
						}
						if ($notes != '') {
							$HTML .= '<dt>Notes:</dt>';
							$HTML .= '<dd><ul class="DS-notes">' . $notes . '</ul></dd>';
						}
						$HTML .= '</dl>';
					}
					else {
						$HTML .= '<p>' . $LANG->getLL('center_details_notv') . '</p>';
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
						$HTML .= '<p>' . $LANG->getLL('center_details_notce') . '</p>';
					}
				}

				/* there are some childs to process ----------------------------- */
				if (isset($def['type']) && ($def['type'] == "array")) {
					if (isset($def['section']))
						;
					if (isset($def['el']))
						$HTML .= $this->renderDSdetails($def['el']);
				}

				$HTML .= '</dd>';
			}

			$HTML .= '</dl>';
		}
		else
			$HTML .= '<p>
					<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/icon_warning2.gif','width="18" height="16"').' alt="" class="absmiddle" />
					' . $LANG->getLL('center_details_nochild') . '
				</p>';

		return $HTML;
	}

	/**
	 * Show meta data part of Data Structure
	 *
	 * @param	[type]		$DSstring: ...
	 * @return	[type]		...
	 */
	function DSdetails($DSstring)	{
		global $LANG;

		$DScontent = t3lib_div::xml2array($DSstring);

		$inputFields = 0;
		$referenceFields = 0;
		$rootelements = 0;
		if (is_array ($DScontent) && is_array($DScontent['ROOT']['el']))	{
			foreach($DScontent['ROOT']['el'] as $elKey => $elCfg)	{
				$rootelements++;
				if (isset($elCfg['TCEforms']))	{
						// Assuming that a reference field for content elements is recognized like this, increment counter. Otherwise assume input field of some sort.
					if ($elCfg['TCEforms']['config']['type']==='group' && $elCfg['TCEforms']['config']['allowed']==='tt_content')	{
						$referenceFields++;
					}
					else {
						$inputFields++;
					}
				}

				if (isset($elCfg['el']))
					$elCfg['el'] = '...';

				unset($elCfg['tx_templavoila']['sample_data']);
				unset($elCfg['tx_templavoila']['tags']);
				unset($elCfg['tx_templavoila']['eType']);

				$rootElementsHTML.='<b>'.$elCfg['tx_templavoila']['title'].'</b>'.t3lib_div::view_array($elCfg);
			}
		}

	/*	$DScontent = array('meta' => $DScontent['meta']);	*/

		$languageMode = '';
		if (is_array($DScontent['meta'])) {
			if ($DScontent['meta']['langDisable'])	{
				$languageMode = $LANG->getLL('disabled');
			} elseif ($DScontent['meta']['langChildren']) {
				$languageMode = $LANG->getLL('inherited');
			} else {
				$languageMode = $LANG->getLL('separated');
			}
		}

		return array(
			'HTML' => /*t3lib_div::view_array($DScontent).'Language Mode => "'.$languageMode.'"<hr/>
						Root Elements = '.$rootelements.', hereof ref/input fields = '.($referenceFields.'/'.$inputFields).'<hr/>
						'.$rootElementsHTML*/ $this->renderDSdetails($DScontent),
			'languageMode' => $languageMode,
			'rootelements' => $rootelements,
			'inputFields' => $inputFields,
			'referenceFields' => $referenceFields
		);
	}















	/******************************
	 *
	 * Wizard for new site
	 *
	 *****************************/

	/**
	 * Wizard overview page - before the wizard is started.
	 *
	 * @return	void
	 */
	function renderNewSiteWizard_overview()	{
		global $BE_USER, $LANG;

		if ($BE_USER->isAdmin())	{

				// Introduction:
			$outputString.= nl2br(htmlspecialchars(trim('
			If you want to start a new website based on the TemplaVoila template engine you can start this wizard which will set up all the boring initial stuff for you.
			You will be taken through these steps:
			- Creation of a new website root, storage folder, sample pages.
			- Creation of the main TemplaVoila template, including mapping of one content area and a main menu.
			- Creation of a backend user and group to manage only that website.

			You should prepare an HTML template before you begin the wizard; simply make a design in HTML and place the HTML file including graphics and stylesheets in a subfolder of "' . $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] . 'templates/" relative to the websites root directory.
			Tip about menus: If you include a main menu in the template, try to place the whole menu inside a container (like <div>, <table> or <tr>) and encapsulate each menu item in a block tag (like <tr>, <td> or <div>). Use A-tags for the links. If you want different designs for normal and active menu elements, design the first menu item as "Active" and the second (and rest) as "Normal", then the wizard might be able to capture the right configuration.
			Tip about stylesheets: The content elements from TYPO3 will be outputted in regular HTML tags like <p>, <h1> to <h6>, <ol> etc. You will prepare yourself well if your stylesheet in the HTML template provides good styles for these standard elements from the start. Then you will have less finetuning to do later.
			')));

				// Checks:
			$missingExt = $this->wizard_checkMissingExtensions();
			$missingConf = $this->wizard_checkConfiguration();
			$missingDir = $this->wizard_checkDirectory();
			if (!$missingExt && !$missingConf)	{
				$outputString.= '
				<br/>
				<br/>
				<input type="submit" value="' . $LANG->getLL('wiz_start') . '!" onclick="'.htmlspecialchars('document.location=\'' . $this->baseScript . 'SET[wiz_step]=1\'; return false;').'" />';
			} else {
				$outputString.= '
				<br/>
				<br/>
				<i>There are some technical problems you have to solve before you can start the wizard! Please see below for details. Solve these problems first and come back.</i>';

			}

				// Add output:
			$this->content.= $this->doc->section($LANG->getLL('wiz_title'),$outputString,0,1);

				// Missing extension warning:
			if ($missingExt)	{
				$this->content.= $this->doc->section('Missing extension!',$missingExt,0,1,3);
			}

				// Missing configuration warning:
			if ($missingConf)	{
				$this->content.= $this->doc->section('Missing configuration!',$missingConf,0,1,3);
			}

				// Missing directory warning:
			if ($missingDir)	{
				$this->content.= $this->doc->section('Missing directory!',$missingDir,0,1,3);
			}
		}
	}

	/**
	 * Running the wizard. Basically branching out to sub functions.
	 * Also gets and saves session data in $this->wizardData
	 *
	 * @return	void
	 */
	function renderNewSiteWizard_run()	{
		global $BE_USER, $LANG;

			// Getting session data:
		$this->wizardData = $BE_USER->getSessionData('tx_templavoila_wizard');

		if ($BE_USER->isAdmin())	{

			$outputString = '';

			switch($this->MOD_SETTINGS['wiz_step'])	{
				case 1:
					$this->wizard_step1();
				break;
				case 2:
					$this->wizard_step2();
				break;
				case 3:
					$this->wizard_step3();
				break;
				case 4:
					$this->wizard_step4();
				break;
				case 5:
					$this->wizard_step5('field_menu');
				break;
				case 5.1:
					$this->wizard_step5('field_submenu');
				break;
				case 6:
					$this->wizard_step6();
				break;
			}

			$outputString.= '<hr/><input type="submit" value="Cancel wizard" onclick="'.htmlspecialchars('document.location=\'' . $this->baseScript . 'SET[wiz_step]=0\'; return false;').'" />';

				// Add output:
			$this->content.= $this->doc->section('',$outputString,0,1);
		}

			// Save session data:
		$BE_USER->setAndSaveSessionData('tx_templavoila_wizard',$this->wizardData);
	}

	/**
	 * Pre-checking for extensions
	 *
	 * @return	string		If string is returned, an error occured.
	 */
	function wizard_checkMissingExtensions()	{

		$outputString.='Before the wizard can run some extensions are required to be installed. Below you will see the which extensions are required and which are not available at this moment. Please go to the Extension Manager and install these first.';

			// Create extension status:
		$checkExtensions = explode(',','css_styled_content,impexp');
		$missingExtensions = FALSE;

		$tRows = array();
		$tRows[] = '<tr class="tableheader bgColor5">
			<td>Extension Key:</td>
			<td>Installed?</td>
		</tr>';

		foreach($checkExtensions as $extKey)	{
			$tRows[] = '<tr class="bgColor4">
				<td>'.$extKey.'</td>
				<td align="center">'.(t3lib_extMgm::isLoaded($extKey) ? 'Yes' : '<span class="typo3-red">No!</span>').'</td>
			</tr>';

			if (!t3lib_extMgm::isLoaded($extKey))	$missingExtensions = TRUE;
		}

		$outputString.='<table border="0" cellpadding="1" cellspacing="1">'.implode('',$tRows).'</table>';

			// If no extensions are missing, simply go to step two:
		if ($missingExtensions)		{
			return $outputString;
		}
	}

	/**
	 * Pre-checking for TemplaVoila configuration
	 *
	 * @return	string		If string is returned, an error occured.
	 */
	function wizard_checkConfiguration()	{

		$TVconfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['templavoila']);
	}

	/**
	 * Pre-checking for directory of extensions.
	 *
	 * @return	string		If string is returned, an error occured.
	 */
	function wizard_checkDirectory()	{

		if (!@is_dir(PATH_site.$this->templatesDir))	{
			return
				nl2br('The directory "'.$this->templatesDir.'" (relative to the website root) does not exist! This is where you must place your HTML templates. Please create that directory <u>before you start the wizard</u>. In order to do so, follow these directions:

			- Go to the module File > Filelist
			- Click the icon of the "' . $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] . '" root and select "Create" from the context menu.
			- Enter the name "templates" of the folder and press the "Create" button.
			- Return to this wizard
			');
		}

		return false;
	}

	/**
	 * Wizard Step 1: Selecting template file.
	 *
	 * @return	void
	 */
	function wizard_step1()	{

		if (@is_dir(PATH_site.$this->templatesDir))	{

			$this->wizardData = array();

			$outputString.=nl2br('The first step is to select the HTML file you want to base the new website design on. Below you see a list of HTML files found in the folder "'.$this->templatesDir.'". Click the "Preview"-link to see what the file looks like and when the right template is found, just click the "Choose as template"-link in order to proceed.
				If the list of files is empty you must now copy the HTML file you want to use as a template into the template folder. When you have done that, press the refresh button to refresh the list.<br/>');

				// Get all HTML files:
			$fileArr = t3lib_div::getAllFilesAndFoldersInPath(array(),PATH_site.$this->templatesDir,'html,htm',0,1);
			$fileArr = t3lib_div::removePrefixPathFromList($fileArr,PATH_site);

				// Prepare header:
			$tRows = array();
			$tRows[] = '<tr class="tableheader bgColor5">
				<td>Path:</td>
				<td>Usage:</td>
				<td>Action:</td>
			</tr>';

				// Traverse available template files:
			foreach($fileArr as $file)	{

					// Has been used:
				$tosForTemplate = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid',
					'tx_templavoila_tmplobj',
					'fileref='.$GLOBALS['TYPO3_DB']->fullQuoteStr($file, 'tx_templavoila_tmplobj').
						t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj')
					);

					// Preview link
				$onClick = 'vHWin=window.open(\''.$this->doc->backPath.'../'.$file.'\',\'tvTemplatePreview\',\'status=1,menubar=1,scrollbars=1,location=1\');vHWin.focus();return false;';

					// Make row:
				$tRows[] = '<tr class="bgColor4">
					<td>'.htmlspecialchars($file).'</td>
					<td>'.(count($tosForTemplate) ? 'Used '.count($tosForTemplate).' times' : 'Not used yet').'</td>
					<td>'.
						'<a href="#" onclick="'.htmlspecialchars($onClick).'">[Preview first]</a> '.
						'<a href="'.htmlspecialchars($this->baseScript . 'SET[wiz_step]=2&CFG[file]=' . rawurlencode($file)) . '">[Choose as Template]</a> '.
						'</td>
				</tr>';
			}
			$outputString.= '<table border="0" cellpadding="1" cellspacing="1" class="lrPadding">'.implode('',$tRows).'</table>';

				// Refresh button:
			$outputString.= '<br/><input type="submit" value="Refresh" onclick="'.htmlspecialchars('document.location=\'' . $this->baseScript . 'SET[wiz_step]=1\'; return false;').'" />';

				// Add output:
			$this->content.= $this->doc->section('Step 1: Select the template HTML file',$outputString,0,1);
		}
		else {
			$this->content .= $this->doc->section('TemplaVoila wizard error',$this->templatesDir.' is not a directory! Please, create it before starting this wizard.',0,1);
		}
	}

	/**
	 * Step 2: Enter default values:
	 *
	 * @return	void
	 */
	function wizard_step2()	{

			// Save session data with filename:
		$cfg = t3lib_div::_GET('CFG');
		if ($cfg['file'] && t3lib_div::getFileAbsFileName($cfg['file']))	{
			$this->wizardData['file'] = $cfg['file'];
		}

			// Show selected template file:
		if ($this->wizardData['file'])	{
			$outputString.= nl2br('The template file "'.htmlspecialchars($this->wizardData['file']).'" is now selected: ');
			$outputString.= '<br/><iframe src="'.htmlspecialchars($this->doc->backPath.'../'.$this->wizardData['file']).'" width="640" height="300"></iframe>';

				// Enter default data:
			$outputString.='
				<br/><br/><br/>
				Next, you should enter default values for the new website. With this basic set of information we are ready to create the initial website structure!<br/>
	<br/>
				<b>Name of the site:</b><br/>
				(Required)<br/>
				This value is shown in the browsers title bar and will be the default name of the first page in the page tree.<br/>
				<input type="text" name="CFG[sitetitle]" value="'.htmlspecialchars($this->wizardData['sitetitle']).'" /><br/>
	<br/>
				<b>URL of the website:</b><br/>
				(Optional)<br/>
				If you know the URL of the website already please enter it here, eg. "www.mydomain.com".<br/>
				<input type="text" name="CFG[siteurl]" value="'.htmlspecialchars($this->wizardData['siteurl']).'" /><br/>
	<br/>
				<b>Editor username</b><br/>
				(Required)<br/>
				Enter the username of a new backend user/group who will be able to edit the pages on the new website. (Password will be "password" by default, make sure to change that!)<br/>
				<input type="text" name="CFG[username]" value="'.htmlspecialchars($this->wizardData['username']).'" /><br/>
	<br/>
				<input type="hidden" name="SET[wiz_step]" value="3" />
				<input type="submit" name="_create_site" value="Create new site" />
			';
		}
		else {
			$outputString.= 'No template file found!?';
		}

			// Add output:
		$this->content.= $this->doc->section('Step 2: Enter default values for new site',$outputString,0,1);
	}

	/**
	 * Step 3: Begin template mapping
	 *
	 * @return	void
	 */
	function wizard_step3()	{

			// Save session data with filename:
		$cfg = t3lib_div::_POST('CFG');
		if (isset($cfg['sitetitle']))	{
			$this->wizardData['sitetitle'] = trim($cfg['sitetitle']);
		}
		if (isset($cfg['siteurl']))	{
			$this->wizardData['siteurl'] = trim($cfg['siteurl']);
		}
		if (isset($cfg['username']))	{
			$this->wizardData['username'] = trim($cfg['username']);
		}

			// If the create-site button WAS clicked:
		if (t3lib_div::_POST('_create_site'))	{

				// Show selected template file:
			if ($this->wizardData['file'] && $this->wizardData['sitetitle'] && $this->wizardData['username'])	{

					// DO import:
				$import = $this->getImportObj();
				$inFile = t3lib_extMgm::extPath('templavoila').'mod2/new_tv_site.xml';
				if (@is_file($inFile) && $import->loadFile($inFile,1))	{

					$import->importData($this->importPageUid);

						// Update various fields (the index values, eg. the "1" in "$import->import_mapId['pages'][1]]..." are the UIDs of the original records from the import file!)
					$data = array();
					$data['pages'][t3lib_BEfunc::wsMapId('pages',$import->import_mapId['pages'][1])]['title'] = $this->wizardData['sitetitle'];
					$data['sys_template'][t3lib_BEfunc::wsMapId('sys_template',$import->import_mapId['sys_template'][1])]['title'] = 'Main template: '.$this->wizardData['sitetitle'];
					$data['sys_template'][t3lib_BEfunc::wsMapId('sys_template',$import->import_mapId['sys_template'][1])]['sitetitle'] = $this->wizardData['sitetitle'];
					$data['tx_templavoila_tmplobj'][t3lib_BEfunc::wsMapId('tx_templavoila_tmplobj',$import->import_mapId['tx_templavoila_tmplobj'][1])]['fileref'] = $this->wizardData['file'];
					$data['tx_templavoila_tmplobj'][t3lib_BEfunc::wsMapId('tx_templavoila_tmplobj',$import->import_mapId['tx_templavoila_tmplobj'][1])]['templatemapping'] = serialize(
						array(
							'MappingInfo' => array(
								'ROOT' => array(
									'MAP_EL' => 'body[1]/INNER'
								)
							),
							'MappingInfo_head' => array(
								'headElementPaths' => array('link[1]','link[2]','link[3]','style[1]','style[2]','style[3]'),
								'addBodyTag' => 1
							)
						)
					);

						// Update user settings
					$newUserID = t3lib_BEfunc::wsMapId('be_users',$import->import_mapId['be_users'][2]);
					$newGroupID = t3lib_BEfunc::wsMapId('be_groups',$import->import_mapId['be_groups'][1]);

					$data['be_users'][$newUserID]['username'] = $this->wizardData['username'];
					$data['be_groups'][$newGroupID]['title'] = $this->wizardData['username'];

					foreach($import->import_mapId['pages'] as $newID)	{
						$data['pages'][$newID]['perms_userid'] = $newUserID;
						$data['pages'][$newID]['perms_groupid'] = $newGroupID;
					}

						// Set URL if applicable:
					if (strlen($this->wizardData['siteurl']))	{
						$data['sys_domain']['NEW']['pid'] = t3lib_BEfunc::wsMapId('pages',$import->import_mapId['pages'][1]);
						$data['sys_domain']['NEW']['domainName'] = $this->wizardData['siteurl'];
					}

						// Execute changes:
					$tce = t3lib_div::makeInstance('t3lib_TCEmain');
					$tce->stripslashes_values = 0;
					$tce->dontProcessTransformations = 1;
					$tce->start($data,Array());
					$tce->process_datamap();

						// Setting environment:
					$this->wizardData['rootPageId'] = $import->import_mapId['pages'][1];
					$this->wizardData['templateObjectId'] = t3lib_BEfunc::wsMapId('tx_templavoila_tmplobj',$import->import_mapId['tx_templavoila_tmplobj'][1]);
					$this->wizardData['typoScriptTemplateID'] = t3lib_BEfunc::wsMapId('sys_template',$import->import_mapId['sys_template'][1]);

					t3lib_BEfunc::getSetUpdateSignal('updatePageTree');

					$outputString.= 'New site has been created and adapted. <hr/>';
				}
			}
			else {
				$outputString.= 'Error happened: Either you did not specify a website name or username in the previous form!';
			}
		}

			// If a template Object id was found, continue with mapping:
		if ($this->wizardData['templateObjectId'])	{
			$url = $this->cm1Script.'id='.$this->id.'&table=tx_templavoila_tmplobj&uid='.$this->wizardData['templateObjectId'].'&SET[selectHeaderContent]=0&_reload_from=1&returnUrl='.rawurlencode($this->mod2Script.'SET[wiz_step]=4');

			$outputString.= '
				You are now ready to point out at which position in the HTML code to insert the TYPO3 generated page content and the main menu. This process is called "mapping".<br/>
				The process of mapping is shown with this little animation. Please study it closely to understand the flow, then click the button below to start the mapping process on your own. Complete the mapping process by pressing "Save and Return".<br/>
				<br/>
				<img src="mapbody_animation.gif" style="border: 2px black solid;" alt=""><br/>
				<br/>
				<br/><input type="submit" value="Start the mapping process" onclick="'.htmlspecialchars('document.location=\''.$url.'\'; return false;').'" />
			';
		}

			// Add output:
		$this->content.= $this->doc->section('Step 3: Begin mapping',$outputString,0,1);
	}

	/**
	 * Step 4: Select HTML header parts.
	 *
	 * @return	void
	 */
	function wizard_step4()	{
		$url = $this->cm1Script.'id='.$this->id.'&table=tx_templavoila_tmplobj&uid='.$this->wizardData['templateObjectId'].'&SET[selectHeaderContent]=1&_reload_from=1&returnUrl='.rawurlencode($this->mod2Script.'SET[wiz_step]=5');
		$outputString.= '
			Finally you also have to select which parts of the HTML header you want to include. For instance it is important that you select all sections with CSS styles in order to preserve the correct visual appearance of your website.<br/>
			You can also select the body-tag of the template if you want to use the original body-tag.<br/>
			This animations shows an example of this process:
			<br/>
			<img src="maphead_animation.gif" style="border: 2px black solid;" alt=""><br/>
			<br/>
			<br/><input type="submit" value="Select HTML header parts" onclick="'.htmlspecialchars('document.location=\''.$url.'\'; return false;').'" />
			';

			// Add output:
		$this->content.= $this->doc->section('Step 4: Select HTML header parts',$outputString,0,1);
	}

	/**
	 * Step 5: Create dynamic menu
	 *
	 * @param	string		Type of menu (main or sub), values: "field_menu" or "field_submenu"
	 * @return	void
	 */
	function wizard_step5($menuField)	{

		$menuPart = $this->getMenuDefaultCode($menuField);
		$menuType = $menuField === 'field_menu' ? 'mainMenu' : 'subMenu';
		$menuTypeText = $menuField === 'field_menu' ? 'main menu' : 'sub menu';
		$menuTypeLetter = $menuField === 'field_menu' ? 'a' : 'b';
		$menuTypeNextStep = $menuField === 'field_menu' ? 5.1 : 6;
		$menuTypeEntryLevel = $menuField === 'field_menu' ? 0 : 1;

		$this->saveMenuCode();

		if (strlen($menuPart))	{

				// Main message:
			$outputString.= '
				The basics of your website should be working now. However the '.$menuTypeText.' still needs to be configured so that TYPO3 automatically generates a menu reflecting the pages in the page tree. This process involves configuration of the TypoScript object path, "lib.'.$menuType.'". This is a technical job which requires that you know about TypoScript if you want it 100% customized.<br/>
				To assist you getting started with the '.$menuTypeText.' this wizard will try to analyse the menu found inside the template file. If the menu was created of a series of repetitive block tags containing A-tags then there is a good chance this will succeed. You can see the result below.
			';

				// Start up HTML parser:
			require_once(PATH_t3lib.'class.t3lib_parsehtml.php');
			$htmlParser = t3lib_div::makeinstance('t3lib_parsehtml');

				// Parse into blocks
			$parts = $htmlParser->splitIntoBlock('td,tr,table,a,div,span,ol,ul,li,p,h1,h2,h3,h4,h5',$menuPart,1);

				// If it turns out to be only a single large block we expect it to be a container for the menu item. Therefore we will parse the next level and expect that to be menu items:
			if (count($parts)==3)	{
				$totalWrap = array();
				$totalWrap['before'] = $parts[0].$htmlParser->getFirstTag($parts[1]);
				$totalWrap['after'] = '</'.strtolower($htmlParser->getFirstTagName($parts[1])).'>'.$parts[2];

				$parts = $htmlParser->splitIntoBlock('td,tr,table,a,div,span,ol,ul,li,p,h1,h2,h3,h4,h5',$htmlParser->removeFirstAndLastTag($parts[1]),1);
			} else {
				$totalWrap = array();
			}

			$menuPart_HTML = trim($totalWrap['before']).chr(10).implode(chr(10),$parts).chr(10).trim($totalWrap['after']);

				// Traverse expected menu items:
			$menuWraps = array();
			$GMENU = FALSE;
			$mouseOver = FALSE;
			$key = '';

			foreach($parts as $k => $value)	{
				if ($k%2)	{	// Only expecting inner elements to be of use:

					$linkTag = $htmlParser->splitIntoBlock('a',$value,1);
					if ($linkTag[1])	{
						$newValue = array();
						$attribs = $htmlParser->get_tag_attributes($htmlParser->getFirstTag($linkTag[1]),1);
						$newValue['A-class'] = $attribs[0]['class'];
						if ($attribs[0]['onmouseover'] && $attribs[0]['onmouseout'])	$mouseOver = TRUE;

							// Check if the complete content is an image - then make GMENU!
						$linkContent = trim($htmlParser->removeFirstAndLastTag($linkTag[1]));
						if (eregi('^<img[^>]*>$',$linkContent))	{
							$GMENU = TRUE;
							$attribs = $htmlParser->get_tag_attributes($linkContent,1);
							$newValue['I-class'] = $attribs[0]['class'];
							$newValue['I-width'] = $attribs[0]['width'];
							$newValue['I-height'] = $attribs[0]['height'];

							$filePath = t3lib_div::getFileAbsFileName(t3lib_div::resolveBackPath(PATH_site.$attribs[0]['src']));
							if (@is_file($filePath))	{
								$newValue['backColorGuess'] = $this->getBackgroundColor($filePath);
							} else $newValue['backColorGuess'] = '';

							if ($attribs[0]['onmouseover'] && $attribs[0]['onmouseout'])	$mouseOver = TRUE;
						}

						$linkTag[1] = '|';
						$newValue['wrap'] = ereg_replace('['.chr(10).chr(13).']*','',implode('',$linkTag));

						$md5Base = $newValue;
						unset($md5Base['I-width']);
						unset($md5Base['I-height']);
						$md5Base = serialize($md5Base);
						$md5Base = ereg_replace('name=["\'][^"\']*["\']','',$md5Base);
						$md5Base = ereg_replace('id=["\'][^"\']*["\']','',$md5Base);
						$md5Base = ereg_replace('[:space:]','',$md5Base);
						$key = md5($md5Base);

						if (!isset($menuWraps[$key]))	{	// Only if not yet set, set it (so it only gets set once and the first time!)
							$menuWraps[$key] = $newValue;
						} else {	// To prevent from writing values in the "} elseif ($key) {" below, we clear the key:
							$key = '';
						}
					} elseif ($key) {

							// Add this to the previous wrap:
						$menuWraps[$key]['bulletwrap'].= str_replace('|','&#'.ord('|').';',ereg_replace('['.chr(10).chr(13).']*','',$value));
					}
				}
			}

				// Construct TypoScript for the menu:
			reset($menuWraps);
			if (count($menuWraps)==1)	{
				$menu_normal = current($menuWraps);
				$menu_active = next($menuWraps);
			} else { 	// If more than two, then the first is the active one.
				$menu_active = current($menuWraps);
				$menu_normal = next($menuWraps);
			}

#debug($menuWraps);
#debug($mouseOver);
			if ($GMENU)	{
				$typoScript = '
lib.'.$menuType.' = HMENU
lib.'.$menuType.'.entryLevel = '.$menuTypeEntryLevel.'
'.(count($totalWrap) ? 'lib.'.$menuType.'.wrap = '.ereg_replace('['.chr(10).chr(13).']','',implode('|',$totalWrap)) : '').'
lib.'.$menuType.'.1 = GMENU
lib.'.$menuType.'.1.NO.wrap = '.$this->makeWrap($menu_normal).
	($menu_normal['I-class'] ? '
lib.'.$menuType.'.1.NO.imgParams = class="'.htmlspecialchars($menu_normal['I-class']).'" ' : '').'
lib.'.$menuType.'.1.NO {
	XY = '.($menu_normal['I-width']?$menu_normal['I-width']:150).','.($menu_normal['I-height']?$menu_normal['I-height']:25).'
	backColor = '.($menu_normal['backColorGuess'] ? $menu_normal['backColorGuess'] : '#FFFFFF').'
	10 = TEXT
	10.text.field = title // nav_title
	10.fontColor = #333333
	10.fontSize = 12
	10.offset = 15,15
	10.fontFace = t3lib/fonts/nimbus.ttf
}
	';

				if ($mouseOver)	{
					$typoScript.= '
lib.'.$menuType.'.1.RO < lib.'.$menuType.'.1.NO
lib.'.$menuType.'.1.RO = 1
lib.'.$menuType.'.1.RO {
	backColor = '.t3lib_div::modifyHTMLColorAll(($menu_normal['backColorGuess'] ? $menu_normal['backColorGuess'] : '#FFFFFF'),-20).'
	10.fontColor = red
}
			';

				}
				if (is_array($menu_active))	{
					$typoScript.= '
lib.'.$menuType.'.1.ACT < lib.'.$menuType.'.1.NO
lib.'.$menuType.'.1.ACT = 1
lib.'.$menuType.'.1.ACT.wrap = '.$this->makeWrap($menu_active).
	($menu_active['I-class'] ? '
lib.'.$menuType.'.1.ACT.imgParams = class="'.htmlspecialchars($menu_active['I-class']).'" ' : '').'
lib.'.$menuType.'.1.ACT {
	backColor = '.($menu_active['backColorGuess'] ? $menu_active['backColorGuess'] : '#FFFFFF').'
}
			';
				}

			} else {
				$typoScript = '
lib.'.$menuType.' = HMENU
lib.'.$menuType.'.entryLevel = '.$menuTypeEntryLevel.'
'.(count($totalWrap) ? 'lib.'.$menuType.'.wrap = '.ereg_replace('['.chr(10).chr(13).']','',implode('|',$totalWrap)) : '').'
lib.'.$menuType.'.1 = TMENU
lib.'.$menuType.'.1.NO {
	allWrap = '.$this->makeWrap($menu_normal).
	($menu_normal['A-class'] ? '
	ATagParams = class="'.htmlspecialchars($menu_normal['A-class']).'"' : '').'
}
	';

				if (is_array($menu_active))	{
					$typoScript.= '
lib.'.$menuType.'.1.ACT = 1
lib.'.$menuType.'.1.ACT {
	allWrap = '.$this->makeWrap($menu_active).
	($menu_active['A-class'] ? '
	ATagParams = class="'.htmlspecialchars($menu_active['A-class']).'"' : '').'
}
			';
				}
			}


				// Output:

				// HTML defaults:
			$outputString.='
			<br/>
			<br/>
			Here is the HTML code from the Template that encapsulated the menu:
			<hr/>
			<pre>'.htmlspecialchars($menuPart_HTML).'</pre>
			<hr/>
			<br/>';


			if (trim($menu_normal['wrap']) != '|')	{
				$outputString.= 'It seems that the menu consists of menu items encapsulated with "'.htmlspecialchars(str_replace('|',' ... ',$menu_normal['wrap'])).'". ';
			} else {
				$outputString.= 'It seems that the menu consists of menu items not wrapped in any block tags except A-tags. ';
			}
			if (count($totalWrap))	{
				$outputString.='It also seems that the whole menu is wrapped in this tag: "'.htmlspecialchars(str_replace('|',' ... ',implode('|',$totalWrap))).'". ';
			}
			if ($menu_normal['bulletwrap'])	{
				$outputString.='Between the menu elements there seems to be a visual division element with this HTML code: "'.htmlspecialchars($menu_normal['bulletwrap']).'". That will be added between each element as well. ';
			}
			if ($GMENU)	{
				$outputString.='The menu items were detected to be images - TYPO3 will try to generate graphical menu items automatically (GMENU). You will need to customize the look of these before it will match the originals! ';
			}
			if ($mouseOver)	{
				$outputString.='It seems like a mouseover functionality has been applied previously, so roll-over effect has been applied as well.  ';
			}

			$outputString.='<br/><br/>';
			$outputString.='Based on this analysis, this TypoScript configuration for the menu is suggested:
			<br/><br/>';
			$outputString.='<hr/>'.$this->syntaxHLTypoScript($typoScript).'<hr/><br/>';


			$outputString.='You can fine tune the configuration here before it is saved:<br/>';
			$outputString.='<textarea name="CFG[menuCode]"'.$GLOBALS['TBE_TEMPLATE']->formWidthText().' rows="10">'.t3lib_div::formatForTextarea($typoScript).'</textarea><br/><br/>';
			$outputString.='<input type="hidden" name="SET[wiz_step]" value="'.$menuTypeNextStep.'" />';
			$outputString.='<input type="submit" name="_" value="Write '.$menuTypeText.' TypoScript code" />';
		} else {
			$outputString.= '
				The basics of your website should be working now. It seems like you did not map the '.$menuTypeText.' to any element, so the menu configuration process will be skipped.<br/>
			';
			$outputString.='<input type="hidden" name="SET[wiz_step]" value="'.$menuTypeNextStep.'" />';
			$outputString.='<input type="submit" name="_" value="Next..." />';
		}

			// Add output:
		$this->content.= $this->doc->section('Step 5'.$menuTypeLetter.': Trying to create dynamic menu',$outputString,0,1);

	}

	/**
	 * Step 6: Done.
	 *
	 * @return	void
	 */
	function wizard_step6()	{

		$this->saveMenuCode();


		$outputString.= '<b>Congratulations!</b> You have completed the initial creation of a new website in TYPO3 based on the TemplaVoila engine. After you click the "Finish" button you can go to the Web>Page module to edit your pages!

		<br/>
		<br/>
		<input type="submit" value="Finish Wizard!" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($this->wizardData['rootPageId'],$this->doc->backPath).'document.location=\'' . $this->baseScript . 'SET[wiz_step]=0\'; return false;').'" />
		';

			// Add output:
		$this->content.= $this->doc->section('Step 6: Done',$outputString,0,1);
	}

	/**
	 * Initialize the import-engine
	 *
	 * @return	object		Returns object ready to import the import-file used to create the basic site!
	 */
	function getImportObj()	{
		global $TYPO3_CONF_VARS;

		require_once(t3lib_extMgm::extPath('impexp').'class.tx_impexp.php');

		$import = t3lib_div::makeInstance('tx_impexp');
		$import->init(0,'import');
		$import->enableLogging = TRUE;

		return $import;
	}

	/**
	 * Syntax Highlighting of TypoScript code
	 *
	 * @param	string		String of TypoScript code
	 * @return	string		HTML content with it highlighted.
	 */
	function syntaxHLTypoScript($v)	{
		require_once(PATH_t3lib.'class.t3lib_tsparser_ext.php');

		$tsparser = t3lib_div::makeInstance('t3lib_TSparser');
		$tsparser->lineNumberOffset=0;
		$TScontent = $tsparser->doSyntaxHighlight(trim($v).chr(10),'',1);

		return $TScontent;
	}

	/**
	 * Produce WRAP value
	 *
	 * @param	array		menuItemSuggestion configuration
	 * @return	string		Wrap for TypoScript
	 */
	function makeWrap($cfg)	{
		if (!$cfg['bulletwrap'])	{
			$wrap = $cfg['wrap'];
		} else {
			$wrap = $cfg['wrap'].'  |*|  '.$cfg['bulletwrap'].$cfg['wrap'];
		}

		return ereg_replace('['.chr(10).chr(13).chr(9).']','',$wrap);
	}

	/**
	 * Returns the code that the menu was mapped to in the HTML
	 *
	 * @param	string		"Field" from Data structure, either "field_menu" or "field_submenu"
	 * @return	string
	 */
	function getMenuDefaultCode($field)	{
			// Select template record and extract menu HTML content
		$toRec = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj',$this->wizardData['templateObjectId']);
		$tMapping = unserialize($toRec['templatemapping']);
		return $tMapping['MappingData_cached']['cArray'][$field];
	}

	/**
	 * Saves the menu TypoScript code
	 *
	 * @return	void
	 */
	function saveMenuCode()	{

			// Save menu code to template record:
		$cfg = t3lib_div::_POST('CFG');
		if (isset($cfg['menuCode']))	{

				// Get template record:
			$TSrecord = t3lib_BEfunc::getRecord('sys_template',$this->wizardData['typoScriptTemplateID']);
			if (is_array($TSrecord))	{
				$data['sys_template'][$TSrecord['uid']]['config'] = '

## Menu [Begin]
'.trim($cfg['menuCode']).'
## Menu [End]



'.$TSrecord['config'];

					// Execute changes:
				global $TYPO3_CONF_VARS;

				require_once(PATH_t3lib.'class.t3lib_tcemain.php');
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->stripslashes_values = 0;
				$tce->dontProcessTransformations = 1;
				$tce->start($data,Array());
				$tce->process_datamap();
			}
		}
	}

	/**
	 * Tries to fetch the background color of a GIF or PNG image.
	 *
	 * @param	string		Filepath (absolute) of the image (must exist)
	 * @return	string		HTML hex color code, if any.
	 */
	function getBackgroundColor($filePath)	{

		if (substr($filePath,-4)=='.gif' && function_exists('imagecreatefromgif'))	{
			$im = @imagecreatefromgif($filePath);
		} elseif (substr($filePath,-4)=='.png' && function_exists('imagecreatefrompng'))	{
			$im = @imagecreatefrompng($filePath);
		}

		if ($im)	{
			$values = imagecolorsforindex($im, imagecolorat($im, 3, 3));
			$color = '#'.substr('00'.dechex($values['red']),-2).
						substr('00'.dechex($values['green']),-2).
						substr('00'.dechex($values['blue']),-2);
			return $color;
		}
		return false;
	}
}

if (!function_exists('md5_file')) {
	function md5_file($file, $raw = false) {
		return md5(file_get_contents($file), $raw);
	}
}

// Make instance:
//$SOBE = t3lib_div::makeInstance('tx_templavoila_module2');
//$SOBE->init();
//$SOBE->main();
//$SOBE->printContent();

/**
 * Module 'TemplaVoila' for the 'templavoila' extension.
 * Modern integrated style
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class tx_templavoila_module2_integral extends tx_templavoila_module2 {

	var $templatesDir;

		// Internal, dynamic:
	var $be_user_Array;
	var $CALC_PERMS;
	var $pageinfo;

	function init() {
		parent::init();
		$this->templatesDir = $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] . 'templates/';
	}

	/**
	 * Preparing menu content
	 *
	 * @return	void
	 */
	function menuConfig() {
		global $BE_USER, $LANG;

		parent::menuConfig();

		$this->MOD_MENU['page'] =
			array(
				'pagetmpl'  => $LANG->getLL('center_tab_pagetmpl', 1),
				'fcetmpl'   => $LANG->getLL('center_tab_fcetmpl', 1),
				'other'     => $LANG->getLL('center_tab_other', 1),
				'unknown'   => $LANG->getLL('center_tab_unknown', 1),
				'lost'      => $LANG->getLL('center_tab_lost', 1),
				'tmplfiles' => $LANG->getLL('center_tab_tmplfiles', 1),
				'errors'    => $LANG->getLL('center_tab_errors', 1)
			);

			// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.'.$this->MCONF['name']);

			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::GPvar('SET'), $this->MCONF['name']);
	}

	/**
	 * Creates additional parameters which are used for linking to the current page while editing it
	 *
	 * @return	string		parameters
	 * @access public
	 */
	function link_getParameters()	{
		$output =
			'id='.$this->id;

		return $output;
	}

	/**
	 * Returns a selector box "function menu" for a module
	 * Requires the JS function jumpToUrl() to be available
	 * See Inside TYPO3 for details about how to use / make Function menus
	 * Usage: 50
	 *
	 * @param	mixed		$id is the "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
	 * @param	string		$elementName it the form elements name, probably something like "SET[...]"
	 * @param	string		$currentValue is the value to be selected currently.
	 * @param	array		$menuItems is an array with the menu items for the selector box
	 * @param	string		$script is the script to send the &id to, if empty it's automatically found
	 * @param	string		$addParams is additional parameters to pass to the script.
	 * @return	string		HTML code for selector box
	 */
	function getFuncMenuNoHSC($mainParams, $elementName, $currentValue, $menuItems, $script = '', $addparams = '') {
		if (is_array($menuItems)) {
			if (!is_array($mainParams)) {
				$mainParams = array('id' => $mainParams);
			}
			$mainParams = t3lib_div::implodeArrayForUrl('', $mainParams);

			if (!$script) {
				$script = basename(PATH_thisScript);
				$mainParams.= (t3lib_div::_GET('M') ? '&M='.rawurlencode(t3lib_div::_GET('M')) : '');
			}

			$options = array();
			foreach($menuItems as $value => $label) {
				$options[] = str_replace('><span ', '',
						 str_replace('</span>', '',
						 '<option value="'.htmlspecialchars($value).'"'.(!strcmp($currentValue, $value)?' selected="selected"':'').'>'.
								/*t3lib_div::deHSCentities(htmlspecialchars(*/$label/*))*/.
								'</option>'));
			}
			if (count($options)) {
				$onChange = 'jumpToUrl(\''.$script.'?'.$mainParams.$addparams.'&'.$elementName.'=\'+this.options[this.selectedIndex].value,this);';
				return '

					<!-- Function Menu of module -->
					<select style="width: 160px;" name="'.$elementName.'" onchange="'.htmlspecialchars($onChange).'">
						'.implode('
						',$options).'
					</select>
							';
			}
		}
	}

	function getOptsMenuNoHSC() {
		global $LANG;

		$options  = '<div id="options-menu">';
		$options .=  '<a href="#" class="toolbar-item">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/options.gif') . ' title="' . $LANG->getLL('center_settings', 1) . '" alt="Options" />' .
				'</a>';
		$options .= '<ul class="toolbar-item-menu" style="display: none; width: 185px;">';

		/* general option-group */
		{
			$link = $this->baseScript . $this->link_getParameters() . '&SET[set_details]=###';

			$entries[] = '<li class="radio'.(!$this->MOD_SETTINGS['set_details']?' selected':'').'" name="set_details"><a href="' . str_replace('###', '', $link).'"'. '>' . $LANG->getLL('center_settings_hidden', 1) . '</a></li>';
			$entries[] = '<li class="radio'.( $this->MOD_SETTINGS['set_details']?' selected':'').'" name="set_details"><a href="' . str_replace('###', '1', $link).'"'.'>' . $LANG->getLL('center_settings_all', 1) . '</a></li>';

			$group = '<ul class="group">'.implode(chr(10), $entries).'</ul>';
			$options .= '<li class="group">' . $group . '</li>';
		}

		$options .= '</ul>';
		$options .= '</div>';

		return $options;
	}

	/**
	 * Document Template Object
	 *
	 * @var mediumDoc
	 */
	var $doc;

	/**
	 * Initialize module header etc and call extObjContent function
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH;

		// Access check...
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
			$this->CALC_PERMS = $BE_USER->calcPerms($this->pageinfo);
			if ($BE_USER->user['admin'] && !$this->id)	{
				$this->pageinfo=array('title' => '[root-level]','uid'=>0,'pid'=>0);
			}

			$this->doc = t3lib_div::makeInstance('template');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->setModuleTemplate('templates/control-center.html');
			$this->doc->docType = 'xhtml_trans';
			$this->doc->tableLayout = Array (
				'0' => Array (
					'0' => Array('<td valign="top"><b>','</b></td>'),
					"defCol" => Array('<td><img src="'.$this->doc->backPath.'clear.gif" width="10" height="1" alt="" /></td><td valign="top"><b>','</b></td>')
				),
				"defRow" => Array (
					"0" => Array('<td valign="top">','</td>'),
					"defCol" => Array('<td><img src="'.$this->doc->backPath.'clear.gif" width="10" height="1" alt="" /></td><td valign="top">','</td>')
				)
			);

				// Add custom styles
			$this->doc->inDocStylesArray[]='
				/* stylesheet.css (line 189) */
				body#ext-templavoila-mod2-index-php {
					height: 100%;
					margin: 0pt;
					overflow: hidden;
					padding: 0pt;
				}
			';

				// Add optionsmenu
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath($this->extKey)."res/optionsmenu.js");

				// Add custom styles
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey)."mod2/styles.css";

				// JavaScript
			$this->doc->JScode = $this->doc->wrapScriptTags('
				script_ended = 0;
				function jumpToUrl(URL)	{	//
					window.location.href = URL;
				}
			');
			$this->doc->postCode = $this->doc->wrapScriptTags('
				script_ended = 1;
				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
			');

				// Setting up the context sensitive menu:
		//	$this->doc->getContextMenuCode();
		//	$this->doc->form = '<form action="index.php" method="post" name="webtvForm">';

				// Setting up support for context menus (when clicking the items icon)
			$CMparts = $this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
		//	$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->JScode .= $CMparts[0];
			$this->doc->postCode .= $CMparts[2];
			$this->doc->form = '<form action="'.htmlspecialchars($this->baseScript . 'id='.$this->id) . '" method="post" autocomplete="off">';

				// Prototype /Scriptaculous
			$this->doc->JScode .= '<script src="' . $this->doc->backPath . 'contrib/prototype/prototype.js" type="text/javascript"></script>';
			$this->doc->JScode .= '<script src="' . $this->doc->backPath . 'contrib/scriptaculous/scriptaculous.js?load=effects,dragdrop" type="text/javascript"></script>';

			$vContent = $this->doc->getVersionSelector($this->id,1);
			if ($vContent)	{
				$this->content.=$this->doc->section('',$vContent);
			}

			$this->extObjContent();

				// Info Module CSH:
			$this->content .= t3lib_BEfunc::cshItem('_MOD_web_tv', '', $GLOBALS['BACK_PATH'], '<br/>|', FALSE, 'margin-top: 30px;');
		//	$this->content .= $this->doc->spacer(10);

				// Rendering module content
			$funcs = $this->renderModuleContent(true);

				// Setting up the buttons and markers for docheader
			$docHeaderButtons = $this->getButtons();
			$markers = array(
				'CSH'       => $docHeaderButtons['csh'],
				'FUNC_MENU' => $funcs ? $this->getFuncMenuNoHSC($this->id, 'SET[page]', $this->MOD_SETTINGS['page'], $this->MOD_MENU['page'],'',t3lib_div::implodeArrayForUrl('',$_GET,'',1,1))	: '',
				'OPTS_MENU' => $funcs ? $this->getOptsMenuNoHSC() : '',

				'CONTENT'   => $this->content
			);

				// Build the <body> for the module
			$this->content  = $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
			$this->content .= $this->doc->endPage();
			$this->content  = $this->doc->insertStylesAndJS($this->content);
		} else {
				// If no access or if ID == zero
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
			$this->content .= $this->doc->endPage();
			$this->content  = $this->doc->insertStylesAndJS($this->content);
		}
	}

	/**
	 * Print module content (from $this->content)
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content = $this->doc->insertStylesAndJS($this->content);
		echo $this->content;
	}

	/**
	 * Create the panel of buttons for submitting the form or otherwise perform operations.
	 *
	 * @return	array	all available buttons as an assoc. array
	 */
	function getButtons()	{
		global $TCA, $LANG, $BACK_PATH, $BE_USER;

		$this->R_URI = t3lib_div::_GP('returnUrl');

		$buttons = array(
			'csh' => '',
			'back' => '',
		//	'view' => '',
			'record_list' => '',
			'shortcut' => '',
		);

			// If access to Web>List for user, then link to that module.
		if ($BE_USER->check('modules','web_list') && $this->pageinfo['uid']) {
			$href = $BACK_PATH . 'db_list.php?id=' . $this->pageinfo['uid'] . '&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
			$buttons['record_list'] = '<a href="' . htmlspecialchars($href) . '">' .
					'<img src="' . t3lib_iconWorks::skinImg($BACK_PATH, 'MOD:web_list/list.gif','width="16" height="16"', 1) . '" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showList', 1) . '" alt="" />' .
					'</a>';
		}
			// Back
		if ($this->R_URI) {
			$buttons['back'] = '<a href="' . htmlspecialchars($this->R_URI) . '" class="typo3-goBack">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/goback.gif') . ' alt="" />' .
				'</a>';
		}

			// CSH
		$buttons['csh'] = t3lib_BEfunc::cshItem('_MOD_web_tv', '', $GLOBALS['BACK_PATH']);

			// View page
	//	$buttons['view'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::viewOnClick($this->pageinfo['uid'], $BACK_PATH, t3lib_BEfunc::BEgetRootLine($this->pageinfo['uid']))) . '">' .
	//			'<img' . t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/zoom.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showPage', 1) . '" hspace="3" alt="" />' .
	//			'</a>';

			// Shortcut
		if ($BE_USER->mayMakeShortcut())	{
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('id, edit_record, pointer, new_unique_uid, search_field, search_levels, showLimit', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']);
		}

		return $buttons;
	}

}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/index.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_templavoila_module2_integral');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);
$SOBE->checkExtObj();	// Checking for first level external objects

// Repeat Include files! - if any files has been added by second-level extensions
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);
$SOBE->checkSubExtObj();	// Checking second level external objects

$SOBE->main();
$SOBE->printContent();
?>