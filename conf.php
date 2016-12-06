<?php
// Configuration options
// General options
$prSxetos = '2016-17';
$prAdmin1 = '';
$prAdmin2 = '';

// Debug options
// $prDebug: set to 1 for local testing, 0 for production
$prDebug = 0;
// for testing when debug=1
$prsch_name = '';
$pruid = '';
$prem1 = '';
$prem2 = '';

// DB credentials
$prDbname = '';
$prDbhost = '';
$prTable = '';
$schTable = '';
$prDbusername = '';
$prDbpassword = '';

// User can add new programme
$canAdd = 1;
// Array of fields to skip while updating
$skippedFields = array();
//$skippedFields = array('emails1','schnip','dimo','sch1','sch2','emails2','titel','categ','sur1','sur2','sur3');

// custom messages
$msg_insertSuccess = '<center><label class="font">Η φόρμα υποβλήθηκε με επιτυχία!<br><a href=\'javascript:window.open("","_self").close();\'>Κλείσιμο καρτέλας</a></label></center>';
$msg_insertFail = '<center><label class="font"><strong>Προέκυψε σφάλμα.</strong><br/>Η υποβολή της φόρμας απέτυχε.<br><a href=\'javascript:window.open("","_self").close();\'>Κλείσιμο καρτέλας</a></label></center>';
$msg_updateSuccess = '<center><label class="font"><h3>Επιτυχής ενημέρωση!</h3><br><a href=\'javascript:window.open("","_self").close();\'>Κλείσιμο καρτέλας</a></label></center>';
$msg_updateFail = '<center><label class="font"><strong>Προέκυψε σφάλμα.</strong><br/>Η ενημέρωση της εγγραφής απέτυχε.<br><a href=\'javascript:window.open("","_self").close();\'>Κλείσιμο καρτέλας</a></label></center>';

$contactInfo = "";

// hide fields @ program form
$hiddenFields = array('id','timestamp','vev');

?>