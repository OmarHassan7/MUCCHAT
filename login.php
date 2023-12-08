<?php
// Check if the username is provided in the request
if(isset($_POST['username']) && !empty($_POST['username'])) {
    // Sanitize the username to prevent potential security issues
    $username = htmlspecialchars($_POST['username']);

    // Store the username in a session variable for later use
    session_start();
    $_SESSION['username'] = $username;

    // Redirect to the chat.php script
    header("Location: server.php");
    exit();
} else {
    // Redirect to an error page or display an error message
    echo " a7a";
    exit();
}
?>
