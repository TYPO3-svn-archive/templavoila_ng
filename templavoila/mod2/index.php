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
 * @author	Kasper Sk�rh�j <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   93: class tx_templavoila_module2 extends t3lib_SCbase
 *  106:     function init()
 *  121:     function menuConfig()
 *  140:     function main()
 *  206:     function printContent()
 *
 *              SECTION: Rendering module content:
 *  224:     function renderModuleContent($singleView=false)
 *  285:     function renderModuleContent_mainView($singleView)
 *  502:     function renderDSlisting($dsScopeArray, &$toRecords, $scope)
 *  627:     function findRecordsWhereUsed_pid($pid)
 *  647:     function setErrorLog($scope,$type,$HTML)
 *  658:     function getErrorLog($scope)
 *  691:     function md5_file($file, $raw = false)
 *
 *
 *  710: class tx_templavoila_module2_integral extends tx_templavoila_module2
 *  719:     function init()
 *  729:     function menuConfig()
 *  758:     function link_getParameters()
 *  779:     function getFuncMenuNoHSC($mainParams, $elementName, $currentValue, $menuItems, $script = '', $addparams = '')
 *  818:     function getOptsMenuNoHSC()
 *  856:     function main()
 *  978:     function printContent()
 *  988:     function getButtons()
 *
 * TOTAL FUNCTIONS: 19
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once('conf.php');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
require_once(PATH_t3lib . 'class.t3lib_parsehtml.php');

$LANG->includeLLFile('EXT:templavoila/mod2/locallang.xml');
$BE_USER->modAccess($MCONF, 1);

// Include class which contains the constants and definitions of TV
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_defines.php');

// Include class for rendering the different sections:
require_once(t3lib_extMgm::extPath('templavoila') . 'mod2/class.tx_templavoila_mod2_overview.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'mod2/class.tx_templavoila_mod2_ds.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'mod2/class.tx_templavoila_mod2_to.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'mod2/class.tx_templavoila_mod2_xml.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'mod2/class.tx_templavoila_mod2_files.php');

