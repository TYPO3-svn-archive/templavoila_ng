<?php
$MCONF['name'] = 'tx_templavoila_wizardsce';
$MCONF['script'] = '_DISPATCH';
$MCONF['access'] = 'user,group';
$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref'] = 'LLL:EXT:templavoila/mod1/locallang_mod.php';

if (!strstr($_SERVER['REQUEST_URI'], 'ext/templavoila/wizards_ce/index.php'))
	return;

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', 'ext/templavoila/wizards_ce/');
$BACK_PATH = '../../../';
$MCONF['script'] = 'index.php';

require($BACK_PATH . 'init.php');
require($BACK_PATH . 'template.php');
?>