<?php
session_start();
$_SESSION['loggedin'] = 0;
require_once('conf.php');
date_default_timezone_set('Europe/Athens');


// get school data
function get_school($code, $conn) {
	global $schTable;
	$sql = "SELECT id,name FROM $schTable WHERE code = $code";
	$result = $conn->query($sql);
	$row = mysqli_fetch_assoc($result);
	return [
			'id' => $row['id'],
			'name' => $row['name']
	];
}

// Load variables from config.json file
// Read the contents of config.json
$jsonString = file_get_contents('config.json');
// Decode the JSON string to an associative array
$configData = json_decode($jsonString, true);
// Extract values from the associative array and set them as variables
foreach ($configData as $configItem) {
    ${$configItem['name']} = $configItem['value'];
}

if (!$prDebug) {
	// if user not logged-in, display login form
	if (!$_SESSION['loggedin'] && !isset($_POST['login-btn']) && !$_GET['ticket'] && !isset($_POST['logout'])):
		?>
	<!DOCTYPE html>
		<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>Είσοδος</title>
				<!-- Bootstrap CSS -->
				<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
				<style>
					.jumbotron {
						background-color: #f8f9fa;
					}
				</style>
			</head>
			
			<body>
				<div class="container mt-5">
					<div class="p-4 shadow-4 rounded-3" style="background-color: hsl(0, 0%, 94%);">
						<h1>Προγράμματα Σχολικών Δραστηριοτήτων <?=$prSxetos?></h1>
						<p>
							Εισαγωγή, διαχείριση, έγκριση προγραμμάτων σχολικών δραστηριοτήτων
						</p>

  					<hr class="my-4" />

						<p>
						Η είσοδος στο σύστημα γίνεται με κωδικούς ΠΣΔ (Πανελληνίου Σχολικού Δικτύου - <u>κωδικοί email</u>) και ΟΧΙ με κωδικούς MySchool
						</p>
						<form id="login" method="post">
							<button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary" name="login-btn">
								Είσοδος με κωδικούς ΠΣΔ  
							</button>
						</form>
					</div>
				</div>

				<footer class="footer">
					<div class="container">
						<div class="row">
							<div class="col-md-10">
								<span>&copy; ΔΙ.Π.Ε. Ηρακλείου - Τμήμα Δ' Πληροφορικής & νέων Τεχνολογιών, 2024</span>
							</div>	
							<div class="col-md-2">
								<a href="https://github.com/dipeira/sch-progs" target="_blank" title="Github"><img src="files/github.png"></a>
							</div>
						</div>
					</div>
				</footer>
			</body>
		</html>
	<?php
	die();
	endif;
	// Add your CAS server integration here
	// phpCAS simple client, import phpCAS lib (downloaded with composer)
	require_once('vendor/autoload.php');
	//initialize phpCAS using SAML
	phpCAS::client(CAS_VERSION_3_0,'sso.sch.gr',443,'','https://srv1-dipe.ira.sch.gr');
	// if logout
	if (isset($_POST['logout']))
	{
		session_unset();
		session_destroy(); 
		phpCAS::logout();
		header("Location: index.php");
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
} else {
    $_SESSION['loggedin'] = 1;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo 'Προγράμματα Σχολικών Δραστηριοτήτων ' . $prSxetos; ?></title>
    <!-- Include Bootstrap CSS and DataTables.net CSS here -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
		<link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.dataTables.css" />
		<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.1/css/buttons.dataTables.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />
		<style>
			.btn {
						margin: 2px 0px 2px 0px;
					}
		</style>
</head>
<body>
<?php
if (!$prDebug)
{
	$sch_name = phpCAS::getAttribute('description');
	$sch_code = phpCAS::getAttribute('edupersonorgunitdn-gsnunitcode');
	$uid = phpCAS::getUser();
	$em1 = $uid . "@sch.gr";
	$em2 = phpCAS::getAttribute('mail');
	if (!strcmp($uid,$prAdmin1) || !strcmp($uid,$prAdmin2))
		$_SESSION['admin'] = 1;
	$_SESSION['email1'] = $em1;
	$_SESSION['email2'] = $em2;
}
// fill for local testing
else {
  $sch_name = $prsch_name;
	$sch_code = 0;
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
}


if (isset($_SESSION['email1']) || isset($_SESSION['email2'])) {
	// Check if records exist
    $conn = new mysqli($prDbhost, $prDbusername, $prDbpassword, $prDbname);
    if (!$_SESSION['admin']) {    
        $sql = "SELECT *,p.id as pid FROM $prTable p JOIN $schTable s ON s.id = p.sch1 WHERE s.email1='" . $_SESSION['email1'] . "' OR s.email1='" . $_SESSION['email2'] . "' OR s.code='$sch_code'";
	} else {
		$sql = "SELECT *,p.id as pid FROM $prTable p JOIN $schTable s ON s.id = p.sch1";
	}
		$result = $conn->query($sql);
		// get sch id
		$schid = 0;
		if (strlen($sch_name) == 0){
			$function_data = get_school($sch_code, $conn);
			$sch_name = $function_data['name'];
			$schid = $function_data['id'];
		}
		
		echo '<div class="container">';
		echo "<center><h1><i class='bi-newspaper'></i>&nbsp;&nbsp;Προγράμματα Σχολικών Δραστηριοτήτων $prSxetos</h1></center>";
    echo "<h4>Σχολείο: " . $sch_name . "</h4>";
        // if no results
        if (!$result->num_rows) {
            $outmsg = "<h2>Δεν υπάρχουν αποτελέσματα...</h2><p>Ελέγξτε ότι:<ol><li>Ο λογαριασμός με τον οποίο κάνατε είσοδο είναι λογαριασμός <strong>Σχολικής Μονάδας ΠΣΔ <small>(π.χ. για λήψη email, είσοδο στο survey κλπ)</small></strong> και <strong>ΟΧΙ</strong> προσωπικός ή του MySchool*.</li><li>Βεβαιωθείτε ότι η σχολική σας μονάδα έχει προγράμματα σχολικών δραστηριοτήτων.</li><li>Αν ελέγξατε τα παραπάνω και συνεχίζετε να έχετε πρόβλημα, επικοινωνήστε με τη Δ/νση</li></ol><br>
            * Σε περίπτωση που είστε συνδεδεμένοι στο MySchool πρέπει να αποσυνδεθείτε και μετά να κάνετε είσοδο στο σύστημα αυτό.</p>";
            echo '<div style="font-size:10pt;color:black;font-family:arial;">' . $outmsg . '</div>';
						$add_prog = $_SESSION['admin'] || (!$_SESSION['admin'] && $canAdd) ? '' : 'disabled';
						$sid = $schid > 0 ? $schid : 0;
						echo '<a href="#" class="btn btn-success add-record '.$add_prog.'" data-schid=$sid><span class="bi-plus-circle"></span>&nbsp;Προσθήκη</a></td>';
        } else {
						echo '<div id="alertContainer"></div>';
            // Display DataTable with records
            echo '<table id="progs" class="table table-bordered table-striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>A/A</th>';
            echo '<th>Όνομα Σχολείου</th>';
						echo '<th>Κατηγορία</th>';
            echo '<th>Τίτλος προγράμματος</th>';
            echo '<th>Έλεγχος</th>';
						echo '<th>Βεβαίωση</th>';
            echo '<th>Τελ. Μεταβολή</th>';
						echo '<th>Ενέργεια</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            while ($row = $result->fetch_assoc()) {
								$schid = $row['sch1'];
                echo '<tr>';
                echo '<td>' . $row['pid'] . '</td>';
                echo '<td>' . $row['name'] . '</td>';
								echo '<td>' . $row['categ'] . '</td>';
                echo '<td>' . $row['titel'] . '</td>';
                echo '<td>' . $row['chk'] . '</td>';
								echo '<td>' . $row['vev'] . '</td>';
                echo '<td>' . date('d-m-Y, h:i:s',strtotime($row['timestamp'])) . '</td>';
								echo '<td><a href="#" class="btn btn-warning edit-record" data-record-id="'.$row['pid'].'" data-sch-id="'.$row['sch1']. '" data-canvev="'.$canVev;
								echo '" data-lock-basic="'.$lockBasic.'" data-admin="'.$_SESSION['admin'].'"><span class="bi-pencil-square"></span>&nbsp;Επεξεργασία</a>';
								echo '&nbsp;<a href="#" class="btn btn-info view-record" data-record-id="'.$row['pid'].'"><span class="bi-eye"></span>&nbsp;Προβολή</a>';
								echo $showVev ? '&nbsp;<a href="exp.php?id='.$row['pid'].'" class="btn btn-success" data-record-id="'.$row['pid'].'"><span class="bi-file-earmark-text"></span>&nbsp;Βεβαίωση</a>' : '';
								echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
						$add_prog = $_SESSION['admin'] || (!$_SESSION['admin'] && $canAdd) ? '' : 'disabled';
						echo '<a href="#" class="btn btn-success add-record '.$add_prog.'" data-schid="'.$schid.'"><span class="bi-plus-circle"></span>&nbsp;Προσθήκη</a></td>';
        }
				// Logout button
				echo "<br><br>";
				echo '<form action="" method="POST">';
				if ($_SESSION['admin']){
					echo "<!-- Button to open the modal -->";
					echo '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#configModal"><span class="bi-gear"></span>&nbsp;Παράμετροι</button>';
					echo "<br>";
					echo '<button class="btn btn-success" id="exportButton"><span class="bi bi-file-earmark-excel"></span>&nbsp;Εξαγωγή σε Excel</button>';
    			//Open Configuration Modal
					echo '<br>';
				}
				echo '<button type="submit" class="btn btn-danger" name="logout"><span class="bi-box-arrow-right"></span>&nbsp;Έξοδος</button>';
				echo '</form>';
				echo "</div>";
        $conn->close();
    
        
} else {
    die('Σφάλμα επαλήθευσης στοιχείων χρήστη (Authentication Error)');
}

// Add your export and add new program buttons here



$author = '(c) 2024, Τμήμα Δ - Πληροφορικής & Νέων Τεχνολογιών, Δ/νση Π.Ε. Ηρακλείου.';
echo '<div style="font-size:9pt;color:black">' . $author . '</div>';

// Include Bootstrap and DataTables.net JavaScript libraries


?>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Προβολή Εγγραφής</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- View record details content -->
            </div>
        </div>
    </div>
</div>
<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Επεξεργασία Προγράμματος</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
						<form id="editForm">
            <div class="modal-body">
                <!-- Edit record details content with tabs -->
                <ul class="nav nav-tabs" id="editTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="school-tab" data-bs-toggle="tab" href="#school" role="tab" aria-controls="school" aria-selected="true">Σχολείο</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="program-tab" data-bs-toggle="tab" href="#program" role="tab" aria-controls="program" aria-selected="false">Πρόγραμμα</a>
                    </li>
										<li class="nav-item">
                        <a class="nav-link" id="teachers-tab" data-bs-toggle="tab" href="#teachers" role="tab" aria-controls="teachers" aria-selected="false">Εκπαιδευτικοί</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="progress-tab" data-bs-toggle="tab" href="#progress" role="tab" aria-controls="progress" aria-selected="false">Πρόοδος</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="status-tab" data-bs-toggle="tab" href="#status" role="tab" aria-controls="status" aria-selected="false">Κατάσταση</a>
                    </li>
                </ul>
                <div class="tab-content" id="editTabsContent">
                    <!-- Edit record details for each tab -->
                    <div class="tab-pane fade show active" id="school" role="tabpanel" aria-labelledby="school-tab">
                        <!-- School details content -->
												<input type="hidden" class="form-control" id="id" name="id" value=0>
												<div class="form-group">
													<label for="sch1">Σχολείο</label>
													<select class="form-select" id="sch1" name="sch1" style="width: 50%" required></select>
												</div>
												<div class="form-group">
													<label for="princ1">Διευθυντής/-ντρια - Πρ/νος/-η σχολείου</label>
													<input type="text" class="form-control" id="princ1" name="princ1">
												</div>
												<div class="form-group">
													<label for="sch2">Συνεργαζόμενο Σχολείο</label>
													<select class="form-select" id="sch2" name="sch2" style="width: 50%"></select>
												</div>
												<div class="form-group">
													<label for="princ2">Διευθυντής/-ντρια - Πρ/νος/-η συνεργαζόμενου σχολείου</label>
													<input type="text" class="form-control" id="princ2" name="princ2">
												</div>
										</div>
                    <div class="tab-pane fade" id="program" role="tabpanel" aria-labelledby="program-tab">
                        <!-- Program details content -->
												<div class="form-group">
													<label for="titel">Τίτλος Προγράμματος *</label>
													<input type="text" class="form-control" id="titel" name="titel" required>
												</div>
												<div class="form-group">
													<label for="categ">Κατηγορία</label>
													<select name="categ" id="categ" class="form-select" aria-label="Κατηγορία">
														<option value="Αγωγής Υγείας">Αγωγής Υγείας</option>
														<option value="Περιβαλλοντικής Εκπαίδευσης">Περιβαλλοντικής Εκπαίδευσης</option>
														<option value="Πολιτιστικών Θεμάτων">Πολιτιστικών Θεμάτων</option>
													</select>
												</div>
												<div class="form-group">
													<label for="subti">Υποτίτλος</label>
													<input type="text" class="form-control" id="subti" name="subti">
												</div>
												<div class="form-group">
													<label for="praxi">Πράξη</label>
													<input type="text" class="form-control" id="praxi" name="praxi" pattern="[0-9]*" title="Please enter only numeric values." value=0>
												</div>
												<div class="form-group">
													<label for="praxidate">Ημερομηνία Πράξης</label>
													<input type="text" class="form-control datepicker" id="praxidate" name="praxidate">
												</div>
												<div class="form-group">
													<label for="grade">Τάξη/-εις</label>
													<input type="text" class="form-control" id="grade" name="grade">
												</div>
												<div class="form-group">
													<label for="nr">Αριθμός Συμμετεχόντων</label>
													<input type="text" class="form-control" id="nr" name="nr" value=0>
												</div>
												<div class="form-group">
													<label for="nr_boys">Αριθμός Αγοριών</label>
													<input type="text" class="form-control" id="nr_boys" name="nr_boys" value=0>
												</div>
												<div class="form-group">
													<label for="nr_girls">Αριθμός Κοριτσιών</label>
													<input type="text" class="form-control" id="nr_girls" name="nr_girls" value=0>
												</div>
												<div class="form-group">
													<label for="cha">Χαρακτηριστικά ομάδας</label>
													<select name="cha" id="cha" class="form-select" aria-label="Κατηγορία">
														<option value="Μικτή ομάδα">Μικτή ομάδα</option>
														<option value="Αμιγές τμήμα">Αμιγές τμήμα</option>
													</select>
												</div>
												<div class="form-group">
													<label for="arxeio">Ύπαρξη αρχείου Σχολικών Δραστηριοτήτων στο Σχολείο</label>
													<select name="arxeio" id="arxeio" class="form-select" aria-label="Κατηγορία">
														<option value="Όχι">Όχι</option>
														<option value="Ναι">Ναι</option>
													</select>
												</div>
												<div class="form-group">
													<label for="theme">Θεματολογία</label>
													<textarea class="form-control" id="theme" name="theme"></textarea>
												</div>
												<div class="form-group">
													<label for="goal">Στόχος</label>
													<textarea class="form-control" id="goal" name="goal"></textarea>
												</div>
												<div class="form-group">
													<label for="notes">Σχόλια - Σημειώσεις</label>
													<textarea class="form-control" id="notes" name="notes"></textarea>
												</div>
                    </div>
										<div class="tab-pane fade" id="teachers" role="tabpanel" aria-labelledby="teachers-tab">
												<!-- Teachers details content -->
												<div class="form-group">
													<label for="nam1">Όν/μο Εκπαιδευτικού 1 *</label>
													<input type="text" class="form-control" id="nam1" name="nam1" required>
												</div>
												<div class="form-group">
													<label for="eid1">Ειδικότητα Εκπαιδευτικού 1</label>
													<select name="eid1" id="eid1" class="form-select" aria-label="Ειδικότητα">
														<option value="ΠΕ05">ΠΕ05</option>
														<option value="ΠΕ06">ΠΕ06</option>
														<option value="ΠΕ07">ΠΕ07</option>
														<option value="ΠΕ08">ΠΕ08</option>
														<option value="ΠΕ11">ΠΕ11</option>
														<option value="ΠΕ79">ΠΕ79</option>
														<option value="ΠΕ86">ΠΕ86</option>
														<option value="ΠΕ60">ΠΕ60</option>
														<option value="ΠΕ70">ΠΕ70</option>
														<option value="ΠΕ91">ΠΕ91</option>
														<option value="ΠΕ61">ΠΕ61</option>
														<option value="ΠΕ71">ΠΕ71</option>
														<option value="Άλλο">Άλλο</option>
													</select>
												</div>
												<div class="form-group">
													<label for="email1">Email Εκπαιδευτικού 1</label>
													<input type="text" class="form-control" id="email1" name="email1">
												</div>
												<div class="form-group">
													<label for="email1">Τηλέφωνο Εκπαιδευτικού 1</label>
													<input type="text" class="form-control" id="mob1" name="mob1">
												</div>
												<hr class="border-4" />
												<div class="form-group">
													<label for="nam1">Όν/μο Εκπαιδευτικού 2</label>
													<input type="text" class="form-control" id="nam2" name="nam2">
												</div>
												<div class="form-group">
													<label for="eid2">Ειδικότητα Εκπαιδευτικού 2</label>
													<select name="eid2" id="eid2" class="form-select" aria-label="Ειδικότητα">
														<option value="ΠΕ05">ΠΕ05</option>
														<option value="ΠΕ06">ΠΕ06</option>
														<option value="ΠΕ07">ΠΕ07</option>
														<option value="ΠΕ08">ΠΕ08</option>
														<option value="ΠΕ11">ΠΕ11</option>
														<option value="ΠΕ79">ΠΕ79</option>
														<option value="ΠΕ86">ΠΕ86</option>
														<option value="ΠΕ60">ΠΕ60</option>
														<option value="ΠΕ70">ΠΕ70</option>
														<option value="ΠΕ91">ΠΕ91</option>
														<option value="ΠΕ61">ΠΕ61</option>
														<option value="ΠΕ71">ΠΕ71</option>
														<option value="Άλλο">Άλλο</option>
													</select>
												</div>
												<div class="form-group">
													<label for="email1">Email Εκπαιδευτικού 2</label>
													<input type="text" class="form-control" id="email2" name="email2">
												</div>
												<div class="form-group">
													<label for="email1">Τηλέφωνο Εκπαιδευτικού 2</label>
													<input type="text" class="form-control" id="mob2" name="mob2">
												</div>
												<hr class="border-4" />
												<div class="form-group">
													<label for="nam1">Όν/μο Εκπαιδευτικού 3</label>
													<input type="text" class="form-control" id="nam3" name="nam3">
												</div>
												<div class="form-group">
													<label for="eid3">Ειδικότητα Εκπαιδευτικού 3</label>
													<select name="eid3" id="eid3" class="form-select" aria-label="Ειδικότητα">
														<option value="ΠΕ05">ΠΕ05</option>
														<option value="ΠΕ06">ΠΕ06</option>
														<option value="ΠΕ07">ΠΕ07</option>
														<option value="ΠΕ08">ΠΕ08</option>
														<option value="ΠΕ11">ΠΕ11</option>
														<option value="ΠΕ79">ΠΕ79</option>
														<option value="ΠΕ86">ΠΕ86</option>
														<option value="ΠΕ60">ΠΕ60</option>
														<option value="ΠΕ70">ΠΕ70</option>
														<option value="ΠΕ91">ΠΕ91</option>
														<option value="ΠΕ61">ΠΕ61</option>
														<option value="ΠΕ71">ΠΕ71</option>
														<option value="Άλλο">Άλλο</option>
													</select>
												</div>
												<div class="form-group">
													<label for="email1">Email Εκπαιδευτικού 3</label>
													<input type="text" class="form-control" id="email3" name="email3">
												</div>
												<div class="form-group">
													<label for="email1">Τηλέφωνο Εκπαιδευτικού 3</label>
													<input type="text" class="form-control" id="mob3" name="mob3">
												</div>
                    </div>
                    <div class="tab-pane fade" id="progress" role="tabpanel" aria-labelledby="progress-tab">
                        <!-- Progress details content -->
												<div class="form-group">
													<label for="meth">Μέθοδος</label>
													<textarea type="text" class="form-control" id="meth" name="meth"></textarea>
												</div>
												<div class="form-group">
													<label for="month">Μήνας έναρξης</label>
													<input type="text" class="form-control" id="month" name="month">
												</div>
												<div class="form-group">
													<label for="m1">1ος μήνας</label>
													<input type="text" class="form-control" id="m1" name="m1">
												</div>
												<div class="form-group">
													<label for="m2">2ος μήνας</label>
													<input type="text" class="form-control" id="m2" name="m2">
												</div>
												<div class="form-group">
													<label for="m3">3ος μήνας</label>
													<input type="text" class="form-control" id="m3" name="m3">
												</div>
												<div class="form-group">
													<label for="m4">4ος μήνας</label>
													<input type="text" class="form-control" id="m4" name="m4">
												</div>
												<div class="form-group">
													<label for="m5">5ος μήνας</label>
													<input type="text" class="form-control" id="m5" name="m5">
												</div>
												<div class="form-group">
													<label for="visit">Αριθμός προβλεπόμενων επισκέψεων - Συνεργασίες με άλλους φορείς</label>
													<input type="text" class="form-control" id="visit" name="visit">
												</div>
												<div class="form-group">
													<label for="foreis">Φορείς επισκέψεων</label>
													<input type="text" class="form-control" id="foreis" name="foreis">
												</div>
												<div class="form-group">
													<label for="dura">Διάρκεια</label>
													<input type="text" class="form-control" id="dura" name="dura" value=0>
												</div>
                    </div>
                    <div class="tab-pane fade" id="status" role="tabpanel" aria-labelledby="status-tab">
                        <!-- Status details content -->
												<div class="form-group">
													<label for="chk">Βεβαιώνεται ότι ο/η δ/ντής/τρια ή προϊσταμένος/νη της σχολικής μονάδας έλεγξε το παρόν σχέδιο προγράμματος σχολικών δραστηριοτήτων, έκανε απαραίτητες τυχόν διορθώσεις και βεβαιώνει ότι τα στοιχεία που αναφέρονται στο παρόν σχέδιο προγράμματος είναι σωστά.</label>
													<select name="chk" id="chk" class="form-select" aria-label="Έλεγχος">
														<option value="Ναι">Ναι</option>
														<option value="Όχι">Όχι</option>
													</select>
												</div>
												<div class="form-group">
													<label for="vev">Ο/Η  δ/ντής/τρια ή προϊσταμένος/νη βεβαιώνει ότι το συγκεκριμένο σχέδιο προγράμματος σχολικών δραστηριοτήτων ολοκληρώθηκε επιτυχώς και τα αποτελέσματα του προγράμματος είναι διαθέσιμα στο σχολική μονάδα.</label>
													<select name="vev" id="vev" class="form-select" aria-label="Βεβαίωση">
														<option value="Όχι">Όχι</option>
														<option value="Ναι">Ναι</option>
													</select>
												</div>
                    </div>
                </div>
            </div> <!-- of modal body -->
						<div class="modal-footer">
								<button type="button" class="btn btn-secondary btn-danger close-btn" data-bs-dismiss="modal"><i class="bi-x-circle"></i>&nbsp;Κλείσιμο</button>
								<button type="submit" class="btn btn-primary btn-success save-btn"><i class="bi-save"></i>&nbsp;Αποθήκευση</button>
						</div>
						</div> <!-- of form --> 
        </div> <!-- of modal content -->
    </div> <!-- of modal dialog -->
</div> <!-- of modal -->

<!-- Configuration Modal -->
<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="configModalLabel">Παράμετροι εφαρμογής</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Configuration settings will be dynamically loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi-x-circle"></i>&nbsp;Κλείσιμο</button>&nbsp;
        <button type="button" class="btn btn-primary" id="saveConfigBtn"><i class="bi-save"></i>&nbsp;Αποθήκευση</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous" type="text/javascript"></script>
<script src="https://cdn.datatables.net/2.0.3/js/dataTables.js" type="text/javascript"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.dataTables.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.colVis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.html5.min.js"></script>
<!-- Bootstrap Font Icon CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

<script src="script.js" type="text/javascript"></script>

</body>

</html>
