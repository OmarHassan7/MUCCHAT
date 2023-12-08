<?php
// Function to check if the cookie is expired
function isCookieExpired($cookieName) {
    return !isset($_COOKIE[$cookieName]) || (time() > $_COOKIE[$cookieName]);
}

// Check if the user is logged in with the correct user ID
$targetUserId = 22110543;

if (isset($_COOKIE["user_id"])) {
    if ($_COOKIE["user_id"] == $targetUserId && !isCookieExpired("user_id")) {
        // User is logged in with the correct user ID
        $loggedInUserId = $_COOKIE["user_id"];
        echo "User ID: $loggedInUserId is logged in.";
    } else {
        // User ID is incorrect or the session has expired
        echo "Error: User ID is incorrect or the session has expired.";
    }
} else {
    // User is not logged in
    echo "Error: User is not logged in.";
}
?>
