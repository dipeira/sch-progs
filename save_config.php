<?php
$configData = json_decode($_POST['configData'], true);
$file = 'config.json';

// Encode the updated configuration data as JSON and write it to the file
if (file_put_contents($file, json_encode($configData, JSON_PRETTY_PRINT))) {
    echo "success";
} else {
    http_response_code(500);
    echo "error";
}
?>
