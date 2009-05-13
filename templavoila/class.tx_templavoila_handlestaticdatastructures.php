<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003 Kasper Skaarhoj (kasper@typo3.com)
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
 * Class/Function which manipulates the item-array for table/field tx_templavoila_tmplobj_datastructure.
 *
 * $Id: class.tx_templavoila_handlestaticdatastructures.php 15086 2008-12-19 13:18:09Z dmitry $
 *
 * @author    Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   58: class tx_templavoila_handleStaticDataStructures
 *   69:     function main(&$params,&$pObj)
 *   86:     function main_scope1(&$params,&$pObj)
 *  104:     function main_scope2(&$params,&$pObj)
 *  121:     function pi_templates(&$params,$pObj)
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

// Include class which contains the constants and definitions of TV
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_defines.php');

/**
 * Class/Function which manipulates the item-array for table/field tx_templavoila_tmplobj_datastructure.
 *
 * @author    Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_templavoila
 */
class tx_templavoila_handleStaticDataStructures {

	var $prefix = 'Static: ';
	var $iconPath = '../uploads/tx_templavoila/';

	/**
	 * Adds static data structures to selector box items arrays.
	 * Adds ALL available structures
	 *
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void
	 */
	function main(&$params, &$pObj) {
		// Adding an item!
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $val) {
				$params['items'][] = Array($this->prefix . (substr($val['title'], 0, 4) == 'LLL:' ? $GLOBALS['LANG']->sL($val['title']) : $val['title']), $val['path'], $val['icon']);
			}
		}
	}

	/**
	 * Adds static data structures to selector box items arrays.
	 * Adds only structures for Page Templates
	 *
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void
	 */
	function main_scope1(&$params, &$pObj) {
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $val) {
				if ($val['scope'] == TVDS_SCOPE_PAGE) {
					$params['items'][] = Array($this->prefix.$val['title'], $val['path'], $val['icon']);
				}
			}
		}

		tx_templavoila_handleStaticDataStructures::check_permissions($params, $pObj);
	}

	/**
	 * Adds static data structures to selector box items arrays.
	 * Adds only structures for Flexible Content elements
	 *
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void
	 */
	function main_scope2(&$params, &$pObj) {
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $val)	{
				if ($val['scope'] == TVDS_SCOPE_FCE) {
					$params['items'][] = Array($this->prefix.$val['title'], $val['path'], $val['icon']);
				}
			}
		}

		tx_templavoila_handleStaticDataStructures::check_permissions($params, $pObj);
	}

	/**
	 * Adds Template Object records to selector box for Content Elements of the "Plugin" type.
	 *
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void
	 */
	function pi_templates(&$params,$pObj)	{
		global $TYPO3_DB;

		// Find the template data structure that belongs to this plugin:
		$piKey = $params['row']['list_type'];
		// This should be a value of a Data Structure.
		$templateRef = $GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['piKey2DSMap'][$piKey];
		// This should be the Storage PID (at least if the pObj is TCEforms! and t3lib_transferdata is not triggering this function since it is not a real foreign-table thing...)
		$storagePid = intval($pObj->cachedTSconfig[$params['table'].':'.$params['row']['uid']]['_STORAGE_PID']);

		if ($templateRef && $storagePid) {
			// Load the table:
			t3lib_div::loadTCA('tx_templavoila_tmplobj');

			// Select all Template Object Records from storage folder, which are parent records and which has the data structure for the plugin:
			$res = $TYPO3_DB->exec_SELECTquery (
				'title,uid,previewicon',
				'tx_templavoila_tmplobj',
				'tx_templavoila_tmplobj.pid=' . $storagePid . ' AND tx_templavoila_tmplobj.datastructure='.$TYPO3_DB->fullQuoteStr($templateRef, 'tx_templavoila_tmplobj').' AND tx_templavoila_tmplobj.parent=0',
				'',
				'tx_templavoila_tmplobj.title'
			);

			// Traverse these and add them. Icons are set too if applicable.
			while(false != ($row=$TYPO3_DB->sql_fetch_assoc($res)))	{
				if ($row['previewicon']) {
					$icon = '../' . $GLOBALS['TCA']['tx_templavoila_tmplobj']['columns']['previewicon']['config']['uploadfolder'] . '/' . $row['previewicon'];
				} else {
					$icon = '';
				}

				$params['items'][]=Array($row['title'],$row['uid'],$icon);
			}
		}
	}

	/**
	 * Adds items to the template object selector according to the scope and
	 * storage folder of the current page/element.
	 *
	 * @param	array	$params	Parameters for itemProcFunc
	 * @param	t3lib_TCEforms	$pObj	Calling class
	 * @return	void
	 */
	function templateObjectItemsProcFunc(array &$params, t3lib_TCEforms &$pObj) {
		// Find DS scope
		$scope = ($params['table'] == 'pages' ? TVDS_SCOPE_PAGE : TVDS_SCOPE_FCE);

		// Get storage folder
		// This should be the Storage PID (at least if the pObj is TCEforms! and t3lib_transferdata is not triggering this function since it is not a real foreign-table thing...)
		$storagePid = intval($pObj->cachedTSconfig[$params['table'].':'.$params['row']['uid']]['_STORAGE_PID']);

		// Get all DSes from the current storage folder
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,title', 'tx_templavoila_datastructure',
					'scope=' . $scope . ' AND pid=' . $storagePid .
					self::enableFields('tx_templavoila_datastructure'),
					'', 'title');

		$this->dsList = array();
		foreach ($rows as $row) {
			$this->dsList[$row['uid']] = $row['title'];
		}
		unset($rows);

		if (count($this->dsList) > 0) {
			// Get all TOs for these DSes
			$this->toRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
						'uid,title,previewicon,datastructure', 'tx_templavoila_tmplobj',
						'datastructure IN (' . implode(',', array_keys($this->dsList)) . ')' .
						self::enableFields('tx_templavoila_tmplobj'));

			// Sort by DS name than by TO name
			uksort($this->toRows, array($this, 'sortTemplateObjects'));

			$currentDS = 0;
			$params['items'] = array(
				array(
					'', ''
				)
			);

			// Create items sorted visually by DS and title
			foreach ($this->toRows as $row) {
				// Check if we got a new DS
				if ($currentDS != $row['datastructure']) {
					$params['items'][] = array(
						$this->dsList[$row['datastructure']],
						'--div--'
					);
					$currentDS = $row['datastructure'];
				}

				// Add TO
				$icon = '';
				if ($row['previewicon']) {
					$icon = $this->iconPath . $row['previewicon'];
				}

				$params['items'][] = array(
					$row['title'],
					$row['uid'],
					$icon
				);
			}
		}

		unset($this->dsList);
		unset($this->toRows);
	}

	/**
	 * Provides 'enableFields' functionality while fixing some bugs in the older
	 * TYPO3 versions
	 *
	 * @param	string	$tableName	Table name
	 * @return	string	Additional WHERE expression (starting from ' AND') or empty string
	 */
	function enableFields($tableName) {
		$where1 = trim(t3lib_BEfunc::BEenableFields($tableName));
		if (strcasecmp($where1, 'AND') == 0) {
			$where1 = '';
		}

		$where2 = trim(t3lib_BEfunc::deleteClause($tableName));
		if (strcasecmp($where2, 'AND') == 0) {
			$where2 = '';
		}

		$where = trim($where1 . ' ' . $where2);
		return ($where ? ' ' . $where : '');
	}

	/**
	 * Sorts template objects by DS and than by title
	 *
	 * @param	int	$key1	Key 1 to $this->toRows
	 * @param	int	$key2	Key 2 to $this->toRows
	 * @return	int	Result of the comparison (see strcmp())
	 * @see	uksort()
	 * @see	strcmp()
	 */
	function sortTemplateObjects($key1, $key2) {
		$result = 0;

		$row1 = $this->toRows[$key1];
		$row2 = $this->toRows[$key2];

		if ($row1['datastructure'] == $row2['datastructure']) {
			$result = strcmp($row1['title'], $row2['title']);
		} else {
			$result = strcmp($this->dsList[$row1['datastructure']], $this->dsList[$row2['datastructure']]);
		}

		return $result;
	}

	/**
	 * Adds static data structures to selector box items arrays.
	 * Adds only structures for Page Templates
	 *
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void
	 */
	function check_permissions(&$params,&$pObj) {
		global	$BE_USER;

		if ($BE_USER->isAdmin()) {
			return;
		}

		foreach ($BE_USER->userGroups as $group) {
			// Get list of DS & TO
			$items = t3lib_div::trimExplode(',', $group['tx_templavoila_access'], true);

			foreach ($items as $ref) {
				if (strstr($ref, 'tx_templavoila_tmplobj_')) {
					$value1 = $params['row']['tx_templavoila_to'];
					$value2 = ($params['table'] == 'pages' ? $params['row']['tx_templavoila_next_to'] : -1);
					$test = substr($ref, 23);
				}
				else {
					$value1 = $params['row']['tx_templavoila_ds'];
					$value2 = ($params['table'] == 'pages' ? $params['row']['tx_templavoila_next_ds'] : -1);
					$test = substr($ref, 29);
				}

				if ($test == $value1 || $test == $value2) {
					continue;
				}

				foreach ($params['items'] as $key => $item) {
					if ($item[1] == $test) {
						unset($params['items'][$key]);
					}
				}
			}
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.tx_templavoila_handlestaticdatastructures.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.tx_templavoila_handlestaticdatastructures.php']);
}
?>
