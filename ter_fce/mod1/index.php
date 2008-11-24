<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Steffen Kamper <info@sk-typo3.de>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

	// Include TemplaVoila API
require_once (t3lib_extMgm::extPath('templavoila').'class.tx_templavoila_api.php');

$LANG->includeLLFile('EXT:ter_fce/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'FCE Repository' for the 'ter_fce' extension.
 *
 * @author	Steffen Kamper <info@sk-typo3.de>
 * @package	TYPO3
 * @subpackage	tx_terfce
 */
class  tx_terfce_module1 extends t3lib_SCbase {
				var $pageinfo;
				var $tvAPI;
				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// Initialize TemplaVoila API class:
					$apiClassName = t3lib_div::makeInstanceClassName('tx_templavoila_api');
					$this->tvAPI = new $apiClassName ('pages');
		
		
					parent::init();

					/*
					if (t3lib_div::_GP('clear_all_cache'))	{
						$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
					}
					*/
				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function1'),
							'2' => $LANG->getLL('function2'),
							'3' => $LANG->getLL('function3'),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;

					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('bigDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" method="POST">';

							// JavaScript
						$this->doc->JScode = '
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$this->doc->inDocStyles .= ' 
							table.fce-table td {vertical-align: top; padding:2px; border-bottom:1px solid #aaa;}  
							table.fce-table td img.previewimg {float:left;}
							
						';
						
						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent() {
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
							$content = $this->listFCEs();
							$this->content.=$this->doc->section('Message #1:',$content,0,1);
						break;
						case 2:
							$content='<div align=center><strong>Menu item #2...</strong></div>';
							$this->content.=$this->doc->section('Message #2:',$content,0,1);
						break;
						case 3:
							$content='<div align=center><strong>Menu item #3...</strong></div>';
							$this->content.=$this->doc->section('Message #3:',$content,0,1);
						break;
					}
				}
				
				function listFCEs() {
					$sql = 'SELECT ds . * , to.uid to_uid, to.fileref, to.description, to.previewicon to_icon
							FROM `tx_templavoila_datastructure` ds
							LEFT JOIN tx_templavoila_tmplobj `to` ON to.datastructure = ds.uid
							WHERE ds.scope=2';

					$res=$GLOBALS['TYPO3_DB']->sql_query($sql);
					$content = '<table cellspacing="0" cellpadding="0" border="0" class="fce-table">
					<tr class="bgColor5">
						<td>Title</td>
						<td>Description</td>
						<td>DS</td>
						<td>TO</td>
						<td>File</td>
						<td>Action</td>
					</tr>';
					
					while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {         
#t3lib_div::debug($row);     
						$fileReference = t3lib_div::getFileAbsFileName($row['fileref']);
						
						//icons
						$iconDetails = '<a href="index.php?viewdetails=' . $row['uid'] . '"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/magnifier.png', 'width="16" height="16"').' title="view details" alt="view details" /></a>';
						$iconSave = '<a href="index.php?savetofile=' . $row['uid'] . '"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/savedok.gif', 'width="16" height="16"').' title="save" alt="save" /></a>';
						
						$content .= '
						<tr>
							<td><b>' . htmlspecialchars($row['title']) . '</b></td>
							<td class="bgColor5">' . $row['description'] . '</td>
							<td>' . ($row['previewicon'] ? t3lib_BEfunc::getThumbNail($GLOBALS['BACK_PATH'] . 'thumbs.php', PATH_site . 'uploads/tx_templavoila/' . $row['previewicon'], 'hspace="5" vspace="5" border="1" class="previewimg"', '80') : '') . '&nbsp;UID: ' . $row['uid'] . '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_datastructure]['.$row['uid'].']=edit',$this->doc->backPath)).'"><img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' alt="" class="absmiddle" /></a>' . '</td>
							<td class="bgColor5">' . ($row['to_icon'] ? t3lib_BEfunc::getThumbNail($GLOBALS['BACK_PATH'] . 'thumbs.php', PATH_site . 'uploads/tx_templavoila/' . $row['to_icon'], 'hspace="5" vspace="5" border="1" class="previewimg"', '80') : '') . '&nbsp;UID: ' . $row['to_uid'] . '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick('&edit[tx_templavoila_tmplobj]['.$row['to_uid'].']=edit',$this->doc->backPath)).'"><img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' alt="" class="absmiddle" /></a>' . '</td>
							<td>' . '<a href="'.htmlspecialchars($this->doc->backPath.'../'.substr($fileReference,strlen(PATH_site))).'" target="_blank">'.htmlspecialchars($row['fileref']).'</a>' . '</td>
							<td class="bgColor5">' . $iconDetails . $iconSave . '</td>
						</tr>';
						
						if (intval(t3lib_div::_GP('viewdetails')) == $row['uid']) {
							$content .= $this->viewDetails($row);
						}
					}
					
					
					return $content;
				}
				
				private function viewDetails($row) {
					
					$details = '<h3>Details from FCE "' . htmlspecialchars($row['title']) . '"</h3>';
					$content = '
					<tr>
						<td class="bgColor3" colspan="6">
						' . $details . '
						</td>
					</tr>';
					return $content;
				}
			}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fce/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ter_fce/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_terfce_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
