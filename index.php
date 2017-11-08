<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once('conf.php');
require_once ('phpgrid/conf.php');

// If in production, login using sch.gr's CAS server
// (To be able to login via sch.gr's CAS, the app must be whitelisted from their admins)
if (!$prDebug)
{
	// phpCAS simple client, import phpCAS lib (downloaded with composer)
  require_once('vendor/jasig/phpcas/CAS.php');
	//initialize phpCAS using SAML
	phpCAS::client(SAML_VERSION_1_1,'sso-test.sch.gr',443,'');
	// if logout
	if (isset($_POST['logout']))
	{
		session_unset();
		session_destroy(); 
		phpCAS::logout();
	}
	
	// no SSL validation for the CAS server, only for testing environments
	phpCAS::setNoCasServerValidation();
	// handle backend logout requests from CAS server
	phpCAS::handleLogoutRequests(array('sso-test.sch.gr'));
	// force CAS authentication
	if (!phpCAS::checkAuthentication())
	  phpCAS::forceAuthentication();
	// at this step, the user has been authenticated by the CAS server and the user's login name can be read with phpCAS::getUser().
	$_SESSION['loggedin'] = 1;
}
else 
	$_SESSION['loggedin'] = 1;

?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<head>
	<title> <?php echo 'Προγράμματα Σχολικών Δραστηριοτήτων '.$prSxetos; ?> </title>
</head>
<body>
<?php
// declare and prepare phpgrid
$theSql = $printVev ? 
	"SELECT sc.email1,pr.id,sc.name,pr.titel,pr.chk,pr.timestamp,pr.vev FROM $prTable pr JOIN $schTable sc ON pr.sch1 = sc.id" :
	"SELECT sc.email1,pr.id,sc.name,pr.titel,pr.chk,pr.timestamp FROM $prTable pr JOIN $schTable sc ON pr.sch1 = sc.id";

$dg = new C_DataGrid($theSql, "id", "$prTable");
$dg->set_locale('el');

$dg -> set_caption("Προγράμματα Σχολικών Δραστηριοτήτων $prSxetos");
$dg ->set_col_hidden("email1");
$dg ->set_col_property("id", array("name"=>"A/A", "width"=>15));
$dg ->set_col_property("sch1", array("name"=>"A/A", "width"=>70));
$dg ->set_col_property("chk", array("name"=>"checked", "width"=>20));
$dg ->set_col_property("timestamp", array("name"=>"timestamp", "width"=>45));
if ($printVev)
	$dg ->set_col_property("vev", array("name"=>"vev", "width"=>15));
//$dg ->set_col_property("agree", array("name"=>"done", "width"=>15));
$dg ->set_col_title("id", "A/A");
$dg ->set_col_title("name", "Όνομα Σχολείου");
$dg ->set_col_title("titel", "Τίτλος προγράμματος");
$dg ->set_col_title("chk", "Έλεγχος");
$dg ->set_col_title("timestamp", "Τελ. Μεταβολή");
if ($printVev)
	$dg ->set_col_title("vev", "Βεβαίωση");
//$dg ->set_col_title("agree", "Δήλ.Ολοκλ.");

$dg -> enable_search(true);
$dg -> set_dimension(1100, 700);
$dg -> set_pagesize(30);
$dg -> set_col_dynalink("id", "prog.php", "id");
$dg -> set_col_dynalink("titel", "prog.php", "id");
if ($printVev)
	$dg -> set_col_dynalink("vev", "vev.php", "id");

// get data from CAS server
$_SESSION['admin'] = 0;
if (!$prDebug)
{
	$sch_name = phpCAS::getAttribute('description');
	$uid = phpCAS::getUser();
	$em1 = $uid . "@sch.gr";
	$em2 = phpCAS::getAttribute('mail');
	if (!strcmp($uid,$prAdmin1) || !strcmp($uid,$prAdmin2))
		$_SESSION['admin'] = 1;
	$_SESSION['email1'] = $em1;
	$_SESSION['email2'] = $em2;
  $_SESSION['sch_name'] = $sch_name;
}
// fill for local testing
else {
  $sch_name = $prsch_name;
  $uid = $pruid;
  $em1 = $prem1;
  $em2 = $prem2;
  if (strcmp($uid,$prAdmin1) <>0 || !strcmp($uid,$prAdmin2) <>0) {
    $_SESSION['admin'] = 0;
  } else {
    $_SESSION['admin'] = 1;
  }
  $_SESSION['email1'] = $em1;
  $_SESSION['email2'] = $em2;
  $_SESSION['sch_name'] = $sch_name;
}
// get school ID & store in session variable
$conn = new mysqli(PHPGRID_DB_HOSTNAME, PHPGRID_DB_USERNAME, PHPGRID_DB_PASSWORD, PHPGRID_DB_NAME);
$sql = "SELECT id FROM $schTable WHERE email1='$em1' OR email1='$em2'";
$result = $conn->query($sql);
$rs = mysqli_fetch_assoc($result);
$_SESSION['sid'] = $rs['id'];

