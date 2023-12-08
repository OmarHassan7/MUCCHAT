<?php
// Establish a database connection (replace with your database credentials)
$mysqli = new mysqli("localhost", "root", "", "sharkawi_muc");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sender']) && isset($_POST['message']) && isset($_POST['category'])) {
        $sender = $_POST['sender'];
        $message = $_POST['message'];
        $category = $_POST['category']; // Added this line to define $category

        // Insert message into the database
        $stmt = $mysqli->prepare("INSERT INTO messages (sender, message, category) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $sender, $message, $category);
        $stmt->execute();
        $stmt->close();
    }
}

$mysqli->close();
?>
