<?php 
require "func.php";

$serverName = $_SERVER['HTTP_X_SERVER_NAME'] ?? 'unknown';
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch($serverName){
    case 'reg':
        switch($requestMethod){
            case 'GET':
                $params = $_GET;
                $id = isset($params['id']) ? $params['id'] : null;
                $payload = ['id'=>$id];
                $response = sendHttpRequest('GET', 'http://database_service/db', $payload);
                echo $response;
                break;
            case 'POST':
                $inputData = file_get_contents("php://input");
                $decodedData = json_decode($inputData, true);
                $response = sendHttpRequest('POST','http://database_service/db',["received" => [
                    "id"=>$decodedData['received']['id'],
                    "api_key"=>$decodedData['received']['api_key']
                ]]);
                break;
        }
        break;
    default:
        echo json_encode(['error'=>'Unknown Server']);
        break;    
}