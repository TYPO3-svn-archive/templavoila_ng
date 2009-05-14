<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2006 Robert Lemke (robert@typo3.org)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * New elements wizard for templavoila
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @coauthor	Kasper Skaarhoj <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */

require_once('conf.php');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');

// Merging locallang files/arrays:
$GLOBALS['LANG']->includeLLFile('EXT:lang/locallang_misc.xml');
$GLOBALS['LANG']->includeLLFile('EXT:templavoila/wizards/locallang.xml');
$GLOBALS['LANG']->includeLLFile('EXT:templavoila/wizards/locallang_content.xml');
$GLOBALS['LANG']->includeLLFile('EXT:templavoila/wizards/locallang_page.xml');
$GLOBALS['LANG']->includeLLFile('EXT:templavoila/wizards/locallang_site.xml');

// Exits if 'cms' extension is not loaded:
t3lib_extMgm::isLoaded('cms', 1);

// We need the TCE forms functions
require_once(PATH_t3lib . 'class.t3lib_page.php');
require_once(PATH_t3lib . 'class.t3lib_tceforms.php');

// Include class which contains the constants and definitions of TV
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_defines.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_api.php');

/**
 * Module 'Wizard' for the 'templavoila' extension.
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_wizard extends t3lib_SCbase {


	// External static:
	var $pageinfo;
	var $modTSconfig;
	var $extKey = 'templavoila';			// Extension key of this module
	var $baseScript = 'index.php?';
	var $mod1Script = '../mod1/index.php?';
	var $mod2Script = '../mod2/index.php?';
	var $cm1Script = '../cm1/index.php?';
	var $wizScript = '../wizards/index.php?';

	var $errorsWarnings = array();
	var $pid;
	var $wiz;


	/**
	 * @var tx_templavoila_api
	 */
	var $apiObj;					// Instance of tx_templavoila_api

	function init() {
		parent::init();

		if (preg_match('/mod.php$/', PATH_thisScript)) {
			$this->baseScript = 'mod.php?M=tx_templavoila_wizards&';
			$this->mod1Script = 'mod.php?M=web_txtemplavoilaM1&';
			$this->mod2Script = 'mod.php?M=web_txtemplavoilaM2&';
			$this->cm1Script = 'mod.php?M=xMOD_txtemplavoilaCM1&';
			$this->wizScript = 'mod.php?M=tx_templavoila_wizards&wiz=content&';
		}

		// Initialize TemplaVoila API class:
		$apiClassName = t3lib_div::makeInstanceClassName('tx_templavoila_api');
		$this->apiObj = new $apiClassName ($this->altRoot ? $this->altRoot : 'pages');

		// which wizard and where ------------------------------------------------------------
		if (!($this->pid = intval(t3lib_div::_GP('pid')))) {
			/* backward-compatibility */
			$this->pid = intval(t3lib_div::_GP('positionPid'));
		}

		if (!($this->wiz = t3lib_div::_GP('wiz'))) {
			/* backward-compatibility */
			if ($this->MOD_SETTINGS['wiz_step']) {
				$this->wiz = 'site';
			}
			else if (($cmd = t3lib_div::_GP('cmd'))) {
				if (($cmd == 'crPage') && $this->pid) {
					$this->wiz = 'page';
				}
			}
			else if ($this->id) {
				$this->wiz = 'content';
			}
		}
	}

	/**
	 * Preparing menu content
	 *
	 * @return	void
	 */
	function menuConfig()	{
		$this->MOD_MENU = array(
			'wiz_step' => ''
		);

		// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id , 'mod.' . $this->MCONF['name']);

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
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey) . "wizards/styles.css";

			// Adding classic jumpToUrl function, needed for the function menu.
			// Also, the id in the parent frameset is configured.
			$this->doc->JScode = $this->doc->wrapScriptTags('
				function jumpToUrl(URL)	{ //
					document.location = URL;
					return false;
				}
				if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
			');

			// Setting up support for context menus (when clicking the items icon)
			$CMparts = $this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode .= $CMparts[0];
			$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->postCode .= $CMparts[2];

			$this->content .= $this->renderModuleContent(false);

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


	/********************************************
	 *
	 * Rendering functions
	 *
	 ********************************************/

	/**
	 * Renders module content:
	 *
	 * @param	[type]		$singleView: ...
	 * @return	void
	 */
	function renderModuleContent($singleView = false) {
		global $BE_USER, $BACK_PATH;

		$content = '';

		// size-wizard -----------------------------------------------------------------------
		if ($this->wiz == 'site') {
			require_once(t3lib_extMgm::extPath('templavoila') . 'wizards/class.tx_templavoila_wizards_site.php');

			$content .= $this->doc->startPage($GLOBALS['LANG']->getLL('createnewsite_title'));
			$content .= $this->doc->header($GLOBALS['LANG']->getLL('createnewsite_title'));
			$content .= $this->doc->spacer(5);

			// Initialize the wizard
			$wizardObj =& t3lib_div::getUserObj('&tx_templavoila_wizards_site', '');
			$wizardObj->init($this);

			// Render the wizard
			$content .= $wizardObj->renderNewSiteWizard_run();
		}
		// page-wizard -----------------------------------------------------------------------
		else if ($this->wiz == 'page') {
			require_once(t3lib_extMgm::extPath('templavoila') . 'wizards/class.tx_templavoila_wizards_page.php');

			$content .= $this->doc->startPage($GLOBALS['LANG']->getLL('createnewpage_title'));
			$content .= $this->doc->header($GLOBALS['LANG']->getLL('createnewpage_title'));
			$content .= $this->doc->spacer(5);

			// Initialize the wizard
			$wizardObj =& t3lib_div::getUserObj('&tx_templavoila_wizards_page', '');
			$wizardObj->init($this);

			$content .= $wizardObj->renderWizard_createNewPage($this->pid);
		}
		// content-wizard --------------------------------------------------------------------
		else if ($this->wiz == 'content') {
			require_once(t3lib_extMgm::extPath('templavoila') . 'wizards/class.tx_templavoila_wizards_content.php');

			$content .= $this->doc->startPage($GLOBALS['LANG']->getLL('createnewcontent_title'));
			$content .= $this->doc->header($GLOBALS['LANG']->getLL('createnewcontent_title'));
			$content .= $this->doc->spacer(5);

			// Initialize the wizard
			$wizardObj =& t3lib_div::getUserObj('&tx_templavoila_wizards_content', '');
			$wizardObj->init($this);

			// Render the wizard
			$content .= $wizardObj->renderWizard_createNewContentElement();
		}
		else {
			$content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$content .= $this->doc->header($GLOBALS['LANG']->getLL('title'));
			$content .= $this->doc->spacer(5);
		}

		return $content;
	}

	/**
	 * Creates additional parameters which are used for linking to the current page while editing it
	 *
	 * @return	string		parameters
	 * @access public
	 */
	function link_getParameters()	{
		$output =
			'id=' . $this->id .
			(is_array($this->altRoot) ? t3lib_div::implodeArrayForUrl('altRoot', $this->altRoot) : '') .
			($this->versionId ? '&amp;versionId='.rawurlencode($this->versionId) : '');

		return $output;
	}

}

