<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="css/Formitable_style.css">
<html>
<head>
<script src="include/jquery.min.js"></script>
<script>
 $(function() {
	$('textarea').each(function() {
		$(this).height(0);
		$(this).height($(this).prop('scrollHeight'));
	});
});
</script>
<title><?php echo 'Σελίδα Προγράμματος'; ?></title>
</head>
<body>
<div id="content">
<?php
header('Content-Type: text/html; charset=utf-8');

// Displays program record as a form (via Formitable), for adding, editing and exporting to HTML

require_once('conf.php');
session_start();
$admin = $_SESSION['admin'];
if (!$_SESSION['loggedin'])
  die('Error... Not logged-in!');

//include class, create new Formitable, set primary key field name 
include("include/Formitable.class.php");

$myconn = mysql_connect($prDbhost,$prDbusername,$prDbpassword);

mysql_query("SET NAMES 'utf8'", $myconn);
mysql_query("SET CHARACTER SET 'utf8'", $myconn); 

// initialize Formitable
$newForm = new Formitable($myconn,$prDbname,$prTable); 

$newForm->setPrimaryKey("id"); 

// custom messages
$newForm->msg_insertSuccess = $msg_insertSuccess;
$newForm->msg_insertFail = $msg_insertFail;
$newForm->msg_updateSuccess = $msg_updateSuccess;
$newForm->msg_updateFail = $msg_updateFail;

// if form has been submitted, call Formitable submit method to save to db
if( isset($_POST['submit']) ) 
{
	// if not admin, skip (don't update) the following fields (set @conf.php)
	if (!$admin)
	{
    if ($skippedFields)
      $newForm->skipFields($skippedFields);
	}
	$newForm->submitForm(); 
}

