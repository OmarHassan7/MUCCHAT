<?php
session_start();

// Check if 'idnumber' is set and not empty
if (!isset($_POST['idnumber']) || empty($_POST['idnumber'])) {
    header("Location: index.html");
    exit();
}

// Assuming your database credentials
$host = "localhost";
$username_db = "root";
$password_db = "";
$database = "sharkawi_muc";

// Create a connection to the database
$conn = new mysqli($host, $username_db, $password_db, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 'idnumber' from the form
$username = $_POST['idnumber'];

// Fetch email from the users table where id equals $username
$sql = "SELECT email FROM users WHERE id = '$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the first row
    // Fetch the first row
    $row = $result->fetch_assoc();

    // Get the email
    $email = $row['email'];

    // Remove the last 20 characters
    $emailWithoutLast20 = substr($email, 0, -20);

    // Get the remaining characters
    $name = $emailWithoutLast20;
    $user_id = $_POST['idnumber'];
} else {
    echo "";
}

// Close the database connection when you're done
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat</title>
    <link rel="stylesheet" href="./chat-style.css" />
</head>

<body>
    <div id="container">
        <div id="sidebar">
            <div id="welcome-message">Welcome,
                <?php echo    $name; ?>!
            </div>
            <h3>Conversations</h3>
            <div id="channels">

            </div>
            <!-- <a href="#" class="conversation-link" data-channel="General">General</a> -->
            <?php
            // if (substr($username, 0, 4) === "2211") {
            //     echo '<a href="#" class="conversation-link" data-channel="PhysicalTherapy">Physical Therapy</a>';
            // } elseif (substr($username, 0, 4) === "2212") {
            //     echo '<a href="#" class="conversation-link" data-channel="Engineering">Engineering</a>';
            // } elseif (substr($username, 0, 4) === "2213") {
            //     echo '<a href="#" class="conversation-link" data-channel="Business">Business</a>';
            // }
            ?>
            <div id="online-users-container">
                <h3>Online Users</h3>
                <div id="online-users"></div>
            </div>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div id="chat-container">
            <div id="chat-messages"></div>
            <div>
                <input type="text" id="message-input" placeholder="Type your message..." class="input">
                <select id="category-select">
                    <option value="General">General</option>
                    <option value="Engineering">Engineering</option>
                    <option value="PhysicalTherapy">Physical Therapy</option>
                    <option value="Business">Business</option>
                </select>
                <!-- Add input field for shift value -->
                <label for="shift-input">Shift Value:</label>
                <input type="number" id="shift-input" min="1" value="3" class="input">
                <button id="send-button" class="button">Send</button>
            </div>
        </div>
    </div>

    <script>
        const ws = new WebSocket('ws://localhost:8080?username=<?php echo $name; ?>&user_id=<?php echo $user_id ?> ');
        const userName = "<?php echo $name; ?>";
        let currentChannel = "akjshdksajdlksad";

        function sendMessage() {
            const messageInput = document.getElementById('message-input');
            const categorySelect = document.getElementById('category-select');
            const shiftInput = document.getElementById('shift-input'); // Get the shift input
            const message = messageInput.value.trim();
            const category = categorySelect.value || "General";
            shiftValue = parseInt(shiftInput.value) || 3; // Get the shift value or default to 3

            // Function to encrypt a message using Caesar cipher with the specified shift value
            function encryptMessage(text, shift) {
                return [...text]
                    .map(char => {
                        const charCode = char.charCodeAt(0);
                        if (charCode >= 65 && charCode <= 90) {
                            return String.fromCharCode((charCode - 65 + shift) % 26 + 65); // Uppercase letters
                        } else if (charCode >= 97 && charCode <= 122) {
                            return String.fromCharCode((charCode - 97 + shift) % 26 + 97); // Lowercase letters
                        } else {
                            return char; // Non-alphabetic characters
                        }
                    })
                    .join('');
            }

            if (message !== '') {
                // Encrypt the message using Caesar cipher with the specified shift value
                // const encryptedMessage = encryptMessage(message, shiftValue);

                // const fullMessage = `${userName}:${category}:${encryptedMessage}`;
                // ws.send(fullMessage);

                const data = {
                    sender: encodeURIComponent(userName),
                    message: encodeURIComponent(message),
                    channel_id: encodeURIComponent(window.current_channel_id)
                };
                fetch('functions/insert_message.php', {
                        method: 'POST',
                        headers: {
                            'Content-type': 'application/x-www-form-urlencoded'
                        },
                        body: Object.keys(data).map(key => `${key}=${data[key]}`).join('&')
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                // const xhr = new XMLHttpRequest();
                // xhr.open('POST', 'insert_message.php', true);
                // xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                // xhr.send(`sender=${encodeURIComponent(userName)}&message=${encodeURIComponent(encryptedMessage)}&category=${encodeURIComponent(category)}`);

                messageInput.value = '';
            }
        }
        // assume model is DB
        // model

        // view 
        function clearChatMessages() {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';
        }

        // controller


        function updateOnlineUsers(onlineUsers) {
            const onlineUsersElement = document.getElementById('online-users');
            onlineUsersElement.innerHTML = '<ul id="online-users-list"></ul>';

            const onlineUsersList = document.getElementById('online-users-list');
            const categorySelect = document.getElementById('category-select');
            console.log("a7a", {
                onlineUsers
            });
            console.log(onlineUsers[1]);
            onlineUsers.forEach(({
                name: username,
                user_id
            }) => {
                const listItem = document.createElement('li');
                const link = document.createElement('li');

                link.setAttribute('data-channel', username);
                link.setAttribute('data-username', username);
                link.setAttribute('data-userid', user_id);
                link.textContent = username;
                link.setAttribute('class', 'online-user');

                link.addEventListener('click', function(e) {
                    const elem = e.target;
                    fetch("functions/create_channel.php", {
                        method: "POST",
                        body: JSON.stringify({
                            partcipants: [{
                                id: <?php echo $username; ?>,
                                name: "<?php echo $name; ?>"
                            }, {
                                id: +elem.getAttribute("data-userid"),
                                name: elem.getAttribute("data-username")
                            }]
                        })
                    })
                    clearChatMessages();

                    // let option = categorySelect.querySelector(`option[value="${username}"]`);
                    // if (!option) {
                    //     option = document.createElement('option');
                    //     option.value = username;
                    //     option.textContent = username;
                    //     categorySelect.appendChild(option);

                    //     // Create a new conversation link
                    //     const conversationLink = document.createElement('a');
                    //     conversationLink.href = '#';
                    //     conversationLink.setAttribute('data-channel', username);
                    //     conversationLink.setAttribute('data-username', username);
                    //     conversationLink.setAttribute('data-userid', user_id);
                    //     conversationLink.textContent = username;
                    //     conversationLink.classList.add('conversation-link');

                    //     // Add click event listener to the new conversation link
                    //     conversationLink.addEventListener('click', function(event) {
                    //         event.preventDefault();
                    //         const channel = this.getAttribute('data-channel');
                    //         currentChannel = channel;
                    //         loadMessages(currentChannel);
                    //     });

                    //     // Append the new conversation link to the conversations list
                    //     document.getElementById('sidebar').insertBefore(conversationLink, document.getElementById('online-users-container'));
                    // }

                    // option.selected = true;
                    // event.preventDefault();
                    // const channel = this.getAttribute('data-channel');
                    // currentChannel = channel;
                    // // loadMessages(currentChannel);
                });

                listItem.appendChild(link);
                onlineUsersList.appendChild(listItem);
            });
        }



        function appendMessage(message, userClass) {
            const chatMessages = document.getElementById('chat-messages');
            const messageContainer = document.createElement('div');
            messageContainer.className = `message-container ${userClass}`;

            const idChannelContainer = document.createElement('div');
            idChannelContainer.className = 'id-channel-container';

            const idChannelText = document.createElement('span');
            idChannelText.className = 'id-channel-text';
            idChannelText.textContent = message.split(': ')[0];

            const messageText = document.createElement('span');
            messageText.className = 'message-text';
            const encryptedMessage = message.split(': ').slice(1).join(':');
            messageText.textContent = encryptedMessage;

            messageContainer.addEventListener('click', toggleMessage);

            function toggleMessage() {

                if (messageText.textContent === encryptedMessage) {
                    let decrypt_shift = +prompt('Enter shift value');
                    // If the current content is the encrypted message, decrypt it
                    messageText.textContent = decryptMessage(encryptedMessage, decrypt_shift);
                } else {
                    // If the current content is decrypted, revert to the encrypted message
                    messageText.textContent = encryptedMessage;
                }
            }
            idChannelContainer.appendChild(idChannelText);
            idChannelContainer.appendChild(document.createTextNode(':'));
            messageContainer.appendChild(idChannelContainer);
            messageContainer.appendChild(messageText);
            chatMessages.appendChild(messageContainer);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function decryptMessage(text, shift) {
            return [...text]
                .map(char => {
                    const charCode = char.charCodeAt(0);
                    if (charCode >= 65 && charCode <= 90) {
                        return String.fromCharCode(((charCode - 65 - shift + 26 * 999999999) % 26) + 65); // Uppercase letters
                    } else if (charCode >= 97 && charCode <= 122) {
                        return String.fromCharCode(((charCode - 97 - shift + 26 * 999999999) % 26) + 97); // Lowercase letters
                    } else {
                        return char; // Non-alphabetic characters
                    }
                })
                .join('');
        }


        document.getElementById('send-button').addEventListener('click', sendMessage);

        document.getElementById('message-input').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });

        ws.onmessage = function(e) {
            const {
                event,
                data
            } = JSON.parse(e.data);

            switch (event) {
                case "online_users":
                    return updateOnlineUsers(data);
                default:
                    throw new Error("unhandled case");
            }
            // const parts = event.data.split(':');
            // const sender = parts[0];
            // const category = parts[1] || "General";
            // const message = parts.slice(2).join(':');
            // const isCurrentUser = sender === userName;
            // const userClass = isCurrentUser ? 'user-message' : 'other-message';
            // appendMessage(`${sender} (${category}): ${message}`, userClass);

            // Check if the message belongs to the current channel
            // if (category === currentChannel) {
            //     appendMessage(`${sender} (${category}): ${message}`, userClass);
            // }

            // if (event.data.startsWith('Online Users:')) {
            //     const onlineUsers = event.data.replace('Online Users: ', '');

            // }
        };

        window.onload = function() {
            loadChannels();
            loadMessages(currentChannel);
            document.getElementById('welcome-message').style.display = 'block';
            loadOnlineUsers();
        };

        function renderChannels(channels) {
            console.log({
                channels
            })

            const x = channels.filter(chann => chann.name != "");
            console.log(x);
            const channelsContainer = document.getElementById("channels");
            channelsContainer.innerHTML = "";
            x.forEach(channel => {
                const elem = document.createElement("div");
                elem.className = "conversation-link"
                elem.textContent = channel.name;
                elem.setAttribute("data-channel-id", channel.id);
                elem.addEventListener('click', function(event) {

                    event.preventDefault();
                    const channel_id = this.getAttribute('data-channel-id');
                    currentChannel = channel_id;
                    window.current_channel_id = channel_id;
                    console.log("cllll", channel_id);
                    loadMessages(channel_id);
                });
                channelsContainer.appendChild(elem);
            })
        }

        function loadChannels() {
            fetch(`functions/get_channels.php`).then(res => res.json()).then(channels => {
                // Added Logic to filer Channels 
                const y = channels.filter(ch => ch.name !== "Generals" ?
                    ch.name.includes(userName) : "Generals");
                renderChannels(y);
            })
        }

        function loadMessages(channel_id) {
            clearChatMessages();
            console.log(channel_id);
            fetch(`functions/get_messages.php?channel_id=${encodeURIComponent(channel_id)}`).then(res => res.json()).then(messages =>
                messages.reverse().forEach(function(message) {
                    const isCurrentUser = message.sender === userName;
                    const userClass = isCurrentUser ? 'user-message' : 'other-message';
                    appendMessage(`${message.sender} (${message.category}): ${message.message}`, userClass);
                })
            )


            // document.getElementById('category-select').value = channel;
        }


        function loadOnlineUsers() {
            ws.send("Get Online Users");
        }
    </script>
</body>

</html>