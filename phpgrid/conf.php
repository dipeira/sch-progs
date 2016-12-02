<?php

define('PHPGRID_DB_HOSTNAME', ''); // database host name
define('PHPGRID_DB_USERNAME', '');     // database user name
define('PHPGRID_DB_PASSWORD', ''); // database password
define('PHPGRID_DB_NAME', ''); // database name
define('PHPGRID_DB_TYPE', 'mysql');  // database type
define('PHPGRID_DB_CHARSET','utf8'); // ex: utf8(for mysql),AL32UTF8 (for oracle), leave blank to use the default charset
// *** You should define SERVER_ROOT manually when use Apache alias directive or IIS virtual directory ***
define('SERVER_ROOT', '/sch-progs/phpgrid');
define('DEBUG', false); // *** MUST SET TO FALSE WHEN DEPLOYED IN PRODUCTION ***

/******** DO NOT MODIFY ***********/
require_once('phpGrid.php');
/**********************************/
?>
