<?php 

require "func.php";

$serverName = $_SERVER['HTTP_X_SERVER_NAME'] ?? 'unknown';
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch($serverName){
    case 'event':
        switch($requestMethod){
            case 'GET':
                $params = $_GET;
                if (isset($_GET['id'])) {
                    $id = isset($params['id']) ? $params['id'] : null;
                    $payload = ['id'=>$id];
                    $response = sendHttpRequest('GET', 'http://database_service/db', $payload);
                    echo $response;
                } else {
                    $response = sendHttpRequest('GET', 'http://database_service/db');
                    echo $response;
                }
                break;
            case 'POST':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                $response = sendHttpRequest('POST','http://database_service/db',['received'=> $decodedData['received']]); 
                echo $response;               
                break;
            case 'PUT':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                $response = sendHttpRequest('PUT','http://database_service/db',['received'=> $decodedData['received'],"id"=>$decodedData['id']]);
                echo $response;
                break;                
            case 'DELETE':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                $response = sendHttpRequest('DELETE','http://database_service/db',$decodedData['id']);
                break;
        }
        break;
    default:
        echo json_encode(['error'=>'Unknown Server1']);
        break;    
}