<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004, 2005  Robert Lemke (robert@typo3.org)
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
 * Submodule 'sidebar' for the templavoila page module
 *
 * $Id: class.tx_templavoila_mod1_iconlinks.php 5831 2007-07-04 12:41:08Z dmitry $
 *
 * @author     Robert Lemke <robert@typo3.org>
 * @coauthor   Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Submodule 'Icons & links' for the templavoila page module
 *
 * Note: This class is closely bound to the page module class and uses many variables and functions directly. After major modifications of
 *       the page module all functions of this sidebar should be checked to make sure that they still work.
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @coauthor		Niels Fröhling <niels@frohling.biz>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod1_iconlinks {

	// References to the page module object
	var $pObj;				// A pointer to the parent object, that is the templavoila page module script. Set by calling the method init() of this class.
	var $extKey;				// A reference to extension key of the parent object.

	var $doc;				// A reference to the doc object of the parent object.
	var $clipboardObj;			// Instance of clipboard class
	var $apiObj;				// Instance of tx_templavoila_api

	var $blindIcons = array();		// Icons which shouldn't be rendered by configuration, can contain elements of "new,edit,copy,cut,ref,paste,browse,delete,makeLocal,unlink,hide"

	/**
	 * Initializes the iconlinks object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila page module.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	void
	 * @access	public
	 */
	function init(&$pObj) {
		// Make local reference to some important variables:
		$this->pObj =& $pObj;
		$this->extKey =& $this->pObj->extKey;

		$this->doc =& $this->pObj->doc;
		$this->apiObj =& $this->pObj->apiObj;
		$this->clipboardObj =& $this->pObj->clipboardObj;

		$this->blindIcons = isset($this->pObj->modTSconfig['properties']['blindIcons'])
			? t3lib_div::trimExplode(',', $this->pObj->modTSconfig['properties']['blindIcons'], TRUE)
			: array();
	}

	/*******************************************
	 *
	 * Links
	 *
	 *******************************************/

	/**
	 * Returns an HTML link for warn about a content element.
	 *
	 * @param	string		$label: The label
	 * @param	array		$unlinkPointer: Flexform pointer pointing to the element to be unlinked
	 * @return	string		HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function link_warn($label, $uid, $infoData) {
		return '<a href="#" onclick="' . htmlspecialchars('top.launchView(\'tt_content\', \'' . $uid . '\'); return FALSE;') . '" title="' . htmlspecialchars(t3lib_div::fixed_lgd_cs(implode(' / ', $infoData), 100)) . '">' . $label . '</a>';
	}

	/**
	 * Returns an HTML link for viewing
	 *
	 * @param	string		$label: The label (or image)
	 * @param	string		$table: The table, fx. 'tt_content'
	 * @param	integer		$uid: The uid of the element to be hidden/unhidden
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function link_view($label, $table, $uid) {
		$onClick = t3lib_BEfunc::viewOnClick($uid, $this->doc->backPath, t3lib_BEfunc::BEgetRootLine($uid), '', '', ($this->pObj->currentLanguageUid ? '&L=' . $this->pObj->currentLanguageUid : ''));

		return '<a href="#" onclick="' . htmlspecialchars($onClick) . '">' . $label . '</a>';
	}

	/**
	 * Returns an HTML link for creating a new record
	 *
	 * @param	string		$label: The label (or image)
	 * @param	array		$parentPointer: Flexform pointer defining the parent element of the new record
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function link_new($label, $parentPointer) {
		$parameters =
			$this->pObj->uri_getParameters() .
			'&amp;parentRecord=' . rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer)) . '&amp;returnUrl=' . rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'));

		return '<a href="' . $this->pObj->wizScript . $parameters . '">' . $label . '</a>';
	}

	/**
	 * Returns an HTML link for unlinking a content element. Unlinking means that the record still exists but
	 * is not connected to any other content element or page.
	 *
	 * @param	string		$label: The label
	 * @param	array		$unlinkPointer: Flexform pointer pointing to the element to be unlinked
	 * @return	string		HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function link_unlink($label, $unlinkPointer) {
		$unlinkPointerString = rawurlencode(tvID_to_jsID($this->apiObj->flexform_getStringFromPointer($unlinkPointer)));

		if (!$unlinkPointer['position'])
			return '<a href="javascript:' . htmlspecialchars('if (confirm(' . $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('unlinkRecordsAllMsg')) . '))') . ' sortable_unlinkRecordsAll(\'' . $unlinkPointerString . '\');" class="onoff">' . $label . '</a>';
		else
			return '<a href="javascript:' . htmlspecialchars('if (confirm(' . $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('unlinkRecordMsg'    )) . '))') . ' sortable_unlinkRecord    (\'' . $unlinkPointerString . '\');" class="onoff">' . $label . '</a>';
	}

	/**
	 * Returns an HTML link for hiding
	 *
	 * @param	string		$label: The label (or image)
	 * @param	string		$table: The table, fx. 'tt_content'
	 * @param	integer		$uid: The uid of the element to be hidden/unhidden
	 * @param	integer		$hidden: The hidden state of the element
	 * @param	boolean		$forced: By default the link is not shown if translatorMode is set, but with this boolean it can be forced anyway.
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function link_hide($label, $table, $uid, $hidden, $isReferenced = FALSE, $forced = FALSE) {
		if ($label) {
			if (($table == 'pages' && ($this->pObj->calcPerms &  2) ||
			     $table != 'pages' && ($this->pObj->calcPerms & 16)) &&
				(!$this->pObj->translatorMode || $forced))	{
					if ($table == "pages" && $this->pObj->currentLanguageUid) {
						$params = '&data[' . $table . '][' . $uid . '][hidden]=' . (1 - $hidden);

					//	return '<a href="#" onclick="' . htmlspecialchars('return jumpToUrl(\'' . $GLOBALS['SOBE']->doc->issueCommand($params, -1) . '\');') . '">' . $label . '</a>';
					} else {
						$params = '&data[' . $table . '][' . $uid . '][hidden]=' . (1 - $hidden);

						if ($isReferenced)
							$link = '<a href="#" onclick="' . htmlspecialchars('if (confirm(' . $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('hideRecord' . ($isReferenced ? 'WithReferences' : '') . 'Msg')) . '))') . ' eval(this.getAttribute(\'rel\'));"';
						else
							$link = '<a href="#" onclick="eval(this.getAttribute(\'rel\'));"';

						/* the commands are independent of the position,
						 * so sortable doesn't need to update these and we
						 * can safely use '#'
						 */
						if ($hidden)
							$link .= ' rel="sortable_unhideRecord(this, \'' . $GLOBALS['SOBE']->doc->issueCommand($params, -1) . '\');">' . $label . '</a>';
						else
							$link .= ' rel="sortable_hideRecord  (this, \'' . $GLOBALS['SOBE']->doc->issueCommand($params, -1) . '\');">' . $label . '</a>';

						return $link;
					}
				} else {
					return $label;
				}
		}

		return '';
	}

	/**
	 * Returns an HTML link for editing
	 *
	 * @param	string		$label: The label (or image)
	 * @param	string		$table: The table, fx. 'tt_content'
	 * @param	integer		$uid: The uid of the element to be edited
	 * @param	boolean		$columns: show only the column-fields
	 * @param	boolean		$forced: By default the link is not shown if translatorMode is set, but with this boolean it can be forced anyway.
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function link_edit($label, $table, $uid, $columns = '', $forced = FALSE) {
		if ($label) {
			if (($table == 'pages' && ($this->pObj->calcPerms & 2) ||
			     $table != 'pages' && ($this->pObj->calcPerms & 16)) &&
			    (!$this->pObj->translatorMode || $forced)) {
				if ($table == "pages" && $this->pObj->currentLanguageUid) {
					return '<a href="' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&amp;editPageLanguageOverlay=' . $this->pObj->currentLanguageUid . '">' . $label . '</a>';
				} else {
					$which = '&edit[' . $table . '][' . $uid . ']=edit';
					if ($columns)
						$which .= '&columnsOnly=' . rawurlencode($columns);

					$onClick = t3lib_BEfunc::editOnClick($which, $this->doc->backPath);
					return '<a href="#" onclick="' . htmlspecialchars($onClick) . '">' . $label . '</a>';
				}
			} else {
				return $label;
			}
		}

		return '';
	}

	/**
	 * Returns an HTML link for making a reference content element local to the page (copying it).
	 *
	 * @param	string		$label: The label
	 * @param	array		$makeLocalPointer: Flexform pointer pointing to the element which shall be copied
	 * @return	string		HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function link_makeLocal($label, $makeLocalPointer) {
		$makeLocalString = $this->apiObj->flexform_getStringFromPointer($makeLocalPointer);

		return '<a href="' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&amp;makeLocalRecord=' . rawurlencode($makeLocalString) . '#' . tvID_to_jsID($makeLocalString) . '" onclick="' . htmlspecialchars('return confirm(' . $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('makeLocalMsg')) . ');') . '">' . $label . '</a>';
	}

	/**
	 * Returns an HTML link for deleting a content element.
	 *
	 * @param	string		$label: The label
	 * @param	array		$unlinkPointer: Flexform pointer pointing to the element to be unlinked
	 * @return	string		HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function link_delete($label, $deletePointer, $isReferenced = FALSE) {
		$deletePointerString = rawurlencode(tvID_to_jsID($this->apiObj->flexform_getStringFromPointer($deletePointer)));

		if (!$deletePointer['position'] && !$deletePointer['uid'])
			return '<a href="javascript:' . htmlspecialchars('if (confirm(' . $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('deleteRecordsAllMsg'                                           )) . '))') . ' sortable_deleteRecordsAll(\'' . $deletePointerString . '\');">' . $label . '</a>';
		else
			return '<a href="javascript:' . htmlspecialchars('if (confirm(' . $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('deleteRecord' . ($isReferenced ? 'WithReferences' : '') . 'Msg')) . '))') . ' sortable_deleteRecord    (\'' . $deletePointerString . '\');">' . $label . '</a>';
	}

	/*******************************************
	 *
	 * Icons (possibly wrapped)
	 *
	 *******************************************/

	/**
	 * Returns the collapse icon.
	 *
	 * @return	string		image of the collapse icon
	 * @access protected
	 */
	function icon_collapse() {
		if (in_array('collapse', $this->blindIcons))
			return'';

		$collapseIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/ol/minusonly.gif', 'width="11" height="12"') . ' class="absmiddle" alt="" />';

		return $collapseIcon;
	}

	/**
	 * Returns the container icon.
	 *
	 * @return	string		image of the container icon
	 * @access protected
	 */
	function icon_container() {
		if (in_array('container', $this->blindIcons))
			return'';

		$containerIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/i/tt_content.gif', 'width="11" height="12"') . ' title="Container for content elements" class="absmiddle" alt="" />';

		return $containerIcon;
	}

	/**
	 * Returns the record-icon of a given element.
	 *
	 * @param	array		$el: The element configuration
	 * @return	string		image of the record
	 * @access protected
	 */
	function icon_record(&$el) {
		if (in_array('record', $this->blindIcons))
			return'';

		$recordIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, $el['icon'], 'width="18" height="16"') . ' title="' . htmlspecialchars('[' . $el['table'] . ':' . $el['uid'] . ']') . '" class="absmiddle" alt="" />';

		return $recordIcon;
	}

	/**
	 * Returns the page-icon of a given element.
	 *
	 * @param	array		$el: The element configuration
	 * @return	string		image of the page
	 * @access protected
	 */
	function icon_page(&$pageRecord) {
		if (in_array('page', $this->blindIcons))
			return'';

		$pageIcon = t3lib_iconWorks::getIconImage('pages', $pageRecord, $this->pObj->backPath, 'class="absmiddle" title="' . htmlspecialchars(t3lib_BEfunc::getRecordIconAltText($pageRecord, 'pages')) . '"');

		return $pageIcon;
	}

	/**
	 * Returns an image in a HTML link for warn about a content element.
	 *
	 * @param	array		$unlinkPointer: Flexform pointer pointing to the element to be unlinked
	 * @return	string		image inside a HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function icon_warn($uid, $infoData) {
		if (in_array('warn', $this->blindIcons))
			return'';

		$warningIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/icon_warning2.gif', '') . 'class="absmiddle" title="' . htmlspecialchars('Ref: ' . count($infoData)) . '" border="0" alt="" />';

		return $this->link_warn($warningIcon, $uid, $infoData);
	}

	/**
	 * Returns an image in a HTML link for viewing
	 *
	 * @param	array		$el: The element configuration
	 * @return	string		image inside a HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_view(&$el) {
		if (in_array('view', $this->blindIcons))
			return'';

		$viewPageIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/zoom.gif', 'width="12" height="12"') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.showPage', 1) . '" alt="" />';

		$label = $viewPageIcon;

		return $this->link_view($label, $el['table'], $el['uid']);
	}

	/**
	 * Returns an image in a HTML link for creating a new record
	 *
	 * @param	array		$parentPointer: Flexform pointer defining the parent element of the new record
	 * @return	string		image inside a HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_new($parentPointer) {
		if (in_array('new', $this->blindIcons))
			return'';

		$newIcon = '<img class="new"' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/new_el.gif', '') . ' border="0" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:newRecordGeneral') . '" alt="" />';

		return $this->link_new($newIcon, $parentPointer);
	}

	/**
	 * Returns an image in a HTML link for browsing an existing record
	 *
	 * @param	array		$parentPointer: Flexform pointer defining the parent element of the new record
	 * @return	string		image inside a HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_browse($parentPointer) {
		if (in_array('browse', $this->blindIcons))
			return'';

		$browseIcon = '<img class="browse"' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/insert3.gif',     '') . ' border="0" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.browse_db') . '" alt="" />';
		$insertIcon = '<img class="browse"' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/plusbullet2.gif', '') . ' border="0" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.browse_db') . '" alt="" />';

		$p = $this->apiObj->flexform_getStringFromPointer($parentPointer);
		$b = $GLOBALS['BE_USER']->getSessionData('lastPasteRecord');

		if ($p == $b)
			$label = $insertIcon;
		else
			$label = $browseIcon;

		$parameters =
			$this->pObj->uri_getParameters() .
		//	'&amp;CB[removeAll]=normal' .
			'&amp;pasteRecord=ref' .
			'&amp;source=' . rawurlencode('###') .
			'&amp;destination=' . rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer));
		$browser =
			'browserPos = this;' .
			'setFormValueOpenBrowser(\'db\',\'browser[communication]|||tt_content\');' .
			'return FALSE;';

		return '<a href="#" ' . ($p == $b ? 'id="browserPos"' : '') . ' rel="' . $this->pObj->baseScript . $parameters . '#browserPos" onclick="' . $browser . '">' . $label . '</a>';
	}

	/**
	 * Returns an image in a HTML link for unlinking a content element. Unlinking means that the record still
	 * exists but is not connected to any other content element or page.
	 *
	 * @param	array		$unlinkPointer: Flexform pointer pointing to the element to be unlinked
	 * @return	string		image inside a HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function icon_unlink($unlinkPointer) {
		if (in_array('unlink', $this->blindIcons))
			return'';

		if (!$unlinkPointer['position'])
			$unlinkIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, t3lib_extMgm::extRelPath('templavoila') . 'res/link_delete.png', '') . ' title="' . $GLOBALS['LANG']->getLL('unlinkRecordsAll') . '" class="absmiddle" alt="" />';
		else
			$unlinkIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, t3lib_extMgm::extRelPath('templavoila') . 'res/link_delete.png', '') . ' title="' . $GLOBALS['LANG']->getLL('unlinkRecord'    ) . '" class="absmiddle" alt="" />';

		return $this->link_unlink($unlinkIcon, $unlinkPointer);
	}

	/**
	 * Returns an image in a HTML link for hiding
	 *
	 * @param	array		$el: The element configuration
	 * @return	string		image inside a HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_hide(&$el, $isReferenced = FALSE) {
		if (in_array('hide', $this->blindIcons))
			return'';

		if (intval($this->pObj->modTSconfig['properties']['disableHideIcon']))
			return '';

		$hideIcon = ($el['table'] == 'pages'
		?	'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/button_hide.gif',  '') . ' border="0" title="' . htmlspecialchars($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:hidePage'  )) . '" alt="" />'
		:	'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/button_hide.gif',  '') . ' border="0" title="' . htmlspecialchars($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:hide'      )) . '" alt="" />');
		$unhideIcon = ($el['table'] == 'pages'
		?	'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/button_unhide.gif','') . ' border="0" title="' . htmlspecialchars($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:unHidePage')) . '" alt="" />'
		:	'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/button_unhide.gif','') . ' border="0" title="' . htmlspecialchars($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:unHide'    )) . '" alt="" />');

		if ($el['isHidden'])
			$label = $unhideIcon;
		else
			$label = $hideIcon;

		return $this->link_hide($label, $el['table'], $el['uid'], $el['isHidden'], $isReferenced);
	}

	/**
	 * Returns an image in a HTML link for editing
	 *
	 * @param	array		$el: The element configuration
	 * @return	string		image inside a HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function icon_edit(&$el, $link = TRUE) {
		if (in_array('edit', $this->blindIcons))
			return'';

		$editIcon = ($el['table'] == 'pages'
		?	'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', '') . ' title="' . htmlspecialchars($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:editPage')) . '" class="absmiddle" alt="" />'
		:	'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', '') . ' title="' . htmlspecialchars($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:edit'    )) . '" class="absmiddle" alt="" />');

		$label = $editIcon;

		if ($link)
			return $this->link_edit($label, $el['table'], $el['uid']);
		else
			return                  $label;
	}
	
	function icon_editflex() {
		if (in_array('edit', $this->blindIcons))
			return'';

		$editIcon = 
			'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/edit2.gif', '') . ' title="' . $GLOBALS['LANG']->getLL('editrecord') . '" class="absmiddle" alt="" />';

		$label = $editIcon;

		return $label;
	}

	/**
	 * Returns an image in a HTML link for making a reference content element local to the page (copying it).
	 *
	 * @param	array		$makeLocalPointer: Flexform pointer pointing to the element which shall be copied
	 * @param	string		$realDup: Indicated if the element isn't possibly allready from the current page
	 * @return	string		image inside a HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function icon_makeLocal($makeLocalPointer, $realDup = 0) {
		if (in_array('makeLocal', $this->blindIcons))
			return'';

		$dupIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, t3lib_extMgm::extRelPath('templavoila') . 'mod1/makelocalcopy.gif', '') . ' title="' . $GLOBALS['LANG']->getLL('makeLocal') . '" border="0" alt="" />';

		if ($realDup)
			return $this->link_makeLocal($dupIcon, $makeLocalPointer);
		else
			return '';
	}

	/**
	 * Returns an image in a HTML link for deleting a content element.
	 *
	 * @param	array		$unlinkPointer: Flexform pointer pointing to the element to be unlinked
	 * @return	string		image inside a HTML anchor tag containing the label and the unlink-link
	 * @access protected
	 */
	function icon_delete($deletePointer, $isReferenced = FALSE) {
		if (in_array('delete', $this->blindIcons))
			return'';

		/* disabling turn on */
		if ( intval($this->pObj->modTSconfig['properties']['disableDeleteIcon']))
		/* exception to disabling pass-through */
		if (!intval($this->pObj->modTSconfig['properties']['enableDeleteIconForLocalElements']) || $isReferenced)
			return '';

		if (!$deletePointer['position'] && !$deletePointer['uid'])
			$deleteIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/garbage.gif', '') . ' title="' . $GLOBALS['LANG']->getLL('deleteRecordsAll') . '" border="0" alt="" />';
		else
			$deleteIcon = '<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/garbage.gif', '') . ' title="' . $GLOBALS['LANG']->getLL('deleteRecord'    ) . '" border="0" alt="" />';

		return $this->link_delete($deleteIcon, $deletePointer, $isReferenced);
	}

	/**
	 * Returns the language-icon of a given element.
	 *
	 * @param	array		$el: The element configuration
	 * @param	integer		$languageUid: The language identifier
	 * @return	string		image of the flag of the language
	 * @access protected
	 */
	function icon_lang(&$el, $languageUid) {
		if (in_array('lang', $this->blindIcons))
			return'';

		$languageLabel = htmlspecialchars($this->pObj->allAvailableLanguages[$el['sys_language_uid']]['title']);
		$languageIcon = $this->pObj->allAvailableLanguages[$languageUid]['flagIcon'] ? '<img src="' . $this->pObj->allAvailableLanguages[$languageUid]['flagIcon'] . '" title="' . $languageLabel . '" alt="' . $languageLabel . '" />' : ($languageLabel && $languageUid ? '[' . $languageLabel . ']' : '');

		// If there was a language icon and the language was not default or [all] and if that langauge is accessible for the user, then wrap the flag with an edit link (to support the "Click the flag!" principle for translators)
		if ($languageIcon && ($languageUid > 0) && $GLOBALS['BE_USER']->checkLanguageAccess($languageUid) && ($el['table'] === 'tt_content')) {
			$languageIcon = $this->link_edit($languageIcon, 'tt_content', $el['uid'], '', TRUE);
		}

		return $languageIcon;
	}

	/**
	 * Returns a block images showing and offering the operations to
	 * insert new records (new, browse, paste)
	 *
	 * @param	array		$el: The element configuration
	 * @return	string		classed span containing the anchors and images
	 * @access protected
	 */
	function icon_nbp(&$el) {
		$controls = '';

		if ($this->pObj->canEditContent) {
			// "Browse", "New" and "Paste" icon:
			$controls .= '<span class="sortableControls">';
			if (!$this->pObj->translatorMode && $this->pObj->canCreateNew) {
				$controls .= $this->icon_new($el); }
				$controls .= $this->icon_browse($el);
			$controls .= '<span class="sortablePaste">' . $this->clipboardObj->element_getPasteButtons($el) . '</span>';
			$controls .= '</span>';
		}

		return $controls;
	}

	/*******************************************
	 *
	 * Icons
	 *
	 *******************************************/

	function link_user($uid) {
		$user = t3lib_BEfunc::getRecordWSOL('be_users', $uid);

		return $this->link_edit($user['username'], 'be_users', $uid);
	}

	function link_page($pid) {
		$page = t3lib_BEfunc::getRecordWSOL('pages', $pid);
		$title = t3lib_BEfunc::getRecordTitle('pages', $page, TRUE);

		return $this->link_edit($title, 'pages', $pid);
	}

	function link_auto($link) {
		if (is_numeric($link))
			return $this->link_page($link);

		return '<a href="' . $link . '" target="_blank">' . htmlspecialchars($link) . '</a>';
	}

	/*******************************************
	 *
	 * Icons (for ajax-actions)
	 *
	 *******************************************/

	/**
	 * Returns an checkbox for jamming of inheritance of the given element
	 *
	 * @param	array		$parentPointer: Flexform pointer defining the element to be jammed
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function cbox_jamm($parentPointer) {
		$parentPointer['vLang'] = '_JAMM';

		return ' onclick="sortable_exec(\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&amp;ajaxJammField=' . rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer)) . '\');"';
//		return ' onclick="jumpToUrl(\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&amp;ajaxJammField=' . rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer)) . '\');"';
	}

	/**
	 * Returns an checkbox for unjamming of inheritance of the given element
	 *
	 * @param	array		$parentPointer: Flexform pointer defining the element to be unjammed
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function cbox_unjamm($parentPointer) {
		$parentPointer['vLang'] = '_JAMM';

		return ' onclick="sortable_exec(\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&amp;ajaxUnjammField=' . rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer)) . '\');"';
//		return ' onclick="jumpToUrl(\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&amp;ajaxUnjammField=' . rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer)) . '\');"';
	}

	/**
	 * Returns an checkbox for jamming/unjamming of inheritance of the given element
	 *
	 * @param	array		$parentPointer: Flexform pointer defining the element to be jammed/unjammed
	 * @return	string		HTML anchor tag containing the label and the correct link
	 * @access protected
	 */
	function cbox_jammswitch($parentPointer) {
		$parentPointer['vLang'] = '_JAMM';

		return ' onclick="
			if (this.checked)
				sortable_exec(\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&amp;ajaxJammField=' . rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer)) . '\');
			else
				sortable_exec(\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&amp;ajaxUnjammField=' . rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer)) . '\');
			"';

//		return 'onclick="jumpToUrl(\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&amp;ajaxJammField=' . rawurlencode($this->apiObj->flexform_getStringFromPointer($parentPointer)) . '\');"';
	}


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_iconlinks.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_iconlinks.php']);
}

?>