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
function verify(){
		return confirm ("Είστε σίγουροι ότι θέλετε να διαγράψετε το πρόγραμμα;");
}
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
  die('<h1>Σφάλμα... Δεν έχετε συνδεθεί!</h1>');

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


	//retrieve a record for update if GET var is set 
	if ( isset($_GET['id']) ) {
		// delete
		if ($admin && $_GET['delete']) {
			$result = $newForm->query('DELETE FROM '.$prTable.' WHERE `'.$newForm->pkey.'`="'.$_GET['id'].'"');
			print str_replace( 'update', 'delete', ($result?$newForm->msg_updateSuccess:$newForm->msg_updateFail) );
			die();
		}
		$newForm->getRecord($_GET['id']);
  }
  else if (isset($_GET['add'])) {
    // do nothing
  }
	
	// if editing, check if school or admin, else die
	if (!$admin && !isset($_GET['add']) && !isset($_POST['submit']))
	{
		$sid = $newForm->getFieldValue('sch1');
		if (!strcmp($sid,$_SESSION['sid']))
			{}
		// not a school. Exit...
		else
		{
			$errormsg = '<h2>Σφάλμα. Δεν έχετε δικαίωμα να δείτε αυτό το πρόγραμμα...</h2>';
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
	$newForm->hideFields($hiddenFieldsEdit); 

  // if new record
  if (isset($_GET['add'])){
    // check if adding is enabled
    if (!$canAdd){
      die('Σφάλμα... Η προσθήκη προγραμμάτων είναι απενεργοποιημένη.');
    }
    // if not admin
    if (!$admin) {
			$whereClause = "ID = ".$_SESSION['sid'];
      $newForm->normalizedField("sch1", $schTable, "ID", "name", "type ASC", $whereClause);
      $newForm->setDefaultValue("sch1",$_SESSION['sid']);
    } else {
      $newForm->normalizedField("sch1",$schTable,"id","name","type,name ASC");
    }
    $newForm->normalizedField("sch2",$schTable,"id","name","type,name ASC");
  }
  // if editing
  else {
    $newForm->normalizedField("sch1",$schTable,"id","name","type,name ASC");
    $newForm->normalizedField("sch2",$schTable,"id","name","type,name ASC");
  }
	// force types (display as select instead of radio)
  $newForm->forceTypes(
		array("his1","his2","his3","qua1","qua2","qua3","categ","arxeio","prsnt","cha","diax"),
		array("select","select","select","select","select","select","select","select","select","select","checkbox")
 );
    
    //set custom field labels 
	$rows = array (
    'sch1', 'princ1', 'praxi','sch2','princ2',
    'titel' ,'categ', 'subti' ,'theme' ,'goal' ,
    'meth' ,'pedia' , 
    'dura' ,'month', 'm1' ,'m2' ,'m3' ,'m4' ,'m5' ,'visit' ,'foreis' ,
    'arxeio',
    'prsnt',
	  'nam1' ,'email1' ,'mob1' ,'eid1' ,'his1' ,'qua1' ,
	  'nam2' ,'email2' ,'mob2' ,'eid2' ,'his2' ,'qua2' ,
	  'nam3' ,'email3' ,'mob3' ,'eid3' ,'his3' ,'qua3' ,
    'Nr' ,'cha' ,'grade' ,'notes',
    'chk','vev',
    'praxidate', 'nr', 'nr_boys', 'nr_girls', 'diax', 'diax_other'
  );
	 
  $labels = array ('Σχολική Μονάδα','Ονοματεπώνυμο Διευθυντή/ντριας- Προϊσταμένου/νης','Πράξη ανάθεσης','Συστεγαζόμενη Σχολική Μονάδα', 'Δ/ντής/ντρια Συστεγαζόμενης',
    'Τίτλος προγράμματος','Κατηγορία προγράμματος','Θεματολογία','Κύριο θέμα - Θεματικές Ενότητες','Παιδαγωγικοί στόχοι',
    'Μεθοδολογία Υλοποίησης','Πεδία σύνδεσης με τα προγράμματα σπουδών των αντίστοιχων γνωστικών αντικειμένων',
	  'Προβλεπόμενη διάρκεια προγράμματος (μήνες)','Μήνας έναρξης','1ος Μήνας','2ος Μήνας','3ος Μήνας','4ος Μήνας','5ος Μήνας','Αριθμός προβλεπόμενων επισκέψεων - Συνεργασίες με άλλους φορείς','Φορείς επισκέψεων' ,
    'Ύπαρξη αρχείου Σχολικών Δραστηριοτήτων στο Σχολείο',
    'Πρόθεση παρουσίασης του προγράμματος στη Γιορτή Μαθητικής Δημιουργίας '.$prSxetos, 
    'Όνοματεπώνυμο Συντονιστή/τριας','email Συντονιστή/τριας','Κινητό τηλέφωνο Συντονιστή/τριας','Ειδικότητα Συντονιστή/τριας','Υλοποίηση προγραμμάτων Συντονιστή/τριας στο παρελθόν','Επιμόρφωση Συντονιστή/τριας',
	  'Όνοματεπώνυμο 2ου εκπ/κού','email 2ου εκπ/κού','Κινητό τηλέφωνο 2ου εκπ/κού','Ειδικότητα 2ου εκπ/κού','Υλοποίηση προγραμμάτων 2ου εκπ/κού στο παρελθόν','Επιμόρφωση 2ου εκπ/κού',
	  'Όνοματεπώνυμο 3ου εκπ/κού','email 3ου εκπ/κού','Κινητό τηλέφωνο 3ου εκπ/κού','Ειδικότητα 3ου εκπ/κού','Υλοποίηση προγραμμάτων 3ου εκπ/κού στο παρελθόν','Επιμόρφωση 3ου εκπ/κού',
	  'Αριθμός Μαθητών','Χαρακτηριστικά ομάδας','Τμήμα/τμήματα','Τυχόν παρατηρήσεις-επισημάνσεις',
    'Βεβαιώνεται ότι ο/η δ/ντής/τρια ή προϊσταμένος/νη της σχολικής μονάδας έλεγξε το παρόν σχέδιο προγράμματος σχολικών δραστηριοτήτων, έκανε απαραίτητες τυχόν διορθώσεις και βεβαιώνει ότι τα στοιχεία που αναφέρονται στο παρόν σχέδιο προγράμματος είναι σωστά.', 'Ο/Η  δ/ντής/τρια ή προϊσταμένος/νη βεβαιώνει ότι το συγκεκριμένο σχέδιο προγράμματος σχολικών δραστηριοτήτων ολοκληρώθηκε επιτυχώς και τα αποτελέσματα του προγράμματος είναι διαθέσιμα στο σχολική μονάδα.',
    'Ημ/νία πράξης', 'Σύνολο μαθητών', 'Αριθμός αγοριών', 'Αριθμός κοριτσιών', 'Τρόποι διάχυσης', 'Άλλοι τρόποι διάχυσης'
  );
	
	 $newForm->labelFields( $rows, $labels ); 

	//encryption (not working)
	//$key = "$Ftg/%)poa";
	//$newForm->setEncryptionKey($key);
	
	// form validation
	$newForm->registerValidation('required','.+','Απαιτούμενο πεδίο');
	$newForm->registerValidation("valid_email",'^[a-zA-Z0-9_]{2,50}@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.?]+$',
	"Λανθασμένη δ/νση email"); 
	$newForm->registerValidation('integer','[0-9]+','Εισάγετε αριθμό');
	
	$newForm->validateField('sch1','required');
	$newForm->validateField('princ1','required');
	$newForm->validateField('praxi','integer');
	$newForm->validateField('praxidate','required');
	$newForm->validateField('titel','required');
	$newForm->validateField('goal','required');
	$newForm->validateField('meth','required');
	$newForm->validateField('pedia','required');
	$newForm->validateField('m1','required');
	$newForm->validateField('m2','required');
	//$newForm->validateField('diax','required');
	$newForm->validateField('synant','required');

	$newForm->validateField('nam1','required');
	$newForm->validateField('email1','required');
	$newForm->validateField('email1','valid_email');
	$newForm->validateField('mob1','required');
	$newForm->validateField('eid1','required');

	$newForm->validateField('nr','integer');
	$newForm->validateField('nr_boys','integer');
	$newForm->validateField('nr_girls','integer');
	$newForm->validateField('grade','required');
	
	$newForm->feedback="both";


	//output form 
	if( !isset($_POST['submit']) || (isset($_POST['submit']) && $newForm->submitForm() == -1) )
	{
		 echo "<h1>Επεξεργασία προγράμματος σχολικής δραστηριότητας</h1>";
		 $newForm->printForm(array(),array('Υποβολή','','Reset Form',false,true)); 
	}
	
	// if edit, display print button
  if ( isset($_GET['id']) ) {
		if ($canExport){
    	$printText = 'Εκτύπωση';
			echo "<input type=\"button\" onclick=\"window.open('exp.php?id=".$newForm->getFieldValue('id')."');\" value=\"".$printText."\" />";
		}
		if ($admin && $canDelete) {
			echo "<br><a href='prog.php?delete=1&id=".$_GET['id']."' onclick='return verify()'>Διαγραφή προγράμματος</a>";
		}
    //echo "<h4>ΣΗΜΕΙΩΣΕΙΣ:<br>1. Για την αποθήκευση οποιασδήποτε αλλαγής πατήστε 'Υποβολή'.<br>2. Τα πεδία: Σχολική μονάδα, Τίτλος προγράμματος, Όνομα-Επώνυμο-Κλάδος εκπ/κών ΔΕ μεταβάλλονται.<br>Για τη μεταβολή τους επικοινωνήστε με $contactInfo</h4><br>";
    echo '<h4>ΣΗΜΕΙΩΣΕΙΣ:<br>Για την αποθήκευση οποιασδήποτε αλλαγής πατήστε \'Υποβολή\'.</h4><br>';

    // display record timestamp
    if ($updated>0)
      echo "<small>Τελευταία μεταβολή: ".date('d/m/Y, H:i:s',strtotime($updated))."</small>";
	}

	// if form has been submitted, call Formitable submit method to save to db
	if( isset($_POST['submit']) ) 
	{
		if (isset($newForm->errMsg))
			$newForm->openForm();
		else {
			// if not admin, skip (don't update) the following fields (set @conf.php)
			if (!$admin)
			{
				if ($skippedFields)
					$newForm->skipFields($skippedFields);
			}
			$newForm->submitForm(); 
		}
	}

?>
</div>
</body>
</html>
<script>
	var refreshTime = 600000; // every 10 minutes in milliseconds
	window.setInterval( function() {
			$.ajax({
					cache: false,
					type: "GET",
					url: "refresh.php",
					success: function(data) {
						console.log(data);
					}
			});
	}, refreshTime );
</script>