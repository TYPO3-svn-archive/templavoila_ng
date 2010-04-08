<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2006 Niels Fröhling (niels@frohling.biz)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * Class 'tx_templavoila_datamap' for the templavoila extension.
 *
 * $Id: $
 *
 * @author     Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   55: class tx_templavoila_datamap
 *   64:     function recordEditAccessInternals($params, $ref)
 *  108:     function checkObjectAccess($table, $uid, $be_user)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Class being included by UserAuthGroup using a hook
 *
 * @author	Niels Fröhling <niels@frohling.biz>
 * @package TYPO3
 * @subpackage templavoila
 */
class tx_templavoila_datamap {

	/*************************
	 *
	 * Hook functions for TCEmain (invalidation of records)
	 *
	 *************************/

	/**
	 * TCEmain hook function for on-the-fly indexing of database records
	 *
	 * @param	string		TCEmain command
	 * @param	string		Table name
	 * @param	string		Record ID. If new record its a string pointing to index inside t3lib_tcemain::substNEWwithIDs
	 * @param	mixed		Target value (ignored)
	 * @param	object		Reference to tcemain calling object
	 * @return	void
	 */
	function processCmdmap_preProcess($command, $table, $id, $value, &$pObj) {

		// Branch, based on command
		switch ($command) {
			case 'move':
			case 'delete':
			case 'undelete':
				break;
			case 'copy':
			case 'localize':
			case 'inlineLocalizeSynchronize':
			case 'version':
				break;
		}
	}

	/**
	 * TCEmain hook function for on-the-fly indexing of database records
	 *
	 * @param	string		Status "new" or "update"
	 * @param	string		Table name
	 * @param	string		Record ID. If new record its a string pointing to index inside t3lib_tcemain::substNEWwithIDs
	 * @param	array		Field array of updated fields in the operation
	 * @param	object		Reference to tcemain calling object
	 * @return	void
	 */
	function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, &$pObj) {

		// Check if any fields are actually updated:
		if (count($fieldArray))	{

			// Translate new ids.
			if ($status == 'new') {
				$id = $pObj->substNEWwithIDs[$id];
			} else if ($table == 'pages' && $status=='update' && ((array_key_exists('hidden', $fieldArray) && $fieldArray['hidden'] == 1) || (array_key_exists('no_search',$fieldArray) && $fieldArray['no_search'] == 1))) {
				;
			}
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.tx_templavoila_datamap.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.tx_templavoila_datamap.php']);
}

?>