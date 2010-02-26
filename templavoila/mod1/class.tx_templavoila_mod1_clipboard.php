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
 * Submodule 'clipboard' for the templavoila page module
 *
 * $Id: class.tx_templavoila_mod1_clipboard.php 5928 2007-07-12 11:20:33Z kasper $
 *
 * @author     Robert Lemke <robert@typo3.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   55: class tx_templavoila_mod1_clipboard
 *   72:     function init(&$pObj)
 *  116:     function element_getSelectButtons($elementPointer, $listOfButtons = 'copy,cut,ref')
 *  182:     function element_getPasteButtons($destinationPointer)
 *  246:     function sidebar_renderNonUsedElements()
 *  336:     function renderReferenceCount($uid)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Submodule 'clipboard' for the templavoila page module
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod1_clipboard {

	// References to the page module object
	var $pObj;			// A pointer to the parent object, that is the templavoila page module script. Set by calling the method init() of this class.
	var $doc;			// A reference to the doc object of the parent object.

	/**
	 * Initializes the clipboard object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila page module.
	 *
	 * Also takes the GET variable "CB" and submits it to the t3lib clipboard class which handles all
	 * the incoming information and stores it in the user session.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	void
	 * @access	public
	 */
	function init(&$pObj) {
		global $BACK_PATH;

		// Make local reference to some important variables:
		$this->pObj =& $pObj;
		$this->doc =& $this->pObj->doc;
		$this->extKey =& $this->pObj->extKey;
		$this->MOD_SETTINGS =& $this->pObj->MOD_SETTINGS;

		// Initialize the t3lib clipboard:
		$this->t3libClipboardObj = t3lib_div::makeInstance('t3lib_clipboard');
		$this->t3libClipboardObj->backPath = $BACK_PATH;
		$this->t3libClipboardObj->initializeClipboard();
		$this->t3libClipboardObj->lockToNormal();

		// Clipboard actions are handled:
		$CB = t3lib_div::_GP('CB');			// CB is the clipboard command array
		$this->t3libClipboardObj->setCmd($CB);		// Execute commands.

		if (isset($CB['setFlexMode'])) {
			switch ($CB['setFlexMode']) {
				case 'copy':    $this->t3libClipboardObj->clipData['normal']['flexMode'] = 'copy'; break;
				case 'cut':     $this->t3libClipboardObj->clipData['normal']['flexMode'] = 'cut'; break;
				case 'ref':     $this->t3libClipboardObj->clipData['normal']['flexMode'] = 'ref'; break;
				default: unset ($this->t3libClipboardObj->clipData['normal']['flexMode']); break;
			}
		}

		$this->t3libClipboardObj->cleanCurrent();	// Clean up pad
		$this->t3libClipboardObj->endClipboard();	// Save the clipboard content

		// Add a list of non-used elements to the sidebar:
		$this->pObj->sideBarObj->addItem('nonUsedElements', $this, 'sidebar_renderNonUsedElements', $GLOBALS['LANG']->getLL('nonusedelements'), 30);
	}

	/**
	 * Renders the copy, cut and reference buttons for the element specified by the
	 * flexform pointer.
	 *
	 * @param	array		$elementPointer: Flex form pointer specifying the element we want to render the buttons for
	 * @param	string		$listOfButtons: A comma separated list of buttons which should be rendered. Possible values: 'copy', 'cut' and 'ref'
	 * @return	string		HTML output: linked images which act as copy, cut and reference buttons
	 * @access	public
	 */
	function element_getSelectButtons($elementPointer, $listOfButtons = 'copy,cut,ref') {
		global $LANG;

		$clipActive_copy = $clipActive_cut = $clipActive_ref = FALSE;
		if (!$elementPointer = $this->pObj->apiObj->flexform_getValidPointer($elementPointer)) return '';
		$elementRecord = $this->pObj->apiObj->flexform_getRecordByPointer($elementPointer);

		// Fetch the element from the "normal" clipboard (if any) and set the button states accordingly:
		if (is_array($this->t3libClipboardObj->clipData['normal']['el'])) {
		       reset($this->t3libClipboardObj->clipData['normal']['el']);
			list ($clipboardElementTableAndUid, $clipboardElementPointerString) = each($this->t3libClipboardObj->clipData['normal']['el']);
			$clipboardElementPointer = $this->pObj->apiObj->flexform_getValidPointer($clipboardElementPointerString);

			// If we have no flexform reference pointing to the element, we create a short flexform pointer pointing to the record directly:
			if (!is_array($clipboardElementPointer)) {
				list ($clipboardElementTable, $clipboardElementUid) = explode('|', $clipboardElementTableAndUid);

				$clipboardElementPointer = array(
					'table' => 'tt_content',
					'uid' => $clipboardElementUid
				);

				$pointToTheSameRecord = ($elementRecord['uid'] == $clipboardElementUid);
			} else {
				unset($clipboardElementPointer['targetCheckUid']);
				unset($elementPointer['targetCheckUid']);

				$pointToTheSameRecord = ($clipboardElementPointer == $elementPointer);
			}

			// Set whether the current element is selected for copy/cut/reference or not:
			if ($pointToTheSameRecord) {
				$selectMode = isset ($this->t3libClipboardObj->clipData['normal']['flexMode']) ? $this->t3libClipboardObj->clipData['normal']['flexMode'] : ($this->t3libClipboardObj->clipData['normal']['mode'] == 'copy' ? 'copy' : 'cut');

				$clipActive_copy = ($selectMode == 'copy');
				$clipActive_cut  = ($selectMode == 'cut');
				$clipActive_ref  = ($selectMode == 'ref');
			}
		}

		$copyIcon = '<img' . t3lib_iconWorks::skinImg($this->pObj->doc->backPath, 'gfx/clip_copy' . ($clipActive_copy ? '_h' : '') . '.gif', '') . ' title="' . $LANG->getLL('copyrecord') . '" border="0" alt="" />';
		$cutIcon  = '<img' . t3lib_iconWorks::skinImg($this->pObj->doc->backPath, 'gfx/clip_cut' . ($clipActive_cut ? '_h' : '') . '.gif', '') . ' title="' . $LANG->getLL('cutrecord') . '" border="0" alt="" />';
		$refIcon  = '<img' . t3lib_iconWorks::skinImg($this->pObj->doc->backPath, t3lib_extMgm::extRelPath('templavoila') . 'mod1/clip_ref' . ($clipActive_ref ? '_h' : '') . '.gif', '') . ' title="' . $LANG->getLL('createreference') . '" border="0" alt="" />';

		$removeElement = '&amp;CB[removeAll]=normal';
		$setElement    = '&amp;CB[el][' . rawurlencode('tt_content|' . $elementRecord['uid']) . ']=' . rawurlencode($this->pObj->apiObj->flexform_getStringFromPointer($elementPointer));
		$setElementRef = '&amp;CB[el][' . rawurlencode('tt_content|' . $elementRecord['uid']) . ']=1';
		$setElementID  = tvID_to_jsID($this->pObj->apiObj->flexform_getStringFromPointer($elementPointer));

		$linkCopy = '<a href="' . $this->pObj->baseScript . $this->pObj->link_getParameters() . '&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=copy' . ($clipActive_copy ? $removeElement : $setElement   ) . '#' . $setElementID . '">' . $copyIcon . '</a>';
		$linkCut  = '<a href="' . $this->pObj->baseScript . $this->pObj->link_getParameters() . '&amp;CB[setCopyMode]=0&amp;CB[setFlexMode]=cut'  . ($clipActive_cut  ? $removeElement : $setElement   ) . '#' . $setElementID . '">' . $cutIcon  . '</a>';
		$linkRef  = '<a href="' . $this->pObj->baseScript . $this->pObj->link_getParameters() . '&amp;CB[setCopyMode]=1&amp;CB[setFlexMode]=ref'  . ($clipActive_ref  ? $removeElement : $setElementRef) . '#' . $setElementID . '">' . $refIcon  . '</a>';

		$output =
			(t3lib_div::inList($listOfButtons, 'copy') ? $linkCopy : '').
			(t3lib_div::inList($listOfButtons, 'ref' ) ? $linkRef  : '').
			(t3lib_div::inList($listOfButtons, 'cut' ) ? $linkCut  : '');

		return $output;
	}

	/**
	 * Renders and returns paste buttons for the destination specified by the flexform pointer.
	 * The buttons are (or is) only rendered if a suitable element is found in the "normal" clipboard
	 * and if it is valid to paste it at the given position.
	 *
	 * @param	array		$destinationPointer: Flexform pointer defining the destination location where a possible element would be pasted.
	 * @return	string		HTML output: linked image(s) which act as paste button(s)
	 */
	function element_getPasteButtons($destinationPointer) {
		global $LANG, $BE_USER;

		$origDestinationPointer = $destinationPointer;
		if (!$destinationPointer = $this->pObj->apiObj->flexform_getValidPointer($destinationPointer)) return '';
		if (!is_array ($this->t3libClipboardObj->clipData['normal']['el'])) return '';

		reset($this->t3libClipboardObj->clipData['normal']['el']);
		list ($clipboardElementTableAndUid, $clipboardElementPointerString) = each($this->t3libClipboardObj->clipData['normal']['el']);
		$clipboardElementPointer = $this->pObj->apiObj->flexform_getValidPointer($clipboardElementPointerString);

		// If we have no flexform reference pointing to the element, we create a short flexform pointer pointing to the record directly:
		list ($clipboardElementTable, $clipboardElementUid) = explode('|', $clipboardElementTableAndUid);
		if (!is_array($clipboardElementPointer)) {
			if ($clipboardElementTable != 'tt_content') return '';

			$clipboardElementPointer = array (
				'table' => 'tt_content',
				'uid' => $clipboardElementUid
			);
		}

		// If the destination element is already a sub element of the clipboard element, we mustn't show any paste icon:
		$destinationRecord = $this->pObj->apiObj->flexform_getRecordByPointer($destinationPointer);
		$clipboardElementRecord = $this->pObj->apiObj->flexform_getRecordByPointer($clipboardElementPointer);
		$dummyArr = array();
		$clipboardSubElementUidsArr = $this->pObj->apiObj->flexform_getListOfSubElementUidsRecursively('tt_content', $clipboardElementRecord['uid'], $dummyArr);
		$clipboardElementHasSubElements = count($clipboardSubElementUidsArr) > 0;

		if ($clipboardElementHasSubElements) {
			if (array_search ($destinationRecord['uid'], $clipboardSubElementUidsArr) !== FALSE) {
				return '';
			}
			if ($origDestinationPointer['uid'] == $clipboardElementUid) {
				return '';
			}
		}

		// Prepare the ingredients for the different buttons:
		$pasteMode       = isset ($this->t3libClipboardObj->clipData['normal']['flexMode']) ? $this->t3libClipboardObj->clipData['normal']['flexMode'] : ($this->t3libClipboardObj->clipData['normal']['mode'] == 'copy' ? 'copy' : 'cut');
		$pasteAfterIcon  = '<img class="paste"' . t3lib_iconWorks::skinImg($this->pObj->doc->backPath, 'gfx/clip_pasteafter.gif', '') . ' border="0" title="' . $LANG->getLL('pasterecord') . '" alt="" />';
		$pasteSubRefIcon = '<img class="paste"' . t3lib_iconWorks::skinImg('clip_pastesubref.gif', '') . ' border="0" title="' . $LANG->getLL('pastefce_andreferencesubs') . '" alt="" />';

		$sourcePointerString      = $this->pObj->apiObj->flexform_getStringFromPointer($clipboardElementPointer);
		$destinationPointerString = $this->pObj->apiObj->flexform_getStringFromPointer($destinationPointer);

		// FCEs with sub elements have two different paste icons, normal elements only one:
		if ($pasteMode == 'copy' && $clipboardElementHasSubElements) {
			$output  = '<a href="' . $this->pObj->baseScript . $this->pObj->link_getParameters() . '&amp;CB[removeAll]=normal&amp;pasteRecord=copy&amp;source=' . rawurlencode($sourcePointerString) . '&amp;destination=' . rawurlencode($destinationPointerString) . '#' . tvID_to_jsID($destinationPointerString) . '">' . $pasteAfterIcon . '</a>';
			$output .= '<a href="' . $this->pObj->baseScript . $this->pObj->link_getParameters() . '&amp;CB[removeAll]=normal&amp;pasteRecord=copyref&amp;source=' . rawurlencode($sourcePointerString) . '&amp;destination=' . rawurlencode($destinationPointerString) . '#' . tvID_to_jsID($destinationPointerString) . '">' . $pasteSubRefIcon.'</a>';
		} else {
			$output  = '<a href="' . $this->pObj->baseScript . $this->pObj->link_getParameters() . '&amp;CB[removeAll]=normal&amp;pasteRecord=' . $pasteMode . '&amp;source=' . rawurlencode($sourcePointerString) . '&amp;destination=' . rawurlencode($destinationPointerString) . '#' . tvID_to_jsID($destinationPointerString) . '">' . $pasteAfterIcon . '</a>';
		}

		return $output;
	}

	/**
	 * Displays a list of local content elements on the page which were NOT used in the hierarchical structure of the page.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	string		HTML output
	 * @access	protected
	 */
	function sidebar_renderNonUsedElements() {
		global $LANG, $TYPO3_DB, $BE_USER;

		$elementBelongsToCurrentPage = true;

		$output = '';
		$elements = array();
		$usedUids = array_keys($this->pObj->global_tt_content_elementRegister);
		$usedUids[] = 0;
		$pid = $this->pObj->id;	// If workspaces should evaluated non-used elements it must consider the id: For "element" and "branch" versions it should accept the incoming id, for "page" type versions it must be remapped (because content elements are then related to the id of the offline version)

		$res = $TYPO3_DB->exec_SELECTquery (
			t3lib_BEfunc::getCommonSelectFields('tt_content', '', array('uid', 'header', 'bodytext', 'sys_language_uid')),
			'tt_content',
			'pid=' . intval($pid) . ' ' .
				'AND uid NOT IN (' . implode(',', $usedUids) . ') '.
				'AND t3ver_state!=1' .
				t3lib_BEfunc::deleteClause('tt_content') .
				t3lib_BEfunc::versioningPlaceholderClause('tt_content'),
			'',
			'uid'
		);

		// Used to collect all those tt_content uids with no references which can be deleted
		$this->deleteUids = array();
		while(false !== ($row = $TYPO3_DB->sql_fetch_assoc($res))) {
 			$elementTitlebarColor = $this->doc->bgColor5;
			$elementTitlebarStyle = 'background-color: ' . $elementTitlebarColor;

			// Prepare the language icon:
			$languageLabel = htmlspecialchars ($this->pObj->allAvailableLanguages[$row['sys_language_uid']]['title']);
			$languageIcon = $this->pObj->allAvailableLanguages[$row['sys_language_uid']]['flagIcon'] ? '<img src="' . $this->pObj->allAvailableLanguages[$row['sys_language_uid']]['flagIcon'].'" title="'.$languageLabel.'" alt="'.$languageLabel.'" />' : ($languageLabel && $row['sys_language_uid'] ? '['.$languageLabel.']' : '');

			// Prepare buttons:
			$recordIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, t3lib_iconWorks::getIcon('tt_content', $row), 'width="18" height="16"') . ' border="0" title="[tt_content:' . $row['uid'] . ']" alt="" />';
			$recordButton = $this->pObj->doc->wrapClickMenuOnIcon($recordIcon, 'tt_content', $row['uid'], 1, '&callingScriptId=' . rawurlencode($this->pObj->doc->scriptID), 'new,copy,cut,pasteinto,pasteafter,delete');

			$titleBarLeftButtons = $recordButton;

			if (!$this->pObj->translatorMode && $this->pObj->canEditContent) {
				$elementPointerString = 'tt_content' . SEPARATOR_PARMS . $row['uid'];

				$linkEdit = $this->pObj->icon_edit(array('table' => 'tt_content', 'uid' => $row['uid'], 'isHidden' => $row['hidden']));
				$linkHide = $this->pObj->icon_hide(array('table' => 'tt_content', 'uid' => $row['uid'], 'isHidden' => $row['hidden']));

				$copyButton = $this->element_getSelectButtons($elementPointerString, 'copy');
				$refButton  = $this->element_getSelectButtons($elementPointerString, 'ref');
				$cutButton  = $this->element_getSelectButtons($elementPointerString, 'cut');

				$titleBarRightButtons =
					$linkEdit .
					'<div class="typo3-clipCtrl">' .
						$copyButton .
						$refButton .
						$cutButton .
					'</div>' .
					$this->renderLinks($row['uid'], $linkHide);

				if (($ia = $this->pObj->checkReferenceCount($row['uid'])) && (count($ia) > 1)) {
					$warnings = '<div>' . $this->doc->icons(2) . ' <em>' . sprintf(htmlspecialchars($GLOBALS['LANG']->getLL('warning_elementusedelsewheretoo', '')), count($ia), $this->pObj->link_warn('<img src="gfx/magnifier.png" class="absmiddle" />', $row['uid'], $ia)) . '</em></div>';
				}
			} else {
				$titleBarRightButtons = '';
			}

			// Create flexform pointer pointing to "before the first sub element":
			$subElementPointer = array (
				'table' => 'tt_content',
				'uid'   => $row['uid']
			);

			// Finally assemble the table:
			$cellFragment = '
				<table cellpadding="0" cellspacing="0" width="100%" class="tv-coe">
				<caption class="tt_content">' .
					htmlspecialchars($row['header'] ? $row['header'] : $GLOBALS['LANG']->getLL('notitle')) . '
				</caption>
				<thead class="tt_content">
					<tr style="' . $elementTitlebarStyle . ';" class="sortableHandle">
						<th>
							<div style="float:  left;" class="nobr">' .
								$languageIcon .
								$titleBarLeftButtons .
								($elementBelongsToCurrentPage ? '' : '<em>') . htmlspecialchars($row['header'] ? $row['header'] : $GLOBALS['LANG']->getLL('notitle')) . ($elementBelongsToCurrentPage ? '' : '</em>') . '
							</div>
							<div style="float: right;" class="nobr sortableButtons">' .
								$titleBarRightButtons . '
							</div>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr style="' . $elementTitlebarStyle . ';">
						<td>' .
							$warnings . '
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td>' . $this->pObj->render_previewContent($row) . '</td>
					</tr>
				<tbody>
				</table>
			';

			// "Browse", "New" and "Paste" icon:
			$cellFragment .= $this->pObj->icon_browse($subElementPointer);
			if (!$this->pObj->translatorMode && $this->pObj->canCreateNew) {
				$cellFragment .= $this->pObj->icon_new($subElementPointer);
			}

			$cellFragment .= '<span class="sortablePaste">' . $this->element_getPasteButtons($subElementPointer) . '</span>';
			if ($this->pObj->apiObj) {
				/* id-strings must not contain double-colons because of the selectors-api */
				$cellId = tvID_to_jsID($this->pObj->apiObj->flexform_getStringFromPointer($subElementPointer));
				$cellRel = $cellId;

				$cellFragment = '<div class="sortableItem" id="' . $cellId . '" rel="' . $cellRel . '">' . $cellFragment . '</div>';
			}

			$elements[] = $cellFragment;
		}

		if (count($elements)) {
			// Control for deleting all deleteable records:
			$deleteAll = '';
			if (count($this->deleteUids) && (0 === $BE_USER->workspace)) {
			//	$params = '';
			//	foreach($this->deleteUids as $deleteUid) {
			//		$params .= '&cmd[tt_content][' . $deleteUid . '][delete]=1';
			//	}
                        //
			//	$label = $LANG->getLL('rendernonusedelements_deleteall');
			//	$deleteAll = '<a style="float: right;" href="#" onclick="' . htmlspecialchars('jumpToUrl(\'' . $this->doc->issueCommand($params, '') . '\');') . '">'.
			//			'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/garbage.gif', 'width="11" height="12"') . ' title="' . htmlspecialchars($label) . '" alt="" />' .
			//			'</a>';

				$deleteAll = $this->pObj->icon_delete(array('table' => 'tt_content'));
			}

		}

		$groupElementPointer = array (
			'table' => 'tt_content'
		);

		if ($this->pObj->apiObj) {
			/* id-strings must not contain double-colons because of the selectors-api */
			$cellId = tvID_to_jsID($this->pObj->apiObj->flexform_getStringFromPointer($groupElementPointer));

			$this->pObj->sortableContainers[] = $cellId;
		}

		$output = '
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tv-container">
				<thead>
					<tr class="bgColor4-20">
						<th>
							<div style="float:  left;" class="nobr">' .
								$LANG->getLL('inititemno_elementsNotBeingUsed', '1') . ':
							</div>
							<div style="float: right;" class="nobr">' .
								$deleteAll . '
							</div>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr class="bgColor4">
						<td id="' . $cellId . '">'.
							implode('', $elements) . '
						</td>
					</tr>
				</tbody>
			</table>
		';

		return $output;
	}

	/**
	 * Render the available action links for the content
	 * element specified by $uid.
	 *
	 * @param	integer		$uid: Element record Uid
	 * @return	string		HTML-table
	 * @access	protected
	 */
	function renderLinks($uid, $hideIcon) {
		// Create flexform pointer pointing to "before the first sub element":
		$unlinkPointer = array (
			'table' => 'tt_content',
			'uid'   => $uid
		);

		if (0 === $GLOBALS['BE_USER']->workspace) {
			/* collect ids we may remove in a bunch */
			$this->deleteUids[] = $uid;

			return $this->pObj->icon_unlink($unlinkPointer) . $hideIcon .
			       $this->pObj->icon_delete($unlinkPointer);
		} else {
			return						  $hideIcon;
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_clipboard.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_clipboard.php']);
}

?>