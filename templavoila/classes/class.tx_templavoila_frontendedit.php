<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Jeff Segars (jeff@webempoweredchurch.org)
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


require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_api.php');

class tx_templavoila_frontendedit extends tx_feeditadvanced_frontendedit {
	
	var $apiObj;
	
	/**
	 * Initializes and saves configuration options and then refreshes TSFE
	 * with these new settings.
	 *
	 * @return	none
	 */
	public function initConfigOptions() {
		parent::initConfigOptions();
		$this->refreshTSFE();
	}
	
	/**
	 * Wrapper function for editAction in parent class.  Once edits are done,
	 * TSFE is refreshed to make sure that TV-specific data is updated.
	 *
	 * @return		none
	 */
	public function editAction() {
		parent::editAction();
		$this->refreshTSFE();
	}
	
//	case 'clearCache':
//		$this->clearCache();
//		break;
//
//	case 'createNewRecord':
//		// Historically "defVals" has been used for submitting the preset row data for the new element, so we still support it here:
//		$defVals = t3lib_div::_GP('defVals');
//		$newRow = is_array($defVals['tt_content']) ? $defVals['tt_content'] : array();
//
//		if (t3lib_div::_GP('returnUrl'))
//			$returnUrl = t3lib_div::_GP('returnUrl');
//		else
//			$returnUrl = $this->mod1Script . $this->uri_getParameters();
//
//		if (($newUid = $commandParameters) >= 0) {
//			/* revert selector-api valid flex-string to original one */
//			$destinationPointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID($commandParameters));
//
//			// Create new record and open it for editing
//			$newUid = $this->apiObj->insertElement($destinationPointer, $newRow);
//			$params = 'edit[tt_content][' .  $newUid . ']=edit';
//			// Don't enter edit-mode
//			if (intval($this->getMetaValue($newRow['tx_templavoila_ds'], $newRow['tx_templavoila_to'], 'noEditOnCreation', 0)) == 1) {
//				$redirectLocation = $returnUrl;
//			}
//		} else {
//			// Create a new elements via standard-means if not to be inserted into a flexform
//			$params = 'edit[tt_content][' . -$newUid . ']=new' . t3lib_div::implodeArrayForUrl('defVals', $defVals);
//		}
//
//		if (($redirectLocation != $returnUrl)) {
//			$redirectLocation = $GLOBALS['BACK_PATH'] . 'alt_doc.php?' . $params . '&returnUrl=' . rawurlencode($returnUrl);
//		}
//
//		break;
//
//	case 'unlinkRecord':
//		/* revert selector-api valid flex-string to original one */
//		$unlinkDestinationPointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID($commandParameters));
//
//		$this->apiObj->unlinkElement($unlinkDestinationPointer);
//		break;
//
//	case 'deleteRecord':
//		/* revert selector-api valid flex-string to original one */
//		$deleteDestinationPointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID($commandParameters));
//
//		$this->apiObj->deleteElement($deleteDestinationPointer);
//		break;
//
//	case 'pasteRecord':
//		/* revert selector-api valid flex-string to original one */
//		$sourcePointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID(t3lib_div::_GP('source')));
//		$destinationPointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID(t3lib_div::_GP('destination')));
//
//		switch ($commandParameters) {
//			case 'copy' :	$this->apiObj->copyElement($sourcePointer, $destinationPointer); break;
//			case 'copyref':	$this->apiObj->copyElement($sourcePointer, $destinationPointer, FALSE); break;
//			case 'cut':	$this->apiObj->moveElement($sourcePointer, $destinationPointer); break;
//			case 'ref':	list(,$uid) = explode(SEPARATOR_PARMS, jsID_to_tvID(t3lib_div::_GP('source')));
//					$this->apiObj->referenceElementByUid($uid, $destinationPointer);
//			break;
//		}
//
//		$destinationPointer['position'] = 1 + $destinationPointer['position'];
//
//		$GLOBALS['BE_USER']->setAndSaveSessionData('lastPasteRecord', $this->apiObj->flexform_getStringFromPointer($destinationPointer));
//		break;
//
//	case 'makeLocalRecord':
//		/* revert selector-api valid flex-string to original one */
//		$sourcePointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID($commandParameters));
//		$destinationPointer = $sourcePointer;
//
//		$sourceElement = $this->apiObj->flexform_getRecordByPointer($sourcePointer);
//		$tempPointer = array('table' => 'tt_content', 'uid' => $sourceElement['uid']);
//		$destinationPointer['position'] = $destinationPointer['position'] - 1;
//
//		$this->apiObj->unlinkElement($sourcePointer);
//		$this->apiObj->copyElement($tempPointer, $destinationPointer);
//		break;
//
//	case 'localizeElement':
//		/* revert selector-api valid flex-string to original one */
//		$sourcePointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID(t3lib_div::_GP('source')));
//
//		$this->apiObj->localizeElement($sourcePointer, $commandParameters);
//		break;
//
//	case 'createNewPageTranslation':
//		// Create parameters and finally run the classic page module for creating a new page translation
//		$params = '&edit[pages_language_overlay][' . intval(t3lib_div::_GP('pid')) . ']=new&overrideVals[pages_language_overlay][sys_language_uid]=' . intval($commandParameters);
//
//		if (t3lib_div::_GP('returnUrl'))
//			$returnUrl = '&returnUrl=' . rawurlencode(t3lib_div::_GP('returnUrl'));
//		else
//			$returnUrl = '&returnUrl=' . rawurlencode($this->mod1Script . $this->uri_getParameters());
//
//		$redirectLocation = $GLOBALS['BACK_PATH'] . 'alt_doc.php?' . $params . $returnUrl;
//		break;
//
//	case 'editPageLanguageOverlay':
//		// Look for pages language overlay record for language:
//		$sys_language_uid = intval($commandParameters);
//		$params = '';
//		if ($sys_language_uid != 0) {
//			// Edit overlay record
//			list($pLOrecord) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
//					'*',
//					'pages_language_overlay',
//					'pid=' . intval($this->id) . ' AND sys_language_uid=' . $sys_language_uid .
//						t3lib_BEfunc::deleteClause('pages_language_overlay') .
//						t3lib_BEfunc::versioningPlaceholderClause('pages_language_overlay')
//				);
//
//			if ($pLOrecord) {
//				t3lib_beFunc::workspaceOL('pages_language_overlay', $pLOrecord);
//				if (is_array($pLOrecord)) {
//					$params = '&edit[pages_language_overlay][' . $pLOrecord['uid'] . ']=edit';
//				}
//			}
//		} else {
//			// Edit default language (page properties)
//			// No workspace overlay because we already on this page
//			$params = '&edit[pages][' . intval($this->id) . ']=edit';
//		}
//
//		if ($params) {
//			if (t3lib_div::_GP('returnUrl'))
//				$returnUrl = '&returnUrl=' . rawurlencode(t3lib_div::_GP('returnUrl'));
//			else
//				$returnUrl = '&returnUrl=' . rawurlencode($this->mod1Script . $this->uri_getParameters());
//
//			$redirectLocation = $GLOBALS['BACK_PATH'] . 'alt_doc.php?' . $params . $returnUrl;	//.'&localizationMode=text';
//		}
//
//		break;

