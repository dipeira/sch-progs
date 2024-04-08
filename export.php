<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet library
require_once('conf.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create a new connection to the database
$conn = new mysqli($prDbhost, $prDbusername, $prDbpassword, $prDbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the database
$sql = "SELECT *, p.id AS pid FROM $prTable p JOIN $schTable s ON s.id = p.sch1";
$result = $conn->query($sql);

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column headers dynamically based on database table columns
$columnHeaders = ['id', 'timestamp', 'sch1', 'princ1', 'sch2', 'princ2', 'nam1', 'email1', 'mob1', 'eid1', 'his1', 'qua1', 'nam2', 'email2', 'mob2', 'eid2', 'his2', 'qua2', 'nam3', 'email3', 'mob3', 'eid3', 'his3', 'qua3', 'titel', 'categ', 'subti', 'praxi', 'praxidate', 'grade', 'nr', 'nr_boys', 'nr_girls', 'cha', 'arxeio', 'theme', 'goal', 'meth', 'dura', 'month', 'visit', 'foreis', 'pedia', 'diax', 'diax_other', 'm1', 'm2', 'm3', 'm4', 'm5', 'prsnt', 'notes', 'chk', 'vev'];

// Fetch column names from the database table
$columnNames = [];
for ($i = 0; $i < $result->field_count; $i++) {
    $columnNames[] = $result->fetch_field_direct($i)->name;
}

// Set column headers in the spreadsheet
foreach ($columnHeaders as $key => $header) {
    $sheet->setCellValueByColumnAndRow($key + 1, 1, $header);
}

// Fetch and add data to the spreadsheet
$row = 2; // Start from row 2
while ($row_data = $result->fetch_assoc()) {
    // Loop through each column and add data to the spreadsheet
    foreach ($columnNames as $col => $columnName) {
        $sheet->setCellValueByColumnAndRow($col + 1, $row, $row_data[$columnName]);
    }
    $row++;
}

// Create a new Excel writer
$writer = new Xlsx($spreadsheet);

// Set the file path where the Excel file will be saved
$filePath = 'excel_file.xlsx';

// Save the Excel file to the specified path
$writer->save($filePath);

// Close the database connection
$conn->close();

// Return the file path as JSON
echo json_encode(['fileUrl' => $filePath]);
?>
