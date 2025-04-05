<?php
require "func.php";

$_SERVER['HTTP_X_SERVER_NAME'] = 'auth';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = ['email' => 'john@example.com'];

$serverName = $_SERVER['HTTP_X_SERVER_NAME'] ?? 'unknown';
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch($serverName){
    case 'auth':
        switch($requestMethod){
            case 'GET':
                $params = $_GET;
                $payload = ['recieved' => $params];
                $response = sendHttpRequest('GET', 'http://load_balancer/db', $payload);
                echo $response;
                break;
            case 'POST':
                break;
        }
        break;
    default:
        echo json_encode(['error'=>'Unknown Server']);
        break;    
}
