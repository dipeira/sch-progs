<?php
require_once('conf.php'); // Include your configuration file

// get program record
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Retrieve the record ID from the GET request
    $recordId = $_GET['id'];

    // Use the $recordId to fetch the record details from your database
    $conn = new mysqli($prDbhost, $prDbusername, $prDbpassword, $prDbname);

    // Construct your SQL query based on the record ID
    $sql = "SELECT p.*, s.name as sch1name FROM $prTable p JOIN $schTable s ON p.sch1 = s.id WHERE p.id = $recordId";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Fetch the record data
        $recordData = $result->fetch_assoc();

        // Close the database connection
        $conn->close();

        // Return the record data as JSON (or any other format you prefer)
        header('Content-Type: application/json');
        echo json_encode($recordData);
    } else {
        // Handle the case where the record doesn't exist
        echo 'Record not found';
    }

// get school name (by id)
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['sch_id'])) {
    $mysqli = new mysqli($prDbhost, $prDbusername, $prDbpassword, $prDbname);
    // Query your database to get options from the $schTable table
    $sql = "SELECT name FROM $schTable WHERE id=".$_GET['sch_id'];
    $result = $mysqli->query($sql);
    $row = $result->fetch_assoc();
    echo $row['name'];

// get all schools
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['all_schools'])) {
    $mysqli = new mysqli($prDbhost, $prDbusername, $prDbpassword, $prDbname);

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    // Query your database to get options from the $schTable table
    if (isset($_GET['term']) ){
        $sql = "SELECT id, name FROM $schTable WHERE name like '%". $_GET['term'] . "%'";   
    } else {
        $sql = "SELECT id, name FROM $schTable";    
    }    
    
    $result = $mysqli->query($sql);

    $options = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $options[] = array(
                'id' => $row['id'],
                'text' => $row['name']
            );
        }
    }

    // Close the database connection
    $mysqli->close();

    // Return the options as JSON
    echo json_encode($options);

// add record
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && $_POST['id'] == 0) {
    // INSERT operation
    // Connect to your database (adjust these parameters as needed)
    $mysqli = new mysqli($prDbhost, $prDbusername, $prDbpassword, $prDbname);

    // Check for a successful database connection
    if ($mysqli->connect_error) {
        $response = ['success' => false, 'error' => 'Database connection error'];
        echo json_encode($response);
        exit;
    }

    // Create an empty array to store the SQL insert fields and values
    $fields = array();
    $values = array();

    // Iterate through the posted fields and construct SQL insert fields and values
    foreach ($_POST as $key => $value) {
        // build the SQL insert fields and values
        if ($key == 'praxidate') {
            $dateTime = DateTime::createFromFormat('d/m/Y', $value);
            if ($dateTime != false) {
                $mysql_date = $dateTime->format('Y-m-d');
                $fields[] = "`$key`";
                $values[] = "'" . $mysql_date . "'";
            }
            continue;
        }
        // Sanitize and validate the values as needed
        $fields[] = "`$key`";
        $values[] = "'" . mysqli_real_escape_string($mysqli, $value) . "'";
    }

    if (count($fields) > 0 && count($values) > 0) {
        // Construct the SQL query for INSERT
        $sql = "INSERT INTO $prTable (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";

        // Execute the SQL query to insert the new record
        if (mysqli_query($mysqli, $sql)) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false, 'error' => 'Database insert error: ' . mysqli_error($mysqli)];
        }
    } else {
        $response = ['success' => false, 'error' => 'No fields to insert'];
    }

    // Close the database connection
    $mysqli->close();

    // Return a JSON response indicating success or failure
    echo json_encode($response);
// update record
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && $_POST['id'] > 0) {
    // UPDATE operation
    $recordId = $_POST['id'] > 0 ? $_POST['id'] : false;
    // Connect to your database (adjust these parameters as needed)
    $mysqli = new mysqli($prDbhost, $prDbusername, $prDbpassword, $prDbname);

    // Check for a successful database connection
    if ($mysqli->connect_error) {
        $response = ['success' => false, 'error' => 'Database connection error'];
        echo json_encode($response);
        exit;
    }

    // Create an empty array to store the SQL updates
    $updates = array();

    // Iterate through the posted fields and construct SQL updates
    foreach ($_POST as $key => $value) {
        // Exclude the 'record_id' field and build the SET part of the SQL statement
        if ($key !== 'record_id') {
            if ($key == 'praxidate') {
                $dateTime = DateTime::createFromFormat('d/m/Y', $value);
                if ($dateTime != false) {
                    $mysql_date = $dateTime->format('Y-m-d');
                    $updates[] = "`$key` = '" . $mysql_date . "'";
                }
                continue;
            }
            // Sanitize and validate the values as needed
            $updates[] = "`$key` = '" . mysqli_real_escape_string($mysqli, $value) . "'";
        }
    }

    if (count($updates) > 0) {
        // Construct the SQL query for UPDATE
        $sql = "UPDATE $prTable SET " . implode(', ', $updates) . " WHERE id = " . (int)$recordId;

        // Execute the SQL query to update the record
        if (mysqli_query($mysqli, $sql)) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false, 'error' => 'Database update error: ' . mysqli_error($mysqli)];
        }
    } else {
        $response = ['success' => false, 'error' => 'No fields to update'];
    }

    // Close the database connection
    $mysqli->close();

    // Return a JSON response indicating success or failure
    echo json_encode($response);
// delete record
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];

    // Connect to the database
    $mysqli = new mysqli($prDbhost, $prDbusername, $prDbpassword, $prDbname);

    // Check for a successful database connection
    if ($mysqli->connect_error) {
        $response = ['success' => false, 'error' => 'Database connection error'];
        echo json_encode($response);
        exit;
    }

    // Prepare the SQL query to delete the record
    $sql = "DELETE FROM $prTable WHERE id = ?";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('i', $deleteId);

        // Execute the statement
        if ($stmt->execute()) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false, 'error' => 'Database delete error: ' . $stmt->error];
        }

        $stmt->close();
    } else {
        $response = ['success' => false, 'error' => 'Statement preparation failed: ' . $mysqli->error];
    }

    // Close the database connection
    $mysqli->close();

    // Return a JSON response
    echo json_encode($response);
} else {
    // Handle invalid or missing parameters
    $response = ['success' => false, 'error' => 'Invalid request'];
    echo json_encode($response);
}
//}
?>
