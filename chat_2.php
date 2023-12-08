<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['idnumber'])) {
    header("Location: login.php"); // Redirect to the login page if not logged in
    exit();
}

// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'chat_db';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine the selected conversation
$selectedConversation = isset($_GET['conversation']) ? $_GET['conversation'] : 'engineering';

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = isset($_POST['message']) ? $_POST['message'] : '';

    if (!empty($message)) {
        $sender = $_SESSION['idnumber'];

        // Store the message in the database
        $sql = "INSERT INTO messages (sender, conversation, message) VALUES ('$sender', '$selectedConversation', '$message')";
        $conn->query($sql);

        // Close the database connection
        $conn->close();

        // Reload the page
        header("Location: " . $_SERVER['PHP_SELF'] . "?conversation=$selectedConversation");
        exit();
    }
}

// Retrieve messages from the database
$sql = "SELECT sender, message FROM messages WHERE conversation = '$selectedConversation' ORDER BY timestamp ASC";
$result = $conn->query($sql);
$messages = $result->fetch_all(MYSQLI_ASSOC);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        #container {
            display: flex;
            height: 100vh;
        }

        #sidebar {
            width: 20%;
            padding: 20px;
            background-color: #333;
            color: #fff;
        }

        #sidebar h3 {
            margin-bottom: 20px;
        }

        .conversation-link {
            display: block;
            padding: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            background-color: #555;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .conversation-link:hover {
            background-color: #777;
        }

        #chat-container {
            flex: 1;
            padding: 20px;
        }

        #chat-messages {
            height: 70vh;
            overflow-y: auto;
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
        }

        #message-input {
            width: 80%;
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        #send-button {
            padding: 10px;
            cursor: pointer;
            background-color: #4285f4;
            color: #fff;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        #send-button:hover {
            background-color: #0066cc;
        }
    </style>
</head>

<body>
    <div id="container">
        <div id="sidebar">
            <h3>Conversations</h3>
            <a href="?conversation=engineering" class="conversation-link">Engineering</a>
            <a href="?conversation=physical-therapy" class="conversation-link">Physical Therapy</a>
            <a href="?conversation=business" class="conversation-link">Business</a>
        </div>

        <div id="chat-container">
            <div id="chat-messages"></div>
            <div>
                <input type="text" id="message-input" placeholder="Type your message...">
                <button id="send-button" onclick="sendMessage('<?php echo $selectedConversation; ?>')">Send</button>
            </div>
        </div>
    </div>

<script>
    // Update the displayMessage function to use the retrieved messages
    function displayMessages() {
        var chatMessages = document.getElementById('chat-messages');
        chatMessages.innerHTML = '';

        <?php foreach ($messages as $message) : ?>
            var messageContainer = document.createElement('div');
            messageContainer.innerHTML = '<strong><?php echo $message['sender']; ?>:</strong> <?php echo $message['message']; ?>';
            chatMessages.appendChild(messageContainer);
        <?php endforeach; ?>

        // Scroll to the bottom to show the latest message
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Call displayMessages when the page loads
    window.onload = function () {
        displayMessages();
    };

    // Modify sendMessage to send messages via AJAX and update the messages dynamically
    function sendMessage(conversation) {
        var messageInput = document.getElementById('message-input');
        var message = messageInput.value.trim();

        if (message !== '') {
            var sender = '<?php echo $_SESSION['idnumber']; ?>';

            // Send the message to the server using AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', window.location.href, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Update the messages after sending
                    displayMessages();
                }
            };
            xhr.send('message=' + encodeURIComponent(message));

            messageInput.value = '';
        }
    }
    function sendMessage(conversation) {
                var messageInput = document.getElementById('message-input');
                var message = messageInput.value.trim();

                if (message !== '') {
                    var sender = '<?php echo $_SESSION['idnumber']; ?>';

                    // Send the message to the server using AJAX
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', window.location.href, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 4 && xhr.status == 200) {
                            // Update the messages after sending
                            displayMessages();

                            // Reload the page after sending
                            location.reload();
                        }
                    };
                    xhr.send('message=' + encodeURIComponent(message));

                    messageInput.value = '';
                }
            }
</script>
</body>

</html>
