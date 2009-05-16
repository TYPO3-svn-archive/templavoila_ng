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
 * Submodule 'files' for the templavoila control-center module
 *
 * @author   Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor Niels Fröhling <niels@frohling.biz>
 */

/**
 * Submodule 'files' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_mod2_files {


	// References to the control-center module object
	var $pObj;	// A pointer to the parent object, that is the templavoila control-center module script. Set by calling the method init() of this class.
	var $doc;	// A reference to the doc object of the parent object.
	var $modifiable;


	/**
	 * Initializes the files object. The calling class must make sure that the right locallang files are already loaded.
	 * This method is usually called by the templavoila control-center module.
	 *
	 * @param	$pObj:		Reference to the parent object ($this)
	 * @return	void
	 * @access	public
	 */
	function init(&$pObj) {
		global $BE_USER;

		// Make local reference to some important variables:
		$this->pObj = &$pObj;
		$this->doc = &$this->pObj->doc;
		$this->extKey = &$this->pObj->extKey;
		$this->modTSconfig = &$this->pObj->modTSconfig;
		$this->MOD_SETTINGS = &$this->pObj->MOD_SETTINGS;

		// Module may be allowed, but modify may not
		$this->modifiable =
			$BE_USER->check('tables_modify', 'tx_templavoila_datastructure') &&
			$BE_USER->check('tables_modify', 'tx_templavoila_tmplobj');
	}


	/**
	 * Creates a list of all template files used in TOs
	 *
	 * @return	string		HTML table
	 */
	function renderTemplateFileList($tFileList) {

		$output = '';

		if (is_array($tFileList)) {
			$output = '';

			// USED FILES:
			$tRows = array();

			$i = 0;
			foreach ($tFileList as $tFile => $count) {
				$tRows[] = '
					<tr class="' . ($i++ % 2 == 0 ? 'bgColor4' : 'bgColor6') . '">
						<td>
							<a href="' . htmlspecialchars($this->doc->backPath . '../' . substr($tFile, strlen(PATH_site))) . '" target="_blank">'.
								'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/zoom.gif', 'width="11" height="12"') . ' alt="" class="absmiddle" /> ' .
								htmlspecialchars(substr($tFile,strlen(PATH_site))) . '
							</a></td>
						<td align="center">' . $count . '</td>' . ($this->modifiable ? '
						<td align="center">
							<a href="' . htmlspecialchars($this->pObj->cm1Script . 'id=' . $this->pObj->id . '&_new=1&file=' . rawurlencode($tFile)) . '&mapElPath=%5BROOT%5D">'.
								'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/new_el.gif', 'width="11" height="12"') . ' alt="" class="absmiddle" /> ' .
								htmlspecialchars('Create...') . '
							</a></td>' : '') . '
					</tr>';
			}

			if (count($tRows) > 0) {
				$output .= '
				<h3>' . $GLOBALS['LANG']->getLL('center_templates_used') . ':</h3>
				<table border="0" cellpadding="1" cellspacing="1" class="typo3-dblist typo3-tvlist">
				<colgroup>
					<col width="*"  align="left" />
					<col width="80" align="center" />' . ($this->modifiable ? '
					<col width="80" align="center" />' : '') . '
				</colgroup>
				<thead>
					<tr class="c-headLineTable" style="font-weight: bold; color: #FFFFFF;">
						<th>' . $GLOBALS['LANG']->getLL('center_templates_file') . '</th>
						<th>' . $GLOBALS['LANG']->getLL('center_templates_count') . '</th>' . ($this->modifiable ? '
						<th>' . $GLOBALS['LANG']->getLL('center_templates_new') . '</th>' : '') . '
					</tr>
				</thead>
				<tbody>
					' . implode('', $tRows) . '
				</tbody>
				</table>
				';
			}
		}

		// TEMPLATE ARCHIVE:
		if ($this->modTSconfig['properties']['templatePath']) {
			$paths = t3lib_div::trimExplode(',', $this->modTSconfig['properties']['templatePath'], true);
			$prefix = t3lib_div::getFileAbsFileName($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir']);

			if (count($paths) > 0 && is_array($GLOBALS['FILEMOUNTS'])) {
				foreach ($GLOBALS['FILEMOUNTS'] as $mountCfg) {
					// look in paths if it's part of mounted path
					$isPart = false;
					$files = array();
					foreach ($paths as $path) {
						if (t3lib_div::isFirstPartOfStr($prefix . $path, $mountCfg['path'])) {
							$isPart = true;
							$files = array_merge(t3lib_div::getFilesInDir($prefix . $path, 'html,htm,tmpl', 1), $files);
						}
					}

					if ($isPart) {
						// USED FILES:
						$tRows = array();

                    				$i = 0;
						foreach($files as $tFile) {
							$tRows[] = '
								<tr class="' . ($i++ % 2 == 0 ? 'bgColor4' : 'bgColor6') . '">
									<td>
										<a href="' . htmlspecialchars($this->doc->backPath . '../' . substr($tFile, strlen(PATH_site))) . '" target="_blank">' .
											'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/zoom.gif','width="11" height="12"') . ' alt="" class="absmiddle" /> ' .
											htmlspecialchars(substr($tFile, strlen(PATH_site))) . '
										</a></td>
									<td align="center">' . ($tFileList[$tFile] ? $tFileList[$tFile] : '-') . '</td>' . ($this->modifiable ? '
									<td align="center">
										<a href="' . htmlspecialchars($this->pObj->cm1Script . 'id=' . $this->pObj->id . '&_new=1&&file=' . rawurlencode($tFile)) . '&mapElPath=%5BROOT%5D">' .
											'<img' . t3lib_iconWorks::skinImg($this->doc->backPath, 'gfx/new_el.gif','width="11" height="12"') . ' alt="" class="absmiddle" /> ' .
											htmlspecialchars('Create...') . '
										</a></td>' : '') . '
								</tr>';
						}

						if (count($tRows) > 0) {
							$output .= '
							<h3>' . $GLOBALS['LANG']->getLL('center_templates_unused') . ':</h3>
							<table border="0" cellpadding="1" cellspacing="1" class="typo3-dblist typo3-tvlist">
							<colgroup>
								<col width="*"  align="left" />
								<col width="80" align="center" />' . ($this->modifiable ? '
								<col width="80" align="center" />' : '') . '
							</colgroup>
							<thead>
								<tr class="c-headLineTable" style="font-weight: bold; color: #FFFFFF;">
									<th>' . $GLOBALS['LANG']->getLL('center_templates_file') . '</th>
									<th>' . $GLOBALS['LANG']->getLL('center_templates_count') . '</th>' . ($this->modifiable ? '
									<th>' . $GLOBALS['LANG']->getLL('center_templates_new') . '</th>' : '') . '
								</tr>
							</thead>
							<tbody>
								' . implode('', $tRows) . '
							</tbody>
							</table>
							';
						}
					}
				}
			}
		}

		return $output;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_files.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/mod2/class.tx_templavoila_mod2_files.php']);
}

?>