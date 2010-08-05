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
 * Submodule 'browser' for the templavoila control-center module
 *
 * @author   Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   56: class tx_templavoila_cm1_browser
 *  109:     function init(&$pObj)
 *  126:     function getConfigArray()
 *  160:     function showTemplate($conf, $pObj = false)
 *  220:     function renderModuleContent()
 *  279:     function flip(li)
 *  298:     function touch(loc)
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * Submodule 'browser' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_cm1_browser {


	// References to the control-center module object
	var $pObj;	// A pointer to the parent object, that is the templavoila control-center module script. Set by calling the method init() of this class.
	var $doc;	// A reference to the doc object of the parent object.

	var $sys_page;
	var $tmpl;

	// Available content objets
	var $cTypes = array(
		'HTML',
		'TEXT',
		'COBJ_ARRAY',
		'COA',
		'COA_INT',
		'FILE',
		'IMAGE',
		'IMG_RESOURCE',
		'CLEARGIF',
		'CONTENT',
		'RECORDS',
		'HMENU',
		'CTABLE',
		'OTABLE',
		'COLUMNS',
		'HRULER',
		'IMGTEXT',
		'CASE',
		'LOAD_REGISTER',
		'RESTORE_REGISTER',
		'FORM',
		'SEARCHRESULT',
		'USER',
		'USER_INT',
		'PHP_SCRIPT',
		'PHP_SCRIPT_INT',
		'PHP_SCRIPT_EXT',
		'TEMPLATE',
		'MULTIMEDIA',
		'EDITPANEL',
	);


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
	 * Get TS template
	 *
	 * This function creates instances of the class needed to render
	 * the TS template, and gets it as a multi-dimensionnal array.
	 *
	 * @return		An array containing all the available TS objects
	 */
	function getConfigArray() {

		// Initialize the page selector
		$this->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$this->sys_page->init(true);

		// initialize the TS template
		$this->tmpl = t3lib_div::makeInstance('t3lib_TStemplate');
		$this->tmpl->init();

		// Avoid an error
		$this->tmpl->tt_track = 0;

		// Get rootline for current PID
		$rootline = $this->sys_page->getRootLine(t3lib_div::_GP('pid'));

		// Start TS template
		$this->tmpl->start($rootline);

		//Return configuration array
		return $this->tmpl->setup;
	}


	/**
	 * Show TS template hierarchy
	 *
	 * This function displays the TS template hierarchy as HTML list
	 * elements. Each section can be expanded/collapsed.
	 *
	 * @param		$object		A section of the TS template
	 * @param		$object		The path to the current object
	 * @return
	 */
	function showTemplate($conf, $pObj = false) {

		// Storage
		$htmlCode = array();
		$curr = t3lib_div::_GP('current');

		// Process each object of the configuration array
		foreach ($conf as $key => $value) {

			// TS object ID
			$id = $pObj . $key;
			$sel = (strpos($curr, $id) === 0);
			$mtc = ($curr == $id);

			// Check if object is a container
			if (is_array($value)) {

				// Check if object has a content type
				if (substr($key, 0, strlen($key) - 1) != $lastKey) {

					// No content type - Process sub configuration
					$subArray = $this->showTemplate($value, $id);

					// Check if objects are available
					if ($subArray) {

						// Add container
						$htmlCode[] = '<li class="pm ' . ($sel ? 'minus act' : 'plus') . '" ' . ($mtc ? 'id="selected"' : '') . ' name="' . $key . '"><strong onclick="flip(this.parentNode);">' . $key . '</strong>' . $subArray . '</li>';
					}
				}
			}
			else if (in_array($value, $this->cTypes)) {

				// Memorize key
				$lastKey = $key;

				// TS object
				$htmlCode[] = '<li class="' . ($sel ? 'act' : '') . '" ' . ($mtc ? 'id="selected"' : '') . ' name="' . $key . '"><span onclick="touch(this);">' . $key . '</span> <em>['. $value . ']</em></li>';
			}
		}

		// Check if objects have been detected
		if (count($htmlCode)) {
		//	array_push($htmlCode,  str_replace(  '<li class="' , '<li class="last ', array_pop($htmlCode)));
			array_push($htmlCode, preg_replace('/^<li class="/', '<li class="last ', array_pop($htmlCode)));

			// Return hierarchy
			return
			'<ul class="' . (!$pObj ? 'tree' : '') . '">' .
			implode(chr(10), $htmlCode) .
			'</ul>';
		}
	}


	/**
	 * Renders module content, TS browser
	 *
	 * @return	void
	 */
	function renderModuleContent() {
		$this->content = '<html><head><style type="text/css">
			html, body {
				font-size: 12px;
				padding: 0;
				margin: 0;
			}

			ul, li {
				padding: 0;
				margin: 0;

				list-style: none;
				line-height: 16px;
			}

			li {
				padding-left: 20px;
			}

			ul.tree {
				padding-left: 2px; }
			ul.tree li {
				background: 0 0 url(/typo3/gfx/ol/join.gif) no-repeat transparent;  }
			ul.tree li > span {
				cursor: pointer; }
			ul.tree li > strong {
				cursor: pointer; }
			ul.tree li.last {
				background: 0 0 url(/typo3/gfx/ol/joinbottom.gif) no-repeat transparent; }
			ul.tree li.act > strong {
				font-style: italic; }
			ul.tree li.act > span {
				font-style: italic; }
			ul.tree li.pm > strong {
				margin-left: -20px;
				padding-left: 20px; }
			ul.tree li.pm > ul {
				margin-left: -20px;
				padding-left: 20px;
				background: 0 0 url(/typo3/gfx/ol/line.gif) repeat-y transparent; }
			ul.tree li.last.pm > ul {
				background: none; }
			ul.tree li.minus {
				background: 0 0 url(/typo3/gfx/ol/minus.gif) no-repeat transparent; }
			ul.tree li.plus {
				background: 0 0 url(/typo3/gfx/ol/plus.gif) no-repeat transparent; }
			ul.tree li.minus.last {
				background: 0 0 url(/typo3/gfx/ol/minusbottom.gif) no-repeat transparent; }
			ul.tree li.plus.last {
				background: 0 0 url(/typo3/gfx/ol/plusbottom.gif) no-repeat transparent; }
			ul.tree li.minus > ul {
				display: block; }
			ul.tree li.plus > ul {
				display: none; }


		</style><script type="text/javascript">

			function flip(li) {
				if (li.className.match(/plus/)) {
					li.className =
					li.className.replace(/plus/, \'minus\');
				}
				else {
					li.className =
					li.className.replace(/minus/, \'plus\');
				}

				parent.setSizeBrowseFrame(document.body.firstChild.scrollHeight);
			}

			function touch(loc) {
				var old = document.getElementById(\'selected\');
				var str = \'\';

				if (old) {
					old.id = \'\';
					while (old) {
						if (old.className) {
							old.className =
							old.className.replace(/act/, \'\');
						}

						old = old.parentNode;
					}
				}

				if (loc) {
					loc.parentNode.id = \'selected\';
					while (loc) {
						if (loc.hasAttribute)
						if (loc.hasAttribute(\'name\'))
							str = loc.getAttribute(\'name\') + str;

						if (loc.tagName)
						if (loc.tagName.toLowerCase() == \'li\')
							loc.className += \' act\';

						loc = loc.parentNode;
					}
				}

				parent.setFormValueFromBrowseFrame(str);
			}
		</script></head><body>';

		// Get TypoScript template for current page
		$conf = $this->getConfigArray();

		// Show TS template hierarchy
		$this->content .= $this->showTemplate($conf);

		$this->content .= '<script type="text/javascript">
			parent.setSizeBrowseFrame(document.body.firstChild.scrollHeight);
		</script>';

		$this->content .= '</body></html>';

		return $this->content;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/cm1/class.tx_templavoila_cm1_browser.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/cm1/class.tx_templavoila_cm1_browser.php']);
}

?>