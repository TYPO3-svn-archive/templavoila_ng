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

		// Wrapper for wizards
		$this->elementWrapper['sectionHeader'] = array('<h3 class="bgColor5">', '</h3>');
		$this->elementWrapper['section'] = array('<table border="0" cellpadding="1" cellspacing="2">', '</table>');
		$this->elementWrapper['wizard'] = array('<tr>', '</tr>');
		$this->elementWrapper['wizardPart'] = array('<td>', '</td>');

		// copy wrapper for tabs
		$this->elementWrapperForTabs = $this->elementWrapper;

		// Hook for manipulating wizardItems, wrapper, onClickEvent etc.
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['templavoila']['db_new_content_el']['wizardItemsHook'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['templavoila']['db_new_content_el']['wizardItemsHook'] as $classData) {
				$hookObject = t3lib_div::getUserObj($classData);

				if (!($hookObject instanceof cms_newContentElementWizardsHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface cms_newContentElementWizardItemsHook', 1227834741);
				}

				$hookObject->manipulateWizardItems($wizardItems, $this);
			}
		}

		if (($this->pObj->MOD_SETTINGS['set_rendermode'] == 'tabs') && ($this->elementWrapperForTabs != $this->elementWrapper)) {
			// restore wrapper for tabs if they are overwritten in hook
			$this->elementWrapper = $this->elementWrapperForTabs;
		}

		// add document inline javascript
		$this->doc->JScode .= $this->doc->wrapScriptTags('
			function goToalt_doc()	{	//
				' . $this->onClickEvent . '
			}

			if(top.refreshMenu) {
				top.refreshMenu();
			} else {
				top.TYPO3ModuleMenu.refreshMenu();
			}

			if(top.shortcutFrame) {
				top.shortcutFrame.refreshShortcuts();
			}
		');

		// Traverse items for the wizard.
		// An item is either a header or an item rendered with a title/description and icon:
		$counter = 0;
		foreach ($wizardItems as $k => $wInfo) {
			if ($wInfo['header']) {
				$menuItems[] = array (
					'label' => htmlspecialchars($wInfo['header']),
					'content' => $this->elementWrapper['section'][0]
				);

				$key = count($menuItems) - 1;
			} else {
				$content = '';

				// href URI for icon/title:
				$newRecordLink = $this->pObj->mod1Script . $this->linkParams() . '&createNewRecord=' . rawurlencode($this->parentRecord) . $wInfo['params'];
				if (t3lib_div::_GP('returnUrl'))
					$newRecordLink .= '&returnUrl=' . rawurlencode(t3lib_div::_GP('returnUrl'));

				// Icon:
				$iInfo = @getimagesize($wInfo['icon']);
				$content .=
					$this->elementWrapper['wizardPart'][0] . '
					<a href="' . htmlspecialchars($newRecordLink) . '">
						<img' . t3lib_iconWorks::skinImg($this->doc->backPath, $wInfo['icon'], '') . ' alt="" />
					</a>' .
					$this->elementWrapper['wizardPart'][1];

				// Title + description:
				$content .=
					$this->elementWrapper['wizardPart'][0] . '
					<a href="' . htmlspecialchars($newRecordLink) . '">
						<strong>' .
							htmlspecialchars($wInfo['title']) . '
						</strong>
						<br />' .
						nl2br(htmlspecialchars(trim($wInfo['description']))) . '
					</a>' .
					$this->elementWrapper['wizardPart'][1];

				// Finally, put it together in a table row:
				$menuItems[$key]['content'] .=
					$this->elementWrapper['wizard'][0] .
					$content .
					$this->elementWrapper['wizard'][1];

				$counter++;
			}
		}

		// Add closing section-tag
		foreach ($menuItems as $key => $val) {
			$menuItems[$key]['content'] .= $this->elementWrapper['section'][1];
		}

		// Add the wizard table to the content, wrapped in tabs:
		if ($this->pObj->MOD_SETTINGS['set_rendermode'] == 'tabs') {
			$this->doc->inDocStylesArray[] = '
				.typo3-dyntabmenu-divs { background-color: #fafafa; border: 1px solid #000; /*width: 680px;*/ }
				.typo3-dyntabmenu-divs table { margin: 15px; }
				.typo3-dyntabmenu-divs table td { padding: 3px; }
			';

			$code = $GLOBALS['LANG']->getLL('sel1', 1) . '<br /><br />' .

			$this->doc->getDynTabMenu($menuItems, 'new-content-element-wizard', false, false, 100);
		} else {
			$code = $GLOBALS['LANG']->getLL('sel1', 1) . '<br /><br />';

			foreach ($menuItems as $section) {
				$code .=
					$this->elementWrapper['sectionHeader'][0] .
					$section['label'] .
					$this->elementWrapper['sectionHeader'][1] .
					$section['content'];
			}
		}

		return $this->doc->section(!$this->onClickEvent ? $GLOBALS['LANG']->getLL('1_selectType') : '', $code, 0, 1);
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
	function wizardArray() {
		if (is_array($this->pObj->config)) {
			$wizards = $this->pObj->config['wizardItems.'];
		}

		$pluginWizards = $this->wizard_appendWizards($wizards['elements.']);
		$fceWizards = $this->wizard_renderFCEs($wizards['elements.']);
		$appendWizards = array_merge((array)$fceWizards, (array)$pluginWizards);

		$wizardItems = array();
		if (is_array($wizards)) {
			foreach ($wizards as $groupKey => $wizardGroup) {
				$groupKey = preg_replace('/\.$/', '', $groupKey);
				$showItems = t3lib_div::trimExplode(',', $wizardGroup['show'], true);
				$showAll = (strcmp($wizardGroup['show'], '*') ? false : true);
				$groupItems = array ();

				if (is_array($appendWizards[$groupKey . '.']['elements.'])) {
					$wizardElements = array_merge((array) $wizardGroup['elements.'], $appendWizards[$groupKey . '.']['elements.']);
				} else {
					$wizardElements = $wizardGroup['elements.'];
				}

				if (is_array($wizardElements)) {
					/* in the explicit case we do sort by given keys */
					if (count($showItems) && !$showAll)
						foreach ($showItems as $itemKey) {
							$itemConf = $wizardElements[$itemKey . '.'];
							if ($itemConf) {
								$tmpItem = $this->wizard_getItem($groupKey, $itemKey, $itemConf);
								if ($tmpItem) {
									$groupItems[$groupKey . '_' . $itemKey] = $tmpItem;
								}
							}
						}
					/* otherwise we just go ahead */
					else
						foreach ($wizardElements as $itemKey => $itemConf) {
							$itemKey = preg_replace('/\.$/', '', $itemKey);
							if ($showAll || in_array($itemKey, $showItems)) {
								$tmpItem = $this->wizard_getItem($groupKey, $itemKey, $itemConf);
								if ($tmpItem) {
									$groupItems[$groupKey . '_' . $itemKey] = $tmpItem;
								}
							}
						}
				}

				if (count($groupItems)) {
					$wizardItems[$groupKey] = $this->wizard_getGroupHeader($groupKey, $wizardGroup);
					$wizardItems = array_merge($wizardItems, $groupItems);
				}
			}
		}

		// Remove elements where preset values are not allowed:
		$this->removeInvalidElements($wizardItems);

		return $wizardItems;
	}

	/**
	 * Get wizard array for plugins
	 *
	 * @param array $wizardElements
	 * @return array $wizardItems
	 */
	function wizard_appendWizards($wizardElements) {
		if (!is_array($wizardElements)) {
			$wizardElements = array ();
		}

		// plugins
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses'])) {
			foreach ($GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses'] as $class => $path) {
				require_once ($path);
				$modObj = t3lib_div::makeInstance($class);
				$wizardElements = $modObj->proc($wizardElements);
			}
		}

		$wizardItems = array();
		foreach ($wizardElements as $key => $wizardItem) {
			preg_match('/^[a-zA-Z0-9]+_/', $key, $group);
			$wizardGroup = $group[0] ? substr($group[0], 0, - 1) . '.' : $key;
			$wizardItems[$wizardGroup]['elements.'][substr($key, strlen($wizardGroup)) . '.'] = $wizardItem;
		}

		return $wizardItems;
	}

	/**
	 * Get wizard array for FCEs
	 *
	 * @param array $wizardElements
	 * @return array $wizardItems
	 */
	function wizard_renderFCEs($wizardElements) {
		$wizardItems = array();
		if (!is_array($wizardElements)) {
			$wizardElements = array();
		}

		// Flexible content elements:
        	$positionPid = $this->id;
        	$dataStructureRecords = array();
        	$storageFolderPID = $this->apiObj->getStorageFolderPid($positionPid);

        	// Fetch data structures stored in the database:
        	$addWhere = $this->buildRecordWhere('tx_templavoila_datastructure');
        	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
        		'*',
        		'tx_templavoila_datastructure',
        		'pid=' . intval($storageFolderPID) . ' AND scope=' . TVDS_SCOPE_FCE . $addWhere .
        			t3lib_BEfunc::deleteClause('tx_templavoila_datastructure').
        			t3lib_BEfunc::versioningPlaceholderClause('tx_templavoila_datastructure')
        	);

		while (FALSE !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ) {
        		$dataStructureRecords[$row['uid']] = $row;
        	}
/*
        	// Fetch static data structures which are stored in XML files:
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures']))	{
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $staticDataStructureArr) {
				$staticDataStructureArr['_STATIC'] = TRUE;
				$dataStructureRecords[$staticDataStructureArr['path']] = $staticDataStructureArr;
			}
		}
*/
		// Fetch all template object records which uare based one of the previously fetched data structures:
		$templateObjectRecords = array();
		$recordDataStructure = array();
		$addWhere = $this->buildRecordWhere('tx_templavoila_tmplobj');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_templavoila_tmplobj',
			'pid=' . intval($storageFolderPID) . ' AND parent=0' . $addWhere .
				t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj') .
				t3lib_BEfunc::versioningPlaceholderClause('tx_templavoila_tmpl'), '', 'sorting'
		);

		while(FALSE !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			if (is_array($dsr = $dataStructureRecords[$rd = $row['datastructure']])) {
				$templateObjectRecords[] = $row;
				$recordDataStructure[$rd] = t3lib_div::xml2array($dsr['dataprot']);
			}
		}

		// Add the filtered set of TO entries to the wizard list:
        	foreach($templateObjectRecords as $index => $templateObjectRecord) {
        		$tmpFilename = 'uploads/tx_templavoila/' . $templateObjectRecord['previewicon'];

			// Get default values from datastructure
			$localProcessing = t3lib_div::xml2array($templateObjectRecord['localprocessing']);
			$defDSVals = $this->getDSDefaultValues($recordDataStructure[$templateObjectRecord['datastructure']], $localProcessing);

			$wizardItems['fce.']['elements.']['fce_' . $templateObjectRecord['uid'] . '.'] = array(
        			'icon'		=> (@is_file(PATH_site . $tmpFilename)) ? ('../' . $tmpFilename) : ('../' . t3lib_extMgm::siteRelPath('templavoila').'res1/default_previewicon.gif'),
        			'description'	=> $templateObjectRecord['description'] ? htmlspecialchars($templateObjectRecord['description']) : $GLOBALS['LANG']->getLL('template_nodescriptionavailable'),
        			'title'		=> $templateObjectRecord['title'],
        			'params'	=> '&defVals[tt_content][CType]=templavoila_pi1&defVals[tt_content][tx_templavoila_ds]=' . $templateObjectRecord['datastructure'] . '&defVals[tt_content][tx_templavoila_to]=' . $templateObjectRecord['uid'] . $defVals . $defDSVals,
			);

        		$index++;
        	}

		return $wizardItems;
	}

	function wizard_getItem($groupKey, $itemKey, $itemConf) {
		$itemConf['title'] = $GLOBALS['LANG']->sL($itemConf['title']);
		$itemConf['description'] = $GLOBALS['LANG']->sL($itemConf['description']);

		$itemConf['tt_content_defValues'] = $itemConf['tt_content_defValues.'];
		unset($itemConf['tt_content_defValues.']);

		return $itemConf;
	}

	function wizard_getGroupHeader($groupKey, $wizardGroup) {
		return array('header' => $GLOBALS['LANG']->sL($wizardGroup['header']));
 	}

	/**
	 * Get default values from DataStructure and merge it with TemplateObject
	 * @param array $dsStructure	DataStructure as array
	 * @param array $toStructure	LocalProcessing as array
	 * @return string	additional URL arguments with configured default values
	 */
	function getDSDefaultValues($dsStructure, $toStructure) {
		// if we've no datastructure information there's no need to proceed here
		if (!is_array($dsStructure))
			return '';

		// if available local processing needs to be merged
		if (is_array($toStructure)) {
			$dsStructure = t3lib_div::array_merge_recursive_overrule($dsStructure, $toStructure);
		}

		$dsValues = '';
		if (is_array($dsStructure['meta']['default']['TCEForms'])) {
			foreach ($dsStructure['meta']['default']['TCEForms'] as $field => $value) {
				$dsValues .= '&defVals[tt_content][' . $field . ']=' . $value;
			}
		}

		return $dsValues;
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
		foreach ($wizardItems as $key => $cfg) {
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
						$authModeDeny = $config['type'] == 'select' && $config['authMode'] && !$GLOBALS['BE_USER']->checkAuthMode('tt_content', $fN, $fV, $config['authMode']);

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