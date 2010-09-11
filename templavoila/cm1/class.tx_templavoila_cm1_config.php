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
 * Submodule 'presets' for the templavoila control-center module
 *
 * @author   Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   55: class tx_templavoila_cm1_config
 *   70:     function init(&$pObj)
 * 2615:     function lipsumLink($formElementName)
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

if (t3lib_extMgm::isLoaded('t3editor')) {
	/* pre 4.4 */
	     if (file_exists(t3lib_extMgm::extPath('t3editor') . 'class.tx_t3editor.php'))
		require_once(t3lib_extMgm::extPath('t3editor') . 'class.tx_t3editor.php');
	/* post 4.4 */
	else if (file_exists(t3lib_extMgm::extPath('t3editor') . 'classes/class.tx_t3editor.php'))
		require_once(t3lib_extMgm::extPath('t3editor') . 'classes/class.tx_t3editor.php');
}

if (t3lib_extMgm::isLoaded('lorem_ipsum')) {
	require_once(t3lib_extMgm::extPath('lorem_ipsum') . 'class.tx_loremipsum_wiz.php');
	if (t3lib_extMgm::isLoaded('rtehtmlarea')) {
		require_once(t3lib_extMgm::extPath('rtehtmlarea') . 'class.tx_rtehtmlarea_base.php');
	}
}

