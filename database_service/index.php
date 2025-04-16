<?php 

require "func.php";

$serverName = $_SERVER['HTTP_X_SERVER_NAME'] ?? 'unknown';
$requestMethod = $_SERVER['REQUEST_METHOD'];
// $inputData = file_get_contents("php://input");
// $decodedData = json_decode($inputData, true) ?? [];

switch ($serverName) {
    case 'auth':
        switch ($requestMethod) {
            case 'GET':
                if (isset($_GET['received']['email'])) {
                    $email = $_GET['received']['email'];
                    echo getUserByEmail($email);
                } else {
                    echo json_encode(['error' => 'ID parameter missing']);
                }
                break;
            case 'POST':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                if(isset($decodedData['received']['logout'])==true){
                    echo logoutUser($decodedData['received']['api_key']);
                }else if(isset($decodedData['received']['api_key'])){
                    echo registerUser($decodedData['received']);
                }else{
                    echo loginUser($decodedData['received']['email'],$decodedData['received']['password']);
                }
                break;                
        }
        break;

    case 'event':
        switch ($requestMethod) {
            case 'GET':
                if (isset($_GET['id'])) {
                    retrieve($_GET['id']);
                } else {
                    retrieve();
                }
                break;
            case 'POST':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                echo createEvent($decodedData['received']);                
                break;
            case 'PUT':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                echo json_encode($decodedData['received']);
                echo update($decodedData['id'], $decodedData['received']);            
                break;            
            case 'DELETE':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                echo deleteEvent($decodedData['id']);
                break;
        }
        break;
    
    case 'reg':
        switch ($requestMethod) {
            case 'GET':
                if (isset($_GET['id'])) {
                    $id = $_GET['id'];
                    echo getUserEvents($id);
                } else {
                    echo json_encode(['error' => 'ID parameter missing']);
                }
                break;
            case 'POST':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                echo addAttendee($decodedData['received']['api_key'],$decodedData['received']['id']);
                break;
        }
        break;

    // Add more services as needed

    default:
        echo json_encode(['error' => 'Unknown server']);
}
