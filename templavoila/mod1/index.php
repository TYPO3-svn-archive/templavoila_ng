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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Module 'Page' for the 'templavoila' extension.
 *
 * $Id: index.php 11102 2008-08-13 13:12:31Z dmitry $
 *
 * @author     Robert Lemke <robert@typo3.org>
 * @coauthor   Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @coauthor   Dmitry Dulepov <dmitry@typo3.org>
 * @coauthor   Niels Fr�hling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  152: class tx_templavoila_module1 extends t3lib_SCbase
 *
 *              SECTION: Initialization functions
 *  202:     function init()
 *  262:     function menuConfig()
 *
 *              SECTION: Main functions
 *  318:     function main()
 *  457:     function setFormValueFromBrowseWin(fName,value,label,exclusiveValues)
 *  571:     function printContent()
 *
 *              SECTION: Rendering functions
 *  591:     function renderModuleContent($singleView=false)
 *  665:     function render_editPageScreen($singleView)
 *
 *              SECTION: Framework rendering functions
 *  747:     function render_framework_allSheets($singleView, $contentTreeArr, $languageKey='DEF', $parentPointer=array(), $parentDsMeta=array())
 *  791:     function render_framework_singleSheet($singleView, $contentTreeArr, $languageKey, $sheet, $parentPointer=array(), $parentDsMeta=array())
 *  934:     function render_framework_singleSheet_traverse($singleView, $elementContentTreeArr, $languageKey, $sheet, $group = '')
 * 1061:     function render_framework_singleSheet_flush(&$cells, &$headerCells)
 * 1097:     function render_framework_subElement($singleView, $elementContentTreeArr, $languageKey, $sheet, $fieldID)
 * 1212:     function render_framework_previewData($elementContentTreeArr, $languageKey, $sheet, $fieldID)
 *
 *              SECTION: Rendering functions for certain elements
 * 1326:     function render_previewContent($row)
 * 1411:     function render_previewContent_extraPluginInfo($row)
 * 1437:     function render_localizationInfoTable($contentTreeArr, $parentPointer, $parentDsMeta=array())
 *
 *              SECTION: Outline rendering:
 * 1582:     function render_outline($singleView, $contentTreeArr)
 * 1691:     function render_outline_element($singleView, $contentTreeArr, &$entries, $indentLevel=0, $parentPointer=array(), $controls='')
 * 1801:     function render_outline_subElements($contentTreeArr, $sheet, &$entries, $indentLevel)
 * 1892:     function render_outline_localizations($contentTreeArr, &$entries, $indentLevel)
 *
 *              SECTION: Link functions (protected)
 * 1955:     function icon_view($el)
 * 1973:     function link_view($label, $table, $uid)
 * 1989:     function icon_hide($el)
 * 2017:     function link_hide($label, $table, $uid, $hidden, $forced=FALSE)
 * 2055:     function icon_edit($el)
 * 2076:     function link_edit($label, $table, $uid, $forced=FALSE)
 * 2103:     function icon_browse($parentPointer)
 * 2139:     function icon_new($parentPointer)
 * 2156:     function link_new($label, $parentPointer)
 * 2174:     function icon_unlink($unlinkPointer, $realDelete=0)
 * 2205:     function link_unlink($label, $unlinkPointer, $realDelete=FALSE)
 * 2227:     function icon_makeLocal($makeLocalPointer, $realDup=0)
 * 2247:     function link_makeLocal($label, $makeLocalPointer)
 * 2259:     function link_getParameters()
 *
 *              SECTION: Processing and structure functions (protected)
 * 2288:     function handleIncomingCommands()
 * 2399:     function clearCache()
 *
 *              SECTION: Miscelleaneous helper functions (protected)
 * 2427:     function getAvailableLanguages($id=0, $onlyIsoCoded=true, $setDefault=true, $setMulti=false)
 * 2501:     function hooks_prepareObjectsArray ($hookName)
 * 2518:     function alternativeLanguagesDefined()
 * 2528:     function displayElement($subElementArr)
 * 2548:     function localizedFFLabel($label, $hsc)
 * 2567:     function getRecordStatHookValue($table,$id)
 * 2579:     function hasFCEAccess($row)
 *
 *
 * 2604: class tx_templavoila_module1_integral extends tx_templavoila_module1
 * 2616:     function menuConfig()
 * 2653:     function getFuncMenuNoHSC($mainParams, $elementName, $currentValue, $menuItems, $script = '', $addparams = '')
 * 2692:     function getOptsMenuNoHSC()
 * 2737:     function main()
 * 2911:     function setFormValueFromBrowseWin(fName,value,label,exclusiveValues)
 * 3023:     function printContent()
 * 3033:     function getButtons()
 * 3144:     function getPagePath($pageRecord)
 * 3165:     function getPageInfo($pageRecord)
 *
 * TOTAL FUNCTIONS: 52
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once('conf.php');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');

$LANG->includeLLFile('EXT:templavoila/mod1/locallang.xml');
$BE_USER->modAccess($MCONF, 1);    								// This checks permissions and exits if the users has no permission for entry.

t3lib_extMgm::isLoaded('cms', 1);

// We need the TCE forms functions
require_once(PATH_t3lib . 'class.t3lib_loaddbgroup.php');
require_once(PATH_t3lib . 'class.t3lib_tcemain.php');
require_once(PATH_t3lib . 'class.t3lib_clipboard.php');

// Include class which contains the constants and definitions of TV
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_defines.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_api.php');