//	// Make instance:
//$SOBE = t3lib_div::makeInstance('tx_templavoila_wizard');
//$SOBE->init();
//$SOBE->main();
//$SOBE->printContent();

/**
 * Module 'TemplaVoila' for the 'templavoila' extension.
 * Modern integrated style
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_wizard_integral extends tx_templavoila_wizard {

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

		if (!is_callable(array('t3lib_div', 'int_from_ver')) || t3lib_div::int_from_ver(TYPO3_version) < 4000000) {
			$this->content = 'Fatal error:This version of TemplaVoila does not work with TYPO3 versions lower than 4.0.0! Please upgrade your TYPO3 core installation.';
			return;
		}

		// Access check...
		if (is_array($this->altRoot)) {
			$access = true;
		} else {
			// The page will show only if there is a valid page and if this page may be viewed by the user
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 : 0;
		}

		if ($access) {
			$this->calcPerms =
			$this->CALC_PERMS = $BE_USER->calcPerms($this->pageinfo);
			if ($BE_USER->user['admin'] && !$this->id) {
				$this->pageinfo = array('title' => '[root-level]', 'uid' => 0, 'pid' => 0);
			}

			// Define the root element record:
			$this->rootElementTable = is_array($this->altRoot) ? $this->altRoot['table'] : 'pages';
			$this->rootElementUid = is_array($this->altRoot) ? $this->altRoot['uid'] : $this->id;
			$this->rootElementRecord = t3lib_BEfunc::getRecordWSOL($this->rootElementTable, $this->rootElementUid, '*');
			$this->rootElementUid_pidForContent = $this->rootElementRecord['t3ver_swapmode'] == 0 && $this->rootElementRecord['_ORIG_uid'] ? $this->rootElementRecord['_ORIG_uid'] : $this->rootElementRecord['uid'];

			// Check if we have to update the pagetree:
			if (t3lib_div::_GP('updatePageTree')) {
				t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
			}

			$this->doc = t3lib_div::makeInstance('template');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->setModuleTemplate('templates/wizard.html');
			$this->doc->docType = 'xhtml_trans';
			$this->doc->tableLayout = Array (
				'0' => Array (
					'0' => Array('<td valign="top"><b>','</b></td>'),
					"defCol" => Array('<td><img src="' . $this->doc->backPath . 'clear.gif" width="10" height="1" alt="" /></td><td valign="top"><b>','</b></td>')
				),
				"defRow" => Array (
					"0" => Array('<td valign="top">','</td>'),
					"defCol" => Array('<td><img src="' . $this->doc->backPath . 'clear.gif" width="10" height="1" alt="" /></td><td valign="top">','</td>')
				)
			);

			// Add custom styles
			$this->doc->inDocStylesArray[] = '
				/* stylesheet.css (line 189) */
				body#ext-templavoila-wizards-index-php {
					height: 100%;
					margin: 0pt;
					overflow: hidden;
					padding: 0pt;
				}
			';

			// Add optionsmenu
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath($this->extKey) . "res/optionsmenu.js");

			// Add custom styles
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey) . "wizards/styles.css";

			// JavaScript
			$this->doc->JScode = $this->doc->wrapScriptTags('
				script_ended = 0;
				function jumpToUrl(URL)	{	//
					window.location.href = URL;
				}

				'.$this->doc->redirectUrls().'
				function jumpExt(URL,anchor)	{	//
					var anc = anchor?anchor:"";
					window.location.href = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
					return false;
				}
				function jumpSelf(URL)	{	//
					window.location.href = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
					return false;
				}
			');

			$this->doc->postCode = $this->doc->wrapScriptTags('
				script_ended = 1;
				if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
			');

			// Setting up the context sensitive menu:
		//	$this->doc->getContextMenuCode();
		//	$this->doc->form = '<form action="index.php" method="post" name="webtvForm">';

			// Setting up support for context menus (when clicking the items icon)
			$CMparts = $this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode .= $CMparts[0];
		//	$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->postCode .= $CMparts[2];
			$this->doc->form = '<form action="' . htmlspecialchars($this->baseScript . $this->link_getParameters()) . '" method="post" autocomplete="off">';

			$vContent = $this->doc->getVersionSelector($this->id, 1);
			if ($vContent)	{
				$this->content .= $this->doc->section('', $vContent);
			}

			$this->extObjContent();

			// Info Module CSH:
			$this->content .= t3lib_BEfunc::cshItem('_MOD_web_tv', '', $GLOBALS['BACK_PATH'], '<br/>|', FALSE, 'margin-top: 30px;');
		//	$this->content .= $this->doc->spacer(10);

			$this->content .= $this->renderModuleContent(true);

			// Setting up the buttons and markers for docheader
			$docHeaderButtons = $this->getButtons();
			$markers = array(
				'CSH'       => $docHeaderButtons['csh'],
				'FUNC_MENU' => '',
				'OPTS_MENU' => '',

				'CONTENT'   => $this->content,

				'PAGEPATH'  => $this->getPagePath($this->pageinfo),
				'PAGEINFO'  => $this->getPageInfo($this->pageinfo)
			);

			// Build the <body> for the module
			$this->content  = $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
			$this->content .= $this->doc->endPage();
			$this->content  = $this->doc->insertStylesAndJS($this->content);
		} else {
			// If no access
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content  = $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
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
			'level_up' => '',
			'new' => '',
			'view' => '',
			'edit_page' => '',
			'hide_unhide' => '',
			'clean' => '',
			'cache' => '',
			'reload' => '',
			'record_list' => '',
			'shortcut' => '',
		);

		// If access to Web>List for user, then link to that module.
		if ($BE_USER->check('modules', 'web_list') && $this->pageinfo['uid']) {
			$href = $BACK_PATH . 'db_list.php?id=' . $this->pageinfo['uid'] . '&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
			$buttons['record_list'] = '<a href="' . htmlspecialchars($href) . '">' .
					'<img src="' . t3lib_iconWorks::skinImg($BACK_PATH, 'MOD:web_list/list.gif', 'width="16" height="16"', 1) . '" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showList', 1) . '" alt="" />' .
					'</a>';
		}

		// Back
		if (is_array($this->altRoot)) {
			$buttons['back'] = '<a href="' . $this->mod1Script . 'id=' . $this->id . '" class="typo3-goBack">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/goback.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.goBack', 1) . '" alt="" />' .
				'</a>';
		}
		if ($this->R_URI) {
			$buttons['back'] = '<a href="' . htmlspecialchars($this->R_URI) . '" class="typo3-goBack">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/goback.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.goBack', 1) . '" alt="" />' .
				'</a>';
		}

		// Up one level
		if ($this->pageinfo['pid']) {
			$buttons['level_up'] = '<a href="' . $this->mod1Script . 'id=' . $this->pageinfo['pid'] . '" onclick="setHighlight(' . $this->pageinfo['pid'] . ')">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/i/pages_up.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.upOneLevel', 1) . '" alt="" />' .
						'</a>';
		}

		// CSH
		$buttons['csh'] = t3lib_BEfunc::cshItem('_MOD_web_tv', '', $GLOBALS['BACK_PATH']);

		if ($this->id) {
			// View page
			$buttons['view'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::viewOnClick($this->pageinfo['uid'], $BACK_PATH, t3lib_BEfunc::BEgetRootLine($this->pageinfo['uid']),'','',($this->currentLanguageUid?'&L='.$this->currentLanguageUid:''))) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/zoom.gif') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showPage', 1) . '" hspace="3" alt="" />' .
					'</a>';

			if ($this->CALC_PERMS & 2) {
				// Edit page properties
				$params = '&edit[pages][' . $this->id . ']=edit';
				$buttons['edit_page'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick($params, $BACK_PATH)) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', 'width="11" height="12"') . ' hspace="2" vspace="2" align="top" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:editPage', 1) . '" alt="" />' .
					'</a>';
			}
		}

		// Shortcut
		if ($BE_USER->mayMakeShortcut())	{
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('id, edit_record, pointer, new_unique_uid, search_field, search_levels, showLimit', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']);
		}

		return $buttons;
	}

	/**
	 * Generate the page path for docheader
	 *
	 * @param	array		Current page
	 * @return	string		Page path
	 */
	function getPagePath($pageRecord) {

		// Is this a real page
		if ($pageRecord['uid'])	{
			$title = $pageRecord['_thePath'];
		} else {
			$title = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
		}

		// Setting the path of the page
		$pagePath = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.path', 1) . ': <span class="typo3-docheader-pagePath">' . htmlspecialchars(t3lib_div::fixed_lgd_cs($title, -50)) . '</span>';

		return $pagePath;
	}

	/**
	 * Setting page icon with clickmenu + uid for docheader
	 *
	 * @param	array		Current page
	 * @return	string		Page info
	 */
	function getPageInfo($pageRecord) {
		global $BE_USER;

		// Add icon with clickmenu, etc:
		if ($pageRecord['uid'])	{	// If there IS a real page
			$alttext = t3lib_BEfunc::getRecordIconAltText($pageRecord, 'pages');
			$iconImg = t3lib_iconWorks::getIconImage('pages', $pageRecord, $this->backPath, 'class="absmiddle" title="'. htmlspecialchars($alttext) . '"');

			// Make Icon:
			$theIcon = $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg, 'pages', $pageRecord['uid']);
		// On root-level of page tree
		} else {
			// Make Icon
			$iconImg = '<img' . t3lib_iconWorks::skinImg($this->backPath, 'gfx/i/_icon_website.gif') . ' alt="' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] . '" />';
			if($BE_USER->user['admin']) {
				$theIcon = $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg, 'pages', 0);
			} else {
				$theIcon = $iconImg;
			}
		}

		// Setting icon with clickmenu + uid
		$pageInfo = $theIcon . '<em>[pid: ' . $pageRecord['uid'] . ']</em>';
		return $pageInfo;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/wizards/index.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/wizards/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_templavoila_wizard_integral');
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