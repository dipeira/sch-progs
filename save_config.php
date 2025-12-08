<?php
header('Content-Type: application/json');

if (!isset($_POST['configData'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No config data provided']);
    exit;
}

$configData = json_decode($_POST['configData'], true);

if ($configData === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

$file = 'config.json';

// Encode the updated configuration data as JSON and write it to the file
// JSON_PRETTY_PRINT for readability, JSON_UNESCAPED_UNICODE to preserve Greek characters
$jsonContent = json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if ($jsonContent === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to encode JSON']);
    exit;
}

if (file_put_contents($file, $jsonContent) !== false) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to write config file']);
}
?>
