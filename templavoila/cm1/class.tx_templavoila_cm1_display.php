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
 * Submodule 'display' for the templavoila control-center module
 *
 * @author   Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   55: class tx_templavoila_cm1_display
 *   83:     function init(&$pObj)
 *   98:     function displayFrameError($error)
 *  123:     function displayFileContentWithPreview($content, $relPathFix)
 *  164:     function displayFileContentWithMarkup($content, $path, $relPathFix, $limitTags)
 *  218:     function renderModuleContent()
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * Submodule 'display' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_cm1_display {


	// References to the control-center module object
	var $pObj;			// A pointer to the parent object, that is the templavoila control-center module script. Set by calling the method init() of this class.
	var $doc;			// A reference to the doc object of the parent object.

	// GPvars:
	var $displayFile = '';		// (GPvar "file", shared with DISPLAY mode!) The file to display, if file is referenced directly from filelist module. Takes precedence over displayTable/displayUid
	var $show;			// Boolean; if true no mapping-links are rendered.
	var $preview;			// Boolean; if true, the currentMappingInfo preview data is merged in
	var $path;			// HTML-path to explode in template.
	var $limitTags;			// String, list of tags to limit display by

	var $tagProfiles = array(
		'' => '',
		1 => 'body,h1,h2,h3,h4,h5,h6,div,hr,p,pre,blockquote,address',
		2 => 'abbr,acronym,br,em,font,span,link,strong,b,u,i,code,cite,q,dfn,ins,del',
		3 => 'form,fieldset,legend,input,select,label,textarea,button,optgroup,option',
		4 => 'area,map,a,img,object,embed',
		5 => 'table,caption,tbody,thead,tfoot,tr,th,td,colgroup,col',
		6 => 'dl,dt,dd,ol,ul,li'
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
	 * Outputs a simple HTML page with an error message
	 *
	 * @param	string		Error message for output in <h2> tags
	 * @return	void		Echos out an HTML page.
	 */
	function displayFrameError($error) {
		return '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Untitled</title>
</head>

<body bgcolor="#eeeeee">
<h2>ERROR: '.$error.'</h2>
</body>
</html>
		';
	}


	/**
	 * This will add preview data to the HTML file used as a template according to the currentMappingInfo
	 *
	 * @param	string		The file content as a string
	 * @param	string		The rel-path string to fix images/links with.
	 * @return	void		Exits...
	 * @see main_display()
	 */
	function displayFileContentWithPreview($content, $relPathFix) {
		// Init mark up object.
		$markupObj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');
		$markupObj->htmlParse = t3lib_div::makeInstance('t3lib_parsehtml');

		// Getting session data to get currentMapping info:
		$sesDat = $GLOBALS['BE_USER']->getSessionData($this->MCONF['name'] . '_mappingInfo');
		$currentMappingInfo = is_array($sesDat['currentMappingInfo']) ? $sesDat['currentMappingInfo'] : array();

		// Splitting content, adding a random token for the part to be previewed:
		$contentSplittedByMapping = $markupObj->splitContentToMappingInfo($content, $currentMappingInfo);
		$token = md5(microtime());
		$content = $markupObj->mergeSampleDataIntoTemplateStructure($sesDat['dataStruct'], $contentSplittedByMapping, $token);

		// Exploding by that token and traverse content:
		$pp = explode($token, $content);
		foreach($pp as $kk => $vv) {
			$pp[$kk] = $markupObj->passthroughHTMLcontent($vv, $relPathFix, $this->MOD_SETTINGS['displayMode'], $kk == 1 ? 'font-size: 11px; color: #000066;' : '');
		}

		// Adding a anchor point (will work in most cases unless put into a table/tr tag etc).
		if (trim($pp[0])) {
			$pp[1] = '<a name="_MARKED_UP_ELEMENT"></a>' . $pp[1];
		}

		// Implode content and return it:
		return implode('', $pp);
	}


	/**
	 * This will mark up the part of the HTML file which is pointed to by $path
	 *
	 * @param	string		The file content as a string
	 * @param	string		The "HTML-path" to split by
	 * @param	string		The rel-path string to fix images/links with.
	 * @param	string		List of tags to show
	 * @return	void		Exits...
	 * @see main_display()
	 */
	function displayFileContentWithMarkup($content, $path, $relPathFix, $limitTags) {
		// Init mark up object.
		$markupObj = t3lib_div::makeInstance('tx_templavoila_htmlmarkup');
		$markupObj->htmlParse = t3lib_div::makeInstance('t3lib_parsehtml');

		// Select shown tags of mark up object.
		if (!empty($this->MOD_SETTINGS['displayTags'])) {
			$baseTags = explode(',', $this->tagProfiles[$this->MOD_SETTINGS['displayTags']]);
		} else {
			$baseTags = array_keys($markupObj->tags);
		}

		if (!empty($limitTags)) {
			$limitTags = explode(',', $limitTags);
		} else {
			$limitTags = array_keys($markupObj->tags);
		}

		$validTags = implode(',', array_keys($markupObj->tags));
		$showTags = implode(',', array_intersect($baseTags, $limitTags));

		// Set options of mark up object.
		$markupObj->gnyfImgAdd = $this->show ? '' : 'onclick="return parent.updPath(\'###PATH###\');"';
		$markupObj->pathPrefix = $path ? $path . '|' : '';
		$markupObj->onlyElements = $showTags;
#		$markupObj->setTagsFromXML($content);

		$cParts = $markupObj->splitByPath($content, $path);
		if (is_array($cParts)) {
			$cParts[0] = $markupObj->passthroughHTMLcontent($cParts[0], $relPathFix, $this->MOD_SETTINGS['displayMode']);
			$cParts[2] = $markupObj->passthroughHTMLcontent($cParts[2], $relPathFix, $this->MOD_SETTINGS['displayMode']);
			$cParts[1] = $markupObj->markupHTMLcontent(
				$cParts[1],
				$GLOBALS['BACK_PATH'],
				$relPathFix,
				$validTags,
				$this->MOD_SETTINGS['displayMode']
			);

			if (trim($cParts[0])) {
				$cParts[1] = '<a name="_MARKED_UP_ELEMENT"></a>' . $cParts[1];
			}

			return implode('', $cParts);
		}

		return $this->displayFrameError($cParts);
	}


	/**
	 * Renders module content, HTML display
	 *
	 * @return	void
	 */
	function renderModuleContent() {
		// Setting GPvars:
		$this->displayFile = t3lib_div::_GP('file');
		$this->show = t3lib_div::_GP('show');
		$this->preview = t3lib_div::_GP('preview');
		$this->limitTags = t3lib_div::_GP('limitTags');
		$this->path = t3lib_div::_GP('path');

		// Checking if the displayFile parameter is set:
		if (@is_file($this->displayFile) && t3lib_div::getFileAbsFileName($this->displayFile)) {
			// FUTURE: grabbing URLS?: 		.... || substr($this->displayFile,0,7)=='http://'
			if (($content = t3lib_div::getUrl($this->displayFile))) {
				$relPathFix = $GLOBALS['BACK_PATH'] . '../' . dirname(substr($this->displayFile, strlen(PATH_site))) . '/';

				// In preview mode, merge preview data into the template:
				if ($this->preview) {
					// Add preview data to file:
					$this->content = $this->displayFileContentWithPreview($content, $relPathFix);
				} else {
					// Markup file:
					$this->content = $this->displayFileContentWithMarkup($content, $this->path, $relPathFix, $this->limitTags);
				}
			} else {
				$this->content = $this->displayFrameError($GLOBALS['LANG']->getLL('errorNoContentInFile') . ': <em>' . htmlspecialchars($this->displayFile) . '</em>');
			}
		} else {
			$this->content = $this->displayFrameError($GLOBALS['LANG']->getLL('errorNoFile'));
		}

		return $this->content;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/cm1/class.tx_templavoila_cm1_display.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/cm1/class.tx_templavoila_cm1_display.php']);
}

?>