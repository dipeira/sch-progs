<?php
  require_once('./vendor/autoload.php');
  
  $id = $_GET['id'].".pdf";
  
  //override memory limit
  ini_set('memory_limit', '-1');
  // FPDI lib is used. File must be PDF/A (v.1.4)
  //initiate FPDI
  $pdf = new \fpdi\FPDI();
  
  //add a page
  $pdf->AddPage();
  //set the source file (includes all certificates)
  $fn = "./pdf/vev.pdf";
  $pdf->setSourceFile($fn);
  //import page
  $tplIdx = $pdf->importPage($id);
  //use the imported page 
  $pdf->useTemplate($tplIdx);

  ob_end_clean();
  // output PDF to user's browser
  $pdf->Output();
?>


