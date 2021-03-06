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
	<title> <?php echo 'Προγράμματα Σχολικών Δραστηριοτήτων - Εξαγωγή'; ?> </title>
</head>
<body>
<?php
require_once ('conf.php');
require_once ('phpgrid/conf.php');

$theSql = "SELECT sc.*,pr.* FROM $prTable pr JOIN $schTable sc ON pr.sch1 = sc.id";
$dg = new C_DataGrid($theSql, "id", "$prTable");
$dg->set_locale('el');

$dg -> set_caption("Προγράμματα Σχολικών Δραστηριοτήτων - Προβολή εξαγωγής");

$dg -> set_dimension(1100, 700);
$dg -> set_pagesize(30);

$dg->enable_export('EXCEL');
$dg -> display();

?>
<a href="javascript:history.go(-1)">Go back...</a>
</body>

</html>