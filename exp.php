<?php

// Function to create a DOCX file from the given data ($dt) and return a link to download it
function createFile($dt) {
    // Load PhpWord library via Composer
    require_once('vendor/autoload.php');

    // Load, alter, and save new DOCX based on the template
    $templ = new \PhpOffice\PhpWord\TemplateProcessor('files/vev_tmpl.docx');

    // Set values from $dt into the template (replacing placeholders)
    foreach ($dt as $k => $v) {
        $templ->setValue("$k", htmlspecialchars($v));
    }
    
    // Save the modified DOCX file
    $docxFile = "files/exp_".$dt['id'].".docx";
    $templ->saveAs($docxFile);
    
    // Return the link to download the generated DOCX file
    return $docxFile;
}

// Start session and get connection info
session_start();
require_once('conf.php');

$admin = $_SESSION['admin'];

if (isset($_GET['id'])) {
    $progId = $_GET['id'];
} else {
    die('Authentication Error... (no GET var)');
}

// Create connection
$conn = mysqli_connect($prDbhost, $prDbusername, $prDbpassword, $prDbname);
mysqli_query($conn, "SET NAMES 'utf8'");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query database to get the relevant data for the template
$sql = "SELECT p.id, p.titel, p.nam1, p.categ, p.nam2, p.nam3, s1.name as s1name FROM $prTable p JOIN $schTable s1 ON p.sch1 = s1.id WHERE p.id = $progId";
$result = mysqli_query($conn, $sql);

// Fetch record
$rec = mysqli_fetch_assoc($result);

// Check if the user is allowed to view the program
if (!$admin) {
    $sid = $rec['sch1'];
    if (strcmp($sid, $_SESSION['sid']) !== 0) {
        die('<h2>Λάθος. Δεν έχετε δικαίωμα να δείτε αυτό το πρόγραμμα...</h2>');
    }
}

// Create DOCX file and offer it for download
$outFile = createFile($rec);

// Close connection
mysqli_close($conn);

// Offer the file for download
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="'.basename($outFile).'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($outFile));
readfile($outFile);

// Delete the file after download
unlink($outFile);

exit;
?>
