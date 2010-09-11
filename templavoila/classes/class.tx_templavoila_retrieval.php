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
 * $Id: class.tx_templavoila_retrieval.php 17762 2009-03-13 12:24:13Z steffenk $
 *
 * @author    Kasper Skaarhoj <kasper@typo3.com>
 * @coauthor  Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   58: class tx_templavoila_retrieval
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
require_once(t3lib_extMgm::extPath('templavoila') . 'ext_defines.php');

/**
 * Class/Function which manipulates the item-array for table/field tx_templavoila_tmplobj_datastructure.
 *
 * @author    Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_templavoila
 */
class tx_templavoila_retrieval {


	var $prefix = 'Static: ';
	var $iconPath = '../uploads/tx_templavoila/';


	function getDataStructureBody($srcPointer, &$DSrec, $BE_MODE = FALSE) {
		$xmlContent = FALSE;

		if (t3lib_div::testInt($srcPointer)) {
			// If integer, then its a record we will look up:
		//	if (TYPO3_MODE == 'BE') {
			if ($BE_MODE)
				$DSrec = t3lib_BEfunc::getRecord('tx_templavoila_datastructure', $srcPointer);
			else
				$DSrec = $GLOBALS['TSFE']->sys_page->checkRecord('tx_templavoila_datastructure', $srcPointer);

			if ($DSrec) {
				$xmlContent = $DSrec['dataprot'];
			}
		} else {
			// Otherwise expect it to be a file:
			$DSfile = t3lib_div::getFileAbsFileName($srcPointer);
			if ($DSfile && @is_file($DSfile)) {
				$xmlContent = t3lib_div::getUrl($DSfile);
			}
		}

		return ($xmlContent ? t3lib_div::xml2array($xmlContent) : FALSE);
	}

	/**
	 * Returns an array of available Page Template Object records from the scope of the given page.
	 *
	 * Note: All TO records which are found in the selected storage folder will be returned, no matter
	 *       if they match the currently selected data structure for the given page.
	 *
	 * @param	integer		$pageUid: (current) page uid, used for finding the correct storage folder
	 * @return	mixed		Array of Template Object records or FALSE if an error occurred.
	 * @access public
	 */
	function getAvailableFCEDSRecords($positionPid) {
		// Flexible content elements:
        	$dataStructureRecords = array();

		/* database-based dataStructures */
		if (1) {
        		$storageFolderPID = $this->getStoragePidsNoTCERecursive($positionPid);

			if (count($storageFolderPID) > 0) {
        			// Fetch data structures stored in the database:
				$tDS = 'tx_templavoila_datastructure';
				$where = $tDS . '.pid IN (' . implode(',', $storageFolderPID) . ')' .
					 ' AND ' . $tDS . '.scope = ' . TVDS_SCOPE_FCE .
						$this->buildRecordWhere($tDS) .
						t3lib_befunc::deleteClause($tDS).
						t3lib_BEfunc::versioningPlaceholderClause($tDS);

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					$tDS . '.*',
					$where
				);

				if ($res) {
        				while(FALSE !== ($row = $TYPO3_DB->sql_fetch_assoc($res))) {
        					$dataStructureRecords[$row['uid']] = $row;
        				}
        			}
        		}
        	}

