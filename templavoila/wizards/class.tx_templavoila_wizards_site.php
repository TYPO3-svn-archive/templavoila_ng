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
 * Submodule 'wizard' for the templavoila control-center module
 *
 * @author   Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor Niels Fröhling <niels@frohling.biz>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */

/**
 * Submodule 'wizard' for the templavoila control-center module
 *
 * @author	Kasper Sk?rh?j <kasper@typo3.com>
 * @coauthor	Niels Fröhling <niels@frohling.biz>
 * @package	TYPO3
 * @subpackage	tx_templavoila
 */
class tx_templavoila_wizards_site {


	// References to the page module object
	var $pObj;		// A pointer to the parent object, that is the templavoila page module script. Set by calling the method init() of this class.
	var $doc;		// A reference to the doc object of the parent object.
	var $extKey;		// A reference to extension key of the parent object.


	/**
	 * Initializes the wizard object. The calling class must make sure that the right locallang files are already loaded.
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

		$GLOBALS['LANG']->includeLLFile('EXT:templavoila/wizards/locallang_site.xml');
	}


	/******************************
	 *
	 * Wizard for new site
	 *
	 *****************************/

	var $wizardData = array();	// Session data during wizard
	var $importPageUid = 0;		// Import as first page in root!


	/**
	 * Wizard overview page - before the wizard is started.
	 *
	 * @return	void
	 */
	function renderNewSiteWizard_overview()	{
		global $BE_USER;

		$this->content = '';

		if ($BE_USER->isAdmin()) {
			// Introduction:
			$outputString .= nl2br(htmlspecialchars(trim('
			If you want to start a new website based on the TemplaVoila template engine you can start this wizard which will set up all the boring initial stuff for you.
			You will be taken through these steps:
			- Creation of a new website root, storage folder, sample pages.
			- Creation of the main TemplaVoila template, including mapping of one content area and a main menu.
			- Creation of a backend user and group to manage only that website.

			You should prepare an HTML template before you begin the wizard; simply make a design in HTML and place the HTML file including graphics and stylesheets in a subfolder of "' . $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] . 'templates/" relative to the websites root directory.
			Tip about menus: If you include a main menu in the template, try to place the whole menu inside a container (like <div>, <table> or <tr>) and encapsulate each menu item in a block tag (like <tr>, <td> or <div>). Use A-tags for the links. If you want different designs for normal and active menu elements, design the first menu item as "Active" and the second (and rest) as "Normal", then the wizard might be able to capture the right configuration.
			Tip about stylesheets: The content elements from TYPO3 will be outputted in regular HTML tags like <p>, <h1> to <h6>, <ol> etc. You will prepare yourself well if your stylesheet in the HTML template provides good styles for these standard elements from the start. Then you will have less finetuning to do later.
			')));

			// Checks:
			$missingExt  = $this->wizard_checkMissingExtensions();
			$missingConf = $this->wizard_checkConfiguration();
			$missingDir  = $this->wizard_checkDirectory();
			if (!$missingExt && !$missingConf) {
				$outputString .= '
				<br/>
				<br/>
				<input type="submit" value="' . $GLOBALS['LANG']->getLL('wiz_start') . '!" onclick="' . htmlspecialchars('document.location=\'' . $this->pObj->wizScript . 'wiz=site&SET[wiz_step]=1\'; return false;').'" />';
			} else {
				$outputString .= '
				<br/>
				<br/>
				<i>There are some technical problems you have to solve before you can start the wizard! Please see below for details. Solve these problems first and come back.</i>';

			}

			// Add output:
			$this->content .= $this->doc->section($GLOBALS['LANG']->getLL('wiz_title'), $outputString, 0, 1);

			// Missing extension warning:
			if ($missingExt) {
				$this->content .= $this->doc->section('Missing extension!', $missingExt, 0, 1, 3);
			}

			// Missing configuration warning:
			if ($missingConf) {
				$this->content .= $this->doc->section('Missing configuration!', $missingConf, 0, 1, 3);
			}

			// Missing directory warning:
			if ($missingDir) {
				$this->content .= $this->doc->section('Missing directory!', $missingDir, 0, 1, 3);
			}
		}

		return $this->content;
	}

	/**
	 * Running the wizard. Basically branching out to sub functions.
	 * Also gets and saves session data in $this->wizardData
	 *
	 * @return	void
	 */
	function renderNewSiteWizard_run() {
		global $BE_USER;

		$this->content = '';

		// Getting session data:
		$this->wizardData = $BE_USER->getSessionData('tx_templavoila_wizard');

		if ($BE_USER->isAdmin()) {
			$outputString = '';

			switch($this->MOD_SETTINGS['wiz_step'])	{
				case 1:
					$this->wizard_step1();
					break;
				case 2:
					$this->wizard_step2();
					break;
				case 3:
					$this->wizard_step3();
					break;
				case 4:
					$this->wizard_step4();
					break;
				case 5:
					$this->wizard_step5('field_menu');
					break;
				case 5.1:
					$this->wizard_step5('field_submenu');
					break;
				case 6:
					$this->wizard_step6();
					break;
			}

			$outputString .= '<hr/><input type="submit" value="Cancel wizard" onclick="' . htmlspecialchars('document.location=\'' . $this->pObj->mod2Script . 'SET[wiz_step]=0\'; return false;').'" />';

			// Add output:
			$this->content .= $this->doc->section('', $outputString, 0, 1);
		}

		// Save session data:
		$BE_USER->setAndSaveSessionData('tx_templavoila_wizard', $this->wizardData);

		return $this->content;
	}

	/**
	 * Pre-checking for extensions
	 *
	 * @return	string		If string is returned, an error occured.
	 */
	function wizard_checkMissingExtensions() {
		$outputString .= 'Before the wizard can run some extensions are required to be installed. Below you will see the which extensions are required and which are not available at this moment. Please go to the Extension Manager and install these first.';

			// Create extension status:
		$checkExtensions = explode(',', 'css_styled_content,impexp');
		$missingExtensions = FALSE;

		$tRows = array();
		$tRows[] = '<tr class="tableheader bgColor5">
			<td>Extension Key:</td>
			<td>Installed?</td>
		</tr>';

		foreach ($checkExtensions as $extKey) {
			$tRows[] = '<tr class="bgColor4">
				<td>'.$extKey.'</td>
				<td align="center">'.(t3lib_extMgm::isLoaded($extKey) ? 'Yes' : '<span class="typo3-red">No!</span>') . '</td>
			</tr>';

			if (!t3lib_extMgm::isLoaded($extKey))
				$missingExtensions = TRUE;
		}

		$outputString .= '<table border="0" cellpadding="1" cellspacing="1">' . implode('', $tRows) . '</table>';

		// If no extensions are missing, simply go to step two:
		if ($missingExtensions)	 {
			return $outputString;
		}
	}

	/**
	 * Pre-checking for TemplaVoila configuration
	 *
	 * @return	string		If string is returned, an error occured.
	 */
	function wizard_checkConfiguration() {
		$TVconfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['templavoila']);
	}

	/**
	 * Pre-checking for directory of extensions.
	 *
	 * @return	string		If string is returned, an error occured.
	 */
	function wizard_checkDirectory() {
		if (!@is_dir(PATH_site . $this->pObj->templatesDir)) {
			return nl2br('The directory "' . $this->pObj->templatesDir . '" (relative to the website root) does not exist! This is where you must place your HTML templates. Please create that directory <u>before you start the wizard</u>. In order to do so, follow these directions:

			- Go to the module File > Filelist
			- Click the icon of the "' . $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] . '" root and select "Create" from the context menu.
			- Enter the name "templates" of the folder and press the "Create" button.
			- Return to this wizard
			');
		}

		return false;
	}

	/**
	 * Wizard Step 1: Selecting template file.
	 *
	 * @return	void
	 */
	function wizard_step1()	{

		if (@is_dir(PATH_site.$this->pObj->templatesDir))	{

			$this->wizardData = array();

			$outputString.=nl2br('The first step is to select the HTML file you want to base the new website design on. Below you see a list of HTML files found in the folder "'.$this->pObj->templatesDir.'". Click the "Preview"-link to see what the file looks like and when the right template is found, just click the "Choose as template"-link in order to proceed.
				If the list of files is empty you must now copy the HTML file you want to use as a template into the template folder. When you have done that, press the refresh button to refresh the list.<br/>');

				// Get all HTML files:
			$fileArr = t3lib_div::getAllFilesAndFoldersInPath(array(),PATH_site.$this->pObj->templatesDir,'html,htm',0,1);
			$fileArr = t3lib_div::removePrefixPathFromList($fileArr,PATH_site);

				// Prepare header:
			$tRows = array();
			$tRows[] = '<tr class="tableheader bgColor5">
				<td>Path:</td>
				<td>Usage:</td>
				<td>Action:</td>
			</tr>';

				// Traverse available template files:
			foreach($fileArr as $file)	{

					// Has been used:
				$tosForTemplate = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid',
					'tx_templavoila_tmplobj',
					'fileref='.$GLOBALS['TYPO3_DB']->fullQuoteStr($file, 'tx_templavoila_tmplobj').
						t3lib_BEfunc::deleteClause('tx_templavoila_tmplobj')
					);

					// Preview link
				$onClick = 'vHWin=window.open(\''.$this->doc->backPath.'../'.$file.'\',\'tvTemplatePreview\',\'status=1,menubar=1,scrollbars=1,location=1\');vHWin.focus();return false;';

					// Make row:
				$tRows[] = '<tr class="bgColor4">
					<td>'.htmlspecialchars($file).'</td>
					<td>'.(count($tosForTemplate) ? 'Used '.count($tosForTemplate).' times' : 'Not used yet').'</td>
					<td>'.
						'<a href="#" onclick="'.htmlspecialchars($onClick).'">[Preview first]</a> '.
						'<a href="'.htmlspecialchars($this->pObj->wizScript . 'wiz=site&SET[wiz_step]=2&CFG[file]=' . rawurlencode($file)) . '">[Choose as Template]</a> '.
						'</td>
				</tr>';
			}
			$outputString.= '<table border="0" cellpadding="1" cellspacing="1" class="lrPadding">'.implode('',$tRows).'</table>';

				// Refresh button:
			$outputString.= '<br/><input type="submit" value="Refresh" onclick="'.htmlspecialchars('document.location=\'' . $this->pObj->wizScript . 'wiz=site&SET[wiz_step]=1\'; return false;').'" />';

				// Add output:
			$this->content.= $this->doc->section('Step 1: Select the template HTML file',$outputString,0,1);
		}
		else {
			$this->content .= $this->doc->section('TemplaVoila wizard error',$this->pObj->templatesDir.' is not a directory! Please, create it before starting this wizard.',0,1);
		}
	}

	/**
	 * Step 2: Enter default values:
	 *
	 * @return	void
	 */
	function wizard_step2()	{

			// Save session data with filename:
		$cfg = t3lib_div::_GET('CFG');
		if ($cfg['file'] && t3lib_div::getFileAbsFileName($cfg['file']))	{
			$this->wizardData['file'] = $cfg['file'];
		}

			// Show selected template file:
		if ($this->wizardData['file'])	{
			$outputString.= nl2br('The template file "'.htmlspecialchars($this->wizardData['file']).'" is now selected: ');
			$outputString.= '<br/><iframe src="'.htmlspecialchars($this->doc->backPath.'../'.$this->wizardData['file']).'" width="640" height="300"></iframe>';

				// Enter default data:
			$outputString.='
				<br/><br/><br/>
				Next, you should enter default values for the new website. With this basic set of information we are ready to create the initial website structure!<br/>
	<br/>
				<b>Name of the site:</b><br/>
				(Required)<br/>
				This value is shown in the browsers title bar and will be the default name of the first page in the page tree.<br/>
				<input type="text" name="CFG[sitetitle]" value="'.htmlspecialchars($this->wizardData['sitetitle']).'" /><br/>
	<br/>
				<b>URL of the website:</b><br/>
				(Optional)<br/>
				If you know the URL of the website already please enter it here, eg. "www.mydomain.com".<br/>
				<input type="text" name="CFG[siteurl]" value="'.htmlspecialchars($this->wizardData['siteurl']).'" /><br/>
	<br/>
				<b>Editor username</b><br/>
				(Required)<br/>
				Enter the username of a new backend user/group who will be able to edit the pages on the new website. (Password will be "password" by default, make sure to change that!)<br/>
				<input type="text" name="CFG[username]" value="'.htmlspecialchars($this->wizardData['username']).'" /><br/>
	<br/>
				<input type="hidden" name="SET[wiz_step]" value="3" />
				<input type="submit" name="_create_site" value="Create new site" />
			';
		}
		else {
			$outputString.= 'No template file found!?';
		}

			// Add output:
		$this->content.= $this->doc->section('Step 2: Enter default values for new site',$outputString,0,1);
	}

	/**
	 * Step 3: Begin template mapping
	 *
	 * @return	void
	 */
	function wizard_step3()	{

			// Save session data with filename:
		$cfg = t3lib_div::_POST('CFG');
		if (isset($cfg['sitetitle']))	{
			$this->wizardData['sitetitle'] = trim($cfg['sitetitle']);
		}
		if (isset($cfg['siteurl']))	{
			$this->wizardData['siteurl'] = trim($cfg['siteurl']);
		}
		if (isset($cfg['username']))	{
			$this->wizardData['username'] = trim($cfg['username']);
		}

			// If the create-site button WAS clicked:
		if (t3lib_div::_POST('_create_site'))	{

				// Show selected template file:
			if ($this->wizardData['file'] && $this->wizardData['sitetitle'] && $this->wizardData['username'])	{

					// DO import:
				$import = $this->getImportObj();
				$inFile = t3lib_extMgm::extPath('templavoila').'mod2/new_tv_site.xml';
				if (@is_file($inFile) && $import->loadFile($inFile,1))	{

					$import->importData($this->importPageUid);

						// Update various fields (the index values, eg. the "1" in "$import->import_mapId['pages'][1]]..." are the UIDs of the original records from the import file!)
					$data = array();
					$data['pages'][t3lib_BEfunc::wsMapId('pages',$import->import_mapId['pages'][1])]['title'] = $this->wizardData['sitetitle'];
					$data['sys_template'][t3lib_BEfunc::wsMapId('sys_template',$import->import_mapId['sys_template'][1])]['title'] = 'Main template: '.$this->wizardData['sitetitle'];
					$data['sys_template'][t3lib_BEfunc::wsMapId('sys_template',$import->import_mapId['sys_template'][1])]['sitetitle'] = $this->wizardData['sitetitle'];
					$data['tx_templavoila_tmplobj'][t3lib_BEfunc::wsMapId('tx_templavoila_tmplobj',$import->import_mapId['tx_templavoila_tmplobj'][1])]['fileref'] = $this->wizardData['file'];
					$data['tx_templavoila_tmplobj'][t3lib_BEfunc::wsMapId('tx_templavoila_tmplobj',$import->import_mapId['tx_templavoila_tmplobj'][1])]['templatemapping'] = serialize(
						array(
							'MappingInfo' => array(
								'ROOT' => array(
									'MAP_EL' => 'body[1]/INNER'
								)
							),
							'MappingInfo_head' => array(
								'headElementPaths' => array('link[1]','link[2]','link[3]','style[1]','style[2]','style[3]'),
								'addBodyTag' => 1
							)
						)
					);

						// Update user settings
					$newUserID = t3lib_BEfunc::wsMapId('be_users',$import->import_mapId['be_users'][2]);
					$newGroupID = t3lib_BEfunc::wsMapId('be_groups',$import->import_mapId['be_groups'][1]);

					$data['be_users'][$newUserID]['username'] = $this->wizardData['username'];
					$data['be_groups'][$newGroupID]['title'] = $this->wizardData['username'];

					foreach($import->import_mapId['pages'] as $newID)	{
						$data['pages'][$newID]['perms_userid'] = $newUserID;
						$data['pages'][$newID]['perms_groupid'] = $newGroupID;
					}

						// Set URL if applicable:
					if (strlen($this->wizardData['siteurl']))	{
						$data['sys_domain']['NEW']['pid'] = t3lib_BEfunc::wsMapId('pages',$import->import_mapId['pages'][1]);
						$data['sys_domain']['NEW']['domainName'] = $this->wizardData['siteurl'];
					}

						// Execute changes:
					$tce = t3lib_div::makeInstance('t3lib_TCEmain');
					$tce->stripslashes_values = 0;
					$tce->dontProcessTransformations = 1;
					$tce->start($data,Array());
					$tce->process_datamap();

						// Setting environment:
					$this->wizardData['rootPageId'] = $import->import_mapId['pages'][1];
					$this->wizardData['templateObjectId'] = t3lib_BEfunc::wsMapId('tx_templavoila_tmplobj',$import->import_mapId['tx_templavoila_tmplobj'][1]);
					$this->wizardData['typoScriptTemplateID'] = t3lib_BEfunc::wsMapId('sys_template',$import->import_mapId['sys_template'][1]);

					t3lib_BEfunc::getSetUpdateSignal('updatePageTree');

					$outputString.= 'New site has been created and adapted. <hr/>';
				}
			}
			else {
				$outputString.= 'Error happened: Either you did not specify a website name or username in the previous form!';
			}
		}

			// If a template Object id was found, continue with mapping:
		if ($this->wizardData['templateObjectId'])	{
			$url = $this->pObj->cm1Script . 'id=' . $this->pObj->id . '&table=tx_templavoila_tmplobj&uid=' . $this->wizardData['templateObjectId'] . '&SET[selectHeaderContent]=0&_reload_from=1&returnUrl=' . rawurlencode($this->pObj->wizScript . 'wiz=site&SET[wiz_step]=4');

			$outputString .= '
				You are now ready to point out at which position in the HTML code to insert the TYPO3 generated page content and the main menu. This process is called "mapping".<br/>
				The process of mapping is shown with this little animation. Please study it closely to understand the flow, then click the button below to start the mapping process on your own. Complete the mapping process by pressing "Save and Return".<br/>
				<br/>
				<img src="mapbody_animation.gif" style="border: 2px black solid;" alt=""><br/>
				<br/>
				<br/><input type="submit" value="Start the mapping process" onclick="'.htmlspecialchars('document.location=\''.$url.'\'; return false;').'" />
			';
		}

			// Add output:
		$this->content .= $this->doc->section('Step 3: Begin mapping',$outputString,0,1);
	}

	/**
	 * Step 4: Select HTML header parts.
	 *
	 * @return	void
	 */
	function wizard_step4()	{
		$url = $this->pObj->cm1Script . 'id=' . $this->pObj->id . '&table=tx_templavoila_tmplobj&uid=' . $this->wizardData['templateObjectId'] . '&SET[selectHeaderContent]=1&_reload_from=1&returnUrl=' . rawurlencode($this->pObj->wizScript . 'wiz=site&SET[wiz_step]=5');
		$outputString.= '
			Finally you also have to select which parts of the HTML header you want to include. For instance it is important that you select all sections with CSS styles in order to preserve the correct visual appearance of your website.<br/>
			You can also select the body-tag of the template if you want to use the original body-tag.<br/>
			This animations shows an example of this process:
			<br/>
			<img src="maphead_animation.gif" style="border: 2px black solid;" alt=""><br/>
			<br/>
			<br/><input type="submit" value="Select HTML header parts" onclick="'.htmlspecialchars('document.location=\''.$url.'\'; return false;').'" />
			';

			// Add output:
		$this->content.= $this->doc->section('Step 4: Select HTML header parts',$outputString,0,1);
	}

	/**
	 * Step 5: Create dynamic menu
	 *
	 * @param	string		Type of menu (main or sub), values: "field_menu" or "field_submenu"
	 * @return	void
	 */
	function wizard_step5($menuField)	{

		$menuPart = $this->getMenuDefaultCode($menuField);
		$menuType = $menuField === 'field_menu' ? 'mainMenu' : 'subMenu';
		$menuTypeText = $menuField === 'field_menu' ? 'main menu' : 'sub menu';
		$menuTypeLetter = $menuField === 'field_menu' ? 'a' : 'b';
		$menuTypeNextStep = $menuField === 'field_menu' ? 5.1 : 6;
		$menuTypeEntryLevel = $menuField === 'field_menu' ? 0 : 1;

		$this->saveMenuCode();

		if (strlen($menuPart))	{

				// Main message:
			$outputString.= '
				The basics of your website should be working now. However the '.$menuTypeText.' still needs to be configured so that TYPO3 automatically generates a menu reflecting the pages in the page tree. This process involves configuration of the TypoScript object path, "lib.'.$menuType.'". This is a technical job which requires that you know about TypoScript if you want it 100% customized.<br/>
				To assist you getting started with the '.$menuTypeText.' this wizard will try to analyse the menu found inside the template file. If the menu was created of a series of repetitive block tags containing A-tags then there is a good chance this will succeed. You can see the result below.
			';

				// Start up HTML parser:
			require_once(PATH_t3lib.'class.t3lib_parsehtml.php');
			$htmlParser = t3lib_div::makeinstance('t3lib_parsehtml');

				// Parse into blocks
			$parts = $htmlParser->splitIntoBlock('td,tr,table,a,div,span,ol,ul,li,p,h1,h2,h3,h4,h5',$menuPart,1);

				// If it turns out to be only a single large block we expect it to be a container for the menu item. Therefore we will parse the next level and expect that to be menu items:
			if (count($parts)==3)	{
				$totalWrap = array();
				$totalWrap['before'] = $parts[0].$htmlParser->getFirstTag($parts[1]);
				$totalWrap['after'] = '</'.strtolower($htmlParser->getFirstTagName($parts[1])).'>'.$parts[2];

				$parts = $htmlParser->splitIntoBlock('td,tr,table,a,div,span,ol,ul,li,p,h1,h2,h3,h4,h5',$htmlParser->removeFirstAndLastTag($parts[1]),1);
			} else {
				$totalWrap = array();
			}

			$menuPart_HTML = trim($totalWrap['before']).chr(10).implode(chr(10),$parts).chr(10).trim($totalWrap['after']);

				// Traverse expected menu items:
			$menuWraps = array();
			$GMENU = FALSE;
			$mouseOver = FALSE;
			$key = '';

			foreach($parts as $k => $value)	{
				if ($k%2)	{	// Only expecting inner elements to be of use:

					$linkTag = $htmlParser->splitIntoBlock('a',$value,1);
					if ($linkTag[1])	{
						$newValue = array();
						$attribs = $htmlParser->get_tag_attributes($htmlParser->getFirstTag($linkTag[1]),1);
						$newValue['A-class'] = $attribs[0]['class'];
						if ($attribs[0]['onmouseover'] && $attribs[0]['onmouseout'])	$mouseOver = TRUE;

							// Check if the complete content is an image - then make GMENU!
						$linkContent = trim($htmlParser->removeFirstAndLastTag($linkTag[1]));
						if (eregi('^<img[^>]*>$',$linkContent))	{
							$GMENU = TRUE;
							$attribs = $htmlParser->get_tag_attributes($linkContent,1);
							$newValue['I-class'] = $attribs[0]['class'];
							$newValue['I-width'] = $attribs[0]['width'];
							$newValue['I-height'] = $attribs[0]['height'];

							$filePath = t3lib_div::getFileAbsFileName(t3lib_div::resolveBackPath(PATH_site.$attribs[0]['src']));
							if (@is_file($filePath))	{
								$newValue['backColorGuess'] = $this->getBackgroundColor($filePath);
							} else $newValue['backColorGuess'] = '';

							if ($attribs[0]['onmouseover'] && $attribs[0]['onmouseout'])	$mouseOver = TRUE;
						}

						$linkTag[1] = '|';
						$newValue['wrap'] = ereg_replace('['.chr(10).chr(13).']*','',implode('',$linkTag));

						$md5Base = $newValue;
						unset($md5Base['I-width']);
						unset($md5Base['I-height']);
						$md5Base = serialize($md5Base);
						$md5Base = ereg_replace('name=["\'][^"\']*["\']','',$md5Base);
						$md5Base = ereg_replace('id=["\'][^"\']*["\']','',$md5Base);
						$md5Base = ereg_replace('[:space:]','',$md5Base);
						$key = md5($md5Base);

						if (!isset($menuWraps[$key]))	{	// Only if not yet set, set it (so it only gets set once and the first time!)
							$menuWraps[$key] = $newValue;
						} else {	// To prevent from writing values in the "} elseif ($key) {" below, we clear the key:
							$key = '';
						}
					} elseif ($key) {

							// Add this to the previous wrap:
						$menuWraps[$key]['bulletwrap'].= str_replace('|','&#'.ord('|').';',ereg_replace('['.chr(10).chr(13).']*','',$value));
					}
				}
			}

				// Construct TypoScript for the menu:
			reset($menuWraps);
			if (count($menuWraps)==1)	{
				$menu_normal = current($menuWraps);
				$menu_active = next($menuWraps);
			} else { 	// If more than two, then the first is the active one.
				$menu_active = current($menuWraps);
				$menu_normal = next($menuWraps);
			}

#debug($menuWraps);
#debug($mouseOver);
			if ($GMENU)	{
				$typoScript = '
lib.'.$menuType.' = HMENU
lib.'.$menuType.'.entryLevel = '.$menuTypeEntryLevel.'
'.(count($totalWrap) ? 'lib.'.$menuType.'.wrap = '.ereg_replace('['.chr(10).chr(13).']','',implode('|',$totalWrap)) : '').'
lib.'.$menuType.'.1 = GMENU
lib.'.$menuType.'.1.NO.wrap = '.$this->makeWrap($menu_normal).
	($menu_normal['I-class'] ? '
lib.'.$menuType.'.1.NO.imgParams = class="'.htmlspecialchars($menu_normal['I-class']).'" ' : '').'
lib.'.$menuType.'.1.NO {
	XY = '.($menu_normal['I-width']?$menu_normal['I-width']:150).','.($menu_normal['I-height']?$menu_normal['I-height']:25).'
	backColor = '.($menu_normal['backColorGuess'] ? $menu_normal['backColorGuess'] : '#FFFFFF').'
	10 = TEXT
	10.text.field = title // nav_title
	10.fontColor = #333333
	10.fontSize = 12
	10.offset = 15,15
	10.fontFace = t3lib/fonts/nimbus.ttf
}
	';

				if ($mouseOver)	{
					$typoScript.= '
lib.'.$menuType.'.1.RO < lib.'.$menuType.'.1.NO
lib.'.$menuType.'.1.RO = 1
lib.'.$menuType.'.1.RO {
	backColor = '.t3lib_div::modifyHTMLColorAll(($menu_normal['backColorGuess'] ? $menu_normal['backColorGuess'] : '#FFFFFF'),-20).'
	10.fontColor = red
}
			';

				}
				if (is_array($menu_active))	{
					$typoScript.= '
lib.'.$menuType.'.1.ACT < lib.'.$menuType.'.1.NO
lib.'.$menuType.'.1.ACT = 1
lib.'.$menuType.'.1.ACT.wrap = '.$this->makeWrap($menu_active).
	($menu_active['I-class'] ? '
lib.'.$menuType.'.1.ACT.imgParams = class="'.htmlspecialchars($menu_active['I-class']).'" ' : '').'
lib.'.$menuType.'.1.ACT {
	backColor = '.($menu_active['backColorGuess'] ? $menu_active['backColorGuess'] : '#FFFFFF').'
}
			';
				}

			} else {
				$typoScript = '
lib.'.$menuType.' = HMENU
lib.'.$menuType.'.entryLevel = '.$menuTypeEntryLevel.'
'.(count($totalWrap) ? 'lib.'.$menuType.'.wrap = '.ereg_replace('['.chr(10).chr(13).']','',implode('|',$totalWrap)) : '').'
lib.'.$menuType.'.1 = TMENU
lib.'.$menuType.'.1.NO {
	allWrap = '.$this->makeWrap($menu_normal).
	($menu_normal['A-class'] ? '
	ATagParams = class="'.htmlspecialchars($menu_normal['A-class']).'"' : '').'
}
	';

				if (is_array($menu_active))	{
					$typoScript.= '
lib.'.$menuType.'.1.ACT = 1
lib.'.$menuType.'.1.ACT {
	allWrap = '.$this->makeWrap($menu_active).
	($menu_active['A-class'] ? '
	ATagParams = class="'.htmlspecialchars($menu_active['A-class']).'"' : '').'
}
			';
				}
			}


				// Output:

				// HTML defaults:
			$outputString.='
			<br/>
			<br/>
			Here is the HTML code from the Template that encapsulated the menu:
			<hr/>
			<pre>'.htmlspecialchars($menuPart_HTML).'</pre>
			<hr/>
			<br/>';


			if (trim($menu_normal['wrap']) != '|')	{
				$outputString.= 'It seems that the menu consists of menu items encapsulated with "'.htmlspecialchars(str_replace('|',' ... ',$menu_normal['wrap'])).'". ';
			} else {
				$outputString.= 'It seems that the menu consists of menu items not wrapped in any block tags except A-tags. ';
			}
			if (count($totalWrap))	{
				$outputString.='It also seems that the whole menu is wrapped in this tag: "'.htmlspecialchars(str_replace('|',' ... ',implode('|',$totalWrap))).'". ';
			}
			if ($menu_normal['bulletwrap'])	{
				$outputString.='Between the menu elements there seems to be a visual division element with this HTML code: "'.htmlspecialchars($menu_normal['bulletwrap']).'". That will be added between each element as well. ';
			}
			if ($GMENU)	{
				$outputString.='The menu items were detected to be images - TYPO3 will try to generate graphical menu items automatically (GMENU). You will need to customize the look of these before it will match the originals! ';
			}
			if ($mouseOver)	{
				$outputString.='It seems like a mouseover functionality has been applied previously, so roll-over effect has been applied as well.  ';
			}

			$outputString.='<br/><br/>';
			$outputString.='Based on this analysis, this TypoScript configuration for the menu is suggested:
			<br/><br/>';
			$outputString.='<hr/>'.$this->syntaxHLTypoScript($typoScript).'<hr/><br/>';


			$outputString.='You can fine tune the configuration here before it is saved:<br/>';
			$outputString.='<textarea name="CFG[menuCode]"'.$GLOBALS['TBE_TEMPLATE']->formWidthText().' rows="10">'.t3lib_div::formatForTextarea($typoScript).'</textarea><br/><br/>';
			$outputString.='<input type="hidden" name="SET[wiz_step]" value="'.$menuTypeNextStep.'" />';
			$outputString.='<input type="submit" name="_" value="Write '.$menuTypeText.' TypoScript code" />';
		} else {
			$outputString.= '
				The basics of your website should be working now. It seems like you did not map the '.$menuTypeText.' to any element, so the menu configuration process will be skipped.<br/>
			';
			$outputString.='<input type="hidden" name="SET[wiz_step]" value="'.$menuTypeNextStep.'" />';
			$outputString.='<input type="submit" name="_" value="Next..." />';
		}

			// Add output:
		$this->content.= $this->doc->section('Step 5'.$menuTypeLetter.': Trying to create dynamic menu',$outputString,0,1);

	}

	/**
	 * Step 6: Done.
	 *
	 * @return	void
	 */
	function wizard_step6()	{

		$this->saveMenuCode();


		$outputString.= '<b>Congratulations!</b> You have completed the initial creation of a new website in TYPO3 based on the TemplaVoila engine. After you click the "Finish" button you can go to the Web>Page module to edit your pages!

		<br/>
		<br/>
		<input type="submit" value="Finish Wizard!" onclick="' . htmlspecialchars(t3lib_BEfunc::viewOnClick($this->wizardData['rootPageId'], $this->doc->backPath) . 'document.location=\'' . $this->pObj->mod2Script . 'SET[wiz_step]=0\'; return false;').'" />
		';

			// Add output:
		$this->content.= $this->doc->section('Step 6: Done',$outputString,0,1);
	}

	/**
	 * Initialize the import-engine
	 *
	 * @return	object		Returns object ready to import the import-file used to create the basic site!
	 */
	function getImportObj()	{
		global $TYPO3_CONF_VARS;

		require_once(t3lib_extMgm::extPath('impexp').'class.tx_impexp.php');

		$import = t3lib_div::makeInstance('tx_impexp');
		$import->init(0,'import');
		$import->enableLogging = TRUE;

		return $import;
	}

	/**
	 * Syntax Highlighting of TypoScript code
	 *
	 * @param	string		String of TypoScript code
	 * @return	string		HTML content with it highlighted.
	 */
	function syntaxHLTypoScript($v)	{
		require_once(PATH_t3lib.'class.t3lib_tsparser_ext.php');

		$tsparser = t3lib_div::makeInstance('t3lib_TSparser');
		$tsparser->lineNumberOffset=0;
		$TScontent = $tsparser->doSyntaxHighlight(trim($v).chr(10),'',1);

		return $TScontent;
	}

	/**
	 * Produce WRAP value
	 *
	 * @param	array		menuItemSuggestion configuration
	 * @return	string		Wrap for TypoScript
	 */
	function makeWrap($cfg)	{
		if (!$cfg['bulletwrap'])	{
			$wrap = $cfg['wrap'];
		} else {
			$wrap = $cfg['wrap'].'  |*|  '.$cfg['bulletwrap'].$cfg['wrap'];
		}

		return ereg_replace('['.chr(10).chr(13).chr(9).']','',$wrap);
	}

	/**
	 * Returns the code that the menu was mapped to in the HTML
	 *
	 * @param	string		"Field" from Data structure, either "field_menu" or "field_submenu"
	 * @return	string
	 */
	function getMenuDefaultCode($field)	{
			// Select template record and extract menu HTML content
		$toRec = t3lib_BEfunc::getRecordWSOL('tx_templavoila_tmplobj',$this->wizardData['templateObjectId']);
		$tMapping = unserialize($toRec['templatemapping']);
		return $tMapping['MappingData_cached']['cArray'][$field];
	}

	/**
	 * Saves the menu TypoScript code
	 *
	 * @return	void
	 */
	function saveMenuCode()	{

			// Save menu code to template record:
		$cfg = t3lib_div::_POST('CFG');
		if (isset($cfg['menuCode']))	{

				// Get template record:
			$TSrecord = t3lib_BEfunc::getRecord('sys_template',$this->wizardData['typoScriptTemplateID']);
			if (is_array($TSrecord))	{
				$data['sys_template'][$TSrecord['uid']]['config'] = '

## Menu [Begin]
'.trim($cfg['menuCode']).'
## Menu [End]



'.$TSrecord['config'];

					// Execute changes:
				global $TYPO3_CONF_VARS;

				require_once(PATH_t3lib.'class.t3lib_tcemain.php');
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->stripslashes_values = 0;
				$tce->dontProcessTransformations = 1;
				$tce->start($data,Array());
				$tce->process_datamap();
			}
		}
	}

	/**
	 * Tries to fetch the background color of a GIF or PNG image.
	 *
	 * @param	string		Filepath (absolute) of the image (must exist)
	 * @return	string		HTML hex color code, if any.
	 */
	function getBackgroundColor($filePath)	{

		if (substr($filePath,-4)=='.gif' && function_exists('imagecreatefromgif'))	{
			$im = @imagecreatefromgif($filePath);
		} elseif (substr($filePath,-4)=='.png' && function_exists('imagecreatefrompng'))	{
			$im = @imagecreatefrompng($filePath);
		}

		if ($im)	{
			$values = imagecolorsforindex($im, imagecolorat($im, 3, 3));
			$color = '#'.substr('00'.dechex($values['red']),-2).
						substr('00'.dechex($values['green']),-2).
						substr('00'.dechex($values['blue']),-2);
			return $color;
		}
		return false;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/wizards/class.tx_templavoila_wizards_site.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/wizards/class.tx_templavoila_wizards_site.php']);
}

?>