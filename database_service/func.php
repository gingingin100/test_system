<?php

$servername = getenv('DB_HOST');
$password = getenv('DB_PASS');
$username = getenv('DB_USER');
$dbname = getenv('DB_NAME');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$redis = new Redis();
$redis->connect(getenv('REDIS_HOST'), getenv('REDIS_PORT'));


function getUserEvents($user_id) {
    global $conn, $redis;

    // Check if the data is cached in Redis
    $cacheKey = "user_events_{$user_id}";
    $cachedData = $redis->get($cacheKey);

    if ($cachedData) {
        // Data is in Redis, return the cached data
        return $cachedData;
    }

    // If not cached, query the database
    $stmt = $conn->prepare("SELECT e.id, e.event_name, e.start_date, e.end_date, e.location, e.price, e.created_by
                            FROM attendees a
                            INNER JOIN events e ON a.event_id = e.id
                            WHERE a.user_id = ?");
    if (!$stmt) {
        return json_encode(['error' => 'Database error while fetching user events', 'details' => $conn->error]);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        return json_encode(['message' => 'No events found for this user']);
    }

    $stmt->bind_result($id, $event_name, $start_date, $end_date, $location, $price, $created_by);

    $events = [];
    while ($stmt->fetch()) {
        $events[] = [
            'event_id' => $id,
            'event_name' => $event_name,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'location' => $location,
            'price' => $price,
            'created_by' => $created_by,
        ];
    }

    // Cache the data in Redis for future use (set a timeout of 3600 seconds, i.e., 1 hour)
    $redis->setex($cacheKey, 300, json_encode(['events' => $events]));

    return json_encode(['events' => $events]);
}

function addAttendee($api_key, $event_id) {
    global $conn;
    // Step 1: Fetch user_id based on api_key
    $stmt = $conn->prepare("SELECT id FROM users WHERE api_key = ?");
    if (!$stmt) {
        return json_encode(['error' => 'Database error while fetching user ID', 'details' => $conn->error]);
    }

    $stmt->bind_param("s", $api_key); // Bind the API key as a string
    $stmt->execute();
    $stmt->store_result();

    // Check if the api_key is valid (if user exists)
    if ($stmt->num_rows === 0) {
        return json_encode(['error' => 'Invalid API key or user not found']);
    }

    // Fetch user_id from the result
    $stmt->bind_result($user_id);
    $stmt->fetch();

    // Step 2: Insert the user_id and event_id into the attendees table
    $insertStmt = $conn->prepare("INSERT INTO attendees (user_id, event_id) VALUES (?, ?)");
    if (!$insertStmt) {
        return json_encode(['error' => 'Database error while adding attendee', 'details' => $conn->error]);
    }

    $insertStmt->bind_param("ii", $user_id, $event_id); // Bind both user_id and event_id as integers

    // Execute the insert query
    if ($insertStmt->execute()) {
        return json_encode(['message' => 'Attendee added successfully']);
    } else {
        return json_encode(['error' => 'Failed to add attendee', 'details' => $insertStmt->error]);
    }
}


function retrieve($id = null) {
    global $conn , $redis; // Use the database connection from db.php
    $cacheKey = $id ? "event_{$id}" : "all_events";
    $cachedData = $redis->get($cacheKey);
    if ($cachedData) {
        // Data is in Redis, return the cached data
        echo $cachedData;
        return;
    }
    if ($id === null) {
        // Retrieve all records from events table
        $sql = "SELECT * FROM events";
    } else {
        // Retrieve a specific record by ID
        $sql = "SELECT * FROM events WHERE id = " . $id;
    }

    // Prepare the statement to prevent SQL injection
    $stmt = $conn->prepare($sql);

    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    $stmt->close();
    
    $redis->setex($cacheKey, 300, json_encode($events));

    // Return the results as JSON
    if (!headers_sent() && empty($_SERVER['HTTP_CONTENT_TYPE'])) {
        header('Content-Type: application/json');
    }
    
    echo json_encode($events);
}

function update($id, $fields) {
    global $conn; 

    if (!is_array($fields) || empty($fields)) {
        return json_encode(['error' => 'Invalid or empty fields']);
    }

    $setClause = [];
    foreach ($fields as $column => $value) {
        $setClause[] = "`$column` = ?";
    }
    $setClauseString = implode(", ", $setClause);

    $sql = "UPDATE events SET $setClauseString WHERE id = ?";
    $stmt = $conn->prepare($sql);

    $types = str_repeat("s", count($fields)) . "i"; 
    $values = array_values($fields);
    $values[] = $id;

    $stmt->bind_param($types, ...$values);

    $stmt->execute();

    // Check if any rows were updated
    if ($stmt->affected_rows > 0) {
        return json_encode(['success' => 'Record updated successfully']);
    } elseif ($stmt->affected_rows === 0) {
        return json_encode(['error' => 'No record found or data is the same']);
    } else {
        return json_encode(['error' => 'Update failed', 'details' => $stmt->error]);
    }
}

function deleteEvent($id) {
    global $conn; 

    $sql = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return json_encode(['error' => 'Database error', 'details' => $conn->error]);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return json_encode(['success' => 'Record deleted successfully']);
    } else {
        return json_encode(['error' => 'No record found with that ID']);
    }
}

