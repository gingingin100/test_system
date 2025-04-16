<?php 

require "func.php";

$serverName = $_SERVER['HTTP_X_SERVER_NAME'] ?? 'unknown';
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch($serverName){
    case 'auth':
        switch($requestMethod){
            case 'GET':
                $params = $_GET;
                $payload = ['received'=>$params];
                $response = sendHttpRequest('GET', 'http://database_service/db', $payload);
                echo $response;
                break;
            case 'POST':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                $response = sendHttpRequest('POST','http://database_service/db',['received'=> $decodedData['received']]); 
                echo $response;                     
                break;
        }
        break;
    default:
        echo json_encode(['error'=>'Unknown Server']);
        break;    
}