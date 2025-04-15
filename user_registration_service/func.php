<?php

function sendHttpRequest($method, $url, $data = []) {
    $ch = curl_init();
    
    // Convert data to JSON if it's not empty
    $payload = !empty($data) ? json_encode($data) : '';
    if ($method === 'GET' && !empty($data)) {
        // Append query parameters to URL
        $url .= '?' . http_build_query($data);
    }   

    $headers = [
        'Content-Type: application/json',
        'X-Server-Name: reg',
        'X-Internal-Request: true'
    ];

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method)); // GET, POST, PUT, DELETE
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method !== 'GET' && !empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return 'Error: ' . curl_error($ch);
    }

    curl_close($ch);
    return $response;
}

