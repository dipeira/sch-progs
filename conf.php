<?php
// Configuration options
// General options
$prSxetos = '2015-16';
$prAdmin1 = '';
$prAdmin2 = '';

// Debug options
// $prDebug: set to 1 for local testing, 0 for production
$prDebug = 0;
// for testing when debug=1
$prsch_name = 'XX';
$pruid = 'XXXXX';
$prem1 = 'XXXXX';
$prem2 = 'XXXXX';

// DB credentials
$prDbname = '';
$prDbhost = '';
$prTable = 'progs';
$prDbusername = '';
$prDbpassword = '';


// phpgrid credentials - DO NOT ALTER!
// declared here, instead of phpgrid/conf.php
define('PHPGRID_DB_HOSTNAME', $prDbhost); // database host name
define('PHPGRID_DB_USERNAME', $prDbusername);     // database user name
define('PHPGRID_DB_PASSWORD', $prDbpassword); // database password
define('PHPGRID_DB_NAME', $prDbname); // database name
define('PHPGRID_DB_TYPE', 'mysql');  // database type
define('PHPGRID_DB_CHARSET','utf8'); // ex: utf8(for mysql),AL32UTF8 (for oracle), leave blank to use the default charset