		/* file-based dataStructures */
		if (1) {
        		// Fetch static data structures which are stored in XML files:
			if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
				foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $staticDataStructureArr)	{
					$staticDataStructureArr['_STATIC'] = TRUE;
					$dataStructureRecords[$staticDataStructureArr['path']] = $staticDataStructureArr;
				}
			}
        	}

		return count($dataStructureRecords) ? $dataStructureRecords : FALSE;
        }

	/**
	 * Returns an array of available Page Template Object records from the scope of the given page.
	 *
	 * Note: All TO records which are found in the selected storage folder will be returned, no matter
	 *       if they match the currently selected data structure for the given page.
	 *
	 * @param	integer		$pageUid: (current) page uid, used for finding the correct storage folder
	 * @return	mixed		Array of Template Object records or FALSE if an error occurred.
	 * @access public
	 */
	function getAvailablePageTORecords($positionPid) {
		$templateObjectRecords = array();

		/* database-based templateObjects */
		if (1) {
			$storageFolderPID = $this->getStoragePidsNoTCERecursive($positionPid);

			if (count($storageFolderPID) > 0) {
				$tTO = 'tx_templavoila_tmplobj';
				$tDS = 'tx_templavoila_datastructure';
				$where = $tTO . '.parent = 0 AND ' .
					 $tTO . '.pid IN (' . implode(',', $storageFolderPID) . ')' .
					 ' AND ' . $tDS . '.scope = ' . TVDS_SCOPE_PAGE .
						$this->buildRecordWhere($tTO) .
						$this->buildRecordWhere($tDS) .
						t3lib_befunc::deleteClause($tTO) .
						t3lib_befunc::deleteClause($tDS).
						t3lib_BEfunc::versioningPlaceholderClause($tTO) .
						t3lib_BEfunc::versioningPlaceholderClause($tDS);

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					$tTO . '.*',
					$tTO . ' LEFT JOIN ' . $tDS . ' ON ' .
					$tTO . '.datastructure = ' . $tDS . '.uid',
					$where
				);

				if ($res) {
					while (false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
						$templateObjectRecords[$row['uid']] = $row;
					}
				}
			}
		}

		return count($templateObjectRecords) ? $templateObjectRecords : FALSE;
	}

	function getAvailableFCETORecords($positionPid) {
		$templateObjectRecords = array();

		/* database-based templateObjects */
		if (1) {
			$storageFolderPID = $this->getStoragePidsNoTCERecursive($positionPid);

			if (count($storageFolderPID) > 0) {
        			// Fetch data structures stored in the database:
				$tTO = 'tx_templavoila_tmplobj';
				$where = $tTO . '.pid IN (' . implode(',', $storageFolderPID) . ')' .
						$this->buildRecordWhere($tTO) .
						t3lib_befunc::deleteClause($tTO).
						t3lib_BEfunc::versioningPlaceholderClause($tTO);

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					$tTO . '.*',
					$tTO . '.parent = 0 AND ',
					$where
				);

				if ($res) {
        				while(FALSE !== ($row = $TYPO3_DB->sql_fetch_assoc($res))) {
						$templateObjectRecords[/*$row['uid']*/] = $row;
        				}
        			}
        		}
        	}

		return count($templateObjectRecords) ? $templateObjectRecords : FALSE;
	}

	/**
	 * Adds static data structures to selector box items arrays.
	 * Adds ALL available structures
	 *
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void
	 */
	function dataStructureItemsProcFunc(&$params, &$pObj) {
		// Adding an item!
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $val)	{
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
	function dataStructureItemsProcFuncForPages(&$params, &$pObj) {
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $val)	{
				if ($val['scope'] == TVDS_SCOPE_PAGE) {
					$params['items'][] = Array($this->prefix . $val['title'], $val['path'], $val['icon']);
				}
			}
		}

		$this->check_permissions($params, $pObj);
	}

	/**
	 * Adds static data structures to selector box items arrays.
	 * Adds only structures for Flexible Content elements
	 *
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void
	 */
	function dataStructureItemsProcFuncForFCEs(&$params, &$pObj) {
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $val)	{
				if ($val['scope'] == TVDS_SCOPE_FCE) {
					$params['items'][] = Array($this->prefix . $val['title'], $val['path'], $val['icon']);
				}
			}
		}

		$this->check_permissions($params, $pObj);
	}

	/**
	 * Adds Template Object records to selector box for Content Elements of the "Plugin" type.
	 *
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void
	 */
	function pluginTemplateItemsProcFunc(&$params, $pObj) {
		// Find the template data structure that belongs to this plugin:
		$piKey = $params['row']['list_type'];

		// This should be a value of a Data Structure.
		$templateRef = $GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['piKey2DSMap'][$piKey];

	//	$storagePid = $this->getStoragePid($params, $pObj);
		$storagePids = $this->getStoragePidsRecursive($params, $pObj);

		if ($templateRef && $storagePids) {
			// Load the table:
			t3lib_div::loadTCA('tx_templavoila_tmplobj');

			// Select all Template Object Records from storage folder, which are parent records and which has the data structure for the plugin:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
				'title,uid,previewicon',
				'tx_templavoila_tmplobj',
				(count($storagePids) > 1
				?	'FIELD(tx_templavoila_tmplobj.pid, ' . implode(',', $storagePids) . ') > 0'
				:	'tx_templavoila_tmplobj.pid=' . $storagePids[0]
				) . ' AND ' .
				'tx_templavoila_tmplobj.datastructure=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($templateRef, 'tx_templavoila_tmplobj') . ' AND ' .
				'tx_templavoila_tmplobj.parent=0',
				'',
				'tx_templavoila_tmplobj.title'
			);

			// Traverse these and add them. Icons are set too if applicable.
			while(false != ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
				if ($row['previewicon']) {
					$icon = '../' . $GLOBALS['TCA']['tx_templavoila_tmplobj']['columns']['previewicon']['config']['uploadfolder'] . '/' . $row['previewicon'];
				} else {
					$icon = '';
				}

				$params['items'][] = Array($row['title'], $row['uid'], $icon);
			}
		}
	}

	/**
	 * Creates the DS selector box. This function takes into account TS
	 * config override of the GRSP.
	 *
	 * @param	array	$params	Parameters to the itemsProcFunc
	 * @param	t3lib_TCEforms	$pObj	Calling object
	 * @return	void
	 */
	function dataSourceItemsProcFunc(array &$params, t3lib_TCEforms& $pObj) {

		// Get all DSes
		$dsList = $this->getDSList($params, $pObj);
		$params['items'] = array(
			array(
				'', ''
			)
		);

		foreach ($dsList as $dsRecord) {
			$icon = '';
			if ($dsRecord['previewicon']) {
				$icon = $this->iconPath . $dsRecord['previewicon'];
			}

			$params['items'][] = array(
				$dsRecord['title'],
				$dsRecord['uid'],
				$icon
			);
		}

		// Call functions to add static data structures
		if ($params['table'] == 'pages') {
			$this->dataStructureItemsProcFuncForPages($params, $pObj);
		} else {
			$this->dataStructureItemsProcFuncForFCEs($params, $pObj);
		}
	}

	/**
	 * Adds items to the template object selector according to the current
	 * extension mode.
	 *
	 * @param	array	$params	Parameters for itemProcFunc
	 * @param	t3lib_TCEforms	$pObj	Calling class
	 * @return	void
	 */
	function templateObjectItemsProcFunc(array &$params, t3lib_TCEforms &$pObj) {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['templavoila']);
		if ($conf['enable.']['selectDataSource']) {
			$this->templateObjectItemsProcFuncForCurrentDS($params, $pObj);
		} else {
			$this->templateObjectItemsProcFuncForAllDSes($params, $pObj);
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
	function templateObjectItemsProcFuncForCurrentDS(array &$params, t3lib_TCEforms &$pObj) {
		// Get DS
		$tsConfig = &$pObj->cachedTSconfig[$params['table'] . ':' . $params['row']['uid']];
		$dataSource = $tsConfig['_THIS_ROW']['tx_templavoila_ds'];

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid,title,previewicon,datastructure',
			'tx_templavoila_tmplobj',
			'datastructure=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($dataSource, 'tx_templavoila_tmplobj') .
			self::enableFields('tx_templavoila_tmplobj')
		);

		$params['items'] = array(
			array(
				'', ''
			)
		);

		// Create items sorted visually by DS and title
		foreach ($rows as $row) {
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

	/**
	 * Adds items to the template object selector according to the scope and
	 * storage folder of the current page/element.
	 *
	 * @param	array	$params	Parameters for itemProcFunc
	 * @param	t3lib_TCEforms	$pObj	Calling class
	 * @return	void
	 */
	function templateObjectItemsProcFuncForAllDSes(array &$params, t3lib_TCEforms &$pObj) {
		// Find DSes
		$this->dsList = $this->getDSList($params, $pObj);

		if (count($this->dsList) > 0) {
			// Get all TOs for these DSes
			$this->toRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'uid,title,previewicon,datastructure', 'tx_templavoila_tmplobj',
				'datastructure IN (' . implode(',', array_keys($this->dsList)) . ')' .
				self::enableFields('tx_templavoila_tmplobj')
			);

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
					$currentDS = $row['datastructure'];

					$params['items'][] = array(
						$this->dsList[$row['datastructure']]['title'],
						'--div--'
					);
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
	 * Retrieves DS list for the current itemsProcFunc parameters
	 *
	 * @param	array	$params	Parameters to the itemsProcFunc
	 * @param	t3lib_TCEforms	$pObj	Calling function
	 * @return	array	Rows with DSes (uid, title, previewicon)
	 */
	function getDSList(array &$params, t3lib_TCEforms& $pObj) {
		// Find DS scope
		$scope = ($params['table'] == 'pages' ? TVDS_SCOPE_PAGE : TVDS_SCOPE_FCE);

		// Get storage folder
	//	$storagePid = $this->getStoragePid($params, $pObj);
		$storagePids = $this->getStoragePidsRecursive($params, $pObj);

		// Get all DSes from the current storage folder
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, title, previewicon',
			'tx_templavoila_datastructure',
			'scope = ' . $scope . ' AND ' .
			(count($storagePids) > 1
			?	'FIELD(pid, ' . implode(',', $storagePids) . ') > 0'
			:	'pid = ' . $storagePids[0]
			) .
			self::enableFields('tx_templavoila_datastructure'),
			'', 'title', '', 'uid'
		);

		return $rows;
	}

	/**
	 * Retrieves DS/TO storage pid for the current page. This function expects
	 * to be called from the itemsProcFunc only!
	 *
	 * @param	array	$params	Parameters as come to the itemsProcFunc
	 * @param	t3lib_TCEforms	$pObj	Calling object
	 * @return	int	Storage pid
	 */
	function getStoragePid(array &$params, t3lib_TCEforms &$pObj) {
		// Get default first
		$tsConfig = &$pObj->cachedTSconfig[$params['table'] . ':' . $params['row']['uid']];
		$storagePid = intval($tsConfig['_STORAGE_PID']);

		// Check for alternative storage folder
		$field = $params['table'] == 'pages' ? 'uid' : 'pid';
		$modTSConfig = t3lib_BEfunc::getModTSconfig($params['row'][$field], 'tx_templavoila.storagePid');
		if (is_array($modTSConfig) && t3lib_div::testInt($modTSConfig['value'])) {
			$storagePid = intval($modTSConfig['value']);
		}

		return $storagePid;
	}

	/**
	 * Retrieves all DS/TO storage pid for the current page, walking till root. This function expects
	 * to be called from the itemsProcFunc only!
	 *
	 * @param	array	$params	Parameters as come to the itemsProcFunc
	 * @param	t3lib_TCEforms	$pObj	Calling object
	 * @return	int	Storage pid
	 */
	function getStoragePidsRecursive(array &$params, t3lib_TCEforms &$pObj) {
		$storagePids = array();

		/* collect storagePids all over the rootline (including TS-modifications) */
		do {
			$pObj->setTSconfig($params['table'], $params['row']['uid']);
			if (($storagePid = $this->getStoragePid($params, $pObj)))
				$storagePids[] = $storagePid;

			$params['row'] = t3lib_BEfunc::getRecordWSOL($params['table'], $params['row']['pid']);
			$params['table'] = 'pages';
		} while($params['row']);

		return array_unique($storagePids);
	}

	/**
	 * Retrieves DS/TO storage pid for the current page. This function expects
	 * to be called from the itemsProcFunc only!
	 *
	 * @param	array	$params	Parameters as come to the itemsProcFunc
	 * @param	t3lib_TCEforms	$pObj	Calling object
	 * @return	int	Storage pid
	 */
	function getStoragePidNoTCE($row, $pageUid) {
		// Get default first
		$tsConfig = t3lib_BEfunc::getTCEFORM_TSconfig('pages', $row);
		$storagePid = intval($tsConfig['_STORAGE_PID']);

		// Check for alternative storage folder
		$modTSConfig = t3lib_BEfunc::getModTSconfig($pageUid, 'tx_templavoila.storagePid');
		if (is_array($modTSConfig) && t3lib_div::testInt($modTSConfig['value'])) {
			$storagePid = intval($modTSConfig['value']);
		}

		return $storagePid;
	}

	/**
	 * Returns the page uid of the selected storage folder from the context of the given page uid.
	 *
	 * @param	integer		$pageUid: Context page uid
	 * @return	integer		PID(s) of the storage folder
	 * @access public
	 */
	function getStoragePidsNoTCERecursive($pageUid) {
		$storagePids = array();

		// Negative PID values is pointing to a page on the same level as the current.
		if ($pageUid < 0) {
			$pidRow = t3lib_BEfunc::getRecordWSOL('pages', abs($pageUid), 'pid');
			$pageUid = $pidRow['pid'];
		}

		/* collect storagePids all over the rootline (including TS-modifications) */
		do {
			$row = t3lib_BEfunc::getRecordWSOL('pages', $pageUid);
			if (($storagePid = $this->getStoragePidNoTCE($row, $pageUid)))
				$storagePids[] = $storagePid;
		} while(($pageUid = $row['pid']));

		return array_unique($storagePids);
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
			$result = strcmp($this->dsList[$row1['datastructure']]['title'], $this->dsList[$row2['datastructure']]['title']);
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
				} else {
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

	/**
	 * Create sql condition for given table to limit records according to user access.
	 *
	 * @param	string	$table	Table nme to fetch records from
	 * @return	string	Condition or empty string
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

		return (count($result) > 0 ? ' AND ' . $table . '.uid NOT IN (' . implode(',', $result) . ') ' : '');
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/classes/class.tx_templavoila_retrieval.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/classes/class.tx_templavoila_retrieval.php']);
}
?>
