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
 * New content elements wizard for templavoila
 *
 * $Id: db_new_content_el.php 8140 2008-02-04 21:17:33Z dmitry $
 * Originally based on the CE wizard / cms extension by Kasper Skaarhoj <kasper@typo3.com>
 * XHTML compatible.
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @coauthor	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   86: class tx_templavoila_dbnewcontentel
 *  108:     function init()
 *  154:     function main()
 *  235:     function printContent()
 *  245:     function linkParams()
 *
 *              SECTION: OTHER FUNCTIONS:
 *  271:     function getWizardItems()
 *  281:     function wizardArray()
 *  448:     function removeInvalidElements(&$wizardItems)
 *  512:     function buildRecordWhere($table)
 *
 * TOTAL FUNCTIONS: 8
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Script Class for the New Content element wizard
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @package TYPO3
 * @subpackage templavoila
 */
class tx_templavoila_wizards_content {


	// References to the page module object
	var $pObj;		// A pointer to the parent object, that is the templavoila page module script. Set by calling the method init() of this class.
	var $doc;		// A reference to the doc object of the parent object.
	var $extKey;		// A reference to extension key of the parent object.


	// Internal, static (from GPvars):
	var $id;			// Page id
	var $parentRecord;		// Parameters for the new record
	var $altRoot;			// Array with alternative table, uid and flex-form field (see index.php in module for details, same thing there.)

	// Internal, dynamic:
	var $include_once = array();	// Includes a list of files to include between init() and main() - see init()
	var $content;			// Used to accumulate the content of the module.
	var $access;			// Access boolean.

	/**
	 * Initialize internal variables.
	 *
	 * @return	void
	 */
	function init(&$pObj) {
		// Make local reference to some important variables:
		$this->pObj =& $pObj;
		$this->doc =& $this->pObj->doc;
		$this->extKey =& $this->pObj->extKey;
		$this->apiObj =& $this->pObj->apiObj;

		// Setting internal vars:
		$this->id = intval(t3lib_div::_GP('id'));
		$this->parentRecord = t3lib_div::_GP('parentRecord');
		$this->altRoot = t3lib_div::_GP('altRoot');
		$this->defVals = t3lib_div::_GP('defVals');

		// If no parent record was specified, find one:
		if (!$this->parentRecord) {
			$mainContentAreaFieldName = $this->apiObj->ds_getFieldNameByColumnPosition($this->id, 0);
			if ($mainContentAreaFieldName != FALSE) {
				$this->parentRecord = implode(SEPARATOR_PARMS, array('pages', $this->id, 'sDEF', 'lDEF', $mainContentAreaFieldName, 'vDEF', 0));
			}
		}

		// If still no parent has been found, we're in list-module
		if (!$this->parentRecord) {
			$this->parentRecord = -$this->id;
		}
	}

