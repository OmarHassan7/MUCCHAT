<?php
// Establish a database connection (replace with your database credentials)
$mysqli = new mysqli("localhost", "root", "", "sharkawi_muc");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Retrieve messages from the database based on the specified category
$category = isset($_GET['category']) ? $_GET['category'] : 'General';
$stmt = $mysqli->prepare("SELECT * FROM messages WHERE category = ? ORDER BY timestamp DESC");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$messages = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Add each message to the array
        $messages[] = array(
            'sender' => $row['sender'],
            'category' => $row['category'],
            'message' => $row['message'],
        );
    }
}

// Close the database connection
$stmt->close();
$mysqli->close();

// Return messages as JSON
header('Content-Type: application/json');
echo json_encode($messages);
?>
