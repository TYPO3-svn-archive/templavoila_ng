<?php
$MCONF['name'  ] = 'web_txtemplavoilaM2';
$MCONF['access'] = 'user,group';
$MCONF['script'] = '_DISPATCH';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref'] = 'LLL:EXT:templavoila/mod2/locallang_mod.php';

if (!strstr($_SERVER['REQUEST_URI'], 'ext/templavoila/mod2/index.php'))
	return;

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', 'ext/templavoila/mod2/');
$BACK_PATH='../../../';
$MCONF['script'] = 'index.php';

require($BACK_PATH . 'init.php');
require($BACK_PATH . 'template.php');
?>