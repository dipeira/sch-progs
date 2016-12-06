<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<html>
<head>
</head>
<body>
<?php

// Creates an HTML file from the given data ($dt) and returns a link
function createFile($dt) {
	// cleanup old prog files
    $oldPdfs = glob("files/prog_".$dt['id']."*.html");
	foreach ($oldPdfs as $target){
		unlink($target);
	}

	// load PhpWord & mpdf libraries via composer
	require_once('vendor/autoload.php');

	// load, alter and save new docx based on template
	$templ = new \PhpOffice\PhpWord\TemplateProcessor('files/tmpl.docx');

	foreach ($dt as $k => $v) {
		$templ->setValue("$k", htmlspecialchars($v));
  }
	
	$docxFile = "files/exp_".$dt['id'].".docx";
	$templ->saveAs($docxFile);
	
	// prepare PDF renderer (unused because of high overhead for sch.gr servers - left as a paradigm)
	//\PhpOffice\PhpWord\Settings::setPdfRendererPath(realpath(__DIR__ . '/vendor/mpdf/mpdf/'));
	//\PhpOffice\PhpWord\Settings::setPdfRendererName('MPDF');

	// open the new docx
	$phpWord = \PhpOffice\PhpWord\IOFactory::load($docxFile);
	$random = rand(1000,3000);
		
	// add a window.print() section @ end of HTML
	$section = $phpWord->addSection();
	$section->addText('<script>window.print();</script>');
	// save HTML with a random number on filename
	$fileLink = 'files/prog_'.$dt['id'].$random.'.html';
	$phpWord->save($fileLink, 'HTML');
	
	// save PDF (unused - see above)
	//$fileLink = 'files/prog_'.$dt['id'].$random.'.pdf';
	//$phpWord->save($fileLink, 'PDF');
	
	// delete docx
	unlink($docxFile);
	
	return $fileLink;
}

// Retrieves the selected record, creates an html report (based on a docx template) and redirects to it.

session_start();
require_once('conf.php');

$admin = $_SESSION['admin'];

if (isset($_GET['id']))
	$progId = $_GET['id'];
else
	die('Authentication Error... (no get var)');

// Create connection
$conn = mysqli_connect($prDbhost,$prDbusername,$prDbpassword,$prDbname);

mysqli_query($conn,"SET NAMES 'utf8'");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// query db
$sql = "SELECT p.*, s1.name as s1name, s2.name as s2name FROM $prTable p JOIN $schTable s1 ON p.sch1 = s1.id JOIN $schTable s2 ON p.sch2 = s2.id WHERE p.id = $progId";
$result = mysqli_query($conn,$sql);
// fetch record
$rec = mysqli_fetch_assoc($result);

// check if user can view programme
if (!$admin)
{
	$email = $rec['emails1'];
	if (!strcmp($email,$_SESSION['email1']) || !strcmp($email,$_SESSION['email2']))
		{}
	else
	{
		$errormsg = '<h2>Λάθος. Δεν έχετε δικαίωμα να δείτε αυτό το πρόγραμμα...</h2>';
		die ($errormsg);
	}
}

// create HTML file and redirect to it (later, with HTML Meta)
$outName = createFile($rec);

mysqli_close($conn);

?>
<META HTTP-EQUIV="Refresh" CONTENT="0; URL=<?= $outName ?>">
</body>
</html>
