<?php
require "db.php";
require "func.php";  // Make sure to include your necessary functions

// Simulate the input
$_SERVER['HTTP_X_SERVER_NAME'] = 'reg';  // Simulating the header 'X-Server-Name'
$_SERVER['REQUEST_METHOD'] = 'GET';  // Simulate a GET request

// Simulate the GET request parameters
$inputData = json_encode(['id' => 123]); // Simulating the body data sent in the GET request

// Here we fake the incoming 'php://input' for GET request
file_put_contents("php://input", $inputData);

// Your code
$serverName = $_SERVER['HTTP_X_SERVER_NAME'] ?? 'unknown';
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($serverName) {
    case 'reg':
        switch ($requestMethod) {
            case 'GET':
                // Capture the data sent in the GET request
                //$inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                
                // Grab 'id' from the decoded data
                $id = isset($decodedData['id']) ? $decodedData['id'] : null;
                
                // Prepare the payload to send to the database service
                $payload = ['id' => $id];
                
                // Send the request to database_service
                $response = sendHttpRequest('GET', 'http://database_service/db', $payload);
                
                // Output the response
                echo $response;
                break;
            case 'POST':
                // Handle POST if needed
                break;
        }
        break;
    default:
        echo json_encode(['error' => 'Unknown Server']);
        break;
}
?>
