<?php
// Configuration options
// General options
$prSxetos = '2023-24';
$prAdmin1 = 'dipeira';
$prAdmin2 = 'taypeira';

// Debug options
// $prDebug: set to 1 for local testing, 0 for production
$prDebug = 1;
// for testing when debug=1
$prsch_name = 'ΔΙΠΕ Ηρακλείου';
$pruid = '';
$prem1 = 'mail@15dim-irakl.ira.sch.gr';
$prem2 = '';

// DB credentials
$prDbname = 'progs';
$prDbhost = 'localhost';
$prTable = 'progs';
$schTable = 'progs_schools';
$prDbusername = 'root';
$prDbpassword = '';

// User can add new programme
$canAdd = 1;
// admin can delete
$canDelete = 1;
// Print certificate
// $printVev = 1;
// Lock basic details
// Lock the following fields for the user 'sch1','sch2','titel','nam1','nam2','nam3','eid1','eid2','eid3'
$lockBasic = 0;


// custom messages
$msg_insertSuccess = '<center><label class="font"><h3>Η φόρμα υποβλήθηκε με επιτυχία!</h3><br><br>Σε περίπτωση που θέλετε να διορθώσετε κάτι, μην υποβάλετε νέα φόρμα, αλλά πραγματοποιήστε είσοδο με τους κωδικούς ΠΣΔ της Σχολικής Μονάδας & διορθώστε.<br><a href=\'javascript:window.open("","_self").close();\'>Κλείσιμο καρτέλας</a></label></center>';
$msg_insertFail = '<center><label class="font"><strong>Προέκυψε σφάλμα.</strong><br/>Η υποβολή της φόρμας απέτυχε.<br><a href=\'javascript:window.open("","_self").close();\'>Κλείσιμο καρτέλας</a></label></center>';
$msg_updateSuccess = '<center><label class="font"><h3>Επιτυχής ενημέρωση!</h3><br><a href=\'javascript:window.open("","_self").close();\'>Κλείσιμο καρτέλας</a></label></center>';
$msg_updateFail = '<center><label class="font"><strong>Προέκυψε σφάλμα.</strong><br/>Η ενημέρωση της εγγραφής απέτυχε.<br><a href=\'javascript:window.open("","_self").close();\'>Κλείσιμο καρτέλας</a></label></center>';

$contactInfo = "το τμήμα Εκπαιδευτικών Θεμάτων (2810-529314) ή το τμ. Πληροφορικής (2810-529301).";

// hide fields @ program form (New/Edit)
$hiddenFieldsNew = array('id','timestamp','chk','vev');
$hiddenFieldsEdit = array('id','timestamp');
//$hiddenFieldsEdit = array('id','timestamp','vev');
?>