/**
 * Module 'TemplaVoila' for the 'templavoila' extension.
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_module2 extends t3lib_SCbase {

	// External static:
	var $pageinfo;
	var $modTSconfig;
	var $extKey = 'templavoila';			// Extension key of this module
	var $baseScript = 'index.php?';
	var $mod2Script = '../mod2/index.php?';
	var $cm1Script = '../cm1/index.php?';
	var $wizScript = '../wizards/index.php?';

	var $errorsWarnings = array();


	function init() {
		parent::init();

		if (preg_match('/mod.php$/', PATH_thisScript)) {
			$this->baseScript = 'mod.php?M=web_txtemplavoilaM2&';
			$this->mod2Script = 'mod.php?M=web_txtemplavoilaM2&';
			$this->cm1Script = 'mod.php?M=xMOD_txtemplavoilaCM1&';
			$this->wizScript = 'mod.php?M=tx_templavoila_wizards&wiz=content&';
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
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id, 'mod.'. $this->MCONF['name']);

		// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);
	}

	/**
	 * Main function of the module.
	 *
	 * @return	void		Nothing.
	 */
	function main() {
		global $BE_USER, $BACK_PATH;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if ($access) {
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('noDoc');
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->divClass = '';
			$this->doc->form = '<form action="' . htmlspecialchars($this->baseScript . 'id=' . $this->id) . '" method="post" autocomplete="off">';

			// Add custom styles
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey) . "mod2/styles.css";

			// Adding classic jumpToUrl function, needed for the function menu.
			// Also, the id in the parent frameset is configured.
			$this->doc->JScode = $this->doc->wrapScriptTags('
				function jumpToUrl(URL)	{
					document.location = URL;
					return false;
				}

				function setHighlight(id) {
					if (top.fsMod) {
						top.fsMod.recentIds["web"] = id;
						top.fsMod.navFrameHighlightedID["web"] = "pages" + id + "_" + top.fsMod.currentBank;	// For highlighting

						if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav) {
							top.content.nav_frame.refresh_nav();
						}
					}
				}

			//	if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
			');

			// Setting up support for context menus (when clicking the items icon)
			$CMparts = $this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode .= $CMparts[0];
			$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->postCode.= $CMparts[2];

			$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->header($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->spacer(5);

			// Rendering module content
			$this->renderModuleContent(false);

			if ($BE_USER->mayMakeShortcut()) {
				$this->content .= '<br /><br />' . $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']);
			}
		// No access or no current uid:
		} else {
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('noDoc');
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->divClass = '';
			$this->doc->form = '<form action="' . htmlspecialchars($this->baseScript . 'id=' . $this->id) . '" method="post" autocomplete="off">';

			$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
		}

		$this->content .= $this->doc->endPage();
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent() {
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
	 * @param	[type]		$singleView: ...
	 * @return	void
	 */
	function renderModuleContent($singleView = false) {

		// Show overview.
		if (intval($this->id)) {
			// -------------------------------------------------------------------------
			// Select all Data Structures in the PID and put into an array:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'count(*)',
				'tx_templavoila_datastructure',
				'pid = ' . intval($this->id) . t3lib_BEfunc::deleteClause('tx_templavoila_datastructure')
			);

			list($countDS) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			// -------------------------------------------------------------------------
			// Select all Template Records in PID:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'count(*)',
				'tx_templavoila_tmplobj',
				'pid = ' . intval($this->id) . t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj')
			);

			list($countTO) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			// -------------------------------------------------------------------------
			// Select all Sys-Folders:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'count(*)',
				'pages',
				'doktype = 254' . ($this->perms_clause ? ' AND '. $this->perms_clause : '') . t3lib_BEfunc::deleteClause('pages')
			);

			list($countSF) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			// -------------------------------------------------------------------------
			// If there are TO/DS, render the module as usual, otherwise do something else...:
			if ($countTO || $countDS || $countSF) {
				$this->renderModuleContent_mainView($singleView);

				return true;
			}
		}

		{
			// Initialize the overview
			$overviewObj =& t3lib_div::getUserObj('&tx_templavoila_mod2_overview', '');
			$overviewObj->init($this);

			// Render the overview
			$this->content .= $overviewObj->renderModuleContent_searchForTODS();

			// Hmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm :-(
			$LOCAL_LANG_orig = $GLOBALS['LOCAL_LANG'];
			$GLOBALS['LANG']->includeLLFile('EXT:templavoila/wizards/locallang_site.xml');
			$GLOBALS['LOCAL_LANG'] = t3lib_div::array_merge_recursive_overrule($LOCAL_LANG_orig, $GLOBALS['LOCAL_LANG']);

			// Initialize the wizard
			$wizardObj =& t3lib_div::getUserObj('EXT:templavoila/wizards/class.tx_templavoila_wizards_site.php:&tx_templavoila_wizards_site', '');
			$wizardObj->init($this);

			// Render the wizard
			$this->content .= $wizardObj->renderNewSiteWizard_overview();

			return false;
		}
	}

	/**
	 * Renders module content main view:
	 *
	 * @param	[type]		$singleView: ...
	 * @return	void
	 */
	function renderModuleContent_mainView($singleView) {
		// Initialize the datastructure-submodule
		$this->dsObj =& t3lib_div::getUserObj('&tx_templavoila_mod2_ds', '');
		$this->dsObj->init($this);

		// Initialize the templateobject-submodule
		$this->toObj =& t3lib_div::getUserObj('&tx_templavoila_mod2_to', '');
		$this->toObj->init($this);

		// Initialize the xml-submodule
		$this->xmlObj =& t3lib_div::getUserObj('&tx_templavoila_mod2_xml', '');
		$this->xmlObj->init($this);

		// Select all Data Structures in the PID and put into an array:
		$dsRecords = array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_templavoila_datastructure',
			'pid = ' . intval($this->id) . t3lib_BEfunc::deleteClause('tx_templavoila_datastructure'),
			'',
			'title'
		);

		while($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			t3lib_BEfunc::workspaceOL('tx_templavoila_datastructure', $row);
			$dsRecords[$row['scope']][] = $row;
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		// Select all static Data Structures and add to array:
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $staticDS)	{
				$staticDS['_STATIC'] = 1;
				$dsRecords[$staticDS['scope']][] = $staticDS;
			}
		}

		// Select all Template Records in PID:
		$toRecords = array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'cruser_id, crdate, tstamp, uid, title, parent, fileref, sys_language_uid, datastructure, rendertype, localprocessing, previewicon, description, fileref_mtime, fileref_md5',
			'tx_templavoila_tmplobj',
			'pid = ' . intval($this->id).t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj'),
			'',
			'title'
		);

		while($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			t3lib_BEfunc::workspaceOL('tx_templavoila_tmplobj',$row);
			$toRecords[$row['parent']][] = $row;
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		// -----------------------------------------------------
		// Traverse scopes of data structures display template records belonging to them:
		// Each scope is places in its own tab in the tab menu:
		$parts = array();

		$dsScopes = array_unique(array_merge(
			array(TVDS_SCOPE_PAGE, TVDS_SCOPE_FCE, TVDS_SCOPE_OTHER),
			array_keys($dsRecords)
		));

		foreach ($dsScopes as $scopePointer) {
			$scopeIcon = '';

			// Create listing for a DS:
			list($content, $dsCount, $toCount) = $this->renderDSlisting($dsRecords[$scopePointer], $toRecords, $scopePointer);

			// Label for the tab:
			switch (intval($scopePointer)) {
				case TVDS_SCOPE_PAGE:
					$func = 'pagetmpl';
					$label = $GLOBALS['LANG']->getLL('center_tab_pagetmpl');
					$scopeIcon = t3lib_iconWorks::getIconImage('pages', array(), $this->doc->backPath, 'class="absmiddle"');
					break;
				case TVDS_SCOPE_FCE:
					$func = 'fcetmpl';
					$label = $GLOBALS['LANG']->getLL('center_tab_fcetmpl');
					$scopeIcon = t3lib_iconWorks::getIconImage('tt_content', array(), $this->doc->backPath, 'class="absmiddle"');
					break;
				case TVDS_SCOPE_OTHER:
					$func = 'other';
					$label = $GLOBALS['LANG']->getLL('center_tab_other');
					break;
				default:
					$func = 'unknown' . $scopePointer;
					$label = $GLOBALS['LANG']->getLL('center_tab_unknown') . ' "' . $scopePointer . '"';
					break;
			}

			// Error/Warning log:
			$errStat = $this->getErrorLog($scopePointer);

			// Add parts for Tab menu:
			$parts[$func][] = array(
				'label' => $label,
				'icon' => $scopeIcon,
				'content' => $content,
				'linkTitle' => 'DS/TO = ' . $dsCount . '/' . $toCount,
				'stateIcon' => $errStat['iconCode']
			);

			if (false !== ($errStat)) {
				if ($singleView)
					$this->MOD_MENU['page'][$func] = '<span style="color: brown;">' .
					$this->MOD_MENU['page'][$func] . ' (' . $errStat['iconCode'] . ')</span>';
			}
		}

		// -----------------------------------------------------
		// Find lost Template Objects and add them to a TAB if any are found:
		$lostTOs = '';
		$lostTOCount = 0;
		foreach ($toRecords as $toCategories) {
			foreach($toCategories as $toCat) {
				$rTODres = $this->toObj->renderTODisplay($toCat, $toRecords, 1);

				$lostTOs .= $rTODres['HTML'];
				$lostTOCount++;
			}
		}

		// Add parts for Tab menu:
		if ($lostTOs) {
			$parts['lost'][] = array(
				'label' => $GLOBALS['LANG']->getLL('center_tab_lost') . ' [' . $lostTOCount . ']',
				'content' => $lostTOs
			);

			if ($singleView)
				$this->MOD_MENU['page']['lost'] = '<span style="color: brown;">' .
				$this->MOD_MENU['page']['lost'] . ' (' . $lostTOCount . ')</span>';
		}
		else if ($singleView) {
			$parts['lost'][] = array(
				'label' => $GLOBALS['LANG']->getLL('center_tab_lost')
			);
		}

		// -----------------------------------------------------
		// Complete Template File List
		$parts['tmplfiles'][] = array(
			'label' => $GLOBALS['LANG']->getLL('center_tab_tmplfiles'),
			'content' => $this->toObj->completeTemplateFileList()
		);

		// -----------------------------------------------------
		// Errors:
		if (false !== ($errStat = $this->getErrorLog('_ALL'))) {
			$parts['errors'][] = array(
				'label' => $GLOBALS['LANG']->getLL('center_tab_errors') . ' (' . $errStat['count'] . ')',
				'content' => $errStat['content'],
				'stateIcon' => $errStat['iconCode']
			);

			if ($singleView)
				$this->MOD_MENU['page']['errors'] = '<span style="color: red;">' .
				$this->MOD_MENU['page']['errors'] . ' (' . $errStat['count'] . ')</span>';
		}
		else if ($singleView) {
			$parts['errors'][] = array(
				'label' => $GLOBALS['LANG']->getLL('center_tab_errors')
			);
		}

		// -----------------------------------------------------
		if ($singleView) {
			foreach ($parts as $func => $list)
				if (!isset($this->MOD_MENU['page'][$func]))
					$this->MOD_MENU['page'][$func] = $list[0]['label'];

			foreach ($this->MOD_MENU['page'] as $label) {
				if (!$parts[$label])
					unset($this->MOD_MENU['page'][$label]);
				//	$this->MOD_MENU['page'][$label] = '<span style="text-decoration: line-through;">' .
				//	$this->MOD_MENU['page'][$label] . '</span>';
			}

			foreach ($parts as $func => $list)
			foreach ($list as $cnf) {
				if (!$cnf['content'])
					unset($this->MOD_MENU['page'][$func]);
				//	$this->MOD_MENU['page'][$label] = '<span style="text-decoration: line-through;">' .
				//	$this->MOD_MENU['page'][$label] . '</span>';
			}

			// show only selected parts (fall back to tmplfiles, because it's always visible)
			if (isset($this->MOD_MENU['page'][$this->MOD_SETTINGS['page']]))
				$list = $parts[$this->MOD_SETTINGS['page']];
			else
				$list = $parts['tmplfiles'];

			foreach ($list as $cnf) {
				$this->content .= $this->doc->section(
					$cnf['label'] ? $cnf['label'] : $this->MOD_MENU['page'][$this->MOD_SETTINGS['page']],
					$cnf['content'] ? $cnf['content'] : $GLOBALS['LANG']->getLL('none'),
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
					t3lib_BEfunc::getFuncCheck('', 'SET[set_details]', $this->MOD_SETTINGS['set_details'], '', t3lib_div::implodeArrayForUrl('', $_GET, '', 1, 1)).
					' ' . $GLOBALS['LANG']->getLL('center_details') . ' &nbsp;&nbsp;&nbsp;'.
				'</p><hr />';

			// Add output:
			$this->content .=
				$settings .
				$this->doc->getDynTabMenu($tabs, 'TEMPLAVOILA:templateModule:' . $this->id, 0, 0, 300);
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
	function renderDSlisting($dsScopeArray, &$toRecords, $scope) {
		global $BE_USER;

		$dsCount = 0;
		$toCount = 0;
		$content = '';
		$index = '';

			// Traverse data structures to list:
		if (is_array($dsScopeArray)) {
			foreach ($dsScopeArray as $dsRow) {

				// Set relation ID of data structure used by template objects:
				$dsID = $dsRow['_STATIC'] ? $dsRow['path'] : $dsRow['uid'];

				// Traverse template objects which are not children of anything:
				$TOcontent = '';
				$indexTO = '';
				$toIdArray = array(-1);
				if (is_array($toRecords[0]))	{
					$newPid = $dsRow['pid'];
					$newFileRef = '';
					$newTitle = ($dsRow['_STATIC'] && substr($dsRow['title'], 0, 4) == 'LLL:' ? $GLOBALS['LANG']->sL($dsRow['title']) : $dsRow['title']) . ' [TEMPLATE]';

					foreach($toRecords[0] as $toIndex => $toRow)	{
						// If the relation ID matches, render the template object:
						if (!strcmp($toRow['datastructure'], $dsID)) {
							$rTODres = $this->toObj->renderTODisplay($toRow, $toRecords, $scope);
							$TOcontent .= '<a name="to-' . $toRow['uid'] . '"></a>' . $rTODres['HTML'];
							$indexTO .= '
								<tr class="bgColor4">
									<td>&nbsp;&nbsp;&nbsp;</td>
									<td><a href="#to-' . $toRow['uid'] . '">' . htmlspecialchars($toRow['title']).'</a></td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td align="center">' . $rTODres['mappingStatus'] . '</td>
									<td align="center">' . $rTODres['usage'] . '</td>
								</tr>';
							$toCount++;

							// Unset it so we can eventually see what is left:
							unset($toRecords[0][$toIndex]);

							$newPid = -$toRow['uid'];
							$newFileRef = $toRow['fileref'];
							$newTitle = $toRow['title'] . ' [ALT]';
							$toIdArray[] = $toRow['uid'];
						}
					}

					// For static DS we use the current page id as the PID:
					if (is_null($newPid)) {
						$newPid = t3lib_div::_GP('id');
					}

					if ($newFileRef == '' && $dsRow['_STATIC'] && isset($dsRow['fileref'])) {
						$newFileRef = $dsRow['fileref'];
					}

					// Module may be allowed, but modify may not
					if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
						// New-TO link:
						$TOcontent.= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick(
									'&edit[tx_templavoila_tmplobj][' . $newPid . ']=new'.
									'&defVals[tx_templavoila_tmplobj][datastructure]=' . rawurlencode($dsID) .
									'&defVals[tx_templavoila_tmplobj][title]=' . rawurlencode($newTitle) .
									'&defVals[tx_templavoila_tmplobj][fileref]=' . rawurlencode($newFileRef)
									,$this->doc->backPath)) . '">' .
								'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/new_el.gif', 'width="11" height="12"') . ' alt="" class="absmiddle" /> ' . $GLOBALS['LANG']->getLL('center_view_to_new') .
								'</a>';
					}
				}

				// Render data structure display
				$rDSDres = $this->dsObj->renderDSDisplay($dsRow, $toIdArray, $scope);
				$content.= '<a name="ds-' . md5($dsID) . '"></a>' . $rDSDres['HTML'];
				$index.='
					<tr class="bgColor4-20">
						<td colspan="2"><a href="#ds-' . md5($dsID) . '">' . htmlspecialchars($dsRow['title'] ? $dsRow['title'] : $dsRow['path']) . '</a></td>
						<td align="center">' . $rDSDres['languageMode'] . '</td>
						<td align="center">' . $rDSDres['container'] . '</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>';
				if ($indexTO)	{
					$index.=$indexTO;
				}
				$dsCount++;

				// Wrap TO elements in a div-tag and add to content:
				if ($TOcontent)	{
					$content.='<div style="margin-left: 102px;">' . $TOcontent . '</div>';
				}

				$content.='<br />';
			}
		}

		if ($index) {
			$content = '
				<h4>' . $GLOBALS['LANG']->getLL('center_list_overview') . ':</h4>
				<table border="0" cellpadding="0" cellspacing="1">
					<tr class="bgColor5 tableheader">
						<td colspan="2">' . $GLOBALS['LANG']->getLL('center_list_title') . ':</td>
						<td>' . $GLOBALS['LANG']->getLL('center_list_loc') . ':</td>
						<td>' . $GLOBALS['LANG']->getLL('center_list_costatus') . ':</td>
						<td>' . $GLOBALS['LANG']->getLL('center_list_mapstatus') . ':</td>
						<td>' . $GLOBALS['LANG']->getLL('center_list_usage') . ':</td>
					</tr>
				' . $index . '
				</table>
				<h4>' . $GLOBALS['LANG']->getLL('center_list_listing') . ':</h4>'.
				$content;
		}

		return array($content, $dsCount, $toCount);
	}

	/**
	 * Checks if a PID value is accessible and if so returns the path for the page.
	 * Processing is cached so many calls to the function are OK.
	 *
	 * @param	integer		Page id for check
	 * @return	string		Page path of PID if accessible. otherwise zero.
	 */
	function findRecordsWhereUsed_pid($pid)	{
		if (!isset($this->pidCache[$pid])) {
			$this->pidCache[$pid] = array();

			$pageinfo = t3lib_BEfunc::readPageAccess($pid, $this->perms_clause);
			$this->pidCache[$pid]['path'] = $pageinfo['_thePathFull'];
		}

		return $this->pidCache[$pid]['path'];
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
	function setErrorLog($scope,$type,$HTML) {
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

}

if (!function_exists('md5_file')) {

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$file: ...
	 * @param	[type]		$raw: ...
	 * @return	[type]		...
	 */
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
		global $BE_USER;

		parent::menuConfig();

		$this->MOD_MENU['page'] =
			array(
				'pagetmpl'  => $GLOBALS['LANG']->getLL('center_tab_pagetmpl', 1),
				'fcetmpl'   => $GLOBALS['LANG']->getLL('center_tab_fcetmpl', 1),
				'other'     => $GLOBALS['LANG']->getLL('center_tab_other', 1),
		//		'unknown'   => $GLOBALS['LANG']->getLL('center_tab_unknown', 1),
				'lost'      => $GLOBALS['LANG']->getLL('center_tab_lost', 1),
				'tmplfiles' => $GLOBALS['LANG']->getLL('center_tab_tmplfiles', 1),
				'errors'    => $GLOBALS['LANG']->getLL('center_tab_errors', 1)
			);

		// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id, 'mod.' . $this->MCONF['name']);

		// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);
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
				$onChange = 'jumpToUrl(\'' . $script . '?' . $mainParams . $addparams . '&' . $elementName . '=\'+this.options[this.selectedIndex].value,this);';
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

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function getOptsMenuNoHSC() {
		$options  = '<div id="options-menu">';
		$options .=  '<a href="#" class="toolbar-item">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/options.gif') . ' title="' . $GLOBALS['LANG']->getLL('center_settings', 1) . '" alt="Options" />' .
				'</a>';
		$options .= '<ul class="toolbar-item-menu" style="display: none; width: 185px;">';

		/* general option-group */
		{
			$link = $this->baseScript . $this->link_getParameters() . '&SET[set_details]=###';

			$entries[] = '<li class="mradio'.(!$this->MOD_SETTINGS['set_details']?' selected':'').'" name="set_details"><a href="' . str_replace('###', '', $link).'"'. '>' . $GLOBALS['LANG']->getLL('center_settings_hidden', 1) . '</a></li>';
			$entries[] = '<li class="mradio'.( $this->MOD_SETTINGS['set_details']?' selected':'').'" name="set_details"><a href="' . str_replace('###', '1', $link).'"'.'>' . $GLOBALS['LANG']->getLL('center_settings_all', 1) . '</a></li>';

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
		global $BE_USER, $BACK_PATH;

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

				function setHighlight(id) {
					if (top.fsMod) {
						top.fsMod.recentIds["web"] = id;
						top.fsMod.navFrameHighlightedID["web"] = "pages" + id + "_" + top.fsMod.currentBank;	// For highlighting

						if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav) {
							top.content.nav_frame.refresh_nav();
						}
					}
				}

			//	if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
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
			$this->doc->form = '<form action="'.htmlspecialchars($this->baseScript . 'id=' . $this->id) . '" method="post" autocomplete="off">';

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
			$this->content  = $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
			$this->content .= $this->doc->endPage();
			$this->content  = $this->doc->insertStylesAndJS($this->content);
		} else {
				// If no access or if ID == zero
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->header($GLOBALS['LANG']->getLL('title'));
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
	 * @return	array		all available buttons as an assoc. array
	 */
	function getButtons()	{
		global $TCA, $BACK_PATH, $BE_USER;

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
$SOBE->checkExtObj();		// Checking for first level external objects

// Repeat Include files! - if any files has been added by second-level extensions
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);
$SOBE->checkSubExtObj();	// Checking second level external objects

$SOBE->main();
$SOBE->printContent();
?>