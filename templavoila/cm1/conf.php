<?php
$MCONF['name'  ] = 'xMOD_tx_templavoila_cm1';
$MCONF['script'] = '_DISPATCH';

	// the access module check
$ACONF['name'  ] = 'web_txtemplavoilaM2';
$ACONF['access'] = 'user,group';

if (isset($MCONF['_']))
	return;

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/templavoila/cm1/');
$BACK_PATH='../../../../typo3/';
$MCONF['script'] = 'index.php';

require($BACK_PATH . 'init.php');
require($BACK_PATH . 'template.php');
?>