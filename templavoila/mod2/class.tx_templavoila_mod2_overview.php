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
 * @coauthor Niels Fröhling <niels@frohling.biz>
 */

/**
 * Submodule 'overview' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
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


	/**
	 * Renders module content, overview of pages with DS/TO on.
	 *
	 * @return	void
	 */
	function renderModuleContent_searchForTODS() {
		$this->content = '';

		// Select all Data Structures in the PID and put into an array:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'pid,count(*)',
			'tx_templavoila_datastructure',
			'pid>=0'.t3lib_BEfunc::deleteClause('tx_templavoila_datastructure'),
			'pid'
		);
		while($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
			$list[$row['pid']]['DS'] = $row['count(*)'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		// Select all Template Records in PID:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'pid,count(*)',
			'tx_templavoila_tmplobj',
			'pid>=0'.t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj'),
			'pid'
		);
		while($res && false !== ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)))	{
			$list[$row['pid']]['TO'] = $row['count(*)'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		// Traverse the pages found and list in a table:
		$tRows = array();
		$tRows[] = '
			<tr class="bgColor5 tableheader">
				<td>' . $GLOBALS['LANG']->getLL('list_storage') . '</td>
				<td>' . $GLOBALS['LANG']->getLL('list_dss') . ':</td>
				<td>' . $GLOBALS['LANG']->getLL('list_tos') . ':</td>
			</tr>';

		if (is_array($list)) {
			foreach($list as $pid => $stat)	{
				$path = $this->pObj->findRecordsWhereUsed_pid($pid);
				if ($path)	{
					$tRows[] = '
						<tr class="bgColor4">
							<td><a href="' . $this->pObj->baseScript . 'id=' . $pid . '" onclick="setHighlight(' . $pid . ');">' . htmlspecialchars($path) . '</a></td>
							<td>'.htmlspecialchars($stat['DS']).'</td>
							<td>'.htmlspecialchars($stat['TO']).'</td>
						</tr>';
				}
			}

			// Create overview
			$outputString  = '<p>' . $GLOBALS['LANG']->getLL('list_intro') . ':</p>';
			$outputString .= '<table border="0" cellpadding="1" cellspacing="1" class="lrPadding">' . implode('', $tRows) . '</table>';

			// Add output:
			$this->content .= $outputString;
		}

		return $this->content;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_overview.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_overview.php']);
}

?>