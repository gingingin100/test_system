<?php
$servername = "34.56.190.88";
$username = "root";
$password = 'Z[FJO4C"=:}[X6A7';
$dbname = "test_management";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getUserEvents($user_id) {
    global $conn;
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
    global $conn; // Use the database connection from db.php

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
?>