//otherwise continue with form customization 
else { 
	//retrieve a record for update if GET var is set 
	if ( isset($_GET['id']) ) {
		$newForm->getRecord($_GET['id']);
  }
  else if (isset($_GET['add'])) {
    // do nothing
  }
	else {
		die ("Σφάλμα...(no get var)");
	}
	// if editing, check if school or admin, else die
	if (!$admin && !isset($_GET['add']))
	{
		$email = $newForm->getFieldValue('emails1');
		if (!strcmp($email,$_SESSION['email1']) || !strcmp($email,$_SESSION['email2']))
			{}
		// not a school. Exit...
		else
		{
			$errormsg = '<h2>Λάθος. Δεν έχετε δικαίωμα να δείτε αυτό το πρόγραμμα...</h2>';
			die ($errormsg);
		}
	}
	// if editing, set header
  if ( isset($_GET['id']) ) {
    $title = $newForm->getFieldValue('titel');
    $updated = $newForm->getFieldValue('timestamp');
    echo "<h1><i>Πρόγραμμα:</i> ". $title . "</h1>";
  }
	  
	// hide fields from users
	$newForm->hideFields($hiddenFields); 
	
  // if new record
  if (isset($_GET['add'])){
    // check if adding is enabled
    if (!$canAdd){
      die('Σφάλμα... Η προσθήκη προγραμμάτων είναι απενεργοποιημένη.');
    }
    // if not admin
    if (!$admin) {
      $whereClause = "id = ".$_SESSION['sch_id'];
      $newForm->normalizedField("sch1","schools","id","name","type ASC", $whereClause);
      $newForm->setDefaultValue("emails1",$_SESSION['email1']);
    } else {
      $newForm->normalizedField("sch1","schools","id","name","type ASC");
    }
    $newForm->normalizedField("sch2","schools","id","name","type ASC");
  }
  // if editing
  else {
    $newForm->normalizedField("sch1","schools","id","name","type ASC");
    $newForm->normalizedField("sch2","schools","id","name","type ASC");
  }
	// force types (display as select instead of radio)
  $newForm->forceTypes(
      array("his1","his2","his3","qua1","qua2","qua3","categ","arxeio","prsnt","cha"),
      array("select","select","select","select","select","select","select","select","select","select")
  );
  // form validation - TODO: NOT WORKING!!!
  //$newForm->registerValidation("required",".+","Απαιτούμενο πεδίο"); 
  //$newForm->validateField("princ1","required"); 
  //$newForm->feedback="both";
  
    //set custom field labels 
	 $rows = array (
	 'emails1', 'schnip', 'dimo', 'sch1', 'princ1', 'praxi', 'sch2','princ2','emails2',
	 'titel' ,'subti' ,'categ' ,'theme' ,'goal' ,'meth' ,'pedia' ,
	 'dura' ,'m1' ,'m2' ,'m3' ,'m4' ,'m5' ,'visit' ,'for1' , 'for2',
   'synant' , 'arxeio','act' ,'prsnt',
	 'nam1' ,'email1' ,'mob1' ,'eid1' ,'his1' ,'qua1' ,
	 'nam2' ,'email2' ,'mob2' ,'eid2' ,'his2' ,'qua2' ,
	 'nam3' ,'email3' ,'mob3' ,'eid3' ,'his3' ,'qua3' ,
	 'Nr' ,'cha' ,'grade' ,'notes','chk','vev'
	 );
	 
	 $labels = array ('email Σχολείου','Τύπος Μονάδας','Δήμος','Σχολική Μονάδα','Ονοματεπώνυμο Διευθυντή/ντριας- Προϊσταμένου/νης','Πράξη ανάθεσης', 'Συστεγαζόμενη Σχολική Μονάδα', 'Δ/ντής/ντρια Συστεγαζόμενης', 'email Συστεγαζόμενης',
	 'Τίτλος προγράμματος','Υπότιτλος-Υποθέματα','Κατηγορία προγράμματος','Θεματολογία','Παιδαγωγικοί στόχοι','Μεθοδολογία Υλοποίησης-Συνεργασίες','Πεδία σύνδεσης με τα προγράμματα σπουδών των αντίστοιχων γνωστικών αντικειμένων',
	 'Διάρκεια προγράμματος (μήνες)','1ος Μήνας','2ος Μήνας','3ος Μήνας','4ος Μήνας','5ος Μήνας','Αριθμός επισκέψεων','1ος φορέας επίσκεψης' ,	'2ος φορέας επίσκεψης',
   'Ημέρα, ώρα και τόπος συνάντησης ομάδας', 'Ύπαρξη αρχείου Σχολικών Δραστηριοτήτων στο Σχολείο','Δράσεις','Πρόθεση παρουσίασης του προγράμματος στη Γιορτή Μαθητικής Δημιουργίας 2016',
	 'Όνοματεπώνυμο 1ου εκπ/κού','email 1ου εκπ/κού','Κινητό τηλέφωνο 1ου εκπ/κού','Ειδικότητα 1ου εκπ/κού','Υλοποίηση προγραμμάτων 1ου εκπ/κού στο παρελθόν','Επιμόρφωση 1ου εκπ/κού',
	 'Όνοματεπώνυμο 2ου εκπ/κού','email 2ου εκπ/κού','Κινητό τηλέφωνο 2ου εκπ/κού','Ειδικότητα 2ου εκπ/κού','Υλοποίηση προγραμμάτων 2ου εκπ/κού στο παρελθόν','Επιμόρφωση 2ου εκπ/κού',
	 'Όνοματεπώνυμο 3ου εκπ/κού','email 3ου εκπ/κού','Κινητό τηλέφωνο 3ου εκπ/κού','Ειδικότητα 3ου εκπ/κού','Υλοποίηση προγραμμάτων 3ου εκπ/κού στο παρελθόν','Επιμόρφωση 3ου εκπ/κού',
	 'Αριθμός Μαθητών','Χαρακτηριστικά ομάδας','Τάξεις','Τυχόν παρατηρήσεις-επισημάνσεις',
	 'Βεβαιώνεται ότι ο/η δ/ντής/τρια ή προϊσταμένος/νη της σχολικής μονάδας έλεγξε το παρόν σχέδιο προγράμματος σχολικών δραστηριοτήτων, έκανε απαραίτητες τυχόν διορθώσεις και βεβαιώνει ότι τα στοιχεία που αναφέρονται στο παρόν σχέδιο προγράμματος είναι σωστά.', 'Ο/Η  δ/ντής/τρια ή προϊσταμένος/νη βεβαιώνει ότι το συγκεκριμένο σχέδιο προγράμματος σχολικών δραστηριοτήτων ολοκληρώθηκε επιτυχώς και τα αποτελέσματα του προγράμματος είναι διαθέσιμα στο σχολική μονάδα.'
   );
	
	 $newForm->labelFields( $rows, $labels ); 

	//encryption (not working)
	//$key = "$Ftg/%)poa";
	//$newForm->setEncryptionKey($key);
	
	//output form 
  // TODO: Use printField instead of printForm, in case validation works
	$newForm->printForm(array(),array('Υποβολή','','Reset Form',false,true)); 
	
	// if edit, display print button
  if ( isset($_GET['id']) ) {
    $printText = 'Εκτύπωση';
    echo "<input type=\"button\" onclick=\"window.open('exp.php?id=".$newForm->getFieldValue('id')."');\" value=\"".$printText."\" />";
    echo "<h4>ΣΗΜΕΙΩΣΕΙΣ:<br>1. Για την αποθήκευση οποιασδήποτε αλλαγής πατήστε 'Υποβολή'.<br>2. Τα πεδία: Σχολική μονάδα, Τίτλος προγράμματος, Όνομα-Επώνυμο-Κλάδος εκπ/κών ΔΕ μεταβάλλονται.<br>Για τη μεταβολή τους επικοινωνήστε με $contactInfo</h4><br>";
    //$shm = '<h4>ΣΗΜΕΙΩΣΕΙΣ:<br>Για την αποθήκευση οποιασδήποτε αλλαγής πατήστε \'Υποβολή\'.</h4><br>';

    // display record timestamp
    if ($updated>0)
      echo "<small>Τελευταία μεταβολή: ".date('d/m/Y, H:i:s',strtotime($updated))."</small>";
  }
}
?>
</div>
</body>
</html>