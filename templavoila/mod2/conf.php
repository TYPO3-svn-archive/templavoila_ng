<?php
$MCONF['name'  ] = 'web_txtemplavoilaM2';
$MCONF['access'] = 'user,group';
$MCONF['script'] = '_DISPATCH';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref']='LLL:EXT:templavoila/mod2/locallang_mod.php';

if (isset($MCONF['_']))
	return;

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/templavoila/mod2/');
$BACK_PATH='../../../../typo3/';
$MCONF['script'] = 'index.php';

require($BACK_PATH . 'init.php');
require($BACK_PATH . 'template.php');
?>