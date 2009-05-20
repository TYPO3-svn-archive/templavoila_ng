<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2009  Niels Fr�hling (niels@frohling.biz)
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
 * Public defines for TemplaVoila
 *
 * @author     Niels Fr�hling <niels@frohling.biz>
 */

/* this is the 'default' encoding of content-elements */
define('SEPARATOR_XPATH', 		'/');
define('SEPARATOR_PARMS', 		':');
define('SEPARATOR_PARMG', 		'|');	// parameter-groups

/* scope-definitions of the TV-datastructure */
define('TVDS_SCOPE_OTHER',		0);
define('TVDS_SCOPE_PAGE',		1);
define('TVDS_SCOPE_FCE',		2);

define('TVDS_SCOPE_KNOWN',		2);	// frontier

/* inheritance-definitions of the TV-datastructure */
define('TVDS_INHERITANCE_NONE',		0);
define('TVDS_INHERITANCE_REPLACE',	1);
define('TVDS_INHERITANCE_ACCUMULATE',	2);

?>