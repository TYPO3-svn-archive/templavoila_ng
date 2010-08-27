<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006  Robert Lemke (robert@typo3.org)
*  All rights reserved
*
*  script is part of the TYPO3 project. The TYPO3 project is
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
 * Submodule 'records' for the templavoila page module
 *
 * $Id$
 *
 * @author     Dmitry Dulepov <dmitry@typo3.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   58: class tx_templavoila_mod1_records
 *   73:     function init(&$pObj)
 *  100:     function sidebar_renderRecords()
 *  117:     function sidebar_renderTableSelector()
 *  151:     function sidebar_renderRecords()
 *  168:     function canDisplayTable($table)
 *  179:     function initDbList($table)
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once('class.tx_templavoila_mod1_recordlist.php');

/**
 * Submodule 'records' for the templavoila page module
 *
 * @author		Dmitry Dulepov <dmitry@typo3.org>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod1_records {

	var $pObj;	// Reference to parent module
	var $tables;
	var $calcPerms;

	var $dblist;

	/**
	 * Initializes sidebar object. Checks if there any tables to display and
	 * adds sidebar item if there are any.
	 *
	 * @param	object		$pObj	Parent object
	 * @return	void
	 */
	function init(&$pObj) {
		if (t3lib_div::int_from_ver(TYPO3_version) >= 4000005) {
			$this->pObj = &$pObj;
			$this->tables = array();

			// Get tables
			$tables = t3lib_div::trimExplode(',', $this->pObj->modTSconfig['properties']['recordDisplay_tables'], true);
			if ($tables) {
				// Get permissions
				$this->calcPerms = $GLOBALS['BE_USER']->calcPerms(t3lib_BEfunc::readPageAccess($this->pObj->id, $this->pObj->perms_clause));
				foreach ($tables as $table) {
					if ($this->canDisplayTable($table)) {
						$this->tables[] = $table;
					}
				}
			}

			// At least one displayable table found!
			if (count($this->tables)) {
				$this->pObj->sideBarObj->addItem('records', $this, 'sidebar_renderRecords', $GLOBALS['LANG']->getLL('records'), 25);
			}
		}
	}


	/**
	 * Displays a list of local content elements on the page which were NOT used in the hierarchical structure of the page.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	string		HTML output
	 * @access protected
	 */
	function sidebar_renderRecords() {
		// Render table selector
		return '
			<table border="0" cellpadding="0" cellspacing="1" class="lrPadding" width="100%">
			<thead>
				<tr class="bgColor4-20">
					<th colspan="3">&nbsp;</th>
				</tr>' .
				$this->sidebar_renderTableSelector() . '
			</thead>
			<tbody>' .
				$this->sidebar_renderRecordTables() . '
			</tbody>
			</table>
		';
	}

	/**
	 * Renders table selector.
	 *
	 * @return	string		Genrated content
	 */
	function sidebar_renderTableSelector() {
		$content  = '
			<tr class="bgColor4">
				<td width="20">&nbsp;</td>
				<td width="200">' . $GLOBALS['LANG']->getLL('displayRecordsFrom') . '</td>
				<td>
				' . $this->sidebar_renderTableSelector_pure() . '
				</td>
			</tr>';

		return $content;
	}

	function sidebar_renderTableSelector_pure() {
		$link = '\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&SET[recordsView_start]=0&SET[recordsView_table]=\'+this.options[this.selectedIndex].value';

		$content  = '
					<select onchange="document.location.href=' . $link . '">
						<option value=""' . ($this->pObj->MOD_SETTINGS['recordsView_table'] == '' ? ' selected="selected"' : '') . '></options>';

		foreach ($this->tables as $table) {
			$t = htmlspecialchars($table);
			t3lib_div::loadTCA($table);
			if ($this->canDisplayTable($table)) {
				$title = $GLOBALS['LANG']->sl($GLOBALS['TCA'][$table]['ctrl']['title']);

				$content .= '
						<option value="' . $t . '"' . ($this->pObj->MOD_SETTINGS['recordsView_table'] == $table ? ' selected="selected"' : '') . '>' .
							$title . ' (' . $t . ')' . '
						</option>';
			}
		}

		$content .= '
					</select>';

		if (!in_array($this->pObj->MOD_SETTINGS['recordsView_table'], $this->tables)) {
			unset($this->pObj->MOD_SETTINGS['recordsView_table']);
			unset($this->pObj->MOD_SETTINGS['recordsView_start']);
		}

		return $content;
	}

	/**
	 * Renders record list.
	 *
	 * @return	void
	 */
	function sidebar_renderRecordTables() {
		$content = '';

		if (($table = $this->pObj->MOD_SETTINGS['recordsView_table'])) {
			$content = '
				<tr class="bgColor4">
					<td colspan="3" style="padding: 0 0 3px 3px">' . $this->sidebar_renderTable($table) . '</td>
				</tr>';
		}

		return $content;
	}

	/**
	 * Renders record table.
	 *
	 * @return	void
	 */
	function sidebar_renderAllTables() {
		$content = '';

		foreach ($this->tables as $table) {
			$content .= $this->sidebar_renderTable($table);
		}

		return $content;
	}

	/**
	 * Renders record table.
	 *
	 * @return	void
	 */
	function sidebar_renderTable($table) {
		$content = '';

		if ($table && in_array($table, $this->tables)) {
			$this->initDbList($table);
			$this->dblist->generateList();

			$content = $this->dblist->HTMLcode;
		}

		return $content;
	}

	/**
	 * Checks if table can be displayed to the current user.
	 *
	 * @param	string		$table	Table name
	 * @return	boolean		<code>true</code> if table can be displayed.
	 */
	function canDisplayTable($table) {
		t3lib_div::loadTCA($table);

		return ($table != 'pages' && $table != 'tt_content' && isset($GLOBALS['TCA'][$table]) && $GLOBALS['BE_USER']->check('tables_select', $table));
	}

	/**
	 * Initializes List classes.
	 *
	 * @param	string		$table	Table name to show
	 * @return	void
	 */
	function initDbList($table) {
		// Initialize the dblist object:
		$this->dblist = t3lib_div::makeInstance('tx_templavoila_mod1_recordlist');
		$this->dblist->backPath = $this->pObj->doc->backPath;
		$this->dblist->calcPerms = $this->calcPerms;
		$this->dblist->thumbs = $GLOBALS['BE_USER']->uc['thumbnailsByDefault'];
		$this->dblist->returnUrl = $this->pObj->baseScript . $this->pObj->uri_getParameters();
		$this->dblist->allFields = true;
		$this->dblist->localizationView = true;
		$this->dblist->showClipboard = false;
		$this->dblist->disableSingleTableView = true;
		$this->dblist->listOnlyInSingleTableMode = false;
//		$this->dblist->clickTitleMode = $this->modTSconfig['properties']['clickTitleMode'];
		$this->dblist->alternateBgColors = (isset($this->pObj->MOD_SETTINGS['recordsView_alternateBgColors']) ? intval($this->pObj->MOD_SETTINGS['recordsView_alternateBgColors']) : false);
		$this->dblist->allowedNewTables = array($table);
		$this->dblist->newWizards = false;
		$this->dblist->tableList = $table;
		$this->dblist->itemsLimitPerTable = ($GLOBALS['TCA'][$table]['interface']['maxDBListItems'] ?
						$GLOBALS['TCA'][$table]['interface']['maxDBListItems'] :
						(intval($this->pObj->modTSconfig['properties']['recordDisplay_maxItems']) ?
						intval($this->pObj->modTSconfig['properties']['recordDisplay_maxItems']) : 10));
		$this->dblist->start($this->pObj);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_records.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_records.php']);
}

?>