function createEvent($fields) {
    global $conn; 

    if (!is_array($fields) || empty($fields)) {
        return json_encode(['error' => 'Invalid or empty fields']);
    }

    if (empty($fields['event_name']) || empty($fields['start_date']) || empty($fields['end_date']) || empty($fields['location']) || empty($fields['price']) || empty($fields['created_by'])) {
        return json_encode(['error' => 'Missing required fields: event_name, start_date, end_date, location, price, or created_by']);
    }

    $sql = "INSERT INTO events (event_name, start_date, end_date, location, price, created_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return json_encode(['error' => 'Database error', 'details' => $conn->error]);
    }

    $stmt->bind_param("ssssds", $fields['event_name'], $fields['start_date'], $fields['end_date'], $fields['location'], $fields['price'], $fields['created_by']);

    if ($stmt->execute()) {
        return json_encode(['success' => 'Event created successfully']);
    } else {
        return json_encode(['error' => 'Failed to create event', 'details' => $stmt->error]);
    }
}


function registerUser($fields) {
    global $conn; // Use the global database connection
    echo json_encode($fields);
    // Check if the necessary fields are present in the $fields array
    if (!isset($fields['name'], $fields['email'], $fields['password'], $fields['api_key'])) {
        return json_encode(['error' => 'Missing required fields']);
    }

    // Extract the values from the fields array
    $name = $fields['name'];
    $email = $fields['email'];
    $password = $fields['password'];
    $api_key = $fields['api_key'];

    // Prepare the SQL query
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, api_key) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        return json_encode(['error' => 'Database error', 'details' => $conn->error]);
    }

    // Bind the parameters
    $stmt->bind_param("ssss", $name, $email, $password, $api_key);

    // Execute the query
    if ($stmt->execute()) {
        return json_encode(["message" => "User registered", "api_key" => $api_key]);
    } else {
        return json_encode(["error" => "User registration failed", "details" => $stmt->error]);
    }
}

function loginUser($email, $enteredPassword) {
    global $conn;

    $stmt = $conn->prepare("SELECT id, name, email, api_key, password, auth_flag FROM users WHERE email = ?");
    if (!$stmt) {
        return json_encode(['error' => 'Database error', 'details' => $conn->error]);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    

    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        return json_encode(['error' => 'No user found with this email']);
    }


    $stmt->bind_result($id, $name, $email, $api_key, $hashedPassword, $authFlag);
    $stmt->fetch();


    if (password_verify($enteredPassword, $hashedPassword)) {
        
        $updateStmt = $conn->prepare("UPDATE users SET auth_flag = ? WHERE id = ?");
        if (!$updateStmt) {
            return json_encode(['error' => 'Database error while updating auth flag', 'details' => $conn->error]);
        }
        
        $authFlag = 1;
        $updateStmt->bind_param("ii", $authFlag, $id);

        if ($updateStmt->execute()) {
            return json_encode(['message' => 'Login successful', 'user_id' => $id, 'name' => $name, 'api_key' => $api_key]);
        } else {
            return json_encode(['error' => 'Error updating auth flag', 'details' => $updateStmt->error]);
        }
    } else {
        return json_encode(['error' => 'Invalid password']);
    }
}

function getUserByEmail($email) {
    global $conn;

    // Prepare the SQL query to fetch the user by email where auth_flag is true
    $stmt = $conn->prepare("SELECT id, name, email, api_key, password, auth_flag FROM users WHERE email = ? AND auth_flag = 1");
    if (!$stmt) {
        return json_encode(['error' => 'Database error', 'details' => $conn->error]);
    }

    // Bind the parameter
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // Store the result and check if a matching user is found
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        return json_encode(['error' => 'No user found with this email or the user is not authenticated']);
    }

    // Bind the result to variables
    $stmt->bind_result($id, $name, $email, $api_key, $password, $auth_flag);
    $stmt->fetch();

    // Return user information in JSON format
    return json_encode([
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'api_key' => $api_key,
        'auth_flag' => $auth_flag
    ]);
}

function logoutUser($api_key) {
    global $conn;

    // Prepare a statement to find the user with the provided api_key
    $stmt = $conn->prepare("SELECT id FROM users WHERE api_key = ?");
    if (!$stmt) {
        return json_encode(['error' => 'Database error', 'details' => $conn->error]);
    }

    $stmt->bind_param("s", $api_key);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        return json_encode(['error' => 'Invalid API key or user not found']);
    }

    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    // Update the auth_flag to 0 (logout)
    $updateStmt = $conn->prepare("UPDATE users SET auth_flag = 0 WHERE id = ?");
    if (!$updateStmt) {
        return json_encode(['error' => 'Database error while updating auth flag', 'details' => $conn->error]);
    }

    $updateStmt->bind_param("i", $id);
    if ($updateStmt->execute()) {
        return json_encode(['message' => 'Logout successful']);
    } else {
        return json_encode(['error' => 'Error updating auth flag', 'details' => $updateStmt->error]);
    }
}