if (isset($sch_name))
	echo "<h2>Σχολείο: ".$sch_name."</h2>";
	
if (isset($em1) || isset($em2))
{
	if (!$_SESSION['admin']){
		// Check if records exist
		$conn = new mysqli(PHPGRID_DB_HOSTNAME, PHPGRID_DB_USERNAME, PHPGRID_DB_PASSWORD, PHPGRID_DB_NAME);
		$sql = "SELECT * FROM $prTable p JOIN $schTable s ON s.id = p.sch1 WHERE s.email1='$em1' OR s.email1='$em2'";
		$result = $conn->query($sql);
		// if no results
		if (!$result->num_rows) {
			$outmsg = "<h2>Δεν υπάρχουν αποτελέσματα...</h2><p>Ελέγξτε ότι:<ol><li>Ο λογαριασμός με τον οποίο κάνατε είσοδο είναι λογαριασμός <strong>Σχολικής Μονάδας ΠΣΔ <small>(π.χ. για λήψη email, είσοδο στο survey κλπ)</small></strong> και <strong>ΟΧΙ</strong> προσωπικός ή του MySchool*.</li><li>Βεβαιωθείτε ότι η σχολική σας μονάδα έχει προγράμματα σχολικών δραστηριοτήτων.</li><li>Αν ελέγξατε τα παραπάνω και συνεχίζετε να έχετε πρόβλημα, επικοινωνήστε με $contactInfo</li></ol><br>
			* Σε περίπτωση που είστε συνδεδεμένοι στο MySchool πρέπει να αποσυνδεθείτε και μετά να κάνετε είσοδο στο σύστημα αυτό.</p>";
			echo '<div style="font-size:10pt;color:black;font-family:arial;">'.$outmsg.'</div>';
		}
		// else display phpgrid only for selected school records
		else
		{
			$dg -> set_query_filter("sc.email1='$em1' OR sc.email1='$em2'");
			//$dg -> set_col_hidden("done");
			//$dg -> set_col_hidden("agree");
			$dg -> set_dimension(1024, 400);
			$dg -> display();
      
      // store sch id
      $row = mysqli_fetch_assoc($result);
      $_SESSION['sch_id'] = $row['sch1'];
		}
		$conn->close();
		/*$out = 'H υποβολή προγραμμάτων έχει τελειώσει. Ευχαριστούμε...';
		die($out);*/
	}
	// if admin, display all records
	elseif (!strcmp($uid,$prAdmin1) || !strcmp($uid,$prAdmin2)){
		$dg -> display();
  }
}
else
	die('Σφάλμα επαλήθευσης στοιχείων χρήστη (Authentication Error)');

// write to log...
$fname = "login_log.txt";
$fh = fopen($fname, 'a');
$data = $uid . "\t" . date('d-m-Y, H:i:s') . "\t" . $_SERVER['HTTP_USER_AGENT'] ."\n";
fwrite($fh, $data);
fclose($fh);

// export data link	
echo $_SESSION['admin'] ? '<a href="export.php">'.'Εξαγωγή δεδομένων'.'</a>' : '';
echo "<br><br>";
// add new programme button
if ($canAdd){
  echo "<form action='prog.php' method='GET' target='_blank'>";
  echo "<input type='hidden' name='add' value='1' />";
  echo "<input type='submit' name='logout' value='Προσθήκη Προγράμματος'>";
  echo "</form>";
}
//logout button
echo "<form action='' method='POST'>";
echo "<input type='submit' name='logout' value='Έξοδος'>";
echo "</form>";

$author = '(c) 2017, Βαγγέλης Ζαχαριουδάκης, Τμ.Μηχανογράφησης, Δ/νση Π.Ε. Ηρακλείου.';
echo '<div style="font-size:9pt;color:black;font-family:arial;">'.$author.'</div>';

?>
<script type="text/javascript">
$(function() {
    var grid = jQuery("#progs");
    grid[0].toggleToolbar();
});
</script>
</body>

</html>