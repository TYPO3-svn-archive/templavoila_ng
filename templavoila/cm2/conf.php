<?php
$MCONF['name'  ] = 'xMOD_tx_templavoila_cm2';
$MCONF['script'] = '_DISPATCH';

if (!strstr($_SERVER['REQUEST_URI'], 'ext/templavoila/cm2/index.php'))
	return;

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/templavoila/cm2/');
$BACK_PATH='../../../../typo3/';
$MCONF['script'] = 'index.php';

require($BACK_PATH . 'init.php');
require($BACK_PATH . 'template.php');
?>