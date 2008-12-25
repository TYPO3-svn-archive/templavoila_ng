<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003, 2004, 2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * templavoila module cm1
 *
 * $Id: index.php 11101 2008-08-13 12:54:41Z dmitry $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @co-author	Robert Lemke <robert@typo3.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  125: class tx_templavoila_cm1 extends t3lib_SCbase
 *  189:     function menuConfig()
 *  209:     function main()
 *  230:     function printContent()
 *
 *              SECTION: MODULE mode
 *  259:     function main_mode()
 *  352:     function renderFile($singleView)
 *  352:     function renderFile_editProcessing($singleView)
 *  793:     function renderDSO($singleView)
 *  928:     function renderTO($singleView)
 * 1096:     function renderTO_editProcessing($singleView,&$dataStruct,$row,$theFile)
 *
 *              SECTION: Mapper functions
 * 1317:     function renderHeaderSelection($displayFile,$currentHeaderMappingInfo,$showBodyTag,$htmlAfterDSTable='')
 * 1382:     function renderTemplateMapper($displayFile,$path,$dataStruct=array(),$currentMappingInfo=array(),$htmlAfterDSTable='')
 * 1570:     function drawDataStructureMap($dataStruct,$mappingMode=0,$currentMappingInfo=array(),$pathLevels=array(),$optDat=array(),$contentSplittedByMapping=array(),$level=0,$tRows=array(),$formPrefix='',$path='',$mapOK=1)
 * 1786:     function drawDataStructureMap_editItem($formPrefix,$key,$value,$level)
 * 1905:     function drawDataStructureMap_editItem_editTypeExtra($type, $formFieldName, $curValue)
 *
 *              SECTION: Helper-functions for File-based DS/TO creation
 * 1955:     function substEtypeWithRealStuff(&$elArray,$v_sub=array(),$scope = 0)
 * 2236:     function substEtypeWithRealStuff_contentInfo($content)
 *
 *              SECTION: Various helper functions
 * 2283:     function getDataStructFromDSO($datString,$file='')
 * 2299:     function linkForDisplayOfPath($title,$path)
 * 2319:     function linkThisScript($array=array())
 * 2342:     function makeIframeForVisual($file,$path,$limitTags,$showOnly,$preview=0)
 * 2358:     function explodeMappingToTagsStr($mappingToTags,$unsetAll=0)
 * 2376:     function unsetArrayPath(&$dataStruct,$ref)
 * 2393:     function cleanUpMappingInfoAccordingToDS(&$currentMappingInfo,$dataStruct)
 * 2412:     function findingStorageFolderIds()
 *
 *              SECTION: DISPLAY mode
 * 2458:     function main_display()
 * 2503:     function displayFileContentWithMarkup($content,$path,$relPathFix,$limitTags)
 * 2539:     function displayFileContentWithPreview($content,$relPathFix)
 * 2575:     function displayFrameError($error)
 * 2602:     function cshItem($table,$field,$BACK_PATH,$wrap='',$onlyIconMode=FALSE, $styleAttrib='')
 * 2615:     function lipsumLink($formElementName)
 *
 * TOTAL FUNCTIONS: 29
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once('conf.php');

require_once(PATH_t3lib . 'class.t3lib_scbase.php');
require_once(PATH_t3lib . 'class.t3lib_parsehtml.php');

require_once(PATH_t3lib . 'class.t3lib_page.php');
require_once(PATH_t3lib . 'class.t3lib_tsparser.php');
require_once(PATH_t3lib . 'class.t3lib_tcemain.php');

$LANG->includeLLFile('EXT:templavoila/cm1/locallang.xml');
$BE_USER->modAccess($ACONF, 1);

// Include class which contains the constants and definitions of TV
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_defines.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_htmlmarkup.php');

// some internal defines only used here
define("TVDS_CLEAR_ALL",	1);
define("TVDS_CLEAR_MAPPING",	2);

define("TVTO_CLEAR_ALL",	1);
define("TVTO_CLEAR_HEAD",	2);
define("TVTO_CLEAR_BODY",	3);

if (t3lib_extMgm::isLoaded('lorem_ipsum')) {
	// Dmitry: this dependency on lorem_ipsum is bad :(
	// http://bugs.typo3.org/view.php?id=3691
	require_once(t3lib_extMgm::extPath('lorem_ipsum').'class.tx_loremipsum_wiz.php');
	if (t3lib_extMgm::isLoaded('rtehtmlarea')) {
		require_once(t3lib_extMgm::extPath('rtehtmlarea').'class.tx_rtehtmlarea_base.php');
	}
}

if (t3lib_extMgm::isLoaded('t3editor')) {
	require_once(t3lib_extMgm::extPath('t3editor').'class.tx_t3editor.php');
}

/*************************************
 *
 * Short glossary;
 *
 * DS - Data Structure
 * DSO - Data Structure Object (table record)
 * TO - Template Object
 *
 ************************************/

/**
 * Class for controlling the TemplaVoila module.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @co-author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_templavoila
 */
class tx_templavoila_cm1 extends t3lib_SCbase {

		// t3editor
	var $t3e = null;

		// Static:
	var $theDisplayMode = '';	// Set to ->MOD_SETTINGS[]
	var $head_markUpTags = array(
			// Block elements:
		'title' => array(),
		'script' => array(),
		'style' => array(),
			// Single elements:

		'link' => array('single'=>1),
		'meta' => array('single'=>1),
	);
	var $extKey = 'templavoila';	// Extension key of this module
	var $baseScript = 'index.php?';
	var $mod2Script = '../mod2/index.php?';
	var $dsTypes;			// chached DS-node icons
	var $changedTO = false;		// detect changes in the TO for "revert"
	var $changedDS = false;		// detect changes in the DS for "reimport"
	var $databaseDSTO = false;	// the DS/TO originally comes from a database-entry
	var $parts = array();		// rendered parts

		// Internal, dynamic:
	var $markupFile = '';		// Used to store the name of the file to mark up with a given path.
	var $markupObj = '';
	var $elNames = array();
	var $zebraRows = 0;
	var $editDataStruct = 0;	// Setting whether we are editing a data structure or not.
	var $storageFolders = array();	// Storage folders as key(uid) / value (title) pairs.
	var $storageFolders_pidList = 0;// The storageFolders pids imploded to a comma list including "0"

		// GPvars:
	var $mode;			// Looking for "&mode", which defines if we draw a frameset (default), the module (mod) or display (display)

		// GPvars for MODULE mode
	var $displayFile = '';		// (GPvar "file", shared with DISPLAY mode!) The file to display, if file is referenced directly from filelist module. Takes precedence over displayTable/displayUid
	var $displayTable = '';		// (GPvar "table") The table from which to display element (Data Structure object [tx_templavoila_datastructure], template object [tx_templavoila_tmplobj])
	var $displayUid = '';		// (GPvar "uid") The UID to display (from ->displayTable)
	var $displayPath = '';		// (GPvar "htmlPath") The "HTML-path" to display from the current file
	var $returnUrl = '';		// (GPvar "returnUrl") Return URL if the script is supplied with that.
	var $sessionBuffer = '';	// (GPvar "sessionBuffer") The session-buffer to choose for DS/TO-editing.

		// GPvars for MODULE mode, specific to mapping a DS:
	var $_preview;
	var $htmlPath;
	var $mapElPath;
	var $doMappingOfPath;
	var $showPathOnly;
	var $mappingToTags;
	var $DS_element;
	var $DS_cmd;
	var $fieldName;

		// GPvars for MODULE mode, specific to creating a DS:
	var $_load_ds_xml_content;
	var $_load_ds_xml_to;
	var $_save_dsto_spec;

		// GPvars for DISPLAY mode:
	var $show;			// Boolean; if true no mapping-links are rendered.
	var $preview;			// Boolean; if true, the currentMappingInfo preview data is merged in
	var $limitTags;			// String, list of tags to limit display by
	var $path;			// HTML-path to explode in template.

	function init() {
		parent::init();

		if (preg_match('/mod.php$/', PATH_thisScript)) {
			$this->baseScript = 'mod.php?M=xMOD_txtemplavoilaCM1&';
			$this->mod2Script = 'mod.php?M=web_txtemplavoilaM2&';
		}

			// General GPvars for module mode:
		$this->displayFile  = t3lib_div::GPvar('file');
		$this->displayTable = t3lib_div::GPvar('table');
		$this->displayUid   = t3lib_div::GPvar('uid');
		$this->displayPath  = t3lib_div::GPvar('htmlPath');
		$this->returnUrl = t3lib_div::GPvar('returnUrl');

			// GPvars specific to the DS listing/table and mapping features:
		$this->_preview = t3lib_div::GPvar('_preview'  ) ||
				  t3lib_div::GPvar('_preview_x') ;
		$this->mapElPath = t3lib_div::GPvar('mapElPath');
		$this->doMappingOfPath = t3lib_div::GPvar('doMappingOfPath');
		$this->showPathOnly = t3lib_div::GPvar('showPathOnly');
		$this->mappingToTags = t3lib_div::GPvar('mappingToTags');
		$this->DS_element = t3lib_div::GPvar('DS_element');
		$this->DS_cmd = t3lib_div::GPvar('DS_cmd');
		$this->DS_element_DELETE = t3lib_div::GPvar('DS_element_DELETE');
		$this->DS_element_MUP = t3lib_div::GPvar('DS_element_MUP');
		$this->DS_element_MDOWN = t3lib_div::GPvar('DS_element_MDOWN');
		$this->fieldName = t3lib_div::GPvar('fieldName');

			// GPvars specific for DS creation from a file.
		$this->_load_ds_xml_content = t3lib_div::GPvar('_load_ds_xml_content');
		$this->_load_ds_xml_to = t3lib_div::GPvar('_load_ds_xml_to');
		$this->_save_dsto_spec = t3lib_div::GPvar('_save_dsto_spec');
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()    {
		$this->MOD_MENU = Array (
			'displayMode' => array	(
				'explode' => 'Mode: Exploded Visual',
#				'_' => 'Mode: Overlay',
				'source' => 'Mode: HTML Source ',
#				'borders' => 'Mode: Table Borders',
			),
			'showDSxml' => ''
		);

		parent::menuConfig();
	}

	/**
	 * Returns an abbrevation and a description for a given element-type.
	 *
	 * @return	array
	 */
	function dsTypeInfo($conf) {
			// Icon:
		if ($conf['type']=='section')
			return $this->dsTypes['sc'];

		if ($conf['type']=='array') {
			if (!$conf['section'])
				return $this->dsTypes['co'];
			return $this->dsTypes['sc'];
		}

		if ($conf['type']=='attr')
			return $this->dsTypes['at'];

		if ($conf['type']=='no_map')
			return $this->dsTypes['no'];

		return $this->dsTypes['el'];
	}

	/**
	 * Main function, distributes the load between the module and display modes.
	 * "Display" mode is when the exploded template file is shown in an IFRAME
	 *
	 * @return	void
	 */
	function main()	{
			// Setting GPvars:
		$this->mode = t3lib_div::GPvar('mode');

			// Selecting display or module mode:
		switch((string)$this->mode)	{
			case 'display':
				$this->main_display();
				break;
			case 'browser':
				$this->main_browser();
				break;
			default:
				$this->main_mode();
				break;
		}
	}

	/**
	 * Prints module content.
	 * Is only used in case of &mode = "mod" since both "display" mode and frameset is outputted + exiting before this is called.
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content .= $this->doc->middle();
		$this->content .= $this->doc->endPage();

		echo $this->content;
	}

	/**
	 * Makes a context-free xml-string from an array.
	 *
	 * @param	array		Input array to be converted to XML
	 * @return	string
	 */
	function flattenarray($array) {
		if (!is_array($array)) {
			if (is_string($array))
				return $array;
			else
				return '';
		}

		return str_replace("<>\n", '', str_replace("</>", '', t3lib_div::array2xml($array,'',-1,'',0,array('useCDATA' => 1))));
	}

	/**
	 * Makes an array from a context-free xml-string.
	 *
	 * @param	string		Input XML to be converted to an array
	 * @return	array
	 */
	function unflattenarray($string) {
		if (!is_string($string) || !trim($string)) {
			if (is_array($string))
				return $string;
			else
				return array();
		}

		return t3lib_div::xml2array('<grouped>' . $string . '</grouped>');
	}

	/**
	 * Merges two arrays recursively and "binary safe" (integer keys are overridden as well), overruling similar values in the first array ($arr0) with the values of the second array ($arr1)
	 * In case of identical keys, ie. keeping the values of the second.
	 * Usage: 0
	 *
	 * @param	array		First array
	 * @param	array		Second array, overruling the first array
	 * @param	boolean		If set, keys that are NOT found in $arr0 (first array) will not be set. Thus only existing value can/will be overruled from second array.
	 * @param	boolean		If set, values from $arr1 will overrule if they are empty or zero. Default: true
	 * @param	boolean		If set, anything will override arrays in $arr0
	 * @return	array		Resulting array where $arr1 values has overruled $arr0 values
	 */
	function array_merge_recursive_overrule($arr0,$arr1,$notAddKeys=0,$includeEmtpyValues=true,$kill=true) {
		foreach ($arr1 as $key => $val) {
			if(is_array($arr0[$key])) {
				if (is_array($arr1[$key]))	{
					$arr0[$key] = $this->array_merge_recursive_overrule($arr0[$key],$arr1[$key],$notAddKeys,$includeEmtpyValues,$kill);
				}
				else if ($kill) {
					if ($includeEmtpyValues || $val) {
						$arr0[$key] = $val;
					}
				}
			}
			else {
				if ($notAddKeys) {
					if (isset($arr0[$key])) {
						if ($includeEmtpyValues || $val) {
							$arr0[$key] = $val;
						}
					}
				}
				else {
					if ($includeEmtpyValues || $val) {
						$arr0[$key] = $val;
					}
				}
			}
		}
		reset($arr0);
		return $arr0;
	}

	/**
	 * Removes empty nodes from an array which supposely is to be tranformed to XML
	 * Usage: 0
	 *
	 * @param	array		Clearing array
	 * @return	array		Resulting array where all empty nodes have been stripped
	 */
	function array_clear_recursive($arr0) {
		if (is_array($arr0)) {
			foreach ($arr0 as $key => $val) {
				if(is_array($arr0[$key])) {
					$arr0[$key] = $this->array_clear_recursive($arr0[$key]);

					if (($arr0[$key] === null) || (count($arr0[$key]) == 0)) {
						unset($arr0[$key]);
					}
				}
				else {
					if (($arr0[$key] === null) || ($arr0[$key] === '')) {
						unset($arr0[$key]);
					}
				}
			}

			reset($arr0);
		}

		return $arr0;
	}

	/*****************************************
	 *
	 * MODULE mode
	 *
	 *****************************************/

	/**
	 * Main function of the MODULE. Write the content to $this->content
	 * There are three main modes:
	 * - Based on a file reference, creating/modifying a DS/TO
	 * - Based on a Template Object uid, remapping
	 * - Based on a Data Structure uid, selecting a Template Object to map.
	 *
	 * @return	void
	 */
	function main_mode()	{
		global $LANG, $BACK_PATH;

			// Draw the header.
		$this->doc = t3lib_div::makeInstance('noDoc');
		$this->doc->docType = 'xhtml_trans';
		$this->doc->backPath = $BACK_PATH;
		$this->doc->divClass = '';

			// Add xmlarea
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath($this->extKey)."cm1/getElementsByClassName.js");
		$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath($this->extKey)."cm1/xmlarea.js");