	/**
	 * Creating the module output.
	 *
	 * @return	void
	 * @todo	provide position mapping if no position is given already. Like the columns selector but for our cascading element style ...
	 */
	function renderWizard_createNewContentElement()	{
		global $BACK_PATH;

		$elRow = t3lib_BEfunc::getRecordWSOL('pages', $this->id);
		$header = t3lib_iconWorks::getIconImage('pages', $elRow, $BACK_PATH, ' title="' . htmlspecialchars(t3lib_BEfunc::getRecordIconAltText($elRow,'pages')).'" align="top"');
		$header .= t3lib_BEfunc::getRecordTitle('pages', $elRow, 1);

		// Wizard
		$wizardCode = '';
		$tableRows = array();
		$wizardItems = $this->getWizardItems();

		// Traverse items for the wizard.
		// An item is either a header or an item rendered with a title/description and icon:
		$counter = 0;
		foreach($wizardItems as $key => $wizardItem) {
			if ($wizardItem['header'])	{
				if ($counter > 0)
					$tableRows[] = '
					<tr>
						<td colspan="3"><br /></td>
					</tr>';

				$tableRows[] = '
					<tr class="bgColor5">
						<td colspan="3"><strong>' . htmlspecialchars($wizardItem['header']) . '</strong></td>
					</tr>';
			} else {
				$tableLinks = array();

				// href URI for icon/title:
				$newRecordLink = $this->pObj->mod1Script . $this->linkParams() . '&createNewRecord=' . rawurlencode($this->parentRecord) . $wizardItem['params'];
				if (t3lib_div::_GP('returnUrl'))
					$newRecordLink .= '&returnUrl=' . rawurlencode(t3lib_div::_GP('returnUrl'));

				// Icon:
				$iInfo = @getimagesize($wizardItem['icon']);
				$tableLinks[] = '<a href="' . $newRecordLink . '"><img'.t3lib_iconWorks::skinImg($this->doc->backPath, $wizardItem['icon'], '') . ' alt="" /></a>';

				// Title + description:
				$tableLinks[] = '<a href="' . $newRecordLink . '"><strong>' . htmlspecialchars($wizardItem['title']) . '</strong><br />' . nl2br(htmlspecialchars(trim($wizardItem['description']))) . '</a>';

				// Finally, put it together in a table row:
				$tableRows[] = '
					<tr>
						<td valign="top">' . implode('</td>
						<td valign="top">', $tableLinks) . '</td>
					</tr>';

				$counter++;
			}
		}

		// Add the wizard table to the content:
		$wizardCode .= $GLOBALS['LANG']->getLL('sel1', 1) . '
		<br /><br />
		<!--
			Content Element wizard table:
		-->
			<table border="0" cellpadding="1" cellspacing="2" id="typo3-ceWizardTable">
				' . implode('', $tableRows) . '
			</table>';

		return $this->doc->section($GLOBALS['LANG']->getLL('1_selectType'), $wizardCode, 0, 1);
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function linkParams()	{
		$output = 'id=' . $this->id . (is_array($this->altRoot) ? t3lib_div::implodeArrayForUrl('altRoot', $this->altRoot) : '');

		return $output;
	}

	/***************************
	 *
	 * OTHER FUNCTIONS:
	 *
	 ***************************/


	/**
	 * Returns the content of wizardArray() function...
	 *
	 * @return	array		Returns the content of wizardArray() function...
	 */
	function getWizardItems() {
		return $this->wizardArray();
	}

	/**
	 * Returns the array of elements in the wizard display.
	 * For the plugin section there is support for adding elements there from a global variable.
	 *
	 * @return	array
	 */
	function wizardArray()	{
		global $LANG, $TBE_MODULES_EXT, $TYPO3_DB;

		$defVals = t3lib_div::implodeArrayForUrl('defVals', is_array($this->defVals) ? $this->defVals : array());

		$wizardItems = array(
			'common' => array('header'=>$LANG->getLL('common')),

			'common_1' => array(
				'icon'=>'gfx/c_wiz/regular_text.gif',
				'title'=>$LANG->getLL('common_1_title'),
				'description'=>$LANG->getLL('common_1_description'),
				'params'=>'&defVals[tt_content][CType]=text' . $defVals,
			),

			'common_2' => array(
				'icon'=>'gfx/c_wiz/text_image_below.gif',
				'title'=>$LANG->getLL('common_2_title'),
				'description'=>$LANG->getLL('common_2_description'),
				'params'=>'&defVals[tt_content][CType]=textpic&defVals[tt_content][imageorient]=8' . $defVals,
			),

			'common_3' => array(
				'icon'=>'gfx/c_wiz/text_image_right.gif',
				'title'=>$LANG->getLL('common_3_title'),
				'description'=>$LANG->getLL('common_3_description'),
				'params'=>'&defVals[tt_content][CType]=textpic&defVals[tt_content][imageorient]=17' . $defVals,
			),

			'common_4' => array(
				'icon'=>'gfx/c_wiz/images_only.gif',
				'title'=>$LANG->getLL('common_4_title'),
				'description'=>$LANG->getLL('common_4_description'),
				'params'=>'&defVals[tt_content][CType]=image&defVals[tt_content][imagecols]=2' . $defVals,
			),

			'common_5' => array(
				'icon'=>'gfx/c_wiz/bullet_list.gif',
				'title'=>$LANG->getLL('common_5_title'),
				'description'=>$LANG->getLL('common_5_description'),
				'params'=>'&defVals[tt_content][CType]=bullets' . $defVals,
			),

			'common_6' => array(
				'icon'=>'gfx/c_wiz/table.gif',
				'title'=>$LANG->getLL('common_6_title'),
				'description'=>$LANG->getLL('common_6_description'),
				'params'=>'&defVals[tt_content][CType]=table'.$defVals,
			),

			'special' => array('header'=>$LANG->getLL('special')),

			'special_1' => array(
				'icon'=>'gfx/c_wiz/filelinks.gif',
				'title'=>$LANG->getLL('special_1_title'),
				'description'=>$LANG->getLL('special_1_description'),
				'params'=>'&defVals[tt_content][CType]=uploads'.$defVals,
			),

			'special_2' => array(
				'icon'=>'gfx/c_wiz/multimedia.gif',
				'title'=>$LANG->getLL('special_2_title'),
				'description'=>$LANG->getLL('special_2_description'),
				'params'=>'&defVals[tt_content][CType]=multimedia'.$defVals,
			),

			'special_3' => array(
				'icon'=>'gfx/c_wiz/sitemap2.gif',
				'title'=>$LANG->getLL('special_3_title'),
				'description'=>$LANG->getLL('special_3_description'),
				'params'=>'&defVals[tt_content][CType]=menu&defVals[tt_content][menu_type]=2'.$defVals,
			),

			'special_4' => array(
				'icon'=>'gfx/c_wiz/html.gif',
				'title'=>$LANG->getLL('special_4_title'),
				'description'=>$LANG->getLL('special_4_description'),
				'params'=>'&defVals[tt_content][CType]=html'.$defVals,
			),

			'forms' => array('header'=>$LANG->getLL('forms')),

			'forms_1' => array(
				'icon'=>'gfx/c_wiz/mailform.gif',
				'title'=>$LANG->getLL('forms_1_title'),
				'description'=>$LANG->getLL('forms_1_description'),
				'params'=>'&defVals[tt_content][CType]=mailform&defVals[tt_content][bodytext]='.rawurlencode(trim($LANG->getLL ('forms_1_example'))).$defVals,
			),

			'forms_2' => array(
				'icon'=>'gfx/c_wiz/searchform.gif',
				'title'=>$LANG->getLL('forms_2_title'),
				'description'=>$LANG->getLL('forms_2_description'),
				'params'=>'&defVals[tt_content][CType]=search'.$defVals,
			),

			'forms_3' => array(
				'icon'=>'gfx/c_wiz/login_form.gif',
				'title'=>$LANG->getLL('forms_3_title'),
				'description'=>$LANG->getLL('forms_3_description'),
				'params'=>'&defVals[tt_content][CType]=login'.$defVals,
			),
		);

		// Flexible content elements:
        	$positionPid = $this->id;
        	$dataStructureRecords = array();
        	$storageFolderPID = $this->apiObj->getStorageFolderPid($positionPid);

        	// Fetch data structures stored in the database:
        	$addWhere = $this->buildRecordWhere('tx_templavoila_datastructure');
        	$res = $TYPO3_DB->exec_SELECTquery(
        		'*',
        		'tx_templavoila_datastructure',
        		'pid=' . intval($storageFolderPID) . ' AND scope=' . TVDS_SCOPE_FCE . $addWhere .
        			t3lib_BEfunc::deleteClause('tx_templavoila_datastructure').
        			t3lib_BEfunc::versioningPlaceholderClause('tx_templavoila_datastructure')
        	);

        	while(FALSE !== ($row = $TYPO3_DB->sql_fetch_assoc($res))) {
        		$dataStructureRecords[$row['uid']] = $row;
        	}
/*
        	// Fetch static data structures which are stored in XML files:
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures']))	{
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $staticDataStructureArr)	{
				$staticDataStructureArr['_STATIC'] = TRUE;
				$dataStructureRecords[$staticDataStructureArr['path']] = $staticDataStructureArr;
			}
		}
*/
			// Fetch all template object records which uare based one of the previously fetched data structures:
		$templateObjectRecords = array();
		$addWhere = $this->buildRecordWhere('tx_templavoila_tmplobj');
		$res = $TYPO3_DB->exec_SELECTquery(
			'*',
			'tx_templavoila_tmplobj',
			'pid=' . intval($storageFolderPID) . ' AND parent=0' . $addWhere .
				t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj') .
				t3lib_BEfunc::versioningPlaceholderClause('tx_templavoila_tmpl'), '', 'sorting'
		);

		while(FALSE !== ($row = $TYPO3_DB->sql_fetch_assoc($res))) {
			if (is_array($dataStructureRecords[$row['datastructure']])) {
				$templateObjectRecords[] = $row;
			}
		}

		// Add the filtered set of TO entries to the wizard list:
		$wizardItems['fce']['header'] = $LANG->getLL('fce');
        	foreach($templateObjectRecords as $index => $templateObjectRecord) {
        	    $tmpFilename = 'uploads/tx_templavoila/'.$templateObjectRecord['previewicon'];

        	    $wizardItems['fce_'.$index]['icon'] = (@is_file(PATH_site.$tmpFilename)) ? ('../' . $tmpFilename) : ('../' . t3lib_extMgm::siteRelPath('templavoila').'res1/default_previewicon.gif');
        	    $wizardItems['fce_'.$index]['description'] = $templateObjectRecord['description'] ? htmlspecialchars($templateObjectRecord['description']) : $LANG->getLL ('template_nodescriptionavailable');
        	    $wizardItems['fce_'.$index]['title'] = $templateObjectRecord['title'];
        	    $wizardItems['fce_'.$index]['params'] = '&defVals[tt_content][CType]=templavoila_pi1&defVals[tt_content][tx_templavoila_ds]='.$templateObjectRecord['datastructure'].'&defVals[tt_content][tx_templavoila_to]='.$templateObjectRecord['uid'].$defVals;

        	    $index++;
        	}

		// PLUG-INS:
		if (is_array($TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses'])) {
			$wizardItems['plugins'] = array('header' => $LANG->getLL('plugins'));

			reset($TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']);
			while(list($class, $path) = each($TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses'])) {
				include_once($path);

				$modObj = t3lib_div::makeInstance($class);
				$wizardItems = $modObj->proc($wizardItems);
			}
		}

		// Remove elements where preset values are not allowed:
		$this->removeInvalidElements($wizardItems);

		return $wizardItems;
	}

	/**
	 * Checks the array for elements which might contain unallowed default values and will unset them!
	 * Looks for the "tt_content_defValues" key in each element and if found it will traverse that array as fieldname / value pairs and check. The values will be added to the "params" key of the array (which should probably be unset or empty by default).
	 *
	 * @param	array		Wizard items, passed by reference
	 * @return	void
	 */
	function removeInvalidElements(&$wizardItems) {
		global $TCA;

		// Load full table definition:
		t3lib_div::loadTCA('tt_content');

		// Get TCEFORM from TSconfig of current page
        	$TCEFORM_TSconfig = t3lib_BEfunc::getTCEFORM_TSconfig('tt_content', array('pid' => $this->id));
        	$removeItems = t3lib_div::trimExplode(',', $TCEFORM_TSconfig['CType']['removeItems'], 1);

		$headersUsed = Array();

		// Traverse wizard items:
		foreach($wizardItems as $key => $cfg) {
			// Exploding parameter string, if any (old style)
			if ($wizardItems[$key]['params'])	{
				// Explode GET vars recursively
				$tempGetVars = t3lib_div::explodeUrl2Array($wizardItems[$key]['params'],TRUE);

				// If tt_content values are set, merge them into the tt_content_defValues array, unset them from $tempGetVars and re-implode $tempGetVars into the param string (in case remaining parameters are around).
				if (is_array($tempGetVars['defVals']['tt_content']))	{
					$wizardItems[$key]['tt_content_defValues'] = array_merge(is_array($wizardItems[$key]['tt_content_defValues']) ? $wizardItems[$key]['tt_content_defValues'] : array(), $tempGetVars['defVals']['tt_content']);
					unset($tempGetVars['defVals']['tt_content']);
					$wizardItems[$key]['params'] = t3lib_div::implodeArrayForUrl('',$tempGetVars);
				}
			}

			// If tt_content_defValues are defined...:
			if (is_array($wizardItems[$key]['tt_content_defValues'])) {
				// Traverse field values:
				foreach($wizardItems[$key]['tt_content_defValues'] as $fN => $fV) {
					if (is_array($TCA['tt_content']['columns'][$fN])) {
						// Get information about if the field value is OK:
						$config = &$TCA['tt_content']['columns'][$fN]['config'];
						$authModeDeny = $config['type']=='select' && $config['authMode'] && !$GLOBALS['BE_USER']->checkAuthMode('tt_content',$fN,$fV,$config['authMode']);

						if ($authModeDeny || in_array($fV,$removeItems)) {
							// Remove element all together:
							unset($wizardItems[$key]);
							break;
						} else {
							// Add the parameter:
							$wizardItems[$key]['params'].= '&defVals[tt_content]['.$fN.']='.rawurlencode($fV);
							$tmp = explode('_', $key);
							$headersUsed[$tmp[0]] = $tmp[0];
						}
					}
				}
			}
		}

		// Remove headers without elements
		foreach ($wizardItems as $key => $cfg)	{
			list($itemCategory, $dummy) = explode('_', $key);
			if (!isset($headersUsed[$itemCategory]))
				unset($wizardItems[$key]);
		}
	}

	/**
	 * Create sql condition for given table to limit records according to user access.
	 *
	 * @param	string		$table	Table nme to fetch records from
	 * @return	string		Condition or empty string
	 */
	function buildRecordWhere($table) {
		$result = array();

		if (!$GLOBALS['BE_USER']->isAdmin()) {
			$prefLen = strlen($table) + 1;
			foreach($GLOBALS['BE_USER']->userGroups as $group) {
				$items = t3lib_div::trimExplode(',', $group['tx_templavoila_access'], 1);
				foreach ($items as $ref) {
					if (strstr($ref, $table)) {
						$result[] = intval(substr($ref, $prefLen));
					}
				}
			}
		}

		return (count($result) > 0 ? ' AND uid NOT IN (' . implode(',', $result) . ') ' : '');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/wizards/class.tx_templavoila_wizards_content.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/wizards/class.tx_templavoila_wizards_content.php']);
}

?>