	public function doMoveAfter($table, $uid) {
		// Initialize TemplaVoila API class:
		$apiClassName = t3lib_div::makeInstanceClassName('tx_templavoila_api');
		$this->apiObj = new $apiClassName($table);
		
		$sourcePointerString = $GLOBALS['BE_USER']->frontendEdit->TSFE_EDIT['flexformPointer'];
		$destinationPointerString = $GLOBALS['BE_USER']->frontendEdit->TSFE_EDIT['destinationPointer'];

		/* revert selector-api valid flex-string to original one */
		$sourcePointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID($sourcePointerString));
		$destinationPointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID($destinationPointerString));
		
		// ?????????
		if ($table == 'tt_content') {
			$result = $this->apiObj->moveElement($sourcePointer, $destinationPointer);
		} else {
			parent::doMove($table, $uid);
		}
	}

	/**
	 *  Moves records when using TemplaVoila.
	 *
	 * @param		string		The name of the table that the record is within.
	 * @param		integer		The UID of the record to move.
	 * @param		string		The direction that the record should be moved ('up' or 'down')
	 * @param		object		The TCEMain object.
	 * @return		none
	 * @todo 		Jeff:  Need to get rid of this function or integrate it differently due to drag/drop.
	 */
	protected function move($table, $uid, $direction) {
		if ($table == 'tt_content') {
			$sourcePointerString = $GLOBALS['BE_USER']->frontendEdit->TSFE_EDIT['flexformPointer'];
			$sourcePointer = $this->flexform_getPointerFromString($sourcePointerString);

			$destinationPointerString = explode('/', $sourcePointerString, 2);
			$destinationPointer = $this->flexform_getPointerFromString($destinationPointerString[0]);
			if ($direction == 'up') {
				if ($destinationPointer['position'] > 1) {
					$destinationPointer['position'] = $destinationPointer['position'] - 2;
				}
			} else {
				$destinationPointer['position'] = $destinationPointer['position'] + 1;
			}

			$templaVoilaObj = $this->getTemplaVoilaObj($sourcePointer['table']);
			$result = $templaVoilaObj->moveElement($sourcePointer, $destinationPointer);
		} else {
			parent::doMove($table, $uid);
		}
	}	

	/**
	 * Pastes a record using TemplVoila.
	 *
	 * @return		none
	 * @todo 		Jeff: Completely untested!
	 */
	protected function doPaste() {
		// @todo 	Set table properly!
		$templaVoilaObj = $this->getTemplaVoilaObj();
		
		// @todo 	Need to figure how to actually pass the data in a standard way.
		$myPOST=t3lib_div::_POST(); 
		$sourcePointer = $this->flexform_getPointerFromString($myPOST['sourcePointer']);
		$destinationPointer = $this->flexform_getPointerFromString($myPOST['destinationPointer']);
			
		if(!t3lib_div::_GP('setCopyMode')) {
			$templaVoilaObj->moveElement_setElementReferences($sourcePointer, $destinationPointer);
		}
		elseif(intval(t3lib_div::_GP('setCopyMode')) == 1) {
			$templaVoilaObj->insertElement_setElementReferences($destinationPointer, $sourcePointer['uid']);
			$templaVoilaObj->copyElement($sourcePointer, $destinationPointer);
		} else {
			$templaVoilaObj->referenceElement($sourcePointer, $destinationPointer);
		}
	}

	/**
	 * Deletes a record.
	 *
	 * @return		none
	 */
	public function doDelete($table, $uid) {
		// Initialize TemplaVoila API class:
		$apiClassName = t3lib_div::makeInstanceClassName('tx_templavoila_api');
		$this->apiObj = new $apiClassName($table);
		
		$sourcePointerString = $GLOBALS['BE_USER']->frontendEdit->TSFE_EDIT['flexformPointer'];

		/* revert selector-api valid flex-string to original one */
		$sourcePointer = $this->apiObj->flexform_getPointerFromString(jsID_to_tvID($sourcePointerString));
		
		// ?????????
		if ($table == 'tt_content') {
		//	$this->apiObj->deleteElement($sourcePointer);
		
			// Unlinking rather than deleting to be consistent with TemplaVoila's backend interface.
			$this->apiObj->unlinkElement($sourcePointer);
		} else {
			parent::doDelete($table, $uid);
		}
	}

	/**
	 * Returns a string of files for JS includes for front-end editing
	 *
	 * @param		none
	 * @return		string
	 */
	public function getJavascriptIncludes() {
		// @todo move this to TV folder
		$incJS .= '<script type="text/javascript" src="' . t3lib_extMgm::siteRelPath('templavoila') . 'res/frontentedit.js"></script>';

		return $incJS;

	}
	
	/**
	 * Returns an associative array of keys and values that should be used as
	 * hidden form fields within an edit panel.
	 *
	 * @param		array		The data array for the edit panel.
	 * @return		array
	 */
	public function getHiddenFields2($dataArr) {
		$sourcePointerString = explode(':', $dataArr['flexformPointer']);

		// For the parent pointer, strip off the table and UID on the end.
		list($parentPointerString) = explode('/', $dataArr['flexformPointer']);

		$sourcePointerString = explode('/',$dataArr['flexformPointer']);
		$sourcePointerString = $sourcePointerString[0];

		return array (
			'flexformPointer' => $dataArr['flexformPointer'],
			'sourcePointer' => $sourcePointerString,
			'destinationPointer' => $parentPointerString,
			'setCopyMode' => t3lib_div::_GP('setCopyMode')
		);
		
	}

	/**
	 * Refreshes the TSFE to account for the fact that TV content elements are
	 * stored as part of the page record and are not completely standalone records.
	 *
	 * @return		none
	 */
	protected function refreshTSFE() {
		$GLOBALS['TSFE']->checkAlternativeIdMethods();
		$GLOBALS['TSFE']->clear_preview();
		$GLOBALS['TSFE']->determineId();
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feeditadvanced/templavoila/class.tx_templavoila_frontendedit.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/feeditadvanced/templavoila/class.tx_templavoila_frontendedit.php']);
}

?>