/**
 * Submodule 'config' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_cm1_config {


	// References to the control-center module object
	var $pObj;			// A pointer to the parent object, that is the templavoila control-center module script. Set by calling the method init() of this class.
	var $doc;			// A reference to the doc object of the parent object.

	var $presetsObj;		// Instance of presets class

	var $dsTypes;			// cached DS-node icons

	// t3editor
	var $t3e = null;

	var $textareaCols = 60;		//default cols for textareas

	/**
	 * Initializes the overview object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila control-center module.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	void
	 * @access	public
	 */
	function init(&$pObj) {
		// Make local reference to some important variables:
		$this->pObj =& $pObj;
		$this->extKey =& $this->pObj->extKey;

		// Make local reference to some important variables:
		$this->doc =& $this->pObj->doc;
		$this->presetsObj =& $this->pObj->presetsObj;

		$this->MOD_SETTINGS =& $this->pObj->MOD_SETTINGS;

		// Icons
		$this->dsTypes = array(
			'sc' => $GLOBALS['LANG']->getLL('typeSC') . ': ',
			'co' => $GLOBALS['LANG']->getLL('typeCO') . ': ',
			'el' => $GLOBALS['LANG']->getLL('typeEL') . ': ',
			'at' => $GLOBALS['LANG']->getLL('typeAT') . ': ',
			'no' => $GLOBALS['LANG']->getLL('typeNO') . ': ');

		foreach ($this->dsTypes as $id => $title) {
			$this->dsTypes[$id] = array(
				// abbrevation
				$id,
				// descriptive title
				$title,
				// image-path
				t3lib_iconWorks::skinImg($this->doc->backPath, t3lib_extMgm::extRelPath('templavoila') . 'cm1/item_' . $id . '.gif', 'width="24" height="16"') . ' border="0" style="margin-right: 5px;"',
				// background-path
				t3lib_iconWorks::skinImg($this->doc->backPath, t3lib_extMgm::extRelPath('templavoila') . 'cm1/item_' . $id . '.gif', '', 1)
			);

			// information
			$this->dsTypes[$id][4] = @getimagesize($this->dsTypes[$id][3]);
		}

		if (t3lib_extMgm::isLoaded('t3editor')) {
			/* pre 4.4 */
			     if (file_exists(t3lib_extMgm::extPath('t3editor') . 'class.tx_t3editor.php'))
				 $this->t3e = t3lib_div::getUserObj('EXT:t3editor/class.tx_t3editor.php:&tx_t3editor');
			/* post 4.4 */
			else if (file_exists(t3lib_extMgm::extPath('t3editor') . 'classes/class.tx_t3editor.php'))
				 $this->t3e = t3lib_div::getUserObj('EXT:t3editor/classes/class.tx_t3editor.php:&tx_t3editor');
		}
	}

	/**
	 * Returns an abbrevation and a description for a given element-type.
	 *
	 * @return	array
	 */
	function dsTypeInfo($conf) {
		// Icon:
		if ($conf['type'] == 'section')
			return $this->dsTypes['sc'];

		if ($conf['type'] == 'array') {
			if (!$conf['section'])
				return $this->dsTypes['co'];

			return $this->dsTypes['sc'];
		}

		if ($conf['type'] == 'attr')
			return $this->dsTypes['at'];

		if ($conf['type'] == 'no_map')
			return $this->dsTypes['no'];

		return $this->dsTypes['el'];
	}

	function getJavascriptCode() {
		$code = '';

		if (t3lib_extMgm::isLoaded('t3editor')) {
			$code .= $this->t3e->getJavascriptCode($this->doc);
			$code .= $this->doc->wrapScriptTags('
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
			');
		}

		// TS-browser
		$code .= $this->doc->wrapScriptTags('
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
						$(\'browser[communication]\').src = \'' . $this->pObj->baseScript . 'mode=browser&pid=\' + rid + \'&current=\' +
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
		');

		return $code;
	}

	/*****************************************
	 *
	 * Render info functions
	 *
	 *****************************************/

	function renderInfo_elicon($value, $key) {
		// Icon:
		$info = $this->dsTypeInfo($value);

		return '<img' . $info[2] . ' alt="" title="' . $info[1] . $key . '" class="absmiddle" />';
	}

	/*****************************************
	 *
	 * Render config functions
	 *
	 *****************************************/

	function renderConfig_elselector($formFieldName, &$insertDataArray, $inheritable = true, $localizable = false) {
		// Icons:
		$info = $this->dsTypeInfo($insertDataArray);

		return '
			<select style="margin: 4px 0 4px 0; padding: 1px 1px 1px 30px; background: 0 50% url(' . $info[3] . ') no-repeat; width: 150px !important;" title="Mapping Type" name="' . $formFieldName . '[type]" onchange="if (confirm(\'' . $GLOBALS['LANG']->getLL('mess.onChangeAlert') . '\')) document.getElementById(\'_updateDS\').click();">
				<optgroup class="c-divider" label="' . $GLOBALS['LANG']->getLL('structureFormContainers') . '">
					<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['sc'][3] . ') no-repeat;" value="section"' . ($insertDataArray['type'] == 'section' ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('dsnode_sc') . '</option>
					<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['co'][3] . ') no-repeat;" value="array"' .   ($insertDataArray['type'] == 'array'   ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('dsnode_co') . '</option>
				</optgroup>
				<optgroup class="c-divider" label="' . $GLOBALS['LANG']->getLL('structureFormElements') . '">
					<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['el'][3] . ') no-repeat;" value=""' .        ($insertDataArray['type'] == ''        ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('dsnode_el') . '</option>
					<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['at'][3] . ') no-repeat;" value="attr"' .    ($insertDataArray['type'] == 'attr'    ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('dsnode_at') . '</option>
				</optgroup>
				<optgroup class="c-divider" label="' . $GLOBALS['LANG']->getLL('structureFormOther') . '">
					<option style="padding: 1px 1px 1px 30px; background: 0 50% url(' . $this->dsTypes['no'][3] . ') no-repeat;" value="no_map"' .  ($insertDataArray['type'] == 'no_map'  ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('dsnode_no') . '</option>
				</optgroup>
			</select>
		';
	}

	function renderConfig_jsselector() {
		return '
			<input id="dsel-act" type="hidden" name="dsel_act" />
			<ul id="dsel-menu" class="DS-tree">
				<li><a id="dssel-general" class="active" href="#" onclick="" title="' .        $GLOBALS['LANG']->getLL('structureFormMenuTitleConfig'  ) . '">' . $GLOBALS['LANG']->getLL('structureFormMenuConfig'  ) . '</a>
					<ul>
						<li                   ><a id="dssel-ts" href="#" title="' .    $GLOBALS['LANG']->getLL('structureFormMenuTitleTS'      ) . '">' . $GLOBALS['LANG']->getLL('structureFormMenuTS'      ) . '</a></li>
						<li                   ><a id="dssel-extra" href="#" title="' . $GLOBALS['LANG']->getLL('structureFormMenuTitleExtra'   ) . '">' . $GLOBALS['LANG']->getLL('structureFormMenuExtra'   ) . '</a></li>
						<li class="last-child"><a id="dssel-proc" href="#" title="' .  $GLOBALS['LANG']->getLL('structureFormMenuTitleDataProc') . '">' . $GLOBALS['LANG']->getLL('structureFormMenuDataProc') . '</a></li>
					</ul></li>
				<li class="last-child"><a id="dssel-tce" href="#" title="' .                   $GLOBALS['LANG']->getLL('structureFormMenuTitleTCE'     ) . '">' . $GLOBALS['LANG']->getLL('structureFormMenuTCE'     ) . '</a></li>
			</ul>
		';
	}

	function renderConfig_jscode() {
		return '
			<script type="text/javascript">
				var dsel_act = "' . (t3lib_div::_GP('dsel_act') ? t3lib_div::_GP('dsel_act') : 'general') . '";
				var dsel_menu = [
					{"id" : "general",		"avail" : true,	"label" : "' . $GLOBALS['LANG']->getLL('structureFormMenuConfig'  ) . '", "title" : "' . $GLOBALS['LANG']->getLL('structureFormMenuTitleConfig'  ) . '",	"childs" : [
						{"id" : "ts",		"avail" : true,	"label" : "' . $GLOBALS['LANG']->getLL('structureFormMenuTS'      ) . '", "title" : "' . $GLOBALS['LANG']->getLL('structureFormMenuTitleTS'      ) . '"},
						{"id" : "extra",	"avail" : true,	"label" : "' . $GLOBALS['LANG']->getLL('structureFormMenuExtra'   ) . '", "title" : "' . $GLOBALS['LANG']->getLL('structureFormMenuTitleExtra'   ) . '"},
						{"id" : "proc",		"avail" : true,	"label" : "' . $GLOBALS['LANG']->getLL('structureFormMenuDataProc') . '", "title" : "' . $GLOBALS['LANG']->getLL('structureFormMenuTitleDataProc') . '"}]},
					{"id" : "tce",			"avail" : true,	"label" : "' . $GLOBALS['LANG']->getLL('structureFormMenuTCE'     ) . '", "title" : "' . $GLOBALS['LANG']->getLL('structureFormMenuTitleTCE'     ) . '"}
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
		';
	}

	function renderConfig_general($formFieldName, &$insertDataArray, $inheritable = true, $localizable = false) {
		/* The basic XML-structure of an tx_templavoila-entry is:
		 *
		 * <tx_templavoila>
		 * 	<title>			-> Human readable title of the element
		 * 	<description>		-> A description explaining the elements function
		 * 	<sample_data>		-> Some sample-data (can't contain HTML)
		 * 	<eType>			-> The preset-type of the element, used to switch use/content of TCEforms/TypoScriptObjPath
		 * 	<oldStyleColumnNumber>	-> for distributing the fields across the tt_content column-positions
		 * 	<langOverlayMode>	-> A description explaining the elements function
		 * 	<langOverlayMode>	-> A description explaining the elements function
		 * </tx_templavoila>
		 */
		$form = '
		<dl id="dsel-general" class="DS-config">
			<!-- always present options +++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormTitle') . ':</label></dt>
			<dd><input type="text" size="80" name="' . $formFieldName . '[tx_templavoila][title]" value="' . htmlspecialchars($insertDataArray['tx_templavoila']['title']) . '" /></dd>

			' . (($insertDataArray['type'] != 'no_map') ? '
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormInstruction') . ':</label></dt>
			<dd><textarea class="fixed-font enable-tab" cols="' . $this->textareaCols . '" rows="2" name="' . $formFieldName . '[tx_templavoila][description][]">' . t3lib_div::deHSCentities(htmlspecialchars($insertDataArray['tx_templavoila']['description'][0])) . '</textarea></dd>

			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormRules') . ':</label></dt>
			<dd><input type="text" size="80" name="' . $formFieldName . '[tx_templavoila][tags]" value="'.htmlspecialchars($insertDataArray['tx_templavoila']['tags']).'" /></dd>
			' :'') . '

			' . (($insertDataArray['type'] != 'array') &&
			     ($insertDataArray['type'] != 'section') ? '
			' . (($insertDataArray['type'] != 'no_map') ? '
			<!-- non-array options ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormSamples') . ':</label></dt>
			<dd><textarea class="fixed-font enable-tab" cols="' . $this->textareaCols . '" rows="5" name="' . $formFieldName . '[tx_templavoila][sample_data][]">' . t3lib_div::deHSCentities(htmlspecialchars($insertDataArray['tx_templavoila']['sample_data'][0])) . '</textarea>
				' . $this->lipsumLink($formFieldName . '[tx_templavoila][sample_data]') . '</dd>
			' :'') . '

			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormPreset') . ':</label></dt>
			<dd><select onchange="if (confirm(\'' . $GLOBALS['LANG']->getLL('mess.onChangeAlert') . '\')) document.getElementById(\'_updateDS\').click();"
				name="' . $formFieldName . '[tx_templavoila][eType]">
				<optgroup class="c-divider" label="' . $GLOBALS['LANG']->getLL('structureFormTCEFields') . '">
					<option value="ce"'.              ($insertDataArray['tx_templavoila']['eType'] == 'ce'               ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_ce'              ) . '</option>
					<option value="input"'.           ($insertDataArray['tx_templavoila']['eType'] == 'input'            ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_input'           ) . '</option>
					<option value="input_h"'.         ($insertDataArray['tx_templavoila']['eType'] == 'input_h'          ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_input_h'         ) . '</option>
					<option value="input_g"'.         ($insertDataArray['tx_templavoila']['eType'] == 'input_g'          ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_input_g'         ) . '</option>
					<option value="text"'.            ($insertDataArray['tx_templavoila']['eType'] == 'text'             ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_text'            ) . '</option>
					<option value="rte"'.             ($insertDataArray['tx_templavoila']['eType'] == 'rte'              ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_rte'             ) . '</option>
					<option value="link"'.            ($insertDataArray['tx_templavoila']['eType'] == 'link'             ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_link'            ) . '</option>
					<option value="int"'.             ($insertDataArray['tx_templavoila']['eType'] == 'int'              ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_int'             ) . '</option>
					<option value="image"'.           ($insertDataArray['tx_templavoila']['eType'] == 'image'            ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_image'           ) . '</option>
					<option value="imagefixed"'.      ($insertDataArray['tx_templavoila']['eType'] == 'imagefixed'       ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_imagefixed'      ) . '</option>
					<option value="imagelist"'.       ($insertDataArray['tx_templavoila']['eType'] == 'imagelist'        ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_imagelist'       ) . '</option>
					<option value="select"'.          ($insertDataArray['tx_templavoila']['eType'] == 'select'           ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_select'          ) . '</option>
					<option value="custom"'.          ($insertDataArray['tx_templavoila']['eType'] == 'custom'           ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_custom'          ) . '</option>
				</optgroup>
				<optgroup class="c-divider" label="' . $GLOBALS['LANG']->getLL('structureFormTSElements') . '">
					<option value="TypoScriptObject"'.($insertDataArray['tx_templavoila']['eType'] == 'TypoScriptObject' ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_TypoScriptObject') . '</option>
					<option value="none"'.            ($insertDataArray['tx_templavoila']['eType'] == 'none'             ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('presets_none'            ) . '</option>
				</optgroup>
				<optgroup class="c-divider" label="' . $GLOBALS['LANG']->getLL('structureFormOther') . '">
				</optgroup>
			</select><input type="hidden"
				name="' . $formFieldName . '[tx_templavoila][eType_before]"
				value="' . $insertDataArray['tx_templavoila']['eType'] . '" /></dd>
			' :'') . '

			' . (($insertDataArray['type'] != 'array') ? '
			' . ($inheritable ? '
			<!-- non-array options ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormInheritance') . ':</label></dt>
			<dd>
				<input type="radio" name="' . $formFieldName . '[tx_templavoila][inheritance]" value="' . TVDS_INHERITANCE_NONE       . '" ' . ($insertDataArray['tx_templavoila']['inheritance'] == TVDS_INHERITANCE_NONE       ? 'checked="checked"' : '') . ' /> Never<br />
				<input type="radio" name="' . $formFieldName . '[tx_templavoila][inheritance]" value="' . TVDS_INHERITANCE_REPLACE    . '" ' . ($insertDataArray['tx_templavoila']['inheritance'] == TVDS_INHERITANCE_REPLACE    ? 'checked="checked"' : '') . ' /> Replace<br />
				<input type="radio" name="' . $formFieldName . '[tx_templavoila][inheritance]" value="' . TVDS_INHERITANCE_ACCUMULATE . '" ' . ($insertDataArray['tx_templavoila']['inheritance'] == TVDS_INHERITANCE_ACCUMULATE ? 'checked="checked"' : '') . ' /> Accumulate<br />
			</dd>
			' : '') . '
			' . ($localizable ? '
			<!-- non-array options ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ -->
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormOverlayMode') . ':</label></dt>
			<dd><select
				name="' . $formFieldName . '[tx_templavoila][langOverlayMode]">
				<option value=""'              . ($insertDataArray['tx_templavoila']['langOverlayMode'] == ''              ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('overlaymode_'             ) . '</option>
				<option value="ifFalse"'       . ($insertDataArray['tx_templavoila']['langOverlayMode'] == 'ifFalse'       ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('overlaymode_ifFalse'      ) . '</option>
				<option value="ifBlank"'       . ($insertDataArray['tx_templavoila']['langOverlayMode'] == 'ifBlank'       ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('overlaymode_ifBlank'      ) . '</option>
				<option value="never"'         . ($insertDataArray['tx_templavoila']['langOverlayMode'] == 'never'         ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('overlaymode_never'        ) . '</option>
				<option value="removeIfBlank"' . ($insertDataArray['tx_templavoila']['langOverlayMode'] == 'removeIfBlank' ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('overlaymode_removeIfBlank') . '</option>
			</select>
			</dd>
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormMultilanguage') . ':</label></dt>
			<dd>
				<input type="radio" name="' . $formFieldName . '[tx_templavoila][multilang]" value="1" ' . ( $insertDataArray['tx_templavoila']['multilang'] ? 'checked="checked"' : '') . ' /> ' . $GLOBALS['LANG']->getLL('yes') . '
				<input type="radio" name="' . $formFieldName . '[tx_templavoila][multilang]" value="0" ' . (!$insertDataArray['tx_templavoila']['multilang'] ? 'checked="checked"' : '') . ' /> ' . $GLOBALS['LANG']->getLL('no' ) . '
			</dd>
			' : '') . '
			' : '') . '
		</dl>';

		return $form;
	}

	function renderConfig_typoscript($formFieldName, &$insertDataArray, $inheritable = true, $localizable = false) {
		/* The Typoscript-related XML-structure of an tx_templavoila-entry is:
		 *
		 * <tx_templavoila>
		 *	<TypoScript_constants>	-> an array of constants that will be substituted in the <TypoScript>-element
		 * 	<TypoScript>		->
		 * </tx_templavoila>
		 */
		$form = '
		<dl id="dsel-ts" class="DS-config">
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormTSConst') . ':</label></dt>
			<dd><textarea class="fixed-font enable-tab xml" cols="' . $this->textareaCols . '" rows="10" name="' . $formFieldName . '[tx_templavoila][TypoScript_constants]" rel="tx_templavoila.TypoScript_constants">' .
				htmlspecialchars($this->pObj->flattenarray($insertDataArray['tx_templavoila']['TypoScript_constants'])) . '</textarea></dd>
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormTSCode') . ':</label></dt>
			<dd>' . (!$this->t3e ? '
				<textarea
					class="fixed-font enable-tab ts"
					cols="' . $this->textareaCols . '"
					rows="10"
					name="' . $formFieldName . '[tx_templavoila][TypoScript]"
					rel="tx_templavoila.TypoScript">' .
					htmlspecialchars($insertDataArray['tx_templavoila']['TypoScript']) . '
				</textarea>' :
				str_replace('<br/>', '', $this->t3e->getCodeEditor(
					$formFieldName . '[tx_templavoila][TypoScript]',
					'fixed-font enable-tab ts',
					htmlspecialchars($insertDataArray['tx_templavoila']['TypoScript']),
					'cols="' . $this->textareaCols . '"
					rows="10"
					rel="tx_templavoila.TypoScript"
					id="dsel-t3editor"'))) . '
			</dd>
		</dl>';

		return $form;
	}

	function renderConfig_extra($formFieldName, &$insertDataArray, $inheritable = true, $localizable = false) {
		/* The Typoscript-related XML-structure of an tx_templavoila-entry is:
		 *
		 * <tx_templavoila>
		 * 	<TypoScriptObjPath>	->
		 * </tx_templavoila>
		 */
		if (($extra = $this->presetsObj->drawDataStructureMap_editItem_editTypeExtra(
				$insertDataArray['tx_templavoila']['eType'],
				$formFieldName . '[tx_templavoila][eType_EXTRA]',
				($insertDataArray['tx_templavoila']['eType_EXTRA'] 	// Use eType_EXTRA only if it is set (could be modified, etc), otherwise use TypoScriptObjPath!
				?	$insertDataArray['tx_templavoila']['eType_EXTRA']
				:	($insertDataArray['tx_templavoila']['TypoScriptObjPath']
					?	array(
							'objDesc' => $insertDataArray['tx_templavoila']['TypoScriptObjDesc'],
							'objPath' => $insertDataArray['tx_templavoila']['TypoScriptObjPath']
						)
					:	''))
			)))
			$form = '
			<dl id="dsel-extra" class="DS-config">
				<dt>' . $GLOBALS['LANG']->getLL('structureFormExtra') . '</dt>
				<dd>' . $extra . '</dd>
			</dl>';
		else
			$form == '';

		return $form;
	}

	function renderConfig_procf($formFieldName, &$insertDataArray, $inheritable = true, $localizable = false) {
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
		$form = '
		<dl id="dsel-proc" class="DS-config">
			<dt>' . $GLOBALS['LANG']->getLL('structureFormPost') . ':</dt>
			<dd>
				<label style="float: left; width: 20em;">' . $GLOBALS['LANG']->getLL('structureFormPostCast') . ':</label>
				<input type="radio" name="' . $formFieldName . '[tx_templavoila][proc][int]" value="1" ' . ( $insertDataArray['tx_templavoila']['proc']['int'] ? 'checked="checked"' : '') . ' /> ' . $GLOBALS['LANG']->getLL('yes') . '
				<input type="radio" name="' . $formFieldName . '[tx_templavoila][proc][int]" value="0" ' . (!$insertDataArray['tx_templavoila']['proc']['int'] ? 'checked="checked"' : '') . ' /> ' . $GLOBALS['LANG']->getLL('no' ) . '
				<br />
				<label style="float: left; width: 20em;">' . $GLOBALS['LANG']->getLL('structureFormPostHSC') . ':</label>
				<input type="radio" name="' . $formFieldName . '[tx_templavoila][proc][HSC]" value="1" ' . ( $insertDataArray['tx_templavoila']['proc']['HSC'] ? 'checked="checked"' : '') . ' /> ' . $GLOBALS['LANG']->getLL('yes') . '
				<input type="radio" name="' . $formFieldName . '[tx_templavoila][proc][HSC]" value="0" ' . (!$insertDataArray['tx_templavoila']['proc']['HSC'] ? 'checked="checked"' : '') . ' /> ' . $GLOBALS['LANG']->getLL('no' ) . '
			</dd>

			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormStdWrap') . ':</label></dt>
			<dd><textarea class="fixed-font enable-tab ts" cols="' . $this->textareaCols . '" rows="10" name="' . $formFieldName . '[tx_templavoila][proc][stdWrap]" rel="tx_templavoila.proc.stdWrap">' . htmlspecialchars($insertDataArray['tx_templavoila']['proc']['stdWrap']) . '</textarea></dd>
		</dl>';

		return $form;
	}

	function renderConfig_procw($formFieldName, &$insertDataArray, $inheritable = true, $localizable = false) {
		/* The process-related XML-structure of an tx_templavoila-entry is:
		 *
		 * <tx_templavoila>
		 * 	<proc>			-> define post-processes for this element's value
		 *		<stdWrap>	-> an implicit stdWrap for this element, "stdWrap { ...inside... }"
		 * 	</proc>
		 * </tx_templavoila>
		 */
		$form = '
		<dl id="dsel-proc" class="DS-config">
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormStdWrap') . ':</label></dt>
			<dd>' . /*(!$this->t3e ?*/ '
				<textarea
					class="fixed-font enable-tab ts"
					cols="' . $this->textareaCols . '"
					rows="10"
					name="' . $formFieldName . '[tx_templavoila][proc][stdWrap]"
					rel="tx_templavoila.proc.stdWrap">' .
					htmlspecialchars($insertDataArray['tx_templavoila']['proc']['stdWrap']) . '
				</textarea>' /*:
				str_replace('<br/>', '', $this->t3e->getCodeEditor(
					$formFieldName . '[tx_templavoila][proc][stdWrap]',
					'fixed-font enable-tab ts',
					htmlspecialchars($insertDataArray['tx_templavoila']['proc']['stdWrap']),
					'cols="' . $this->textareaCols . '"
					rows="10"
					rel="tx_templavoila.TypoScript"
					id="dsel-t3editor"')))*/ . '
			</dd>
		</dl>';

		return $form;
	}

	function renderConfig_tceform($formFieldName, &$insertDataArray, $inheritable = true, $localizable = false) {
		/* The basic XML-structure of an TCEforms-entry is:
		 *
		 * <TCEforms>
		 * 	<label>			-> TCE-label for the BE
		 * 	<config>		-> TCE-configuration array
		 * </TCEforms>
		 */
		$form = '
		<dl id="dsel-tce" class="DS-config">
			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormTCELabel') . ':</label></dt>
			<dd><input type="text" size="80" name="' . $formFieldName . '[TCEforms][label]" value="' . htmlspecialchars($insertDataArray['TCEforms']['label']).'" /></dd>

			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormTCEMLang') . ':</label></dt>
			<dd>	<input type="radio" name="' . $formFieldName . '[TCEforms][multi]" value="1" ' . ( $insertDataArray['TCEforms']['multi'] ? 'checked="checked"' : '') . ' /> ' . $GLOBALS['LANG']->getLL('yes') . '
				<input type="radio" name="' . $formFieldName . '[TCEforms][multi]" value="0" ' . (!$insertDataArray['TCEforms']['multi'] ? 'checked="checked"' : '') . ' /> ' . $GLOBALS['LANG']->getLL('no' ) . '</dd>

			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormTCEConfig') . ':</label></dt>
			<dd><textarea class="fixed-font enable-tab xml" cols="' . $this->textareaCols . '" rows="10" name="' . $formFieldName . '[TCEforms][config]" rel="TCEforms.config">' .
				htmlspecialchars($this->pObj->flattenarray($insertDataArray['TCEforms']['config'])) .
				'</textarea></dd>

			<dt><label>' . $GLOBALS['LANG']->getLL('structureFormTCEExtras') . ':</label></dt>
			<dd><input type="text" size="80" name="'.$formFieldName.'[TCEforms][defaultExtras]" value="' . htmlspecialchars($insertDataArray['TCEforms']['defaultExtras']) . '" /></dd>
		</dl>';

		return $form;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$formElementName: ...
	 * @return	[type]		...
	 */
	function lipsumLink($formElementName) {
		if (t3lib_extMgm::isLoaded('lorem_ipsum')) {
			$LRobj = t3lib_div::makeInstance('tx_loremipsum_wiz');
			$LRobj->backPath = $this->doc->backPath;

			$PA = array(
				'fieldChangeFunc' => array(),
				'formName' => 'pageform',
				'itemName' => $formElementName . '[]',
				'params' => array(
#					'type'	=> 'header',
					'type'	=> 'description',
					'add'	=> 1,
					'endSequence' => '46,32',
				)
			);

			return $LRobj->main($PA, 'ID:templavoila');
		}

		return '';
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/cm1/class.tx_templavoila_cm1_config.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/cm1/class.tx_templavoila_cm1_config.php']);
}

?>