			// Add custom styles
		$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey)."cm1/styles.css";

			// Setting up form-wrapper:
		$this->doc->form='<form id="tv-form" action="'.$this->linkThisScript(array()).'" method="post" name="pageform">';

			// JavaScript
		$this->doc->JScode.= $this->doc->wrapScriptTags('
			script_ended = 0;
			function jumpToUrl(URL)	{ //
				document.location = URL;
			}
			function updPath(inPath) {	//
				document.location = "' . t3lib_div::linkThisScript(array('htmlPath' => '', 'doMappingOfPath' => 1)) . '&htmlPath=" + top.rawurlencode(inPath);
			}
		');

			// Setting up the context sensitive menu:
		$CMparts = $this->doc->getContextMenuCode();
		$this->doc->bodyTagAdditions = $CMparts[1];
		$this->doc->JScode .= $CMparts[0];
		$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
		$this->doc->postCode.= $CMparts[2];

		$this->content .= $this->doc->startPage($LANG->getLL('title'));
		$this->content .= $this->doc->header($LANG->getLL('mappingTitle'));
		$this->content .= $this->doc->spacer(5);

		if ($this->returnUrl)	{
			$this->content .= '<p><a href="' . htmlspecialchars($this->returnUrl) . '" class="typo3-goBack">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/goback.gif', 'width="14" height="14"') . ' alt="" />'.
				$LANG->sL('LLL:EXT:lang/locallang_misc.xml:goBack', 1) .
				'</a></p><hr />';
		}

		$this->render_mode(false);

			// Add spacer:
		$this->content .= $this->doc->spacer(10);
		$this->content  = $this->doc->insertStylesAndJS($this->content);
	}

	/**
	 * Renders the appropriate content for the indicated input
	 *
	 * @param	boolean		Tweak the output to show a single thematic block or tabs with all blocks
	 * @return	array with content-blocks
	 */
	function render_mode($singleView = false) {
		$this->doc->inDocStylesArray[] = '
			DIV.typo3-noDoc { width: 98%; margin: 0 0 0 0; }
			DIV.typo3-noDoc H2 { width: 100%; }
			TABLE#c-mapInfo { margin-top: 10px; margin-bottom: 5px; }
			TABLE#c-mapInfo TR TD { padding-right: 20px; }
		';

		$this->doc->loadJavascriptLib('tab.js');

		// Add Prototype /Scriptaculous + t3editor
		if (t3lib_extMgm::isLoaded('t3editor')) {
			$this->t3e = t3lib_div::getUserObj('EXT:t3editor/class.tx_t3editor.php:&tx_t3editor');
			$this->doc->JScode .= $this->t3e->getJavascriptCode($this->doc);
		}
		/* Add Prototype /Scriptaculous */
		else {
			$this->doc->loadJavascriptLib('contrib/prototype/prototype.js');

			/* Drag'N'Drop bug:
			 *	http://prototype.lighthouseapp.com/projects/8887/milestones/9608-1-8-2-bugfix-release
			 *	#59  drag drop problem in scroll div  draggable
			 */
		//	$this->doc->loadJavascriptLib('contrib/scriptaculous/scriptaculous.js?load=effects,dragdrop');
			$this->doc->loadJavascriptLib('contrib/scriptaculous/scriptaculous.js?load=effects');
		}

			/* Drag'N'Drop bug */
		$this->doc->JScode .= '<script src="' . $this->doc->backPath . t3lib_extMgm::extRelPath($this->extKey) . 'res/dragdrop.js" type="text/javascript"></script>';

			// TS-browser
		$this->doc->JScode .= $this->doc->wrapScriptTags('
				var browserPos = null, browserWin = "";
				function setFormValueOpenBrowser(mode,params) {	//
					var url = "' . $GLOBALS['BACK_PATH'] . 'browser.php?mode=" + mode + "&bparams=" + params;

					browserWin = window.open(url, "Typo3WinBrowser - TemplaVoila Element Selector", "height=350,width=" + (mode == "db" ? 650 : 600) + ",status=0,menubar=0,resizable=1,scrollbars=1");
					browserWin.focus();
				}
				function setFormValueFromBrowseWin(fName,value,label,exclusiveValues){
					if (value) {
						var ret = value.split(\'_\');
						var rid = ret.pop();
							ret = ret.join(\'_\');

						$(\'browser[context]\').innerHTML = label + \' <em>[pid: \' + rid + \']</em>\';
						$(\'browser[communication]\').src = \'' . $this->baseScript . 'mode=browser&pid=\' + rid + \'&current=\' +
						$(\'browser[result]\').value;
					}
				}
				function setFormValueFromBrowseFrame(value){
					if (value) {
						$(\'browser[result]\').value = value;
					}
				}
				function setSizeBrowseFrame(height){
					if (height) {
						$(\'browser[communication]\').height = height;
					}
				}

				' . (t3lib_extMgm::isLoaded('t3editor') ? '
					/* overwrite ajax-hadling, we dont need it */
				T3editor.prototype.saveFunction = function(event) {
					if (t3e_instances[0])
						t3e_instances[0].textarea.value = t3e_instances[0].mirror.editor.getCode();
				};

						// callback if ajax saving was successful
				T3editor.prototype.saveFunctionComplete = function(ajaxrequest) {
				};

				Event.observe(window, \'load\', function(){
					if (t3e_instances[0])
						Event.observe(\'tv-form\', \'submit\', t3e_instances[0].saveFunction);
				});
				' : '') . '
		');

		// Finding Storage folder:
		$this->findingStorageFolderIds();

			// Icons
		$this->dsTypes = array(
			'sc' => 'Section: ',
			'co' => 'Container: ',
			'el' => 'Attribute: ',
			'at' => 'Element: ',
			'no' => 'Not Mapped: ');

		foreach ($this->dsTypes as $id => $title) {
			$this->dsTypes[$id] = array(
					// abbrevation
				$id,
					// descriptive title
				$title,
					// image-path
				t3lib_iconWorks::skinImg($this->doc->backPath,t3lib_extMgm::extRelPath('templavoila').'cm1/item_'.$id.'.gif','width="24" height="16" border="0" style="margin-right: 5px;"'),
					// background-path
				t3lib_iconWorks::skinImg($this->doc->backPath,t3lib_extMgm::extRelPath('templavoila').'cm1/item_'.$id.'.gif','',1)
			);

				// information
			$this->dsTypes[$id][4] = @getimagesize($this->dsTypes[$id][3]);
		}

			// Render content, depending on input values:
		if ($this->displayFile)	{
			// Browsing file directly, possibly creating a template/data object records.
			$this->renderFile($singleView);
		}
		else if ($this->displayTable == 'tx_templavoila_datastructure') {
			// Data source display
			$this->renderDSO($singleView);
		}
		else if ($this->displayTable == 'tx_templavoila_tmplobj') {
			// Data source display
			$this->renderTO($singleView);
		}
	}

	/*****************************************
	 *
	 * Render minor functions
	 *
	 *****************************************/

	/**
	 * Renders informations of a given template-file
	 *
	 * @param	string		The file-path
	 * @return	the dl-fragment with the informations and icons
	 */
	function renderFile_info($theFile) {
			// Find the file:
		$theFile = t3lib_div::getFileAbsFileName($theFile,1);
		if ($theFile && @is_file($theFile))	{
			ereg("(.*)\.([^\.]*$)", $theFile, $reg);
			$alttext = $reg[2];
			$icon = t3lib_BEfunc::getFileIcon($reg[2]);
			$icon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/fileicons/'.$icon, 'width="18" height="16"').' align="top" title="'.htmlspecialchars($alttext).'" alt="" />';

			$relFilePath = substr($theFile,strlen(PATH_site));
			$onCl = 'return top.openUrlInWindow(\''.t3lib_div::getIndpEnv('TYPO3_SITE_URL').$relFilePath.'\',\'FileView\');';

			return '
				<dt>'.$GLOBALS['LANG']->getLL('templateFile').': </dt>
				<dd>' . $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($icon, $theFile) . ' <a href="#" onclick="'.htmlspecialchars($onCl).'">'.htmlspecialchars($relFilePath).'</a></dd>
			';
		}

		return '';
	}

	/**
	 * Renders informations of a given data-structure
	 *
	 * @param	string		The ds-row
	 * @return	the dl-fragment with the informations and icons
	 */
	function renderDS_info($row) {
			// Get title and icon:
		$icon = t3lib_iconworks::getIconImage('tx_templavoila_datastructure',$row,$GLOBALS['BACK_PATH'],' align="top" title="UID: '.$row['uid'].'"');
		$title = t3lib_BEfunc::getRecordTitle('tx_templavoila_datastructure',$row,1);
		return '
			<dt>Data Structure Record: </dt>
			<dd>' . $this->doc->wrapClickMenuOnIcon($icon, 'tx_templavoila_datastructure', $row['uid'], 1) . ' ' . $title . ' <em>[uid: ' . $row['uid'] . '</em>]</dd>
		';
	}

	/**
	 * Renders the preview-icon of a given data-structure
	 *
	 * @param	string		The ds-row
	 * @return	the dl-fragment with the preview-icon
	 */
	function renderDS_icon($row) {
			// Preview icon:
		$icon = ($row['previewicon'] ? '<img src="'.$this->doc->backPath.'../uploads/tx_templavoila/'.$row['previewicon'].'" alt="" />' : '['.$GLOBALS['LANG']->getLL('noicon').']');
		$title = '';
		return '
			<dt class="r">' . $GLOBALS['LANG']->getLL('templateIcon') . ': </dt>
			<dd class="r">' . $icon . '</dd>
		';
	}

	/**
	 * Renders the type of a given data-structure
	 *
	 * @param	string		The ds-row
	 * @return	the dl-fragment with the type information
	 */
	function renderDS_type($row) {
		// Get type:
		switch (intval($row['scope'])) {
			case TVDS_SCOPE_PAGE:
				$icon = '<img' . t3lib_iconworks::skinImg($this->doc->backPath, 'gfx/i/pages.gif') . ' align="top" alt="Page" />';
				$title = 'Page';
				break;
			case TVDS_SCOPE_FCE:
				$icon = '<img' . t3lib_iconworks::skinImg($this->doc->backPath, 'gfx/i/tt_content_fce.gif') . ' align="top" alt="FCE" />';
				$title = 'Flexible Content Element';
				break;
			default:
				$icon = '<img' . t3lib_iconworks::skinImg($this->doc->backPath, 'gfx/i/unknown.gif') . ' align="top" alt="Unknown" />';
				$title = 'Custom [' . $row['scope'] . ']';
				break;
		}

		return '
			<dt>Data Structure Type: </dt>
			<dd>' . $icon . ' ' . $title . '</dd>
		';
	}

	/**
	 * Renders informations of a given external data-structure--file
	 *
	 * @param	string		The file-path
	 * @return	the dl-fragment with the informations and icons
	 */
	function renderDS_file($theFile) {
			// Find the file:
		$theFile = t3lib_div::getFileAbsFileName($theFile,1);
		if ($theFile && @is_file($theFile))	{
			ereg("(.*)\.([^\.]*$)", $theFile, $reg);
			$alttext = $reg[2];
			$icon = t3lib_BEfunc::getFileIcon($reg[2]);
			$icon = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/fileicons/'.$icon, 'width="18" height="16"').' align="top" title="'.htmlspecialchars($alttext).'" alt="" />';

			$relFilePath = substr($theFile,strlen(PATH_site));
			$onCl = 'return top.openUrlInWindow(\''.t3lib_div::getIndpEnv('TYPO3_SITE_URL').$relFilePath.'\',\'FileView\');';

			return '
				<dt>'.$GLOBALS['LANG']->getLL('structureFile').': </dt>
				<dd>' . $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($icon, $theFile) . ' <a href="#" onclick="'.htmlspecialchars($onCl).'">'.htmlspecialchars($relFilePath).'</a></dd>
			';
		}

		return '';
	}

	/**
	 * Renders informations of a given template-object
	 *
	 * @param	string		The to-row
	 * @return	the dl-fragment with the informations and icons
	 */
	function renderTO_info($row) {
			// Get title and icon:
		$icon = t3lib_iconworks::getIconImage('tx_templavoila_tmplobj',$row,$GLOBALS['BACK_PATH'],' align="top" title="UID: '.$this->displayUid.'"');
		$title = t3lib_BEfunc::getRecordTitle('tx_templavoila_tmplobj',$row,1);
		return '
			<dt>'. $GLOBALS['LANG']->getLL('templateObject') . ': </dt>
			<dd>'. $this->doc->wrapClickMenuOnIcon($icon, 'tx_templavoila_tmplobj', $row['uid'], 1) . ' ' . $title . ' <em>[uid: ' . $row['uid'] . ']</em></dd>
		';
	}

	/**
	 * Renders the preview-icon of a given template-object
	 *
	 * @param	string		The to-row
	 * @return	the dl-fragment with the preview-icon
	 */
	function renderTO_icon($row) {
			// Preview icon:
		$icon = ($row['previewicon'] ? '<img src="'.$this->doc->backPath.'../uploads/tx_templavoila/'.$row['previewicon'].'" alt="" />' : '['.$GLOBALS['LANG']->getLL('noicon').']');
		$title = '';
		return '
			<dt class="r">'.$GLOBALS['LANG']->getLL('templateIcon').': </dt>
			<dd class="r">'.$icon.'</dd>
		';
	}

	/*****************************************
	 *
	 * Render medium functions
	 *
	 *****************************************/

	/**
	 * Renders an overview of available informations of the current source-of-information
	 *
	 * @return	the dl with the informations and icons
	 */
	function renderOrigin_details() {
		$tRows = array();
		$fRows = array();

		$orgDat = $GLOBALS['BE_USER']->getSessionData($this->MCONF['name'] . '_origin');

		if ($orgDat['source'] == '_load_ds_xml_to') {
			$toUID = $orgDat['subject'];
			$row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj', $toUID);
			$tM = unserialize($row['templatemapping']);
			$DS_row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_datastructure', $row['datastructure']);

			// Get title and icon:
			$tRows[] = $this->renderTO_info($row);
			// Find the file:
			$tRows[] = $this->renderFile_info($row['fileref']);
			// Get title and icon:
			$tRows[] = $this->renderDS_info($DS_row);
			// Get type:
			$tRows[] = $this->renderDS_type($DS_row);
		}
		else if ($orgDat['source'] == '_load_ds_xml_content') {
			$tRows[] = '
				<dt>Pure XML import: </dt>
				<dd>' . t3lib_div::formatSize(strlen($orgDat['subject'])) . 'bytes</dd>
			';
		}
		else {
			$tRows[] = '
				<dt>From scratch construction </dt>
			';
		}

		$fRows[] = '
			<div class="clear nosize"></div>
		';

		return
			'<dl class="DS-infos">' .
				implode('', $tRows) .
			'</dl>' .
			implode('', $fRows)
		;
	}

	/**
	 * Renders an overview of available informations of a given data-structure
	 *
	 * @param	string		The ds-row
	 * @return	the dl with the informations and icons
	 */
	function renderDSO_details($row) {
		$tRows = array();
		$fRows = array();

			// Preview icon:
		$tRows[] = $this->renderDS_icon($row);
			// Get title and icon:
		$tRows[] = $this->renderDS_info($row);
			// Get type:
		$tRows[] = $this->renderDS_type($row);

		$fRows[]='
			<div class="clear nosize"></div>
		';

		return
			'<dl class="DS-infos">' .
				implode('', $tRows) .
			'</dl>' .
			implode('', $fRows)
		;
	}

	/**
	 * Renders an overview of available informations of a given template-object
	 *
	 * @param	string		The to-row
	 * @return	the dl with the informations and icons
	 */
	function renderTO_details($row) {
	}

	/*****************************************
	 *
	 * Render major functions
	 *
	 *****************************************/

	/**
	 * Renders the display of DS/TO creation directly from a file
	 *
	 * @param	boolean		Tweak the output to show a single thematic block or tabs with all blocks
	 * @param	string		request a specific command, regardless of the state of the GPvars
	 * @return	void
	 */
	function renderFile_editProcessing($singleView, $cmd = '') {
		global $LANG, $BE_USER, $TYPO3_DB;

		// Got an override?:
		$msg = array();
		if (!$cmd)
		// Converting GPvars into a "cmd" value:
		if (t3lib_div::GPvar('_reload_from') ||
		    t3lib_div::GPvar('_reload_from_x')) {			// Reverting to old values in TO
			$cmd = 'reload_from';
		} elseif (t3lib_div::GPvar('_load_ds_xml')) {			// Loading DS from XML or TO uid
			$cmd = 'load_ds_xml';
		} elseif (t3lib_div::GPvar('_clear') == TVDS_CLEAR_MAPPING) {	// Resetting all Mapping
			$cmd = 'clear_mapping';
		} elseif (t3lib_div::GPvar('_clear') == TVDS_CLEAR_ALL ||
			  t3lib_div::GPvar('_clear_x'))	{			// Resetting all Mapping/DS
			$cmd = 'clear';
		} elseif (t3lib_div::GPvar('_save_dsto_into')) {		// Saving DS and TO to records.
			$cmd = 'save_dsto_into';
		} elseif (t3lib_div::GPvar('_save_dsto') ||
			  t3lib_div::GPvar('_save_dsto_x') ||
			  t3lib_div::GPvar('_save_dsto_preview') ||
			  t3lib_div::GPvar('_save_dsto_preview_x') ||
			  t3lib_div::GPvar('_save_dsto_return') ||
			  t3lib_div::GPvar('_save_dsto_return_x')) {		// Updating DS and TO
			$cmd = 'save_dsto';
		} elseif (t3lib_div::GPvar('_showXMLDS')) {			// Showing current DS as XML
			$cmd = 'showXMLDS';
		} elseif (t3lib_div::GPvar('_preview') ||
			  t3lib_div::GPvar('_preview_x')) {			// Previewing mappings
			$cmd = 'preview';
		} elseif (t3lib_div::GPvar('_save_data_mapping')) {		// Saving mapping to Session
			$cmd = 'save_data_mapping';
		} elseif (t3lib_div::GPvar('_updateDS') ||
			  t3lib_div::GPvar('_updateDS_x')) {
			$cmd = 'updateDS';
		} elseif (t3lib_div::GPvar('DS_element_DELETE')) {
			$cmd = 'DS_element_DELETE';
		} elseif (t3lib_div::GPvar('DS_element_MUP')) {
			$cmd = 'DS_element_MUP';
		} elseif (t3lib_div::GPvar('DS_element_MDOWN')) {
			$cmd = 'DS_element_MDOWN';
		} elseif (t3lib_div::GPvar('_saveScreen') ||
			  t3lib_div::GPvar('_saveScreen_x')) {
			$cmd = 'saveScreen';
		} elseif (t3lib_div::GPvar('_loadScreen') ||
			  t3lib_div::GPvar('_loadScreen_x')) {
			$cmd = 'loadScreen';
		}

		// this user has no permition to continue
		if (!$BE_USER->check('tables_modify', 'tx_templavoila_datastructure') && !(($cmd == '') || ($cmd == 'reload_from') || ($cmd != 'preview'))) {
			return '';
		}

		// Init settings:
		$this->editDataStruct = 1;	// Edit DS...
		$content = '';

		// Checking Storage Folder PID:
		if (!count($this->storageFolders))	{
			$msg[] = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/icon_fatalerror.gif', 'width="18" height="16"').' border="0" align="top" class="absmiddle" alt="" /><strong>'.$GLOBALS['LANG']->getLL('error').'</strong> '.$GLOBALS['LANG']->getLL('errorNoStorageFolder');
		}

		// Session data
		if ($cmd == 'clear')	{
			// Reset session data:
			$sesDat = array();
			   $GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
			$orgDat = $GLOBALS['BE_USER']->getSessionData($this->MCONF['name'] . '_origin');
		}
		else {
			// Get session data:
			$sesDat = $GLOBALS['BE_USER']->getSessionData($this->MCONF['name'] . '_mappingInfo');
			$orgDat = $GLOBALS['BE_USER']->getSessionData($this->MCONF['name'] . '_origin');

				// Reset partial session data:
			if ($cmd == 'clear_mapping') {
				$sesDat['currentMappingInfo'] = '';
				$sesDat['currentMappingInfo_head'] = '';

				$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
			}
		}

			// Loading DS from either XML or a Template Object (containing reference to DS)
		if ($cmd == 'reload_from') {
			if ($orgDat['source'] == '_load_ds_xml_to') {
				$cmd = 'load_ds_xml';
				$this->_load_ds_xml_to = $orgDat['subject'];
			}

			if ($orgDat['source'] == '_load_ds_xml_content') {
				$cmd = 'load_ds_xml';
				$this->_load_ds_xml_content = $orgDat['subject'];
			}
		}

			// Loading DS from either XML or a Template Object (containing reference to DS)
		if ($cmd == 'load_ds_xml' && ($this->_load_ds_xml_content || $this->_load_ds_xml_to)) {
			$sesDat = array();

			$toUID = $this->_load_ds_xml_to;
			if ($toUID) {
				$toREC = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj', $toUID);
				$dsREC = t3lib_BEfunc::getRecordWSOL('tx_templavoila_datastructure', $toREC['datastructure']);

				$tM = unserialize($toREC['templatemapping']);
				$sesDat['currentMappingInfo'] = $tM['MappingInfo'];
				$sesDat['currentMappingInfo_head'] = $tM['MappingInfo_head'];

					// Just set $ds, not only its ROOT! Otherwise <meta> will be lost.
				$ds = t3lib_div::xml2array($dsREC['dataprot']);
				$sesDat['dataStruct'] = $sesDat['autoDS'] = $ds;

				$orgDat = array(
					'source' => '_load_ds_xml_to',
					'subject' => $this->_load_ds_xml_to,
					'csum' => md5(serialize($sesDat['autoDS']))
				);
			}
			else {
				$ds = t3lib_div::xml2array($this->_load_ds_xml_content);
				$sesDat['dataStruct'] = $sesDat['autoDS'] = $ds;

				$orgDat = array(
					'source' => '_load_ds_xml_content',
					'subject' => $this->_load_ds_xml_content,
					'csum' => md5(serialize($sesDat['autoDS']))
				);
			}

			$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
			$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_origin', $orgDat);
		}

			// Setting Data Structure to value from session data - unless it does not exist in which case a default structure is created.
		$dataStruct = is_array($sesDat['autoDS']) ? $sesDat['autoDS'] : array(
			'meta' => array(
				'langDisable' => '1',
			),
			'ROOT' => array (
				'tx_templavoila' => array (
					'title' => 'ROOT',
					'description' => $GLOBALS['LANG']->getLL('rootDescription'),
				),
				'type' => 'array',
				'el' => array()
			)
		);

			// Setting Current Mapping information to session variable content OR blank if none exists.
		$currentMappingInfo = is_array($sesDat['currentMappingInfo']) ? $sesDat['currentMappingInfo'] : array();
			// This will clean up the Current Mapping info to match the Data Structure.
		$this->cleanUpMappingInfoAccordingToDS($currentMappingInfo,$dataStruct);

			// CMD switch:
		switch($cmd)	{

			// Saving incoming Mapping Data to session data:
			case 'save_data_mapping':
				$inputData = t3lib_div::GPvar('dataMappingForm',1);
				if (is_array($inputData)) {
					$sesDat['currentMappingInfo'] = $currentMappingInfo = $this->array_merge_recursive_overrule($currentMappingInfo,$inputData);
					$sesDat['dataStruct'] = $dataStruct;
					$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
				}
				break;

			// Saving incoming Data Structure settings to session data:
			case 'updateDS':
				$inDS = t3lib_div::GPvar('autoDS', 1);
				if (is_array($inDS))	{
					$sesDat['dataStruct'] = $sesDat['autoDS'] = $dataStruct = $this->array_merge_recursive_overrule($dataStruct,$inDS);
					$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
				}
				break;

			// If DS element is requested for deletion, remove it and update session data:
			case 'DS_element_DELETE':
				$ref = explode('][',substr($this->DS_element_DELETE,1,-1));
				$this->unsetArrayPath($dataStruct,$ref);
				$sesDat['dataStruct'] = $sesDat['autoDS'] = $dataStruct;
				$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'].'_mappingInfo',$sesDat);
				break;

			// If DS element is requested for moving up, move it and update session data:
			case 'DS_element_MUP':
				$ref = explode('][', substr($this->DS_element_MUP, 1, -1));
				$this->upArrayPath($dataStruct,$ref);
				$sesDat['dataStruct'] = $sesDat['autoDS'] = $dataStruct;
				$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
				break;

			// If DS element is requested for moving down, move it and update session data:
			case 'DS_element_MDOWN':
				$ref = explode('][', substr($this->DS_element_MDOWN, 1, -1));
				$this->downArrayPath($dataStruct,$ref);
				$sesDat['dataStruct'] = $sesDat['autoDS'] = $dataStruct;
				$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
				break;
		}

			// Creating $templatemapping array with cached mapping content:
		if (t3lib_div::inList('showXMLDS,save_dsto_into,save_dsto', $cmd)) {

				// Template mapping prepared:
			$templatemapping=array();
			$templatemapping['MappingInfo'] = $currentMappingInfo;
			if (isset($sesDat['currentMappingInfo_head'])) {
				$templatemapping['MappingInfo_head'] = $sesDat['currentMappingInfo_head'];
			}

				// Getting cached data:
			reset($dataStruct);
			$fileContent = t3lib_div::getUrl($this->displayFile);
			$htmlParse = t3lib_div::makeInstance('t3lib_parsehtml');
			$relPathFix = dirname(substr($this->displayFile, strlen(PATH_site))) . '/';
			$fileContent = $htmlParse->prefixResourcePath($relPathFix, $fileContent);
			$this->markupObj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');
			$contentSplittedByMapping = $this->markupObj->splitContentToMappingInfo($fileContent, $currentMappingInfo);
			$templatemapping['MappingData_cached'] = $contentSplittedByMapping['sub']['ROOT'];

			list($html_header) =  $this->markupObj->htmlParse->getAllParts($htmlParse->splitIntoBlock('head', $fileContent), 1, 0);
			$this->markupObj->tags = $this->head_markUpTags;	// Set up the markupObject to process only header-section tags:

			if (isset($templatemapping['MappingInfo_head'])) {
				$h_currentMappingInfo = array();
				$currentMappingInfo_head = $templatemapping['MappingInfo_head'];
				if (is_array($currentMappingInfo_head['headElementPaths']))	{
					foreach($currentMappingInfo_head['headElementPaths'] as $kk => $vv) {
						$h_currentMappingInfo['el_' . $kk]['MAP_EL'] = $vv;
					}
				}

				$contentSplittedByMapping = $this->markupObj->splitContentToMappingInfo($html_header, $h_currentMappingInfo);
				$templatemapping['MappingData_head_cached'] = $contentSplittedByMapping;

					// Get <body> tag:
				$reg='';
				eregi('<body[^>]*>',$fileContent,$reg);
				$templatemapping['BodyTag_cached'] = $currentMappingInfo_head['addBodyTag'] ? $reg[0] : '';
			}

			if ($cmd != 'showXMLDS') {
				// Set default flags to <meta> tag
				if (!isset($dataStruct['meta'])) {
					// Make sure <meta> goes at the beginning of data structure.
					// This is not critical for typo3 but simply convinient to
					// people who used to see it at the beginning.
					$dataStruct = array_merge(array('meta' => array()), $dataStruct);
				}

				if ($this->_save_dsto_spec['type'] == 1) {
					// If we save a page template, set langDisable to 1 as per localization guide
					if (!isset($dataStruct['meta']['langDisable'])) {
						$dataStruct['meta']['langDisable'] = '1';
					}
				}
				else {
					// FCE defaults to inheritance
					if (!isset($dataStruct['meta']['langDisable'])) {
						$dataStruct['meta']['langDisable'] = '0';
						$dataStruct['meta']['langChildren'] = '1';
					}
				}
			}
		}

		// CMD switch:
		switch($cmd) {

			// If it is requested to save the current DS and mapping information to a DS and TO record, then...:
			case 'save_dsto_into':
				// DS:
				$dataArr = array();
				$dataArr['tx_templavoila_datastructure']['NEW']['pid'] = intval($this->_save_dsto_spec['pid']);
				$dataArr['tx_templavoila_datastructure']['NEW']['title'] = $this->_save_dsto_spec['title'];
				$dataArr['tx_templavoila_datastructure']['NEW']['scope'] = $this->_save_dsto_spec['type'];

				// Modifying data structure with conversion of preset values for field types to actual settings:
				$storeDataStruct = $dataStruct;
				if (is_array($storeDataStruct['ROOT']['el']))
					$this->substEtypeWithRealStuff($storeDataStruct['ROOT']['el'], $contentSplittedByMapping['sub']['ROOT'],$dataArr['tx_templavoila_datastructure']['NEW']['scope']);
				$dataProtXML = t3lib_div::array2xml_cs($storeDataStruct, 'T3DataStructure', array('useCDATA' => 1));
				$dataArr['tx_templavoila_datastructure']['NEW']['dataprot'] = $dataProtXML;

				// Init TCEmain object and store:
				$tce = t3lib_div::makeInstance("t3lib_TCEmain");
				$tce->stripslashes_values = 0;
				$tce->start($dataArr, array());
				$tce->process_datamap();

				// If that succeeded, create the TO as well:
				if ($tce->substNEWwithIDs['NEW']) {
					$dataArr = array();
					$dataArr['tx_templavoila_tmplobj']['NEW']['pid'] = intval($this->_save_dsto_spec['pid']);
					$dataArr['tx_templavoila_tmplobj']['NEW']['title'] = $this->_save_dsto_spec['title'] . ' [Template]';
					$dataArr['tx_templavoila_tmplobj']['NEW']['datastructure'] = intval($tce->substNEWwithIDs['NEW']);
					$dataArr['tx_templavoila_tmplobj']['NEW']['fileref'] = substr($this->displayFile, strlen(PATH_site));
					$dataArr['tx_templavoila_tmplobj']['NEW']['templatemapping'] = serialize($templatemapping);
					$dataArr['tx_templavoila_tmplobj']['NEW']['fileref_mtime'] = @filemtime($this->displayFile);
					$dataArr['tx_templavoila_tmplobj']['NEW']['fileref_md5'] = @md5_file($this->displayFile);

					// Init TCEmain object and store:
					$tce = t3lib_div::makeInstance("t3lib_TCEmain");
					$tce->stripslashes_values = 0;
					$tce->start($dataArr, array());
					$tce->process_datamap();

					if ($tce->substNEWwithIDs['NEW']) {
						$msg[] = '<img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/icon_ok.gif', 'width="18" height="16"') . ' border="0" align="top" class="absmiddle" alt="" />' . sprintf($GLOBALS['LANG']->getLL('msgDSTOSaved'), $dataArr['tx_templavoila_tmplobj']['NEW']['datastructure'], $this->_load_ds_xml_to = $tce->substNEWwithIDs['NEW'], $this->_save_dsto_spec['pid']);
					} else {
						$msg[] = '<img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/icon_warning.gif', 'width="18" height="16"') . ' border="0" align="top" class="absmiddle" alt="" /><strong>' . $GLOBALS['LANG']->getLL('error') . ':</strong> ' . sprintf($GLOBALS['LANG']->getLL('errorTONotSaved'), $dataArr['tx_templavoila_tmplobj']['NEW']['datastructure']);
					}
				} else {
					$msg[] = '<img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/icon_warning.gif', 'width="18" height="16"').' border="0" align="top" class="absmiddle" alt="" /><strong>'.$GLOBALS['LANG']->getLL('error').':</strong> '.$GLOBALS['LANG']->getLL('errorTONotCreated');
				}

				unset($tce);

				$orgDat = array(
					'source' => '_load_ds_xml_to',
					'subject' => $this->_load_ds_xml_to,
					'csum' => md5(serialize($sesDat['autoDS']))
				);

				// Clear cached header info because save_dsto_into always resets headers
				// $sesDat['currentMappingInfo_head'] = '';

				$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
				$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_origin', $orgDat);
				break;

			// Updating DS and TO records:
			case 'save_dsto':
				/* If there would be nothing in the field, you get
				 * the warning instead of writing into the origin.
				 * Otherwise it's the regular save_dsto, which will
				 * write into origin.
				 */
				if (is_array($this->_save_dsto_spec)) {
					$toUID = $this->_save_dsto_spec['uid'];
				} else if ($orgDat['source'] == '_load_ds_xml_to') {
					$toUID = $orgDat['subject'];
				}

				// Looking up the records by their uids:
				$toREC = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj', $toUID);
				$dsREC = t3lib_BEfunc::getRecordWSOL('tx_templavoila_datastructure', $toREC['datastructure']);

				// If they are found, continue:
				if ($toREC['uid'] && $dsREC['uid']) {
					// DS:
					$dataArr = array();

					// Modifying data structure with conversion of preset values for field types to actual settings:
					$storeDataStruct=$dataStruct;
					if (is_array($storeDataStruct['ROOT']['el']))
						$this->substEtypeWithRealStuff($storeDataStruct['ROOT']['el'], $contentSplittedByMapping['sub']['ROOT'], $dsREC['scope']);
					$dataProtXML = t3lib_div::array2xml_cs($storeDataStruct,'T3DataStructure', array('useCDATA' => 1));
					$dataArr['tx_templavoila_datastructure'][$dsREC['uid']]['dataprot'] = $dataProtXML;

					// Init TCEmain object and store:
					$tce = t3lib_div::makeInstance('t3lib_TCEmain');
					$tce->stripslashes_values=0;
					$tce->start($dataArr, array());
					$tce->process_datamap();

					// TO:
					$TOuid = t3lib_BEfunc::wsMapId('tx_templavoila_tmplobj', $this->_load_ds_xml_to = $toREC['uid']);
					$dataArr = array();
					$dataArr['tx_templavoila_tmplobj'][$TOuid]['fileref'] = substr($this->displayFile, strlen(PATH_site));
					$dataArr['tx_templavoila_tmplobj'][$TOuid]['templatemapping'] = serialize($templatemapping);
					$dataArr['tx_templavoila_tmplobj'][$TOuid]['fileref_mtime'] = @filemtime($this->displayFile);
					$dataArr['tx_templavoila_tmplobj'][$TOuid]['fileref_md5'] = @md5_file($this->displayFile);

					// Init TCEmain object and store:
					$tce = t3lib_div::makeInstance('t3lib_TCEmain');
					$tce->stripslashes_values = 0;
					$tce->start($dataArr, array());
					$tce->process_datamap();

					$orgDat = array(
						'source' => '_load_ds_xml_to',
						'subject' => $this->_load_ds_xml_to,
						'csum' => md5(serialize($sesDat['autoDS']))
					);

					unset($tce);

					$msg[] = '<img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/icon_note.gif', 'width="18" height="16"') . ' border="0" align="top" class="absmiddle" alt="" />' . sprintf($GLOBALS['LANG']->getLL('msgDSTOUpdated'), $dsREC['uid'], $toREC['uid']);

					// Clear cached header info because save_dsto always resets headers
					// $sesDat['currentMappingInfo_head'] = '';

					$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
					$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_origin', $orgDat);

					if (t3lib_div::GPvar('_save_dsto_return') ||
					    t3lib_div::GPvar('_save_dsto_return_x')) {
						header('Location: ' . t3lib_div::locationHeaderUrl($this->returnUrl));
						exit;
					}

					if (t3lib_div::GPvar('_save_dsto_preview') ||
					    t3lib_div::GPvar('_save_dsto_preview_x')) {
						header('Location: ' . t3lib_div::locationHeaderUrl($_SERVER['REQUEST_URI']) . '&SET[page]=preview');
						exit;
					}
				}
				break;
		}

		// If a difference is detected...:
		$this->changedDS =
			($orgDat['source'] && ($orgDat['csum'] != md5(serialize($sesDat['autoDS']))));
		$this->databaseDSTO =
			($orgDat['source'] && ($orgDat['source'] == '_load_ds_xml_to'));

		// Messages:
		if (is_array($msg)) {
			$content .= '

				<!--
					Messages:
				-->
				' . implode('<br />', $msg) . '
			';
		}

		// Generate selector box options:
		// Storage Folders for elements:
		$sf_opt=array();
		$res = $TYPO3_DB->exec_SELECTquery(
			'*',
			'pages',
			'uid IN (' . $this->storageFolders_pidList . ')' . t3lib_BEfunc::deleteClause('pages'),
			'',
			'title'
		);

		while(false !== ($row = $TYPO3_DB->sql_fetch_assoc($res))) {
			$sf_opt[]='<option value="'.htmlspecialchars($row['uid']).'">'.htmlspecialchars($row['title'].' (UID:'.$row['uid'].')').'</option>';
		}

		$sysf = ' style="padding: 1px 1px 1px 20px; background-attachment: 0 50%; background-repeat: no-repeat; background-image: url(' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/i/sysf.gif', '', 1) . ')"';
		$pgei = ' style="padding: 1px 1px 1px 20px; background-attachment: 0 50%; background-repeat: no-repeat; background-image: url(' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/i/pages.gif', '', 1) . ')"';
		$fcei = ' style="padding: 1px 1px 1px 20px; background-attachment: 0 50%; background-repeat: no-repeat; background-image: url(' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/i/tt_content_fce.gif', '', 1) . ')"';
		$nfos = array(
			1 => array('Page Template'  , $pgei),
			2 => array('Content Element', $fcei),
			0 => array('Undefined'      , ''   ),
		);

		// Template Object records:
		$opts = array();
		$res = $TYPO3_DB->exec_SELECTquery (
			'tx_templavoila_tmplobj.*,tx_templavoila_datastructure.scope',
			'tx_templavoila_tmplobj LEFT JOIN tx_templavoila_datastructure ON tx_templavoila_datastructure.uid=tx_templavoila_tmplobj.datastructure',
			'tx_templavoila_tmplobj.pid IN ('.$this->storageFolders_pidList.') AND tx_templavoila_tmplobj.datastructure>0 '.
				t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj').
				t3lib_BEfunc::versioningPlaceholderClause('tx_templavoila_tmplobj'),
			'',
			'tx_templavoila_datastructure.scope, tx_templavoila_tmplobj.title'
		);

		while (false !== ($row = $TYPO3_DB->sql_fetch_assoc($res))) {
			t3lib_BEfunc::workspaceOL('tx_templavoila_tmplobj', $row);
			$opts[$row['scope'] <= TVDS_SCOPE_KNOWN ? $row['scope'] : TVDS_SCOPE_OTHER][$row['pid']][] = '<option value="'.htmlspecialchars($row['uid']).'">'.htmlspecialchars($this->storageFolders[$row['pid']].'/'.$row['title'].' (UID:'.$row['uid'].')').'</option>';
		}

		$opt[] = '<option value="0"></option>';
		foreach ($nfos as $num => $nfo) {
			$optg = $opts[$num];

			if (is_array($optg) && (count($optg) > 0)) {
				$opt[]='<optgroup class="c-divider" label="' . $nfo[0] . '"'.str_replace('50%', '0%', $nfo[1]).'>';
				foreach ($optg as $opid => $optf) {
					$opt[]='<optgroup label="' . $this->storageFolders[$opid] . '"'.str_replace('50%', '0%', $sysf).'>';
					foreach ($optf as $o) {
						$opt[] = str_replace('<option ', '<option ' . $nfo[1], $o);
					}
					$opt[]='</optgroup>';
				}
				$opt[]='</optgroup>';
			}
		}

			// Module Interface output begin:
		switch($cmd)	{
			// Show XML DS
			case 'showXMLDS':
				require_once(PATH_t3lib.'class.t3lib_syntaxhl.php');

					// Make instance of syntax highlight class:
				$hlObj = t3lib_div::makeInstance('t3lib_syntaxhl');
				$storeDataStruct=$dataStruct;
				if (is_array($storeDataStruct['ROOT']['el']))
					$this->substEtypeWithRealStuff($storeDataStruct['ROOT']['el'],$contentSplittedByMapping['sub']['ROOT']);
				$dataStructureXML = t3lib_div::array2xml_cs($storeDataStruct,'T3DataStructure', array('useCDATA' => 1));

				$content .= /*$this->doc->section(
					$GLOBALS['LANG']->getLL('titleXmlConfiguration') . ': ' . $this->cshItem('xMOD_tx_templavoila','mapping_file_showXMLDS',$this->doc->backPath,''),*/
					'<pre>'.
					$hlObj->highLight_DS($dataStructureXML).
					'</pre>'/*,
					FALSE,
					TRUE,
					0,
					TRUE)*/;
				break;

			case 'loadScreen':
				$content .= $this->doc->section(
					$GLOBALS['LANG']->getLL('titleLoadDSXml') . ': ' . $this->cshItem('xMOD_tx_templavoila','mapping_file_loadDSXML',$this->doc->backPath,''),
					'<p>'.$GLOBALS['LANG']->getLL('selectTOrecrdToLoadDSFrom').':</p>
					<select name="_load_ds_xml_to">' . implode('', $opt) . '</select>
					<br />
					<p>'.$GLOBALS['LANG']->getLL('pasteDSXml').':</p>
					<textarea class="fixed-font enable-tab" rows="15" name="_load_ds_xml_content" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'></textarea>
					<br />
					<input type="submit" name="_load_ds_xml" value="'.$GLOBALS['LANG']->getLL('loadDSXml').'" />
					<input type="submit" name="_" value="Cancel" />',
					FALSE,
					TRUE,
					0,
					TRUE);
				break;

			case 'saveScreen':
				$content .= $this->doc->section(
					'CREATE Data Structure / Template Object: ' . $this->cshItem('xMOD_tx_templavoila','mapping_file_createDSTO',$this->doc->backPath,''),
					'<table border="0" cellpadding="2" cellspacing="2">
						<tr>
							<td class="bgColor5"><strong>Title of DS/TO:</strong></td>
							<td class="bgColor4"><input type="text" name="_save_dsto_spec[title]" /></td>
						</tr>
						<tr>
							<td class="bgColor5"><strong>Template Type:</strong></td>
							<td class="bgColor4">
								<select name="_save_dsto_spec[type]">
									<option value="1">Page Template</option>
									<option value="2">Content Element</option>
									<option value="0">Undefined</option>
								</select>
							</td>
						</tr>
						<tr>
							<td class="bgColor5"><strong>Store in PID:</strong></td>
							<td class="bgColor4">
								<select name="_save_dsto_spec[pid]">
									'.implode('
									',$sf_opt).'
								</select>
							</td>
						</tr>
					</table>

					<input type="submit" name="_save_dsto_into" value="CREATE TO and DS" />
					<input type="submit" name="_" value="Cancel" />',
					FALSE,
					TRUE,
					0,
					TRUE).
					$this->doc->section(
					'UPDATE existing Data Structure / Template Object: ' . $this->cshItem('xMOD_tx_templavoila', 'mapping_file_updateDSTO', $this->doc->backPath, ''),
					'<table border="0" cellpadding="2" cellspacing="2">
						<tr>
							<td class="bgColor5"><strong>Select TO:</strong></td>
							<td class="bgColor4">
								<select name="_save_dsto_spec[uid]">
									'.implode('
									',$opt).'
								</select>
							</td>
						</tr>
					</table>

					<input type="submit" name="_save_dsto" value="UPDATE TO (and DS)" onclick="return confirm(\'' . $LANG->getLL('mess.onOverwriteAlert') . '\');" />
					<input type="submit" name="_" value="Cancel" />',
					FALSE,
					TRUE,
					0,
					TRUE);
				break;

			default:
				if ($BE_USER->check('tables_modify', 'tx_templavoila_datastructure')) {
						// Creating menu:
					$menuItems = array();

					if (!$singleView) {
					//	$menuItems[] = '<input type="submit" name="_showXMLDS" value="Show XML" title="Preview the currently build Data Structure as XML" />';
						$menuItems[] = '<input type="submit" name="_clear" value="Clear all" title="Clear all Data Structure and Mapping information" /> ';
					//	$menuItems[] = '<input type="submit" name="_preview" value="Preview" title="Preview the mapping to the template" />';
						$menuItems[] = '<input type="submit" name="_saveScreen" value="Save as" title="Go to save menu" />';
						if ($this->changedDS) {
						$menuItems[] = '<input type="submit" name="_reload_from" value="Revert" title="Reverting structure data to last imported data" />';
						}
						$menuItems[] = '<input type="submit" name="_loadScreen" value="Load" title="Go to load menu" />';
						$menuItems[] = '<input type="submit" name="_DO_NOTHING" value="Refresh" title="Redraw screen" />';
					}
					else {
						$menuItems[] = '<input type="submit" name="_DO_NOTHING" value="Apply changes" title="Will update session data with current settings." />';
					}

					$menuContent = '
						<!--
							Menu for creation Data Structures / Template Objects
						-->
						<table border="0" cellpadding="2" cellspacing="2" id="c-toMenu">
							<tr class="bgColor5">
								<td>'.implode('</td>
								<td>',$menuItems).'</td>
							</tr>
						</table>
					';
				}

				// the preview-screen has it's own section and csh
				if ($cmd == 'preview') {
					$content.=
						$this->renderTemplateMapper($this->displayFile,$this->displayPath,$dataStruct,$currentMappingInfo,$menuContent);
				}
				else {
					$content.='
						<!--
							Data Structure creation table:
						-->' .
						$this->doc->section(
						'Building Data Structure: ' . $this->cshItem('xMOD_tx_templavoila','mapping_file',$this->doc->backPath,''),
						$this->renderTemplateMapper($this->displayFile,$this->displayPath,$dataStruct,$currentMappingInfo,$menuContent),
						FALSE,
						TRUE,
						0,
						TRUE);
				}
				break;
		}

	//	$this->content.=$this->doc->section('',$content,0,1);
		return $content;
	}

	/**
	 * Renders the display of DS/TO creation directly from a file
	 *
	 * @param	boolean		Tweak the output to show a single thematic block or tabs with all blocks
	 * @return	array with content-blocks
	 */
	function renderFile($singleView) {
		global $TYPO3_DB;

		// Working on Header and Body of HTML source:
		if (@is_file($this->displayFile) && t3lib_div::getFileAbsFileName($this->displayFile)) {

			// -----------------------------------------------------
			// -- Aquiring structural view --
			$this->doc->sectionBegin();
			$content = $this->renderFile_editProcessing($singleView);

			// -----------------------------------------------------
			// -- Write header of page --
			$this->doc->sectionBegin();
			$originContent='
				<!--
					Origin Header:
				-->
				' .
				$this->doc->section(
					$GLOBALS['LANG']->getLL('structureInfoOrigin').': '.$this->cshItem('xMOD_tx_templavoila','mapping_file',$this->doc->backPath,''),
					$this->renderOrigin_details(),
					FALSE,
					TRUE,
					0,
					TRUE);

			$this->parts['details'] = array(
				'label' => $GLOBALS['LANG']->getLL('tabDSDetails'),
				'content' => $originContent
			);

			// -----------------------------------------------------
			// -- Processing the XML editing --
			$this->doc->sectionBegin();
			$xmlContent='
				<!--
					HTML header parts selection:
				-->
				' .
				$this->doc->section(
					'Data Structure XML: : '.$this->cshItem('xMOD_tx_templavoila','mapping_ds_showXML',$this->doc->backPath,''),
					$this->renderFile_editProcessing($singleView, 'showXMLDS'),
					FALSE,
					TRUE,
					0,
					TRUE);

			$this->parts['xml'] = array(
				'label' => $GLOBALS['LANG']->getLL('tabXML'),
				'content' => $xmlContent
			);

			// -----------------------------------------------------
			// -- Processing the preview --
			$this->_preview = TRUE;

			$this->doc->sectionBegin();
			$previewContent='
				<!--
					HTML header parts selection:
				-->
				' .
				$this->doc->section(
					$GLOBALS['LANG']->getLL('mappingPreview').': ',
					$this->renderFile_editProcessing($singleView, 'preview'),
					FALSE,
					TRUE,
					0,
					TRUE);

			$this->parts['preview'] = array(
				'label' => $GLOBALS['LANG']->getLL('tabPreview'),
				'content' => $previewContent
			);
		} else $content = $this->doc->section($GLOBALS['LANG']->getLL('templateFile').' '.$GLOBALS['LANG']->getLL('error'),$GLOBALS['LANG']->getLL('errorFileNotFound'),0,1,3);

		$this->parts['structure'] = array(
			'label' => $GLOBALS['LANG']->getLL('tabDSStructure'),
			'content' => $content
		);

		// -----------------------------------------------------
		if ($singleView) {
			// show only selected parts
			$cnf = $this->parts[$this->MOD_SETTINGS['page']];
		//	$this->content .= $this->doc->section(
		//		$cnf['label'] ? $cnf['label'] : $this->MOD_MENU['page'][$this->MOD_SETTINGS['page']],
		//		$cnf['content'] ? $cnf['content'] : 'None found',
		//		FALSE,
		//		TRUE);
			$this->content .=
				$cnf['content'] ? $cnf['content'] : 'None found';
		}
		else {
			// put all existing into tabs (no index!)
			$tabs = array();
			foreach ($this->parts as &$cnf)
				$tabs[] = $cnf;
			$tabs = array_reverse($tabs);

				// Create location handlers:
			$relFilePath = substr($this->displayFile,strlen(PATH_site));
			$onCl = 'return top.openUrlInWindow(\''.t3lib_div::getIndpEnv('TYPO3_SITE_URL').$relFilePath.'\',\'FileView\');';
			$location.='
				<!--
					Create Data Structure Header:
				-->
				<table border="0" cellpadding="2" cellspacing="1" id="c-toHeader">
				<tr>
					<td class="bgColor5"><strong>'.$GLOBALS['LANG']->getLL('templateFile').':</strong></td>
					<td class="bgColor4"><a href="#" onclick="'.htmlspecialchars($onCl).'">'.htmlspecialchars($relFilePath).'</a></td>
				</tr>
				</table>
				<hr />';

			// Add output:
			$this->content .=
				$location .
				$this->doc->getDynTabMenu($tabs,'TEMPLAVOILA:templateModule:'.$this->id, 0,0,300);
		}
	}

	/**
	 * Renders the display of Data Structure Objects.
	 *
	 * @param	boolean		Tweak the output to show a single thematic block or tabs with all blocks
	 * @return	array with content-blocks
	 */
	function renderDSO($singleView) {
		global $LANG, $BE_USER, $TYPO3_DB;

		// Working on Header and Body of HTML source:
		if (intval($this->displayUid) > 0) {
			$row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_datastructure', $this->displayUid);

			if (is_array($row)) {

				// Write header of page:
				$this->doc->sectionBegin();
				$content='
					<!--
						Template Object Header:
					-->
					' .
					$this->doc->section(
						$GLOBALS['LANG']->getLL('structureInfoParts').': '.$this->cshItem('xMOD_tx_templavoila','mapping_ds',$this->doc->backPath,''),
						$this->renderDSO_details($row),
						FALSE,
						TRUE,
						0,
						TRUE);

				$this->parts['details'] = array(
					'label' => $GLOBALS['LANG']->getLL('tabDSDetails'),
					'content' => $content
				);

				// -----------------------------------------------------
				// Get Data Structure:
				$origDataStruct = $dataStruct = $this->getDataStructFromDSO($row['dataprot']);

				if (is_array($dataStruct)) {
					// Showing Data Structure:
					$tRows = $this->drawDataStructureMap($dataStruct);

					$this->doc->sectionBegin();
					$content='
					<!--
						Data Structure content:
					-->
					<div id="c-ds">'.
						$this->doc->section(
							'Data Structure in record: ' . $this->cshItem('xMOD_tx_templavoila','mapping_ds',$this->doc->backPath,''),
							'<table border="0" cellspacing="2" cellpadding="2">
									<tr class="bgColor5">
										<td nowrap="nowrap"><strong>Data Element:</strong>'.
											$this->cshItem('xMOD_tx_templavoila','mapping_head_dataElement',$this->doc->backPath,'',TRUE).
											'</td>
										<td nowrap="nowrap"><strong>Mapping instructions:</strong>'.
											$this->cshItem('xMOD_tx_templavoila','mapping_head_mapping_instructions',$this->doc->backPath,'',TRUE).
											'</td>
										<td nowrap="nowrap"><strong>Rules:</strong>'.
											$this->cshItem('xMOD_tx_templavoila','mapping_head_Rules',$this->doc->backPath,'',TRUE).
											'</td>
									</tr>'
							.implode('',$tRows).
							'</table>',
							FALSE,
							TRUE,
							0,
							TRUE).'
					</div>';
				} else {
					$content='<h4>'.$GLOBALS['LANG']->getLL('error').': '.$GLOBALS['LANG']->getLL('noDSDefined').'</h4>';
				}

				// -----------------------------------------------------
				// Get Template Objects pointing to this Data Structure
				$res = $TYPO3_DB->exec_SELECTquery (
					'*',
					'tx_templavoila_tmplobj',
					'pid IN ('.$this->storageFolders_pidList.') AND datastructure='.intval($row['uid']).
						t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj').
						t3lib_BEfunc::versioningPlaceholderClause('tx_templavoila_tmplobj')
				);
				$tRows=array();
				$tRows[]='
							<tr class="bgColor5">
								<td><strong>Uid:</strong></td>
								<td><strong>Title:</strong></td>
								<td><strong>File reference:</strong></td>
								<td><strong>Mapping Data Lgd:</strong></td>
								'.($BE_USER->check('tables_modify', 'tx_templavoila_datastructure') ? '
								<td><strong>Modify DS/TO:'.$this->cshItem('xMOD_tx_templavoila','mapping_to_modifyDSTO',$this->doc->backPath,'').'</strong></td>' : '') . '
							</tr>';

				$TOicon = t3lib_iconworks::getIconImage('tx_templavoila_tmplobj',array(),$GLOBALS['BACK_PATH'],' align="top"');

				// -----------------------------------------------------
				// Listing Template Objects with links:
				while (false !== ($TO_Row = $TYPO3_DB->sql_fetch_assoc($res)))	{
					t3lib_BEfunc::workspaceOL('tx_templavoila_tmplobj',$TO_Row);

					$fileref = t3lib_div::getFileAbsFileName($TO_Row['fileref']);

					if ($fileref) {
						// Link to updating DS/TO:
						$onCl = $this->baseScript . 'id=' . $this->id . '&file=' . rawurlencode($fileref) . '&_load_ds_xml=1&_load_ds_xml_to=' . $TO_Row['uid'];
						$onClMsg = 'if (confirm(\'' . $LANG->getLL('mess.onModifyAlert') . '\')) { document.location=\'' . $onCl . '\'; } return false;';
					}

					$tRows[] = '
							<tr class="bgColor4">
								<td>['.$TO_Row['uid'].']</td>
								<td nowrap="nowrap">'.$this->doc->wrapClickMenuOnIcon($TOicon,'tx_templavoila_tmplobj',$TO_Row['uid'],1).
									' <a href="'.htmlspecialchars($this->baseScript . 'id='.$this->id.'&table=tx_templavoila_tmplobj&uid='.$TO_Row['uid'].'&_reload_from=1').'">'.
									t3lib_BEfunc::getRecordTitle('tx_templavoila_tmplobj',$TO_Row,1).'</a>'.
									'</td>
								<td nowrap="nowrap">'.htmlspecialchars($TO_Row['fileref']).' <strong>'.(!$fileref?'(NOT FOUND!)':'(OK)').'</strong></td>
								<td>'.strlen($TO_Row['templatemapping']).'</td>
								'.($BE_USER->check('tables_modify', 'tx_templavoila_datastructure') ? '
								<td><input type="submit" name="_" value="Edit" onclick="'.htmlspecialchars($onClMsg).'"/></td>' : '') . '
							</tr>';
				}

				$this->doc->sectionBegin();
				$content .= '
					<!--
						Template Objects attached to Data Structure Record:
					-->
					<div id="c-to">'.
						$this->doc->section(
							'Template Objects using this Data Structure: '.$this->cshItem('xMOD_tx_templavoila','mapping_ds_to',$this->doc->backPath,''),
							'<table border="0" cellspacing="2" cellpadding="2">'
							.implode('',$tRows).
							'</table>',
							FALSE,
							TRUE,
							0,
							TRUE).'
					</div>';

				// -----------------------------------------------------
				// Display XML of data structure:
				if (is_array($dataStruct)) {
					require_once(PATH_t3lib.'class.t3lib_syntaxhl.php');

						// Make instance of syntax highlight class:
					$hlObj = t3lib_div::makeInstance('t3lib_syntaxhl');

					$dataStructureXML = t3lib_div::array2xml_cs($origDataStruct,'T3DataStructure', array('useCDATA' => 1));

					$xmlContent.='
					<!--
						Data Structure XML:
					-->
					<div id="c-dsxml">'.
						$this->doc->section(
							'Data Structure XML: '.$this->cshItem('xMOD_tx_templavoila','mapping_ds_showXML',$this->doc->backPath,''),
						//	<p>'.t3lib_BEfunc::getFuncCheck('','SET[showDSxml]',$this->MOD_SETTINGS['showDSxml'],'',t3lib_div::implodeArrayForUrl('',$_GET,'',1,1)).' Show XML</p>
						//	($this->MOD_SETTINGS['showDSxml'] ? $hlObj->highLight_DS($dataStructureXML) : '').'
							'<pre>'
							.$hlObj->highLight_DS($dataStructureXML).
							'</pre>',
							FALSE,
							TRUE,
							0,
							TRUE).'
					</div>';

					$this->parts['xml'] = array(
						'label' => $GLOBALS['LANG']->getLL('tabXML'),
						'content' => $xmlContent
					);
				}
			} else $content = $this->doc->section($GLOBALS['LANG']->getLL('structureInfoParts'),'ERROR: No Data Structure Record with the UID '.$this->displayUid,0,1,3);
		} else $content = $this->doc->section($GLOBALS['LANG']->getLL('structureInfoParts'),'ERROR: No UID was found pointing to a Data Structure Object record.',0,1,3);

		$this->parts['overview'] = array(
			'label' => $GLOBALS['LANG']->getLL('tabDSDetails'),
			'content' => $content
		);

		// -----------------------------------------------------
		if ($singleView) {
			// show only selected parts
			$cnf = $this->parts[$this->MOD_SETTINGS['page']];
		//	$this->content .= $this->doc->section(
		//		$cnf['label'] ? $cnf['label'] : $this->MOD_MENU['page'][$this->MOD_SETTINGS['page']],
		//		$cnf['content'] ? $cnf['content'] : 'None found',
		//		FALSE,
		//		TRUE);
			$this->content .=
				$cnf['content'] ? $cnf['content'] : 'None found';
		}
		else {
			// put all existing into tabs (no index!)
			$tabs = array();
			foreach ($this->parts as &$cnf)
				$tabs[] = $cnf;
			$tabs = array_reverse($tabs);

				// Add output:
			$this->content .=
				$this->doc->getDynTabMenu($tabs,'TEMPLAVOILA:templateModule:'.$this->id, 0,0,300);
		}
	}

	/**
	 * Renders the display of Template Objects.
	 *
	 * @param	boolean		Tweak the output to show a single thematic block or tabs with all blocks
	 * @return	array with content-blocks
	 */
	function renderTO($singleView)	{
		global $LANG, $BE_USER;

		// Working on Header and Body of HTML source:
		if (intval($this->displayUid) > 0) {
			$row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj', $this->displayUid);

			if (is_array($row)) {
				$tRows=array();
				$fRows=array();

				// Preview icon:
				$tRows[] = $this->renderTO_icon($row);
				// Get title and icon:
				$tRows[] = $this->renderTO_info($row);

				// Find the file:
				$theFile = t3lib_div::getFileAbsFileName($row['fileref'], 1);
				if ($theFile && @is_file($theFile)) {
					// Get name and icon:
					$tRows[] = $this->renderFile_info($row['fileref']);

					// Finding Data Structure Record:
					$DSOfile='';
					$dsValue = $row['datastructure'];
					if ($row['parent']) {
						$parentRec = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj', $row['parent'], 'datastructure');
						$dsValue=$parentRec['datastructure'];
					}

					if (t3lib_div::testInt($dsValue)) {
						$DS_row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_datastructure', $dsValue);
					} else {
						$DSOfile = t3lib_div::getFileAbsFileName($dsValue);
					}

					if (is_array($DS_row) || @is_file($DSOfile))	{
							// Get main DS array:
						if (is_array($DS_row))	{
							// Get title and icon:
							$tRows[] = $this->renderDS_info($DS_row);
							// Get type:
							$tRows[] = $this->renderDS_type($DS_row);

							// Link to updating DS/TO:
							$onCl = $this->baseScript .
								'?id=' . $this->id .
								'&file=' . rawurlencode($theFile) .
								'&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')) .
								'&_load_ds_xml=1' .
								'&_load_ds_xml_to=' . $row['uid'];
							$onClMsg = 'if (confirm(\'' . $LANG->getLL('mess.onModifyAlert') . '\')) { document.location=\'' . $onCl . '\'; } return false;';

							$this->parts['modify'] =
								'<input type="submit" name="_" value="Modify DS / TO" onclick="'.htmlspecialchars($onClMsg).'"/>';

							if ($BE_USER->check('tables_modify', 'tx_templavoila_datastructure') && !$singleView)
								$fRows[]='
									<div class="clear">
										' . $this->parts['modify'] . $this->cshItem('xMOD_tx_templavoila', 'mapping_to_modifyDSTO', $this->doc->backPath, '') . '
									</div>
								';
							else
								$fRows[]='
									<div class="clear nosize"></div>
								';

							// Read Data Structure:
							$dataStruct = $this->getDataStructFromDSO($DS_row['dataprot']);
						} else {
							// Show filepath of external XML file:
							$tRows[] = $this->renderDS_file($DSOfile);

							// Read Data Structure:
							$dataStruct = $this->getDataStructFromDSO('', $DSOfile);
						}

						// Write header of page:
						$this->doc->sectionBegin();
						$content='
							<!--
								Template Object Header:
							-->
							' .
							$this->doc->section(
								$GLOBALS['LANG']->getLL('mappingInfoParts').': '.$this->cshItem('xMOD_tx_templavoila','mapping_to',$this->doc->backPath,''),
								'<dl class="TO-infos">' . implode('', $tRows) . '</dl>' . implode('', $fRows),
								FALSE,
								TRUE,
								0,
								TRUE);

						// If there is a valid data structure, draw table:
						if (is_array($dataStruct)) {

							// -----------------------------------------------------
							// Determine if DS is a template record and if it is a page template:
							$showBodyTag = !is_array($DS_row) || intval($DS_row['scope']) == TVDS_SCOPE_PAGE ? TRUE : FALSE;

							// -----------------------------------------------------
							// -- Processing the head editing --
							list($editContent,$currentHeaderMappingInfo) = $this->renderTO_editProcessing($singleView,$dataStruct,$row,$theFile, 1);

							$this->doc->sectionBegin();
							$headerContent='
								<!--
									HTML header parts selection:
								-->
								' .
								$this->doc->section(
									$GLOBALS['LANG']->getLL('mappingHeadParts').': '.$this->cshItem('xMOD_tx_templavoila','mapping_to_headerParts',$this->doc->backPath,''),
									$this->renderHeaderSelection($theFile,$currentHeaderMappingInfo,$showBodyTag,$editContent),
									FALSE,
									TRUE,
									0,
									TRUE);

							$this->parts['header'] = array(
								'label' => $GLOBALS['LANG']->getLL('tabHeadParts'),
								'content' => $headerContent
							);

							// -----------------------------------------------------
							// -- Processing the body editing --
							list($editContent,$currentMappingInfo) = $this->renderTO_editProcessing($singleView,$dataStruct,$row,$theFile, 0);

							$this->doc->sectionBegin();
							$bodyContent='
								<!--
									Data Structure mapping table:
								-->
								' .
								$this->doc->section(
									$GLOBALS['LANG']->getLL('mappingBodyParts').': '.$this->cshItem('xMOD_tx_templavoila','mapping_to_bodyParts',$this->doc->backPath,''),
									$this->renderTemplateMapper($theFile,$this->displayPath,$dataStruct,$currentMappingInfo,$editContent),
									FALSE,
									TRUE,
									0,
									TRUE);

							$this->parts['mapping'] = array(
								'label' => $GLOBALS['LANG']->getLL('tabBodyParts'),
								'content' => $bodyContent
							);

							// -----------------------------------------------------
							// -- Processing the preview --
							$this->_preview = TRUE;

							$this->doc->sectionBegin();
							$previewContent='
								<!--
									Data Structure mapping table preview:
								-->
								' .
								$this->doc->section(
									$GLOBALS['LANG']->getLL('mappingPreview').': ',
									$this->renderTemplateMapper($theFile,$this->displayPath,$dataStruct,$currentMappingInfo,$editContent),
									FALSE,
									TRUE,
									0,
									TRUE);

							$this->parts['preview'] = array(
								'label' => $GLOBALS['LANG']->getLL('tabPreview'),
								'content' => $previewContent
							);

						} else $content.= $GLOBALS['LANG']->getLL('error').': No Data Structure Record could be found with UID "'.$dsValue.'"';
					} else $content.= $GLOBALS['LANG']->getLL('error').': No Data Structure Record could be found with UID "'.$dsValue.'"';
				} else $content.= $GLOBALS['LANG']->getLL('error').': The file "'.$row['fileref'].'" could not be found!';
			} else $content.= $GLOBALS['LANG']->getLL('error').': No Template Object Record with the UID '.$this->displayUid;
		} else $content=$this->doc->section($GLOBALS['LANG']->getLL('templateObject').' '.$GLOBALS['LANG']->getLL('error'), $GLOBALS['LANG']->getLL('errorNoUidFound'),0,1,3);

		$this->parts['details'] = array(
			'label' => $GLOBALS['LANG']->getLL('tabTODetails'),
			'content' => $content
		);

		// -----------------------------------------------------
		if ($singleView) {

			foreach ($this->parts as $label => $cnf) {
				if (!$cnf['content'])
					$this->MOD_MENU[$label] = '<span style="text-decoration: line-through;">' .
					$this->MOD_MENU[$label] . '</span>';
			}

			// show only selected parts
			$cnf = $this->parts[$this->MOD_SETTINGS['page']];
		//	$this->content .= $this->doc->section(
		//		$cnf['label'] ? $cnf['label'] : $this->MOD_MENU['page'][$this->MOD_SETTINGS['page']],
		//		$cnf['content'] ? $cnf['content'] : 'None found',
		//		FALSE,
		//		TRUE);
			$this->content .=
				$cnf['content'] ? $cnf['content'] : 'None found';
		}
		else {
			// put all existing into tabs (no index!)
			$tabs = array();
			foreach ($this->parts as &$cnf)
				$tabs[] = $cnf;
			$tabs = array_reverse($tabs);

				// Add output:
			$this->content .=
				$this->doc->getDynTabMenu($tabs,'TEMPLAVOILA:templateModule:'.$this->id, 0,0,300);
		}
	}

	/**
	 * Process editing of a TO for renderTO() function
	 *
	 * @param	boolean		Tweak the output to show a single thematic block or tabs with all blocks
	 * @param	array		Data Structure. Passed by reference; The sheets found inside will be resolved if found!
	 * @param	array		TO record row
	 * @param	string		Template file path (absolute)
	 * @param   	integer		Process the headerPart instead of the bodyPart
	 * @return	array		Array with two keys (0/1) with a) content and b) currentMappingInfo which is retrieved inside (currentMappingInfo will be different based on whether "head" or "body" content is "mapped")
	 * @see renderTO()
	 */
	function renderTO_editProcessing($singleView, &$dataStruct, $row, $theFile, $headerPart = 0) {
		global $BE_USER;

		// Converting GPvars into a "cmd" value:
		$msg = array();
		$cmd = '';
		if (t3lib_div::GPvar('_reload_from') ||
		    t3lib_div::GPvar('_reload_from_x')) {			// Reverting to old values in TO
			$cmd = 'reload_from';
		} elseif (t3lib_div::GPvar('_clear') == TVTO_CLEAR_HEAD) {	// Resetting Head-Mapping
			$cmd = 'clear_head';
		} elseif (t3lib_div::GPvar('_clear') == TVTO_CLEAR_BODY) {	// Resetting Body-Mapping
			$cmd = 'clear_body';
		} elseif (t3lib_div::GPvar('_clear') == TVTO_CLEAR_ALL ||
			  t3lib_div::GPvar('_clear_x'))	{			// Resetting all Mappings
			$cmd = 'clear';
		} elseif (t3lib_div::GPvar('_save_data_mapping')) {		// Saving to Session
			$cmd = 'save_data_mapping';
		} elseif (t3lib_div::GPvar('_save_to') ||
			  t3lib_div::GPvar('_save_to_x') ||
			  t3lib_div::GPvar('_save_to_preview') ||
			  t3lib_div::GPvar('_save_to_preview_x') ||
			  t3lib_div::GPvar('_save_to_return') ||
			  t3lib_div::GPvar('_save_to_return_x')) {		// Saving to Template Object
			$cmd = 'save_to';
		}

		// this user has no permition to continue
		if (!$BE_USER->check('tables_modify', 'tx_templavoila_tmplobj') && !(($cmd == '') || ($cmd == 'reload_from'))) {
			return '';
		}

		// Getting data from tmplobj
		$templatemapping = unserialize($row['templatemapping']);
		if (!is_array($templatemapping))	$templatemapping=array();

		// If that array contains sheets, then traverse them:
		if (is_array($dataStruct['sheets']))	{
			$dSheets = t3lib_div::resolveAllSheetsInDS($dataStruct);
			$dataStruct=array(
				'ROOT' => array (
					'tx_templavoila' => array (
						'title' => 'ROOT of MultiTemplate',
						'description' => 'Select the ROOT container for this template project. Probably just select a body-tag or some other HTML element which encapsulates ALL sub templates!',
					),
					'type' => 'array',
					'el' => array()
				)
			);
			foreach($dSheets['sheets'] as $nKey => $lDS)	{
				if (is_array($lDS['ROOT']))	{
					$dataStruct['ROOT']['el'][$nKey]=$lDS['ROOT'];
				}
			}
		}

		// Get session data:
		$sesDat = $GLOBALS['BE_USER']->getSessionData($this->MCONF['name'].'_mappingInfo');

		// Set current mapping info arrays:
		$currentMappingInfo_head = is_array($sesDat['currentMappingInfo_head']) ? $sesDat['currentMappingInfo_head'] : array();
		$currentMappingInfo      = is_array($sesDat['currentMappingInfo'     ]) ? $sesDat['currentMappingInfo'     ] : array();

		$this->cleanUpMappingInfoAccordingToDS($currentMappingInfo,$dataStruct);

		// Perform processing for head -----------------------------------------------------
		// GPvars, incoming data
		$headerData = t3lib_div::GPvar('headMappingForm', 1);
		$checkboxElement = t3lib_div::GPvar('checkboxElement',1);
		$addBodyTag = t3lib_div::GPvar('addBodyTag');

		// Update session data:
		if ($cmd == 'reload_from' || $cmd == 'clear' || $cmd == 'clear_head') {
			$currentMappingInfo_head = is_array($templatemapping['MappingInfo_head'])&&$cmd == 'reload_from' ? $templatemapping['MappingInfo_head'] : array();

			$sesDat['currentMappingInfo_head'] = $currentMappingInfo_head;
			$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'].'_mappingInfo',$sesDat);
		} else {
			if ($cmd == 'save_data_mapping' || $cmd == 'save_to') {
				/* overwrite only if receiving new assignment */
				if ($headerData) {
					$currentMappingInfo_head = array(
						'headElementPaths' => $checkboxElement,
						'addBodyTag' => $addBodyTag ? 1 : 0
					);
				}

				$sesDat['currentMappingInfo_head'] = $currentMappingInfo_head;
				$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'] . '_mappingInfo', $sesDat);
			}
		}

		// Perform processing for body ----------------------------------------------------
		// GPvars, incoming data
		$inputData = t3lib_div::GPvar('dataMappingForm', 1);

		// Update session data:
		if ($cmd == 'reload_from' || $cmd == 'clear' || $cmd == 'clear_body') {
			$currentMappingInfo = is_array($templatemapping['MappingInfo'])&&$cmd == 'reload_from' ? $templatemapping['MappingInfo'] : array();
			$this->cleanUpMappingInfoAccordingToDS($currentMappingInfo,$dataStruct);

			$sesDat['dataStruct'] = $dataStruct;
			$sesDat['currentMappingInfo'] = $currentMappingInfo;
			$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'].'_mappingInfo',$sesDat);
		} else {
			if ($cmd == 'save_data_mapping' && is_array($inputData)) {
				/* overwrite only if receiving new assignment */
				if ($inputData) {
					$currentMappingInfo =
						$this->array_merge_recursive_overrule($currentMappingInfo,$inputData);
				}

					// Adding data structure to session data so that the PREVIEW window can access the DS easily...
				$sesDat['dataStruct'] = $dataStruct;
				$sesDat['currentMappingInfo'] = $currentMappingInfo;
				$GLOBALS['BE_USER']->setAndSaveSessionData($this->MCONF['name'].'_mappingInfo',$sesDat);
			}
		}

		// SAVE to template object
		if ($cmd == 'save_to')	{
			$dataArr=array();

			// Set content, either for header or body:
			$templatemapping['MappingInfo_head'] = $currentMappingInfo_head;
			$templatemapping['MappingInfo'     ] = $currentMappingInfo;

			// Getting cached data:
			reset($dataStruct);

			// Init; read file, init objects:
			$fileContent = t3lib_div::getUrl($theFile);
			$htmlParse = t3lib_div::makeInstance('t3lib_parsehtml');
			$this->markupObj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');

				// Fix relative paths in source:
			$relPathFix=dirname(substr($theFile,strlen(PATH_site))).'/';
			$uniqueMarker = uniqid('###') . '###';
			$fileContent = $htmlParse->prefixResourcePath($relPathFix,$fileContent, array('A' => $uniqueMarker));
			$fileContent = $this->fixPrefixForLinks($relPathFix, $fileContent, $uniqueMarker);

			// Get BODY content for caching:
			$contentSplittedByMapping=$this->markupObj->splitContentToMappingInfo($fileContent,$currentMappingInfo);
			$templatemapping['MappingData_cached'] = $contentSplittedByMapping['sub']['ROOT'];

			// Get HEAD content for caching:
			list($html_header) =  $this->markupObj->htmlParse->getAllParts($htmlParse->splitIntoBlock('head',$fileContent),1,0);
			// Set up the markupObject to process only header-section tags:
			$this->markupObj->tags = $this->head_markUpTags;

			$h_currentMappingInfo=array();
			if (is_array($currentMappingInfo_head['headElementPaths']))	{
				foreach($currentMappingInfo_head['headElementPaths'] as $kk => $vv)	{
					$h_currentMappingInfo['el_'.$kk]['MAP_EL'] = $vv;
				}
			}

			$contentSplittedByMapping = $this->markupObj->splitContentToMappingInfo($html_header,$h_currentMappingInfo);
			$templatemapping['MappingData_head_cached'] = $contentSplittedByMapping;

			// Get <body> tag:
			$reg='';
			eregi('<body[^>]*>',$fileContent,$reg);
			$templatemapping['BodyTag_cached'] = $currentMappingInfo_head['addBodyTag'] ? $reg[0] : '';

			$TOuid = t3lib_BEfunc::wsMapId('tx_templavoila_tmplobj',$row['uid']);
			$dataArr['tx_templavoila_tmplobj'][$TOuid]['templatemapping'] = serialize($templatemapping);
			$dataArr['tx_templavoila_tmplobj'][$TOuid]['fileref_mtime'] = @filemtime($theFile);
			$dataArr['tx_templavoila_tmplobj'][$TOuid]['fileref_md5'] = @md5_file($theFile);

			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->stripslashes_values=0;
			$tce->start($dataArr,array());
			$tce->process_datamap();
			unset($tce);
			$msg[] = $GLOBALS['LANG']->getLL('msgMappingSaved');
			$row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj',$this->displayUid);
			$templatemapping = unserialize($row['templatemapping']);

			if (t3lib_div::GPvar('_save_to_return') ||
			    t3lib_div::GPvar('_save_to_return_x')) {
				header('Location: ' . t3lib_div::locationHeaderUrl($this->returnUrl));
				exit;
			}

			if (t3lib_div::GPvar('_save_to_preview') ||
			    t3lib_div::GPvar('_save_to_preview_x')) {
				header('Location: ' . t3lib_div::locationHeaderUrl($_SERVER['REQUEST_URI']) . '&SET[page]=preview');
				exit;
			}
		}

		// If a difference is detected...:
		$this->changedTO =
			(serialize($templatemapping['MappingInfo_head']) != serialize($currentMappingInfo_head)) ||
			(serialize($templatemapping['MappingInfo'     ]) != serialize($currentMappingInfo     ));

		if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
			// Making the menu
			$menuItems = array();

			if (!$singleView) {
					$menuItems[] = '<input type="submit" name="_clear" value="Clear all" title="Clears all mapping information currently set." />';

				// Make either "Preview" button (body) or "Set" button (header)
				if ($headerPart) {	// Header:
					$menuItems[] = '<input type="submit" name="_save_data_mapping" value="Set" title="Will update session data with current settings." />';
				} else {		// Body:
					$menuItems[] = '<input type="submit" name="_preview" value="Preview" title="Will merge sample content into the template according to the current mapping information." />';
				}

					$menuItems[] = '<input type="submit" name="_save_to" value="Save" title="Saving all mapping data into the Template Object." />';

				if ($this->returnUrl) {
					$menuItems[] = '<input type="submit" name="_save_to_return" value="Save and Return" title="Saving all mapping data into the Template Object and return." />';
				}

				// If a difference is detected...:
				if ($this->changedTO) {
					$menuItems[]='<input type="submit" name="_reload_from" value="Revert" title="'.sprintf('Reverting %s mapping data to original data in the Template Object.',$headerPart?'HEAD':'BODY').'" />';

					$msg[] = 'The current mapping information is different from the mapping information in the Template Object';
				}
			}
			else {
					$menuItems[] = '<input type="submit" name="_save_data_mapping" value="Apply changes" title="Will update session data with current settings." />';
			}

			$content = '

				<!--
					Menu for saving Template Objects
				-->
				<table border="0" cellpadding="2" cellspacing="2" id="c-toMenu">
					<tr class="bgColor5">
						<td>'.implode('</td>
						<td>',$menuItems).'</td>
					</tr>
				</table>
			';
		}

		// Making messages:
		foreach($msg as $msgStr) {
			$content .= '
				<p><img' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/icon_note.gif', 'width="18" height="16"') . ' border="0" align="top" class="absmiddle" alt="" /><strong>' . htmlspecialchars($msgStr) . '</strong></p>';
		}

		return array($content, $headerPart ? $currentMappingInfo_head : $currentMappingInfo);
	}

	/*******************************
	 *
	 * Mapper functions
	 *
	 *******************************/

	/**
	 * Renders the table with selection of part from the HTML header + bodytag.
	 *
	 * @param	string		The abs file name to read
	 * @param	array		Header mapping information
	 * @param	boolean		If true, show body tag.
	 * @param	string		HTML content to show after the Data Structure table.
	 * @return	string		HTML table.
	 */
	function renderHeaderSelection($displayFile, $currentHeaderMappingInfo, $showBodyTag, $htmlAfterDSTable = '') {

		// Get file content
		$this->markupFile = $displayFile;
		$fileContent = t3lib_div::getUrl($this->markupFile);

		// Init mark up object.
		$this->markupObj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');
		$this->markupObj->init();

		// Get <body> tag:
		$reg='';
		eregi('<body[^>]*>',$fileContent,$reg);
		$html_body = $reg[0];

		// Get <head>...</head> from template:
		$splitByHeader = $this->markupObj->htmlParse->splitIntoBlock('head',$fileContent);
		list($html_header) =  $this->markupObj->htmlParse->getAllParts($splitByHeader,1,0);

		// Set up the markupObject to process only header-section tags:
		$this->markupObj->tags = $this->head_markUpTags;
		$this->markupObj->checkboxPathsSet = is_array($currentHeaderMappingInfo['headElementPaths']) ? $currentHeaderMappingInfo['headElementPaths'] : array();
		// Should not enter more than one level.
		$this->markupObj->maxRecursion = 0;

		// Markup the header section data with the header tags, using "checkbox" mode:
		$tRows = $this->markupObj->markupHTMLcontent($html_header,$GLOBALS['BACK_PATH'], '','script,style,link,meta','checkbox');
		$bodyTagRow = $showBodyTag ? '
				<tr class="bgColor2">
					<td><input type="checkbox" name="addBodyTag" value="1"'.($currentHeaderMappingInfo['addBodyTag'] ? ' checked="checked"' : '').' /></td>
					<td><img src="../html_tags/body.gif" width="32" height="9" alt="" /></td>
					<td><pre>'.htmlspecialchars($html_body).'</pre></td>
				</tr>' : '';

		$headerParts = '
			<!--
				Header parts:
			-->
			<input type="hidden" name="headMappingForm[present]" value="1" />
			<table border="0" cellpadding="2" cellspacing="2" id="c-headerParts">
				<tr class="bgColor5">
					<td><strong>Incl:</strong></td>
					<td><strong>Tag:</strong></td>
					<td><strong>Tag content:</strong></td>
				</tr>
				'.$tRows.'
				'.$bodyTagRow.'
			</table>' .
		/*
			'<p style="margin: 5px 3px">' .
			'<img'.t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_warning.gif', 'width="18" height="16"').' alt="" align="absmiddle" /> '.
			'<strong>Do not forget to press "Set" if header parts are changed!</strong></p>' .
			$this->cshItem('xMOD_tx_templavoila','mapping_to_headerParts_buttons',$this->doc->backPath,'').
		*/
			$htmlAfterDSTable;

		// Return result:
		return $headerParts;
	}

	/**
	 * Creates the template mapper table + form for either direct file mapping or Template Object
	 *
	 * @param	string		The abs file name to read
	 * @param	string		The HTML-path to follow. Eg. 'td#content table[1] tr[1] / INNER | img[0]' or so. Normally comes from clicking a tag-image in the display frame.
	 * @param	array		The data Structure to map to
	 * @param	array		The current mapping information
	 * @param	string		HTML content to show after the Data Structure table.
	 * @return	string		HTML table.
	 */
	function renderTemplateMapper($displayFile, $path, $dataStruct = array(), $currentMappingInfo = array(), $htmlAfterDSTable = '') {
		global $BE_USER;

		// Get file content
		$this->markupFile = $displayFile;
		$fileContent = t3lib_div::getUrl($this->markupFile);

		// Init mark up object.
		$this->markupObj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');

		// Load splitted content from currentMappingInfo array (used to show us which elements maps to some real content).
		$contentSplittedByMapping = $this->markupObj->splitContentToMappingInfo($fileContent,$currentMappingInfo);

		// Show path:
		$pathRendered = t3lib_div::trimExplode('|', $path, 1);
		$acc=array();
		foreach($pathRendered as $k => $v) {
			$acc[] = $v;
			$pathRendered[$k] = $this->linkForDisplayOfPath($v, implode('|', $acc));
		}
		array_unshift($pathRendered, $this->linkForDisplayOfPath('[ROOT]', ''));

		// Get attributes of the extracted content:
		$attrDat=array();
		$contentFromPath = $this->markupObj->splitByPath($fileContent, $path);	// ,'td#content table[1] tr[1]','td#content table[1]','map#cdf / INNER','td#content table[2] tr[1] td[1] table[1] tr[4] td.bckgd1[2] table[1] tr[1] td[1] table[1] tr[1] td.bold1px[1] img[1] / RANGE:img[2]'
		$firstTag = $this->markupObj->htmlParse->getFirstTag($contentFromPath[1]);
		list($attrDat) = $this->markupObj->htmlParse->get_tag_attributes($firstTag,1);

		// Make options:
		$pathLevels = $this->markupObj->splitPath($path);
		$lastEl = end($pathLevels);

		$optDat = array();
		$optDat[$lastEl['path']] = 'OUTER (Include tag)';
		$optDat[$lastEl['path'] . '/INNER'] = 'INNER (Exclude tag)';

		// Tags, which will trigger "INNER" to be listed on top (because it is almost always INNER-mapping that is needed)
		if (t3lib_div::inList('body,span,h1,h2,h3,h4,h5,h6,div,td,p,b,i,u,a', $lastEl['el'])) {
			$optDat = array_reverse($optDat);
		}

		// Add options for "samelevel" elements:
		$sameLevelElements = $this->markupObj->elParentLevel[$lastEl['parent']];
		if (is_array($sameLevelElements)) {
			$startFound = 0;
			foreach ($sameLevelElements as $rEl)  {
				if ($startFound) {
					$optDat[$lastEl['path'] . '/RANGE:' . $rEl] = 'RANGE to "' . $rEl . '"';
				}

				if (trim($lastEl['parent'] . ' ' . $rEl) == $lastEl['path'])
					$startFound=1;
			}
		}

			// Add options for attributes:
		if (is_array($attrDat))	{
			foreach($attrDat as $attrK => $v) {
				$optDat[$lastEl['path'] . '/ATTR:' . $attrK] = 'ATTRIBUTE "' . $attrK . '" (= ' . t3lib_div::fixed_lgd_cs($v, 15) . ')';
			}
		}

		// Create Data Structure table:
		$content .= '

			<!--
				Data Structure table:
			-->
			<table border="0" cellspacing="2" cellpadding="2">
			<tr class="bgColor5">
				<td nowrap="nowrap"><strong>Data Element:</strong>'.
					$this->cshItem('xMOD_tx_templavoila', 'mapping_head_dataElement', $this->doc->backPath, '', TRUE).
					'</td>
				' . ($this->editDataStruct ? '
				<td nowrap="nowrap"><strong>Field:</strong>'.
					$this->cshItem('xMOD_tx_templavoila', 'mapping_head_Field', $this->doc->backPath, '', TRUE).
					'</td>' : '').'
				<td nowrap="nowrap"><strong>'.(!$this->_preview ? 'Mapping instructions:' : 'Sample Data:').'</strong>'.
					$this->cshItem('xMOD_tx_templavoila', 'mapping_head_' . (!$this->_preview ? 'mapping_instructions' : 'sample_data'), $this->doc->backPath, '', TRUE).
					'<br /><img src="clear.gif" width="200" height="1" alt="" /></td>
				<td nowrap="nowrap"><strong>Rules:</strong>'.
					$this->cshItem('xMOD_tx_templavoila', 'mapping_head_Rules', $this->doc->backPath, '', TRUE).
					'</td>
				<td nowrap="nowrap"><strong>HTML-path:</strong>'.
					$this->cshItem('xMOD_tx_templavoila', 'mapping_head_HTMLpath', $this->doc->backPath, '', TRUE).
					'</td>
				' . ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj') ? '
				<td nowrap="nowrap"><strong>Action:</strong>'.
					$this->cshItem('xMOD_tx_templavoila', 'mapping_head_Action', $this->doc->backPath, '', TRUE).
					'</td>' : '').'
				' . ($this->editDataStruct && !$this->_preview ? '
				<td nowrap="nowrap"><strong>Edit:</strong>'.
					$this->cshItem('xMOD_tx_templavoila', 'mapping_head_Edit', $this->doc->backPath, '', TRUE).
					'</td>' : '').'
			</tr>
			'. implode('', $this->drawDataStructureMap($dataStruct, 1, $currentMappingInfo, $pathLevels, $optDat, $contentSplittedByMapping)) . '</table>
			'. $htmlAfterDSTable;

		// Make mapping window:
		$limitTags = implode(',', array_keys($this->explodeMappingToTagsStr($this->mappingToTags, 1)));
		if (($this->mapElPath && !$this->doMappingOfPath) || $this->showPathOnly || $this->_preview) {
			$section=
			'<!--
				Visual Mapping Window (Iframe)
			-->
			<p>'.
				t3lib_BEfunc::getFuncMenu('', 'SET[displayMode]', $this->MOD_SETTINGS['displayMode'], $this->MOD_MENU['displayMode'], '', t3lib_div::implodeArrayForUrl('', $_GET, '', 1, 1)).
				$this->cshItem('xMOD_tx_templavoila', 'mapping_window_modes', $this->doc->backPath,'').
				'</p>';

			if ($this->_preview && !$this->mapElPath) {
				$label = 'Preview of Data Structure sample data merged into the mapped tags';
				$section .= $this->makeIframeForVisual($displayFile, '', '', 0, 1);
			}
			else {
				$label = 'Mapping Window';

			//	$tRows=array();
			//	if ($this->showPathOnly)	{
			//		$tRows[]='
			//			<tr class="bgColor4">
			//				<td class="bgColor5"><strong>HTML path:</strong></td>
			//				<td>'.htmlspecialchars($this->displayPath).'</td>
			//			</tr>
			//		';
			//	} else {
			//		$tRows[]='
			//			<tr class="bgColor4">
			//				<td class="bgColor5"><strong>Mapping DS element:</strong></td>
			//				<td>'.$this->elNames[$this->mapElPath]['tx_templavoila']['title'].'</td>
			//			</tr>
			//			<tr class="bgColor4">
			//				<td class="bgColor5"><strong>Limiting to tags:</strong></td>
			//				<td>'.htmlspecialchars(($limitTags?strtoupper($limitTags):'(ALL TAGS)')).'</td>
			//			</tr>
			//			<tr class="bgColor4">
			//				<td class="bgColor5"><strong>Instructions:</strong></td>
			//				<td>'.htmlspecialchars($this->elNames[$this->mapElPath]['tx_templavoila']['description']).'</td>
			//			</tr>
			//		';
			//
			//	}
			//
			//	$section .= '
			//
			//		<!--
			//			Mapping information table
			//		-->
			//		<table border="0" cellpadding="2" cellspacing="2" id="c-mapInfo">
			//			'.implode('',$tRows).'
			//		</table>
			//	';

				// Add the Iframe:
				$section .= $this->makeIframeForVisual($displayFile, $this->displayPath, $limitTags, $this->doMappingOfPath);
			}

			$content .= $this->doc->section(
				$label . ' ' . $this->cshItem('xMOD_tx_templavoila','mapping_window_help', $this->doc->backPath, ''),
				$section,
				FALSE,
				TRUE,
				0,
				TRUE);
		}

		return $content;
	}

	/**
	 * Renders the hierarchical display for a Data Structure.
	 * Calls itself recursively
	 *
	 * @param	array		Part of Data Structure (array of elements)
	 * @param	boolean		If true, the Data Structure table will show links for mapping actions. Otherwise it will just layout the Data Structure visually.
	 * @param	array		Part of Current mapping information corresponding to the $dataStruct array - used to evaluate the status of mapping for a certain point in the structure.
	 * @param	array		Array of HTML paths
	 * @param	array		Options for mapping mode control (INNER, OUTER etc...)
	 * @param	array		Content from template file splitted by current mapping info - needed to evaluate whether mapping information for a certain level actually worked on live content!
	 * @param	integer		Recursion level, counting up
	 * @param	array		Accumulates the table rows containing the structure. This is the array returned from the function.
	 * @param	string		Form field prefix. For each recursion of this function, two [] parts are added to this prefix
	 * @param	string		HTML path. For each recursion a section (divided by "|") is added.
	 * @param	boolean		If true, the "Map" link can be shown, otherwise not. Used internally in the recursions.
	 * @return	array		Table rows as an array of <tr> tags, $tRows
	 */
	function drawDataStructureMap($dataStruct, $mappingMode = 0, $currentMappingInfo = array(), $pathLevels = array(), $optDat = array(), $contentSplittedByMapping = array(), $level = 0, $tRows = array(), $formPrefix = '', $path = '', $mapOK = 1)	{
		global $LANG, $BE_USER;

		$bInfo = t3lib_div::clientInfo();
		$multilineTooltips = ($bInfo['BROWSER'] == 'msie');
		$rowIndex = -1;

		// Data Structure array must be ... and array of course...
		if (is_array($dataStruct)) {
			foreach ($dataStruct as $key => $value) {
				$rowIndex++;

				// Do not show <meta> information in mapping interface!
				if ($key == 'meta') {
					continue;
				}

				// The value of each entry must be an array.
				if (is_array($value)) {

					// ********************
					// Making the row:
					// ********************
					$rowCells=array();

					// Icon:
					$info = $this->dsTypeInfo($value);
					$icon = '<img' . $info[2] . ' alt="" title="' . $info[1] . $key . '" class="absmiddle" />';

					// Composing title-cell:
					if (preg_match('/^LLL:/', $value['tx_templavoila']['title'])) {
						$translatedTitle = $GLOBALS['LANG']->sL($value['tx_templavoila']['title']);
						$translateIcon = '<sup title="This title is translated!">*</sup>';
					}
					else {
						$translatedTitle = $value['tx_templavoila']['title'];
						$translateIcon = '';
					}
					$this->elNames[$formPrefix . '[' . $key . ']']['tx_templavoila']['title'] = $icon . '<strong>' . htmlspecialchars(t3lib_div::fixed_lgd_cs($translatedTitle, 30)).'</strong>'.$translateIcon;
					$rowCells['title'] = '<img src="clear.gif" width="' . ($level * 16) . '" height="1" alt="" />' . $this->elNames[$formPrefix.'['.$key.']']['tx_templavoila']['title'];

					// Description:
					$this->elNames[$formPrefix . '[' . $key . ']']['tx_templavoila']['description'] = $rowCells['description'] = htmlspecialchars($value['tx_templavoila']['description']);


					// Display mapping rules:
					$rowCells['tagRules'] = implode('<br />', t3lib_div::trimExplode(',', strtolower($value['tx_templavoila']['tags']),1));
					if (!$rowCells['tagRules'])
						$rowCells['tagRules']='(ALL)';

					// In "mapping mode", render HTML page and Command links:
					if ($mappingMode) {

						// HTML-path + CMD links:
						$isMapOK = 0;
						// If mapping information exists...:
						if ($currentMappingInfo[$key]['MAP_EL']) {
							// If mapping of this information also succeeded...:
							if (isset($contentSplittedByMapping['cArray'][$key])) {
								$cF = implode(chr(10),t3lib_div::trimExplode(chr(10),$contentSplittedByMapping['cArray'][$key],1));
								if (strlen($cF)>200)	{
									$cF = t3lib_div::fixed_lgd_cs($cF,90).' '.t3lib_div::fixed_lgd_cs($cF,-90);
								}

								// Render HTML path:
								list($pI) = $this->markupObj->splitPath($currentMappingInfo[$key]['MAP_EL']);
								$rowCells['htmlPath'] = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/icon_ok2.gif','width="18" height="16"').' border="0" alt="" title="'.htmlspecialchars($cF?'Content found ('.strlen($contentSplittedByMapping['cArray'][$key]).' chars)'.($multilineTooltips ? ':'.chr(10).chr(10).$cF:''):'Content empty.').'" class="absmiddle" />'.
														'<img src="../html_tags/'.$pI['el'].'.gif" height="9" border="0" alt="" hspace="3" class="absmiddle" title="---'.htmlspecialchars(t3lib_div::fixed_lgd_cs($currentMappingInfo[$key]['MAP_EL'],-80)).'" />'.
														($pI['modifier'] ? $pI['modifier'].($pI['modifier_value']?':'.($pI['modifier']!='RANGE'?$pI['modifier_value']:'...'):''):'');
								$rowCells['htmlPath'] = '<a href="'.$this->linkThisScript(array('htmlPath'=>$path.($path?'|':'').ereg_replace('\/[^ ]*$','',$currentMappingInfo[$key]['MAP_EL']),'showPathOnly'=>1)).'">'.$rowCells['htmlPath'].'</a>';

								// CMD links, default content:
								$rowCells['cmdLinks'] = '<span class="nobr"><input type="submit" value="Re-Map" name="_" onclick="document.location=\''.$this->linkThisScript(array('mapElPath'=>$formPrefix.'['.$key.']','htmlPath'=>$path,'mappingToTags'=>$value['tx_templavoila']['tags'])).'\';return false;" title="Map this DS element to another HTML element in template file." />'.
														'<input type="submit" value="Change Mode" name="_" onclick="document.location=\''.$this->linkThisScript(array('mapElPath'=>$formPrefix.'['.$key.']','htmlPath'=>$path.($path?'|':'').$pI['path'],'doMappingOfPath'=>1)).'\';return false;" title="Change mapping mode, eg. from INNER to OUTER etc." /></span>';

								// If content mapped ok, set flag:
								$isMapOK=1;
							} else {
								// Issue warning if mapping was lost:
								$rowCells['htmlPath'] = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/icon_warning.gif','width="18" height="16"').' border="0" alt="" title="No content found!" class="absmiddle" />'.htmlspecialchars($currentMappingInfo[$key]['MAP_EL']);
							}
						} else {
							// For non-mapped cases, just output a no-break-space:
							$rowCells['htmlPath'] = '&nbsp;';
						}

						// CMD links; Content when current element is under mapping, then display control panel or message:
						if ($this->mapElPath == ($formPrefix . '[' . $key . ']')) {
							if ($this->doMappingOfPath) {

								// Creating option tags:
								$lastLevel = end($pathLevels);
								$tagsMapping = $this->explodeMappingToTagsStr($value['tx_templavoila']['tags']);
								$mapDat = is_array($tagsMapping[$lastLevel['el']]) ? $tagsMapping[$lastLevel['el']] : $tagsMapping['*'];
								unset($mapDat['']);
								if (is_array($mapDat) && !count($mapDat))	unset($mapDat);

								// Create mapping options:
								$didSetSel=0;
								$opt=array();
								foreach($optDat as $k => $v)	{
									list($pI) = $this->markupObj->splitPath($k);

									if (($value['type']=='attr' && $pI['modifier']=='ATTR') || ($value['type']!='attr' && $pI['modifier']!='ATTR'))	{
										if (
												(!$this->markupObj->tags[$lastLevel['el']]['single'] || $pI['modifier']!='INNER') &&
												(!is_array($mapDat) || ($pI['modifier']!='ATTR' && isset($mapDat[strtolower($pI['modifier']?$pI['modifier']:'outer')])) || ($pI['modifier']=='ATTR' && (isset($mapDat['attr']['*']) || isset($mapDat['attr'][$pI['modifier_value']]))))

											)	{

											if($k==$currentMappingInfo[$key]['MAP_EL'])	{
												$sel = ' selected="selected"';
												$didSetSel=1;
											} else {
												$sel = '';
											}
											$opt[]='<option value="'.htmlspecialchars($k).'"'.$sel.'>'.htmlspecialchars($v).'</option>';
										}
									}
								}

									// Finally, put together the selector box:
								$rowCells['cmdLinks'] = '<img src="../html_tags/'.$lastLevel['el'].'.gif" height="9" border="0" alt="" class="absmiddle" title="---'.htmlspecialchars(t3lib_div::fixed_lgd_cs($lastLevel['path'],-80)).'" /><br />
									<select name="dataMappingForm'.$formPrefix.'['.$key.'][MAP_EL]">
										'.implode('
										',$opt).'
										<option value=""></option>
									</select>
									<br />
									<input type="submit" name="_save_data_mapping" value="Set" />
									<input type="submit" name="_" value="Cancel" />';
								$rowCells['cmdLinks'].=
									$this->cshItem('xMOD_tx_templavoila','mapping_modeset',$this->doc->backPath,'',FALSE,'margin-bottom: 0px;');
							} else {
								$rowCells['cmdLinks'] = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/icon_note.gif','width="18" height="16"').' border="0" alt="" class="absmiddle" /><strong>Click a tag-icon in the window below to map this element.</strong>';
								$rowCells['cmdLinks'].= '<br />
										<input type="submit" value="Cancel" name="_" onclick="document.location=\''.$this->linkThisScript(array()).'\';return false;" />';
							}
						} elseif (!$rowCells['cmdLinks'] && $mapOK && $value['type']!='no_map') {
							$rowCells['cmdLinks'] = '
										<input type="submit" value="Map" name="_" onclick="document.location=\''.$this->linkThisScript(array('mapElPath'=>$formPrefix.'['.$key.']','htmlPath'=>$path,'mappingToTags'=>$value['tx_templavoila']['tags'])).'\';return false;" />';
						}
					}

					// Display edit/delete icons:
					if ($this->editDataStruct && !$this->_preview) {
						$editAddCol = '<a href="'.$this->linkThisScript(array('DS_element_MUP'=>$formPrefix.'['.$key.']')).'" ' . ($isMapOK ? 'style="visibility: hidden;"' : '') . '>'.
						'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/button_up.gif','width="11" height="12"').' hspace="2" border="0" alt="" title="Move entry up" />'.
						'</a>';

						$editAddCol.= '<a href="'.$this->linkThisScript(array('DS_element_MDOWN'=>$formPrefix.'['.$key.']')).'" ' . ($isMapOK ? 'style="visibility: hidden;"' : '') . '>'.
						'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/button_down.gif','width="11" height="12"').' hspace="2" border="0" alt="" title="Move entry down" />'.
						'</a>';

						$editAddCol.= '<a href="'.$this->linkThisScript(array('DS_element'=>$formPrefix.'['.$key.']')).'">'.
						'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' hspace="2" border="0" alt="" title="Edit entry" />'.
						'</a>';

						$editAddCol.= '<a href="'.$this->linkThisScript(array('DS_element_DELETE'=>$formPrefix.'['.$key.']')).'">'.
						'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' hspace="2" border="0" alt="" title="DELETE entry" onclick=" return confirm(\'' . $LANG->getLL('mess.onDeleteAlert') . '\');" />'.
						'</a>';

						$editAddCol = '<td nowrap="nowrap">'.$editAddCol.'</td>';
					} else {
						$editAddCol = '';
					}

					// Description:
					if ($this->_preview) {
						$rowCells['description'] = is_array($value['tx_templavoila']['sample_data']) ? t3lib_div::view_array($value['tx_templavoila']['sample_data']) : '[No sample data]';
					}

					// Getting editing row, if applicable:
					list($addEditRows, $placeBefore) = $this->drawDataStructureMap_editItem($formPrefix, $key, $value, $level);

					// Add edit-row if found and destined to be set BEFORE:
					if ($addEditRows && $placeBefore) {
						$tRows[] = $addEditRows;
					}
					// Put row together
					else if (!$this->mapElPath || ($this->mapElPath == ($formPrefix . '[' . $key . ']'))) {
						$tRows[] = '

							<tr class="' . ($rowIndex % 2 ? 'bgColor4' : 'bgColor6') . '">
							<td nowrap="nowrap" valign="center">' . $rowCells['title'] . '</td>
							' . ($this->editDataStruct ? '
							<td nowrap="nowrap">' . $key . '</td>' : '').'
							<td>' . $rowCells['description'] . '</td>
							<td>' . $rowCells['tagRules'] . '</td>
							' . ($mappingMode ? '
							<td nowrap="nowrap">' . $rowCells['htmlPath'] . '</td>' : '').'
							' . ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj') ? '
							<td>' . $rowCells['cmdLinks'] . '</td>' : '').'
							' . $editAddCol . '
						</tr>';
					}

					// Recursive call:
					if (($value['type'] == 'array') ||
					    ($value['type'] == 'section')) {

						$tRows = $this->drawDataStructureMap(
							$value['el'],
							$mappingMode,
							$currentMappingInfo[$key]['el'],
							$pathLevels,
							$optDat,
							$contentSplittedByMapping['sub'][$key],
							$level + 1,
							$tRows,
							$formPrefix . '[' . $key . '][el]',
							$path . ($path ? '|' : '') . $currentMappingInfo[$key]['MAP_EL'],
							$isMapOK
						);
					}

					// Add edit-row if found and destined to be set AFTER:
					if ($addEditRows && !$placeBefore) {
						$tRows[] = $addEditRows;
					}
				}
			}
		}

		return $tRows;
	}

	/**
	 * Creates the editing row for a Data Structure element - when DS's are build...
	 *
	 * @param	string		Form element prefix
	 * @param	string		Key for form element
	 * @param	array		Values for element
	 * @param	integer		Indentation level
	 * @return	array		Two values, first is addEditRows (string HTML content), second is boolean whether to place row before or after.
	 */
	function drawDataStructureMap_editItem($formPrefix,$key,$value,$level)	{
		global $LANG;

			// Init:
		$addEditRows='';
		$placeBefore=0;

			// If editing command is set:
			// Triggering the preview turn it off
		if ($this->editDataStruct && !$this->_preview)	{
			if ($this->DS_element == $formPrefix.'['.$key.']')	{	// If the editing-command points to this element:

					// Initialize, detecting either "add" or "edit" (default) mode:
				$autokey='';
				if ($this->DS_cmd=='add')	{
					if (trim($this->fieldName)!='[Enter new fieldname]' && trim($this->fieldName)!='field_')	{
						$autokey = strtolower(ereg_replace('[^[:alnum:]_]','',trim($this->fieldName)));
						if (isset($value['el'][$autokey]))	{
							$autokey.='_'.substr(md5(microtime()),0,2);
						}
					} else {
						$autokey='field_'.substr(md5(microtime()),0,6);
					}

						// new entries are more offset
					$level = $level + 1;

					$formFieldName = 'autoDS'.$formPrefix.'['.$key.'][el]['.$autokey.']';
					$insertDataArray=array();
				} else {
					$placeBefore = 1;

					$formFieldName = 'autoDS'.$formPrefix.'['.$key.']';
					$insertDataArray=$value;
				}

				/* do the preset-completition */
				$real = array($key => &$insertDataArray);
				$this->substEtypeWithRealStuff($real);

				/* ... */
				if (($insertDataArray['type'] == 'array') &&
					($insertDataArray['section'] == '1'))
					$insertDataArray['type'] = 'section';

					// Create form:
				/* The basic XML-structure of an tx_templavoila-entry is:
				 *
				 * <tx_templavoila>
				 * 	<title>			-> Human readable title of the element
				 * 	<description>		-> A description explaining the elements function
				 * 	<sample_data>		-> Some sample-data (can't contain HTML)
				 * 	<eType>			-> The preset-type of the element, used to switch use/content of TCEforms/TypoScriptObjPath
				 * 	<oldStyleColumnNumber>	-> for distributing the fields across the tt_content column-positions
				 * </tx_templavoila>
				 */
				$form = '
				<dl id="dsel-general" class="DS-config">
					<!-- always present options +++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
					<dt><label>Title:</label></dt>
					<dd><input type="text" size="80" name="'.$formFieldName.'[tx_templavoila][title]" value="'.htmlspecialchars($insertDataArray['tx_templavoila']['title']).'" /></dd>

					<dt><label>Mapping instructions:</label></dt>
					<dd><input type="text" size="80" name="'.$formFieldName.'[tx_templavoila][description]" value="'.htmlspecialchars($insertDataArray['tx_templavoila']['description']).'" /></dd>

					'.(($insertDataArray['type'] != 'array') &&
					   ($insertDataArray['type'] != 'section') ? '
					<!-- non-array options ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
					<dt><label>Sample Data:</label></dt>
					<dd><textarea class="fixed-font enable-tab" cols="80" rows="5" name="'.$formFieldName.'[tx_templavoila][sample_data][]">'.t3lib_div::deHSCentities(htmlspecialchars($insertDataArray['tx_templavoila']['sample_data'][0])).'</textarea>
					'.$this->lipsumLink($formFieldName.'[tx_templavoila][sample_data]').'</dd>

					<dt><label>Element Preset:</label></dt>
					<dd><select onchange="if (confirm(\''.$LANG->getLL('mess.onChangeAlert').'\')) document.getElementById(\'_updateDS\').click();"
						name="'.$formFieldName.'[tx_templavoila][eType]">
						<optgroup class="c-divider" label="TCE-Fields">
							<option value="input"'.           ($insertDataArray['tx_templavoila']['eType']=='input'            ? ' selected="selected"' : '').'>Plain input field</option>
							<option value="input_h"'.         ($insertDataArray['tx_templavoila']['eType']=='input_h'          ? ' selected="selected"' : '').'>Header field</option>
							<option value="input_g"'.         ($insertDataArray['tx_templavoila']['eType']=='input_g'          ? ' selected="selected"' : '').'>Header field, Graphical</option>
							<option value="text"'.            ($insertDataArray['tx_templavoila']['eType']=='text'             ? ' selected="selected"' : '').'>Text area for bodytext</option>
							<option value="rte"'.             ($insertDataArray['tx_templavoila']['eType']=='rte'              ? ' selected="selected"' : '').'>Rich text editor for bodytext</option>
							<option value="link"'.            ($insertDataArray['tx_templavoila']['eType']=='link'             ? ' selected="selected"' : '').'>Link field</option>
							<option value="int"'.             ($insertDataArray['tx_templavoila']['eType']=='int'              ? ' selected="selected"' : '').'>Integer value</option>
							<option value="image"'.           ($insertDataArray['tx_templavoila']['eType']=='image'            ? ' selected="selected"' : '').'>Image field</option>
							<option value="imagefixed"'.      ($insertDataArray['tx_templavoila']['eType']=='imagefixed'       ? ' selected="selected"' : '').'>Image field, fixed W+H</option>
							<option value="select"'.          ($insertDataArray['tx_templavoila']['eType']=='select'           ? ' selected="selected"' : '').'>Selector box</option>
						</optgroup>
						<optgroup class="c-divider" label="TS-Elements">
							<option value="ce"'.              ($insertDataArray['tx_templavoila']['eType']=='ce'               ? ' selected="selected"' : '').'>Page-Content Elements [Pos.: '.($insertDataArray['tx_templavoila']['oldStyleColumnNumber'] ? $insertDataArray['tx_templavoila']['oldStyleColumnNumber'] : 'to be defined').']</option>
							<option value="TypoScriptObject"'.($insertDataArray['tx_templavoila']['eType']=='TypoScriptObject' ? ' selected="selected"' : '').'>TypoScript Object Path</option>
						</optgroup>
						<optgroup class="c-divider" label="Other">
							<option value="none"'.            ($insertDataArray['tx_templavoila']['eType']=='none'             ? ' selected="selected"' : '').'>None (only TS)</option>
							<option value="custom"'.          ($insertDataArray['tx_templavoila']['eType']=='custom'           ? ' selected="selected"' : '').'>Custom TCE</option>
						</optgroup>
					</select><input type="hidden"
						name="'.$formFieldName.'[tx_templavoila][eType_before]"
						value="'.$insertDataArray['tx_templavoila']['eType'].'" /></dd>
					' :'').'

					<dt><label>Mapping rules:</label></dt>
					<dd><input type="text" size="80" name="'.$formFieldName.'[tx_templavoila][tags]" value="'.htmlspecialchars($insertDataArray['tx_templavoila']['tags']).'" /></dd>
					<dt><label>Inheritance:</label></dt>
					<dd>
						<input type="radio" name="'.$formFieldName.'[tx_templavoila][inheritance]" value="0" '.($insertDataArray['tx_templavoila']['inheritance']==0?'checked="checked"':'').' /> Never<br />
						<input type="radio" name="'.$formFieldName.'[tx_templavoila][inheritance]" value="1" '.($insertDataArray['tx_templavoila']['inheritance']==1?'checked="checked"':'').' /> Replace<br />
						<input type="radio" name="'.$formFieldName.'[tx_templavoila][inheritance]" value="2" '.($insertDataArray['tx_templavoila']['inheritance']==2?'checked="checked"':'').' /> Accumulate<br />
					</dd>
				</dl>';

			/*	// The dam-tv-connector will substitute the text above, that's $%*%&"$%, but well anyway, let's not break it
				if (count($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['cm1']['eTypesExtraFormFields']) > 0) {
				$form .= '
						<optgroup class="c-divider" label="Extra Elements">';
					foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['cm1']['eTypesExtraFormFields'] as $key => $value) {
							<option value="'.$key.'"'.($insertDataArray['tx_templavoila']['eType']==$key ? ' selected="selected"' : '').'>'.$key.'</option>
					}
				$form .= '
						</optgroup>';
				}	*/

				if (($insertDataArray['type'] != 'array') &&
					($insertDataArray['type'] != 'section')) {
					/* The Typoscript-related XML-structure of an tx_templavoila-entry is:
					 *
					 * <tx_templavoila>
					 *	<TypoScript_constants>	-> an array of constants that will be substituted in the <TypoScript>-element
					 * 	<TypoScript>		->
					 * </tx_templavoila>
					 */
					if ($insertDataArray['tx_templavoila']['eType'] != 'TypoScriptObject')
					$form .= '
					<dl id="dsel-ts" class="DS-config">
						<dt><label>Typoscript Constants:</label></dt>
						<dd><textarea class="fixed-font enable-tab xml" cols="80" rows="10" name="'.$formFieldName.'[tx_templavoila][TypoScript_constants]" rel="tx_templavoila.TypoScript_constants">'.htmlspecialchars($this->flattenarray($insertDataArray['tx_templavoila']['TypoScript_constants'])).'</textarea></dd>
						<dt><label>Typoscript Code:</label></dt>
						<dd>' . (!$this->t3e ? '
							<textarea
								class="fixed-font enable-tab ts"
								cols="80"
								rows="10"
								name="'.$formFieldName.'[tx_templavoila][TypoScript]"
								rel="tx_templavoila.TypoScript">'.
								htmlspecialchars($insertDataArray['tx_templavoila']['TypoScript']).'
							</textarea>' :
							str_replace('<br/>', '', $this->t3e->getCodeEditor(
								$formFieldName . '[tx_templavoila][TypoScript]',
								'ts',
								htmlspecialchars($insertDataArray['tx_templavoila']['TypoScript']),
								'cols="80" rows="10" rel="tx_templavoila.TypoScript" id="dsel-t3editor"'))) . '
						</dd>
					</dl>';

					/* The Typoscript-related XML-structure of an tx_templavoila-entry is:
					 *
					 * <tx_templavoila>
					 * 	<TypoScriptObjPath>	->
					 * </tx_templavoila>
					 */
					if (($extra = $this->drawDataStructureMap_editItem_editTypeExtra(
							$insertDataArray['tx_templavoila']['eType'],
							$formFieldName.'[tx_templavoila][eType_EXTRA]',
							($insertDataArray['tx_templavoila']['eType_EXTRA'] ?	// Use eType_EXTRA only if it is set (could be modified, etc), otherwise use TypoScriptObjPath!
								$insertDataArray['tx_templavoila']['eType_EXTRA'] :
									($insertDataArray['tx_templavoila']['TypoScriptObjPath'] ?
									array('objPath' => $insertDataArray['tx_templavoila']['TypoScriptObjPath']) : ''))
						)))
					$form .= '
					<dl id="dsel-extra" class="DS-config">
						<dt>Extra options</dt>
						<dd>'.$extra.'</dd>
					</dl>';

					/* The process-related XML-structure of an tx_templavoila-entry is:
					 *
					 * <tx_templavoila>
					 * 	<proc>			-> define post-processes for this element's value
					 *		<int>		-> this element's value will be cast to an integer (if exist)
					 *		<HSC>		-> this element's value will convert special chars to HTML-entities (if exist)
					 *		<stdWrap>	-> an implicit stdWrap for this element, "stdWrap { ...inside... }"
					 * 	</proc>
					 * </tx_templavoila>
					 */
					$form .= '
					<dl id="dsel-proc" class="DS-config">
						<dt>Applied post-processes:</dt>
						<dd>
							<label style="float: left; width: 20em;">Cast content to integer:</label>
							<input type="radio" name="'.$formFieldName.'[tx_templavoila][proc][int]" value="1" '.( $insertDataArray['tx_templavoila']['proc']['int'] ? 'checked="checked"' : '').' /> yes
							<input type="radio" name="'.$formFieldName.'[tx_templavoila][proc][int]" value="0" '.(!$insertDataArray['tx_templavoila']['proc']['int'] ? 'checked="checked"' : '').' /> no
							<br />
							<label style="float: left; width: 20em;">Pass content through htmlentities():</label>
							<input type="radio" name="'.$formFieldName.'[tx_templavoila][proc][HSC]" value="1" '.( $insertDataArray['tx_templavoila']['proc']['HSC'] ? 'checked="checked"' : '').' /> yes
							<input type="radio" name="'.$formFieldName.'[tx_templavoila][proc][HSC]" value="0" '.(!$insertDataArray['tx_templavoila']['proc']['HSC'] ? 'checked="checked"' : '').' /> no
						</dd>

						<dt><label>Custom stdWrap:</label></dt>
						<dd><textarea class="fixed-font enable-tab ts" cols="80" rows="10" name="'.$formFieldName.'[tx_templavoila][proc][stdWrap]" rel="tx_templavoila.proc.stdWrap">'.htmlspecialchars($insertDataArray['tx_templavoila']['proc']['stdWrap']).'</textarea></dd>
					</dl>';

					/* The basic XML-structure of an TCEforms-entry is:
					 *
					 * <TCEforms>
					 * 	<label>			-> TCE-label for the BE
					 * 	<config>		-> TCE-configuration array
					 * </TCEforms>
					 */
					if ($insertDataArray['tx_templavoila']['eType'] != 'TypoScriptObject')
					$form .= '
					<dl id="dsel-tce" class="DS-config">
						<dt><label>TCE Label:</label></dt>
						<dd><input type="text" size="80" name="'.$formFieldName.'[TCEforms][label]" value="'.htmlspecialchars($insertDataArray['TCEforms']['label']).'" /></dd>

						<dt><label>TCE Configuration:</label></dt>
						<dd><textarea class="fixed-font enable-tab xml" cols="80" rows="10" name="'.$formFieldName.'[TCEforms][config]" rel="TCEforms.config">'.htmlspecialchars($this->flattenarray($insertDataArray['TCEforms']['config'])).'</textarea></dd>

						<dt><label>TCE Extras:</label></dt>
						<dd><input type="text" size="80" name="'.$formFieldName.'[TCEforms][defaultExtras]" value="'.htmlspecialchars($insertDataArray['TCEforms']['defaultExtras']).'" /></dd>
					</dl>';
				}
				else if ($insertDataArray['type'] != 'section') {
					/* The process-related XML-structure of an tx_templavoila-entry is:
					 *
					 * <tx_templavoila>
					 * 	<proc>			-> define post-processes for this element's value
					 *		<stdWrap>	-> an implicit stdWrap for this element, "stdWrap { ...inside... }"
					 * 	</proc>
					 * </tx_templavoila>
					 */
					$form .= '
					<dl id="dsel-proc" class="DS-config">
						<dt><label>Custom stdWrap:</label></dt>
						<dd><textarea class="fixed-font enable-tab ts" cols="80" rows="10" name="'.$formFieldName.'[tx_templavoila][proc][stdWrap]" rel="tx_templavoila.proc.stdWrap">'.htmlspecialchars($insertDataArray['tx_templavoila']['proc']['stdWrap']).'</textarea></dd>
					</dl>';
				}

				$formSubmit = '
					<input type="hidden" name="DS_element" value="'.htmlspecialchars($this->DS_cmd=='add' ? $this->DS_element.'[el]['.$autokey.']' : $this->DS_element).'" />
					<input type="submit" name="_updateDS" id="_updateDS" style="display: none;" />
					<input type="image" name="_updateDS" title="'.($this->DS_cmd == 'add' ? 'Add' : 'Update').'"
						style="cursor: pointer; vertical-align: middle;" src="' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/savedok'.($this->DS_cmd == 'add' ? 'new' : '').'.gif', '', 1) . '" />
<!--					<input type="submit" name="'.$formFieldName.'" value="Delete (!)" />  -->
					<img                                 title="'.($this->DS_cmd=='add' ? 'Cancel' : 'Cancel/Close').'"
						style="cursor: pointer; vertical-align: middle;" src="' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/close.gif', '', 1) . '"
						onclick="document.location=\''.$this->linkThisScript().'\'; return false;" hspace="2" />
				';

				/* The basic XML-structure of an entry is:
				 *
				 * <element>
				 * 	<tx_templavoila>	-> entries with informational character belonging to this entry
				 * 	<TCEforms>		-> entries being used for TCE-construction
				 * 	<type + el + section>	-> subsequent hierarchical construction
				 *	<langOverlayMode>	-> ??? (is it the language-key?)
				 * </element>
				 */

					// Icons:
				$info = $this->dsTypeInfo($insertDataArray);

				$addEditRows = '<tr class="bgColor4 tv-edit-row">
					<td valign="top" style="padding: 0.8em 2px 0.8em '.(($level) * 16 + 3).'px;" nowrap="nowrap">
						<select style="margin: 4px 0 4px 0; padding: 1px 1px 1px 30px; background: 0 50% url(' . $info[3] . ') no-repeat; width: 150px !important;" title="Mapping Type" name="'.$formFieldName.'[type]" onchange="if (confirm(\''.$LANG->getLL('mess.onChangeAlert').'\')) document.getElementById(\'_updateDS\').click();">
							<optgroup class="c-divider" label="Containers">
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['sc'][3] . ') no-repeat;" value="section"'. ($insertDataArray['type']=='section' ? ' selected="selected"' : '').'>Section of elements</option>
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['co'][3] . ') no-repeat;" value="array"'.   ($insertDataArray['type']=='array'   ? ' selected="selected"' : '').'>Container for elements</option>
							</optgroup>
							<optgroup class="c-divider" label="Elements">
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['el'][3] . ') no-repeat;" value=""'.        ($insertDataArray['type']==''        ? ' selected="selected"' : '').'>Element</option>
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['at'][3] . ') no-repeat;" value="attr"'.    ($insertDataArray['type']=='attr'    ? ' selected="selected"' : '').'>Attribute</option>
							</optgroup>
							<optgroup class="c-divider" label="Other">
								<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['no'][3] . ') no-repeat;" value="no_map"'.  ($insertDataArray['type']=='no_map'  ? ' selected="selected"' : '').'>Not mapped</option>
							</optgroup>
						</select>
						<div style="margin: 0.25em;">' .
							($this->DS_cmd == 'add' ? $autokey . ' <strong>(new)</strong>:<br />' : $key) .
						'</div>
						<input id="dsel-act" type="hidden" name="dsel_act" />
						<ul id="dsel-menu" class="DS-tree">
							<li><a id="dssel-general" class="active" href="#" onclick="" title="edit general configuration">Configuration</a>
								<ul>
									<li><a id="dssel-proc" href="#" title="edit data-processing modifications">Data-Processing</a></li>
									<li><a id="dssel-ts" href="#" title="edit TypoScript specializations">Typo-Script</a></li>
									<li class="last-child"><a id="dssel-extra" href="#" title="edit extra options">Extra</a></li>
								</ul>
							</li>
							<li class="last-child"><a id="dssel-tce" href="#" title="edit TCE-Form configuration">TCE-Form</a></li>
						</ul>
						' . $this->cshItem('xMOD_tx_templavoila', 'mapping_editform', $this->doc->backPath, '', FALSE, 'margin-bottom: 0px;') . '
					</td>
					<td valign="top" style="padding: 0.8em 2px 0.8em 2px;" colspan="3">
						' . $form . '
						<script type="text/javascript">
							var dsel_act = "' . (t3lib_div::GPvar('dsel_act') ? t3lib_div::GPvar('dsel_act') : 'general') . '";
							var dsel_menu = [
								{"id" : "general",		"avail" : true,	"label" : "Configuration",	"title" : "edit general configuration",	"childs" : [
									{"id" : "ts",		"avail" : true,	"label" : "Typo-Script",	"title" : "edit TypoScript specializations"},
									{"id" : "extra",	"avail" : true,	"label" : "Extra",		"title" : "edit extra options"},
									{"id" : "proc",		"avail" : true,	"label" : "Data-Processing",	"title" : "edit data-processing modifications"}]},
								{"id" : "tce",			"avail" : true,	"label" : "TCE-Form",		"title" : "edit TCE-Form configuration"}
							];

							function dsel_menu_construct(dsul, dsmn) {
								if (dsul) {
									while (dsul.childNodes.length)
										dsul.removeChild(dsul.childNodes[0]);
									for (var el = 0, pos = 0; el < dsmn.length; el++) {
										var tab = document.getElementById("dsel-" + dsmn[el]["id"]);
										var stl = "none";
										if (tab) { if (dsmn[el]["avail"]) {
											var tx = document.createTextNode(dsmn[el]["label"]);
											var ac = document.createElement("a"); ac.appendChild(tx);
											var li = document.createElement("li"); li.appendChild(ac);
											ac.title = dsmn[el]["title"]; ac.href = "#dsel-menu"; ac.rel = dsmn[el]["id"];
											ac.className = (dsel_act == dsmn[el]["id"] ? "active" : "");
											ac.onclick = function() { dsel_act = this.rel; dsel_menu_reset(); };
											if (dsmn[el]["childs"]) {
												var ul = document.createElement("ul");
												dsel_menu_construct(ul, dsmn[el]["childs"]);
												li.appendChild(ul);
											}
											dsul.appendChild(li);
											stl = (dsel_act == dsmn[el]["id"] ? "" : "none");
										} tab.style.display = stl; }
									}
									if (dsul.lastChild)
										dsul.lastChild.className = "last-child";
								}
							}

							function dsel_menu_reset() {
								dsel_menu_construct(document.getElementById("dsel-menu"), dsel_menu);
								document.getElementById("dsel-act").value = dsel_act;
							}

							dsel_menu_reset();
							xmlarea_init();
						</script>
					</td>
					<td valign="top" align="right" style="padding: 0.5em 2px 0.5em 2px;" colspan="3">
					' . $formSubmit . '
					</td>
				</tr>';
			}
			else if (!$this->DS_element && ($value['type']=='array' || $value['type']=='section') && !$this->mapElPath) {
				$addEditRows='<tr class="bgColor4">
					<td colspan="7">
						<img src="clear.gif" width="'.(($level+1)*16).'" height="1" alt="" />
						<input type="text" name="'.md5($formPrefix.'['.$key.']').'" value="[Enter new fieldname]" onfocus="if (this.value==\'[Enter new fieldname]\'){this.value=\'field_\';}" />
						<img style="cursor: pointer; vertical-align: -3px;" title="Add" src="' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/new_record.gif', '', 1) . '" hspace="2"
							onclick="document.location=\''.$this->linkThisScript(array('DS_element'=>$formPrefix.'['.$key.']','DS_cmd'=>'add')).'&amp;fieldName=\'+document.pageform[\''.md5($formPrefix.'['.$key.']').'\'].value; return false;" />
						'.$this->cshItem('xMOD_tx_templavoila','mapping_addfield',$this->doc->backPath,'',FALSE,'margin-bottom: 0px;').'
					</td>
				</tr>';
			}
		}

			// Return edit row:
		return array($addEditRows,$placeBefore);
	}

	/**
	 * Renders extra form fields for configuration of the Editing Types.
	 *
	 * @param	string		Editing Type string
	 * @param	string		Form field name prefix
	 * @param	array		Current values for the form field name prefix.
	 * @return	string		HTML with extra form fields
	 * @access	private
	 * @see drawDataStructureMap_editItem()
	 */
	function drawDataStructureMap_editItem_editTypeExtra($type, $formFieldName, $curValue)	{
			// If a user function was registered, use that instead of our own handlers:
		if (isset ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['cm1']['eTypesExtraFormFields'][$type])) {
			$_params = array (
				'type' => $type,
				'formFieldName' => $formFieldName,
				'curValue' => $curValue,
			);

			$output = t3lib_div::callUserFunction($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['cm1']['eTypesExtraFormFields'][$type], $_params, $this);
		}
		else {
			switch($type)	{
				case 'TypoScriptObject':
					$output = '
						Object path:<br />
						<input style="width: 95%;" id="browser[result]" type="text" name="'.$formFieldName.'[objPath]" value="'.htmlspecialchars($curValue['objPath'] ? $curValue['objPath'] : 'lib.myObject').'" />
					';

					$output .= '
						Context:<br />
						<div style="width: 94.7%;" class="tv-ts-tree">
							<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/insert3.gif') . ' title="Context" alt="Load context"
								 onclick="setFormValueOpenBrowser(\'db\',\'browser[communication]|||pages\');" style="cursor: pointer; vertical-align: middle;" />
							<span id="browser[context]">root <em>[pid: 0]</em></span>
							<iframe width="100%" height="400" style="border: 0;" id="browser[communication]" src="' . $this->baseScript . 'mode=browser&pid=&current='.$curValue['objPath'].'"></iframe>
						</div>
					';
					break;
			}
		}

		return $output;
	}

	/****************************************************
	 *
	 * Helper-functions for File-based DS/TO creation
	 *
	 ****************************************************/

	/**
	 * When mapping HTML files to DS the field types are selected amount some presets - this function converts these presets into the actual settings needed in the DS
	 * Typically called like: ->substEtypeWithRealStuff($storeDataStruct['ROOT']['el'],$contentSplittedByMapping['sub']['ROOT']);
	 * Notice: this function is used to preview XML also. In this case it is always called with $scope=0, so XML for 'ce' type will not contain wrap with TYPO3SEARCH_xxx. Currently there is no way to avoid it.
	 *
	 * @param	array		$elArray: Data Structure, passed by reference!
	 * @param	array		$v_sub: Actual template content splitted by Data Structure
	 * @param	int		$scope: Scope as defined in tx_templavoila_datastructure.scope
	 * @return	void		Note: The result is directly written in $elArray
	 * @see renderFile()
	 */
	function substEtypeWithRealStuff(&$elArray, $v_sub = array(), $scope = TVDS_SCOPE_OTHER) {
		$eTypeCECounter = 0;

		t3lib_div::loadTCA('tt_content');

			// Traverse array
		foreach ($elArray as $key => $value) {
			// this MUST not ever enter the XMLs (it will break TV)
			if ($elArray[$key]['type'] == 'section') {
				$elArray[$key]['type'] = 'array';
				$elArray[$key]['section'] = '1';
			}

			// put these into array-form for preset-completition
			if (!is_array($elArray[$key]['tx_templavoila']['TypoScript_constants']))
				$elArray[$key]['tx_templavoila']['TypoScript_constants'] =
					$this->unflattenarray($elArray[$key]['tx_templavoila']['TypoScript_constants']);
			if (!is_array($elArray[$key]['TCEforms']['config']))
				$elArray[$key]['TCEforms']['config'] =
					$this->unflattenarray($elArray[$key]['TCEforms']['config']);

			/* ---------------------------------------------------------------------- */
			if (($elArray[$key]['type'] == '') ||
				($elArray[$key]['type'] == 'attr')) {
				/* this is the default in the selector */
				if (!$insertDataArray['tx_templavoila']['eType']) {
					$insertDataArray['tx_templavoila']['eType_before'] = '';
					$insertDataArray['tx_templavoila']['eType'] = 'input';
				}
			}

				// this is too much different to preserve any previous information
			$reset = isset($elArray[$key]['tx_templavoila']['eType_before']) &&
					  ($elArray[$key]['tx_templavoila']['eType_before'] !=
					   $elArray[$key]['tx_templavoila']['eType']);

			unset($elArray[$key]['tx_templavoila']['eType_before']);
		//	unset($elArray[$key]['tx_templavoila']['proc']);

			/* ---------------------------------------------------------------------- */
			if (is_array ($elArray[$key]['tx_templavoila']['sample_data'])) {
				foreach ($elArray[$key]['tx_templavoila']['sample_data'] as $tmpKey => $tmpValue) {
					$elArray[$key]['tx_templavoila']['sample_data'][$tmpKey] = htmlspecialchars($tmpValue);
				}
			} else {
				$elArray[$key]['tx_templavoila']['sample_data']= htmlspecialchars($elArray[$key]['tx_templavoila']['sample_data']);
			}

			/* ---------------------------------------------------------------------- */
			if ($elArray[$key]['type']=='array')	{	// If array, then unset:
				unset($elArray[$key]['tx_templavoila']['sample_data']);
				unset($elArray[$key]['tx_templavoila']['eType']);
			} else {	// Only non-arrays can have configuration (that is elements and attributes)

					// Getting some information about the HTML content (eg. images width/height if applicable)
				$contentInfo = $this->substEtypeWithRealStuff_contentInfo(trim($v_sub['cArray'][$key]));

					// Based on the eType (the preset type) we make configuration settings.
					// If a user function was registered, use that instead of our own handlers:
				if (isset ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['cm1']['eTypesConfGen'][$elArray[$key]['tx_templavoila']['eType']])) {
					$_params = array (
						'key' => $key,
						'elArray' => &$elArray,
						'contentInfo' => $contentInfo,
					);

					$bef = $elArray[$key]['tx_templavoila']['TypoScript'];

					t3lib_div::callUserFunction($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['templavoila']['cm1']['eTypesConfGen'][$elArray[$key]['tx_templavoila']['eType']], $_params, $this,'');

					if (!$reset && trim($bef))
						$elArray[$key]['tx_templavoila']['TypoScript'] = $bef;
				} else {
					switch($elArray[$key]['tx_templavoila']['eType'])	{
						case 'text':
							/* preserve previous config, if of the right kind */
							if (($reset = $reset || ($elArray[$key]['TCEforms']['config']['type'] != 'text'))) {
								$elArray[$key]['TCEforms']['label']=$elArray[$key]['tx_templavoila']['title'];
								$elArray[$key]['TCEforms']['config'] = array(
									'type' => 'text',
									'cols' => '48',
									'rows' => '5',
								);
							}

							/* preserve previous config, if explicitly set */
							if (!isset($elArray[$key]['tx_templavoila']['proc']['HSC']) || $reset)
								$elArray[$key]['tx_templavoila']['proc']['HSC'] = 1;
							break;
						case 'rte':
							/* preserve previous config, if of the right kind */
							if (($reset = $reset || ($elArray[$key]['TCEforms']['config']['type'] != 'text'))) {
								$elArray[$key]['TCEforms']['label']=$elArray[$key]['tx_templavoila']['title'];
								$elArray[$key]['TCEforms']['config'] = array(
									'type' => 'text',
									'cols' => '48',
									'rows' => '5',
									'softref' => (isset($GLOBALS['TCA']['tt_content']['columns']['bodytext']['config']['softref']) ?
													$GLOBALS['TCA']['tt_content']['columns']['bodytext']['config']['softref'] :
													'typolink_tag,images,email[subst],url'),
								);
							}

							/* preserve previous config, if explicitly set */
							if (!$elArray[$key]['TCEforms']['defaultExtras'])
								$elArray[$key]['TCEforms']['defaultExtras'] = 'richtext:rte_transform[flag=rte_enabled|mode=ts_css]';
							/* preserve previous config, if explicitly set */
							if (!isset($elArray[$key]['tx_templavoila']['proc']['HSC']) || $reset)
								$elArray[$key]['tx_templavoila']['proc']['HSC'] = 0;

							/* preserve previous config, if of the right kind */
							if ($reset || !trim($elArray[$key]['tx_templavoila']['TypoScript'])) {
								$elArray[$key]['tx_templavoila']['TypoScript'] = '
	10 = TEXT
	10.current = 1
	10.parseFunc = < lib.parseFunc_RTE
					';			// Proper alignment (at least for the first level)
										}
							break;
						case 'image':
						case 'imagefixed':
							/* preserve previous config, if of the right kind */
							if (($reset = $reset || ($elArray[$key]['TCEforms']['config']['type'] != 'group'))) {
								$elArray[$key]['TCEforms']['label']=$elArray[$key]['tx_templavoila']['title'];
								$elArray[$key]['TCEforms']['config'] = array(
									'type' => 'group',
									'internal_type' => 'file',
									'allowed' => 'gif,png,jpg,jpeg',
									'max_size' => '1000',
									'uploadfolder' => 'uploads/tx_templavoila',
									'show_thumbs' => '1',
									'size' => '1',
									'maxitems' => '1',
									'minitems' => '0'
								);
							}

							$maxW = $contentInfo['img']['width'] ? $contentInfo['img']['width'] : 200;
							$maxH = $contentInfo['img']['height'] ? $contentInfo['img']['height'] : 150;
							$typoScriptImageObject = ($elArray[$key]['type'] == 'attr') ? 'IMG_RESOURCE' : 'IMAGE';

							/* preserve previous config, if of the right kind */
							if ($reset || !trim($elArray[$key]['tx_templavoila']['TypoScript'])) {
								if ($elArray[$key]['tx_templavoila']['eType'] == 'image') {
									$elArray[$key]['tx_templavoila']['TypoScript'] = '
	10 = ' . $typoScriptImageObject . '
	10.file.import = uploads/tx_templavoila/
	10.file.import.current = 1
	10.file.import.listNum = 0
	10.file.maxW = ' . $maxW . '
					';			// Proper alignment (at least for the first level)
								} else {
									$elArray[$key]['tx_templavoila']['TypoScript'] = '
	10 = ' . $typoScriptImageObject . '
	10.file.XY = ' . $maxW . ',' . $maxH . '
#	10.file.format = jpg
#	10.file.quality = 80
	10.file.import = uploads/tx_templavoila/
	10.file.import.current = 1
	10.file.import.listNum = 0
	10.file.maxW = ' . $maxW . '
	10.file.minW = ' . $maxW . '
	10.file.maxH = ' . $maxH . '
	10.file.minH = ' . $maxH . '
					';			// Proper alignment (at least for the first level)
								}
							}

							// Finding link-fields on same level and set the image to be linked by that TypoLink:
							$elArrayKeys = array_keys($elArray);
							foreach($elArrayKeys as $theKey) {
								if ($elArray[$theKey]['tx_templavoila']['eType'] == 'link') {
									$elArray[$key]['tx_templavoila']['TypoScript'] .= '
	10.stdWrap.typolink.parameter.field = ' . $theKey . '
					';			// Proper alignment (at least for the first level)
									break;
								}
							}
							break;
						case 'link':
							/* preserve previous config, if of the right kind */
							if (($reset = $reset || ($elArray[$key]['TCEforms']['config']['type'] != 'input'))) {
								$elArray[$key]['TCEforms']['label']=$elArray[$key]['tx_templavoila']['title'];
								$elArray[$key]['TCEforms']['config'] = array(
									'type' => 'input',
									'size' => '15',
									'max' => '256',
									'checkbox' => '',
									'eval' => 'trim',
									'wizards' => Array(
										'_PADDING' => 2,
										'link' => Array(
											'type' => 'popup',
											'title' => 'Link',
											'icon' => 'link_popup.gif',
											'script' => 'browse_links.php?mode=wizard',
											'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
										)
									)
								);
							}

							/* preserve previous config, if of the right kind */
							if ($reset || !trim($elArray[$key]['tx_templavoila']['TypoScript'])) {
								if ($elArray[$key]['type'] == 'attr') {
									$elArray[$key]['tx_templavoila']['TypoScript'] = '
	10 = TEXT
	10.typolink.parameter.current = 1
	10.typolink.returnLast = url
					';			// Proper alignment (at least for the first level)
									/* preserve previous config, if explicitly set */
									if (!isset($elArray[$key]['tx_templavoila']['proc']['HSC']) || $reset)
										$elArray[$key]['tx_templavoila']['proc']['HSC'] = 1;
								}
								else {
									$elArray[$key]['tx_templavoila']['TypoScript'] = '
	10 = TEXT
	10.typolink.parameter.current = 1
					';			// Proper alignment (at least for the first level)
								}
							}
							break;
						case 'ce':
							/* preserve previous config, if of the right kind */
							if (($reset = $reset || ($elArray[$key]['TCEforms']['config']['type'] != 'group'))) {
								$elArray[$key]['TCEforms']['label']=$elArray[$key]['tx_templavoila']['title'];
								$elArray[$key]['TCEforms']['config'] = array(
									'type' => 'group',
									'internal_type' => 'db',
									'allowed' => 'tt_content',
									'size' => '5',
									'maxitems' => '200',
									'minitems' => '0',
									'multiple' => '1',
									'show_thumbs' => '1'
								);
							}

							/* preserve previous config, if of the right kind */
							if ($reset || !trim($elArray[$key]['tx_templavoila']['TypoScript'])) {
								$elArray[$key]['tx_templavoila']['TypoScript'] = '
	10 = RECORDS
	10.source.current = 1
	10.tables = tt_content' . ($scope == TVDS_SCOPE_PAGE ? '
	10.wrap = <!--TYPO3SEARCH_begin--> | <!--TYPO3SEARCH_end-->' : '') . '
					';			// Proper alignment (at least for the first level)
										}

							$elArray[$key]['tx_templavoila']['oldStyleColumnNumber'] = $eTypeCECounter;
							$eTypeCECounter++;
							break;
						case 'int':
							/* preserve previous config, if of the right kind */
							if (($reset = $reset || ($elArray[$key]['TCEforms']['config']['type'] != 'input'))) {
								$elArray[$key]['TCEforms']['label']=$elArray[$key]['tx_templavoila']['title'];
								$elArray[$key]['TCEforms']['config'] = array(
									'type' => 'input',
									'size' => '4',
									'max' => '4',
									'eval' => 'int',
									'checkbox' => '0',
									'range' => Array (
										'upper' => '999',
										'lower' => '25'
									),
									'default' => 0
								);
							}
							break;
						case 'select':
							/* preserve previous config, if of the right kind */
							if (($reset = $reset || ($elArray[$key]['TCEforms']['config']['type'] != 'select'))) {
								$elArray[$key]['TCEforms']['label']=$elArray[$key]['tx_templavoila']['title'];
								$elArray[$key]['TCEforms']['config'] = array(
									'type' => 'select',
									'items' => Array (
										Array('', ''),
										Array('Value 1', 'Value 1'),
										Array('Value 2', 'Value 2'),
										Array('Value 3', 'Value 3'),
									),
									'default' => '0'
								);
							}
							break;
						case 'input':
						case 'input_h':
						case 'input_g':
							/* preserve previous config, if of the right kind */
							if (($reset = $reset || ($elArray[$key]['TCEforms']['config']['type'] != 'input'))) {
								$elArray[$key]['TCEforms']['label']=$elArray[$key]['tx_templavoila']['title'];
								$elArray[$key]['TCEforms']['config'] = array(
									'type' => 'input',
									'size' => '48',
									'eval' => 'trim',
								);
							}

							if ($elArray[$key]['tx_templavoila']['eType']=='input_h')	{	// Text-Header
									// Finding link-fields on same level and set the image to be linked by that TypoLink:
								$elArrayKeys = array_keys($elArray);
								foreach($elArrayKeys as $theKey)	{
									if ($elArray[$theKey]['tx_templavoila']['eType']=='link')	{
										$elArray[$key]['tx_templavoila']['TypoScript'] = '
	10 = TEXT
	10.current = 1
	10.typolink.parameter.field = '.$theKey.'
										';
									}
								}
							} elseif ($elArray[$key]['tx_templavoila']['eType']=='input_g')	{	// Graphical-Header

								$maxW = $contentInfo['img']['width'] ? $contentInfo['img']['width'] : 200;
								$maxH = $contentInfo['img']['height'] ? $contentInfo['img']['height'] : 20;

								$elArray[$key]['tx_templavoila']['TypoScript'] = '
	10 = IMAGE
	10.file = GIFBUILDER
	10.file {
	  XY = '.$maxW.','.$maxH.'
	  backColor = #999999

	  10 = TEXT
	  10.text.current = 1
	  10.text.case = upper
	  10.fontColor = #FFCC00
	  10.fontFile =  t3lib/fonts/vera.ttf
	  10.niceText = 0
	  10.offset = 0,14
	  10.fontSize = 14
	}
								';
							} else if (!isset($elArray[$key]['tx_templavoila']['proc']['HSC']) || $reset) {	// Normal output.
								$elArray[$key]['tx_templavoila']['proc']['HSC'] = 1;
							}

							if ($reset)
								unset($elArray[$key]['tx_templavoila']['TypoScript']);
							break;
						case 'TypoScriptObject':
							unset($elArray[$key]['tx_templavoila']['TypoScript_constants']);
							unset($elArray[$key]['tx_templavoila']['TypoScript']);

							unset($elArray[$key]['TCEforms']);

							/* preserve previous config, if of the right kind */
						//	if (($reset = $reset || ($elArray[$key]['tx_templavoila']['TypoScriptObjPath'] == ''))) {
								$elArray[$key]['tx_templavoila']['TypoScriptObjPath'] =
									($elArray[$key]['tx_templavoila']['eType_EXTRA']['objPath'] ?
										$elArray[$key]['tx_templavoila']['eType_EXTRA']['objPath'] :
										($elArray[$key]['tx_templavoila']['TypoScriptObjPath'] ?
											$elArray[$key]['tx_templavoila']['TypoScriptObjPath'] : ''));
						//	}
							break;
						case 'none':
							unset($elArray[$key]['TCEforms']['config']);
							break;
					}
				}	// End switch else

				if ($elArray[$key]['tx_templavoila']['eType'] != 'TypoScriptObject') {
					if (isset($elArray[$key]['tx_templavoila']['TypoScriptObjPath'])) {
						unset($elArray[$key]['tx_templavoila']['TypoScriptObjPath']);
					}
				}
				else if (isset($elArray[$key]['tx_templavoila']['eType_EXTRA']['objPath'])) {
					unset($elArray[$key]['tx_templavoila']['eType_EXTRA']['objPath']);
					if (count($elArray[$key]['tx_templavoila']['eType_EXTRA']) == 0) {
						unset($elArray[$key]['tx_templavoila']['eType_EXTRA']);
					}
				}

					// Setting TCEforms title for element if configuration is found:
				if (!is_array($elArray[$key]['TCEforms']['config'])) {
					unset($elArray[$key]['TCEforms']);
				}
					// empty label wouldn't be bad, but should be evaded
				if (!$elArray[$key]['TCEforms']['label']) {
					$elArray[$key]['TCEforms']['label'] = $elArray[$key]['tx_templavoila']['title'];
				}
			}

				// Apart from converting eType to configuration, we also clean up other aspects:
			if (!$elArray[$key]['type'])
				unset($elArray[$key]['type']);
			if (!$elArray[$key]['section'])
				unset($elArray[$key]['section']);
			else {
				unset($elArray[$key]['tx_templavoila']['TypoScript_constants']);
				unset($elArray[$key]['tx_templavoila']['TypoScript']);
				unset($elArray[$key]['tx_templavoila']['proc']);
				unset($elArray[$key]['TCEforms']);
			}

			$elArray[$key]['tx_templavoila'] = $this->array_clear_recursive($elArray[$key]['tx_templavoila']);
			if (!count($elArray[$key]['tx_templavoila'])) {
				unset($elArray[$key]['tx_templavoila']);
			}

			$elArray[$key]['TCEforms'] = $this->array_clear_recursive($elArray[$key]['TCEforms']);
			if (!count($elArray[$key]['TCEforms'])) {
				unset($elArray[$key]['TCEforms']);
			}

				// Run this function recursively if needed:
			if (is_array($elArray[$key]['el']))	{
				$this->substEtypeWithRealStuff($elArray[$key]['el'],$v_sub['sub'][$key],$scope);
			}
		}	// End loop
	}

	/**
	 * Analyzes the input content for various stuff which can be used to generate the DS.
	 * Basically this tries to intelligently guess some settings.
	 *
	 * @param	string		HTML Content string
	 * @return	array		Configuration
	 * @see substEtypeWithRealStuff()
	 */
	function substEtypeWithRealStuff_contentInfo($content)	{
		if ($content)	{
			if (substr($content,0,4)=='<img')	{
				$attrib = t3lib_div::get_tag_attributes($content);
				if ((!$attrib['width'] || !$attrib['height']) && $attrib['src'])	{
					$pathWithNoDots = t3lib_div::resolveBackPath($attrib['src']);
					$filePath = t3lib_div::getFileAbsFileName($pathWithNoDots);
					if ($filePath && @is_file($filePath))	{
						$imgInfo = @getimagesize($filePath);

						if (!$attrib['width'])	$attrib['width']=$imgInfo[0];
						if (!$attrib['height'])	$attrib['height']=$imgInfo[1];
					}
				}
				return array('img'=>$attrib);
			}
		}
		return false;
	}

	/*******************************
	 *
	 * Various helper functions
	 *
	 *******************************/

	/**
	 * Returns Data Structure from the $datString
	 *
	 * @param	string		XML content which is parsed into an array, which is returned.
	 * @param	string		Absolute filename from which to read the XML data. Will override any input in $datString
	 * @return	mixed		The variable $dataStruct. Should be array. If string, then no structures was found and the function returns the XML parser error.
	 */
	function getDataStructFromDSO($datString,$file='')	{
		if ($file)	{
			$dataStruct = t3lib_div::xml2array(t3lib_div::getUrl($file));
		} else {
			$dataStruct = t3lib_div::xml2array($datString);
		}
		return $dataStruct;
	}

	/**
	 * Creating a link to the display frame for display of the "HTML-path" given as $path
	 *
	 * @param	string		The text to link
	 * @param	string		The path string ("HTML-path")
	 * @return	string		HTML link, pointing to the display frame.
	 */
	function linkForDisplayOfPath($title,$path)	{
		$theArray=array(
			'file' => $this->markupFile,
			'path' => $path,
			'mode' => 'display'
		);
		$p = t3lib_div::implodeArrayForUrl('',$theArray);

		$content.='<strong><a href="'.htmlspecialchars($this->baseScript.$p).'" target="display">'.$title.'</a></strong>';
		return $content;
	}

	/**
	 * Creates a link to this script, maintaining the values of the displayFile, displayTable, displayUid variables.
	 * Primarily used by ->drawDataStructureMap
	 *
	 * @param	array		Overriding parameters.
	 * @see drawDataStructureMap()
	 */
	function linkThisScript($array = array()) {
		$theArray=array(
			'id' => $this->id,
			'file' => $this->displayFile,
			'table' => $this->displayTable,
			'uid' => $this->displayUid,
			'returnUrl' => $this->returnUrl
		);
		$p = t3lib_div::implodeArrayForUrl('', array_merge($theArray,$array), '', 1);

		return htmlspecialchars($this->baseScript . $p);
	}

	/**
	 * Creates the HTML code for the IFRAME in which the display mode is shown:
	 *
	 * @param	string		File name to display in exploded mode.
	 * @param	string		HTML-page
	 * @param	string		Tags which is the only ones to show
	 * @param	boolean		If set, the template is only shown, mapping links disabled.
	 * @param	boolean		Preview enabled.
	 * @return	string		HTML code for the IFRAME.
	 * @see main_display()
	 */
	function makeIframeForVisual($file,$path,$limitTags,$showOnly,$preview=0)	{
		$url = $this->baseScript . 'mode=display'.
				'&file='.rawurlencode($file).
				'&path='.rawurlencode($path).
				'&preview='.($preview?1:0).
				($showOnly?'&show=1':'&limitTags='.rawurlencode($limitTags));
		return '<iframe width="98%" height="500" src="'.htmlspecialchars($url).'#_MARKED_UP_ELEMENT" style="border: 1xpx solid black;"></iframe>';
	}

	/**
	 * Converts a list of mapping rules to an array
	 *
	 * @param	string		Mapping rules in a list
	 * @param	boolean		If set, then the ALL rule (key "*") will be unset.
	 * @return	array		Mapping rules in a multidimensional array.
	 */
	function explodeMappingToTagsStr($mappingToTags,$unsetAll=0)	{
		$elements = t3lib_div::trimExplode(',',strtolower($mappingToTags));
		$output=array();
		foreach($elements as $v)	{
			$subparts = t3lib_div::trimExplode(':',$v);
			$output[$subparts[0]][$subparts[1]][($subparts[2]?$subparts[2]:'*')]=1;
		}
		if ($unsetAll)	unset($output['*']);
		return $output;
	}

	/**
	 * General purpose unsetting of elements in a multidimensional array
	 *
	 * @param	array		Array from which to remove elements (passed by reference!)
	 * @param	array		An array where the values in the specified order points to the position in the array to unset.
	 * @return	void
	 */
	function unsetArrayPath(&$dataStruct, $ref) {
		$key = array_shift($ref);

		if (!count($ref)) {
			unset($dataStruct[$key]);
		}
		else if (is_array($dataStruct[$key]))	{
			$this->unsetArrayPath($dataStruct[$key], $ref);
		}
	}

	/**
	 * General purpose unsetting of elements in a multidimensional array
	 *
	 * @param	array		Array in which to move elements (passed by reference!)
	 * @param	array		An array where the values in the specified order points to the position in the array to move.
	 * @return	void
	 */
	function upArrayPath(&$dataStruct, $ref) {
		$key = array_shift($ref);

		if (!count($ref)) {
			$ds = array();
			$ky = null;
			$dl = null;

			foreach ($dataStruct as $k => $e) {
				if ($k == $key) {
					$ds[$k] = $e;
				}
				else {
					if ($dl) {
						$ds[$ky] = $dl;
					}
					$ky = $k;
					$dl = $e;
				}
			}

			if ($dl) {
				$ds[$ky] = $dl;
			}

			$dataStruct = $ds;
		}
		else if (is_array($dataStruct[$key])) {
			$this->upArrayPath($dataStruct[$key], $ref);
		}
	}

	/**
	 * General purpose unsetting of elements in a multidimensional array
	 *
	 * @param	array		Array in which to move elements (passed by reference!)
	 * @param	array		An array where the values in the specified order points to the position in the array to move.
	 * @return	void
	 */
	function downArrayPath(&$dataStruct, $ref) {
		$key = array_shift($ref);

		if (!count($ref)) {
			$ds = array();
			$ky = null;
			$dl = null;

			foreach ($dataStruct as $k => $e) {
				if ($k == $key) {
					$ky = $k;
					$dl = $e;
				}
				else {
					$ds[$k] = $e;
					if ($dl) {
						$ds[$ky] = $dl;

						$ky = null;
						$dl = null;
					}
				}
			}

			if ($dl) {
				$ds[$ky] = $dl;
			}

			$dataStruct = $ds;
		}
		else if (is_array($dataStruct[$key])) {
			$this->downArrayPath($dataStruct[$key], $ref);
		}
	}

	/**
	 * Function to clean up "old" stuff in the currentMappingInfo array. Basically it will remove EVERYTHING which is not known according to the input Data Structure
	 *
	 * @param	array		Current Mapping info (passed by reference)
	 * @param	array		Data Structure
	 * @return	void
	 */
	function cleanUpMappingInfoAccordingToDS(&$currentMappingInfo, $dataStruct) {
		if (is_array($currentMappingInfo))	{
			foreach($currentMappingInfo as $key => $value)	{
				if (!isset($dataStruct[$key]))	{
					unset($currentMappingInfo[$key]);
				} else {
					if (is_array($dataStruct[$key]['el']))	{
						$this->cleanUpMappingInfoAccordingToDS($currentMappingInfo[$key]['el'],$dataStruct[$key]['el']);
					}
				}
			}
		}
	}

	/**
	 * Generates $this->storageFolders with available sysFolders linked to as storageFolders for the user
	 *
	 * @return	void		Modification in $this->storageFolders array
	 */
	function findingStorageFolderIds() {
		global $TYPO3_DB;

			// Init:
		$readPerms = $GLOBALS['BE_USER']->getPagePermsClause(1);
		$this->storageFolders=array();

			// Looking up all references to a storage folder:
		$res = $TYPO3_DB->exec_SELECTquery (
			'uid,storage_pid',
			'pages',
			'storage_pid>0'.t3lib_BEfunc::deleteClause('pages')
		);
		while(false !== ($row = $TYPO3_DB->sql_fetch_assoc($res)))	{
			if ($GLOBALS['BE_USER']->isInWebMount($row['storage_pid'],$readPerms))	{
				$storageFolder = t3lib_BEfunc::getRecord('pages',$row['storage_pid'],'uid,title');
				if ($storageFolder['uid'])	{
					$this->storageFolders[$storageFolder['uid']] = $storageFolder['title'];
				}
			}
		}

			// Compopsing select list:
		$sysFolderPIDs = array_keys($this->storageFolders);
		$sysFolderPIDs[]=0;
		$this->storageFolders_pidList = implode(',',$sysFolderPIDs);
	}

	/*****************************************
	 *
	 * BROWSER mode
	 *
	 *****************************************/

	/**
	 * Outputs a browser in the IFRAME
	 *
	 * @return	void		Exits before return
	 * @see makeIframeForVisual()
	 */
	function main_browser()	{

			// Output content:
		echo $this->render_browser();

			// Exit since a full page has been outputted now.
		exit;
	}

	function render_browser() {
		echo '<html><head><style type="text/css">
			html, body {
				font-size: 12px;
				padding: 0;
				margin: 0;
			}

			ul, li {
				padding: 0;
				margin: 0;

				list-style: none;
				line-height: 16px;
			}

			li {
				padding-left: 20px;
			}

			ul.tree {
				padding-left: 2px; }
			ul.tree li {
				background: 0 0 url(/typo3/gfx/ol/join.gif) no-repeat transparent;  }
			ul.tree li > span {
				cursor: pointer; }
			ul.tree li > strong {
				cursor: pointer; }
			ul.tree li.last {
				background: 0 0 url(/typo3/gfx/ol/joinbottom.gif) no-repeat transparent; }
			ul.tree li.act > strong {
				font-style: italic; }
			ul.tree li.act > span {
				font-style: italic; }
			ul.tree li.pm > strong {
				margin-left: -20px;
				padding-left: 20px; }
			ul.tree li.pm > ul {
				margin-left: -20px;
				padding-left: 20px;
				background: 0 0 url(/typo3/gfx/ol/line.gif) repeat-y transparent; }
			ul.tree li.last.pm > ul {
				background: none; }
			ul.tree li.minus {
				background: 0 0 url(/typo3/gfx/ol/minus.gif) no-repeat transparent; }
			ul.tree li.plus {
				background: 0 0 url(/typo3/gfx/ol/plus.gif) no-repeat transparent; }
			ul.tree li.minus.last {
				background: 0 0 url(/typo3/gfx/ol/minusbottom.gif) no-repeat transparent; }
			ul.tree li.plus.last {
				background: 0 0 url(/typo3/gfx/ol/plusbottom.gif) no-repeat transparent; }
			ul.tree li.minus > ul {
				display: block; }
			ul.tree li.plus > ul {
				display: none; }


		</style><script type="text/javascript">
			function flip(li) {
				if (li.className.match(/plus/)) {
					li.className =
					li.className.replace(/plus/, \'minus\');
				}
				else {
					li.className =
					li.className.replace(/minus/, \'plus\');
				}

				parent.setSizeBrowseFrame(document.body.firstChild.scrollHeight);
			}

			function touch(loc) {
				var old = document.getElementById(\'selected\');
				var str = \'\';

				if (old) {
					old.id = \'\';
					while (old) {
						if (old.className) {
							old.className =
							old.className.replace(/act/, \'\');
						}

						old = old.parentNode;
					}
				}

				if (loc) {
					loc.parentNode.id = \'selected\';
					while (loc) {
						if (loc.hasAttribute)
						if (loc.hasAttribute(\'name\'))
							str = loc.getAttribute(\'name\') + str;

						if (loc.tagName)
						if (loc.tagName.toLowerCase() == \'li\')
							loc.className += \' act\';

						loc = loc.parentNode;
					}
				}

				parent.setFormValueFromBrowseFrame(str);
			}
		</script></head><body>';

		// Get TypoScript template for current page
		$conf = $this->getConfigArray();

		// Show TS template hierarchy
		echo $this->showTemplate($conf);

		echo '<script type="text/javascript">
			parent.setSizeBrowseFrame(document.body.firstChild.scrollHeight);
		</script>';

		echo '</body></html>';
		exit;
	}

	// Available content objets
	var $cTypes = array(
		'HTML',
		'TEXT',
		'COBJ_ARRAY',
		'COA',
		'COA_INT',
		'FILE',
		'IMAGE',
		'IMG_RESOURCE',
		'CLEARGIF',
		'CONTENT',
		'RECORDS',
		'HMENU',
		'CTABLE',
		'OTABLE',
		'COLUMNS',
		'HRULER',
		'IMGTEXT',
		'CASE',
		'LOAD_REGISTER',
		'RESTORE_REGISTER',
		'FORM',
		'SEARCHRESULT',
		'USER',
		'USER_INT',
		'PHP_SCRIPT',
		'PHP_SCRIPT_INT',
		'PHP_SCRIPT_EXT',
		'TEMPLATE',
		'MULTIMEDIA',
		'EDITPANEL',
	);

	/**
	 * Get TS template
	 *
	 * This function creates instances of the class needed to render
	 * the TS template, and gets it as a multi-dimensionnal array.
	 *
	 * @return		An array containing all the available TS objects
	 */
	function getConfigArray() {

		// Initialize the page selector
		$this->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$this->sys_page->init(true);

		// initialize the TS template
		$this->tmpl = t3lib_div::makeInstance('t3lib_TStemplate');
		$this->tmpl->init();

		// Avoid an error
		$this->tmpl->tt_track = 0;

		// Get rootline for current PID
		$rootline = $this->sys_page->getRootLine(t3lib_div::GPvar('pid'));

		// Start TS template
		$this->tmpl->start($rootline);

		//Return configuration array
		return $this->tmpl->setup;
	}

	/**
	 * Show TS template hierarchy
	 *
	 * This function displays the TS template hierarchy as HTML list
	 * elements. Each section can be expanded/collapsed.
	 *
	 * @param		$object		A section of the TS template
	 * @param		$object		The path to the current object
	 * @return
	 */
	function showTemplate($conf, $pObj = false) {

		// Storage
		$htmlCode = array();
		$curr = t3lib_div::GPvar('current');

		// Process each object of the configuration array
		foreach($conf as $key => $value) {

			// TS object ID
			$id = $pObj . $key;
			$sel = (strpos($curr, $id) === 0);
			$mtc = ($curr == $id);

			// Check if object is a container
			if (is_array($value)) {

				// Check if object has a content type
				if (substr($key, 0, strlen($key) - 1) != $lastKey) {

					// No content type - Process sub configuration
					$subArray = $this->showTemplate($value, $id);

					// Check if objects are available
					if ($subArray) {

						// Add container
						$htmlCode[] = '<li class="pm ' . ($sel ? 'minus act' : 'plus') . '" ' . ($mtc ? 'id="selected"' : '') . ' name="' . $key . '"><strong onclick="flip(this.parentNode);">' . $key . '</strong>' . $subArray . '</li>';
					}
				}
			}
			else if (in_array($value, $this->cTypes)) {

				// Memorize key
				$lastKey = $key;

				// TS object
				$htmlCode[] = '<li class="' . ($sel ? 'act' : '') . '" ' . ($mtc ? 'id="selected"' : '') . ' name="' . $key . '"><span onclick="touch(this);">' . $key . '</span> <em>['. $value . ']</em></li>';
			}
		}

		// Check if objects have been detected
		if (count($htmlCode)) {
		//	array_push($htmlCode,  str_replace(  '<li class="' , '<li class="last ', array_pop($htmlCode)));
			array_push($htmlCode, preg_replace('/^<li class="/', '<li class="last ', array_pop($htmlCode)));

				// Return hierarchy
			return
			'<ul class="' . (!$pObj ? 'tree' : '') . '">' .
			implode(chr(10),$htmlCode) .
			'</ul>';
		}
	}

	/*****************************************
	 *
	 * DISPLAY mode
	 *
	 *****************************************/

	/**
	 * Outputs the display of a marked-up HTML file in the IFRAME
	 *
	 * @return	void		Exits before return
	 * @see makeIframeForVisual()
	 */
	function main_display()	{

			// Output content:
		echo $this->render_display();

			// Exit since a full page has been outputted now.
		exit;
	}

	function render_display() {

			// Setting GPvars:
		$this->displayFile = t3lib_div::GPvar('file');
		$this->show = t3lib_div::GPvar('show');
		$this->preview = t3lib_div::GPvar('preview');
		$this->limitTags = t3lib_div::GPvar('limitTags');
		$this->path = t3lib_div::GPvar('path');

			// Checking if the displayFile parameter is set:
		if (@is_file($this->displayFile) && t3lib_div::getFileAbsFileName($this->displayFile))		{	// FUTURE: grabbing URLS?: 		.... || substr($this->displayFile,0,7)=='http://'
			$content = t3lib_div::getUrl($this->displayFile);
			if ($content)	{
				$relPathFix = $GLOBALS['BACK_PATH'].'../'.dirname(substr($this->displayFile,strlen(PATH_site))).'/';

				if ($this->preview)	{	// In preview mode, merge preview data into the template:
						// Add preview data to file:
					$content = $this->displayFileContentWithPreview($content,$relPathFix);
				} else {
						// Markup file:
					$content = $this->displayFileContentWithMarkup($content,$this->path,$relPathFix,$this->limitTags);
				}

				echo $content;
			} else {
				$this->displayFrameError('No content found in file reference: <em>'.htmlspecialchars($this->displayFile).'</em>');
			}
		} else {
			$this->displayFrameError('No file to display');
		}

		exit;
	}

	/**
	 * This will mark up the part of the HTML file which is pointed to by $path
	 *
	 * @param	string		The file content as a string
	 * @param	string		The "HTML-path" to split by
	 * @param	string		The rel-path string to fix images/links with.
	 * @param	string		List of tags to show
	 * @return	void		Exits...
	 * @see main_display()
	 */
	function displayFileContentWithMarkup($content,$path,$relPathFix,$limitTags)	{
		$markupObj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');
		$markupObj->gnyfImgAdd = $this->show ? '' : 'onclick="return parent.updPath(\'###PATH###\');"';
		$markupObj->pathPrefix = $path?$path.'|':'';
		$markupObj->onlyElements = $limitTags;

#		$markupObj->setTagsFromXML($content);

		$cParts = $markupObj->splitByPath($content,$path);
		if (is_array($cParts))	{
			$cParts[1] = $markupObj->markupHTMLcontent(
							$cParts[1],
							$GLOBALS['BACK_PATH'],
							$relPathFix,
							implode(',',array_keys($markupObj->tags)),
							$this->MOD_SETTINGS['displayMode']
						);
			$cParts[0] = $markupObj->passthroughHTMLcontent($cParts[0],$relPathFix,$this->MOD_SETTINGS['displayMode']);
			$cParts[2] = $markupObj->passthroughHTMLcontent($cParts[2],$relPathFix,$this->MOD_SETTINGS['displayMode']);
			if (trim($cParts[0]))	{
				$cParts[1]='<a name="_MARKED_UP_ELEMENT"></a>'.$cParts[1];
			}
			return implode('',$cParts);
		}
		$this->displayFrameError($cParts);
		return '';
	}

	/**
	 * This will add preview data to the HTML file used as a template according to the currentMappingInfo
	 *
	 * @param	string		The file content as a string
	 * @param	string		The rel-path string to fix images/links with.
	 * @return	void		Exits...
	 * @see main_display()
	 */
	function displayFileContentWithPreview($content,$relPathFix)	{

			// Getting session data to get currentMapping info:
		$sesDat = $GLOBALS['BE_USER']->getSessionData($this->MCONF['name'].'_mappingInfo');
		$currentMappingInfo = is_array($sesDat['currentMappingInfo']) ? $sesDat['currentMappingInfo'] : array();

			// Init mark up object.
		$this->markupObj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');
		$this->markupObj->htmlParse = t3lib_div::makeInstance('t3lib_parsehtml');

			// Splitting content, adding a random token for the part to be previewed:
		$contentSplittedByMapping = $this->markupObj->splitContentToMappingInfo($content,$currentMappingInfo);
		$token = md5(microtime());
		$content = $this->markupObj->mergeSampleDataIntoTemplateStructure($sesDat['dataStruct'],$contentSplittedByMapping,$token);

			// Exploding by that token and traverse content:
		$pp = explode($token,$content);
		foreach($pp as $kk => $vv)	{
			$pp[$kk] = $this->markupObj->passthroughHTMLcontent($vv,$relPathFix,$this->MOD_SETTINGS['displayMode'],$kk==1?'font-size:11px; color:#000066;':'');
		}

			// Adding a anchor point (will work in most cases unless put into a table/tr tag etc).
		if (trim($pp[0]))	{
			$pp[1]='<a name="_MARKED_UP_ELEMENT"></a>'.$pp[1];
		}

			// Implode content and return it:
		return implode('',$pp);
	}

	/**
	 * Outputs a simple HTML page with an error message
	 *
	 * @param	string		Error message for output in <h2> tags
	 * @return	void		Echos out an HTML page.
	 */
	function displayFrameError($error)	{
			echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Untitled</title>
</head>

<body bgcolor="#eeeeee">
<h2>ERROR: '.$error.'</h2>
</body>
</html>
			';
	}

	/**
	 * Wrapper function for context sensitive help - for downwards compatibility with TYPO3 prior 3.7.x
	 *
	 * @param	string		Table name ('_MOD_'+module name)
	 * @param	string		Field name (CSH locallang main key)
	 * @param	string		Back path
	 * @param	string		Wrap code for icon-mode, splitted by "|". Not used for full-text mode.
	 * @param	boolean		If set, the full text will never be shown (only icon). Useful for places where it will break the page if the table with full text is shown.
	 * @param	string		Additional style-attribute content for wrapping table (full text mode only)
	 * @return	string		HTML content for help text
	 */
	function cshItem($table,$field,$BACK_PATH,$wrap='',$onlyIconMode=FALSE, $styleAttrib='')	{
		if (is_callable (array ('t3lib_BEfunc','cshItem'))) {
			return t3lib_BEfunc::cshItem ($table,$field,$BACK_PATH,$wrap,$onlyIconMode, $styleAttrib);
		}
		return '';
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$formElementName: ...
	 * @return	[type]		...
	 */
	function lipsumLink($formElementName)	{
		if (t3lib_extMgm::isLoaded('lorem_ipsum'))	{
			$LRobj = t3lib_div::makeInstance('tx_loremipsum_wiz');
			$LRobj->backPath = $this->doc->backPath;

			$PA = array(
				'fieldChangeFunc' => array(),
				'formName' => 'pageform',
				'itemName' => $formElementName.'[]',
				'params' => array(
#					'type' => 'header',
					'type' => 'description',
					'add' => 1,
					'endSequence' => '46,32',
				)
			);

			return $LRobj->main($PA,'ID:templavoila');
		}
		return '';
	}

	function buildCachedMappingInfo_head($currentMappingInfo_head, $html_header) {
		$h_currentMappingInfo=array();
		if (is_array($currentMappingInfo_head['headElementPaths']))	{
			foreach($currentMappingInfo_head['headElementPaths'] as $kk => $vv)	{
				$h_currentMappingInfo['el_'.$kk]['MAP_EL'] = $vv;
			}
		}

		return $this->markupObj->splitContentToMappingInfo($html_header,$h_currentMappingInfo);
	}

	/**
	 * Checks if link points to local marker or not and sets prefix accordingly.
	 *
	 * @param	string	$relPathFix	Prefix
	 * @param	string	$fileContent	Content
	 * @param	string	$uniqueMarker	Marker inside links
	 * @return	string	Content
	 */
	function fixPrefixForLinks($relPathFix, $fileContent, $uniqueMarker) {
		$parts = explode($uniqueMarker, $fileContent);
		$count = count($parts);
		if ($count > 1) {
			for ($i = 1; $i < $count; $i++) {
				if ($parts[$i]{0} != '#') {
					$parts[$i] = $relPathFix . $parts[$i];
				}
			}
		}
		return implode($parts);
	}

}

if (!function_exists('md5_file')) {
	function md5_file($file, $raw = false) {
		return md5(file_get_contents($file), $raw);
	}
}

// Make instance:
//$SOBE = t3lib_div::makeInstance('tx_templavoila_cm1');
//$SOBE->init();
//$SOBE->main();
//$SOBE->printContent();

/**
 * Class for controlling the TemplaVoila module.
 * Modern integral style
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @co-author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage tx_templavoila
 */
class tx_templavoila_cm1_integral extends tx_templavoila_cm1 {

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
		global $BE_USER;

		parent::menuConfig();

			// Sticky Information?
		$this->MOD_MENU['stick'] = '';

			// Render content, depending on input values:
		if (t3lib_div::GPvar('mode') == 'display' ||
			t3lib_div::GPvar('mode') == 'browser') {
				// Data source display
			$this->MOD_MENU['page'] =
				array(
				/*	'details'   => '',	*/
					'overview'  => '',
					'structure' => '',
					'xml'       => '',
					'header'    => '',
					'mapping'   => '',
					'preview'   => ''
				);
		} elseif (t3lib_div::GPvar('file')) {
				// Browsing file directly, possibly creating a template/data object records.
			$this->MOD_MENU['page'] =
				array(
				/*	'details'   => 'Information',	*/
					'structure' => 'Structure',
					'preview'   => 'Preview',
					'xml'       => 'XML'
				);
		} elseif (t3lib_div::GPvar('table') == 'tx_templavoila_datastructure') {
				// Data source display
			$this->MOD_MENU['page'] =
				array(
				/*	'details'   => 'Information',	*/
					'overview'  => 'Information',
					'xml'       => 'XML'
				);
		} elseif (t3lib_div::GPvar('table') == 'tx_templavoila_tmplobj') {
				// Data source display
			$this->MOD_MENU['page'] =
				array(
				/*	'details'   => 'Information',	*/
					'header'    => 'Map head-elements',
					'mapping'   => 'Map body-elements',
					'preview'   => 'Preview current mapping'
				);

			if (!$BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
				unset($this->MOD_MENU['page']['header']);
				unset($this->MOD_MENU['page']['mapping']);
			}
		}

			// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id, 'mod.' . $this->MCONF['name']);

			// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::GPvar('SET'), $this->MCONF['name']);

			// Fallback on multi-pages
		if (t3lib_div::GPvar('_loadScreen'))
			$this->MOD_SETTINGS['page'] = 'structure';
		if (t3lib_div::GPvar('_saveScreen'))
			$this->MOD_SETTINGS['page'] = 'structure';
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
			$script = $this->linkThisScript(array());

			$options = array();
			foreach($menuItems as $value => $label) {
				$options[] = str_replace('><span ', '',
						 str_replace('</span>', '',
						 '<option value="'.htmlspecialchars($value).'"'.(!strcmp($currentValue, $value)?' selected="selected"':'').'>'.
								/*t3lib_div::deHSCentities(htmlspecialchars(*/$label/*))*/.
								'</option>'));
			}
			if (count($options)) {
				$onChange = 'jumpToUrl(\'' . $script . '&' . $elementName . '=\'+this.options[this.selectedIndex].value,this);';
				return '

					<!-- Function Menu of module -->
					<select name="'.$elementName.'" onchange="'./*htmlspecialchars(*/$onChange/*)*/.'">
						'.implode('
						',$options).'
					</select>
							';
			}
		}
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
				// calls from sticky
			if (t3lib_div::_GP("ajaxStick")) {
				$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData(array('stick'=>''), t3lib_div::GPvar('SET'), $this->MCONF['name']);
				exit;
			}

			$this->CALC_PERMS = $BE_USER->calcPerms($this->pageinfo);
			if ($BE_USER->user['admin'] && !$this->id)	{
				$this->pageinfo = array('title' => '[root-level]','uid'=>0,'pid'=>0);
			}

			$this->doc = t3lib_div::makeInstance('template');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->setModuleTemplate('templates/mapper.html');
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
				body#ext-templavoila-cm1-index-php {
					height: 100%;
					margin: 0pt;
					overflow: hidden;
					padding: 0pt;
				}
			';

				// Add xmlarea
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath($this->extKey)."res/getElementsByClassName.js");
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath($this->extKey)."res/clearmappingmenu.js");
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath($this->extKey)."res/togglesticky.js");
			$this->doc->loadJavascriptLib(t3lib_extMgm::extRelPath($this->extKey)."res/xmlarea.js");

				// Add custom styles
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey)."cm1/styles.css";

				// JavaScript
			$this->doc->JScode = $this->doc->wrapScriptTags('
				script_ended = 0;
				function jumpToUrl(URL)	{	//
					window.location.href = URL;
				}
				function updPath(inPath)	{	//
					window.location.href = "'.t3lib_div::linkThisScript(array('htmlPath'=>'','doMappingOfPath'=>1)).'&htmlPath="+top.rawurlencode(inPath);
				}
			');
			$this->doc->postCode = $this->doc->wrapScriptTags('
				script_ended = 1;
				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
			');

				// Setting up the context sensitive menu:
		//	$this->doc->getContextMenuCode();
		//	$this->doc->form = '<form id="tv-form" action="index.php" method="post" name="webtvForm">';

				// Setting up support for context menus (when clicking the items icon)
			$CMparts = $this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
		//	$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->JScode .= $CMparts[0];
			$this->doc->postCode .= $CMparts[2];
			$this->doc->form='<form id="tv-form" action="'.$this->linkThisScript(array()).'" method="post" name="pageform">';

			$vContent = $this->doc->getVersionSelector($this->id,1);
			if ($vContent)	{
				$this->content.=$this->doc->section('',$vContent);
			}

			$this->extObjContent();

				// Info Module CSH:
			$this->content .= t3lib_BEfunc::cshItem('_MOD_web_tv_mapper', '', $GLOBALS['BACK_PATH'], '<br/>|', FALSE, 'margin-top: 30px;');
		//	$this->content .= $this->doc->spacer(10);

				// Setting GPvars:
			$this->mode = t3lib_div::GPvar('mode');

				// Selecting display or module mode:
			switch((string)$this->mode) {
				case 'display':
					$this->render_display();
					break;
				case 'browser':
					$this->render_browser();
					break;
				default:
					$this->render_mode(true);
					break;
			}

				// Setting up the buttons and markers for docheader
			$docHeaderButtons = $this->getButtons();
			$markers = array(
				'CSH'          => $docHeaderButtons['csh'],
				'FUNC_MENU'    =>
					$this->getFuncMenuNoHSC($this->id, 'SET[page]', $this->MOD_SETTINGS['page'], $this->MOD_MENU['page'],'',t3lib_div::implodeArrayForUrl('',$_GET,'',1,1)) .
					$this->parts['modify'],
				'CONTENT'      => $this->content,

				'STICKY'       => $this->parts['details']['content'],
				'STICKY_CLASS' => $this->MOD_SETTINGS['stick'] ? 'expulsed' : 'impulsed',

				'FILEPATH'     => $this->displayFile                                    ? $this->getFilePath()              : '',
				'FILEINFO'     => $this->displayFile                                    ? $this->getFileInfo()              : '',

				'DSPATH'       => $this->displayTable == 'tx_templavoila_datastructure' ? $this->getDSPath($this->pageinfo) : '',
				'DSINFO'       => $this->displayTable == 'tx_templavoila_datastructure' ? $this->getDSInfo($this->pageinfo) : '',

				'TOPATH'       => $this->displayTable == 'tx_templavoila_tmplobj'       ? $this->getTOPath($this->pageinfo) : '',
				'TOINFO'       => $this->displayTable == 'tx_templavoila_tmplobj'       ? $this->getTOInfo($this->pageinfo) : ''
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
			'save' => '',
			'saveas' => '',
			'savepreview' => '',
			'savereturn' => '',
			'load' => '',
			'save' => '',
			'undo' => '',
			'clear' => '',
			'view' => '',
			'related_ds' => '',
			'related_to' => '',
			'related_tf' => '',
			'record_list' => '',
			'shortcut' => '',
		);

			// If access to File>List for user, then link to that module.
		if ($this->displayFile && $BE_USER->check('modules','file_list') && $this->markupFile) {
			$href = $BACK_PATH . 'file_list.php?id=' . dirname($this->markupFile) . '&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
			$buttons['record_list'] .= '<a href="' . htmlspecialchars($href) . '">' .
					'<img src="' . t3lib_iconWorks::skinImg($BACK_PATH, 'MOD:file_list/list.gif', 'width="16" height="16"', 1) . '" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showList', 1) . '" alt="" />' .
					'</a>';
		}

			// If access to Web>List for user, then link to that module.
		if (!$this->displayFile && $BE_USER->check('modules','web_list') && $this->pageinfo['uid']) {
			$href = $BACK_PATH . 'db_list.php?id=' . $this->pageinfo['uid'] . '&returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));
			$buttons['record_list'] .= '<a href="' . htmlspecialchars($href) . '">' .
					'<img src="' . t3lib_iconWorks::skinImg($BACK_PATH, 'MOD:web_list/list.gif', 'width="16" height="16"', 1) . '" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showList', 1) . '" alt="" />' .
					'</a>';
		}

			// Back (first global, then local, then specialized
		if ($href) {
			$buttons['back'] = '<a href="' . htmlspecialchars($href) . '">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/closedok.gif') . ' class="c-inputButton" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.goBack', 1) . '" alt="" />' .
				'</a>';
		}
		if ($this->id) {
			$buttons['back'] = '<a href="' . htmlspecialchars($this->mod1Script.'id='.$this->id) . '">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/closedok.gif') . ' class="c-inputButton" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.goBack', 1) . '" alt="" />' .
				'</a>';
		}
		if ($this->R_URI) {
			$buttons['back'] = '<a href="' . htmlspecialchars($this->R_URI) . '">' .
				'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/closedok.gif') . ' class="c-inputButton" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.goBack', 1) . '" alt="" />' .
				'</a>';
		}

			// Render content, depending on input values:
		if ($this->displayFile)	{	// Browsing file directly, possibly creating a template/data object records.
				// CSH
			$buttons['csh'] = t3lib_BEfunc::cshItem('xMOD_tx_templavoila','mapping_file', $GLOBALS['BACK_PATH']);

				// Save all
			if ($this->databaseDSTO) {
			$buttons['save'] = '<input type="image" name="_save_dsto"' .
					' ' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/savedok.gif') . ' class="c-inputButton" title="Saving all data into the original Data Structure and Template Object" alt="Save"' .
					' />';
			$buttons['savepreview'] = '<input type="image" name="_save_dsto_preview"' .
					' ' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/savedokshow.gif') . ' class="c-inputButton" title="Saving all data into the original Data Structure and Template Object and preview" alt="Save and Preview"' .
					' />';
			$buttons['savereturn'] = '<input type="image" name="_save_dsto_return"' .
					' ' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/saveandclosedok.gif') . ' class="c-inputButton" title="Saving all data into the original Data Structure and Template Object and return" alt="Save and Return"' .
					' />';
			}

			$buttons['saveas'] = '<input type="image" name="_saveScreen"' .
					' ' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/savedoknew.gif') . ' class="c-inputButton" title="Go to save menu" alt="Save As"' .
					' />';

				// Load page
			$buttons['load'] = '<a href="' . $this->linkThisScript(array('_loadScreen' => '1', 'SET[page]' => 'structure')) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/insert3.gif') . ' class="c-inputButton" title="Go to load menu" alt="Load" />' .
					'</a>';

				// Undo all
			if ($this->changedDS)
			$buttons['undo'] = '<a href="' . $this->linkThisScript(array('_reload_from' => '1', 'SET[page]' => 'structure')) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/undo.gif') . ' class="c-inputButton" title="Reverting structure data to last imported data" alt="Revert all" />' .
					'</a>';

				// Clear all
			$buttons['clear'] = '<div id="clear-mapping-actions-menu">';
			$buttons['clear'] .= '<a href="#" class="toolbar-item">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/lightning.png') . ' class="c-inputButton" title="Clear all Mapping information" alt="Clear all" />' .
					'</a>';
			$buttons['clear'] .= '<ul class="toolbar-item-menu" style="display: none;">';
			$buttons['clear'] .= '<li><a href="' . $this->linkThisScript(array('_clear' => TVDS_CLEAR_MAPPING, 'SET[page]' => $this->MOD_SETTINGS['page'])) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/lightning.png') . ' alt="Clear all mapping" /> Clear all mappings' .
					'</a></li>';
			$buttons['clear'] .= '<li><a href="' . $this->linkThisScript(array('_clear' => TVDS_CLEAR_ALL, 'SET[page]' => $this->MOD_SETTINGS['page'])) . '">' .
					'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/lightning_red.png') . ' alt="Clear all" /> Clear all (incl. DS)' .
					'</a></li>';
			$buttons['clear'] .= '</ul>';
			$buttons['clear'] .= '</div>';

		} elseif ($this->displayTable=='tx_templavoila_datastructure') {	// Data source display
				// CSH
			$buttons['csh'] = t3lib_BEfunc::cshItem('xMOD_tx_templavoila','mapping_structure', $GLOBALS['BACK_PATH']);

			if ($BE_USER->check('tables_modify', 'tx_templavoila_datastructure')) {
			}

		} elseif ($this->displayTable=='tx_templavoila_tmplobj') {	// Data source display
				// CSH
			$buttons['csh'] = t3lib_BEfunc::cshItem('xMOD_tx_templavoila','mapping_basics', $GLOBALS['BACK_PATH']);

			if ($BE_USER->check('tables_modify', 'tx_templavoila_tmplobj')) {
					// Save all
				$buttons['save'] = '<input type="image" name="_save_to"' .
						' ' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/savedok.gif') . ' class="c-inputButton" title="Saving all mapping data into the Template Object" alt="Save"' .
						' />';
				$buttons['savepreview'] = '<input type="image" name="_save_to_preview"' .
						' ' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/savedokshow.gif') . ' class="c-inputButton" title="Saving all mapping data into the Template Object and preview" alt="Save and Preview"' .
						' />';
				$buttons['savereturn'] = '<input type="image" name="_save_to_return"' .
						' ' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/saveandclosedok.gif') . ' class="c-inputButton" title="Saving all mapping data into the Template Object and return" alt="Save and Return"' .
						' />';

					// Undo all
				if ($this->changedTO)
				$buttons['undo'] = '<a href="' . $this->linkThisScript(array('_reload_from' => '1', 'SET[page]' => $this->MOD_SETTINGS['page'])) . '">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/undo.gif') . ' class="c-inputButton" title="Reverting mapping data to original data in the Template Object" alt="Revert all" />' .
						'</a>';

					// Clear all
				$buttons['clear'] = '<div id="clear-mapping-actions-menu">';
				$buttons['clear'] .= '<a href="#" class="toolbar-item">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/lightning.png') . ' class="c-inputButton" title="Clear all Mapping information" alt="Clear all" />' .
						'</a>';
				$buttons['clear'] .= '<ul class="toolbar-item-menu" style="display: none;">';
				$buttons['clear'] .= '<li><a href="' . $this->linkThisScript(array('_clear' => TVTO_CLEAR_HEAD, 'SET[page]' => $this->MOD_SETTINGS['page'])) . '">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/lightning_green.png') . ' alt="Clear all head" /> Clear all head mapping' .
						'</a></li>';
				$buttons['clear'] .= '<li><a href="' . $this->linkThisScript(array('_clear' => TVTO_CLEAR_BODY, 'SET[page]' => $this->MOD_SETTINGS['page'])) . '">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/lightning.png') . ' alt="Clear all body" /> Clear all body mapping' .
						'</a></li>';
				$buttons['clear'] .= '<li><a href="' . $this->linkThisScript(array('_clear' => TVTO_CLEAR_ALL, 'SET[page]' => $this->MOD_SETTINGS['page'])) . '">' .
						'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/lightning_red.png') . ' alt="Clear all" /> Clear all mappings' .
						'</a></li>';
				$buttons['clear'] .= '</ul>';
				$buttons['clear'] .= '</div>';
			}

				// View page
		//	$buttons['view'] = '<a href="' . htmlspecialchars($_SERVER['REQUEST_URI'].'&_preview=1') . '">' .
		//			'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/viewdok.gif') . ' class="c-inputButton" title="Preview the mapping to the template" alt="Preview" />' .
		//			'</a>';
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
	 * @param 	array	Current page
	 * @return	string	Page path
	 */
	function getFilePath() {
		global $LANG;

			// Is this a real page
		if ($this->markupFile)	{
			$title = substr($this->markupFile, strlen(PATH_site));
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
	 * @param 	array	Current page
	 * @return	string	Page info
	 */
	function getFileInfo() {
		global $BE_USER;

				// Add icon with clickmenu, etc:
		if ($this->markupFile)	{	// If there IS a real page
			ereg("(.*)\.([^\.]*$)", $this->markupFile, $reg);
			$alttext = $reg[2];
			$icon = t3lib_BEfunc::getFileIcon($reg[2]);
			$iconImg = '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/fileicons/'.$icon,'width="18" height="16"').' title="'.htmlspecialchars($alttext).'" alt="" />';
				// Make Icon:
			$theIcon = $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg, $this->markupFile);
			$theIcon = $theIcon . '<em>[modified: ' . Date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], @filemtime($this->markupFile)) . ']</em>';
		} else {	// On root-level of page tree
				// Make Icon
			$iconImg = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/i/_icon_website.gif') . ' alt="' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] . '" />';
			if($BE_USER->user['admin']) {
				$theIcon = $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg, 'pages', 0);
			} else {
				$theIcon = $iconImg;
			}
			$theIcon = $theIcon . '<em>[pid: ' . $pageRecord['uid'] . ']</em>';
		}

			// Setting icon with clickmenu
		$pageInfo = $theIcon;
		return $pageInfo;
	}

	/**
	 * Generate the page path for docheader
	 *
	 * @param 	array	Current page
	 * @return	string	Page path
	 */
	function getDSPath($pageRecord) {
		global $LANG;

			// Is this a real page
		if ($pageRecord['uid'])	{
			$title = $pageRecord['_thePath'];
		} else {
			$title = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
		}

			// Is this a real DS
		if (intval($this->displayUid) > 0)	{
			$row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_datastructure', $this->displayUid);

			if (is_array($row))	{
				$title .= t3lib_BEfunc::getRecordTitle('tx_templavoila_datastructure',$row,1);
			}
		}

			// Setting the path of the page
		$pagePath = $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path', 1) . ': <span class="typo3-docheader-pagePath">' . htmlspecialchars(t3lib_div::fixed_lgd_cs($title, -50)) . '</span>';
		return $pagePath;
	}

	/**
	 * Setting page icon with clickmenu + uid for docheader
	 *
	 * @param 	array	Current page
	 * @return	string	Page info
	 */
	function getDSInfo($pageRecord) {
		global $BE_USER;

			// Is this a real DS
		if (intval($this->displayUid) > 0)	{
			$row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_datastructure', $this->displayUid);

			if (is_array($row))	{
				$iconImg = t3lib_iconworks::getIconImage('tx_templavoila_datastructure', $row, $this->doc->backPath, 'class="absmiddle" title="UID: '.$this->displayUid.'"');
					// Make Icon:
				$theIcon = $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg, 'tx_templavoila_datastructure', $row['uid'], 1);

					// Setting icon with clickmenu + uid
				$pageInfo = $theIcon . '<em>[uid: ' . $this->displayUid . ']</em>';
			}
		}

		return $pageInfo;
	}

	/**
	 * Generate the page path for docheader
	 *
	 * @param 	array	Current page
	 * @return	string	Page path
	 */
	function getTOPath($pageRecord) {
		global $LANG;

			// Is this a real page
		if ($pageRecord['uid'])	{
			$title = $pageRecord['_thePath'];
		} else {
			$title = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
		}

			// Is this a real TO
		if (intval($this->displayUid) > 0)	{
			$row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj', $this->displayUid);

			if (is_array($row))	{
				$title .= t3lib_BEfunc::getRecordTitle('tx_templavoila_tmplobj', $row);
			}
		}

			// Setting the path of the page
		$pagePath = $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path', 1) . ': <span class="typo3-docheader-pagePath">' . htmlspecialchars(t3lib_div::fixed_lgd_cs($title, -50)) . '</span>';
		return $pagePath;
	}

	/**
	 * Setting page icon with clickmenu + uid for docheader
	 *
	 * @param 	array	Current page
	 * @return	string	Page info
	 */
	function getTOInfo($pageRecord) {
		global $BE_USER;

			// Is this a real TO
		if (intval($this->displayUid) > 0)	{
			$row = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj', $this->displayUid);

			if (is_array($row))	{
				$iconImg = t3lib_iconworks::getIconImage('tx_templavoila_tmplobj', $row, $this->doc->backPath, 'class="absmiddle" title="UID: '.$this->displayUid.'"');
					// Make Icon:
				$theIcon = $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg, 'tx_templavoila_tmplobj', $row['uid'], 1);

					// Setting icon with clickmenu + uid
				$pageInfo = $theIcon . '<em>[uid: ' . $this->displayUid . ']</em>';
			}
		}

		return $pageInfo;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/cm1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/cm1/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_templavoila_cm1_integral');
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