// Include class for rendering the side bar and wizards:
require_once(t3lib_extMgm::extPath('templavoila') . 'mod1/class.tx_templavoila_mod1_sidebar.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'mod1/class.tx_templavoila_mod1_wizards.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'mod1/class.tx_templavoila_mod1_clipboard.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'mod1/class.tx_templavoila_mod1_localization.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'mod1/class.tx_templavoila_mod1_records.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'mod1/class.tx_templavoila_mod1_specialdoktypes.php');

/**
 * Module 'Page' for the 'templavoila' extension.
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @coauthor	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_module1 extends t3lib_SCbase {

	var $modTSconfig;					// This module's TSconfig
	var $modSharedTSconfig;					// TSconfig from mod.SHARED
	var $extKey = 'templavoila';				// Extension key of this module
	var $baseScript = 'index.php?';
	var $mod1Script = 'mod1/index.php?';
	var $cm2Script = '../cm2/index.php?';

	var $global_tt_content_elementRegister=array(); 	// Contains a list of all content elements which are used on the page currently being displayed (with version, sheet and language currently set). Mainly used for showing "unused elements" in sidebar.
	var $global_localization_status=array(); 		// Contains structure telling the localization status of each element

	var $altRoot = array();					// Keys: "table", "uid" - thats all to define another "rootTable" than "pages" (using default field "tx_templavoila_flex" for flex form content)
	var $versionId = 0;					// Versioning: The current version id

	var $currentLanguageKey;				// Contains the currently selected language key (Example: DEF or DE)
	var $currentLanguageUid;				// Contains the currently selected language uid (Example: -1, 0, 1, 2, ...)
	var $allAvailableLanguages = array();			// Contains records of all available languages (not hidden, with ISOcode), including the default language and multiple languages. Used for displaying the flags for content elements, set in init().
	var $translatedLanguagesArr = array();			// Select language for which there is a page translation
	var $translatedLanguagesArr_isoCodes = array();		// ISO codes (for l/v pairs) of translated languages.
	var $translatorMode = FALSE;				// If this is set, the whole page module scales down functionality so that a translator only needs  to look for and click the "Flags" in the interface to localize the page! This flag is set if a user does not have access to the default language; then translator mode is assumed.
	var $xmlCleanCandidates = FALSE;
	var $calcPerms;						// Permissions for the parrent record (normally page). Used for hiding icons.

	var $doc;						// Instance of template doc class
	var $sideBarObj;					// Instance of sidebar class
	var $wizardsObj;					// Instance of wizards class
	var $clipboardObj;					// Instance of clipboard class
	var $recordsObj;					// Instance of records class

	/**
	 * @var tx_templavoila_api
	 */
	var $apiObj;						// Instance of tx_templavoila_api
	var $sortableContainers = array();			// Contains the containers for drag and drop

	var $newElementWizardLink;

	/*******************************************
	 *
	 * Initialization functions
	 *
	 *******************************************/

	/**
	 * Initialisation of this backend module
	 *
	 * @return	void
	 * @access public
	 */
	function init()    {
		parent::init();

		$this->mod1Script = t3lib_extMgm::extRelPath('templavoila') . $this->mod1Script;
		if (preg_match('/mod.php$/', PATH_thisScript)) {
			$this->baseScript = 'mod.php?M=web_txtemplavoilaM1&';
			$this->mod1Script = 'mod.php?M=web_txtemplavoilaM1&';
			$this->cm2Script = 'mod.php?M=xMOD_txtemplavoilaCM2&';
		}

		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);

		$this->altRoot = t3lib_div::_GP('altRoot');
		$this->versionId = t3lib_div::_GP('versionId');

			// Fill array allAvailableLanguages and currently selected language (from language selector or from outside)
		$this->allAvailableLanguages = $this->getAvailableLanguages(0, true, true, true);
		$this->currentLanguageKey = $this->allAvailableLanguages[$this->MOD_SETTINGS['language']]['ISOcode'];
		$this->currentLanguageUid = $this->allAvailableLanguages[$this->MOD_SETTINGS['language']]['uid'];

			// If no translations exist for this page, set the current language to default (as there won't be a language selector)
		$this->translatedLanguagesArr = $this->getAvailableLanguages($this->id);
		if (count($this->translatedLanguagesArr) == 1) {	// Only default language exists
			$this->currentLanguageKey = 'DEF';
		}

			// Set translator mode if the default langauge is not accessible for the user:
		if (!$GLOBALS['BE_USER']->checkLanguageAccess(0) && !$GLOBALS['BE_USER']->isAdmin())	{
			$this->translatorMode = TRUE;
		}

			// Initialize side bar and wizards:
		$this->sideBarObj =& t3lib_div::getUserObj ('&tx_templavoila_mod1_sidebar','');
		$this->sideBarObj->init($this);
		$this->sideBarObj->position = isset($this->modTSconfig['properties']['sideBarPosition']) ? $this->modTSconfig['properties']['sideBarPosition'] : 'toptabs';

		$this->wizardsObj = t3lib_div::getUserObj('&tx_templavoila_mod1_wizards','');
		$this->wizardsObj->init($this);

		$this->newElementWizardLink = 'mod.php?M=tx_templavoila_dbnewcontentel&';

			// Initialize TemplaVoila API class:
		$apiClassName = t3lib_div::makeInstanceClassName('tx_templavoila_api');
		$this->apiObj = new $apiClassName ($this->altRoot ? $this->altRoot : 'pages');

			// Initialize the clipboard
		$this->clipboardObj =& t3lib_div::getUserObj ('&tx_templavoila_mod1_clipboard','');
		$this->clipboardObj->init($this);

			// Initialize the record module
		$this->recordsObj =& t3lib_div::getUserObj ('&tx_templavoila_mod1_records','');
		$this->recordsObj->init($this);
	}

	/**
	 * Preparing menu content and initializing clipboard and module TSconfig
	 *
	 * @return	void
	 * @access public
	 */
	function menuConfig()	{
		global $TYPO3_CONF_VARS;

			// Prepare array of sys_language uids for available translations:
		$this->translatedLanguagesArr = $this->getAvailableLanguages($this->id);
		$translatedLanguagesUids = array();
		foreach ($this->translatedLanguagesArr as $languageRecord) {
			$translatedLanguagesUids[$languageRecord['uid']] = $languageRecord['title'];
		}

		$this->MOD_MENU = array(
			'tt_content_showHidden' => 1,
			'showOutline' => 1,
			'language' => $translatedLanguagesUids,
			'clip_parentPos' => '',
			'clip' => '',
			'langDisplayMode' => '',
			'recordsView_table' => '',
			'recordsView_start' => ''
		);

			// Hook: menuConfig_preProcessModMenu
		$menuHooks = $this->hooks_prepareObjectsArray('menuConfigClass');
		foreach ($menuHooks as $hookObj) {
			if (method_exists ($hookObj, 'menuConfig_preProcessModMenu')) {
				$hookObj->menuConfig_preProcessModMenu ($this->MOD_MENU, $this);
			}
		}

			// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.'.$this->MCONF['name']);
		$this->MOD_MENU['view'] = t3lib_BEfunc::unsetMenuItems($this->modTSconfig['properties'],$this->MOD_MENU['view'],'menu.function');

		if (!isset($this->modTSconfig['properties']['sideBarEnable'])) $this->modTSconfig['properties']['sideBarEnable'] = 1;
		$this->modSharedTSconfig = t3lib_BEfunc::getModTSconfig($this->id, 'mod.SHARED');

			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);
	}





	/*******************************************
	 *
	 * Main functions
	 *
	 *******************************************/

	/**
	 * Main function of the module.
	 *
	 * @return	void
	 * @access public
	 */
	function main()    {
		global $BE_USER,$LANG,$BACK_PATH;

		if (!is_callable(array('t3lib_div', 'int_from_ver')) || t3lib_div::int_from_ver(TYPO3_version) < 4000000) {
			$this->content = 'Fatal error:This version of TemplaVoila does not work with TYPO3 versions lower than 4.0.0! Please upgrade your TYPO3 core installation.';
			return;
		}

			// Access check! The page will show only if there is a valid page and if this page may be viewed by the user
		if (is_array($this->altRoot))	{
			$access = true;
		} else {
			$pageInfoArr = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
			$access = (intval($pageInfoArr['uid'] > 0));
		}

		if ($access)    {

				// calls from drag and drop
			if (t3lib_div::_GP("ajaxPasteRecord") == 'cut') {
				$sourcePointer = $this->apiObj->flexform_getPointerFromString(t3lib_div::_GP('source'));
				$destinationPointer = $this->apiObj->flexform_getPointerFromString(t3lib_div::_GP('destination'));
				$this->apiObj->moveElement($sourcePointer, $destinationPointer);
				exit;
			}

			if (t3lib_div::_GP("ajaxUnlinkRecord")) {
				$unlinkDestinationPointer = $this->apiObj->flexform_getPointerFromString(t3lib_div::_GP('ajaxUnlinkRecord'));
				$this->apiObj->unlinkElement($unlinkDestinationPointer);
				exit;
			}

			if (t3lib_div::_GP("ajaxDeleteRecord")) {
				$deleteDestinationPointer = $this->apiObj->flexform_getPointerFromString(t3lib_div::_GP('ajaxDeleteRecord'));
				$this->apiObj->deleteElement($unlinkDestinationPointer);
				exit;
			}

			$this->calcPerms = $GLOBALS['BE_USER']->calcPerms($pageInfoArr);

				// Define the root element record:
			$this->rootElementTable = is_array($this->altRoot) ? $this->altRoot['table'] : 'pages';
			$this->rootElementUid = is_array($this->altRoot) ? $this->altRoot['uid'] : $this->id;
			$this->rootElementRecord = t3lib_BEfunc::getRecordWSOL($this->rootElementTable, $this->rootElementUid, '*');
			$this->rootElementUid_pidForContent = $this->rootElementRecord['t3ver_swapmode']==0 && $this->rootElementRecord['_ORIG_uid'] ? $this->rootElementRecord['_ORIG_uid'] : $this->rootElementRecord['uid'];

				// Check if we have to update the pagetree:
			if (t3lib_div::_GP('updatePageTree')) {
				t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
			}

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('noDoc');
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->divClass = '';
			$this->doc->form='<form action="'.htmlspecialchars($this->baseScript . $this->link_getParameters()) . '" method="post" autocomplete="off">' .
				'<input type="hidden" id="browser[communication]" name="browser[communication]" />';

				// Add custom styles
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey) . "mod1/styles.css";

				// Adding classic jumpToUrl function, needed for the function menu. Also, the id in the parent frameset is configured.
			$this->doc->JScode = $this->doc->wrapScriptTags('
				function jumpToUrl(URL)	{ //
					document.location = URL;
					return false;
				}
				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
			' . $this->doc->redirectUrls() . '
				function jumpToUrl(URL)	{	//
					window.location.href = URL;
					return false;
				}
				function jumpExt(URL,anchor)	{	//
					var anc = anchor?anchor:"";
					window.location.href = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
					return false;
				}
				function jumpSelf(URL)	{	//
					window.location.href = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
					return false;
				}

				function setHighlight(id)	{	//
					top.fsMod.recentIds["web"]=id;
					top.fsMod.navFrameHighlightedID["web"]="pages"+id+"_"+top.fsMod.currentBank;	// For highlighting

					if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)	{
						top.content.nav_frame.refresh_nav();
					}
				}

				function editRecords(table,idList,addParams,CBflag)	{	//
					window.location.href="'.$BACK_PATH.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).
						'&edit["+table+"]["+idList+"]=edit"+addParams;
				}
				function editList(table,idList)	{	//
					var list="";

						// Checking how many is checked, how many is not
					var pointer=0;
					var pos = idList.indexOf(",");
					while (pos!=-1)	{
						if (cbValue(table+"|"+idList.substr(pointer,pos-pointer))) {
							list+=idList.substr(pointer,pos-pointer)+",";
						}
						pointer=pos+1;
						pos = idList.indexOf(",",pointer);
					}
					if (cbValue(table+"|"+idList.substr(pointer))) {
						list+=idList.substr(pointer)+",";
					}

					return list ? list : idList;
				}

				var browserPos = null,
				    browserWin = "",
				    browserPlus = "'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/plusbullet2.gif','', 1).'",
				    browserInsert = "'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/insert3.gif','', 1).'";

				function setFormValueOpenBrowser(mode,params) {	//
					var url = "'.$BACK_PATH.'browser.php?mode="+mode+"&bparams="+params;

					browserWin = window.open(url,"Typo3WinBrowser - TemplaVoila Element Selector","height=350,width="+(mode=="db"?650:600)+",status=0,menubar=0,resizable=1,scrollbars=1");
					browserWin.focus();

					$$(\'img.browse\').each(function(browserElm) {
						browserElm.src = browserInsert; });
					browserPos.firstChild.src = browserPlus;
				}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$fName,value,label,exclusiveValues: ...
	 * @return	[type]		...
	 */
				function setFormValueFromBrowseWin(fName,value,label,exclusiveValues){
					if (value) {
						var ret = value.split(\'_\');
						var rid = ret.pop();
							ret = ret.join(\'_\')
						browserPos.href = browserPos.rel.replace(\''.rawurlencode('###').'\', ret+\':\'+rid);
						jumpToUrl(browserPos.href);
					}
				}

				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
			');

				/* Prototype /Scriptaculous */
			$this->doc->JScode .= '<script src="' . $this->doc->backPath . 'contrib/prototype/prototype.js" type="text/javascript"></script>';

				/* Drag'N'Drop bug:
				 *	http://prototype.lighthouseapp.com/projects/8887/milestones/9608-1-8-2-bugfix-release
				 *	#59  drag drop problem in scroll div  draggable
				 */
		//	$this->doc->JScode .= '<script src="' . $this->doc->backPath . 'contrib/scriptaculous/scriptaculous.js?load=effects,dragdrop" type="text/javascript"></script>';
			$this->doc->JScode .= '<script src="' . $this->doc->backPath . 'contrib/scriptaculous/scriptaculous.js?load=effects" type="text/javascript"></script>';
			$this->doc->JScode .= '<script src="' . t3lib_extMgm::extRelPath($this->extKey) . 'res/dragdrop.js" type="text/javascript"></script>';
			$this->doc->JScode .= '<script src="' . t3lib_extMgm::extRelPath($this->extKey) . 'res/page-dnd.js" type="text/javascript"></script>';

				// Set up JS for dynamic tab menu and side bar
			$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->JScode .= $this->modTSconfig['properties']['sideBarEnable'] ? $this->sideBarObj->getJScode() : '';

				// Setting up support for context menus (when clicking the items icon)
			$CMparts = $this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode.= $CMparts[0];
			$this->doc->postCode.= $CMparts[2];

			// CSS for drag and drop
			$this->doc->inDocStyles .= '
				table {position:relative;}
				.sortableHandle {cursor:move;}
			';

			if (t3lib_extMgm::isLoaded('t3skin')) {
				// Fix padding for t3skin in disabled tabs
				$this->doc->inDocStyles .= '
table.typo3-dyntabmenu td.disabled,
table.typo3-dyntabmenu td.disabled_over,
table.typo3-dyntabmenu td.disabled:hover {
	padding-left: 10px;
}
				';
			}

			$this->content .= $this->doc->startPage($LANG->getLL('title'));

				// Rendering module content
			$content = $this->renderModuleContent(false);

				// Hook for adding new sidebars or removing existing
			$sideBarHooks = $this->hooks_prepareObjectsArray('sideBarClass');
			foreach ($sideBarHooks as $hookObj)	{
				if (method_exists($hookObj, 'main_alterSideBar')) {
					$hookObj->main_alterSideBar($this->sideBarObj, $this);
				}
			}

				// Show the "edit current page" screen along with the sidebar
			$shortCut = ($BE_USER->mayMakeShortcut() ? '<br /><br />'.$this->doc->makeShortcutIcon('id,altRoot',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']) : '');
			if ($this->sideBarObj->position == 'left' && $this->modTSconfig['properties']['sideBarEnable']) {
				$this->content .= '
					<table cellspacing="0" cellpadding="0" style="width:100%; height:550px; padding:0; margin:0;">
						<tr>
							<td style="vertical-align:top;">'.$this->sideBarObj->render().'</td>
							<td style="vertical-align:top; padding-bottom:20px;" width="99%">'.$content.$shortCut;'</td>
						</tr>
					</table>
				';
			}
			else {
				$sideBarTop = $this->modTSconfig['properties']['sideBarEnable']  && ($this->sideBarObj->position == 'toprows' || $this->sideBarObj->position == 'toptabs') ? $this->sideBarObj->render() : '';
				$this->content .= $sideBarTop.$content.$shortCut;
			}

		}
			// No access or no current page uid:
		else {
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->content.=$this->doc->startPage($LANG->getLL('title'));

			$cmd = t3lib_div::_GP ('cmd');
			switch ($cmd) {
					// Create a new page
				case 'crPage' :
						// Output the page creation form
					$this->content .= $this->wizardsObj->renderWizard_createNewPage (t3lib_div::_GP ('positionPid'));
					break;

					// If no access or if ID == zero
				default:
					$this->content .= $this->doc->header($LANG->getLL('title'));
					$this->content .= $LANG->getLL('default_introduction');
			}
		}

		$this->content .= $this->doc->endPage();
	}

	/**
	 * Echoes the HTML output of this module
	 *
	 * @return	void
	 * @access public
	 */
	function printContent()    {
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
	function renderModuleContent($singleView=false)	{
		global $LANG;

		$content = '';

		$this->handleIncomingCommands();

			// Start creating HTML output
		$render_editPageScreen = true;

			// Show message if the page is of a special doktype:
		if ($this->rootElementTable == 'pages') {

				// Initialize the special doktype class:
			$specialDoktypesObj =& t3lib_div::getUserObj ('&tx_templavoila_mod1_specialdoktypes','');
			$specialDoktypesObj->init($this);

			$methodName = 'renderDoktype_'.$this->rootElementRecord['doktype'];
			if (method_exists($specialDoktypesObj, $methodName)) {
				$result = $specialDoktypesObj->$methodName($this->rootElementRecord);
				if ($result !== FALSE) {
					$content .= $result;

					if ($GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'edit')) {
							// Edit icon only if page can be modified by user
						$content .= '<br /><br /><strong>'.$this->icon_edit(array('table'=>'pages','uid'=>$this->id)).'</strong>';
					}

					$render_editPageScreen = false; // Do not output editing code for special doctypes!
				}
			}
		}

		if ($render_editPageScreen) {
				// Render "edit current page" (important to do before calling ->sideBarObj->render() - otherwise the translation tab is not rendered!
			$content .= $this->render_editPageScreen($singleView);

				// Create sortables
			if (is_array($this->sortableContainers)) {
				$content .= '
				<script type="text/javascript" language="javascript">

				Event.observe(window, \'load\', function() {
					sortable_removeHidden = ' . ($this->MOD_SETTINGS['tt_content_showHidden'] ? 'false' : 'true') . ';
					sortable_baseLink = \'' . $this->baseScript . $this->link_getParameters() . '\';
					sortable_containers = [
						"'.implode('",
						"', $this->sortableContainers).'"
					];

					if ($("typo3-docbody")) {
						sortable_parameters.scroll = $("typo3-docbody");
						sortable_parameters.scrollid = "typo3-docbody";
					}

					for (var s = 0; s < sortable_containers.length; s++) {
						Sortable.create(sortable_containers[s], sortable_parameters);
					}
				});

				</script>';
			}
		}

		return $content;
	}

	/**
	 * Displays the default view of a page, showing the nested structure of elements.
	 *
	 * @param	[type]		$singleView: ...
	 * @return	string		The modules content
	 * @access protected
	 */
	function render_editPageScreen($singleView) {
		global $LANG, $BE_USER, $TYPO3_CONF_VARS;

		$output = '';

			// Fetch the content structure of page:
		$contentTreeData = $this->apiObj->getContentTree($this->rootElementTable, $this->rootElementRecord);
		// TODO Dima: seems like it does not return <TCEForms> for elements inside sectiions. Thus titles are not visible for these elements!

			// Set internal variable which registers all used content elements:
		$this->global_tt_content_elementRegister = $contentTreeData['contentElementUsage'];

			// Setting localization mode for root element:
		$this->rootElementLangMode = $contentTreeData['tree']['ds_meta']['langDisable'] ? 'disable' : ($contentTreeData['tree']['ds_meta']['langChildren'] ? 'inheritance' : 'separate');
		$this->rootElementLangParadigm = ($this->modTSconfig['properties']['translationParadigm'] == 'free') ? 'free' : 'bound';

			// Create a back button if neccessary:
		if (is_array($this->altRoot)) {
			$output .= '<div style="text-align:right; width:100%; margin-bottom:5px;"><a href="' . $this->baseScript . 'id='.$this->id.'"><img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/goback.gif','').' title="'.htmlspecialchars($LANG->getLL ('goback')).'" alt="" /></a></div>';
		}

			// Add the localization module if localization is enabled:
		if ($this->alternativeLanguagesDefined()) {
			$this->localizationObj =& t3lib_div::getUserObj ('&tx_templavoila_mod1_localization','');
			$this->localizationObj->init($this);
		}

			// Hook for content at the very top (fx. a toolbar):
		if (is_array ($TYPO3_CONF_VARS['EXTCONF']['templavoila']['mod1']['renderTopToolbar'])) {
			foreach ($TYPO3_CONF_VARS['EXTCONF']['templavoila']['mod1']['renderTopToolbar'] as $_funcRef) {
				$_params = array ();
				$output .= t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}

			// Display the content as outline or the nested page structure:
		if ($BE_USER->isAdmin() && ($this->MOD_SETTINGS['showOutline'] || ($this->MOD_SETTINGS['page'] == 'outline'))) {
			$output .= $this->render_outline($singleView, $contentTreeData['tree']);
		} else {
			if ($this->MOD_SETTINGS['page'] == 'preview_nu')
				$output .= $this->clipboardObj->sidebar_renderNonUsedElements();

			$output .= $this->render_framework_allSheets($singleView, $contentTreeData['tree'], $this->currentLanguageKey);
		}

			// See http://bugs.typo3.org/view.php?id=4821
		$renderHooks = $this->hooks_prepareObjectsArray('render_editPageScreen');
		foreach ($renderHooks as $hookObj)	{
			if (method_exists ($hookObj, 'render_editPageScreen_addContent')) {
				$output .= $hookObj->render_editPageScreen_addContent($this);
			}
		}

		if (!$singleView) {
			$output .= t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', '', $this->doc->backPath, '<hr/>|'.$LANG->getLL('csh_whatisthetemplavoilapagemodule', 1));
		}

		return $output;
	}





	/*******************************************
	 *
	 * Framework rendering functions
	 *
	 *******************************************/

	/**
	 * Rendering the sheet tabs if applicable for the content Tree Array
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	string		$languageKey: Language key for the display
	 * @param	array		$parentPointer: Flexform Pointer to parent element
	 * @param	array		$parentDsMeta: Meta array from parent DS (passing information about parent containers localization mode)
	 * @param	[type]		$parentDsMeta: ...
	 * @return	string		HTML
	 * @access protected
	 * @see	render_framework_singleSheet()
	 */
	function render_framework_allSheets($singleView, $contentTreeArr, $languageKey='DEF', $parentPointer=array(), $parentDsMeta=array()) {

			// If more than one sheet is available, render a dynamic sheet tab menu, otherwise just render the single sheet framework
		if (is_array($contentTreeArr['sub']) && (count($contentTreeArr['sub']) > 1 || !isset($contentTreeArr['sub']['sDEF'])))	{
			$parts = array();

			foreach(array_keys($contentTreeArr['sub']) as $sheetKey)	{
				$this->containedElementsPointer++;
				$this->containedElements[$this->containedElementsPointer] = 0;

				$frContent = $this->render_framework_singleSheet($singleView, $contentTreeArr, $languageKey, $sheetKey, $parentPointer, $parentDsMeta);

				$parts[] = array(
					'label' => ($contentTreeArr['meta'][$sheetKey]['title']
						  ? $contentTreeArr['meta'][$sheetKey]['title'] : $sheetKey),
				#	.' ['.$this->containedElements[$this->containedElementsPointer].']',
					'description' => $contentTreeArr['meta'][$sheetKey]['description'],
					'linkTitle' => $contentTreeArr['meta'][$sheetKey]['short'],
					'content' => $frContent,
				);

				$this->containedElementsPointer--;
			}

			return $this->doc->getDynTabMenu($parts,'TEMPLAVOILA:pagemodule:'.$this->apiObj->flexform_getStringFromPointer($parentPointer));
		}
		else {
			return $this->render_framework_singleSheet($singleView, $contentTreeArr, $languageKey, 'sDEF', $parentPointer, $parentDsMeta);
		}
	}

	/**
	 * Renders the display framework of a single sheet. Calls itself recursively
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	string		$languageKey: Language key for the display
	 * @param	string		$sheet: The sheet key of the sheet which should be rendered
	 * @param	array		$parentPointer: Flexform pointer to parent element
	 * @param	array		$parentDsMeta: Meta array from parent DS (passing information about parent containers localization mode)
	 * @param	[type]		$parentDsMeta: ...
	 * @return	string		HTML
	 * @access protected
	 * @see	render_framework_singleSheet()
	 */
	function render_framework_singleSheet($singleView, $contentTreeArr, $languageKey, $sheet, $parentPointer=array(), $parentDsMeta=array()) {
		global $LANG, $TYPO3_CONF_VARS;

		$elementBelongsToCurrentPage = $contentTreeArr['el']['table'] == 'pages' || $contentTreeArr['el']['pid'] == $this->rootElementUid_pidForContent;

		$canCreateNew   = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'new');
		$canEditPage    = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'edit');
		$canEditContent = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'editcontent');

			// Prepare the record icon including a content sensitive menu link wrapped around it:
		$recordIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,$contentTreeArr['el']['icon'],'width="18" height="16"').' border="0" title="'.htmlspecialchars('['.$contentTreeArr['el']['table'].':'.$contentTreeArr['el']['uid'].']').'" alt="" />';
		$menuCommands = array();
		if ($GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'new')) {
			$menuCommands[] = 'new';
		}
		if ($canEditContent) {
			$menuCommands[] = 'copy,cut,pasteinto,pasteafter,delete';
		}
		else {
			$menuCommands[] = 'copy';
		}

		$titleBarLeftButtons = $this->translatorMode ? $recordIcon : (count($menuCommands) == 0 ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), implode(',', $menuCommands)));
		$titleBarLeftButtons.= $this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);

		unset($menuCommands);

			// Prepare table specific settings:
		switch ($contentTreeArr['el']['table']) {
			case 'pages':
				$titleBarLeftButtons .= $this->translatorMode || !$canEditPage ? '' :
					$this->icon_edit($contentTreeArr['el']) .
					$this->icon_hide($contentTreeArr['el']) .
					$this->icon_view($contentTreeArr['el']);
				$titleBarRightButtons = '';

				if ($singleView)
					$titleBarLeftButtons =
					($this->localizationObj ?
						$this->localizationObj->sidebar_renderItem_renderLanguageSelectorbox_pure_actual() : '') . ' ';
				break;
			case 'tt_content':
				$elementTitlebarColor = ($elementBelongsToCurrentPage ? $this->doc->bgColor5 : $this->doc->bgColor6);
				$elementTitlebarStyle = 'background-color: '.$elementTitlebarColor;

				$languageUid = $contentTreeArr['el']['sys_language_uid'];

				if (!$this->translatorMode && $canEditContent) {
						// Create CE specific buttons:
					$linkMakeLocal =
						$this->icon_makeLocal($parentPointer, !$elementBelongsToCurrentPage);
					$linkEdit = ''; if ($GLOBALS['BE_USER']->recordEditAccessInternals('tt_content', $contentTreeArr['previewData']['fullRow'])) {
					$linkEdit = ($elementBelongsToCurrentPage ?
						$this->icon_edit($contentTreeArr['el']) .
						$this->icon_hide($contentTreeArr['el']) : ''); }
					$linkUnlink =
						$this->icon_unlink($parentPointer, $elementBelongsToCurrentPage ? 1 : 0);

					$titleBarRightButtons = $linkEdit . $linkMakeLocal . $this->clipboardObj->element_getSelectButtons($parentPointer) . $linkUnlink;
				}
				else {
					$titleBarRightButtons = $this->clipboardObj->element_getSelectButtons($parentPointer, 'copy');
				}
				break;
		}

			// Prepare the language icon:
		$languageLabel = htmlspecialchars ($this->allAvailableLanguages[$contentTreeArr['el']['sys_language_uid']]['title']);
		$languageIcon = $this->allAvailableLanguages[$languageUid]['flagIcon'] ? '<img src="'.$this->allAvailableLanguages[$languageUid]['flagIcon'].'" title="'.$languageLabel.'" alt="'.$languageLabel.'" />' : ($languageLabel && $languageUid ? '['.$languageLabel.']' : '');

			// If there was a language icon and the language was not default or [all] and if that langauge is accessible for the user, then wrap the  flag with an edit link (to support the "Click the flag!" principle for translators)
		if ($languageIcon && $languageUid>0 && $GLOBALS['BE_USER']->checkLanguageAccess($languageUid) && $contentTreeArr['el']['table']==='tt_content')	{
			$languageIcon = $this->link_edit($languageIcon, 'tt_content', $contentTreeArr['el']['uid'], TRUE);
		}

			// Create warning messages if neccessary:
		$warnings = '';
		if ($this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']] > 1 && $this->rootElementLangParadigm !='free') {
			$warnings .= '<br />'.$this->doc->icons(2).' <em>'.htmlspecialchars(sprintf($LANG->getLL('warning_elementusedmorethanonce',''), $this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']], $contentTreeArr['el']['uid'])).'</em>';
		}

			// Displaying warning for container content (in default sheet - a limitation) elements if localization is enabled:
		$isContainerEl = count($contentTreeArr['sub']['sDEF']);
		if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning'] && $this->rootElementLangParadigm !='free' && $isContainerEl && $contentTreeArr['el']['table'] === 'tt_content' && $contentTreeArr['el']['CType'] === 'templavoila_pi1' && !$contentTreeArr['ds_meta']['langDisable'])	{
			if ($contentTreeArr['ds_meta']['langChildren'])	{
				if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning_warningOnly']) {
					$warnings .= '<br />'.$this->doc->icons(2).' <b>'.$LANG->getLL('warning_containerInheritance').'</b>';
				}
			}
			else {
				$warnings .= '<br />'.$this->doc->icons(3).' <b>'.$LANG->getLL('warning_containerSeparate').'</b>';
			}
		}

			// Finally assemble the table:
		$finalContent = '
			<table cellpadding="0" cellspacing="0" width="100%" class="tv-coe">
				<tr style="'.$elementTitlebarStyle.';" class="sortableHandle">
					<td>'.
						'<span class="nobr">'.
						$languageIcon.
						$titleBarLeftButtons.
						($elementBelongsToCurrentPage?'':'<em>').htmlspecialchars($contentTreeArr['el']['title']).($elementBelongsToCurrentPage ? '' : '</em>').
						'</span>'.
						$warnings.
					'</td>
					<td nowrap="nowrap" class="sortableButtons">'.
						$titleBarRightButtons.
					'</td>
				</tr>
				<tr>
					<td colspan="2">'.
					(is_array($contentTreeArr['previewData']['fullRow'])
					&&	  $contentTreeArr['el']['table'] == 'tt_content'
					&&	  $contentTreeArr['el']['CType'] != 'templavoila_pi1'
					?	$this->render_previewContent($contentTreeArr['previewData']['fullRow'])
					:	$this->render_framework_singleSheet_traverse($singleView, $contentTreeArr, $languageKey, $sheet)
					).	$this->render_localizationInfoTable($contentTreeArr, $parentPointer, $parentDsMeta).
					'</td>
				</tr>
			</table>
		';

		return $finalContent;
	}

	/**
	 * Traverses a sheet, create previews and sub-elements. Calls itself recursively
	 * Specifically interpretes XPathed fields and groups them into rows, it also
	 * interleaves subElement-blocks with previewData to pertain a consistent view
	 * of the original DS's appearance.
	 *
	 * Calls render_framework_allSheets() and therefore generates a recursion.
	 *
	 * @param	array		$elementContentTreeArr: Content tree starting with the element which possibly has sub elements
	 * @param	string		$languageKey: Language key for current display
	 * @param	string		$sheet: Key of the sheet we want to render
	 * @param	string		$group: Specific group to be rendred, is used for recursion mainly
	 * @param	[type]		$group: ...
	 * @return	string		HTML output (a table) of the sub elements and some "insert new" and "paste" buttons
	 * @access protected
	 * @see render_framework_allSheets(), render_framework_singleSheet()
	 */
	function render_framework_singleSheet_traverse($singleView, $elementContentTreeArr, $languageKey, $sheet, $group = '') {
		global $LANG;
		global $done;

			// Define l/v keys for current language:
		$langChildren = intval($elementContentTreeArr['ds_meta']['langChildren']);
		$langDisable  = intval($elementContentTreeArr['ds_meta']['langDisable']);

		$lKey = $langDisable ? 'lDEF' : ($langChildren ? 'lDEF' : 'l'.$languageKey);
		$vKey = $langDisable ? 'vDEF' : ($langChildren ? 'v'.$languageKey : 'vDEF');

			// gets the layout
		$beTemplate = $elementContentTreeArr['ds_meta']['beLayout'];
			// no layout, no special rendering
		$flagRenderBeLayout = $beTemplate ? TRUE : FALSE;

			// some constants
		$haspreview = is_array($previews = $elementContentTreeArr['previewData']['sheets'][$sheet]);
		$hassubs = is_array($elementContentTreeArr['sub'][$sheet]) && is_array($subs = $elementContentTreeArr['sub'][$sheet][$lKey]);

			// how to render the sheet
		$output = '';
		$cells = array();
		$headerCells = array();
		$done = ($group == '' ? array() : $done);

						// ----------------------------------------------------------------------------------
			// Traverse previewData fields:
		if ($haspreview)
		foreach($previews as $fieldID => $fieldData) {
				// check for early bail out
			if (strlen($fieldID) <= strlen($group))
				continue;
			if (!$fieldData['isMapped'])
				continue;

				// remove the group from the field
			$fieldFrag = str_replace($group, '', $fieldID);
				// check for prefix-replace
			if ($fieldID != ($group . $fieldFrag))
				continue;

							// --------------------------------------------------------------------------
				// the first field of a possible group that hits us will trigger the grouping
			if (strchr($fieldFrag, SEPARATOR_XPATH) !== FALSE) {
				$fieldFrags = explode(SEPARATOR_XPATH, $fieldFrag);

				$co = array_shift($fieldFrags);
				$el = array_shift($fieldFrags);

				$groupID = $group . $co;
				$groupenter = $group . $co . SEPARATOR_XPATH . $el . SEPARATOR_XPATH;
				if (!$done[$groupenter]) {
					$done[$groupenter] = true;

					$output .= $this->render_framework_singleSheet_flush($cells, $headerCells);
					$outbuf  = $this->render_framework_singleSheet_traverse($singleView, $elementContentTreeArr, $languageKey, $sheet, $groupenter);

					if ($outbuf != '') $output .=
						'<div style="border: 1px dotted rgb(0, 0, 0); padding: 5px;">' .
							'<h2>' . $previews[$groupID]['title'] . '</h2>' .
							$outbuf .
						'</div>';
				}
			}
							// -------------------------------------------------------------------------
				// for now process only those field that are direct child of the given group
			else if (strchr($fieldFrag, SEPARATOR_XPATH) === FALSE) {

								// -----------------------------------------------------------------
					// sub-element
				if ($hassubs && is_array($subs[$fieldID][$vKey])) {
					$fieldContent = $subs[$fieldID][$vKey];
					$cellContent = $this->render_framework_subElement($singleView, $elementContentTreeArr, $languageKey, $sheet, $fieldID);

						// Create flexform pointer pointing to "before the first sub element":
					$groupElementPointer = array (
						'table' => $elementContentTreeArr['el']['table'],
						'uid'   => $elementContentTreeArr['el']['uid'],
						'sheet' => $sheet,
						'sLang' => $lKey,
						'field' => $fieldID,
						'vLang' => $vKey
					);

					$cellId = $this->apiObj->flexform_getStringFromPointer($groupElementPointer);
					$this->sortableContainers[] = $cellId;

						// Add cell content to registers:
					if ($flagRenderBeLayout == TRUE) {
						$beTemplateCell = '<table width="100%" class="beTemplateCell"><tr><td valign="top" style="background-color: '.$this->doc->bgColor4.'; padding-top:0; padding-bottom:0;">'.$LANG->sL($fieldContent['meta']['title'],1).'</td></tr><tr><td valign="top" style="padding: 5px;" id="'.$cellId.'">'.$cellContent.'</td></tr></table>';
						$beTemplate = str_replace('###'.$fieldID.'###', $beTemplateCell, $beTemplate);
					}
						// Add cell content to registers:
					else {
						$headerCells[]='<td valign="top" width="###WIDTH###" style="background-color: '.$this->doc->bgColor4.'; padding-top:0; padding-bottom:0;">'.$LANG->sL($fieldContent['meta']['title'],1).'</td>';
						$cells[]='<td valign="top" width="###WIDTH###" style="border: 1px dashed #000; padding: 5px 5px 5px 5px;" id="'.$cellId.'">'.$cellContent.'</td>';
					}
				}
								// -----------------------------------------------------------------
					// just preview
				else {
					$output .= $this->render_framework_singleSheet_flush($cells, $headerCells);
					$outbuf  = $this->render_framework_previewData($elementContentTreeArr, $languageKey, $sheet, $fieldID);

					if ($outbuf != '') $output .=
						'<div style="border: 1px dashed rgb(0, 0, 0); padding: 5px; margin: 2px;">' .
							$outbuf .
						'</div>';
				}
			}
		}

			// removes not used markers
		if ($flagRenderBeLayout == TRUE) {
			$output = preg_replace("/###field_.*?###/", '', $beTemplate);
		}
			// finalizes tables
		else {
			$output .= $this->render_framework_singleSheet_flush($cells, $headerCells);
		}

		return $output;
	}

				// ----------------------------------------------------------------------------------
		// flush the render-queue
	function render_framework_singleSheet_flush(&$cells, &$headerCells) {
		$output = '';

			// Compile the content area for the current element (basically what was put together above):
		if (count($headerCells) || count($cells)) {
			$output = str_replace('###WIDTH###', round(100 / count($cells)) . '%', '
				<table border="0" cellpadding="2" cellspacing="2" width="100%" class="tv-container">
					<tr>'.(count($headerCells) ? implode('', $headerCells) : '<td>&nbsp;</td>').'</tr>
					<tr>'.(count(      $cells) ? implode('',       $cells) : '<td>&nbsp;</td>').'</tr>
				</table>
			');

			$headerCells = array();
			$cells = array();
		}

		return $output;
	}

	/**
	 * Renders a single sub element of the given elementContentTree array. This function basically
	 * renders the "new" and "paste" buttons for the parent element and then traverses through
	 * the sub elements (if any exist). The sub element's (preview-) content will be rendered
	 * by render_framework_singleSheet().
	 *
	 * Calls render_framework_allSheets() and therefore generates a recursion.
	 *
	 * @param	array		$elementContentTreeArr: Content tree starting with the element which possibly has sub elements
	 * @param	string		$languageKey: Language key for current display
	 * @param	string		$sheet: Key of the sheet we want to render
	 * @param	string		$fieldID: The field to render
	 * @param	[type]		$fieldID: ...
	 * @return	string		HTML output (a table) of the sub elements and some "insert new" and "paste" buttons
	 * @access protected
	 * @see render_framework_allSheets(), render_framework_singleSheet()
	 */
	function render_framework_subElement($singleView, $elementContentTreeArr, $languageKey, $sheet, $fieldID) {
		global $LANG;

		$canCreateNew   = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'new');
		$canEditContent = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'editcontent');

			// Define l/v keys for current language:
		$langChildren = intval($elementContentTreeArr['ds_meta']['langChildren']);
		$langDisable  = intval($elementContentTreeArr['ds_meta']['langDisable']);

		$lKey = $langDisable ? 'lDEF' : ($langChildren ? 'lDEF' : 'l'.$languageKey);
		$vKey = $langDisable ? 'vDEF' : ($langChildren ? 'v'.$languageKey : 'vDEF');

		if (!is_array($elementContentTreeArr['sub'][$sheet]) ||
			!is_array($elementContentTreeArr['sub'][$sheet][$lKey]))
			return '';

						// ----------------------------------------------------------------------------------
			// Traverse container fields:
		if (($fieldValuesContent = $elementContentTreeArr['sub'][$sheet][$lKey][$fieldID])) {
			$fieldContent = $fieldValuesContent[$vKey];
			$cellContent = '';

				// Create flexform pointer pointing to "before the first sub element":
			$subElementPointer = array (
				'table' => $elementContentTreeArr['el']['table'],
				'uid'   => $elementContentTreeArr['el']['uid'],
				'sheet' => $sheet,
				'sLang' => $lKey,
				'field' => $fieldID,
				'vLang' => $vKey,
				'position' => 0
			);

				// "Browse", "New" and "Paste" icon:
			$cellContent .= $this->icon_browse($subElementPointer);

			if (!$this->translatorMode && $canCreateNew) {
				$cellContent .= $this->icon_new($subElementPointer);
			}

			$cellContent .= '<span class="sortablePaste">' . $this->clipboardObj->element_getPasteButtons($subElementPointer) . '</span>';

							// -----------------------------------------------------------------------------
				// Render the list of elements (and possibly call itself recursively if needed):
			if (is_array($fieldContent['el_list']))	 {
				foreach($fieldContent['el_list'] as $position => $subElementKey)	{
					$subElementArr = $fieldContent['el'][$subElementKey];

					if ((!$subElementArr['el']['isHidden'] || $this->MOD_SETTINGS['tt_content_showHidden']) && $this->displayElement($subElementArr)) {

							// When "onlyLocalized" display mode is set and an alternative language gets displayed
						if (($this->MOD_SETTINGS['langDisplayMode'] == 'onlyLocalized') && $this->currentLanguageUid>0)	{

								// Default language element. Subsitute displayed element with localized element
							if (($subElementArr['el']['sys_language_uid']==0) && is_array($subElementArr['localizationInfo'][$this->currentLanguageUid]) && ($localizedUid = $subElementArr['localizationInfo'][$this->currentLanguageUid]['localization_uid']))	{
								$localizedRecord = t3lib_BEfunc::getRecordWSOL('tt_content', $localizedUid, '*');
								$tree = $this->apiObj->getContentTree('tt_content', $localizedRecord);
								$subElementArr = $tree['tree'];
							}
						}
						$this->containedElements[$this->containedElementsPointer]++;

							// Modify the flexform pointer so it points to the position of the curren sub element:
						$subElementPointer['position'] = $position;

						$cellFragment = $this->render_framework_allSheets($singleView, $subElementArr, $languageKey, $subElementPointer, $elementContentTreeArr['ds_meta']);

							// "Browse", "New" and "Paste" icon:
						$cellFragment .= $this->icon_browse($subElementPointer);

						if (!$this->translatorMode && $canCreateNew)	{
							$cellFragment .= $this->icon_new($subElementPointer);
						}

						$cellFragment .= '<span class="sortablePaste">' . $this->clipboardObj->element_getPasteButtons($subElementPointer) . '</span>';

						if ($canEditContent) {
							$cellId = $this->apiObj->flexform_getStringFromPointer($subElementPointer);
							$cellFragment = '<div class="sortableItem" id="' . $cellId . '">' . $cellFragment . '</div>';
						}

						$cellContent .= $cellFragment;
					}
					else {
							// Modify the flexform pointer so it points to the position of the curren sub element:
						$subElementPointer['position'] = $position;

						if ($canEditContent) {
							$cellId = $this->apiObj->flexform_getStringFromPointer($subElementPointer);
							$cellFragment = '<div class="sortableItem" id="' . $cellId . '"></div>';
						}

						$cellContent .= $cellFragment;
					}
				}
			}

			return $cellContent;
		}

		return '';
	}

	/**
	 * Rendering the preview of content for Page module.
	 *
	 * @param	array		$elementContentTreeArr: Content tree starting with the element which possibly has previewData elements
	 * @param	string		$languageKey: Language key for current display
	 * @param	string		$sheet: Key of the sheet we want to render
	 * @param	string		$fieldID: The field to render
	 * @return	string		HTML output (a table) of the sub elements and some "insert new" and "paste" buttons
	 * @access protected
	 * @see render_framework_allSheets(), render_framework_singleSheet()
	 */
	function render_framework_previewData($elementContentTreeArr, $languageKey, $sheet, $fieldID) {
		global $LANG;

			// Define l/v keys for current language:
		$langChildren = intval($elementContentTreeArr['ds_meta']['langChildren']);
		$langDisable  = intval($elementContentTreeArr['ds_meta']['langDisable']);

		$lKey = $langDisable ? 'lDEF' : ($langChildren ? 'lDEF' : 'l'.$languageKey);
		$vKey = $langDisable ? 'vDEF' : ($langChildren ? 'v'.$languageKey : 'vDEF');

		if (!is_array($elementContentTreeArr['previewData']['sheets'][$sheet]))
			return '';

						// ----------------------------------------------------------------------------------
			// Preview of FlexForm content if any:
		if (($fieldData = $elementContentTreeArr['previewData']['sheets'][$sheet][$fieldID])) {
			$edit2 = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','').' title="'.$LANG->getLL ('editrecord').'" border="0" alt="" />';
			$table = $elementContentTreeArr['el']['table'];
			$uid   = $elementContentTreeArr['previewData']['fullRow']['uid'];

			$TCEformsConfiguration = $fieldData['TCEforms']['config'];
			$TCEformsLabel = $this->localizedFFLabel($fieldData['TCEforms']['label'], 1);	// title for non-section elements
			$cellContent = '';

							// --------------------------------------------------------------------------
							// Making preview for array/section parts of a FlexForm structure:
			if ($fieldData['type'] == 'array') {
				if (is_array($fieldData['subElements'][$lKey])) {
					if ($fieldData['section']) {
						$cellContent .=
							'<strong>'.$fieldData['title'].'</strong><br /> ' .
							'<ol>';

						foreach($fieldData['subElements'][$lKey] as $sectionData) {
							if (is_array($sectionData)) {
								$sectionFieldKey = key($sectionData);
								if (is_array ($sectionData[$sectionFieldKey]['el'])) {
									$cellContent .=
										'<li style="border-top: 1px dotted rgb(0, 0, 0); padding-top: 5px; margin-top: 5px;">'.
										'<dl>';
									foreach ($sectionData[$sectionFieldKey]['el'] as $containerFieldKey => $containerData) {
										if ($containerFieldKey[0] != '_') {
											$cellContent .=
												'<dt style="width: 25%; float: left; clear: left;"><strong>'.$containerFieldKey.'</strong></dt> '.
												'<dd style="margin-left: 25%;">'.
												(trim($containerData[$vKey]) != ''
												?	$this->link_edit(htmlspecialchars(t3lib_div::fixed_lgd_cs(strip_tags($containerData[$vKey]),200)), $table, $uid) . ' &nbsp;'
												:	(is_array($containerData['el']) ? '&hellip;' : '&mdash;')
												).
												'</dd>';
										}
									}
									$cellContent .=
										'</dl>' .
										'</li>';
								}
							}
						}

						$cellContent .=
							'</ol>';
					}
					else {
						foreach ($fieldData['subElements'][$lKey] as $containerKey => $containerData) {
							$cellContent .=
								'<strong>'.$containerKey.'</strong><br /> '.
								'<p>'.$this->link_edit($edit2 . '&nbsp;' . htmlspecialchars(t3lib_div::fixed_lgd_cs(strip_tags($containerData[$vKey]),200)), $table, $uid).'</p>';
						}
					}
				}
			}
							// --------------------------------------------------------------------------
				// Preview of flexform fields on top-level:
			else {
				$fieldValue = $fieldData['data'][$lKey][$vKey];

				if ($TCEformsConfiguration['type'] == 'group') {
					if ($TCEformsConfiguration['internal_type'] == 'file')	{
						// Render preview for images:
						$thumbnail = t3lib_BEfunc::thumbCode(array('dummyFieldName'=> $fieldValue), '', 'dummyFieldName', $this->doc->backPath, '', $TCEformsConfiguration['uploadfolder']);
						$cellContent .=
							'<strong>'.$TCEformsLabel.'</strong><br /> '.
							$thumbnail.'<br />';
					}
				}
				else if ($TCEformsConfiguration['type'] != '') {
						// Render for everything else:
					$cellContent .=
						'<strong>'.$TCEformsLabel.'</strong><br /> '.
						$this->link_edit($edit2 . '&nbsp;' . htmlspecialchars(t3lib_div::fixed_lgd_cs(strip_tags($fieldValue),200)), $table, $uid).'<br />';
				}
			}

			return $cellContent;
		}

		return '';
	}

	/*******************************************
	 *
	 * Rendering functions for certain elements
	 *
	 *******************************************/

	/**
	 * Returns an HTMLized preview of a certain content element. If you'd like to register a new content type, you can easily use the hook
	 * provided at the beginning of the function.
	 *
	 * @param	array		$row: The row of tt_content containing the content element record.
	 * @return	string		HTML preview content
	 * @access protected
	 * @see		getContentTree(), render_localizationInfoTable()
	 */
	function render_previewContent($row) {
		global $TYPO3_CONF_VARS, $LANG;

		$hookObjectsArr = $this->hooks_prepareObjectsArray ('renderPreviewContentClass');
		$alreadyRendered = FALSE;
		$output = '';

			// Hook: renderPreviewContent_preProcess. Set 'alreadyRendered' to true if you provided a preview content for the current cType !
		reset($hookObjectsArr);
		while (list(,$hookObj) = each($hookObjectsArr)) {
			if (method_exists ($hookObj, 'renderPreviewContent_preProcess')) {
				$output .= $hookObj->renderPreviewContent_preProcess ($row, 'tt_content', $alreadyRendered, $this);
			}
		}

		if (!$alreadyRendered) {
				// Preview content for non-flexible content elements:
			switch($row['CType'])	{
				case 'text':		//	Text
				case 'table':		//	Table
				case 'mailform':	//	Form
					$output = $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','bodytext'),1).'</strong> '.htmlspecialchars(t3lib_div::fixed_lgd_cs(trim(strip_tags($row['bodytext'])),2000)),'tt_content',$row['uid']);
					break;
				case 'image':		//	Image
					$output = $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','image'),1).'</strong><br /> ', 'tt_content', $row['uid']).t3lib_BEfunc::thumbCode ($row, 'tt_content', 'image', $this->doc->backPath);
					break;
				case 'textpic':		//	Text w/image
				case 'splash':		//	Textbox
					$thumbnail = '<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','image'),1).'</strong><br />';
					$thumbnail .= t3lib_BEfunc::thumbCode($row, 'tt_content', 'image', $this->doc->backPath);
					$text = $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','bodytext'),1).'</strong> '.htmlspecialchars(t3lib_div::fixed_lgd_cs(trim(strip_tags($row['bodytext'])),2000)),'tt_content',$row['uid']);
					$output='<table><tr><td valign="top">'.$text.'</td><td valign="top">'.$thumbnail.'</td></tr></table>';
					break;
				case 'bullets':		//	Bullets
					$htmlBullets = '';
					$bulletsArr = explode ("\n", t3lib_div::fixed_lgd_cs($row['bodytext'],2000));
					if (is_array ($bulletsArr)) {
						foreach ($bulletsArr as $listItem) {
							$htmlBullets .= htmlspecialchars(trim(strip_tags($listItem))).'<br />';
						}
					}
					$output = $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','bodytext'),1).'</strong><br />'.$htmlBullets, 'tt_content', $row['uid']);
					break;
				case 'uploads':		//	Filelinks
					$output = $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','media'),1).'</strong><br />'.str_replace (',','<br />',htmlspecialchars(t3lib_div::fixed_lgd_cs(trim(strip_tags($row['media'])),2000))), 'tt_content', $row['uid']);
					break;
				case 'multimedia':	//	Multimedia
					$output = $this->link_edit ('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','multimedia'),1).'</strong><br />' . str_replace (',','<br />',htmlspecialchars(t3lib_div::fixed_lgd_cs(trim(strip_tags($row['multimedia'])),2000))), 'tt_content', $row['uid']);
					break;
				case 'menu':		//	Menu / Sitemap
					$output = $this->link_edit ('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','menu_type')).'</strong> '.$LANG->sL(t3lib_BEfunc::getLabelFromItemlist('tt_content','menu_type',$row['menu_type'])).'<br />'.
						'<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','pages')).'</strong> '.$row['pages'], 'tt_content', $row['uid']);
					break;
				case 'list':		//	Insert Plugin
					$extraInfo = $this->render_previewContent_extraPluginInfo($row);
					$output = $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','list_type')).'</strong> ' . htmlspecialchars($LANG->sL(t3lib_BEfunc::getLabelFromItemlist('tt_content','list_type',$row['list_type']))).' &ndash; '.htmlspecialchars($extraInfo ? $extraInfo : $row['list_type']), 'tt_content', $row['uid']);
					break;
				case 'html':		//	HTML
					$output = $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','bodytext'),1).'</strong> <div style="overflow: hidden">' . htmlspecialchars(t3lib_div::fixed_lgd_cs(trim($row['bodytext']),2000)),'tt_content',$row['uid']).'</div>';
					break;
				case 'header': // Header
					$output = $this->link_edit('<strong>'.$LANG->sL(t3lib_BEfunc::getItemLabel('tt_content','header'),1).'</strong> ' . htmlspecialchars(t3lib_div::fixed_lgd_cs(trim(strip_tags($row['header'])),2000)),'tt_content',$row['uid']);
					break;
				case 'search':			//	Search Box
				case 'login':			//	Login Box
				case 'shortcut':		//	Insert records
				case 'div':				//	Divider
				case 'templavoila_pi1': //	Flexible Content Element: Rendered directly in getContentTree*()
					break;
				default:
						// return CType name for unhandled CType
					$output='<strong>'.htmlspecialchars ($row['CType']).'</strong>';
			}
		}

		return '<div class="tv-preview">' . $output . '</div>';
	}


	/**
	 * Renders additional information about plugins (if available)
	 *
	 * @param	array		$row	Row from database
	 * @return	string		Information
	 */
	function render_previewContent_extraPluginInfo($row) {
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][$row['list_type']]))	{
			$hookArr = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][$row['list_type']];
		} elseif (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['_DEFAULT']))	{
			$hookArr = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['_DEFAULT'];
		}
		$hookOut = '';
		if (count($hookArr) > 0)	{
			$_params = array('pObj' => &$this, 'row' => $row, 'infoArr' => $infoArr);
			foreach ($hookArr as $_funcRef)	{
				$hookOut .= t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}
		return $hookOut;
	}

	/**
	 * Renders a little table containing previews of translated version of the current content element.
	 *
	 * @param	array		$contentTreeArr: Part of the contentTreeArr for the element
	 * @param	string		$parentPointer: Flexform pointer pointing to the current element (from the parent's perspective)
	 * @param	array		$parentDsMeta: Meta array from parent DS (passing information about parent containers localization mode)
	 * @return	string		HTML
	 * @access protected
	 * @see 	render_framework_singleSheet()
	 */
	function render_localizationInfoTable($contentTreeArr, $parentPointer, $parentDsMeta=array()) {
		global $LANG, $BE_USER;

				// LOCALIZATION information for content elements (non Flexible Content Elements)
		$output = '';
		if ($contentTreeArr['el']['table']=='tt_content' && $contentTreeArr['el']['sys_language_uid']<=0)	{

				// Traverse the available languages of the page (not default and [All])
			$tRows=array();
			foreach($this->translatedLanguagesArr as $sys_language_uid => $sLInfo)	{
				if ($this->MOD_SETTINGS['langDisplayMode'] && ($this->currentLanguageUid != $sys_language_uid))
					continue;

				if ($sys_language_uid > 0)	{
					$l10nInfo = '';
					$flagLink_begin = $flagLink_end = '';

					switch((string)$contentTreeArr['localizationInfo'][$sys_language_uid]['mode'])	{
						case 'exists':
							$olrow = t3lib_BEfunc::getRecordWSOL('tt_content',$contentTreeArr['localizationInfo'][$sys_language_uid]['localization_uid']);

							$localizedRecordInfo = array(
								'uid' => $olrow['uid'],
								'row' => $olrow,
								'content' => $this->render_previewContent($olrow)
							);

								// Put together the records icon including content sensitive menu link wrapped around it:
							$recordIcon_l10n = t3lib_iconWorks::getIconImage('tt_content',$localizedRecordInfo['row'],$this->doc->backPath,'class="absmiddle" title="'.htmlspecialchars('[tt_content:'.$localizedRecordInfo['uid'].']').'"');
							if (!$this->translatorMode)	{
								$recordIcon_l10n = $this->doc->wrapClickMenuOnIcon($recordIcon_l10n,'tt_content',$localizedRecordInfo['uid'],1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter');
							}
							$l10nInfo =
								$this->getRecordStatHookValue('tt_content', $localizedRecordInfo['row']['uid']).
								$recordIcon_l10n .
								htmlspecialchars(t3lib_div::fixed_lgd_cs(strip_tags(t3lib_BEfunc::getRecordTitle('tt_content', $localizedRecordInfo['row'])), 50));

							$l10nInfo.= '<br />'.$localizedRecordInfo['content'];

							list($flagLink_begin, $flagLink_end) = explode('|*|', $this->link_edit('|*|', 'tt_content', $localizedRecordInfo['uid'], TRUE));
							if ($this->translatorMode)	{
								$l10nInfo.= '<br />'.$flagLink_begin.'<em>'.$LANG->getLL('clickToEditTranslation').'</em>'.$flagLink_end;
							}

								// Wrap workspace notification colors:
							if ($olrow['_ORIG_uid'])	{
								$l10nInfo = '<div class="ver-element">'.$l10nInfo.'</div>';
							}

							$this->global_localization_status[$sys_language_uid][]=array(
								'status' => 'exist',
								'parent_uid' => $contentTreeArr['el']['uid'],
								'localized_uid' => $localizedRecordInfo['row']['uid'],
								'sys_language' => $contentTreeArr['el']['sys_language_uid']
							);
						break;
						case 'localize':

							if ($this->rootElementLangParadigm =='free')	{
								$showLocalizationLinks = !$parentDsMeta['langDisable'];	// For this paradigm, show localization links only if localization is enabled for DS (regardless of Inheritance and Separate)
							} else {
								$showLocalizationLinks = ($parentDsMeta['langDisable'] || $parentDsMeta['langChildren']);	// Adding $parentDsMeta['langDisable'] here means that the "Create a copy for translation" link is shown only if the parent container element has localization mode set to "Disabled" or "Inheritance" - and not "Separate"!
							}
								// Assuming that only elements which have the default language set are candidates for localization. In case the language is [ALL] then it is assumed that the element should stay "international".
							if ((int)$contentTreeArr['el']['sys_language_uid']===0 && $showLocalizationLinks)	{

									// Copy for language:
								if ($this->rootElementLangParadigm =='free')	{
									$sourcePointerString = $this->apiObj->flexform_getStringFromPointer($parentPointer);
									$onClick = "document.location='".$this->baseScript . $this->link_getParameters().'&source='.rawurlencode($sourcePointerString).'&localizeElement='.$sLInfo['ISOcode']."'; return false;";
								} else {
									$params='&cmd[tt_content]['.$contentTreeArr['el']['uid'].'][localize]='.$sys_language_uid;
									$onClick = "document.location='".$GLOBALS['SOBE']->doc->issueCommand($params)."'; return false;";
								}

								$linkLabel = $LANG->getLL('createcopyfortranslation',1).' ('.htmlspecialchars($sLInfo['title']).')';
								$localizeIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/clip_copy.gif','width="12" height="12"').' class="bottom" title="'.$linkLabel.'" alt="" />';

								$l10nInfo = '<a href="#" onclick="'.htmlspecialchars($onClick).'">'.$localizeIcon.'</a>';
								$l10nInfo .= ' <em><a href="#" onclick="'.htmlspecialchars($onClick).'">'.$linkLabel.'</a></em>';
								$flagLink_begin = '<a href="#" onclick="'.htmlspecialchars($onClick).'">';
								$flagLink_end = '</a>';

								$this->global_localization_status[$sys_language_uid][]=array(
									'status' => 'localize',
									'parent_uid' => $contentTreeArr['el']['uid'],
									'sys_language' => $contentTreeArr['el']['sys_language_uid']
								);
							}
						break;
						case 'localizedFlexform':
								// Here we want to show the "Localized FlexForm" information (and link to edit record) _only_ if there are other fields than group-fields for content elements: It only makes sense for a translator to deal with the record if that is the case.
								// Change of strategy (27/11): Because there does not have to be content fields; could be in sections or arrays and if thats the case you still want to localize them! There has to be another way...
							// if (count($contentTreeArr['contentFields']['sDEF']))	{
								list($flagLink_begin, $flagLink_end) = explode('|*|', $this->link_edit('|*|', 'tt_content', $contentTreeArr['el']['uid'], TRUE));
								$l10nInfo = $flagLink_begin.'<em>[Click to translate FlexForm]</em>'.$flagLink_end;
								$this->global_localization_status[$sys_language_uid][]=array(
									'status' => 'flex',
									'parent_uid' => $contentTreeArr['el']['uid'],
									'sys_language' => $contentTreeArr['el']['sys_language_uid']
								);
							// }
						break;
					}

					if ($l10nInfo && $BE_USER->checkLanguageAccess($sys_language_uid))	{
						$tRows[]='
							<tr class="bgColor4">
								<td width="1%">'.$flagLink_begin.($sLInfo['flagIcon'] ? '<img src="'.$sLInfo['flagIcon'].'" alt="'.htmlspecialchars($sLInfo['title']).'" title="'.htmlspecialchars($sLInfo['title']).'" />' : $sLInfo['title']).$flagLink_end.'</td>
								<td width="99%">'.$l10nInfo.'</td>
							</tr>';
					}
				}
			}

			$output = count($tRows) ? '
				<table border="0" cellpadding="0" cellspacing="1" width="100%" class="lrPadding">
					<tr class="bgColor4-20">
						<td colspan="2">' . $LANG->getLL('element_localizations',1) . ':</td>
					</tr>
					'.implode('',$tRows).'
				</table>
			' : '';
		}
		return $output;
	}






	/*******************************************
	 *
	 * Outline rendering:
	 *
	 *******************************************/

	/**
	 * Rendering the outline display of the page structure
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	[type]		$contentTreeArr: ...
	 * @return	string		HTML
	 */
	function render_outline($singleView, $contentTreeArr) {
		global $LANG;

			// Load possible website languages:
		$this->translatedLanguagesArr_isoCodes = array();
		foreach($this->translatedLanguagesArr as $langInfo)	{
			if ($langInfo['ISOcode'])	{
				$this->translatedLanguagesArr_isoCodes['all_lKeys'][]='l'.$langInfo['ISOcode'];
				$this->translatedLanguagesArr_isoCodes['all_vKeys'][]='v'.$langInfo['ISOcode'];
			}
		}

			// Rendering the entries:
		$entries = array();
		$this->render_outline_element($singleView, $contentTreeArr,$entries);

			// Header of table:
		$output='';
		$output.='<tr class="bgColor5 tableheader">
				<td class="nobr">'.$LANG->getLL('outline_header_title',1).'</td>
				<td class="nobr">'.$LANG->getLL('outline_header_controls',1).'</td>
				<td class="nobr">'.$LANG->getLL('outline_header_status',1).'</td>
				<td class="nobr">'.$LANG->getLL('outline_header_element',1).'</td>
			</tr>';

			// Render all entries:
		foreach($entries as $entry)	{

				// Create indentation code:
			$indent = '';
			for($a=0;$a<$entry['indentLevel'];$a++)	{
				$indent.='&nbsp;&nbsp;&nbsp;&nbsp;';
			}

				// Create status for FlexForm XML:
				// WARNING: Also this section contains cleaning of XML which is sort of mixing functionality but a quick and easy solution for now.
				// @Robert: How would you like this implementation better? Please advice and I will change it according to your wish!
			$status = '';
			if ($entry['table'] && $entry['uid'])	{
				$flexObj = t3lib_div::makeInstance('t3lib_flexformtools');
				$recRow = t3lib_BEfunc::getRecord($entry['table'], $entry['uid']);
				if ($recRow['tx_templavoila_flex'])	{

						// Clean XML:
					$newXML = $flexObj->cleanFlexFormXML($entry['table'],'tx_templavoila_flex',$recRow);

						// If the clean-all command is sent AND there is a difference in current/clean XML, save the clean:
					if ((t3lib_div::_POST('_CLEAN_XML_ALL') ||
						 t3lib_div::_POST('_CLEAN_XML_ALL_x')) && (md5($recRow['tx_templavoila_flex']) != md5($newXML))) {
						$dataArr = array();
						$dataArr[$entry['table']][$entry['uid']]['tx_templavoila_flex'] = $newXML;

							// Init TCEmain object and store:
						$tce = t3lib_div::makeInstance('t3lib_TCEmain');
						$tce->stripslashes_values=0;
						$tce->start($dataArr,array());
						$tce->process_datamap();

							// Re-fetch record:
						$recRow = t3lib_BEfunc::getRecord($entry['table'], $entry['uid']);
					}

						// Render status:
					$xmlUrl = $this->cm2Script.'viewRec[table]='.$entry['table'].'&viewRec[uid]='.$entry['uid'].'&viewRec[field_flex]=tx_templavoila_flex';

					if (md5($recRow['tx_templavoila_flex']) != md5($newXML)) {
						$status = $this->doc->icons(1).'<a href="'.htmlspecialchars($xmlUrl).'">'.$LANG->getLL('outline_status_dirty',1).'</a><br />';
						$this->xmlCleanCandidates = TRUE;
					} else {
						$status = $this->doc->icons(-1).'<a href="'.htmlspecialchars($xmlUrl).'">'.$LANG->getLL('outline_status_clean',1).'</a><br />';
					}
				}
			}

				// Compile table row:
			$output.='<tr class="'.($entry['isNewVersion']?'bgColor5':'bgColor4').'" style="'.$entry['elementTitlebarStyle'].'">
					<td class="nobr">'.$indent.$entry['flag'].$entry['icon'].$entry['title'].'</td>
					<td class="nobr">'.$entry['controls'].'</td>
					<td>'.$status.$entry['warnings'].($entry['isNewVersion']?$this->doc->icons(1).'New version!':'').'</td>
					<td class="nobr">'.htmlspecialchars($entry['id'] ? $entry['id'] : $entry['table'].':'.$entry['uid']).'</td>
				</tr>';
		}

		$output = '<table border="0" cellpadding="1" cellspacing="1" class="tv-elist">'.$output.'</table>';

			// Show link for cleaning all XML structures:
		if (!$singleView && $this->xmlCleanCandidates) {
			$output.= '<br />
				'. t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', 'outline_status_cleanall', $this->doc->backPath).'
				<input type="submit" value="'.$LANG->getLL('outline_status_cleanAll',1).'" name="_CLEAN_XML_ALL" /><br /><br />
			';
		}

		return $output;
	}

	/**
	 * Rendering a single element in outline:
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	array		$entries: Entries accumulated in this array (passed by reference)
	 * @param	integer		$indentLevel: Indentation level
	 * @param	array		$parentPointer: Element position in structure
	 * @param	string		$controls: HTML for controls to add for this element
	 * @param	[type]		$controls: ...
	 * @return	void
	 * @access protected
	 * @see	render_outline_allSheets()
	 */
	function render_outline_element($singleView, $contentTreeArr, &$entries, $indentLevel=0, $parentPointer=array(), $controls='') {
		global $LANG, $TYPO3_CONF_VARS;

			// Get record of element:
		$elementBelongsToCurrentPage = $contentTreeArr['el']['table'] == 'pages' || $contentTreeArr['el']['pid'] == $this->rootElementUid_pidForContent;

			// Prepare the record icon including a context sensitive menu link wrapped around it:
		$recordIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,$contentTreeArr['el']['icon'],'width="18" height="16"').' border="0" title="'.htmlspecialchars('['.$contentTreeArr['el']['table'].':'.$contentTreeArr['el']['uid'].']').'" alt="" />';
		$titleBarLeftButtons = $this->translatorMode ? $recordIcon : $this->doc->wrapClickMenuOnIcon($recordIcon,$contentTreeArr['el']['table'], $contentTreeArr['el']['uid'], 1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter,delete');
		$titleBarLeftButtons.= $this->getRecordStatHookValue($contentTreeArr['el']['table'],$contentTreeArr['el']['uid']);

			// Prepare table specific settings:
		switch ($contentTreeArr['el']['table']) {
			case 'pages':
				$titleBarLeftButtons .= $this->translatorMode ? '' :
					$this->icon_edit($contentTreeArr['el']) .
					$this->icon_hide($contentTreeArr['el']) .
					$this->icon_view($contentTreeArr['el']);
				$titleBarRightButtons = '';

				if ($singleView)
					$titleBarLeftButtons =
					($this->localizationObj ?
						$this->localizationObj->sidebar_renderItem_renderLanguageSelectorbox_pure_actual() : '') . ' ';
			break;
			case 'tt_content':
				$languageUid = $contentTreeArr['el']['sys_language_uid'];

				if ($this->translatorMode)	{
					$titleBarRightButtons = '';
				} else {
						// Create CE specific buttons:
					$linkMakeLocal =
						$this->icon_makeLocal($parentPointer, !$elementBelongsToCurrentPage);
					$linkEdit = ($elementBelongsToCurrentPage ?
						$this->icon_edit($contentTreeArr['el']) .
						$this->icon_hide($contentTreeArr['el']) : '');
					$linkUnlink =
						$this->icon_unlink($parentPointer, $elementBelongsToCurrentPage ? 1 : 0);

					$titleBarRightButtons = $linkEdit . $linkMakeLocal . $this->clipboardObj->element_getSelectButtons ($parentPointer) . $linkUnlink;
				}
			break;
		}

			// Prepare the language icon:
		$languageLabel = htmlspecialchars ($this->allAvailableLanguages[$contentTreeArr['el']['sys_language_uid']]['title']);
		$languageIcon = $this->allAvailableLanguages[$languageUid]['flagIcon'] ? '<img src="'.$this->allAvailableLanguages[$languageUid]['flagIcon'].'" title="'.$languageLabel.'" alt="'.$languageLabel.'" />' : ($languageLabel && $languageUid ? '['.$languageLabel.']' : '');

			// If there was a langauge icon and the language was not default or [all] and if that langauge is accessible for the user, then wrap the flag with an edit link (to support the "Click the flag!" principle for translators)
		if ($languageIcon && $languageUid>0 && $GLOBALS['BE_USER']->checkLanguageAccess($languageUid) && $contentTreeArr['el']['table']==='tt_content')	{
			$languageIcon = $this->link_edit($languageIcon, 'tt_content', $contentTreeArr['el']['uid'], TRUE);
		}

			// Create warning messages if neccessary:
		$warnings = '';
		if ($this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']] > 1 && $this->rootElementLangParadigm !='free') {
			$warnings .= '<br />'.$this->doc->icons(2).' <em>'.htmlspecialchars(sprintf($LANG->getLL('warning_elementusedmorethanonce',''), $this->global_tt_content_elementRegister[$contentTreeArr['el']['uid']], $contentTreeArr['el']['uid'])).'</em>';
		}

			// Displaying warning for container content (in default sheet - a limitation) elements if localization is enabled:
		$isContainerEl = count($contentTreeArr['sub']['sDEF']);
		if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning'] && $this->rootElementLangParadigm !='free' && $isContainerEl && $contentTreeArr['el']['table'] === 'tt_content' && $contentTreeArr['el']['CType'] === 'templavoila_pi1' && !$contentTreeArr['ds_meta']['langDisable'])	{
			if ($contentTreeArr['ds_meta']['langChildren'])	{
				if (!$this->modTSconfig['properties']['disableContainerElementLocalizationWarning_warningOnly']) {
					$warnings .= '<br />'.$this->doc->icons(2).' <b>'.$LANG->getLL('warning_containerInheritance_short').'</b>';
				}
			} else {
				$warnings .= '<br />'.$this->doc->icons(3).' <b>'.$LANG->getLL('warning_containerSeparate_short').'</b>';
			}
		}

			// Create entry for this element:
		$entries[] = array(
			'indentLevel' => $indentLevel,
			'icon' => $titleBarLeftButtons,
			'title' => ($elementBelongsToCurrentPage?'':'<em>').htmlspecialchars($contentTreeArr['el']['title']).($elementBelongsToCurrentPage ? '' : '</em>'),
			'warnings' => $warnings,
			'controls' => $titleBarRightButtons.$controls,
			'table' => $contentTreeArr['el']['table'],
			'uid' =>  $contentTreeArr['el']['uid'],
			'flag' => $languageIcon,
			'isNewVersion' => $contentTreeArr['el']['_ORIG_uid'] ? TRUE : FALSE,
			'elementTitlebarStyle' => (!$elementBelongsToCurrentPage ? 'background-color: '.$this->doc->bgColor6 : '')
		);


			// Create entry for localizaitons...
		$this->render_outline_localizations($contentTreeArr, $entries, $indentLevel+1);

			// Create entries for sub-elements in all sheets:
		if ($contentTreeArr['sub'])	{
			foreach($contentTreeArr['sub'] as $sheetKey => $sheetInfo)	{
				if (is_array($sheetInfo))	{
					$this->render_outline_subElements($contentTreeArr, $sheetKey, $entries, $indentLevel+1);
				}
			}
		}
	}

	/**
	 * Rendering outline for child-elements
	 *
	 * @param	array		$contentTreeArr: DataStructure info array (the whole tree)
	 * @param	string		$sheet: Which sheet to display
	 * @param	array		$entries: Entries accumulated in this array (passed by reference)
	 * @param	integer		$indentLevel: Indentation level
	 * @return	void
	 * @access protected
	 */
	function render_outline_subElements($contentTreeArr, $sheet, &$entries, $indentLevel)	{
		global $LANG;

		$canCreateNew = $GLOBALS['BE_USER']->isPSet($this->calcPerms, 'pages', 'new');

			// Define l/v keys for current language:
		$langChildren = intval($contentTreeArr['ds_meta']['langChildren']);
		$langDisable = intval($contentTreeArr['ds_meta']['langDisable']);
		$lKeys = $langDisable ? array('lDEF') : ($langChildren ? array('lDEF') : $this->translatedLanguagesArr_isoCodes['all_lKeys']);
		$vKeys = $langDisable ? array('vDEF') : ($langChildren ? $this->translatedLanguagesArr_isoCodes['all_vKeys'] : array('vDEF'));

			// Traverse container fields:
		foreach($lKeys as $lKey)	{

				// Traverse fields:
			if (is_array($contentTreeArr['sub'][$sheet][$lKey]))	{
				foreach($contentTreeArr['sub'][$sheet][$lKey] as $fieldID => $fieldValuesContent) {
					foreach($vKeys as $vKey) {

						if (is_array($fieldValuesContent[$vKey]))	{
							$fieldContent = $fieldValuesContent[$vKey];

								// Create flexform pointer pointing to "before the first sub element":
							$subElementPointer = array (
								'table' => $contentTreeArr['el']['table'],
								'uid' => $contentTreeArr['el']['uid'],
								'sheet' => $sheet,
								'sLang' => $lKey,
								'field' => $fieldID,
								'vLang' => $vKey,
								'position' => 0
							);

								// "Browse", "New" and "Paste" icon:
							$controls = $this->icon_browse($subElementPointer);

							if (!$this->translatorMode && $canCreateNew) {
								$controls .= $this->icon_new($subElementPointer);
							}

							$controls .= $this->clipboardObj->element_getPasteButtons($subElementPointer);

								// Add entry for lKey level:
							$specialPath = ($sheet!='sDEF'?'<'.$sheet.'>':'').($lKey!='lDEF'?'<'.$lKey.'>':'').($vKey!='vDEF'?'<'.$vKey.'>':'');
							$entries[] = array(
								'indentLevel' => $indentLevel,
								'icon' => '',
								'title' => '<b>'.$LANG->sL($fieldContent['meta']['title'],1).'</b>'.($specialPath ? ' <em>'.htmlspecialchars($specialPath).'</em>' : ''),
								'id' => '<'.$sheet.'><'.$lKey.'><'.$fieldID.'><'.$vKey.'>',
								'controls' => $controls
							);

								// Render the list of elements (and possibly call itself recursively if needed):
							if (is_array($fieldContent['el_list']))	 {
								foreach($fieldContent['el_list'] as $position => $subElementKey)	{
									$subElementArr = $fieldContent['el'][$subElementKey];
									if (!$subElementArr['el']['isHidden'] || $this->MOD_SETTINGS['tt_content_showHidden'])	{

											// Modify the flexform pointer so it points to the position of the curren sub element:
										$subElementPointer['position'] = $position;

											// "Browse", "New" and "Paste" icon:
										$controls = $this->icon_browse($subElementPointer);

										if (!$this->translatorMode && $canCreateNew) {
											$controls .= $this->icon_new($subElementPointer);
										}

										$controls .= $this->clipboardObj->element_getPasteButtons($subElementPointer);

										$this->render_outline_element($singleView, $subElementArr, $entries, $indentLevel+1, $subElementPointer, $controls);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Renders localized elements of a record
	 *
	 * @param	array		$contentTreeArr: Part of the contentTreeArr for the element
	 * @param	array		$entries: Entries accumulated in this array (passed by reference)
	 * @param	integer		$indentLevel: Indentation level
	 * @return	string		HTML
	 * @access protected
	 * @see 	render_framework_singleSheet()
	 */
	function render_outline_localizations($contentTreeArr, &$entries, $indentLevel) {
		global $LANG, $BE_USER;

		if ($contentTreeArr['el']['table']=='tt_content' && $contentTreeArr['el']['sys_language_uid']<=0)	{

				// Traverse the available languages of the page (not default and [All])
			foreach($this->translatedLanguagesArr as $sys_language_uid => $sLInfo)	{
				if ($sys_language_uid > 0 && $BE_USER->checkLanguageAccess($sys_language_uid))	{
					switch((string)$contentTreeArr['localizationInfo'][$sys_language_uid]['mode'])	{
						case 'exists':

								// Get localized record:
							$olrow = t3lib_BEfunc::getRecordWSOL('tt_content',$contentTreeArr['localizationInfo'][$sys_language_uid]['localization_uid']);

								// Put together the records icon including content sensitive menu link wrapped around it:
							$recordIcon_l10n = $this->getRecordStatHookValue('tt_content', $olrow['uid']).
								t3lib_iconWorks::getIconImage('tt_content',$olrow,$this->doc->backPath,'class="absmiddle" title="'.htmlspecialchars('[tt_content:'.$olrow['uid'].']').'"');
							if (!$this->translatorMode)	{
								$recordIcon_l10n = $this->doc->wrapClickMenuOnIcon($recordIcon_l10n,'tt_content',$olrow['uid'],1,'&amp;callingScriptId='.rawurlencode($this->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter');
							}

							list($flagLink_begin, $flagLink_end) = explode('|*|', $this->link_edit('|*|', 'tt_content', $olrow['uid'], TRUE));

								// Create entry for this element:
							$entries[] = array(
								'indentLevel' => $indentLevel,
								'icon' => $recordIcon_l10n,
								'title' => t3lib_BEfunc::getRecordTitle('tt_content', $olrow),
								'table' => 'tt_content',
								'uid' =>  $olrow['uid'],
								'flag' => $flagLink_begin.($sLInfo['flagIcon'] ? '<img src="'.$sLInfo['flagIcon'].'" alt="'.htmlspecialchars($sLInfo['title']).'" title="'.htmlspecialchars($sLInfo['title']).'" />' : $sLInfo['title']).$flagLink_end,
								'isNewVersion' => $olrow['_ORIG_uid'] ? TRUE : FALSE,
							);
						break;
					}
				}
			}
		}
	}








	/*******************************************
	 *
	 * Link functions (protected)
	 *
	 *******************************************/

	/**
	 * Returns an HTML link for viewing
	 *
	 * @param	string		$label: The label (or image)
	 * @param	string		$table: The table, fx. 'tt_content'
	 * @param	integer		$uid: The uid of the element to be hidden/unhidden
	 * @param	boolean		$forced: By default the link is not shown if translatorMode is set, but with this boolean it can be forced anyway.
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_view($el) {
		global $LANG;

		$viewPageIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/zoom.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.showPage',1).'" alt="" />';

		$label = $viewPageIcon;

		return $this->link_view($label, $el['table'], $el['uid']);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$label: ...
	 * @param	[type]		$table: ...
	 * @param	[type]		$uid: ...
	 * @return	[type]		...
	 */
	function link_view($label, $table, $uid) {
		$onClick = t3lib_BEfunc::viewOnClick($uid, $this->doc->backPath, t3lib_BEfunc::BEgetRootLine($uid),'','',($this->currentLanguageUid?'&L='.$this->currentLanguageUid:''));

		return '<a href="#" onclick="'.htmlspecialchars($onClick).'">'.$label.'</a>';
	}

	/**
	 * Returns an HTML link for hiding
	 *
	 * @param	string		$label: The label (or image)
	 * @param	string		$table: The table, fx. 'tt_content'
	 * @param	integer		$uid: The uid of the element to be hidden/unhidden
	 * @param	boolean		$forced: By default the link is not shown if translatorMode is set, but with this boolean it can be forced anyway.
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_hide($el) {
		global $LANG;

		$hideIcon = ($el['table'] == 'pages'
		?	'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_hide.gif','').' border="0" title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:hidePage')).'" alt="" />'
		:	'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_hide.gif','').' border="0" title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:hide')).'" alt="" />');
		$unhideIcon = ($el['table'] == 'pages'
		?	'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_unhide.gif','').' border="0" title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:unHidePage')).'" alt="" />'
		:	'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/button_unhide.gif','').' border="0" title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:unHide')).'" alt="" />');

		if ($el['isHidden'])
			$label = $unhideIcon;
		else
			$label = $hideIcon;

		return $this->link_hide($label, $el['table'], $el['uid'], $el['isHidden']);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$label: ...
	 * @param	[type]		$table: ...
	 * @param	[type]		$uid: ...
	 * @param	[type]		$hidden: ...
	 * @param	[type]		$forced: ...
	 * @return	[type]		...
	 */
	function link_hide($label, $table, $uid, $hidden, $forced=FALSE) {
		if ($label) {
			if (($table == 'pages' && ($this->calcPerms & 2) ||
				 $table != 'pages' && ($this->calcPerms & 16)) &&
				(!$this->translatorMode || $forced))	{
					if ($table == "pages" && $this->currentLanguageUid) {
						$params = '&data['.$table.']['.$uid.'][hidden]=' . (1 - $hidden);
					//	return '<a href="#" onclick="' . htmlspecialchars('return jumpToUrl(\'' . $GLOBALS['SOBE']->doc->issueCommand($params, -1) . '\');') . '">'.$label.'</a>';
					} else {
						$params = '&data['.$table.']['.$uid.'][hidden]=' . (1 - $hidden);
					//	return '<a href="#" onclick="' . htmlspecialchars('return jumpToUrl(\'' . $GLOBALS['SOBE']->doc->issueCommand($params, -1) . '\');') . '">'.$label.'</a>';

						/* the commands are indipendent of the position,
						 * so sortable doesn't need to update these and we
						 * can safely use '#'
						 */
						if ($hidden)
							return '<a href="#" onclick="sortable_unhideRecord(this, \'' . $GLOBALS['SOBE']->doc->issueCommand($params, -1) . '\');">' . $label . '</a>';
						else
							return '<a href="#" onclick="sortable_hideRecord(this, \'' . $GLOBALS['SOBE']->doc->issueCommand($params, -1) . '\');">' . $label . '</a>';
					}
				} else {
					return $label;
				}
		}
		return '';
	}

	/**
	 * Returns an HTML link for editing
	 *
	 * @param	string		$label: The label (or image)
	 * @param	string		$table: The table, fx. 'tt_content'
	 * @param	integer		$uid: The uid of the element to be edited
	 * @param	boolean		$forced: By default the link is not shown if translatorMode is set, but with this boolean it can be forced anyway.
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_edit($el) {
		global $LANG;

		$editIcon = ($el['table'] == 'pages'
		?	'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','').' border="0" title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:editPage')).'" alt="" />'
		:	'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','').' border="0" title="'.htmlspecialchars($LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit')).'" alt="" />');

		$label = $editIcon;

		return $this->link_edit($label, $el['table'], $el['uid']);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$label: ...
	 * @param	[type]		$table: ...
	 * @param	[type]		$uid: ...
	 * @param	[type]		$forced: ...
	 * @return	[type]		...
	 */
	function link_edit($label, $table, $uid, $forced=FALSE)	{
		if ($label) {
			if (($table == 'pages' && ($this->calcPerms & 2) ||
				$table != 'pages' && ($this->calcPerms & 16)) &&
				(!$this->translatorMode || $forced))	{
					if ($table == "pages" && $this->currentLanguageUid) {
						return '<a href="'.$this->baseScript.$this->link_getParameters().'&amp;editPageLanguageOverlay='.$this->currentLanguageUid.'">'.$label.'</a>';
					} else {
						$onClick = t3lib_BEfunc::editOnClick('&edit['.$table.']['.$uid.']=edit', $this->doc->backPath);
						return '<a href="#" onclick="'.htmlspecialchars($onClick).'">'.$label.'</a>';
					}
				} else {
					return $label;
				}
		}

		return '';
	}

	/**
	 * Returns an HTML link for browsing an existing record
	 *
	 * @param	string		$label: The label (or image)
	 * @param	array		$parentPointer: Flexform pointer defining the parent element of the new record
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_browse($parentPointer) {
		global $LANG;

		$browseIcon = '<img class="browse"'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/insert3.gif','').' border="0" title="'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.browse_db').'" alt="" />';
		$insertIcon = '<img class="browse"'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/plusbullet2.gif','').' border="0" title="'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.browse_db').'" alt="" />';

		$p = $this->apiObj->flexform_getStringFromPointer($parentPointer);
		$b = $GLOBALS['BE_USER']->getSessionData('lastPasteRecord');

		if ($p == $b)
			$label = $insertIcon;
		else
			$label = $browseIcon;

		$parameters =
			$this->link_getParameters().
		//	'&amp;CB[removeAll]=normal'.
			'&amp;pasteRecord=ref'.
			'&amp;source='.rawurlencode('###').
			'&amp;destination='.rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer));
		$browser =
			'browserPos = this;' .
			'setFormValueOpenBrowser(\'db\',\'browser[communication]|||tt_content\');'.
			'return false;';

		return '<a href="#" id="'.$this->baseScript.$parameters.'" onclick="'.$browser.'">'.$label.'</a>';
	}

	/**
	 * Returns an HTML link for creating a new record
	 *
	 * @param	string		$label: The label (or image)
	 * @param	array		$parentPointer: Flexform pointer defining the parent element of the new record
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_new($parentPointer) {
		global $LANG;

		$newIcon = '<img class="new"'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_el.gif','').' border="0" title="'.$LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:newRecordGeneral').'" alt="" />';

		$label = $newIcon;

		return $this->link_new($label, $parentPointer);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$label: ...
	 * @param	[type]		$parentPointer: ...
	 * @return	[type]		...
	 */
	function link_new($label, $parentPointer) {
		$parameters =
			$this->link_getParameters().
			'&amp;parentRecord='.rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer));

		return '<a href="'. $this->newElementWizardLink . $parameters.'">'.$label.'</a>';
	}

	/**
	 * Returns an HTML link for unlinking a content element. Unlinking means that the record still exists but
	 * is not connected to any other content element or page.
	 *
	 * @param	string		$label: The label
	 * @param	array		$unlinkPointer: Flexform pointer pointing to the element to be unlinked
	 * @param	boolean		$realDelete: If set, the record is not just unlinked but deleted!
	 * @return	string		HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function icon_unlink($unlinkPointer, $realDelete=0) {
		global $LANG;

		$deleteIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/garbage.gif','').' title="'.$LANG->getLL('deleteRecord').'" border="0" alt="" />';
		$unlinkIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, t3lib_extMgm::extRelPath('templavoila') . 'res/link_delete.png','').' title="'.$LANG->getLL('unlinkRecord').'" border="0" alt="" />';
		$emptyIcon  = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/clear.gif','width="16" height="16"').' title="" border="0" alt="" />';

		if ($realDelete == 2) {
			return str_replace('<a ', '<a style="visibility: hidden;" ',
				   $this->link_unlink($unlinkIcon, $unlinkPointer, FALSE)) .
				   $this->link_unlink($deleteIcon, $unlinkPointer, TRUE);
		}
		else if ($realDelete == 1) {
			return $this->link_unlink($unlinkIcon, $unlinkPointer, FALSE) .
				   $this->link_unlink($deleteIcon, $unlinkPointer, TRUE);
		}
		else {
			return $this->link_unlink($unlinkIcon, $unlinkPointer, FALSE) .
				   str_replace('<a ', '<a style="visibility: hidden;" ',
				   $this->link_unlink($deleteIcon, $unlinkPointer, TRUE));
		}
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$label: ...
	 * @param	[type]		$unlinkPointer: ...
	 * @param	[type]		$realDelete: ...
	 * @return	[type]		...
	 */
	function link_unlink($label, $unlinkPointer, $realDelete=FALSE)	{
		global $LANG;

		$unlinkPointerString = rawurlencode($this->apiObj->flexform_getStringFromPointer($unlinkPointer));

		if ($realDelete) {
			return '<a href="javascript:'.htmlspecialchars('if (confirm(' . $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('deleteRecordMsg')) . '))') . ' sortable_deleteRecord(\'' . $unlinkPointerString . '\');">' . $label . '</a>';
//			return '<a href="' . $this->baseScript.$this->link_getParameters() . '&amp;deleteRecord=' . $unlinkPointerString . '" onclick="' . htmlspecialchars('return confirm(' . $LANG->JScharCode($LANG->getLL('deleteRecordMsg')) . ');') . '">' . $label . '</a>';
		} else {
			return '<a href="javascript:'.htmlspecialchars('if (confirm(' . $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('unlinkRecordMsg')) . '))') . ' sortable_unlinkRecord(\'' . $unlinkPointerString . '\');" class="onoff">' . $label . '</a>';
//			return '<a href="' . $this->baseScript.$this->link_getParameters() . '&amp;unlinkRecord=' . $unlinkPointerString . '" onclick="' . htmlspecialchars('return confirm(' . $LANG->JScharCode($LANG->getLL('unlinkRecordMsg')) . ');') . '">' . $label . '</a>';
		}
	}

	/**
	 * Returns an HTML link for making a reference content element local to the page (copying it).
	 *
	 * @param	string		$label: The label
	 * @param	array		$makeLocalPointer: Flexform pointer pointing to the element which shall be copied
	 * @return	string		HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function icon_makeLocal($makeLocalPointer, $realDup=0) {
		global $LANG;

		$dupIcon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,t3lib_extMgm::extRelPath('templavoila').'mod1/makelocalcopy.gif','').' title="'.$LANG->getLL('makeLocal').'" border="0" alt="" />';

		$label = $dupIcon;

		if ($realDup)
			return $this->link_makeLocal($label, $makeLocalPointer);
		else
			return '';
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$label: ...
	 * @param	[type]		$makeLocalPointer: ...
	 * @return	[type]		...
	 */
	function link_makeLocal($label, $makeLocalPointer) {
		global $LANG;

		return '<a href="'.$this->baseScript.$this->link_getParameters().'&amp;makeLocalRecord='.rawurlencode($this->apiObj->flexform_getStringFromPointer($makeLocalPointer)).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('makeLocalMsg')).');').'">'.$label.'</a>';
	}

	/**
	 * Creates additional parameters which are used for linking to the current page while editing it
	 *
	 * @return	string		parameters
	 * @access public
	 */
	function link_getParameters()	{
		$output =
			'id='.$this->id.
			(is_array($this->altRoot) ? t3lib_div::implodeArrayForUrl('altRoot',$this->altRoot) : '') .
			($this->versionId ? '&amp;versionId='.rawurlencode($this->versionId) : '');

		return $output;
	}





	/*************************************************
	 *
	 * Processing and structure functions (protected)
	 *
	 *************************************************/

	/**
	 * Checks various GET / POST parameters for submitted commands and handles them accordingly.
	 * All commands will trigger a redirect by sending a location header after they work is done.
	 *
	 * Currently supported commands: 'clearCache', 'createNewRecord', 'unlinkRecord', 'deleteRecord', 'pasteRecord',
	 * 'makeLocalRecord', 'localizeElement', 'createNewPageTranslation' and 'editPageLanguageOverlay'
	 *
	 * @return	void
	 * @access protected
	 */
	function handleIncomingCommands() {
		$possibleCommands = array('clearCache', 'createNewRecord', 'unlinkRecord', 'deleteRecord','pasteRecord', 'makeLocalRecord', 'localizeElement', 'createNewPageTranslation', 'editPageLanguageOverlay');

		foreach ($possibleCommands as $command) {
			if (($commandParameters = t3lib_div::_GP($command)) != '') {
				$redirectLocation = $this->baseScript . $this->link_getParameters();

				switch ($command) {
					case 'clearCache':
						$this->clearCache();
						break;

					case 'createNewRecord':
							// Historically "defVals" has been used for submitting the preset row data for the new element, so we still support it here:
						$defVals = t3lib_div::_GP('defVals');
						$newRow = is_array ($defVals['tt_content']) ? $defVals['tt_content'] : array();

							// Create new record and open it for editing
						$destinationPointer = $this->apiObj->flexform_getPointerFromString($commandParameters);
						$newUid = $this->apiObj->insertElement($destinationPointer, $newRow);
							// TODO If $newUid==0, than we could create new element. Need to handle it...
						$redirectLocation = $GLOBALS['BACK_PATH'].'alt_doc.php?edit[tt_content]['.$newUid.']=edit&returnUrl='.rawurlencode($this->mod1Script.$this->link_getParameters());
						break;

					case 'unlinkRecord':
						$unlinkDestinationPointer = $this->apiObj->flexform_getPointerFromString($commandParameters);
						$this->apiObj->unlinkElement($unlinkDestinationPointer);
						break;

					case 'deleteRecord':
						$deleteDestinationPointer = $this->apiObj->flexform_getPointerFromString($commandParameters);
						$this->apiObj->deleteElement($deleteDestinationPointer);
						break;

					case 'pasteRecord':
						$sourcePointer = $this->apiObj->flexform_getPointerFromString (t3lib_div::_GP('source'));
						$destinationPointer = $this->apiObj->flexform_getPointerFromString (t3lib_div::_GP('destination'));
						switch ($commandParameters) {
							case 'copy' :	$this->apiObj->copyElement ($sourcePointer, $destinationPointer); break;
							case 'copyref':	$this->apiObj->copyElement ($sourcePointer, $destinationPointer, FALSE); break;
							case 'cut':	$this->apiObj->moveElement ($sourcePointer, $destinationPointer); break;
							case 'ref':	list(,$uid) = explode(SEPARATOR_PARMS, t3lib_div::_GP('source'));
									$this->apiObj->referenceElementByUid ($uid, $destinationPointer);
							break;
						}
						$destinationPointer['position'] = 1 + $destinationPointer['position'];
						$GLOBALS['BE_USER']->setAndSaveSessionData('lastPasteRecord', $this->apiObj->flexform_getStringFromPointer($destinationPointer));
						break;

					case 'makeLocalRecord':
						$sourcePointer = $this->apiObj->flexform_getPointerFromString ($commandParameters);
						$this->apiObj->copyElement ($sourcePointer, $sourcePointer);
						$this->apiObj->unlinkElement ($sourcePointer);
						break;

					case 'localizeElement':
						$sourcePointer = $this->apiObj->flexform_getPointerFromString (t3lib_div::_GP('source'));
						$this->apiObj->localizeElement ($sourcePointer, $commandParameters);
						break;

					case 'createNewPageTranslation':
							// Create parameters and finally run the classic page module for creating a new page translation
						$params = '&edit[pages_language_overlay]['.intval (t3lib_div::_GP('pid')).']=new&overrideVals[pages_language_overlay][sys_language_uid]='.intval($commandParameters);
						$returnUrl = '&returnUrl='.rawurlencode($this->mod1Script.$this->link_getParameters());
						$redirectLocation = $GLOBALS['BACK_PATH'].'alt_doc.php?'.$params.$returnUrl;
						break;

					case 'editPageLanguageOverlay':
							// Look for pages language overlay record for language:
						$sys_language_uid = intval($commandParameters);
						$params = '';
						if ($sys_language_uid != 0) {
							// Edit overlay record
							list($pLOrecord) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
									'*',
									'pages_language_overlay',
									'pid='.intval($this->id).' AND sys_language_uid='.$sys_language_uid.
										t3lib_BEfunc::deleteClause('pages_language_overlay').
										t3lib_BEfunc::versioningPlaceholderClause('pages_language_overlay')
								);
							if ($pLOrecord) {
								t3lib_beFunc::workspaceOL('pages_language_overlay', $pLOrecord);
								if (is_array($pLOrecord))	{
									$params = '&edit[pages_language_overlay]['.$pLOrecord['uid'].']=edit';
								}
							}
						}
						else {
							// Edit default language (page properties)
							// No workspace overlay because we already on this page
							$params = '&edit[pages]['.intval($this->id).']=edit';
						}
						if ($params) {
							$returnUrl = '&returnUrl='.rawurlencode($this->mod1Script.$this->link_getParameters());
							$redirectLocation = $GLOBALS['BACK_PATH'].'alt_doc.php?'.$params.$returnUrl;	//.'&localizationMode=text';
						}
						break;
				}
			}
		}

		if (isset($redirectLocation)) {
			header('Location: '.t3lib_div::locationHeaderUrl($redirectLocation));
		}
	}

	/**
	 * Clears page cache for the current id, $this->id
	 *
	 * @return	void
	 */
	function clearCache() {
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->stripslashes_values=0;
		$tce->start(Array(),Array());
		$tce->clear_cacheCmd($this->id);
	}






	/***********************************************
	 *
	 * Miscelleaneous helper functions (protected)
	 *
	 ***********************************************/

	/**
	 * Returns an array of available languages (to use for FlexForms)
	 *
	 * @param	integer		$id: If zero, the query will select all sys_language records from root level. If set to another value, the query will select all sys_language records that has a pages_language_overlay record on that page (and is not hidden, unless you are admin user)
	 * @param	boolean		$onlyIsoCoded: If set, only languages which are paired with a static_info_table / static_language record will be returned.
	 * @param	boolean		$setDefault: If set, an array entry for a default language is set.
	 * @param	boolean		$setMulti: If set, an array entry for "multiple languages" is added (uid -1)
	 * @return	array
	 * @access protected
	 */
	function getAvailableLanguages($id=0, $onlyIsoCoded=true, $setDefault=true, $setMulti=false)	{
		global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;

		t3lib_div::loadTCA ('sys_language');
		$flagAbsPath = t3lib_div::getFileAbsFileName($TCA['sys_language']['columns']['flag']['config']['fileFolder']);
		$flagIconPath = $BACK_PATH.'../'.substr($flagAbsPath, strlen(PATH_site));

		$output = array();
		$excludeHidden = $BE_USER->isAdmin() ? '1=1' : 'sys_language.hidden=0';

		if ($id) {
			$excludeHidden .= ' AND pages_language_overlay.deleted=0';
			$res = $TYPO3_DB->exec_SELECTquery(
				'DISTINCT sys_language.*',
				'pages_language_overlay,sys_language',
				'pages_language_overlay.sys_language_uid=sys_language.uid AND pages_language_overlay.pid='.intval($id).' AND '.$excludeHidden,
				'',
				'sys_language.title'
			);
		} else {
			$res = $TYPO3_DB->exec_SELECTquery(
				'sys_language.*',
				'sys_language',
				$excludeHidden,
				'',
				'sys_language.title'
			);
		}

		if ($setDefault) {
			$output[0]=array(
				'uid' => 0,
				'title' => strlen ($this->modSharedTSconfig['properties']['defaultLanguageLabel']) ? $this->modSharedTSconfig['properties']['defaultLanguageLabel'] : $LANG->getLL('defaultLanguage'),
				'ISOcode' => 'DEF',
				'flagIcon' => strlen($this->modSharedTSconfig['properties']['defaultLanguageFlag']) && @is_file($flagAbsPath.$this->modSharedTSconfig['properties']['defaultLanguageFlag']) ? $flagIconPath.$this->modSharedTSconfig['properties']['defaultLanguageFlag'] : null,
			);
		}

		if ($setMulti) {
			$output[-1]=array(
				'uid' => -1,
				'title' => $LANG->getLL ('multipleLanguages'),
				'ISOcode' => 'DEF',
				'flagIcon' => $flagIconPath.'multi-language.gif',
			);
		}

		while(TRUE == ($row = $TYPO3_DB->sql_fetch_assoc($res)))	{
			t3lib_BEfunc::workspaceOL('sys_language', $row);
			$output[$row['uid']]=$row;

			if ($row['static_lang_isocode'])	{
				$staticLangRow = t3lib_BEfunc::getRecord('static_languages',$row['static_lang_isocode'],'lg_iso_2');
				if ($staticLangRow['lg_iso_2']) {
					$output[$row['uid']]['ISOcode'] = $staticLangRow['lg_iso_2'];
				}
			}
			if (strlen($row['flag'])) {
				$output[$row['uid']]['flagIcon'] = @is_file($flagAbsPath.$row['flag']) ? $flagIconPath.$row['flag'] : '';
			}

			if ($onlyIsoCoded && !$output[$row['uid']]['ISOcode']) unset($output[$row['uid']]);
		}

		return $output;
	}

	/**
	 * Returns an array of registered instantiated classes for a certain hook.
	 *
	 * @param	string		$hookName: Name of the hook
	 * @return	array		Array of object references
	 * @access protected
	 */
	function hooks_prepareObjectsArray ($hookName) {
		global $TYPO3_CONF_VARS;

		$hookObjectsArr = array();
		if (is_array ($TYPO3_CONF_VARS['EXTCONF']['templavoila']['mod1'][$hookName])) {
			foreach ($TYPO3_CONF_VARS['EXTCONF']['templavoila']['mod1'][$hookName] as $classRef) {
				$hookObjectsArr[] = &t3lib_div::getUserObj ($classRef);
			}
		}
		return $hookObjectsArr;
	}

	/**
	 * Checks if translation to alternative languages can be applied to this page.
	 *
	 * @return	boolean		<code>true</code> if alternative languages exist
	 */
	function alternativeLanguagesDefined() {
		return count($this->allAvailableLanguages) > 2;
	}

	/**
	 * Defines if an element is to be displayed in the TV page module (could be filtered out by language settings)
	 *
	 * @param	array		Sub element array
	 * @return	boolean		Display or not
	 */
	function displayElement($subElementArr)	{
			// Don't display when "selectedLanguage" is choosen
		$displayElement = !$this->MOD_SETTINGS['langDisplayMode'];
			// Set to true when current language is not an alteranative (in this case display all elements)
		$displayElement |= ($this->currentLanguageUid<=0);
			// When language of CE is ALL or default display it.
		$displayElement |= ($subElementArr['el']['sys_language_uid']<=0);
			// Display elements which have their language set to the currently displayed language.
		$displayElement |= ($this->currentLanguageUid==$subElementArr['el']['sys_language_uid']);

		return $displayElement;
	}

	/**
	 * Returns label, localized and converted to current charset. Label must be from FlexForm (= always in UTF-8).
	 *
	 * @param	string		$label	Label
	 * @param	boolean		$hsc	<code>true</code> if HSC required
	 * @return	string		Converted label
	 */
	function localizedFFLabel($label, $hsc) {
		global	$LANG, $TYPO3_CONF_VARS;

		$charset = $LANG->origCharSet;
		if ($LANG->origCharSet != $TYPO3_CONF_VARS['BE']['forceCharset']) {
			$LANG->origCharSet = $TYPO3_CONF_VARS['BE']['forceCharset'];
		}
		$result = $LANG->hscAndCharConv($label, $hsc);
		$LANG->origCharSet = $charset;
		return $result;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$table: ...
	 * @param	[type]		$id: ...
	 * @return	[type]		...
	 */
	function getRecordStatHookValue($table,$id)	{
			// Call stats information hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']))	{
			$stat='';
			$_params = array($table,$id);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks'] as $_funcRef)	{
				$stat.=t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
			return $stat;
		}
	}
/*
	function hasFCEAccess($row) {
		$params = array(
			'table' => 'tt_content',
			'row' => $row
		);
		$ref = null;
		return t3lib_div::callUserFunction('EXT:templavoila/class.tx_templavoila_access.php:&tx_templavoila_access->recordEditAccessInternals', $params, $ref);
	}
*/
}

//	// Make instance:
//$SOBE = t3lib_div::makeInstance('tx_templavoila_module1');
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
class tx_templavoila_module1_integral extends tx_templavoila_module1 {

		// Internal, dynamic:
	var $be_user_Array;
	var $CALC_PERMS;
	var $pageinfo;

	/**
	 * Preparing menu content
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $BE_USER, $LANG;

		parent::menuConfig();

		$this->MOD_MENU['page'] =
			array(
				'preview'    => $LANG->getLL('page_display', 1),
				'preview_nu' => $LANG->getLL('page_display_nu', 1),
				'outline'    => $LANG->getLL('page_ouline', 1)
			);

		if (!$BE_USER->isAdmin()) {
			unset($this->MOD_MENU['page']['outline']);
		}

			// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.'.$this->MCONF['name']);

			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::GPvar('SET'), $this->MCONF['name']);
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
					<select style="width: 180px;" name="'.$elementName.'" onchange="'.htmlspecialchars($onChange).'">
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
		global $LANG;

		$options  = '<div id="options-menu">';
		$options .=  '<a href="#" class="toolbar-item">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/options.gif') . ' title="' . $LANG->getLL('page_settings', 1) . '" alt="Options" />' .
				'</a>';
		$options .= '<ul class="toolbar-item-menu" style="display: none; width: 205px;">';

		/* general option-group */
		{
			$link = $this->baseScript.$this->link_getParameters().'&SET[tt_content_showHidden]=###';

			$entries[] = '<li class="radio'.(!$this->MOD_SETTINGS['tt_content_showHidden']?' selected':'').'" name="tt_content_showHidden"><a href="' . str_replace('###', '', $link).'"'. '>' . $LANG->getLL('page_settings_hidden', 1) . '</a></li>';
			$entries[] = '<li class="radio'.( $this->MOD_SETTINGS['tt_content_showHidden']?' selected':'').'" name="tt_content_showHidden"><a href="' . str_replace('###', '1', $link).'"'.'>' . $LANG->getLL('page_settings_all', 1) . '</a></li>';

			$group = '<ul class="group">'.implode(chr(10), $entries).'</ul>';
			$options .= '<li class="group">' . $group . '</li>';
		}

		/* language option-group */
		if ($this->localizationObj && $this->alternativeLanguagesDefined()) {
			$group = $this->localizationObj->sidebar_renderItem_renderLanguageSelectorlist_pure_mode();

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
		global $BE_USER, $LANG, $BACK_PATH;

		if (!is_callable(array('t3lib_div', 'int_from_ver')) || t3lib_div::int_from_ver(TYPO3_version) < 4000000) {
			$this->content = 'Fatal error:This version of TemplaVoila does not work with TYPO3 versions lower than 4.0.0! Please upgrade your TYPO3 core installation.';
			return;
		}

		// Access check...
		if (is_array($this->altRoot)) {
			$access = true;
		}
		else {
			// The page will show only if there is a valid page and if this page may be viewed by the user
			$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
			$access = is_array($this->pageinfo) ? 1 : 0;
		}

		if ($access) {
				// calls from drag and drop
			if (t3lib_div::_GP("ajaxPasteRecord")) {
				$sourcePointer = $this->apiObj->flexform_getPointerFromString(t3lib_div::_GP('source'));
				$destinationPointer = $this->apiObj->flexform_getPointerFromString(t3lib_div::_GP('destination'));

				switch (t3lib_div::_GP("ajaxPasteRecord")) {
					case 'cut': $this->apiObj->moveElement($sourcePointer, $destinationPointer); exit;
					case 'ref': list(,$uid) = explode(SEPARATOR_PARMS, t3lib_div::_GP('source'));
							$this->apiObj->referenceElementByUid($uid, $destinationPointer); exit;
				}
			}

			if (t3lib_div::_GP("ajaxUnlinkRecord")) {
				$unlinkDestinationPointer = $this->apiObj->flexform_getPointerFromString(t3lib_div::_GP('ajaxUnlinkRecord'));
				$this->apiObj->unlinkElement($unlinkDestinationPointer);
				exit;
			}

			if (t3lib_div::_GP("ajaxDeleteRecord")) {
				$deleteDestinationPointer = $this->apiObj->flexform_getPointerFromString(t3lib_div::_GP('ajaxDeleteRecord'));
				$this->apiObj->deleteElement($deleteDestinationPointer);
				exit;
			}

			if (t3lib_div::_GP("ajaxClearCache")) {
				$this->clearCache();
				exit;
			}

			$this->calcPerms =
			$this->CALC_PERMS = $BE_USER->calcPerms($this->pageinfo);
			if ($BE_USER->user['admin'] && !$this->id)	{
				$this->pageinfo=array('title' => '[root-level]','uid'=>0,'pid'=>0);
			}

				// Define the root element record:
			$this->rootElementTable = is_array($this->altRoot) ? $this->altRoot['table'] : 'pages';
			$this->rootElementUid = is_array($this->altRoot) ? $this->altRoot['uid'] : $this->id;
			$this->rootElementRecord = t3lib_BEfunc::getRecordWSOL($this->rootElementTable, $this->rootElementUid, '*');
			$this->rootElementUid_pidForContent = $this->rootElementRecord['t3ver_swapmode']==0 && $this->rootElementRecord['_ORIG_uid'] ? $this->rootElementRecord['_ORIG_uid'] : $this->rootElementRecord['uid'];

				// Check if we have to update the pagetree:
			if (t3lib_div::_GP('updatePageTree')) {
				t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
			}

			$this->doc = t3lib_div::makeInstance('template');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->setModuleTemplate('templates/page.html');
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
				body#ext-templavoila-mod1-index-php {
					height: 100%;
					margin: 0pt;
					overflow: hidden;
					padding: 0pt;
				}

				/* Drag N Drop */
				table {position:relative;}
				.sortableHandle {cursor:move;}
			';

				// Add optionsmenu
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath($this->extKey)."res/optionsmenu.js");

				// Add custom styles
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey)."mod1/styles.css";

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

				function setHighlight(id)	{	//
					top.fsMod.recentIds["web"]=id;
					top.fsMod.navFrameHighlightedID["web"]="pages"+id+"_"+top.fsMod.currentBank;	// For highlighting

					if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)	{
						top.content.nav_frame.refresh_nav();
					}
				}

				function editRecords(table,idList,addParams,CBflag)	{	//
					window.location.href="'.$BACK_PATH.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).
						'&edit["+table+"]["+idList+"]=edit"+addParams;
				}
				function editList(table,idList)	{	//
					var list="";

						// Checking how many is checked, how many is not
					var pointer=0;
					var pos = idList.indexOf(",");
					while (pos!=-1)	{
						if (cbValue(table+"|"+idList.substr(pointer,pos-pointer))) {
							list+=idList.substr(pointer,pos-pointer)+",";
						}
						pointer=pos+1;
						pos = idList.indexOf(",",pointer);
					}
					if (cbValue(table+"|"+idList.substr(pointer))) {
						list+=idList.substr(pointer)+",";
					}

					return list ? list : idList;
				}

				var browserPos = null,
				    browserWin = "",
				    browserPlus = "'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/plusbullet2.gif','', 1).'",
				    browserInsert = "'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/insert3.gif','', 1).'";

				function setFormValueOpenBrowser(mode,params) {	//
					var url = "'.$BACK_PATH.'browser.php?mode="+mode+"&bparams="+params;

					browserWin = window.open(url,"Typo3WinBrowser - TemplaVoila Element Selector","height=350,width="+(mode=="db"?650:600)+",status=0,menubar=0,resizable=1,scrollbars=1");
					browserWin.focus();

					$$(\'img.browse\').each(function(browserElm) {
						browserElm.src = browserInsert; });
					browserPos.firstChild.src = browserPlus;
				}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$fName,value,label,exclusiveValues: ...
	 * @return	[type]		...
	 */
				function setFormValueFromBrowseWin(fName,value,label,exclusiveValues){
					if (value) {
						if (!browserPos) {
							browserPos = document.getElementById(\''.rawurlencode($GLOBALS['BE_USER']->getSessionData('lastPasteRecord')).'\');

							if (!browserPos) {
								return;
							}
						}

						var ret = value.split(\'_\');
						var rid = ret.pop();
							ret = ret.join(\'_\');
						browserPos.href = browserPos.id.replace(\''.rawurlencode('###').'\', ret+\':\'+rid);
						jumpToUrl(browserPos.href);
					}
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
			$this->doc->JScode .= $CMparts[0];
			$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->postCode .= $CMparts[2];
			$this->doc->form = '<form action="'.htmlspecialchars($this->baseScript . $this->link_getParameters()).'" method="post" autocomplete="off">' .
				'<input type="hidden" id="browser[communication]" name="browser[communication]" />';

				/* Prototype /Scriptaculous */
			$this->doc->JScode .= '<script src="' . $this->doc->backPath . 'contrib/prototype/prototype.js" type="text/javascript"></script>';

				/* Drag'N'Drop bug:
				 *	http://prototype.lighthouseapp.com/projects/8887/milestones/9608-1-8-2-bugfix-release
				 *	#59  drag drop problem in scroll div  draggable
				 */
		//	$this->doc->JScode .= '<script src="' . $this->doc->backPath . 'contrib/scriptaculous/scriptaculous.js?load=effects,dragdrop" type="text/javascript"></script>';
			$this->doc->JScode .= '<script src="' . $this->doc->backPath . 'contrib/scriptaculous/scriptaculous.js?load=effects" type="text/javascript"></script>';
			$this->doc->JScode .= '<script src="' . $this->doc->backPath . t3lib_extMgm::extRelPath($this->extKey) . 'res/dragdrop.js" type="text/javascript"></script>';
			$this->doc->JScode .= '<script src="' . $this->doc->backPath . t3lib_extMgm::extRelPath($this->extKey) . 'res/page-dnd.js" type="text/javascript"></script>';

			$vContent = $this->doc->getVersionSelector($this->id,1);
			if ($vContent)	{
				$this->content.=$this->doc->section('',$vContent);
			}

			$this->extObjContent();

				// Info Module CSH:
			$this->content .= t3lib_BEfunc::cshItem('_MOD_web_tv', '', $GLOBALS['BACK_PATH'], '<br/>|', FALSE, 'margin-top: 30px;');
		//	$this->content .= $this->doc->spacer(10);

				// Rendering module content
			$this->content .= $this->renderModuleContent(true);

				// Setting up the buttons and markers for docheader
			$docHeaderButtons = $this->getButtons();
			$markers = array(
				'CSH'       => $docHeaderButtons['csh'],
				'FUNC_MENU' => $this->getFuncMenuNoHSC($this->id, 'SET[page]', $this->MOD_SETTINGS['page'], $this->MOD_MENU['page'],'',t3lib_div::implodeArrayForUrl('',$_GET,'',1,1)),
				'OPTS_MENU' => $this->getOptsMenuNoHSC(),

				'CONTENT'   => (!$this->id ? '' : $this->content),

				'PAGEPATH'  => $this->getPagePath($this->pageinfo),
				'PAGEINFO'  => $this->getPageInfo($this->pageinfo)
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

			$cmd = t3lib_div::_GP ('cmd');
			switch ($cmd) {

					// Create a new page
				case 'crPage' :
						// Output the page creation form
					$this->content .= $this->wizardsObj->renderWizard_createNewPage (t3lib_div::_GP ('positionPid'));
					break;

					// If no access or if ID == zero
				default:
					$this->content .= $this->doc->header($LANG->getLL('title'));
					$this->content .= $this->doc->spacer(5);
					$this->content .= $this->doc->spacer(10);
					$this->content .= $this->doc->endPage();
					$this->content  = $this->doc->insertStylesAndJS($this->content);
			}
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
		global $TCA, $LANG, $BACK_PATH, $BE_USER;

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
		if ($BE_USER->check('modules','web_list') && $this->pageinfo['uid']) {
			$href = $BACK_PATH . 'db_list.php?id=' . $this->pageinfo['uid'] . '&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
			$buttons['record_list'] = '<a href="' . htmlspecialchars($href) . '">' .
					'<img src="' . t3lib_iconWorks::skinImg($BACK_PATH, 'MOD:web_list/list.gif', 'width="16" height="16"', 1) . '" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showList', 1) . '" alt="" />' .
					'</a>';
		}

			// Back
		if (is_array($this->altRoot)) {
			$buttons['back'] = '<a href="' . $this->baseScript . 'id='.$this->id.'" class="typo3-goBack">' .
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
			$buttons['level_up'] = '<a href="' . $this->baseScript . 'id='.$this->pageinfo['pid'].'" onclick="setHighlight(' . $this->pageinfo['pid'] . ')">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/i/pages_up.gif') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.upOneLevel', 1) . '" alt="" />' .
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
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', 'width="11" height="12"') . ' hspace="2" vspace="2" align="top" title="' . $LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:editPage', 1) . '" alt="" />' .
					'</a>';

					// Unhide
				if ($this->pageinfo['hidden'])	{
					$params = '&data[pages][' . $this->pageinfo['uid'] . '][hidden]=0';
					$buttons['hide_unhide'] = '<a href="#" onclick="' . htmlspecialchars('return jumpToUrl(\'' . $GLOBALS['SOBE']->doc->issueCommand($params, -1) . '\');') . '">' .
									'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/button_unhide.gif') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:unHidePage', 1) . '" alt="" />' .
									'</a>';
					// Hide
				} else {
					$params = '&data[pages][' . $this->pageinfo['uid'] . '][hidden]=1';
					$buttons['hide_unhide'] = '<a href="#" onclick="' . htmlspecialchars('return jumpToUrl(\'' . $GLOBALS['SOBE']->doc->issueCommand($params, -1) . '\');') . '">'.
									'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/button_hide.gif') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:hidePage', 1) . '" alt="" />' .
									'</a>';
				}

				if ($this->xmlCleanCandidates) {
					$buttons['clean'] = '<input type="image" name="_CLEAN_XML_ALL" style="background: none !important; border: 0 !important;"' .
								' ' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/lightning_green.png') . ' class="c-inputButton" title="'.$LANG->getLL('outline_status_cleanAll', 1).'" alt="Clean"' .
								' />';
				}
			}

				// Cache
			if (1 /*!$this->modTSconfig['properties']['disableAdvanced']*/) {
				$buttons['cache'] = '<a href="' . $this->baseScript . $this->link_getParameters() . '&clearCache=1">' .
								'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/clear_cache.gif') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.clear_cache', 1) . '" alt="" />' .
								'</a>';
			}

				// Reload
			$buttons['reload'] = '<a href="' . $this->baseScript . $this->link_getParameters() . '">' .
							'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/refresh_n.gif') . ' title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.reload', 1) . '" alt="" />' .
							'</a>';
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
		global $LANG;

			// Is this a real page
		if ($pageRecord['uid'])	{
			$title = $pageRecord['_thePath'];
		} else {
			$title = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
		}

			// Setting the path of the page
		$pagePath = $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path', 1) . ': <span class="typo3-docheader-pagePath">' . htmlspecialchars(t3lib_div::fixed_lgd_cs($title, -50)) . '</span>';
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
		} else {	// On root-level of page tree
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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/index.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_templavoila_module1_integral');
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