<?php
$MCONF['name'  ] = 'xMOD_tx_templavoila_cm1';
$MCONF['script'] = '_DISPATCH';

	// the access module check
$ACONF['name'  ] = 'web_txtemplavoilaM2';
$ACONF['access'] = 'user,group';

if (!strstr($_SERVER['REQUEST_URI'], 'ext/templavoila/cm1/index.php'))
	return;

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', 'ext/templavoila/cm1/');
$BACK_PATH='../../../';
$MCONF['script'] = 'index.php';

require($BACK_PATH . 'init.php');
require($BACK_PATH . 'template.php');
?>