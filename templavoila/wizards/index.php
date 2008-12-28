<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2006 Robert Lemke (robert@typo3.org)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * New elements wizard for templavoila
 *
 * @author	Robert Lemke <robert@typo3.org>
 * @coauthor	Kasper Skaarhoj <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */

require_once('conf.php');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');

// Merging locallang files/arrays:
$GLOBALS['LANG']->includeLLFile('EXT:lang/locallang_misc.xml');
$LOCAL_LANG_orig = $GLOBALS['LOCAL_LANG'];

$GLOBALS['LANG']->includeLLFile('EXT:templavoila/wizards/locallang_content.xml');
$GLOBALS['LANG']->includeLLFile('EXT:templavoila/wizards/locallang_page.xml');
$GLOBALS['LANG']->includeLLFile('EXT:templavoila/wizards/locallang_site.xml');
$LOCAL_LANG = t3lib_div::array_merge_recursive_overrule($LOCAL_LANG_orig, $GLOBALS['LOCAL_LANG']);

$LANG->includeLLFile('EXT:templavoila/wizards/locallang.xml');

// Exits if 'cms' extension is not loaded:
t3lib_extMgm::isLoaded('cms', 1);

// We need the TCE forms functions
require_once(PATH_t3lib . 'class.t3lib_page.php');
require_once(PATH_t3lib . 'class.t3lib_tceforms.php');

// Include class which contains the constants and definitions of TV
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_defines.php');
require_once(t3lib_extMgm::extPath('templavoila') . 'class.tx_templavoila_api.php');

/**
 * Module 'Wizard' for the 'templavoila' extension.
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_wizard extends t3lib_SCbase {

	// External static:
	var $pageinfo;
	var $modTSconfig;
	var $extKey = 'templavoila';			// Extension key of this module
	var $baseScript = 'index.php?';
	var $mod1Script = '../mod1/index.php?';
	var $mod2Script = '../mod2/index.php?';
	var $cm1Script = '../cm1/index.php?';

	var $errorsWarnings = array();

	/**
	 * @var tx_templavoila_api
	 */
	var $apiObj;					// Instance of tx_templavoila_api

	function init() {
		parent::init();

		if (preg_match('/mod.php$/', PATH_thisScript)) {
			$this->baseScript = 'mod.php?M=tx_templavoila_wizards&';
			$this->mod1Script = 'mod.php?M=web_txtemplavoilaM1&';
			$this->mod2Script = 'mod.php?M=web_txtemplavoilaM2&';
			$this->cm1Script = 'mod.php?M=xMOD_txtemplavoilaCM1&';
		}

		// Initialize TemplaVoila API class:
		$apiClassName = t3lib_div::makeInstanceClassName('tx_templavoila_api');
		$this->apiObj = new $apiClassName ($this->altRoot ? $this->altRoot : 'pages');
	}

	/**
	 * Preparing menu content
	 *
	 * @return	void
	 */
	function menuConfig()	{
		$this->MOD_MENU = array(
			'wiz_step' => ''
		);

		// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id , 'mod.' . $this->MCONF['name']);

		// CLEANSE SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::GPvar('SET'), $this->MCONF['name']);
	}

	/**
	 * Main function of the module.
	 *
	 * @return	void		Nothing.
	 */
	function main() {
		global $BE_USER, $BACK_PATH;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if ($access) {
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('noDoc');
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->divClass = '';
			$this->doc->form = '<form action="' . htmlspecialchars($this->baseScript . 'id=' . $this->id) . '" method="post" autocomplete="off">';

			// Add custom styles
			$this->doc->styleSheetFile2 = t3lib_extMgm::extRelPath($this->extKey) . "wizards/styles.css";

			// Adding classic jumpToUrl function, needed for the function menu.
			// Also, the id in the parent frameset is configured.
			$this->doc->JScode = $this->doc->wrapScriptTags('
				function jumpToUrl(URL)	{ //
					document.location = URL;
					return false;
				}
				if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
			');

			// Setting up support for context menus (when clicking the items icon)
			$CMparts = $this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode .= $CMparts[0];
			$this->doc->JScode .= $this->doc->getDynTabMenuJScode();
			$this->doc->postCode .= $CMparts[2];

			// size-wizard -----------------------------------------------------------------------
			if ($this->MOD_SETTINGS['wiz_step']) {
				require_once(t3lib_extMgm::extPath('templavoila') . 'wizards/class.tx_templavoila_wizards_site.php');

				$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('newSite'));
				$this->content .= $this->doc->header($GLOBALS['LANG']->getLL('newSite'));
				$this->content .= $this->doc->spacer(5);

				// Initialize the wizard
				$wizardObj =& t3lib_div::getUserObj('&tx_templavoila_wizards_site', '');
				$wizardObj->init($this);

				// Render the wizard
				$this->content .= $wizardObj->renderNewSiteWizard_run();
			}
			// page-wizard -----------------------------------------------------------------------
			else if (($cmd = t3lib_div::_GP('cmd'))) {
				require_once(t3lib_extMgm::extPath('templavoila') . 'wizards/class.tx_templavoila_wizards_page.php');

				$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('createnewpage_title'));
				$this->content .= $this->doc->header($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:db_new.php.pagetitle'));
				$this->content .= $this->doc->spacer(5);

				$pid = t3lib_div::_GP('positionPid');

				// Create a new page
				if (($cmd == 'crPage') && intval($pid)) {
					// Initialize the wizard
					$wizardObj =& t3lib_div::getUserObj('&tx_templavoila_wizards_page', '');
					$wizardObj->init($this);

					$this->content .= $wizardObj->renderWizard_createNewPage($pid);
				}
				// Render nothing
				else
					$this->content  = '';
			}
			// content-wizard --------------------------------------------------------------------
			else if ($this->id) {
				require_once(t3lib_extMgm::extPath('templavoila') . 'wizards/class.tx_templavoila_wizards_content.php');

				$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('newContentElement'));
				$this->content .= $this->doc->header($GLOBALS['LANG']->getLL('newContentElement'));
				$this->content .= $this->doc->spacer(5);

				// Initialize the wizard
				$wizardObj =& t3lib_div::getUserObj('&tx_templavoila_wizards_content', '');
				$wizardObj->init($this);

				// Render the wizard
				$this->content .= $wizardObj->renderWizard_createNewContentElement();
			}
			else {
				$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
				$this->content .= $this->doc->header($GLOBALS['LANG']->getLL('title'));
				$this->content .= $this->doc->spacer(5);
			}

		// No access or no current uid:
		} else {
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('noDoc');
			$this->doc->docType= 'xhtml_trans';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->divClass = '';
			$this->doc->form = '<form action="' . htmlspecialchars($this->baseScript . 'id=' . $this->id) . '" method="post" autocomplete="off">';

			$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
		}

		$this->content .= $this->doc->endPage();
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent() {
		echo $this->content;
	}

}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_templavoila_wizard');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

/**
 * Module 'TemplaVoila' for the 'templavoila' extension.
 * Modern integrated style
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_wizard_integral extends tx_templavoila_wizard {
}
?>