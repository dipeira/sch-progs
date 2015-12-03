<?php
// A on-the-fly solution for the admin to export all records to excel.
// Just click 'EXCEL' button at the bottom of the grid...
session_start();

if (!$_SESSION['loggedin'] || !$_SESSION['admin'])
	die('Access denied');
	
header('Content-Type: text/html; charset=utf-8');
?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<head>
	<title> <?php echo iconv('Windows-1253', 'UTF-8', 'Προγράμματα Σχολικών Δραστηριοτήτων - Εξαγωγή'); ?> </title>
</head>
<body>
<?php
require_once ('conf.php');
require_once ('phpgrid/conf.php');

$dg = new C_DataGrid("SELECT * FROM $prTable", "id", "$prTable");

$dg->set_locale('el');

$dg -> set_caption(mb_convert_encoding("Προγράμματα Σχολικών Δραστηριοτήτων - Προβολή εξαγωγής", "utf-8","iso-8859-7" ));

$dg -> set_dimension(1100, 700);
$dg -> set_pagesize(30);

$dg->enable_export('EXCEL');
$dg -> display();

?>
<a href="javascript:history.go(-1)">Go back...</a>
</body>

</html>