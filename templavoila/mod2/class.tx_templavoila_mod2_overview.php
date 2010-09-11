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
 * Submodule 'overview' for the templavoila control-center module
 *
 * @author   Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor Niels Fr�hling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   55: class tx_templavoila_mod2_overview
 *   71:     function init(&$pObj)
 *
 *              SECTION: Overview rendering
 *   92:     function renderSysFolders()
 *  164:     function cmp($a, $b)
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * Submodule 'overview' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fr�hling <niels@frohling.biz>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod2_overview {


	// References to the control-center module object
	var $pObj;	// A pointer to the parent object, that is the templavoila control-center module script. Set by calling the method init() of this class.
	var $doc;	// A reference to the doc object of the parent object.


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
		$this->pObj = &$pObj;
		$this->doc = &$this->pObj->doc;
		$this->extKey = &$this->pObj->extKey;
		$this->MOD_SETTINGS = &$this->pObj->MOD_SETTINGS;
	}


	/******************************
	 *
	 * Overview rendering
	 *
	 *****************************/


	/**
	 * Renders module content, overview of pages with DS/TO on.
	 *
	 * @return	content
	 */
	function renderFileFolders() {
		$this->content = '';

		return $this->content;
	}

	/**
	 * Renders module content, overview of pages with DS/TO on.
	 *
	 * @return	content
	 */
	function renderSysFolders() {
		$this->content = '';

		// -------------------------------------------------------------------------
		// Select all Data Structures in the PID and put into an array:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'pid, count(*)',
			'tx_templavoila_datastructure',
			'pid >= 0' . t3lib_BEfunc::deleteClause('tx_templavoila_datastructure'),
			'pid'
		);

		while ($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			$list[$row['pid']]['DS'] = $row['count(*)'];
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		// -------------------------------------------------------------------------
		// Select all Template Records in PID:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'pid, count(*)',
			'tx_templavoila_tmplobj',
			'pid >= 0' . t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj'),
			'pid'
		);

		while ($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			$list[$row['pid']]['TO'] = $row['count(*)'];
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		// -------------------------------------------------------------------------
		// Select all Sys-Folders:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid',
			'pages',
			'doktype = 254' . t3lib_BEfunc::deleteClause('pages') . ($this->pObj->perms_clause ? ' AND '. $this->pObj->perms_clause : ''),
			'uid'
		);

		while ($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			if (!$list[$row['uid']]['TO']) {
				$list[$row['uid']]['TO'] = '&mdash;';
			}
			if (!$list[$row['uid']]['DS']) {
				$list[$row['uid']]['DS'] = '&mdash;';
			}
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		// -------------------------------------------------------------------------
		// Traverse the pages found and list in a table:
		if (is_array($list)) {
			$tRows = array();

			/* get all paths */
			foreach ($list as $pid => &$stat) {
				if (($path = $this->pObj->getPIDPath($pid))) {
					$stat['path'] = $path;
					$stat['full'] = $path;
				}
			}

			/* get all common paths ------------------------------------------------- */
			$ladd = array();
			foreach ($list as $pid => &$stat) {
				$tree = explode('/', $stat['path']);
				$bcrm = '/';
				foreach ($tree as $frag) {
					if ($frag) {
						$bcrm .= $frag . '/';
						$ladd[] = array('path' => $bcrm);
					}
				}
			}
			$ladd = array_unique($ladd);

			function cmp($a, $b) {
				return strcmp($a['path'], $b['path']);
			}

			usort($ladd, cmp);

			for ($l =      0; $l < count($ladd) - 1; $l++) {
			for ($m = $l + 1; $m < count($ladd)    ; $m++) {
				if (strpos($ladd[$m]['path'], $ladd[$l]['path']) === 0) {
					$frag = str_replace($ladd[$l]['path'], '', $ladd[$m]['path']);
					$frag = explode('/', $frag);

					$ladd[$l]['num'] = count($frag) - 1 + $ladd[$l]['num'];
				}

			}
			}
			$ladd = array_values($ladd);

			for ($l = count($ladd) - 1; $l > 0; $l--) {
				if ($ladd[$l]['num'] <= 1)
					unset($ladd[$l]);
			}
			$ladd = array_values($ladd);

			for ($l =      0; $l < count($ladd) - 1; $l++) {
			for ($m = $l + 1; $m < count($ladd)    ; $m++) {
				if (strpos($ladd[$m]['path'], $ladd[$l]['path']) === 0) {
					if ($ladd[$l]['num'] == 1 + $ladd[$m]['num'])
						unset($ladd[$l]);
				}

			}
			}
			$ladd = array_values($ladd);

			for ($l = 0; $l < count($ladd); $l++) {
				$c = 0;
				foreach ($list as $pid => &$stat) {
					if ($stat['path'] == $ladd[$l]['path'])
						$c++;
				}

				if (!$c)
					$list[-$l] = $ladd[$l];
			}
			/* get all common paths ------------------------------------------------- */

			uasort($list, cmp);

			$i = 0; $indent = array();
			foreach ($list as $pid => &$stat) {
				if (($path = $stat['path'])) {
					$in = 0;
					foreach ($indent as $ip) {
						if (strpos($path, $ip) === 0)
							$in++;
						else
							break;
					}

					if ($in > 0)
						$path = str_replace($indent[$in - 1], '.../', $path);

					$tRows[] = '
						<tr class="' . ($i++ % 2 == 0 ? 'bgColor4' : 'bgColor6') . '">
							<td style="padding-left: ' . ($in * 10) . 'px">' . (($pid > 0)
							? '<a title="' . $stat['full'] . '" href="' . $this->pObj->baseScript . 'id=' . $pid . '" onclick="setHighlight(' . $pid . ');">' . htmlspecialchars($path) . '</a>'
							: '<span title="' . $stat['full'] . '">' . htmlspecialchars($path) . '</span>') . '
							</td>
							<td align="center">' . $stat['DS'] . '</td>
							<td align="center">' . $stat['TO'] . '</td>
						</tr>';

					$indent[$in] = $stat['path'];
				}
			}

			// Create overview
			if (count($tRows) > 0) {
				$outputString  = '
					<h3>' . $GLOBALS['LANG']->getLL('list_intro') . ':</h3>
					<table border="0" cellpadding="1" cellspacing="1" class="typo3-dblist typo3-tvlist">
					<colgroup>
						<col width="*"   align="left" />
						<col width="110" align="center" />
						<col width="110" align="center" />
					</colgroup>
					<thead>
					<tr class="t3-row-header c-headLineTable">
						<th>' . $GLOBALS['LANG']->getLL('list_storage') . '</th>
						<th>' . $GLOBALS['LANG']->getLL('list_dss') . ':</th>
						<th>' . $GLOBALS['LANG']->getLL('list_tos') . ':</th>
					</tr>
					</thead>
					<tbody>
						' . implode('', $tRows) . '
					</tbody>
					</table>';

				// Add output:
				$this->content .= $outputString;
			}
		}

		return $this->content;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_overview.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_overview.php']);
}

?>