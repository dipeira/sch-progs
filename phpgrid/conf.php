<?php
// mysql example

//define('PHPGRID_DB_HOSTNAME', ''); // database host name
//define('PHPGRID_DB_USERNAME', '');     // database user name
//define('PHPGRID_DB_PASSWORD', ''); // database password
//define('PHPGRID_DB_NAME', ''); // database name
//define('PHPGRID_DB_TYPE', '');  // database type
//define('PHPGRID_DB_CHARSET',''); // ex: utf8(for mysql),AL32UTF8 (for oracle), leave blank to use the default charset

// microsoft access example (Windows only)
/*
define('PHPGRID_DB_HOSTNAME', ''); // database host name
define('PHPGRID_DB_USERNAME', '');     // database user name
define('PHPGRID_DB_PASSWORD', ''); // database password
define('PHPGRID_DB_NAME', 'c:\\xampp1\\htdocs\\phpGridx\\examples\\SampleDB\\QrySampl.mdb'); // database name
define('PHPGRID_DB_TYPE', 'access');  // database type
define('PHPGRID_DB_CHARSET',''); // ex: utf8(for mysql),AL32UTF8 (for oracle), leave blank to use the default charset
*/

// postgres example
/*
define('PHPGRID_DB_HOSTNAME','localhost'); // database host name
define('PHPGRID_DB_USERNAME', 'postgres');     // database user name
define('PHPGRID_DB_PASSWORD', '1234'); // database password
define('PHPGRID_DB_NAME', 'sampledb'); // database name
define('PHPGRID_DB_TYPE', 'postgres');  // database type
define('PHPGRID_DB_CHARSET','');
*/

// mssql server example
/*
define('PHPGRID_DB_HOSTNAME','phpgridmssql'); // database host name or DSN name
define('PHPGRID_DB_USERNAME', 'mssqluser');     // database user name
define('PHPGRID_DB_PASSWORD', 'chen1234'); // database password
define('PHPGRID_DB_NAME', 'sampledb'); // database name
define('PHPGRID_DB_TYPE', 'odbc_mssql');  // database type
define('PHPGRID_DB_CHARSET','');

putenv("ODBCINSTINI=/usr/local/Cellar/unixodbc/2.3.1/etc/odbcinst.ini");
putenv("ODBCINI=/usr/local/Cellar/unixodbc/2.3.1/etc/odbc.ini"); //odbc.ini contains your DSNs.
*/

// oracle server exampl
/*
define('PHPGRID_DB_HOSTNAME','oracle-rds.cbdlprkhjrmd.us-west-1.rds.amazonaws.com');
define('PHPGRID_DB_USERNAME', 'oracleuser');     // database user name
define('PHPGRID_DB_PASSWORD', ''); // database password
define('PHPGRID_DB_NAME', 'sampledb'); // database name
define('PHPGRID_DB_TYPE', 'oci805');  // database type
define('PHPGRID_DB_CHARSET','AL32UTF8');
*/

// sqlite server example
/*
define('PHPGRID_DB_HOSTNAME','c:\path\to\sqlite.db'); // database host name
define('PHPGRID_DB_USERNAME', '');     // database user name
define('PHPGRID_DB_PASSWORD', ''); // database password
define('PHPGRID_DB_NAME', ''); // database name
define('PHPGRID_DB_TYPE', 'sqlite');  // database type
define('PHPGRID_DB_CHARSET','');
*/

// db2 example
/*
define('PHPGRID_DB_HOSTNAME','localhost'); // database host name
define('PHPGRID_DB_USERNAME', 'db2user');     // database user name
define('PHPGRID_DB_PASSWORD', ''); // database password
define('PHPGRID_DB_NAME', 'sample'); // database name
define('PHPGRID_DB_TYPE', 'db2');  // database type
define('PHPGRID_DB_CHARSET','');
*/

// *** You should define SERVER_ROOT manually when use Apache alias directive or IIS virtual directory ***
//define('SERVER_ROOT', str_replace(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])),'', str_replace('\\', '/',dirname(__FILE__))));
define('SERVER_ROOT', '/schools/progs/phpgrid');
define('DEBUG', false); // *** MUST SET TO FALSE WHEN DEPLOYED IN PRODUCTION ***











/******** DO NOT MODIFY ***********/
require_once('phpGrid.php');
/**********************************/
?>
