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
 * Submodule 'localization' for the templavoila page module
 *
 * $Id: class.tx_templavoila_mod1_localization.php 5928 2007-07-12 11:20:33Z kasper $
 *
 * @author     Robert Lemke <robert@typo3.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   54: class tx_templavoila_mod1_localization
 *   68:     function init(&$pObj)
 *   89:     function sidebar_renderItem(&$pObj)
 *  113:     function sidebar_renderItem_renderLanguageSelectorbox()
 *  204:     function sidebar_renderItem_renderNewTranslationSelectorbox()
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Submodule 'localization' for the templavoila page module
 *
 * @author		Robert Lemke <robert@typo3.org>
 * @package		TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod1_localization {

		// References to the page module object
	var $pObj;										// A pointer to the parent object, that is the templavoila page module script. Set by calling the method init() of this class.
	var $doc;										// A reference to the doc object of the parent object.

	/**
	 * Initializes the sub module object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila page module.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	void
	 * @access	public
	 */
	function init(&$pObj) {
		// Make local reference to some important variables:
		$this->pObj =& $pObj;
		$this->doc =& $this->pObj->doc;
		$this->extKey =& $this->pObj->extKey;
		$this->MOD_SETTINGS =& $this->pObj->MOD_SETTINGS;

		// Add a localization tab to the sidebar:
		$this->pObj->sideBarObj->addItem('localization', $this, 'sidebar_renderItem', $GLOBALS['LANG']->getLL('localization', 1), 60, true);
	}

	/**
	 * Renders the HTML code for a selectorbox for selecting the language version of the current
	 * page.
	 *
	 * @return	mixed		HTML code for the selectorbox or FALSE if no language is available.
	 * @access	protected
	 */
	function sidebar_renderItem_renderLanguageSelectorbox_pure_actual() {
		global $BACK_PATH;

		$availableLanguagesArr = $this->pObj->translatedLanguagesArr;
		if (count($availableLanguagesArr) <= 1)
			return FALSE;

		$optionsArr = array ();
		foreach ($availableLanguagesArr as $languageArr) {
			if ($languageArr['uid'] <= 0 || $GLOBALS['BE_USER']->checkLanguageAccess($languageArr['uid'])) {
				$style = $languageArr['PLO_hidden'] ? 'Filter: alpha(opacity=25); -moz-opacity: 0.25; opacity: 0.25;' : '';
				$flag = ($languageArr['flagIcon'] != '' ? $languageArr['flagIcon'] : $BACK_PATH . 'gfx/flags/unknown.gif');

				$style .= isset($languageArr['flagIcon']) ? 'background: 1px center url(' . $flag . ') no-repeat; padding-left: 22px;' : '';
				$optionsArr[] = '<option style="' . $style . '" value="' . $languageArr['uid'] . '"' . ($this->pObj->MOD_SETTINGS['language'] == $languageArr['uid'] ? ' selected="selected"' : '') . '>' . htmlspecialchars($languageArr['title']) . '</option>';
				$sstyle = ($this->pObj->MOD_SETTINGS['language'] == $languageArr['uid'] ? $style : $sstyle);
			}
		}

		$link = '\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&SET[language]=\'+this.options[this.selectedIndex].value';

		return '<select onchange="document.location=' . $link . '" style="' . $sstyle . '">' . implode('', $optionsArr) . '</select>';
	}

	/**
	 * Renders the HTML code for a selectorbox for selecting the language version of the current
	 * page.
	 *
	 * @return	mixed		HTML code for the selectorbox or FALSE if no language is available.
	 * @access	protected
	 */
	function sidebar_renderItem_renderLanguageEditLinks_pure_actual() {
		global $BACK_PATH;

		$availableLanguagesArr = $this->pObj->translatedLanguagesArr;
		if (count($availableLanguagesArr) <= 1) {
			return FALSE;
		}

		foreach ($availableLanguagesArr as $languageArr) {
			if ($languageArr['uid'] <= 0 || $GLOBALS['BE_USER']->checkLanguageAccess($languageArr['uid'])) {
				$style = $languageArr['PLO_hidden'] ? ' style="Filter: alpha(opacity=25); -moz-opacity: 0.25; opacity: 0.25;"' : '';
				$flag = ($languageArr['flagIcon'] != '' ? $languageArr['flagIcon'] : $BACK_PATH . 'gfx/flags/unknown.gif');

				// Link to editing of language header:
				$availableTranslationsFlags .= '<a href="' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&editPageLanguageOverlay=' . $languageArr['uid'] . '"><img src="' . $flag . '" title="Edit ' . htmlspecialchars($languageArr['title']) . '" alt=""' . $style . ' /></a> ';
			}
		}

		return $availableTranslationsFlags;
	}

	/**
	 * Renders the HTML code for a selectorbox for selecting the language version of the current
	 * page.
	 *
	 * @return	mixed		HTML code for the selectorbox or FALSE if no language is available.
	 * @access	protected
	 */
	function sidebar_renderItem_renderLanguageSelectorbox_pure_missing() {
		global $BACK_PATH;

		$translatedLanguagesArr = $this->pObj->translatedLanguagesArr;
		$newLanguagesArr = $this->pObj->getAvailableLanguages(0, true, FALSE);
		if (count($newLanguagesArr) < 1)
			return FALSE;

		$optionsArr = array ('<option value=""></option>');
		foreach ($newLanguagesArr as $language) {
			if ($GLOBALS['BE_USER']->checkLanguageAccess($language['uid']) && !isset($translatedLanguagesArr[$language['uid']])) {
				$style = isset ($language['flagIcon']) ? 'background-image: url(' . $language['flagIcon'] . '); background-repeat: no-repeat; padding-top: 0px; padding-left: 22px;' : '';
				$optionsArr[] = '<option style="' . $style . '" name="createNewPageTranslation" value="' . $language['uid'] . '">' . htmlspecialchars($language['title']) . '</option>';
				$sstyle = ($this->pObj->MOD_SETTINGS['language'] == $languageArr['uid'] ? $style : $sstyle);
			}
		}

		$link = $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&createNewPageTranslation=\'+this.options[this.selectedIndex].value+\'&pid=' . $this->pObj->id;

		return '<select onchange="document.location=' . $link . '" style="' . $sstyle . '">'.implode ('', $optionsArr) . '</select>';
	}

	/**
	 * Renders the HTML code for a selectorbox for selecting the language version of the current
	 * page.
	 *
	 * @return	mixed		HTML code for the selectorbox or FALSE if no language is available.
	 * @access	protected
	 */
	function sidebar_renderItem_renderLanguageSelectorbox_pure_mode() {
		global $BACK_PATH;

		if ($this->pObj->currentLanguageUid >= 0 && (($this->pObj->rootElementLangMode === 'disable') || ($this->pObj->rootElementLangParadigm === 'bound'))) {
			$options = array();
			$options[] = t3lib_div::inList($this->pObj->modTSconfig['properties']['disableDisplayMode'], 'default'         ) ? '' : '<option value=""'.                  ($this->pObj->MOD_SETTINGS['langDisplayMode'] ==  ''                 ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('pageLocalizationDisplayMode_defaultLanguage') . '</option>';
			$options[] = t3lib_div::inList($this->pObj->modTSconfig['properties']['disableDisplayMode'], 'selectedLanguage') ? '' : '<option value="selectedLanguage"' . ($this->pObj->MOD_SETTINGS['langDisplayMode'] === 'selectedLanguage' ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('pageLocalizationDisplayMode_selectedLanguage') . '</option>';
			$options[] = t3lib_div::inList($this->pObj->modTSconfig['properties']['disableDisplayMode'], 'onlyLocalized'   ) ? '' : '<option value="onlyLocalized"' .    ($this->pObj->MOD_SETTINGS['langDisplayMode'] === 'onlyLocalized'    ? ' selected="selected"' : '') . '>' . $GLOBALS['LANG']->getLL('pageLocalizationDisplayMode_onlyLocalized') . '</option>';
			$link = '\'' . $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&SET[langDisplayMode]=\'+this.options[this.selectedIndex].value';

			return '<select onchange="document.location=' . $link . '">' . implode(chr(10), $options) . '</select>';
		}

		return null;
	}

	function sidebar_renderItem_renderLanguageSelectorlist_pure_mode() {
		global $BACK_PATH;

		if ($this->pObj->currentLanguageUid >= 0 && (($this->pObj->rootElementLangMode === 'disable') || ($this->pObj->rootElementLangParadigm === 'bound'))) {
			$link = $this->pObj->baseScript . $this->pObj->uri_getParameters() . '&SET[langDisplayMode]=###';

			$entries = array();
			$entries[] = t3lib_div::inList($this->pObj->modTSconfig['properties']['disableDisplayMode'], 'default'         ) ? '' : '<li class="mradio' . ($this->pObj->MOD_SETTINGS['langDisplayMode'] ==  ''                 ? ' selected' : '') . '" name="langDisplayMode"><a href="' . str_replace('###', '', $link) . '"' .                 '>' . $GLOBALS['LANG']->getLL('pageLocalizationDisplayMode_defaultLanguage') . '</a></li>';
			$entries[] = t3lib_div::inList($this->pObj->modTSconfig['properties']['disableDisplayMode'], 'selectedLanguage') ? '' : '<li class="mradio' . ($this->pObj->MOD_SETTINGS['langDisplayMode'] === 'selectedLanguage' ? ' selected' : '') . '" name="langDisplayMode"><a href="' . str_replace('###', 'selectedLanguage', $link) . '"' . '>' . $GLOBALS['LANG']->getLL('pageLocalizationDisplayMode_selectedLanguage') . '</a></li>';
			$entries[] = t3lib_div::inList($this->pObj->modTSconfig['properties']['disableDisplayMode'], 'onlyLocalized'   ) ? '' : '<li class="mradio' . ($this->pObj->MOD_SETTINGS['langDisplayMode'] === 'onlyLocalized'    ? ' selected' : '') . '" name="langDisplayMode"><a href="' . str_replace('###', 'onlyLocalized', $link) . '"' .    '>' . $GLOBALS['LANG']->getLL('pageLocalizationDisplayMode_onlyLocalized') . '</a></li>';

			return '<ul class="group">' . implode(chr(10), $entries) . '</ul>';
		}

		return null;
	}

	/**
	 * Renders the HTML code for a selectorbox for selecting the language version of the current
	 * page.
	 *
	 * @return	mixed		HTML code for the selectorbox or FALSE if no language is available.
	 * @access	protected
	 */
	function sidebar_renderItem_renderLanguageSelectorbox() {
		global $BACK_PATH;

		$output = '';

		if (($aoutput = $this->sidebar_renderItem_renderLanguageSelectorbox_pure_actual())) {
			$output .= '
				<tr class="bgColor4">
					<td width="20">' .
						t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', 'selectlanguageversion', $this->doc->backPath) .'
					</td>
					<td width="200" valign="middle">' .
						$GLOBALS['LANG']->getLL('selectlanguageversion', 1) . ':
					</td>
					<td valign="middle">' .
						$aoutput . '
					</td>
				</tr>
			';
		}

		if (($moutput = $this->sidebar_renderItem_renderLanguageSelectorbox_pure_mode())) {
			$output .= '
				<tr class="bgColor4">
					<td width="20">' .
						t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', 'pagelocalizationdisplaymode', $this->doc->backPath) .'
					</td>
					<td width="200" valign="middle">' .
						$GLOBALS['LANG']->getLL('pageLocalizationDisplayMode', 1) . ':
					</td>
					<td valign="middle">' .
						$moutput . '
					</td>
				</tr>
			';
		}

		if ($this->pObj->rootElementLangMode !== 'disable') {
			$output .= '
				<tr class="bgColor4">
					<td width="20">' .
						t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', 'pagelocalizationmode', $this->doc->backPath) .'
					</td>
					<td width="200" valign="middle">' .
						$GLOBALS['LANG']->getLL('pageLocalizationMode', 1) . ':
					</td>
					<td valign="middle"><em>' .
						$GLOBALS['LANG']->getLL('pageLocalizationMode_' . $this->pObj->rootElementLangMode, 1) . ($this->pObj->rootElementLangParadigm != 'free' ? (' / ' .
						$GLOBALS['LANG']->getLL('pageLocalizationParadigm_' . $this->pObj->rootElementLangParadigm)) : '') . '
					</em></td>
				</tr>
			';
		}

		if (($aoutput = $this->sidebar_renderItem_renderLanguageEditLinks_pure_actual())) {
			$output .= '
				<tr class="bgColor4">
					<td width="20">' .
						t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', 'editlanguageversion', $this->doc->backPath) .'
					</td>
					<td width="200" valign="middle">' .
						$GLOBALS['LANG']->getLL('editlanguageversion', 1) . ':
					</td>
					<td valign="middle">' .
						$aoutput . '
					</td>
				</tr>
			';
		}

		return $output;
	}

	/**
	 * Renders the HTML code for a selectorbox for selecting a new translation language for the current
	 * page (create a new "Alternative Page Header".
	 *
	 * @return	mixed		HTML code for the selectorbox or FALSE if no new translation can be created.
	 * @access	protected
	 */
	function sidebar_renderItem_renderNewTranslationSelectorbox() {
		if (!$GLOBALS['BE_USER']->isPSet($this->pObj->calcPerms, 'pages', 'edit')) {
			return FALSE;
		}

		$output = '';

		if (($moutput = $this->sidebar_renderItem_renderLanguageSelectorbox_pure_missing())) {
			$output = '
				<tr class="bgColor4">
					<td width="20">' .
						t3lib_BEfunc::cshItem('_MOD_web_txtemplavoilaM1', 'createnewtranslation', $this->doc->backPath) .'
					</td>
					<td width="200" valign="middle">' .
						$GLOBALS['LANG']->getLL('createnewtranslation', 1) . ':
					</td>
					<td valign="middle" style="padding: 4px;">' .
						$moutput . '
					</td>
				</tr>
			';
		}

		return $output;
	}

	/**
	 * Renders the localization menu item. It contains the language selector, the create new translation button and other settings
	 * related to localization.
	 *
	 * @param	$pObj:		Reference to the sidebar's parent object (the page module). Not used here, we use our own reference, $this->pObj.
	 * @return	string		HTML output
	 * @access	public
	 */
	function sidebar_renderItem(&$pObj) {
		$iOutput = $this->sidebar_renderItem_renderLanguageSelectorbox() .
			   $this->sidebar_renderItem_renderNewTranslationSelectorbox();

		$output = (!$iOutput ? '' : '
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr class="bgColor4-20">
					<th colspan="2">&nbsp;</th>
				</tr>
				'.
				$iOutput .
				'
			</table>
		');

		return $output;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_localization.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod1/class.tx_templavoila_mod1_localization.php']);
}

?>