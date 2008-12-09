<?php

$MCONF['name']='tx_templavoila_dbnewcontentel';
$MCONF['script']='_DISPATCH';

$MCONF['access']='user,group';

if (!strstr($_SERVER['REQUEST_URI'], 'ext/templavoila/mod1/index.php'))
	return;
	
	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/templavoila/db_new_content_el/');
$BACK_PATH='../../../../typo3/';
$MCONF['script'] = 'index.php';

require($BACK_PATH . 'init.php');
require($BACK_PATH . 'template.php');	
?>