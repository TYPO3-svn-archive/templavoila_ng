<?php
$MCONF['name'] = 'tx_templavoila_wizards';
$MCONF['script'] = '_DISPATCH';
$MCONF['access'] = 'user,group';

if (!strstr($_SERVER['REQUEST_URI'], 'ext/templavoila/wizards/index.php'))
	return;

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', 'ext/templavoila/wizards/');
$BACK_PATH = '../../../';
$MCONF['script'] = 'index.php';

require($BACK_PATH . 'init.php');
require($BACK_PATH . 'template.php');
?>